<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Mission Control | ResQLink</title>
    <link rel="stylesheet" href="{{ asset('css/dashboard.css') }}">
    <script src="https://unpkg.com/lucide@latest"></script>
    <style>
        .mission-grid { display: grid; grid-template-columns: 1fr 350px; gap: 24px; }
        .alert-item { 
            background: rgba(229, 9, 20, 0.05); 
            border-left: 4px solid var(--red);
            padding: 16px;
            border-radius: 8px;
            margin-bottom: 16px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .responder-badge {
            background: var(--red);
            color: white;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 0.7rem;
            font-weight: 800;
            text-transform: uppercase;
        }
    </style>
</head>
<body class="dashboard-layout">

<aside class="sidebar">
    <div class="sidebar-header">
        <div class="auth-logo" style="margin-bottom: 0;">
            <div class="logo-icon">R</div>
            Resq<span style="color:var(--red)">Link</span>
        </div>
        <div class="responder-badge" style="margin-top: 10px;">{{ strtoupper(Auth::user()->role) }} UNIT</div>
    </div>
    
    <nav class="sidebar-nav">
        <a href="#" class="nav-item active"><i data-lucide="layout-dashboard"></i> Missions</a>
        <a href="#" class="nav-item"><i data-lucide="map"></i> Live Map</a>
        <a href="#" class="nav-item"><i data-lucide="users"></i> Team</a>
        <a href="#" class="nav-item"><i data-lucide="history"></i> Archive</a>
        <a href="#" class="nav-item"><i data-lucide="settings"></i> System</a>
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
            <h1 style="font-size: 1.5rem; font-weight: 800;">Mission Control</h1>
            <p style="color: var(--grey); font-size: 0.9rem;">Responding Unit: {{ Auth::user()->name }}</p>
        </div>
        
        <div class="user-profile">
            <span style="font-size: 0.85rem; font-weight: 600; color: #22c55e;">Status: On Duty</span>
            <div class="avatar-sm" style="background: var(--red);">{{ substr(Auth::user()->name, 0, 1) }}</div>
        </div>
    </header>

    <div class="mission-grid">
        <div class="dash-card">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
                <h3><i data-lucide="siren" style="color: var(--red);"></i> Active Emergencies</h3>
                <span style="font-size: 0.8rem; color: var(--red); font-weight: 700; background: rgba(229, 9, 20, 0.1); padding: 4px 10px; border-radius: 4px;">LIVE UPDATES</span>
            </div>

            <div class="history-list" id="activeMissions">
                <!-- Sample Active Mission -->
                <div class="alert-item">
                    <div style="display: flex; gap: 16px; align-items: center;">
                        <div style="width: 40px; height: 40px; background: rgba(229, 9, 20, 0.1); border-radius: 50%; display: flex; align-items: center; justify-content: center; color: var(--red);">
                            <i data-lucide="heart-pulse"></i>
                        </div>
                        <div>
                            <p style="font-weight: 700; margin: 0;">Critical Medical Alert</p>
                            <p style="font-size: 0.75rem; color: var(--grey); margin: 0;">Location: 2.4km away • Ikoyi, Lagos</p>
                        </div>
                    </div>
                    <button class="btn-primary" style="padding: 8px 16px; font-size: 0.8rem;">Accept Mission</button>
                </div>

                <div class="alert-item" style="background: rgba(255, 255, 255, 0.02); border-left-color: var(--grey);">
                    <div style="display: flex; gap: 16px; align-items: center;">
                        <div style="width: 40px; height: 40px; background: rgba(255, 255, 255, 0.05); border-radius: 50%; display: flex; align-items: center; justify-content: center; color: var(--grey);">
                            <i data-lucide="shield"></i>
                        </div>
                        <div>
                            <p style="font-weight: 700; margin: 0; color: var(--grey);">Security Ping</p>
                            <p style="font-size: 0.75rem; color: var(--grey); margin: 0;">Location: 0.8km away • Victoria Island</p>
                        </div>
                    </div>
                    <span style="font-size: 0.75rem; color: var(--grey); font-weight: 700;">Waitlisted</span>
                </div>
            </div>
        </div>

        <div>
            <div class="dash-card" style="margin-bottom: 24px;">
                <h3><i data-lucide="activity"></i> Station Stats</h3>
                <div class="stats-row" style="margin-top: 15px;">
                    <div class="stat-box">
                        <h4 style="color: var(--red);">12</h4>
                        <p>Saved</p>
                    </div>
                    <div class="stat-box">
                        <h4>04</h4>
                        <p>Units</p>
                    </div>
                </div>
            </div>

            <div class="dash-card">
                <h3><i data-lucide="map-pin"></i> Coverage Area</h3>
                <div class="map-placeholder" style="height: 250px;">
                     <div style="position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); text-align: center;">
                         <i data-lucide="radar" style="width: 48px; height: 48px; color: var(--red); opacity: 0.3;"></i>
                         <p style="font-size: 0.7rem; color: var(--grey); margin-top: 10px;">Monitoring coverage...</p>
                     </div>
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
