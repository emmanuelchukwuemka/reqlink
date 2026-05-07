<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Dashboard | ResQLink</title>
    <link rel="stylesheet" href="{{ asset('css/dashboard.css') }}">
    <link rel="stylesheet" href="{{ asset('css/chat.css') }}">
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin=""/>
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>
    <script src="https://unpkg.com/lucide@latest"></script>
    <style>
        .tab-pane { display: none; }
        .tab-pane.active { display: block; }
        .nav-item { cursor: pointer; }
        .theme-toggle { background: transparent; border: none; color: var(--grey); cursor: pointer; padding: 8px; border-radius: 50%; transition: all 0.3s; display: flex; align-items: center; justify-content: center; }
        .theme-toggle:hover { background: var(--glass); color: var(--white); }
        :root.light-mode .theme-toggle:hover { background: rgba(0,0,0,0.05); color: var(--black); }
        
        /* Voice SOS Styles */
        .voice-toggle { background: transparent; border: none; color: var(--grey); cursor: pointer; padding: 8px; border-radius: 50%; transition: all 0.3s; display: flex; align-items: center; justify-content: center; position: relative; }
        .voice-toggle.active { color: var(--red); background: rgba(229, 9, 20, 0.1); }
        .voice-toggle.active::after {
            content: '';
            position: absolute;
            inset: 0;
            border-radius: 50%;
            border: 2px solid var(--red);
            animation: pulse-voice 1.5s infinite;
        }
        @keyframes pulse-voice {
            0% { transform: scale(1); opacity: 1; }
            100% { transform: scale(1.8); opacity: 0; }
        }

        /* Live Alert Overlay */
        .live-alert-overlay {
            position: fixed;
            bottom: 30px;
            right: 30px;
            width: 350px;
            background: #0a0a0a;
            border: 2px solid var(--red);
            border-radius: 20px;
            padding: 25px;
            z-index: 2000;
            box-shadow: 0 20px 50px rgba(229, 9, 20, 0.4);
            display: none;
            animation: slide-up 0.5s ease-out;
        }
        @keyframes slide-up { from { transform: translateY(100px); opacity: 0; } to { transform: translateY(0); opacity: 1; } }
    </style>
    <script src="{{ asset('js/theme.js') }}"></script>
</head>
<body class="dashboard-layout">

