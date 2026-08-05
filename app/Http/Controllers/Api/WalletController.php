<?php

namespace App\Http\Controllers\Api;

use App\Domains\Users\Models\User;
use App\Http\Controllers\Controller;
use App\Models\WalletTransaction;
use App\Services\PaystackService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class WalletController extends Controller
{
    protected PaystackService $paystack;

    public function __construct(PaystackService $paystack)
    {
        $this->paystack = $paystack;
    }

    /**
     * Returns the Paystack authorization_url instead of redirecting — the
     * mobile app opens it in a WebView and watches for the callback-mobile
     * URL. The webhook (routes/web.php, public, signature-verified) remains
     * the only thing that actually credits the wallet.
     */
    public function initiate(Request $request)
    {
        $request->validate(['amount' => 'required|numeric|min:100']);

        $user = Auth::user();
        $amountKobo = (int) ($request->amount * 100);
        $reference = 'RQL-' . strtoupper(uniqid());

        WalletTransaction::create([
            'user_id' => $user->id,
            'type' => 'credit',
            'amount' => $request->amount,
            'balance_after' => $user->wallet_balance + $request->amount,
            'reference' => $reference,
            'description' => 'Wallet top-up via Paystack',
            'status' => 'pending',
        ]);

        $response = $this->paystack->post('/transaction/initialize', [
            'email' => $user->email ?? $user->phone . '@resqlink.app',
            'amount' => $amountKobo,
            'reference' => $reference,
            'callback_url' => route('wallet.callback-mobile'),
            'metadata' => ['user_id' => $user->id],
        ]);

        if (!$response || !$response['status']) {
            return response()->json(['success' => false, 'message' => 'Could not connect to payment gateway. Try again.'], 502);
        }

        return response()->json([
            'success' => true,
            'authorization_url' => $response['data']['authorization_url'],
            'reference' => $reference,
        ]);
    }

    /**
     * The mobile app polls this after the WebView checkout closes, since it
     * has no other way to know whether the webhook has landed yet.
     */
    public function transactionStatus($reference)
    {
        $transaction = WalletTransaction::where('reference', $reference)
            ->where('user_id', Auth::id())
            ->firstOrFail();

        return response()->json([
            'status' => $transaction->status,
            'amount' => $transaction->amount,
        ]);
    }

    public function transactions(Request $request)
    {
        $transactions = WalletTransaction::where('user_id', Auth::id())
            ->latest()
            ->paginate(20);

        return response()->json($transactions);
    }

    public function banks()
    {
        $banks = Cache::remember('paystack_banks_ng', now()->addDay(), function () {
            $response = $this->paystack->get('/bank?country=nigeria&currency=NGN');
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

        $response = $this->paystack->get('/bank/resolve?account_number=' . $request->account_number . '&bank_code=' . $request->bank_code);

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

        $resolved = $this->paystack->get('/bank/resolve?account_number=' . $request->account_number . '&bank_code=' . $request->bank_code);

        if (!$resolved || !$resolved['status']) {
            return response()->json(['success' => false, 'message' => 'Could not verify this account. Check the details and try again.'], 422);
        }

        $accountName = $resolved['data']['account_name'];
        $user = Auth::user();

        $recipient = $this->paystack->post('/transferrecipient', [
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

        $newBalance = null;
        DB::transaction(function () use ($user, $request, $reference, &$newBalance) {
            $lockedUser = User::lockForUpdate()->find($user->id);

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

        $transfer = $this->paystack->post('/transfer', [
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

            if (!$tx) return;

            if ($outcome === 'success') {
                $tx->status = 'success';
                $tx->save();
                return;
            }

            $user = User::lockForUpdate()->find($tx->user_id);
            $refundedBalance = $user->wallet_balance + $tx->amount;
            $user->wallet_balance = $refundedBalance;
            $user->save();

            $tx->status = 'failed';
            $tx->save();
        });
    }
}
