<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use App\Domains\Emergencies\Models\Emergency;
use App\Domains\Responders\Models\Responder;

class AnalyticsController extends Controller
{
    public function index()
    {
        // Emergencies per day — last 30 days
        $per_day = Emergency::select(DB::raw('DATE(created_at) as date'), DB::raw('count(*) as count'))
            ->where('created_at', '>=', now()->subDays(29))
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        // Status distribution — array of {status, count}
        $by_status = Emergency::select('status', DB::raw('count(*) as count'))
            ->groupBy('status')
            ->get();

        // Average response time in minutes
        $avg_response = Emergency::whereNotNull('assigned_responder_id')
            ->whereIn('status', ['dispatched', 'enroute', 'arrived', 'resolved'])
            ->selectRaw('AVG(TIMESTAMPDIFF(SECOND, created_at, updated_at)/60) as avg_minutes')
            ->value('avg_minutes');

        // Top responders by resolved missions (via direct FK assigned_responder_id)
        $top_responders = Responder::with('user')
            ->withCount(['assignedEmergencies as resolved_count' => fn($q) => $q->where('status', 'resolved')])
            ->orderByDesc('resolved_count')
            ->limit(8)
            ->get()
            ->each(fn($r) => $r->name = $r->user->name ?? 'Unit #'.$r->id);

        // Totals
        $totals = [
            'total'             => Emergency::count(),
            'resolved'          => Emergency::where('status', 'resolved')->count(),
            'pending'           => Emergency::whereIn('status', ['pending', 'dispatched'])->count(),
            'avg_response_time' => $avg_response,
        ];

        return view('dashboards.analytics', compact('per_day', 'by_status', 'top_responders', 'totals'));
    }
}
