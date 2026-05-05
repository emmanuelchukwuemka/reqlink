<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Dashboard | ResQLink</title>
    <link rel="stylesheet" href="{{ asset('css/dashboard.css') }}">
    <script src="https://unpkg.com/lucide@latest"></script>
</head>
<body class="dashboard-layout">

<!-- SIDEBAR -->
<aside class="sidebar">
    <div class="sidebar-header">
        <div class="auth-logo" style="margin-bottom: 0;">
            <div class="logo-icon">R</div>
            Resq<span style="color:var(--red)">Link</span>
        </div>
    </div>
    
    <nav class="sidebar-nav">
        <a href="#" class="nav-item active"><i data-lucide="layout-grid"></i> Overview</a>
        <a href="#" class="nav-item"><i data-lucide="siren"></i> SOS Alerts</a>
        <a href="#" class="nav-item"><i data-lucide="hospital"></i> Hospitals</a>
        <a href="#" class="nav-item"><i data-lucide="shield"></i> Responders</a>
        <a href="#" class="nav-item"><i data-lucide="history"></i> Incident History</a>
        <a href="#" class="nav-item"><i data-lucide="settings"></i> Settings</a>
    </nav>

    <div class="sidebar-footer">
        <a href="{{ url('/') }}" class="nav-item" style="color: var(--red);"><i data-lucide="log-out"></i> Logout</a>
    </div>
</aside>

<!-- MAIN CONTENT -->
<main class="main-content">
    <header class="top-bar">
        <div>
            <h1 style="font-size: 1.5rem; font-weight: 800;">Command Center</h1>
            <p style="color: var(--grey); font-size: 0.9rem;">Welcome back, User</p>
        </div>
        
        <div class="user-profile">
            <span style="font-size: 0.85rem; font-weight: 600;">Safety Status: Secure</span>
            <div class="avatar-sm">U</div>
        </div>
    </header>

    <!-- SOS TRIGGER -->
    <div class="dash-card sos-trigger-card" style="margin-bottom: 24px;">
        <div class="sos-content">
            <div class="hero-badge" style="background: rgba(229, 9, 20, 0.1); border-color: rgba(229, 9, 20, 0.2); margin-bottom: 16px;">
                <span class="pulse-dot"></span>
                Emergency Panic Mode
            </div>
            <h2>Need Immediate Help?</h2>
            <p>Press the button below. Your location will be captured and the nearest emergency responders will be dispatched instantly.</p>
        </div>
        <div class="panic-btn" id="panicBtn">
            <span>SOS</span>
            <small>Press & Hold</small>
        </div>
    </div>

    <!-- DASHBOARD GRID -->
    <div class="dashboard-grid">
        <div class="dash-card">
            <h3><i data-lucide="map"></i> Real-time Network</h3>
            <div class="map-placeholder">
                <i data-lucide="map-pin" class="map-marker" style="top: 30%; left: 40%;"></i>
                <i data-lucide="hospital" class="map-marker" style="top: 60%; left: 70%; color: #22c55e;"></i>
                <i data-lucide="shield" class="map-marker" style="top: 20%; left: 80%; color: #3b82f6;"></i>
                <div style="position: absolute; bottom: 20px; left: 20px; background: rgba(0,0,0,0.8); padding: 10px; border-radius: 8px; font-size: 0.75rem; border: 1px solid var(--glass-border);">
                    <p><span style="color: var(--red)">●</span> Your Location</p>
                    <p><span style="color: #22c55e">●</span> Nearest Hospital (1.2km)</p>
                    <p><span style="color: #3b82f6">●</span> Active Patrol (400m)</p>
                </div>
            </div>
        </div>

        <div>
            <div class="stats-row">
                <div class="stat-box">
                    <h4>02</h4>
                    <p>Alerts</p>
                </div>
                <div class="stat-box">
                    <h4>05</h4>
                    <p>Nearby</p>
                </div>
                <div class="stat-box">
                    <h4>A+</h4>
                    <p>Rating</p>
                </div>
            </div>

            <div class="dash-card" style="min-height: 250px;">
                <h3><i data-lucide="activity"></i> Recent Activity</h3>
                <div class="history-list">
                    <div class="history-item">
                        <div class="history-info">
                            <i data-lucide="heart-pulse" style="color: var(--red)"></i>
                            <div>
                                <p style="font-size: 0.85rem; font-weight: 600;">Medical Assist</p>
                                <p style="font-size: 0.7rem; color: var(--grey);">Oct 12, 2025 • 2:45 PM</p>
                            </div>
                        </div>
                        <span class="status-badge status-resolved">Resolved</span>
                    </div>
                    <div class="history-item">
                        <div class="history-info">
                            <i data-lucide="shield-alert" style="color: #3b82f6"></i>
                            <div>
                                <p style="font-size: 0.85rem; font-weight: 600;">Security Alert</p>
                                <p style="font-size: 0.7rem; color: var(--grey);">Sept 28, 2025 • 11:20 PM</p>
                            </div>
                        </div>
                        <span class="status-badge status-resolved">Resolved</span>
                    </div>
                </div>
                <a href="#" style="display: block; text-align: center; margin-top: 20px; font-size: 0.85rem; color: var(--red); font-weight: 600;">View Full History</a>
            </div>
        </div>
    </div>
</main>

<script>
    lucide.createIcons();
    
    // Panic Button Interaction
    const panicBtn = document.getElementById('panicBtn');
    let pressTimer;

    panicBtn.addEventListener('mousedown', () => {
        panicBtn.style.transform = 'scale(0.9)';
        pressTimer = setTimeout(() => {
            alert('🚨 EMERGENCY ALERT TRIGGERED! Connecting to responders...');
            panicBtn.style.transform = 'scale(1)';
        }, 1500);
    });

    panicBtn.addEventListener('mouseup', () => {
        clearTimeout(pressTimer);
        panicBtn.style.transform = 'scale(1)';
    });

    panicBtn.addEventListener('mouseleave', () => {
        clearTimeout(pressTimer);
        panicBtn.style.transform = 'scale(1)';
    });
</script>
</body>
</html>
