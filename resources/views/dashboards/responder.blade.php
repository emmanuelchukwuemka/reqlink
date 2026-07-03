<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Mission Control | ResQLink</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="manifest" href="/manifest.json">
    <link rel="stylesheet" href="/css/dashboard.css">
    <link rel="stylesheet" href="/css/chat.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/leaflet@1.9.4/dist/leaflet.min.css"/>
    <script src="https://cdn.jsdelivr.net/npm/leaflet@1.9.4/dist/leaflet.min.js"></script>
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
            <img src="{{ asset('images/logo.png') }}" alt="ResQLink" style="height: 100px; width: auto; object-fit: contain;">
        </div>
        <div class="responder-badge" style="margin-top: 10px;">{{ strtoupper(Auth::user()->role) }} UNIT</div>
    </div>
    
    <nav class="sidebar-nav">
        <a class="nav-item active" data-tab="missions"><i data-lucide="layout-dashboard"></i> Missions</a>
        <a class="nav-item" data-tab="ambulance"><i data-lucide="truck"></i> Ambulance</a>

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

            @include('partials.lang-switcher')
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
                    <div id="noMissions" style="text-align: center; padding: 40px; opacity: 0.5;">
                        <i data-lucide="shield-check" style="width: 40px; height: 40px; margin-bottom: 10px;"></i>
                        <p>No active emergencies. Go on duty to receive alerts.</p>
                    </div>
                </div>
            </div>

            <div>
                <div class="dash-card" style="margin-bottom: 24px;">
                    <h3><i data-lucide="activity"></i> Station Stats</h3>
                    <div class="stats-row" style="margin-top: 15px;">
                        <div class="stat-box">
                            <h4 style="color: var(--red);">{{ $missionsDone }}</h4>
                            <p>Completed</p>
                        </div>
                        <div class="stat-box">
                            <h4>{{ $totalUnits }}</h4>
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
        <div class="dash-card" style="margin-bottom: 24px;">
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

        <!-- BED RESERVATION -->
        @if(Auth::user()->role === 'ambulance')
        <div class="dash-card">
            <h3><i data-lucide="bed"></i> Reserve Hospital Bed</h3>
            <p style="color: var(--grey); font-size: 0.85rem; margin-bottom: 20px;">Reserve a bed at a hospital before you arrive so it's ready when you need it.</p>
            <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); gap: 16px;">
                @forelse($hospitals as $hospital)
                <div style="background: rgba(255,255,255,0.02); border: 1px solid var(--glass-border); border-radius: 14px; padding: 18px;">
                    <div style="display: flex; align-items: center; gap: 12px; margin-bottom: 14px;">
                        <div style="width: 40px; height: 40px; background: rgba(34,197,94,0.1); border-radius: 10px; display: flex; align-items: center; justify-content: center; color: #22c55e; flex-shrink: 0;">
                            <i data-lucide="hospital"></i>
                        </div>
                        <div>
                            <h4 style="margin: 0; font-size: 0.9rem;">{{ $hospital->name }}</h4>
                            <small style="color: var(--grey);">{{ $hospital->available_beds ?? 0 }} beds available</small>
                        </div>
                    </div>
                    @if(($hospital->available_beds ?? 0) > 0)
                    <form onsubmit="reserveBed(event, {{ $hospital->id }}, this)">
                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 8px; margin-bottom: 12px;">
                            <select name="emergency_id" required style="padding: 8px; border-radius: 8px; border: 1px solid var(--glass-border); background: rgba(255,255,255,0.05); color: var(--white); font-size: 0.8rem;">
                                <option value="">Select Emergency</option>
                                @if($activeEmergencyForBed ?? null)
                                <option value="{{ $activeEmergencyForBed->uuid }}" selected>Active Mission</option>
                                @endif
                            </select>
                            <input type="number" name="eta_minutes" placeholder="ETA (mins)" min="1" max="120" style="padding: 8px; border-radius: 8px; border: 1px solid var(--glass-border); background: rgba(255,255,255,0.05); color: var(--white); font-size: 0.8rem;">
                        </div>
                        <button type="submit" class="btn-primary" style="width: 100%; padding: 10px; font-size: 0.82rem; border-radius: 8px;">
                            Reserve Bed
                        </button>
                    </form>
                    @else
                    <div style="text-align: center; padding: 10px; color: var(--grey); font-size: 0.8rem; background: rgba(255,255,255,0.02); border-radius: 8px;">No beds available</div>
                    @endif
                </div>
                @empty
                <p style="color: var(--grey);">No hospitals registered.</p>
                @endforelse
            </div>
            <div id="bedMsg" style="display:none; margin-top:16px; padding:12px; border-radius:10px; font-size:0.85rem;"></div>
        </div>
        @endif
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
<div id="emergencyModal" style="display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.7); z-index: 1000; align-items: center; justify-content: center; backdrop-filter: blur(10px);">
    <div style="background: var(--dark); border: 1px solid var(--red); width: 100%; max-width: 500px; border-radius: 20px; padding: 40px; text-align: center; box-shadow: 0 0 50px rgba(229, 9, 20, 0.3); animation: pulse-red 2s infinite; color: var(--white);">
        <div style="width: 80px; height: 80px; background: rgba(229, 9, 20, 0.1); border-radius: 50%; display: flex; align-items: center; justify-content: center; color: var(--red); margin: 0 auto 24px;">
            <i data-lucide="siren" style="width: 48px; height: 48px;"></i>
        </div>
        <h2 style="font-size: 2rem; font-weight: 900; margin-bottom: 10px; color: var(--white);">CRITICAL ALERT</h2>
        <p id="alertUser" style="font-size: 1.1rem; color: var(--grey); margin-bottom: 5px;">Patient: John Doe</p>
        <p id="alertLoc" style="font-size: 0.9rem; color: var(--red); font-weight: 700; margin-bottom: 30px;">LOCATION: 1.2km away</p>
        
        <div style="background: var(--glass); border: 1px solid var(--glass-border); border-radius: 12px; padding: 20px; margin-bottom: 30px; text-align: left;">
            <p style="font-size: 0.75rem; color: var(--grey); margin-bottom: 5px; text-transform: uppercase;">Medical ID Summary</p>
            <p id="alertMedical" style="font-weight: 600; color: var(--white);">Blood: O+ | Allergies: None | Asthma</p>
        </div>

        <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 10px;">
            <button onclick="closeAlert()" style="background: rgba(255,255,255,0.05); color: var(--white); border: 1px solid var(--glass-border); padding: 16px; border-radius: 12px; font-weight: 700; cursor: pointer;">Decline</button>
            <button onclick="acceptMission()" style="background: var(--red); color: #fff; border: none; padding: 16px; border-radius: 12px; font-weight: 700; cursor: pointer; box-shadow: 0 10px 20px rgba(229, 9, 20, 0.3);">Accept</button>
            <button onclick="openResponderChat()" style="background: rgba(34,197,94,0.15); color: #22c55e; border: 1px solid rgba(34,197,94,0.3); padding: 16px; border-radius: 12px; font-weight: 700; cursor: pointer;">Chat</button>
        </div>
        <a id="navLink" href="#" target="_blank" style="display: none; background: #2563eb; color: #fff; text-decoration: none; padding: 12px; border-radius: 12px; font-weight: 700; font-size: 0.8rem; align-items: center; justify-content: center; gap: 8px; margin-top: 10px;">
            <i data-lucide="navigation"></i> Open Navigation
        </a>
    </div>