<aside class="sidebar">
    <div class="sidebar-header">
        <div class="auth-logo" style="margin-bottom: 0;">
            <img src="{{ asset('images/logo.png') }}" alt="ResQLink" style="height: 50px; width: auto; object-fit: contain;">
        </div>
    </div>
    
    <nav class="sidebar-nav">
        <a class="nav-item active" data-tab="overview"><i data-lucide="layout-grid"></i> Overview</a>
        <a class="nav-item" data-tab="ambulance"><i data-lucide="truck"></i> Ambulance</a>
        <a class="nav-item" data-tab="security"><i data-lucide="shield-alert"></i> Security</a>
        <a class="nav-item" data-tab="fire"><i data-lucide="flame"></i> Fire Services</a>
        <a class="nav-item" data-tab="hospitals"><i data-lucide="hospital"></i> Hospitals</a>
        <a class="nav-item" data-tab="history"><i data-lucide="history"></i> Incident History</a>
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
        <div>
            <h1 id="pageTitle" style="font-size: 1.5rem; font-weight: 800;">Command Center</h1>
            <p style="color: var(--grey); font-size: 0.9rem;">Welcome back, {{ Auth::user()->name }}</p>
        </div>
            @if(Auth::user()->is_good_samaritan)
            <div class="duty-status-container" style="padding: 4px 12px; background: rgba(34, 197, 94, 0.05);">
                <span class="duty-label" id="samaritanText" style="font-size: 0.65rem;">{{ Auth::user()->samaritan_active ? 'ACTIVE SAMARITAN' : 'SAMARITAN OFF' }}</span>
                <label class="duty-toggle" style="width: 36px; height: 18px;">
                    <input type="checkbox" id="samaritanSwitch" {{ Auth::user()->samaritan_active ? 'checked' : '' }} onchange="toggleSamaritan(this)">
                    <span class="duty-slider" style="border-radius: 20px;"></span>
                </label>
            </div>
            @endif
            <button id="voiceToggle" class="voice-toggle" title="AI Voice SOS Mode">
                <i data-lucide="mic" id="voiceIcon"></i>
            </button>
            <button id="themeToggle" class="theme-toggle" aria-label="Toggle Dark Mode">
                <i data-lucide="sun" id="themeIcon"></i>
            </button>
            <div class="user-profile">
                <span style="font-size: 0.85rem; font-weight: 600;">Safety Status: Secure</span>
                <div class="avatar-sm">{{ substr(Auth::user()->name, 0, 1) }}</div>
            </div>
        </div>
    </header>

    <!-- OVERVIEW TAB -->
    <div id="overview" class="tab-pane active">
        <!-- SOS TRIGGER -->
        <div class="dash-card sos-trigger-card" style="margin-bottom: 24px;">
            <div class="sos-content">
                <div class="hero-badge" style="background: rgba(229, 9, 20, 0.1); border-color: rgba(229, 9, 20, 0.2); margin-bottom: 16px;">
                    <span class="pulse-dot"></span>
                    Emergency Panic Mode
                </div>
                <h2>Need Immediate Help?</h2>
                <p>Press and hold the SOS button for 1.5 seconds. Your location will be captured and responders dispatched instantly.</p>
            </div>
            <div class="panic-btn" id="panicBtn">
                <span>SOS</span>
                <small>Hold to Alert</small>
            </div>
        </div>

        <div class="dashboard-grid">
            <div class="dash-card">
                <h3><i data-lucide="map"></i> Real-time Network</h3>
                <div id="map" style="height: 350px; border-radius: 12px; border: 1px solid var(--glass-border); background: var(--dark2);"></div>
            </div>

            <div>
                <div class="dash-card">
                    <h3><i data-lucide="history"></i> Recent Activity</h3>
                    <div class="history-list">
                        @forelse($history as $item)
                        <div class="history-item" style="padding: 12px; background: rgba(255,255,255,0.02); border-radius: 8px; margin-bottom: 10px; display: flex; align-items: center; gap: 12px;">
                            <i data-lucide="alert-circle" style="color: var(--red)"></i>
                            <div>
                                <p style="font-size: 0.85rem; font-weight: 600; margin: 0;">Emergency Alert</p>
                                <p style="font-size: 0.7rem; color: var(--grey); margin: 0;">{{ $item->created_at->diffForHumans() }}</p>
                            </div>
                            <span style="margin-left: auto; font-size: 0.7rem; text-transform: uppercase; font-weight: 700; color: {{ $item->status == 'resolved' ? '#22c55e' : 'var(--red)' }}">{{ $item->status }}</span>
                        </div>
                        @empty
                        <p style="color: var(--grey); font-size: 0.8rem; text-align: center; padding: 20px;">No recent alerts.</p>
                        @endforelse
                    </div>
                </div>

                @if(Auth::user()->is_good_samaritan && Auth::user()->samaritan_active)
                <div class="dash-card" style="margin-top: 24px; border: 1px solid #22c55e; background: rgba(34, 197, 94, 0.05);">
                    <h3 style="color: #22c55e;"><i data-lucide="heart"></i> Nearby Samaritan Missions</h3>
                    <div class="history-list">
                        @forelse($samaritanMissions as $mission)
                        <div style="padding: 15px; background: rgba(255,255,255,0.03); border-radius: 12px; margin-bottom: 12px;">
                            <div style="display: flex; justify-content: space-between; margin-bottom: 10px;">
                                <span style="font-size: 0.75rem; font-weight: 800; color: #22c55e;">MEDICAL FIRST AID</span>
                                <small style="color: var(--grey);">{{ $mission->created_at->diffForHumans() }}</small>
                            </div>
                            <p style="font-size: 0.85rem; margin-bottom: 15px;">A user nearby needs assistance. You are a verified <strong>{{ Auth::user()->samaritan_profession }}</strong>.</p>
                            <button class="btn-primary" style="width: 100%; background: #22c55e; font-size: 0.8rem; padding: 10px;">Accept & Provide Aid</button>
                        </div>
                        @empty
                        <p style="color: var(--grey); font-size: 0.8rem; text-align: center;">No active emergencies nearby.</p>
                        @endforelse
                    </div>
                </div>
                @endif
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
                    <div style="font-size: 0.8rem; color: var(--grey); margin-bottom: 15px;">
                        <p style="margin: 4px 0;">Reg: {{ $unit->vehicle_reg ?? 'N/A' }}</p>
                        <p style="margin: 4px 0;">Capacity: {{ $unit->capacity ?? 'Standard' }}</p>
                    </div>
                    <button class="btn-primary" style="width: 100%; padding: 12px; font-size: 0.85rem; border-radius: 8px;">Request Unit</button>
                </div>
                @empty
                <div style="grid-column: 1/-1; text-align: center; padding: 40px;">
                    <p style="color: var(--grey);">No active ambulance units in your area.</p>
                </div>
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
                                {{ $unit->is_on_duty ? '● Active Patrol' : '○ Standby' }}
                            </small>
                        </div>
                    </div>
                    <button class="btn-primary" style="width: 100%; padding: 12px; font-size: 0.85rem; border-radius: 8px; background: #2563eb;">Request Response</button>
                </div>
                @empty
                <div style="grid-column: 1/-1; text-align: center; padding: 40px;">
                    <p style="color: var(--grey);">No security units currently patrolling.</p>
                </div>
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
                    <button class="btn-primary" style="width: 100%; padding: 12px; font-size: 0.85rem; border-radius: 8px; background: #f97316;">Report Fire</button>
                </div>
                @empty
                <div style="grid-column: 1/-1; text-align: center; padding: 40px;">
                    <p style="color: var(--grey);">No fire stations registered in this sector.</p>
                </div>
                @endforelse
            </div>
        </div>
    </div>

    <!-- HISTORY TAB -->
    <div id="history" class="tab-pane">
        <div class="dash-card">
            <h3><i data-lucide="history"></i> Complete Incident Logs</h3>
            <table style="width: 100%; border-collapse: collapse; margin-top: 20px;">
                <thead>
                    <tr style="text-align: left; color: var(--grey); font-size: 0.8rem; text-transform: uppercase;">
                        <th style="padding: 15px;">Date</th>
                        <th>Status</th>
                        <th>Coordinates</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($history as $item)
                    <tr style="border-top: 1px solid var(--glass-border);">
                        <td style="padding: 15px;">{{ $item->created_at->format('M d, Y H:i') }}</td>
                        <td><span style="color: {{ $item->status == 'resolved' ? '#22c55e' : 'var(--red)' }}">{{ ucfirst($item->status) }}</span></td>
                        <td>{{ round($item->latitude, 4) }}, {{ round($item->longitude, 4) }}</td>
                        <td><a href="#" style="color: var(--red); font-weight: 700; font-size: 0.85rem;">View Report</a></td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <!-- LIVE ALERT OVERLAY -->
    <div id="liveAlert" class="live-alert-overlay">
        <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 20px;">
            <div>
                <h3 style="color: var(--red); margin: 0; font-size: 1.1rem; display: flex; align-items: center; gap: 8px;">
                    <span class="pulse-dot"></span> HELP IS EN ROUTE
                </h3>
                <p id="emergencyStatus" style="font-size: 0.8rem; color: var(--grey); margin: 5px 0 0;">Responder dispatched...</p>
            </div>
            <i data-lucide="siren" style="color: var(--red); width: 24px;"></i>
        </div>

        <div style="background: rgba(255,255,255,0.03); border-radius: 12px; padding: 15px; margin-bottom: 20px;">
            <div style="display: flex; align-items: center; gap: 12px;">
                <div id="responderAvatar" style="width: 40px; height: 40px; background: var(--red); border-radius: 10px; display: flex; align-items: center; justify-content: center; font-weight: 900;">R</div>
                <div>
                    <div id="responderName" style="font-weight: 700; font-size: 0.9rem;">Medical Unit 101</div>
                    <div id="responderETA" style="font-size: 0.75rem; color: #22c55e; font-weight: 700;">ETA: Calculating...</div>
                </div>
            </div>
        </div>

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 10px;">
            <button class="btn-primary" style="padding: 10px; font-size: 0.8rem; background: #2563eb;">Call Unit</button>
            <button onclick="cancelEmergency()" style="padding: 10px; font-size: 0.8rem; background: rgba(255,255,255,0.05); color: white; border: 1px solid var(--glass-border); border-radius: 8px;">Cancel</button>
        </div>
    </div>
