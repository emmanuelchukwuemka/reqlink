<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Mission Control | ResQLink</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
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
        .theme-toggle { background: transparent; border: none; color: var(--grey); cursor: pointer; padding: 8px; border-radius: 50%; transition: all 0.3s; display: flex; align-items: center; justify-content: center; }
        .theme-toggle:hover { background: var(--glass); color: var(--white); }
        :root.light-mode .theme-toggle:hover { background: rgba(0,0,0,0.05); color: var(--black); }

        /* Duty Toggle */
        .duty-status-container { display: flex; align-items: center; gap: 12px; background: var(--glass); padding: 6px 16px; border-radius: 100px; border: 1px solid var(--glass-border); }
        .duty-toggle { position: relative; width: 44px; height: 22px; cursor: pointer; }
        .duty-toggle input { opacity: 0; width: 0; height: 0; }
        .duty-slider { position: absolute; cursor: pointer; top: 0; left: 0; right: 0; bottom: 0; background-color: var(--grey); transition: .4s; border-radius: 34px; }
        .duty-slider:before { position: absolute; content: ""; height: 16px; width: 16px; left: 3px; bottom: 3px; background-color: white; transition: .4s; border-radius: 50%; }
        input:checked + .duty-slider { background-color: #22c55e; }
        input:checked + .duty-slider:before { transform: translateX(22px); }
        .duty-label { font-size: 0.75rem; font-weight: 800; text-transform: uppercase; letter-spacing: 1px; color: var(--grey); }
        input:checked ~ .duty-label-on { color: #22c55e; }
        input:not(:checked) ~ .duty-label-off { color: var(--red); }
    </style>
    <script src="{{ asset('js/theme.js') }}"></script>
</head>
<body class="dashboard-layout">

<aside class="sidebar">
    <div class="sidebar-header">
        <div class="auth-logo" style="margin-bottom: 0;">
            <img src="{{ asset('images/logo.png') }}" alt="ResQLink" style="height: 50px; width: auto; object-fit: contain;">
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
        
        <div style="display: flex; align-items: center; gap: 20px;">
            <!-- Duty Toggle -->
            <div class="duty-status-container">
                <span class="duty-label duty-label-off" id="dutyText">OFF DUTY</span>
                <label class="duty-toggle">
                    <input type="checkbox" id="dutySwitch" {{ $responder && $responder->is_on_duty ? 'checked' : '' }} onchange="toggleDuty(this)">
                    <span class="duty-slider"></span>
                </label>
                <span class="duty-label duty-label-on">ON DUTY</span>
            </div>

            <button id="themeToggle" class="theme-toggle" aria-label="Toggle Dark Mode">
                <i data-lucide="sun" id="themeIcon"></i>
            </button>
            <div class="user-profile">
                <div class="user-info">
                    <span>{{ Auth::user()->name }}</span>
                    <small>Unit: {{ ucfirst(Auth::user()->role) }}</small>
                </div>
                <div class="avatar" style="background: #22c55e">{{ substr(Auth::user()->name, 0, 1) }}</div>
            </div>
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

<!-- EMERGENCY ALERT MODAL -->
<div id="emergencyModal" style="display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.9); z-index: 1000; align-items: center; justify-content: center; backdrop-filter: blur(10px);">
    <div style="background: #0a0a0a; border: 1px solid var(--red); width: 100%; max-width: 500px; border-radius: 20px; padding: 40px; text-align: center; box-shadow: 0 0 50px rgba(229, 9, 20, 0.3); animation: pulse-red 2s infinite;">
        <div style="width: 80px; height: 80px; background: rgba(229, 9, 20, 0.1); border-radius: 50%; display: flex; align-items: center; justify-content: center; color: var(--red); margin: 0 auto 24px;">
            <i data-lucide="siren" style="width: 48px; height: 48px;"></i>
        </div>
        <h2 style="font-size: 2rem; font-weight: 900; margin-bottom: 10px; color: #fff;">CRITICAL ALERT</h2>
        <p id="alertUser" style="font-size: 1.1rem; color: var(--grey); margin-bottom: 5px;">Patient: John Doe</p>
        <p id="alertLoc" style="font-size: 0.9rem; color: var(--red); font-weight: 700; margin-bottom: 30px;">LOCATION: 1.2km away</p>
        
        <div style="background: rgba(255,255,255,0.03); border-radius: 12px; padding: 20px; margin-bottom: 30px; text-align: left;">
            <p style="font-size: 0.75rem; color: var(--grey); margin-bottom: 5px; text-transform: uppercase;">Medical ID Summary</p>
            <p id="alertMedical" style="font-weight: 600;">Blood: O+ | Allergies: None | Asthma</p>
        </div>

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
            <button onclick="closeAlert()" style="background: rgba(255,255,255,0.05); color: #fff; border: 1px solid rgba(255,255,255,0.1); padding: 16px; border-radius: 12px; font-weight: 700; cursor: pointer;">Decline</button>
            <div style="display: flex; flex-direction: column; gap: 10px;">
                <button onclick="acceptMission()" style="background: var(--red); color: #fff; border: none; padding: 16px; border-radius: 12px; font-weight: 700; cursor: pointer; box-shadow: 0 10px 20px rgba(229, 9, 20, 0.3); width: 100%;">Accept Mission</button>
                <a id="navLink" href="#" target="_blank" style="display: none; background: #2563eb; color: #fff; text-decoration: none; padding: 12px; border-radius: 12px; font-weight: 700; font-size: 0.8rem; align-items: center; justify-content: center; gap: 8px;">
                    <i data-lucide="navigation"></i> Open Navigation
                </a>
            </div>
        </div>
    </div>
</div>

<style>
    @keyframes pulse-red {
        0% { box-shadow: 0 0 0 0 rgba(229, 9, 20, 0.4); }
        70% { box-shadow: 0 0 0 20px rgba(229, 9, 20, 0); }
        100% { box-shadow: 0 0 0 0 rgba(229, 9, 20, 0); }
    }
</style>

<script>
    lucide.createIcons();

    let currentAlertId = null;
    const siren = new Audio('https://assets.mixkit.co/active_storage/sfx/2568/2568-preview.mp3');
    siren.loop = true;

    function pollAlerts() {
        if (!document.getElementById('dutySwitch').checked) return;

        fetch('{{ route("responder.alerts") }}')
            .then(res => res.json())
            .then(data => {
                if (data.length > 0) {
                    const alert = data[0];
                    if (currentAlertId !== alert.uuid) {
                        showEmergency(alert);
                    }
                }
            });
    }

    function showEmergency(alert) {
        currentAlertId = alert.uuid;
        document.getElementById('alertUser').textContent = `Patient: ${alert.user.name}`;
        document.getElementById('alertLoc').textContent = `LOCATION: ${alert.latitude}, ${alert.longitude}`;
        document.getElementById('alertMedical').textContent = `Blood: ${alert.user.blood_group || 'N/A'} | Allergies: ${alert.user.allergies || 'None'}`;
        
        // Navigation Link
        const navLink = document.getElementById('navLink');
        navLink.href = `https://www.google.com/maps/dir/?api=1&destination=${alert.latitude},${alert.longitude}`;
        navLink.style.display = 'flex';

        document.getElementById('emergencyModal').style.display = 'flex';
        siren.play().catch(e => console.log('Audio blocked'));
    }

    function closeAlert() {
        document.getElementById('emergencyModal').style.display = 'none';
        siren.pause();
        siren.currentTime = 0;
    }

    function acceptMission() {
        alert('Mission Accepted! Opening Navigation...');
        const navLink = document.getElementById('navLink');
        window.open(navLink.href, '_blank');
        closeAlert();
        // Here we would update the mission status via AJAX
    }

    // Duty & Location Logic
    function toggleDuty(checkbox) {
        const isOnDuty = checkbox.checked;
        const dutyText = document.getElementById('dutyText');
        dutyText.textContent = isOnDuty ? 'ON DUTY' : 'OFF DUTY';

        fetch('{{ route("responder.toggle-duty") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            },
            body: JSON.stringify({ is_on_duty: isOnDuty })
        });

        if (isOnDuty) {
            startTracking();
        } else {
            stopTracking();
        }
    }

    let trackingInterval = null;

    function startTracking() {
        if ("geolocation" in navigator) {
            trackingInterval = setInterval(() => {
                navigator.geolocation.getCurrentPosition(position => {
                    const { latitude, longitude } = position.coords;
                    fetch('{{ route("responder.update-location") }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                        },
                        body: JSON.stringify({ latitude, longitude })
                    });
                });
            }, 10000); // Update location every 10 seconds
        }
    }

    function stopTracking() {
        if (trackingInterval) clearInterval(trackingInterval);
    }

    // Auto-start tracking if already on duty
    if (document.getElementById('dutySwitch').checked) {
        startTracking();
        document.getElementById('dutyText').textContent = 'ON DUTY';
    }

    setInterval(pollAlerts, 5000);
</script>
</body>
</html>
