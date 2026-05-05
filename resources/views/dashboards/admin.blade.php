<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Government Oversight | ResQLink</title>
    <link rel="stylesheet" href="{{ asset('css/dashboard.css') }}">
    <script src="https://unpkg.com/lucide@latest"></script>
</head>
<body class="dashboard-layout">

<aside class="sidebar">
    <div class="sidebar-header">
        <div class="auth-logo" style="margin-bottom: 0;">
            <div class="logo-icon">R</div>
            Resq<span style="color:var(--red)">Link</span>
        </div>
        <div style="font-size: 0.65rem; color: var(--red); font-weight: 800; letter-spacing: 1px; margin-top: 5px;">GOVERNMENT PORTAL</div>
    </div>
    
    <nav class="sidebar-nav">
        <a href="#" class="nav-item active"><i data-lucide="bar-chart-3"></i> Oversight</a>
        <a href="#" class="nav-item"><i data-lucide="landmark"></i> Agencies</a>
        <a href="#" class="nav-item"><i data-lucide="map-pinned"></i> National View</a>
        <a href="#" class="nav-item"><i data-lucide="file-text"></i> Policy & Reports</a>
        <a href="#" class="nav-item"><i data-lucide="shield-check"></i> Verification</a>
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
        <div>
            <h1 style="font-size: 1.5rem; font-weight: 800;">National Oversight</h1>
            <p style="color: var(--grey); font-size: 0.9rem;">Administrator: {{ Auth::user()->name }}</p>
        </div>
        
        <div class="user-profile">
            <span style="font-size: 0.85rem; font-weight: 600;">System Health: Optimal</span>
            <div class="avatar-sm" style="background: #3b82f6;">{{ substr(Auth::user()->name, 0, 1) }}</div>
        </div>
    </header>

    <div class="stats-row" style="margin-bottom: 24px;">
        <div class="stat-box">
            <h4>142</h4>
            <p>Active Nodes</p>
        </div>
        <div class="stat-box">
            <h4>1,024</h4>
            <p>Total Rescues</p>
        </div>
        <div class="stat-box">
            <h4 style="color: #22c55e;">98%</h4>
            <p>Response Rate</p>
        </div>
        <div class="stat-box">
            <h4 style="color: var(--red);">04:12</h4>
            <p>Avg Response</p>
        </div>
    </div>

    <div class="dashboard-grid">
        <div class="dash-card">
            <h3><i data-lucide="map"></i> National Live Feed</h3>
            <div class="map-placeholder" style="height: 400px;">
                <div style="position: absolute; top: 10px; right: 10px; background: rgba(0,0,0,0.8); padding: 10px; border-radius: 8px; border: 1px solid var(--glass-border); font-size: 0.7rem;">
                    <p><span style="color: #ef4444">●</span> Critical Event</p>
                    <p><span style="color: #3b82f6">●</span> Active Agency</p>
                </div>
            </div>
        </div>

        <div class="dash-card">
            <h3><i data-lucide="shield-check"></i> Pending Verifications</h3>
            <div class="history-list">
                <div class="history-item">
                    <div class="history-info">
                        <i data-lucide="hospital"></i>
                        <div>
                            <p style="font-size: 0.85rem; font-weight: 600;">City Hope Hospital</p>
                            <p style="font-size: 0.7rem; color: var(--grey);">New Registration Request</p>
                        </div>
                    </div>
                    <button style="background: transparent; color: #22c55e; border: 1px solid #22c55e; padding: 4px 12px; border-radius: 4px; font-size: 0.7rem; font-weight: 700; cursor: pointer;">Approve</button>
                </div>
            </div>
        </div>
    </div>
</main>

<script>
    lucide.createIcons();
</script>
</body>
</html>
