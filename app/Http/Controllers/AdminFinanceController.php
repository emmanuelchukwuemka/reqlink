<?php

namespace App\Http\Controllers;

use App\Models\AdminActivityLog;
use App\Models\WalletTransaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AdminFinanceController extends Controller
{
    protected function ensureAdmin(): void
    {
        abort_unless(Auth::check() && Auth::user()->role === 'admin', 403);
    }

    public function flag(Request $request, $id)
    {
        $this->ensureAdmin();

        $request->validate(['flag_note' => 'nullable|string|max:500']);

        $transaction = WalletTransaction::findOrFail($id);
        $transaction->update([
            'is_flagged' => true,
            'flag_note' => $request->flag_note,
        ]);

        AdminActivityLog::record('transaction_flagged', "Flagged transaction {$transaction->reference}", $transaction);

        return redirect()->back()->with('success', 'Transaction flagged.');
    }

    public function unflag($id)
    {
        $this->ensureAdmin();

        $transaction = WalletTransaction::findOrFail($id);
        $transaction->update(['is_flagged' => false, 'flag_note' => null]);

        AdminActivityLog::record('transaction_unflagged', "Unflagged transaction {$transaction->reference}", $transaction);

        return redirect()->back()->with('success', 'Flag removed.');
    }

    public function export()
    {
        $this->ensureAdmin();

        $transactions = WalletTransaction::with('user')->latest()->get();

        return response()->streamDownload(function () use ($transactions) {
            $out = fopen('php://output', 'w');
            fputcsv($out, ['Reference', 'User', 'Type', 'Amount', 'Balance After', 'Status', 'Flagged', 'Description', 'Date']);
            foreach ($transactions as $t) {
                fputcsv($out, [
                    $t->reference,
                    $t->user->name ?? 'Unknown',
                    strtoupper($t->type),
                    number_format($t->amount, 2),
                    number_format($t->balance_after, 2),
                    strtoupper($t->status),
                    $t->is_flagged ? 'YES' : 'NO',
                    $t->description,
                    $t->created_at->format('Y-m-d H:i:s'),
                ]);
            }
            fclose($out);
        }, 'wallet-transactions.csv', ['Content-Type' => 'text/csv']);
    }
}