</div>

<!-- RESPONDER CHAT WINDOW -->
<div id="responderChat" style="display:none; position:fixed; bottom:30px; right:30px; width:340px; height:440px; background:var(--dark2); border:1px solid rgba(34,197,94,0.3); border-radius:20px; flex-direction:column; overflow:hidden; z-index:6000; box-shadow:0 20px 50px rgba(0,0,0,0.3);">
    <div style="background:rgba(34,197,94,0.1); padding:16px 20px; display:flex; align-items:center; gap:12px; border-bottom:1px solid var(--glass-border);">
        <div style="width:36px;height:36px;background:var(--red);border-radius:10px;display:flex;align-items:center;justify-content:center;font-weight:900;color:#fff;">P</div>
        <div style="flex:1;">
            <h4 style="margin:0;font-size:0.9rem;color:var(--white);" id="rChatPatientName">Patient</h4>
            <small style="color:#22c55e;font-weight:700;font-size:0.7rem;">Mission Chat</small>
        </div>
        <button onclick="document.getElementById('responderChat').style.display='none'" style="background:none;border:none;color:var(--grey);cursor:pointer;font-size:1.2rem;">&times;</button>
    </div>
    <div id="rChatBody" style="flex:1;padding:16px;overflow-y:auto;display:flex;flex-direction:column;gap:10px;background:var(--dark2);">
        <div style="text-align:center;color:var(--grey);font-size:0.8rem;padding:20px;">Chat with the patient</div>
    </div>
    <div style="padding:12px;border-top:1px solid var(--glass-border);display:flex;gap:8px;background:var(--dark2);">
        <input id="rChatInput" type="text" placeholder="Type a message..." style="flex:1;background:var(--glass);border:1px solid var(--glass-border);border-radius:10px;padding:10px 14px;color:var(--white);font-size:0.85rem;" onkeypress="if(event.key==='Enter') sendResponderChat()">
        <button onclick="sendResponderChat()" style="background:#22c55e;border:none;color:#fff;width:40px;border-radius:10px;cursor:pointer;font-size:1rem;">➤</button>
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
                renderMissionsList(data);
                if (data.length > 0) {
                    const latest = data[0];
                    if (currentAlertId !== latest.uuid) {
                        showEmergency(latest);
                    }
                }
            });
    }

    function renderMissionsList(emergencies) {
        const list = document.getElementById('activeMissions');
        if (emergencies.length === 0) {
            list.innerHTML = '<div id="noMissions" style="text-align:center;padding:40px;opacity:0.5;"><p>No active emergencies. Go on duty to receive alerts.</p></div>';
            return;
        }
        list.innerHTML = emergencies.map(e => `
            <div class="alert-item">
                <div style="display:flex;gap:16px;align-items:center;">
                    <div style="width:40px;height:40px;background:rgba(229,9,20,0.1);border-radius:50%;display:flex;align-items:center;justify-content:center;color:var(--red);">
                        <i data-lucide="heart-pulse"></i>
                    </div>
                    <div>
                        <p style="font-weight:700;margin:0;">Patient: ${e.user ? e.user.name : 'Unknown'}</p>
                        <p style="font-size:0.75rem;color:var(--grey);margin:0;">Status: ${e.status.toUpperCase()} · ${new Date(e.created_at).toLocaleTimeString()}</p>
                    </div>
                </div>
                <button class="btn-primary" style="padding:8px 16px;font-size:0.8rem;" onclick="showEmergency(${JSON.stringify(e).replace(/"/g, '&quot;')})">View</button>
            </div>
        `).join('');
        lucide.createIcons();
    }

    function showEmergency(alert) {
        currentAlertId = alert.uuid;
        document.getElementById('alertUser').textContent = `Patient: ${alert.user ? alert.user.name : 'Unknown'}`;
        document.getElementById('alertLoc').textContent = `LOCATION: ${alert.latitude}, ${alert.longitude}`;
        document.getElementById('alertMedical').textContent = `Blood: ${alert.user?.blood_group || 'N/A'} | Allergies: ${alert.user?.allergies || 'None'}`;

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
    L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a>',
        maxZoom: 19
    }).addTo(responderMap);
    // Dark mode filter
    const rPane = responderMap.getPane('tilePane');
    if (rPane) rPane.style.filter = 'invert(92%) hue-rotate(180deg) brightness(95%) contrast(90%)';

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

    // ── Bed Reservation ──────────────────────────────────────────────
    function reserveBed(e, hospitalUuid, form) {
        e.preventDefault();
        const fd = new FormData(form);
        const emergencyId = fd.get('emergency_id');
        const eta = fd.get('eta_minutes');
        if (!emergencyId) { alert('Please select an emergency/mission first.'); return; }

        fetch(`/bed/reserve/${hospitalUuid}`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content },
            body: JSON.stringify({ emergency_uuid: emergencyId, eta_minutes: eta })
        }).then(r => r.json()).then(data => {
            const msg = document.getElementById('bedMsg');
            msg.textContent = data.message || (data.success ? 'Bed reserved!' : 'Failed to reserve bed.');
            msg.style.background = data.success ? 'rgba(34,197,94,0.1)' : 'rgba(229,9,20,0.1)';
            msg.style.color = data.success ? '#22c55e' : 'var(--red)';
            msg.style.display = 'block';
            setTimeout(() => { msg.style.display = 'none'; }, 5000);
        }).catch(() => {});
    }

    // ── Responder Emergency Chat ──────────────────────────────────────
    let rChatPolling = null;
    let rLastChatId = 0;

    function openResponderChat() {
        if (!currentAlertId) return;
        const el = document.getElementById('responderChat');
        el.style.display = el.style.display === 'flex' ? 'none' : 'flex';
        if (el.style.display === 'flex') {
            pollResponderChat();
            if (!rChatPolling) rChatPolling = setInterval(pollResponderChat, 3000);
        }
    }

    function pollResponderChat() {
        if (!currentAlertId) return;
        fetch(`/chat/${currentAlertId}/messages`)
            .then(r => r.json())
            .then(msgs => {
                const body = document.getElementById('rChatBody');
                const newMsgs = msgs.filter(m => m.id > rLastChatId);
                newMsgs.forEach(m => {
                    rLastChatId = Math.max(rLastChatId, m.id);
                    const isMe = m.sender_role === 'responder';
                    const div = document.createElement('div');
                    div.className = isMe ? 'echat-msg me green' : 'echat-msg them';
                    div.textContent = m.message;
                    body.appendChild(div);
                });
                body.scrollTop = body.scrollHeight;
            }).catch(() => {});
    }

    function sendResponderChat() {
        if (!currentAlertId) return;
        const input = document.getElementById('rChatInput');
        const msg = input.value.trim();
        if (!msg) return;
        input.value = '';
        fetch(`/chat/${currentAlertId}/send`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content },
            body: JSON.stringify({ message: msg })
        }).then(() => pollResponderChat()).catch(() => {});
    }
</script>
<script src="/js/chat.js"></script>
<script src="/js/pwa.js" defer></script>
@include('partials.profile-modal')
</body>
</html>
