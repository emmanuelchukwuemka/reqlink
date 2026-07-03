<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Global Incidents | ResQLink Admin</title>
    <link rel="manifest" href="/manifest.json">
    <link rel="stylesheet" href="/css/dashboard.css">
    <script src="https://unpkg.com/lucide@latest"></script>
    <style>
        .stats-grid-lg { display: grid; grid-template-columns: repeat(auto-fit, minmax(150px, 1fr)); gap: 14px; margin-bottom: 24px; }
        .stat-card-sm { background: var(--glass); border: 1px solid var(--glass-border); border-radius: 14px; padding: 16px 18px; }
        .stat-card-sm .stat-value { font-size: 1.7rem; font-weight: 900; }
        .stat-card-sm .stat-label { font-size: 0.7rem; color: var(--grey); text-transform: uppercase; letter-spacing: 1px; margin-top: 4px; }
        .stat-card-sm .stat-icon { width: 34px; height: 34px; border-radius: 9px; display: flex; align-items: center; justify-content: center; margin-bottom: 10px; }

        /* Status filter tabs */
        .status-tabs { display: flex; gap: 6px; flex-wrap: wrap; margin-bottom: 16px; }
        .stab { padding: 7px 16px; border-radius: 8px; border: 1px solid var(--glass-border); background: transparent; color: var(--grey); font-size: 0.75rem; font-weight: 700; cursor: pointer; text-decoration: none; text-transform: uppercase; letter-spacing: 0.5px; transition: all 0.2s; }
        .stab:hover { border-color: var(--red); color: var(--white); }
        .stab.active { background: var(--red); border-color: var(--red); color: #fff; }
        .stab-all.active    { background: var(--red);  border-color: var(--red);  color: #fff; }
        .stab-pending.active { background: rgba(245,158,11,0.2); border-color: #f59e0b; color: #f59e0b; }
        .stab-active-stat.active { background: rgba(34,197,94,0.12); border-color: #22c55e; color: #22c55e; }
        .stab-resolved.active { background: rgba(99,102,241,0.12); border-color: #6366f1; color: #6366f1; }
        .stab-cancelled.active { background: rgba(107,114,128,0.12); border-color: var(--grey); color: var(--grey); }

        /* Incident table */
        .inc-table { width: 100%; border-collapse: separate; border-spacing: 0 7px; }
        .inc-table th { text-align: left; padding: 10px 14px; color: var(--grey); font-size: 0.72rem; text-transform: uppercase; letter-spacing: 1px; }
        .inc-table tr { background: rgba(255,255,255,0.02); transition: background 0.2s; }
        .inc-table tr:hover { background: rgba(255,255,255,0.05); }
        .inc-table td { padding: 11px 14px; border-top: 1px solid var(--glass-border); border-bottom: 1px solid var(--glass-border); vertical-align: middle; }
        .inc-table td:first-child { border-left: 1px solid var(--glass-border); border-top-left-radius: 10px; border-bottom-left-radius: 10px; }
        .inc-table td:last-child { border-right: 1px solid var(--glass-border); border-top-right-radius: 10px; border-bottom-right-radius: 10px; }

        /* Status pills */
        .s-pill { padding: 3px 9px; border-radius: 100px; font-size: 0.68rem; font-weight: 800; text-transform: uppercase; letter-spacing: 0.5px; }
        .s-pending    { background: rgba(245,158,11,0.12); color: #f59e0b; }
        .s-dispatched { background: rgba(34,197,94,0.1);   color: #22c55e; }
        .s-enroute    { background: rgba(37,99,235,0.12);  color: #2563eb; }
        .s-arrived    { background: rgba(168,85,247,0.12); color: #a855f7; }
        .s-resolved   { background: rgba(99,102,241,0.12); color: #6366f1; }
        .s-cancelled  { background: rgba(107,114,128,0.1); color: var(--grey); }

        /* Status change form */
        .status-form { display: flex; gap: 5px; align-items: center; }
        .status-form select { background: var(--glass); border: 1px solid var(--glass-border); color: var(--white); padding: 4px 8px; border-radius: 7px; font-size: 0.7rem; outline: none; cursor: pointer; max-width: 110px; }
        .status-form button { background: rgba(255,255,255,0.06); border: 1px solid var(--glass-border); color: var(--white); padding: 4px 8px; border-radius: 7px; font-size: 0.7rem; font-weight: 700; cursor: pointer; transition: all 0.2s; }
        .status-form button:hover { background: var(--red); border-color: var(--red); }

        /* Priority badge */
        .pri-high   { color: var(--red); font-weight: 800; font-size: 0.7rem; }
        .pri-medium { color: #f59e0b;    font-weight: 700; font-size: 0.7rem; }
        .pri-low    { color: var(--grey); font-size: 0.7rem; }

        /* Search */
        .filter-bar { display: flex; gap: 10px; flex-wrap: wrap; margin-bottom: 14px; }
        .filter-bar input, .filter-bar select { background: var(--glass); border: 1px solid var(--glass-border); color: var(--white); padding: 9px 14px; border-radius: 10px; font-size: 0.82rem; outline: none; }
        .filter-bar input { flex: 1; min-width: 200px; }
        .filter-bar input::placeholder { color: var(--grey); }
        .filter-bar input:focus { border-color: var(--red); }
        :root.light-mode .filter-bar select option { background: #fff; color: #111; }

        /* Flash */
        .flash-success { background: rgba(34,197,94,0.1); border: 1px solid rgba(34,197,94,0.3); color: #22c55e; padding: 12px 18px; border-radius: 10px; margin-bottom: 20px; display: flex; align-items: center; gap: 10px; font-size: 0.85rem; font-weight: 600; }

        /* Pagination */
        .pagination-wrap { display: flex; justify-content: center; gap: 6px; padding: 20px 0 4px; flex-wrap: wrap; }
        .pagination-wrap a, .pagination-wrap span { padding: 7px 13px; border-radius: 8px; border: 1px solid var(--glass-border); color: var(--grey); font-size: 0.8rem; text-decoration: none; transition: all 0.2s; }
        .pagination-wrap a:hover { border-color: var(--red); color: var(--white); }
        .pagination-wrap span.active-page { background: var(--red); border-color: var(--red); color: #fff; font-weight: 700; }

        .theme-toggle { background: transparent; border: none; color: var(--grey); cursor: pointer; padding: 8px; border-radius: 50%; transition: all 0.3s; display: flex; align-items: center; justify-content: center; }
        .theme-toggle:hover { background: var(--glass); color: var(--white); }
    </style>
    <script src="/js/theme.js"></script>
</head>
<body class="dashboard-layout">

<div class="sidebar-overlay" id="sidebarOverlay"></div>

<aside class="sidebar">
    <div class="sidebar-header">
        <div class="auth-logo" style="margin-bottom: 0;">
            <img src="{{ asset('images/logo.png') }}" alt="ResQLink" style="height: 100px; width: auto; object-fit: contain;">
        </div>
        <div style="font-size: 0.6rem; color: var(--red); font-weight: 900; text-transform: uppercase; margin-top: 5px; letter-spacing: 2px;">Admin Portal</div>
    </div>
    <nav class="sidebar-nav">
        <a href="{{ route('dashboard') }}" class="nav-item"><i data-lucide="users"></i> User Management</a>
        <a href="{{ route('admin.command-center') }}" class="nav-item"><i data-lucide="shield-alert" style="color: var(--red);"></i> Command Center</a>
        <a href="{{ route('admin.incidents') }}" class="nav-item active"><i data-lucide="activity"></i> Global Incidents</a>
        <a href="{{ route('admin.agencies') }}" class="nav-item"><i data-lucide="building-2"></i> Agency Oversight</a>
        <a href="{{ route('admin.analytics') }}" class="nav-item"><i data-lucide="bar-chart-3"></i> System Analytics</a>
    </nav>
    <div class="sidebar-footer">
        <form action="{{ route('logout') }}" method="POST" id="logoutForm">
            @csrf
            <a href="#" onclick="document.getElementById('logoutForm').submit()" class="nav-item" style="color: var(--red);">
                <i data-lucide="log-out"></i> Logout
            </a>
        </form>
    </div>
</aside>

<main class="main-content">
    <header class="top-bar">
        <button class="hamburger-btn" id="hamburgerBtn" aria-label="Toggle Menu">
            <i data-lucide="menu"></i>
        </button>
        <div class="topbar-title">
            <h1 style="font-size: 1.4rem; font-weight: 800;">Global Incidents</h1>
            <p style="color: var(--grey); font-size: 0.85rem;">All emergency reports across the system</p>
        </div>
        <div style="display: flex; align-items: center; gap: 14px;">
            @include('partials.lang-switcher')
            <a href="{{ route('admin.command-center') }}" class="btn-primary" style="padding: 9px 18px; font-size: 0.8rem; display: flex; align-items: center; gap: 8px; text-decoration: none;">
                <i data-lucide="radar" style="width: 16px; height: 16px;"></i>
                LIVE COMMAND
            </a>
            <button id="themeToggle" class="theme-toggle" aria-label="Toggle Dark Mode">
                <i data-lucide="sun" id="themeIcon"></i>
            </button>
            <div class="user-profile">
                <div class="user-info">
                    <span>{{ Auth::user()->name }}</span>
                    <small>Administrator</small>
                </div>
                <div class="avatar" style="background: var(--red)">{{ substr(Auth::user()->name, 0, 1) }}</div>
            </div>
        </div>
    </header>

    @if(session('success'))
    <div class="flash-success"><i data-lucide="check-circle" style="width:16px;height:16px;flex-shrink:0;"></i> {{ session('success') }}</div>
    @endif

    <!-- Stats -->
    <div class="stats-grid-lg">
        <div class="stat-card-sm">
            <div class="stat-icon" style="background:rgba(255,255,255,0.05);color:var(--grey);">
                <i data-lucide="list" style="width:16px;height:16px;"></i>
            </div>
            <div class="stat-value">{{ number_format($stats['total']) }}</div>
            <div class="stat-label">Total All-Time</div>
        </div>
        <div class="stat-card-sm">
            <div class="stat-icon" style="background:rgba(229,9,20,0.1);color:var(--red);">
                <i data-lucide="siren" style="width:16px;height:16px;"></i>
            </div>
            <div class="stat-value" style="color:var(--red);">{{ $stats['active'] }}</div>
            <div class="stat-label">Active Now</div>
        </div>
        <div class="stat-card-sm">
            <div class="stat-icon" style="background:rgba(245,158,11,0.12);color:#f59e0b;">
                <i data-lucide="clock" style="width:16px;height:16px;"></i>
            </div>
            <div class="stat-value" style="color:#f59e0b;">{{ $stats['pending'] }}</div>
            <div class="stat-label">Pending Dispatch</div>
        </div>
        <div class="stat-card-sm">
            <div class="stat-icon" style="background:rgba(99,102,241,0.12);color:#6366f1;">
                <i data-lucide="check-circle-2" style="width:16px;height:16px;"></i>
            </div>
            <div class="stat-value" style="color:#6366f1;">{{ number_format($stats['resolved']) }}</div>
            <div class="stat-label">Resolved</div>
        </div>
        <div class="stat-card-sm">
            <div class="stat-icon" style="background:rgba(34,197,94,0.12);color:#22c55e;">
                <i data-lucide="calendar" style="width:16px;height:16px;"></i>
            </div>
            <div class="stat-value" style="color:#22c55e;">{{ $stats['today'] }}</div>
            <div class="stat-label">Today</div>
        </div>
        <div class="stat-card-sm">
            <div class="stat-icon" style="background:rgba(14,165,233,0.12);color:#0ea5e9;">
                <i data-lucide="timer" style="width:16px;height:16px;"></i>
            </div>
            <div class="stat-value" style="font-size:1.3rem;">
                {{ $stats['avg_response'] ? number_format($stats['avg_response'], 1) . 'm' : '—' }}
            </div>
            <div class="stat-label">Avg Response</div>
        </div>
    </div>

    <!-- Incident Table -->
    <div class="dash-card">
        <!-- Status filter tabs -->
        <div class="status-tabs">
            <a href="{{ route('admin.incidents') }}" class="stab stab-all {{ !request('status') ? 'active' : '' }}">All</a>
            <a href="{{ route('admin.incidents', ['status' => 'pending']) }}" class="stab stab-pending {{ request('status') === 'pending' ? 'active' : '' }}">Pending</a>
            <a href="{{ route('admin.incidents', ['status' => 'dispatched']) }}" class="stab {{ request('status') === 'dispatched' ? 'active' : '' }}" style="{{ request('status') === 'dispatched' ? 'background:rgba(34,197,94,0.12);border-color:#22c55e;color:#22c55e;' : '' }}">Dispatched</a>
            <a href="{{ route('admin.incidents', ['status' => 'enroute']) }}"    class="stab {{ request('status') === 'enroute'    ? 'active' : '' }}" style="{{ request('status') === 'enroute'    ? 'background:rgba(37,99,235,0.12);border-color:#2563eb;color:#2563eb;'  : '' }}">En Route</a>
            <a href="{{ route('admin.incidents', ['status' => 'arrived']) }}"   class="stab {{ request('status') === 'arrived'    ? 'active' : '' }}" style="{{ request('status') === 'arrived'    ? 'background:rgba(168,85,247,0.12);border-color:#a855f7;color:#a855f7;' : '' }}">Arrived</a>
            <a href="{{ route('admin.incidents', ['status' => 'resolved']) }}"  class="stab stab-resolved {{ request('status') === 'resolved'  ? 'active' : '' }}">Resolved</a>
            <a href="{{ route('admin.incidents', ['status' => 'cancelled']) }}" class="stab stab-cancelled {{ request('status') === 'cancelled' ? 'active' : '' }}">Cancelled</a>
        </div>

        <!-- Date filter -->
        <form method="GET" action="{{ route('admin.incidents') }}" class="filter-bar" style="margin-bottom:18px;">
            @if(request('status'))<input type="hidden" name="status" value="{{ request('status') }}">@endif
            <input type="date" name="date_from" value="{{ request('date_from') }}" placeholder="From date"
                style="background:var(--glass);border:1px solid var(--glass-border);color:var(--white);padding:8px 12px;border-radius:10px;font-size:0.82rem;outline:none;">
            <input type="date" name="date_to" value="{{ request('date_to') }}" placeholder="To date"
                style="background:var(--glass);border:1px solid var(--glass-border);color:var(--white);padding:8px 12px;border-radius:10px;font-size:0.82rem;outline:none;">
            <button type="submit" class="btn-primary" style="padding:8px 16px;font-size:0.8rem;">Filter</button>
            @if(request('date_from') || request('date_to'))
            <a href="{{ route('admin.incidents', request('status') ? ['status' => request('status')] : []) }}"
               style="padding:8px 14px;border:1px solid var(--glass-border);border-radius:10px;color:var(--grey);text-decoration:none;font-size:0.8rem;">
                Clear
            </a>
            @endif
        </form>

        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:12px;">
            <span style="font-size:0.8rem;color:var(--grey);">Showing {{ $emergencies->firstItem() ?? 0 }}–{{ $emergencies->lastItem() ?? 0 }} of {{ $emergencies->total() }} incidents</span>
        </div>

        <div class="table-scroll">
        <table class="inc-table" style="min-width: 900px;">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Patient</th>
                    <th>Status</th>
                    <th>Priority</th>
                    <th>Location / Address</th>
                    <th>Assigned Responder</th>
                    <th>Triggered</th>
                    <th>Time</th>
                    <th>Update Status</th>
                </tr>
            </thead>
            <tbody>
                @forelse($emergencies as $emergency)
                <tr>
                    <td style="font-size:0.75rem;color:var(--grey);font-weight:700;">#{{ $emergency->id }}</td>
                    <td>
                        <div style="font-weight:700;font-size:0.88rem;">{{ $emergency->user?->name ?? 'Unknown' }}</div>
                        @if($emergency->subtype)
                        <div style="font-size:0.7rem;color:var(--grey);">{{ $emergency->subtype }}</div>
                        @endif
                    </td>
                    <td>
                        <span class="s-pill s-{{ $emergency->status }}">{{ $emergency->status }}</span>
                    </td>
                    <td>
                        @if($emergency->priority === 'high' || $emergency->priority === 'critical')
                            <span class="pri-high">● {{ strtoupper($emergency->priority ?? 'HIGH') }}</span>
                        @elseif($emergency->priority === 'medium')
                            <span class="pri-medium">● {{ strtoupper($emergency->priority) }}</span>
                        @else
                            <span class="pri-low">● {{ strtoupper($emergency->priority ?? 'STD') }}</span>
                        @endif
                    </td>
                    <td>
                        @if($emergency->address)
                            <div style="font-size:0.78rem;max-width:180px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">{{ $emergency->address }}</div>
                        @elseif($emergency->latitude)
                            <div style="font-size:0.72rem;color:var(--grey);">{{ number_format($emergency->latitude, 4) }}, {{ number_format($emergency->longitude, 4) }}</div>
                        @else
                            <span style="color:var(--grey);font-size:0.75rem;">—</span>
                        @endif
                    </td>
                    <td>
                        @if($emergency->assignedResponder && $emergency->assignedResponder->user)
                            <div style="display:flex;align-items:center;gap:8px;">
                                <div style="width:26px;height:26px;border-radius:50%;background:rgba(34,197,94,0.12);display:flex;align-items:center;justify-content:center;font-size:0.7rem;font-weight:800;color:#22c55e;flex-shrink:0;">
                                    {{ substr($emergency->assignedResponder->user->name, 0, 1) }}
                                </div>
                                <div>
                                    <div style="font-size:0.8rem;font-weight:600;">{{ $emergency->assignedResponder->user->name }}</div>
                                    <div style="font-size:0.68rem;color:var(--grey);">{{ strtoupper($emergency->assignedResponder->responder_type ?? '') }}</div>
                                </div>
                            </div>
                        @else
                            <span style="color:var(--grey);font-size:0.75rem;">Unassigned</span>
                        @endif
                    </td>
                    <td style="font-size:0.72rem;color:var(--grey);">{{ $emergency->triggered_via ?? 'App' }}</td>
                    <td>
                        <div style="font-size:0.78rem;font-weight:600;">{{ $emergency->created_at->diffForHumans() }}</div>
                        <div style="font-size:0.68rem;color:var(--grey);">{{ $emergency->created_at->format('M d, H:i') }}</div>
                    </td>
                    <td>
                        <form action="{{ route('admin.incident.status', $emergency->id) }}" method="POST" class="status-form">
                            @csrf
                            <select name="status">
                                <option value="pending"    {{ $emergency->status === 'pending'    ? 'selected' : '' }}>Pending</option>
                                <option value="dispatched" {{ $emergency->status === 'dispatched' ? 'selected' : '' }}>Dispatched</option>
                                <option value="enroute"    {{ $emergency->status === 'enroute'    ? 'selected' : '' }}>En Route</option>
                                <option value="arrived"    {{ $emergency->status === 'arrived'    ? 'selected' : '' }}>Arrived</option>
                                <option value="resolved"   {{ $emergency->status === 'resolved'   ? 'selected' : '' }}>Resolved</option>
                                <option value="cancelled"  {{ $emergency->status === 'cancelled'  ? 'selected' : '' }}>Cancelled</option>
                            </select>
                            <button type="submit" title="Update status">✓</button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="9" style="text-align:center;padding:40px;color:var(--grey);opacity:0.7;">
                        No incidents found matching the current filters.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
        </div>

        <!-- Pagination -->
        @if($emergencies->hasPages())
        <div class="pagination-wrap">
            @if($emergencies->onFirstPage())
                <span style="opacity:0.4;">← Prev</span>
            @else
                <a href="{{ $emergencies->previousPageUrl() }}">← Prev</a>
            @endif

            @foreach($emergencies->getUrlRange(max(1, $emergencies->currentPage()-2), min($emergencies->lastPage(), $emergencies->currentPage()+2)) as $page => $url)
                @if($page == $emergencies->currentPage())
                    <span class="active-page">{{ $page }}</span>
                @else
                    <a href="{{ $url }}">{{ $page }}</a>
                @endif
            @endforeach

            @if($emergencies->hasMorePages())
                <a href="{{ $emergencies->nextPageUrl() }}">Next →</a>
            @else
                <span style="opacity:0.4;">Next →</span>
            @endif
        </div>
        @endif
    </div>
</main>

<script>
    lucide.createIcons();
    (function() {
        const btn = document.getElementById('hamburgerBtn');
        const sidebar = document.querySelector('.sidebar');
        const overlay = document.getElementById('sidebarOverlay');
        btn.addEventListener('click', () => { sidebar.classList.toggle('open'); overlay.classList.toggle('active'); });
        overlay.addEventListener('click', () => { sidebar.classList.remove('open'); overlay.classList.remove('active'); });
    })();
</script>
</body>
</html>
