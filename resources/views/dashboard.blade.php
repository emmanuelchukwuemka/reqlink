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

<!-- MAIN CONTENT -->
<main class="main-content">
    <header class="top-bar">
        <div>
            <h1 style="font-size: 1.5rem; font-weight: 800;">Command Center</h1>
            <p style="color: var(--grey); font-size: 0.9rem;">Welcome back, {{ Auth::user()->name }}</p>
        </div>
        
        <div class="user-profile">
            <span style="font-size: 0.85rem; font-weight: 600;">Safety Status: Secure</span>
            <div class="avatar-sm">{{ substr(Auth::user()->name, 0, 1) }}</div>
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
            <div id="map" style="height: 350px; border-radius: 12px; border: 1px solid var(--glass-border); background: #0a0a0a;"></div>
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

    // Map Initialization
    let map = L.map('map').setView([6.5244, 3.3792], 13); // Default to Lagos
    L.tileLayer('https://{s}.basemaps.cartocdn.com/dark_all/{z}/{x}/{y}{r}.png', {
        attribution: '&copy; CartoDB'
    }).addTo(map);

    let userMarker;
    let hospitalMarkers = [];

    // Get current location for map
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
        pressTimer = setTimeout(() => {
            triggerEmergency();
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

    function triggerEmergency() {
        if (!navigator.geolocation) {
            alert('Geolocation is not supported by your browser');
            return;
        }

        panicBtn.innerHTML = '<span>...</span><small>Locating</small>';

        navigator.geolocation.getCurrentPosition((position) => {
            const { latitude, longitude } = position.coords;

            fetch('{{ route("emergency.trigger") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({ latitude, longitude })
            })
            .then(response => response.json())
            .then(data => {
                panicBtn.innerHTML = '<span>SOS</span><small>Dispatched</small>';
                
                // Update Map with Responder
                if(data.hospital) {
                    const hospitalPos = [data.hospital.lat, data.hospital.lng];
                    L.marker(hospitalPos, {
                        icon: L.divIcon({
                            html: '<i data-lucide="hospital" style="color:#22c55e"></i>',
                            className: 'custom-div-icon'
                        })
                    }).addTo(map).bindPopup(`<b>${data.hospital.name}</b><br>ETA: ${data.emergency.eta_minutes} mins`).openPopup();
                    
                    // Zoom to fit both
                    const group = new L.featureGroup([userMarker, L.marker(hospitalPos)]);
                    map.fitBounds(group.getBounds().pad(0.5));
                }

                alert(`🚨 EMERGENCY TRIGGERED!\n\nResponder: ${data.hospital.name}\nETA: ${data.emergency.eta_minutes} mins\nContact: ${data.hospital.contact_phone}`);
                
                setTimeout(() => window.location.reload(), 8000);
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Failed to trigger emergency. Please check your connection.');
                panicBtn.innerHTML = '<span>SOS</span><small>Retry</small>';
            });
        }, (error) => {
            alert('Unable to retrieve your location. Please enable location services.');
            panicBtn.innerHTML = '<span>SOS</span><small>Error</small>';
        });
    }
</script>
</body>
</html>
