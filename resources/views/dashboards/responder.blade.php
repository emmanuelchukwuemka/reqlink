<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Mission Control | ResQLink</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="stylesheet" href="/css/dashboard.css">
    <link rel="stylesheet" href="/css/chat.css">
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin=""/>
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>
    <script src="https://unpkg.com/lucide@latest"></script>
    <style>
        .mission-grid { display: grid; grid-template-columns: 1fr 350px; gap: 24px; }
        @media (max-width: 900px) { .mission-grid { grid-template-columns: 1fr; } }
        @media (max-width: 768px) { .top-bar { flex-wrap: wrap; gap: 8px; } .duty-status-container { order: 3; } }
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
    <script src="/js/theme.js"></script>
</head>
<body class="dashboard-layout">

<div class="sidebar-overlay" id="sidebarOverlay"></div>

<aside class="sidebar">
    <div class="sidebar-header">
        <div class="auth-logo" style="margin-bottom: 0;">
            <img src="{{ asset('images/logo.png') }}" alt="ResQLink" style="height: 50px; width: auto; object-fit: contain;">
        </div>
        <div class="responder-badge" style="margin-top: 10px;">{{ strtoupper(Auth::user()->role) }} UNIT</div>
    </div>
    
    <nav class="sidebar-nav">
        <a class="nav-item active" data-tab="missions"><i data-lucide="layout-dashboard"></i> Missions</a>
        <a class="nav-item" data-tab="ambulance"><i data-lucide="truck"></i> Ambulance</a>
        <a class="nav-item" data-tab="security"><i data-lucide="shield-alert"></i> Security</a>
        <a class="nav-item" data-tab="fire"><i data-lucide="flame"></i> Fire Services</a>
        <a class="nav-item" data-tab="hospitals"><i data-lucide="hospital"></i> Hospitals</a>
        <a href="{{ route('settings') }}" class="nav-item"><i data-lucide="settings"></i> Settings</a>
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
            <h1 id="pageTitle" style="font-size: 1.5rem; font-weight: 800;">Mission Control</h1>
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

    <!-- MISSIONS TAB -->
    <div id="missions" class="tab-pane active">
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
                    <div id="responderMap" style="height: 350px; border-radius: 12px; border: 1px solid var(--glass-border); background: var(--dark2);"></div>
                </div>
            </div>
        </div>
    </div>

    <!-- AMBULANCE TAB -->
    <div id="ambulance" class="tab-pane">
        <div class="dash-card">
            <h3><i data-lucide="truck"></i> Active Ambulance Units</h3>
            <div class="hospitals-grid" style="display: grid; grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); gap: 20px; margin-top: 20px;">
                @forelse($ambulances as $unit)
                <div class="sub-card" style="background: rgba(255,255,255,0.02); border: 1px solid var(--glass-border); border-radius: 16px; padding: 20px;">
                    <div style="display: flex; align-items: center; gap: 15px; margin-bottom: 15px;">
                        <div style="width: 45px; height: 45px; background: rgba(229, 9, 20, 0.1); border-radius: 12px; display: flex; align-items: center; justify-content: center; color: var(--red);">
                            <i data-lucide="truck"></i>
                        </div>
                        <div>
                            <h4 style="margin: 0;">{{ $unit->user->name }}</h4>
                            <small style="color: {{ $unit->is_on_duty ? '#22c55e' : 'var(--grey)' }};">
                                {{ $unit->is_on_duty ? '● On Duty' : '○ Off Duty' }}
                            </small>
                        </div>
                    </div>
                    <button class="btn-primary" style="width: 100%; padding: 12px; font-size: 0.85rem; border-radius: 8px;">View Profile</button>
                </div>
                @empty
                <p>No active ambulance units.</p>
                @endforelse
            </div>
        </div>
    </div>

    <!-- SECURITY TAB -->
    <div id="security" class="tab-pane">
        <div class="dash-card">
            <h3><i data-lucide="shield-alert"></i> Rapid Security Response</h3>
            <div class="hospitals-grid" style="display: grid; grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); gap: 20px; margin-top: 20px;">
                @forelse($securityUnits as $unit)
                <div class="sub-card" style="background: rgba(255,255,255,0.02); border: 1px solid var(--glass-border); border-radius: 16px; padding: 20px;">
                    <div style="display: flex; align-items: center; gap: 15px; margin-bottom: 15px;">
                        <div style="width: 45px; height: 45px; background: rgba(37, 99, 235, 0.1); border-radius: 12px; display: flex; align-items: center; justify-content: center; color: #2563eb;">
                            <i data-lucide="shield-alert"></i>
                        </div>
                        <div>
                            <h4 style="margin: 0;">{{ $unit->user->name }}</h4>
                            <small style="color: {{ $unit->is_on_duty ? '#22c55e' : 'var(--grey)' }};">
                                {{ $unit->is_on_duty ? '● Active' : '○ Offline' }}
                            </small>
                        </div>
                    </div>
                    <button class="btn-primary" style="width: 100%; padding: 12px; font-size: 0.85rem; border-radius: 8px; background: #2563eb;">View Profile</button>
                </div>
                @empty
                <p>No security units found.</p>
                @endforelse
            </div>
        </div>
    </div>

    <!-- FIRE TAB -->
    <div id="fire" class="tab-pane">
        <div class="dash-card">
            <h3><i data-lucide="flame"></i> Fire & Rescue Services</h3>
            <div class="hospitals-grid" style="display: grid; grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); gap: 20px; margin-top: 20px;">
                @forelse($fireUnits as $unit)
                <div class="sub-card" style="background: rgba(255,255,255,0.02); border: 1px solid var(--glass-border); border-radius: 16px; padding: 20px;">
                    <div style="display: flex; align-items: center; gap: 15px; margin-bottom: 15px;">
                        <div style="width: 45px; height: 45px; background: rgba(249, 115, 22, 0.1); border-radius: 12px; display: flex; align-items: center; justify-content: center; color: #f97316;">
                            <i data-lucide="flame"></i>
                        </div>
                        <div>
                            <h4 style="margin: 0;">{{ $unit->user->name }}</h4>
                            <small style="color: {{ $unit->is_on_duty ? '#22c55e' : 'var(--grey)' }};">
                                {{ $unit->is_on_duty ? '● Station Ready' : '○ Offline' }}
                            </small>
                        </div>
                    </div>
                    <button class="btn-primary" style="width: 100%; padding: 12px; font-size: 0.85rem; border-radius: 8px; background: #f97316;">View Profile</button>
                </div>
                @empty
                <p>No fire stations found.</p>
                @endforelse
            </div>
        </div>
    </div>

    <!-- HOSPITALS TAB -->
    <div id="hospitals" class="tab-pane">
        <div class="dash-card">
            <h3><i data-lucide="hospital"></i> Medical Facilities</h3>
            <div class="hospitals-grid" style="display: grid; grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); gap: 20px; margin-top: 20px;">
                @forelse($hospitals as $hospital)
                <div class="sub-card" style="background: rgba(255,255,255,0.02); border: 1px solid var(--glass-border); border-radius: 16px; padding: 20px;">
                    <div style="display: flex; align-items: center; gap: 15px; margin-bottom: 15px;">
                        <div style="width: 45px; height: 45px; background: rgba(34, 197, 94, 0.1); border-radius: 12px; display: flex; align-items: center; justify-content: center; color: #22c55e;">
                            <i data-lucide="hospital"></i>
                        </div>
                        <div>
                            <h4 style="margin: 0;">{{ $hospital->name }}</h4>
                            <small style="color: var(--grey);">Verified Hospital</small>
                        </div>
                    </div>
                    <button class="btn-primary" style="width: 100%; padding: 12px; font-size: 0.85rem; border-radius: 8px;">View Details</button>
                </div>
                @empty
                <p>No hospitals registered yet.</p>
                @endforelse
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

    // Mobile sidebar toggle
    (function() {
        const hamburgerBtn = document.getElementById('hamburgerBtn');
        const sidebar = document.querySelector('.sidebar');
        const sidebarOverlay = document.getElementById('sidebarOverlay');
        hamburgerBtn.addEventListener('click', () => {
            sidebar.classList.toggle('open');
            sidebarOverlay.classList.toggle('active');
        });
        sidebarOverlay.addEventListener('click', () => {
            sidebar.classList.remove('open');
            sidebarOverlay.classList.remove('active');
        });
        document.querySelectorAll('.nav-item[data-tab]').forEach(item => {
            item.addEventListener('click', () => {
                if (window.innerWidth <= 768) {
                    sidebar.classList.remove('open');
                    sidebarOverlay.classList.remove('active');
                }
            });
        });
    })();

    // Tab Switching
    document.querySelectorAll('.nav-item[data-tab]').forEach(item => {
        item.addEventListener('click', () => {
            // Update UI
            document.querySelectorAll('.nav-item').forEach(i => i.classList.remove('active'));
            item.classList.add('active');

            // Switch Panes
            const tabId = item.getAttribute('data-tab');
            document.querySelectorAll('.tab-pane').forEach(p => p.classList.remove('active'));
            const pane = document.getElementById(tabId);
            if (pane) pane.classList.add('active');

            // Update Title
            document.getElementById('pageTitle').textContent = item.textContent.trim();
        });
    });

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
        document.getElementById('liveAlert').classList.remove('active');
        siren.pause();
        siren.currentTime = 0;
    }

    function acceptMission() {
        if (!currentAlertId) return;

        fetch(`/emergency/accept/${currentAlertId}`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            }
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                alert('Mission Accepted! Tracking user location...');
                const navLink = document.getElementById('navLink');
                window.open(navLink.href, '_blank');
                closeAlert();
                startMissionTracking(currentAlertId);
            }
        });
    }

    let missionPolling = null;
    let userMarker = null;
    let responderMarker = null;

    function startMissionTracking(uuid) {
        if (missionPolling) clearInterval(missionPolling);
        
        missionPolling = setInterval(() => {
            fetch(`/emergency/status/${uuid}`)
                .then(res => res.json())
                .then(data => {
                    if (data.user_location) {
                        const userPos = [data.user_location.lat, data.user_location.lng];
                        
                        if (!userMarker) {
                            userMarker = L.marker(userPos, {
                                icon: L.divIcon({ 
                                    html: '<div style="background:var(--red); border:2px solid white; border-radius:50%; width:15px; height:15px; box-shadow: 0 0 10px rgba(229,9,20,0.5);"></div>', 
                                    className: 'custom-div-icon' 
                                })
                            }).addTo(responderMap).bindPopup('Patient Location').openPopup();
                        } else {
                            userMarker.setLatLng(userPos);
                        }

                        // Fit map to show both if responder location is known
                        if (responderMarker) {
                            const group = new L.featureGroup([userMarker, responderMarker]);
                            responderMap.fitBounds(group.getBounds().pad(0.2));
                        } else {
                            responderMap.setView(userPos, 15);
                        }
                    }

                    if (data.status === 'resolved' || data.status === 'cancelled') {
                        clearInterval(missionPolling);
                        if (userMarker) responderMap.removeLayer(userMarker);
                        userMarker = null;
                        alert('Mission ended: ' + data.status);
                    }
                });
        }, 5000);
    }

    // Initialize Map
    let responderMap = L.map('responderMap').setView([6.5244, 3.3792], 13);
    L.tileLayer('https://{s}.basemaps.cartocdn.com/dark_all/{z}/{x}/{y}{r}.png', {
        attribution: '&copy; CartoDB'
    }).addTo(responderMap);

    function startTracking() {
        if ("geolocation" in navigator) {
            trackingInterval = setInterval(() => {
                navigator.geolocation.getCurrentPosition(position => {
                    const { latitude, longitude } = position.coords;
                    
                    // Update local marker
                    if (!responderMarker) {
                        responderMarker = L.marker([latitude, longitude]).addTo(responderMap).bindPopup('Your Location');
                    } else {
                        responderMarker.setLatLng([latitude, longitude]);
                    }

                    fetch('{{ route("responder.update-location") }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                        },
                        body: JSON.stringify({ latitude, longitude })
                    });
                });
            }, 10000);
        }
    }

    function stopTracking() {
        if (trackingInterval) clearInterval(trackingInterval);
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

    // Auto-start tracking if already on duty
    if (document.getElementById('dutySwitch').checked) {
        startTracking();
        document.getElementById('dutyText').textContent = 'ON DUTY';
    }

    setInterval(pollAlerts, 5000);
</script>
<script src="/js/chat.js"></script>
</body>
</html>
