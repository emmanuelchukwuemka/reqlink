<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Analytics | ResQLink Admin</title>
    <link rel="stylesheet" href="/css/dashboard.css">
    <script src="https://unpkg.com/lucide@latest"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
    <style>
        .analytics-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 24px; }
        .analytics-grid-3 { display: grid; grid-template-columns: 2fr 1fr; gap: 24px; }
        @media (max-width: 900px) { .analytics-grid, .analytics-grid-3 { grid-template-columns: 1fr; } }
        .stat-hero { text-align: center; padding: 28px 20px; }
        .stat-hero h2 { font-size: 2.8rem; font-weight: 900; margin: 0; color: var(--red); }
        .stat-hero p { color: var(--grey); font-size: 0.85rem; margin: 6px 0 0; text-transform: uppercase; letter-spacing: 1px; }
        .responder-row { display: flex; justify-content: space-between; align-items: center; padding: 12px 0; border-bottom: 1px solid var(--glass-border); }
        .responder-row:last-child { border-bottom: none; }
        .bar-fill { height: 8px; background: var(--red); border-radius: 4px; transition: width 0.6s ease; }
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
        <a href="#" class="nav-item"><i data-lucide="activity"></i> Global Incidents</a>
        <a href="#" class="nav-item"><i data-lucide="building-2"></i> Agency Oversight</a>
        <a href="{{ route('admin.analytics') }}" class="nav-item active"><i data-lucide="bar-chart-3"></i> System Analytics</a>
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
            <h1 style="font-size: 1.5rem; font-weight: 800;">System Analytics</h1>
            <p style="color: var(--grey); font-size: 0.9rem;">Last 30 days performance overview</p>
        </div>
        <div style="display: flex; align-items: center; gap: 16px;">
            @include('partials.lang-switcher')
            <button id="themeToggle" class="theme-toggle" aria-label="Toggle Dark Mode">
                <i data-lucide="sun" id="themeIcon"></i>
            </button>
            <div class="user-profile">
                <div class="user-info">
                    <span>{{ Auth::user()->name }}</span>
                    <small>System Administrator</small>
                </div>
                <div class="avatar" style="background: var(--red)">{{ substr(Auth::user()->name, 0, 1) }}</div>
            </div>
        </div>
    </header>

    <!-- KPI CARDS -->
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px; margin-bottom: 28px;">
        <div class="dash-card stat-hero">
            <h2>{{ $totals['total'] ?? 0 }}</h2>
            <p>Total Emergencies</p>
        </div>
        <div class="dash-card stat-hero">
            <h2 style="color: #22c55e;">{{ $totals['resolved'] ?? 0 }}</h2>
            <p>Resolved</p>
        </div>
        <div class="dash-card stat-hero">
            <h2 style="color: #f59e0b;">{{ $totals['avg_response_time'] ? round($totals['avg_response_time'], 1) . 'm' : 'N/A' }}</h2>
            <p>Avg Response Time</p>
        </div>
        <div class="dash-card stat-hero">
            <h2 style="color: #3b82f6;">{{ $totals['pending'] ?? 0 }}</h2>
            <p>Active Now</p>
        </div>
    </div>

    <!-- CHARTS ROW 1 -->
    <div class="analytics-grid-3" style="margin-bottom: 28px;">
        <!-- Daily Emergencies Line Chart -->
        <div class="dash-card">
            <h3 style="margin-bottom: 20px;"><i data-lucide="trending-up"></i> Daily Emergency Volume (30 days)</h3>
            <canvas id="dailyChart" height="120"></canvas>
        </div>

        <!-- Status Donut -->
        <div class="dash-card" style="display: flex; flex-direction: column; align-items: center;">
            <h3 style="margin-bottom: 20px; align-self: flex-start;"><i data-lucide="pie-chart"></i> By Status</h3>
            <canvas id="statusChart" width="220" height="220" style="max-width: 220px;"></canvas>
            <div style="margin-top: 16px; width: 100%;" id="statusLegend"></div>
        </div>
    </div>

    <!-- CHARTS ROW 2 -->
    <div class="analytics-grid" style="margin-bottom: 28px;">
        <!-- Top Responders -->
        <div class="dash-card">
            <h3 style="margin-bottom: 20px;"><i data-lucide="award"></i> Top Responders</h3>
            @forelse($top_responders as $index => $r)
            @php $max = $top_responders[0]->resolved_count ?? 1; $pct = $max > 0 ? ($r->resolved_count / $max * 100) : 0; @endphp
            <div class="responder-row">
                <div style="display: flex; align-items: center; gap: 12px; flex: 1; min-width: 0;">
                    <div style="width: 32px; height: 32px; background: {{ ['#e50914','#f59e0b','#3b82f6'][$index] ?? '#6b7280' }}22; border-radius: 8px; display: flex; align-items: center; justify-content: center; color: {{ ['#e50914','#f59e0b','#3b82f6'][$index] ?? '#6b7280' }}; font-weight: 900; font-size: 0.8rem; flex-shrink: 0;">{{ $index + 1 }}</div>
                    <div style="flex: 1; min-width: 0;">
                        <p style="margin: 0; font-weight: 700; font-size: 0.9rem; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">{{ $r->name ?? 'Unknown' }}</p>
                        <div style="background: var(--glass-border); border-radius: 4px; height: 8px; margin-top: 4px;">
                            <div class="bar-fill" style="width: {{ $pct }}%;"></div>
                        </div>
                    </div>
                </div>
                <div style="margin-left: 12px; text-align: right; flex-shrink: 0;">
                    <span style="font-size: 1.1rem; font-weight: 900; color: var(--red);">{{ $r->resolved_count }}</span>
                    <p style="margin: 0; font-size: 0.7rem; color: var(--grey);">missions</p>
                </div>
            </div>
            @empty
            <p style="text-align: center; color: var(--grey); padding: 30px 0;">No resolved missions yet.</p>
            @endforelse
        </div>

        <!-- Emergency by Type / hour heatmap placeholder -->
        <div class="dash-card">
            <h3 style="margin-bottom: 20px;"><i data-lucide="bar-chart-2"></i> Emergency Type Breakdown</h3>
            <canvas id="typeChart" height="220"></canvas>
        </div>
    </div>

