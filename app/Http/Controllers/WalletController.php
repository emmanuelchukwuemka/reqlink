<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\WalletTransaction;

class WalletController extends Controller
{
    private string $paystackSecret;
    private string $paystackBase = 'https://api.paystack.co';

    public function __construct()
    {
        $this->paystackSecret = config('services.paystack.secret');
    }

    public function initiate(Request $request)
    {
        $request->validate([
            'amount' => 'required|numeric|min:100',
        ]);

        $user      = Auth::user();
        $amountKobo = (int) ($request->amount * 100); // Paystack uses kobo
        $reference  = 'RQL-' . strtoupper(uniqid());

        // Create pending transaction
        WalletTransaction::create([
            'user_id'      => $user->id,
            'type'         => 'credit',
            'amount'       => $request->amount,
            'balance_after'=> $user->wallet_balance + $request->amount,
            'reference'    => $reference,
            'description'  => 'Wallet top-up via Paystack',
            'status'       => 'pending',
        ]);

        $response = $this->paystackPost('/transaction/initialize', [
            'email'     => $user->email ?? $user->phone . '@resqlink.app',
            'amount'    => $amountKobo,
            'reference' => $reference,
            'callback_url' => route('wallet.callback'),
            'metadata'  => ['user_id' => $user->id],
        ]);

        if (!$response || !$response['status']) {
            return back()->withErrors(['amount' => 'Could not connect to payment gateway. Try again.']);
        }

        return redirect($response['data']['authorization_url']);
    }

    public function callback(Request $request)
    {
        $reference = $request->query('reference') ?? $request->query('trxref');

        if (!$reference) {
            return redirect()->route('dashboard')->withErrors(['wallet' => 'Invalid payment reference.']);
        }

        $response = $this->paystackGet("/transaction/verify/{$reference}");

        if (!$response || !$response['status'] || $response['data']['status'] !== 'success') {
            WalletTransaction::where('reference', $reference)->update(['status' => 'failed']);
            return redirect()->route('dashboard')->withErrors(['wallet' => 'Payment verification failed.']);
        }

        $this->creditWallet($reference);

        return redirect()->route('dashboard')->with('wallet_success', 'Wallet funded successfully!');
    }

    public function webhook(Request $request)
    {
        $signature = $request->header('x-paystack-signature');
        $payload   = $request->getContent();

        if (hash_hmac('sha512', $payload, $this->paystackSecret) !== $signature) {
            return response('Unauthorized', 401);
        }

        $event = json_decode($payload, true);

        if ($event['event'] === 'charge.success') {
            $this->creditWallet($event['data']['reference']);
        } elseif ($event['event'] === 'transfer.success') {
            $this->settleWithdrawal($event['data']['reference'], 'success');
        } elseif (in_array($event['event'], ['transfer.failed', 'transfer.reversed'], true)) {
            $this->settleWithdrawal($event['data']['reference'], 'failed');
        }

        return response('OK', 200);
    }

    // ── Bank account + withdrawals ──────────────────────────────────────

    public function banks()
    {
        $banks = \Illuminate\Support\Facades\Cache::remember('paystack_banks_ng', now()->addDay(), function () {
            $response = $this->paystackGet('/bank?country=nigeria&currency=NGN');
            return ($response && $response['status']) ? $response['data'] : [];
        });

        return response()->json($banks);
    }

    public function resolveAccount(Request $request)
    {
        $request->validate([
            'account_number' => 'required|digits:10',
            'bank_code' => 'required|string',
        ]);

        $response = $this->paystackGet('/bank/resolve?account_number=' . $request->account_number . '&bank_code=' . $request->bank_code);

        if (!$response || !$response['status']) {
            return response()->json(['success' => false, 'message' => $response['message'] ?? 'Could not verify this account. Check the details and try again.'], 422);
        }

        return response()->json(['success' => true, 'account_name' => $response['data']['account_name']]);
    }

    public function saveBankAccount(Request $request)
    {
        $request->validate([
            'account_number' => 'required|digits:10',
            'bank_code' => 'required|string',
            'bank_name' => 'required|string',
        ]);

        $resolved = $this->paystackGet('/bank/resolve?account_number=' . $request->account_number . '&bank_code=' . $request->bank_code);

        if (!$resolved || !$resolved['status']) {
            return response()->json(['success' => false, 'message' => 'Could not verify this account. Check the details and try again.'], 422);
        }

        $accountName = $resolved['data']['account_name'];
        $user = Auth::user();

        $recipient = $this->paystackPost('/transferrecipient', [
            'type' => 'nuban',
            'name' => $accountName,
            'account_number' => $request->account_number,
            'bank_code' => $request->bank_code,
            'currency' => 'NGN',
        ]);

        if (!$recipient || !$recipient['status']) {
            return response()->json(['success' => false, 'message' => $recipient['message'] ?? 'Could not link this bank account. Try again.'], 422);
        }

        $user->update([
            'bank_code' => $request->bank_code,
            'bank_name' => $request->bank_name,
            'account_number' => $request->account_number,
            'account_name' => $accountName,
            'paystack_recipient_code' => $recipient['data']['recipient_code'],
        ]);

        return response()->json(['success' => true, 'account_name' => $accountName]);
    }

