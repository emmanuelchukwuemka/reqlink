<?php

namespace App\Http\Controllers;

use App\Domains\Emergencies\Models\EmergencyType;
use App\Models\AdminActivityLog;
use App\Models\Announcement;
use App\Models\Review;
use App\Models\SupportMessage;
use App\Models\WalletTransaction;
use Illuminate\Support\Facades\Auth;

class AdminToolsController extends Controller
{
    protected function ensureAdmin(): void
    {
        abort_unless(Auth::check() && Auth::user()->role === 'admin', 403);
    }

    public function index()
    {
        $this->ensureAdmin();

        $supportMessages = SupportMessage::with('user')->latest()->paginate(15, ['*'], 'support_page');
        $unreadSupportCount = SupportMessage::where('is_read', false)->count();

        $transactions = WalletTransaction::with('user')->latest()->paginate(20, ['*'], 'finance_page');
        $financeStats = [
            'total_credits' => (float) WalletTransaction::where('type', 'credit')->where('status', 'success')->sum('amount'),
            'total_debits' => (float) WalletTransaction::where('type', 'debit')->where('status', 'success')->sum('amount'),
            'flagged_count' => WalletTransaction::where('is_flagged', true)->count(),
        ];

        $reviews = Review::with(['user', 'responder.user'])->latest()->paginate(15, ['*'], 'reviews_page');

        $announcements = Announcement::with('admin')->latest()->get();

        $emergencyTypes = EmergencyType::withCount('emergencies')->orderBy('name')->get();

        $activityLogs = AdminActivityLog::with('admin')->latest()->paginate(25, ['*'], 'logs_page');

        return view('dashboards.admin_tools', compact(
            'supportMessages', 'unreadSupportCount', 'transactions', 'financeStats',
            'reviews', 'announcements', 'emergencyTypes', 'activityLogs'
        ));
    }
}