</main>

<script>
    lucide.createIcons();

    // Sidebar toggle
    document.getElementById('hamburgerBtn').addEventListener('click', () => {
        document.querySelector('.sidebar').classList.toggle('open');
        document.getElementById('sidebarOverlay').classList.toggle('active');
    });
    document.getElementById('sidebarOverlay').addEventListener('click', () => {
        document.querySelector('.sidebar').classList.remove('open');
        document.getElementById('sidebarOverlay').classList.remove('active');
    });

    const isLight = () => document.documentElement.classList.contains('light-mode');
    const gridColor = () => isLight() ? 'rgba(0,0,0,0.08)' : 'rgba(255,255,255,0.08)';
    const textColor = () => isLight() ? '#111' : '#ccc';

    // Daily chart data
    const perDay = @json($per_day);
    const labels = perDay.map(d => {
        const dt = new Date(d.date);
        return dt.toLocaleDateString('en-NG', { month: 'short', day: 'numeric' });
    });
    const counts = perDay.map(d => d.count);

    const dailyCtx = document.getElementById('dailyChart').getContext('2d');
    new Chart(dailyCtx, {
        type: 'line',
        data: {
            labels,
            datasets: [{
                label: 'Emergencies',
                data: counts,
                borderColor: '#e50914',
                backgroundColor: 'rgba(229,9,20,0.12)',
                borderWidth: 2,
                fill: true,
                tension: 0.4,
                pointBackgroundColor: '#e50914',
                pointRadius: 3,
            }]
        },
        options: {
            responsive: true,
            plugins: { legend: { display: false } },
            scales: {
                x: { grid: { color: gridColor() }, ticks: { color: textColor(), maxTicksLimit: 8, font: { size: 11 } } },
                y: { grid: { color: gridColor() }, ticks: { color: textColor(), stepSize: 1 }, beginAtZero: true }
            }
        }
    });

    // Status donut
    const byStatus = @json($by_status);
    const statusColors = { pending: '#f59e0b', dispatched: '#3b82f6', resolved: '#22c55e', cancelled: '#6b7280' };
    const statusLabels = byStatus.map(s => s.status);
    const statusData = byStatus.map(s => s.count);
    const statusBgColors = statusLabels.map(l => statusColors[l] || '#6b7280');

    const statusCtx = document.getElementById('statusChart').getContext('2d');
    new Chart(statusCtx, {
        type: 'doughnut',
        data: {
            labels: statusLabels,
            datasets: [{
                data: statusData,
                backgroundColor: statusBgColors,
                borderWidth: 0,
                hoverOffset: 8,
            }]
        },
        options: {
            cutout: '68%',
            plugins: {
                legend: { display: false },
                tooltip: { callbacks: { label: ctx => ` ${ctx.label}: ${ctx.parsed}` } }
            }
        }
    });

    // Status legend
    const legendEl = document.getElementById('statusLegend');
    byStatus.forEach((s, i) => {
        legendEl.innerHTML += `<div style="display:flex;justify-content:space-between;align-items:center;padding:5px 0;font-size:0.82rem;">
            <span style="display:flex;align-items:center;gap:8px;"><span style="width:10px;height:10px;border-radius:50%;background:${statusBgColors[i]};display:inline-block;"></span>${s.status.charAt(0).toUpperCase()+s.status.slice(1)}</span>
            <strong>${s.count}</strong>
        </div>`;
    });

    // Type bar (placeholder — uses status data as proxy)
    const typeCtx = document.getElementById('typeChart').getContext('2d');
    new Chart(typeCtx, {
        type: 'bar',
        data: {
            labels: statusLabels.map(l => l.charAt(0).toUpperCase()+l.slice(1)),
            datasets: [{
                label: 'Count',
                data: statusData,
                backgroundColor: statusBgColors,
                borderRadius: 6,
                borderWidth: 0,
            }]
        },
        options: {
            responsive: true,
            plugins: { legend: { display: false } },
            scales: {
                x: { grid: { display: false }, ticks: { color: textColor() } },
                y: { grid: { color: gridColor() }, ticks: { color: textColor() }, beginAtZero: true }
            }
        }
    });
</script>
</body>
</html>
