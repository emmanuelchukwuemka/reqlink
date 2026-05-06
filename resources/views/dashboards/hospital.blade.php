<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Hospital Management | ResQLink</title>
    <link rel="stylesheet" href="{{ asset('css/dashboard.css') }}">
    <script src="https://unpkg.com/lucide@latest"></script>
    <style>
        .management-grid { display: grid; grid-template-columns: 1fr 400px; gap: 30px; }
        .facility-card { background: var(--glass); border: 1px solid var(--glass-border); border-radius: 20px; padding: 30px; }
        .bed-counter { display: flex; align-items: center; justify-content: space-between; padding: 20px; background: rgba(255,255,255,0.03); border-radius: 12px; margin-bottom: 15px; }
        .counter-controls { display: flex; align-items: center; gap: 15px; }
        .count-btn { width: 32px; height: 32px; border-radius: 8px; border: 1px solid var(--glass-border); background: var(--glass); color: white; cursor: pointer; display: flex; align-items: center; justify-content: center; }
        .count-value { font-size: 1.2rem; font-weight: 800; min-width: 30px; text-align: center; }
    </style>
    <script src="{{ asset('js/theme.js') }}"></script>
</head>
<body class="dashboard-layout">

<aside class="sidebar">
    <div class="sidebar-header">
        <div class="auth-logo">
            <img src="{{ asset('images/logo.png') }}" alt="ResQLink" style="height: 50px; width: auto;">
        </div>
        <div class="role-badge" style="background: #22c55e; color: white; margin-top: 10px; font-size: 0.7rem; padding: 4px 12px; border-radius: 20px; font-weight: 800;">MEDICAL FACILITY</div>
    </div>
    
    <nav class="sidebar-nav">
        <a href="#" class="nav-item active"><i data-lucide="building-2"></i> Facility</a>
        <a href="#" class="nav-item"><i data-lucide="users"></i> Patients</a>
        <a href="#" class="nav-item"><i data-lucide="history"></i> Admissions</a>
        <a href="#" class="nav-item"><i data-lucide="settings"></i> Settings</a>
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
            <h1 style="font-size: 1.5rem; font-weight: 800;">Facility Overview</h1>
            <p style="color: var(--grey); font-size: 0.9rem;">{{ $hospital->name }}</p>
        </div>
        
        <div style="display: flex; align-items: center; gap: 20px;">
            <div class="user-profile">
                <div class="user-info">
                    <span>{{ Auth::user()->name }}</span>
                    <small>Administrator</small>
                </div>
                <div class="avatar" style="background: #22c55e">{{ substr($hospital->name, 0, 1) }}</div>
            </div>
        </div>
    </header>

    @if(session('success'))
        <div style="background: rgba(34, 197, 94, 0.1); color: #22c55e; padding: 15px; border-radius: 12px; margin-bottom: 25px; border: 1px solid rgba(34, 197, 94, 0.2);">
            {{ session('success') }}
        </div>
    @endif

    <div class="management-grid">
        <div class="facility-card">
            <h3><i data-lucide="edit-3"></i> Profile Details</h3>
            <form action="{{ route('hospital.update') }}" method="POST" style="margin-top: 25px;">
                @csrf
                <div class="form-grid" style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                    <div class="form-group" style="grid-column: span 2;">
                        <label class="field-label">Official Facility Name</label>
                        <input type="text" name="name" value="{{ $hospital->name }}" required>
                    </div>

                    <div class="form-group">
                        <label class="field-label">Contact Phone</label>
                        <input type="tel" name="contact_phone" value="{{ $hospital->contact_phone }}" required>
                    </div>

                    <div class="form-group">
                        <label class="field-label">Emergency Hotline</label>
                        <input type="tel" name="hotline" value="{{ $hospital->contact_phone }}" placeholder="Optional">
                    </div>

                    <div class="form-group">
                        <label class="field-label">Location Latitude</label>
                        <input type="text" name="lat" value="{{ $hospital->lat }}" required>
                    </div>

                    <div class="form-group">
                        <label class="field-label">Location Longitude</label>
                        <input type="text" name="lng" value="{{ $hospital->lng }}" required>
                    </div>
                </div>

                <div style="margin-top: 30px;">
                    <h4 style="margin-bottom: 15px; font-size: 0.9rem; color: var(--grey); text-transform: uppercase;">Resource Management</h4>
                    
                    <div class="bed-counter">
                        <span>General Admission Beds</span>
                        <div class="counter-controls">
                            <input type="number" name="available_beds" value="{{ $hospital->available_beds }}" style="width: 80px; text-align: center;">
                        </div>
                    </div>

                    <div class="bed-counter">
                        <span>ICU / Critical Care Units</span>
                        <div class="counter-controls">
                            <input type="number" name="icu_beds" value="{{ $hospital->icu_beds }}" style="width: 80px; text-align: center;">
                        </div>
                    </div>
                </div>

                <button type="submit" class="btn-primary" style="margin-top: 30px; width: 100%; padding: 18px; border-radius: 12px;">Save Facility Updates</button>
            </form>
        </div>

        <div>
            <div class="facility-card" style="margin-bottom: 30px;">
                <h3><i data-lucide="map-pin"></i> Live Location</h3>
                <div id="map" style="height: 250px; border-radius: 15px; margin-top: 20px; border: 1px solid var(--glass-border);"></div>
                <p style="font-size: 0.75rem; color: var(--grey); margin-top: 15px;">Tip: Ensure your coordinates are accurate so patients can find you.</p>
            </div>

            <div class="facility-card">
                <h3><i data-lucide="activity"></i> Current Load</h3>
                <div style="margin-top: 20px;">
                    <div style="display: flex; justify-content: space-between; margin-bottom: 8px;">
                        <span style="font-size: 0.85rem;">Bed Occupancy</span>
                        <span style="font-size: 0.85rem; font-weight: 700;">65%</span>
                    </div>
                    <div style="width: 100%; height: 8px; background: rgba(255,255,255,0.05); border-radius: 4px; overflow: hidden;">
                        <div style="width: 65%; height: 100%; background: #22c55e;"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>

<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<script>
    lucide.createIcons();
    
    const lat = {{ $hospital->lat }};
    const lng = {{ $hospital->lng }};
    
    const map = L.map('map').setView([lat, lng], 15);
    L.tileLayer('https://{s}.basemaps.cartocdn.com/dark_all/{z}/{x}/{y}{r}.png').addTo(map);
    L.marker([lat, lng]).addTo(map).bindPopup('{{ $hospital->name }}').openPopup();
</script>
</body>
</html>
