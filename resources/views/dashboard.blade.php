<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Dashboard | ResQLink</title>
    <link rel="stylesheet" href="{{ asset('css/dashboard.css') }}">
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
        <a class="nav-item" data-tab="hospitals"><i data-lucide="hospital"></i> Hospitals</a>
        <a class="nav-item" data-tab="responders"><i data-lucide="shield"></i> Responders</a>
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
        <div style="display: flex; align-items: center; gap: 20px;">
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
            </div>
        </div>
    </div>

    <!-- HOSPITALS TAB -->
    <div id="hospitals" class="tab-pane">
        <div class="dash-card">
            <h3><i data-lucide="hospital"></i> Nearby Medical Facilities</h3>
            <div class="hospitals-grid" style="display: grid; grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); gap: 20px; margin-top: 20px;">
                @forelse($hospitals as $hospital)
                <div style="background: rgba(255,255,255,0.02); border: 1px solid var(--glass-border); border-radius: 16px; padding: 20px;">
                    <div style="display: flex; align-items: center; gap: 15px; margin-bottom: 15px;">
                        <div style="width: 45px; height: 45px; background: rgba(34, 197, 94, 0.1); border-radius: 12px; display: flex; align-items: center; justify-content: center; color: #22c55e;">
                            <i data-lucide="hospital"></i>
                        </div>
                        <div>
                            <h4 style="margin: 0;">{{ $hospital->name }}</h4>
                            <small style="color: var(--grey);">Verified Hospital</small>
                        </div>
                    </div>
                    <div style="font-size: 0.85rem; color: var(--grey); margin-bottom: 20px;">
                        <p style="margin: 5px 0;"><i data-lucide="phone" style="width: 14px; vertical-align: middle; margin-right: 8px;"></i> {{ $hospital->phone }}</p>
                        <p style="margin: 5px 0;"><i data-lucide="map-pin" style="width: 14px; vertical-align: middle; margin-right: 8px;"></i> Victoria Island, Lagos</p>
                    </div>
                    <button class="btn-primary" style="width: 100%; padding: 12px; font-size: 0.85rem; border-radius: 8px;">Request Transfer</button>
                </div>
                @empty
                <p>No hospitals registered yet.</p>
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
                if(data.hospital) {
                    const hospitalPos = [data.hospital.lat, data.hospital.lng];
                    const userPos = [latitude, longitude];
                    const ambMarker = L.marker(hospitalPos, { 
                        icon: L.divIcon({ html: '<i data-lucide="truck" style="color:#22c55e"></i>', className: 'custom-div-icon' })
                    }).addTo(map).bindPopup(`<b>Ambulance En Route</b>`).openPopup();
                    
                    const group = new L.featureGroup([userMarker, ambMarker]);
                    map.fitBounds(group.getBounds().pad(0.5));

                    // Simple move simulation
                    let step = 0;
                    const interval = setInterval(() => {
                        step++;
                        ambMarker.setLatLng([
                            hospitalPos[0] + (userPos[0] - hospitalPos[0]) * (step/100),
                            hospitalPos[1] + (userPos[1] - hospitalPos[1]) * (step/100)
                        ]);
                        if(step >= 100) clearInterval(interval);
                    }, 100);
                }
                alert(`🚨 Emergency Triggered! Responder: ${data.hospital.name}`);
            });
        });
    }
</script>
</body>
</html>
