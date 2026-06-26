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
        }

        return response('OK', 200);
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