</main>

<script>
    lucide.createIcons();

    // Listen for global theme changes to update map
    document.addEventListener('themeChanged', (e) => {
        if (typeof tileLayer !== 'undefined') {
            const newTileUrl = e.detail.isLight 
                ? 'https://{s}.basemaps.cartocdn.com/light_all/{z}/{x}/{y}{r}.png'
                : 'https://{s}.basemaps.cartocdn.com/dark_all/{z}/{x}/{y}{r}.png';
            tileLayer.setUrl(newTileUrl);
        }
    });
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

            // Reflow Map if needed
            if(tabId === 'overview') {
                setTimeout(() => map.invalidateSize(), 100);
            }
        });
    });

    // Map Initialization
    let map = L.map('map').setView([6.5244, 3.3792], 13);
    const initialTileUrl = document.documentElement.classList.contains('light-mode')
        ? 'https://{s}.basemaps.cartocdn.com/light_all/{z}/{x}/{y}{r}.png'
        : 'https://{s}.basemaps.cartocdn.com/dark_all/{z}/{x}/{y}{r}.png';
        
    let tileLayer = L.tileLayer(initialTileUrl, {
        attribution: '&copy; CartoDB'
    }).addTo(map);

    let userMarker;

    if (navigator.geolocation) {
        navigator.geolocation.getCurrentPosition((position) => {
            const { latitude, longitude } = position.coords;
            map.setView([latitude, longitude], 15);
            userMarker = L.marker([latitude, longitude]).addTo(map)
                .bindPopup('Your Current Location').openPopup();
        });
    }
    
    // Panic Button Interaction
    const panicBtn = document.getElementById('panicBtn');
    let pressTimer;

    panicBtn.addEventListener('mousedown', () => {
        panicBtn.style.transform = 'scale(0.9)';
        pressTimer = setTimeout(() => triggerEmergency(), 1500);
    });

    panicBtn.addEventListener('mouseup', () => {
        clearTimeout(pressTimer);
        panicBtn.style.transform = 'scale(1)';
    });

    panicBtn.addEventListener('mouseleave', () => {
        clearTimeout(pressTimer);
        panicBtn.style.transform = 'scale(1)';
    });

    let activeEmergencyUuid = @json($activeEmergency ? $activeEmergency->uuid : null);
    let responderMarker = null;
    let pollingInterval = null;

    function triggerEmergency() {
        if (!navigator.geolocation) return alert('Geolocation not supported');
        panicBtn.innerHTML = '<span>...</span><small>Locating</small>';

        navigator.geolocation.getCurrentPosition((position) => {
            const { latitude, longitude } = position.coords;
            fetch('{{ route("emergency.trigger") }}', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                body: JSON.stringify({ latitude, longitude })
            })
            .then(res => res.json())
            .then(data => {
                panicBtn.innerHTML = '<span>SOS</span><small>Dispatched</small>';
                activeEmergencyUuid = data.uuid;
                startPollingStatus();
                document.getElementById('liveAlert').style.display = 'block';
            });
        });
    }

    function startPollingStatus() {
        if (!activeEmergencyUuid) return;
        
        if (pollingInterval) clearInterval(pollingInterval);
        
        pollingInterval = setInterval(() => {
            fetch(`/emergency/status/${activeEmergencyUuid}`)
                .then(res => res.json())
                .then(data => {
                    updateLiveUI(data);
                    if (data.status === 'resolved' || data.status === 'cancelled') {
                        stopPollingStatus();
                    }
                });
        }, 5000);
    }

    function updateLiveUI(data) {
        const overlay = document.getElementById('liveAlert');
        overlay.style.display = 'block';
        
        document.getElementById('emergencyStatus').textContent = `Status: ${data.status.toUpperCase()}`;
        
        if (data.responder) {
            document.getElementById('responderName').textContent = data.responder.name;
            document.getElementById('responderETA').textContent = `ETA: ${data.eta || '5'} mins`;
            document.getElementById('responderAvatar').textContent = data.responder.name.charAt(0);
            
            if (data.responder.lat && data.responder.lng) {
                const pos = [data.responder.lat, data.responder.lng];
                if (!responderMarker) {
                    responderMarker = L.marker(pos, {
                        icon: L.divIcon({ 
                            html: '<div style="background:var(--red); border:2px solid white; border-radius:50%; width:15px; height:15px; box-shadow: 0 0 10px rgba(229,9,20,0.5);"></div>', 
                            className: 'custom-div-icon' 
                        })
                    }).addTo(map).bindPopup('Assigned Responder');
                } else {
                    responderMarker.setLatLng(pos);
                }
                
                // Adjust map to show both user and responder
                if (userMarker) {
                    const group = new L.featureGroup([userMarker, responderMarker]);
                    map.fitBounds(group.getBounds().pad(0.5));
                }
            }
        }
    }

    function stopPollingStatus() {
        clearInterval(pollingInterval);
        document.getElementById('liveAlert').style.display = 'none';
        if (responderMarker) {
            map.removeLayer(responderMarker);
            responderMarker = null;
        }
        activeEmergencyUuid = null;
    }

    function cancelEmergency() {
        if (confirm('Are you sure you want to cancel this emergency?')) {
            stopPollingStatus();
            alert('Emergency cancelled.');
        }
    }

    // AI Voice SOS Implementation
    const voiceToggle = document.getElementById('voiceToggle');
    const voiceIcon = document.getElementById('voiceIcon');
    let recognition = null;
    let isListening = false;

    if ('webkitSpeechRecognition' in window || 'SpeechRecognition' in window) {
        const SpeechRecognition = window.SpeechRecognition || window.webkitSpeechRecognition;
        recognition = new SpeechRecognition();
        recognition.continuous = true;
        recognition.interimResults = true;
        recognition.lang = 'en-US';

        recognition.onresult = (event) => {
            const transcript = Array.from(event.results)
                .map(result => result[0])
                .map(result => result.transcript)
                .join('')
                .toLowerCase();

            console.log('Transcript:', transcript);

            if (transcript.includes('emergency resqlink') || transcript.includes('help me resqlink')) {
                speak("Emergency detected. Triggering SOS. Help is on the way.");
                triggerEmergency();
                stopVoiceSOS();
            }
        };

        recognition.onend = () => {
            if (isListening) recognition.start(); // Keep listening if active
        };
    }

    voiceToggle.addEventListener('click', () => {
        if (!recognition) return alert('Voice recognition not supported in this browser.');
        
        if (!isListening) {
            startVoiceSOS();
        } else {
            stopVoiceSOS();
        }
    });

    function startVoiceSOS() {
        isListening = true;
        voiceToggle.classList.add('active');
        voiceIcon.setAttribute('data-lucide', 'mic-off');
        lucide.createIcons();
        recognition.start();
        speak("Voice mode active. I am listening for your help command.");
        console.log('Voice SOS Active: Listening for "Emergency ResQLink"');
    }

    function stopVoiceSOS() {
        isListening = false;
        voiceToggle.classList.remove('active');
        voiceIcon.setAttribute('data-lucide', 'mic');
        lucide.createIcons();
        recognition.stop();
        speak("Voice mode deactivated.");
    }

    function speak(text) {
        if ('speechSynthesis' in window) {
            const utterance = new SpeechSynthesisUtterance(text);
            utterance.rate = 1.0;
            utterance.pitch = 1.0;
            window.speechSynthesis.speak(utterance);
        }
    }

    // Samaritan Toggle Logic
    function toggleSamaritan(checkbox) {
        const isActive = checkbox.checked;
        const text = document.getElementById('samaritanText');
        text.textContent = isActive ? 'ACTIVE SAMARITAN' : 'SAMARITAN OFF';

        fetch('/user/toggle-samaritan', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({ active: isActive })
        }).then(() => {
            window.location.reload(); // Reload to show/hide missions
        });
    }

    // Initialize polling if there's an active emergency from page load
    if (activeEmergencyUuid) {
        startPollingStatus();
    }
</script>
<script src="{{ asset('js/chat.js') }}"></script>
</body>
</html>