    public function requestWithdrawal(Request $request)
    {
        $request->validate(['amount' => 'required|numeric|min:100']);

        $user = Auth::user();

        if (!$user->paystack_recipient_code) {
            return response()->json(['success' => false, 'message' => 'Add a bank account before requesting a withdrawal.'], 422);
        }

        if ($request->amount > $user->wallet_balance) {
            return response()->json(['success' => false, 'message' => 'Insufficient wallet balance.'], 422);
        }

        $reference = 'RQL-WD-' . strtoupper(uniqid());

        // Debit up front so the balance can't be spent twice while the transfer is
        // in flight; settleWithdrawal() re-credits if Paystack later reports failure.
        $newBalance = null;
        DB::transaction(function () use ($user, $request, $reference, &$newBalance) {
            $lockedUser = \App\Domains\Users\Models\User::lockForUpdate()->find($user->id);

            if ($request->amount > $lockedUser->wallet_balance) {
                abort(422, 'Insufficient wallet balance.');
            }

            $newBalance = $lockedUser->wallet_balance - $request->amount;
            $lockedUser->wallet_balance = $newBalance;
            $lockedUser->save();

            WalletTransaction::create([
                'user_id' => $user->id,
                'type' => 'debit',
                'amount' => $request->amount,
                'balance_after' => $newBalance,
                'reference' => $reference,
                'description' => 'Withdrawal to ' . $user->bank_name . ' ****' . substr($user->account_number, -4),
                'status' => 'pending',
            ]);
        });

        $transfer = $this->paystackPost('/transfer', [
            'source' => 'balance',
            'amount' => (int) ($request->amount * 100),
            'recipient' => $user->paystack_recipient_code,
            'reason' => 'ResQLink wallet withdrawal',
            'reference' => $reference,
        ]);

        if (!$transfer || !$transfer['status']) {
            $this->settleWithdrawal($reference, 'failed');
            return response()->json(['success' => false, 'message' => $transfer['message'] ?? 'Could not initiate the transfer. Your balance has been refunded.'], 422);
        }

        return response()->json(['success' => true, 'message' => 'Withdrawal initiated.']);
    }

    private function settleWithdrawal(string $reference, string $outcome): void
    {
        DB::transaction(function () use ($reference, $outcome) {
            $tx = WalletTransaction::where('reference', $reference)
                ->where('status', 'pending')
                ->lockForUpdate()
                ->first();

            if (!$tx) return; // already settled or not a withdrawal we know about

            if ($outcome === 'success') {
                $tx->status = 'success';
                $tx->save();
                return;
            }

            // Transfer failed/reversed on Paystack's side -- refund the wallet.
            $user = \App\Domains\Users\Models\User::lockForUpdate()->find($tx->user_id);
            $refundedBalance = $user->wallet_balance + $tx->amount;
            $user->wallet_balance = $refundedBalance;
            $user->save();

            $tx->status = 'failed';
            $tx->save();
        });
    }

    private function creditWallet(string $reference): void
    {
        DB::transaction(function () use ($reference) {
            $tx = WalletTransaction::where('reference', $reference)
                ->where('status', 'pending')
                ->lockForUpdate()
                ->first();

            if (!$tx) return; // already processed

            $user = \App\Domains\Users\Models\User::lockForUpdate()->find($tx->user_id);
            $newBalance = $user->wallet_balance + $tx->amount;

            $user->wallet_balance = $newBalance;
            $user->save();

            $tx->status       = 'success';
            $tx->balance_after = $newBalance;
            $tx->save();
        });
    }

    private function paystackPost(string $path, array $data): ?array
    {
        $ch = curl_init($this->paystackBase . $path);
        curl_setopt_array($ch, [
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => json_encode($data),
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER     => [
                'Authorization: Bearer ' . $this->paystackSecret,
                'Content-Type: application/json',
            ],
        ]);
        $result = curl_exec($ch);
        curl_close($ch);
        return $result ? json_decode($result, true) : null;
    }

    private function paystackGet(string $path): ?array
    {
        $ch = curl_init($this->paystackBase . $path);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER     => [
                'Authorization: Bearer ' . $this->paystackSecret,
            ],
        ]);
        $result = curl_exec($ch);
        curl_close($ch);
        return $result ? json_decode($result, true) : null;
    }
}
