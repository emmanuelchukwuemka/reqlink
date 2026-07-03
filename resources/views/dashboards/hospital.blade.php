<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Hospital Management | ResQLink</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="manifest" href="/manifest.json">
    <link rel="stylesheet" href="/css/dashboard.css">
    <script src="https://unpkg.com/lucide@latest"></script>
    <style>
        .management-grid { display: grid; grid-template-columns: 1fr 400px; gap: 30px; }
        .facility-card { background: var(--glass); border: 1px solid var(--glass-border); border-radius: 20px; padding: 30px; }
        .bed-counter { display: flex; align-items: center; justify-content: space-between; padding: 20px; background: rgba(255,255,255,0.03); border-radius: 12px; margin-bottom: 15px; }
        .counter-controls { display: flex; align-items: center; gap: 15px; }
        .count-btn { width: 32px; height: 32px; border-radius: 8px; border: 1px solid var(--glass-border); background: var(--glass); color: var(--white); cursor: pointer; display: flex; align-items: center; justify-content: center; }
        .count-value { font-size: 1.2rem; font-weight: 800; min-width: 30px; text-align: center; }

        @media (max-width: 900px) {
            .management-grid { grid-template-columns: 1fr; }
        }
        @media (max-width: 768px) {
            .facility-card { padding: 20px; border-radius: 16px; }
            .form-grid { grid-template-columns: 1fr !important; }
            .form-grid .form-group[style*="grid-column"] { grid-column: span 1 !important; }
            .top-bar { flex-wrap: wrap; gap: 8px; }
        }
        @media (max-width: 480px) {
            .facility-card { padding: 16px; }
        }
    </style>
    <script src="/js/theme.js"></script>
</head>
<body class="dashboard-layout">

<div class="sidebar-overlay" id="sidebarOverlay"></div>

<aside class="sidebar">
    <div class="sidebar-header">
        <div class="auth-logo">
            <img src="{{ asset('images/logo.png') }}" alt="ResQLink" style="height: 100px; width: auto;">
        </div>
        <div class="role-badge" style="background: #22c55e; color: white; margin-top: 10px; font-size: 0.7rem; padding: 4px 12px; border-radius: 20px; font-weight: 800;">MEDICAL FACILITY</div>
    </div>
    
    <nav class="sidebar-nav">
        <a href="#" class="nav-item active"><i data-lucide="building-2"></i> Facility</a>
        <a href="#" class="nav-item"><i data-lucide="users"></i> Patients</a>
        <a href="#" class="nav-item"><i data-lucide="history"></i> Admissions</a>
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
            <h1 style="font-size: 1.5rem; font-weight: 800;">Facility Overview</h1>
            <p style="color: var(--grey); font-size: 0.9rem;">{{ $hospital->name }}</p>
        </div>
        
        <div style="display: flex; align-items: center; gap: 20px;">
            @include('partials.lang-switcher')
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
        <div style="display: flex; flex-direction: column; gap: 25px;">
            <!-- INBOUND EMERGENCIES -->
            <div class="facility-card" style="border-left: 4px solid #ef4444;">
                <h3 style="display: flex; justify-content: space-between; align-items: center;">
                    <span style="display: flex; align-items: center; gap: 10px;"><i data-lucide="siren" style="color: #ef4444;"></i> Inbound Alerts</span>
                    <span style="background: rgba(239, 68, 68, 0.1); color: #ef4444; font-size: 0.7rem; padding: 2px 10px; border-radius: 12px; font-weight: 800;">{{ $incomingEmergencies->count() }} ACTIVE</span>
                </h3>
                
                <div style="margin-top: 20px; display: flex; flex-direction: column; gap: 12px;">
                    @forelse($incomingEmergencies as $emergency)
                    <div style="background: rgba(255,255,255,0.03); border: 1px solid var(--glass-border); border-radius: 12px; padding: 15px; display: flex; justify-content: space-between; align-items: center;">
                        <div>
                            <div style="display: flex; align-items: center; gap: 10px;">
                                <span style="font-size: 0.7rem; font-weight: 800; color: {{ $emergency->priority >= 4 ? '#ef4444' : '#f59e0b' }}; text-transform: uppercase;">{{ $emergency->emergencyType->name }}</span>
                                <span style="font-size: 0.6rem; opacity: 0.6;">{{ $emergency->created_at->diffForHumans() }}</span>
                            </div>
                            <h4 style="margin: 5px 0 0 0; font-size: 1rem;">Patient: {{ $emergency->user->name }}</h4>
                            <p style="margin: 3px 0 0 0; font-size: 0.75rem; color: var(--grey);">ETA: {{ $emergency->eta_minutes ?? '--' }} mins | Status: {{ strtoupper($emergency->status) }}</p>
                        </div>
                        
                        @if(!$emergency->hospital_accepted_at)
                        <form action="{{ route('hospital.accept', $emergency->uuid) }}" method="POST">
                            @csrf
                            <button type="submit" class="btn-primary" style="padding: 10px 20px; font-size: 0.8rem; border-radius: 10px; background: #22c55e; border-color: #22c55e;">Accept Patient</button>
                        </form>
                        @else
                        <div style="display: flex; align-items: center; gap: 8px; color: #22c55e; font-size: 0.8rem; font-weight: 700;">
                            <i data-lucide="check-circle" style="width: 16px;"></i> ACCEPTED
                        </div>
                        @endif
                    </div>
                    @empty
                    <div style="text-align: center; padding: 30px; opacity: 0.5;">
                        <i data-lucide="shield-check" style="width: 40px; height: 40px; margin-bottom: 10px;"></i>
                        <p>No active inbound emergencies.</p>
                    </div>
                    @endforelse
                </div>
            </div>

            <!-- PROFILE & RESOURCES -->
            <div class="facility-card">
                <h3><i data-lucide="edit-3"></i> Resource Management</h3>
                <form action="{{ route('hospital.update') }}" method="POST" style="margin-top: 25px;">
                    @csrf
                    <div class="form-grid" style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                        <div class="form-group" style="grid-column: span 2;">
                            <label class="field-label">Official Facility Name</label>
                            <input type="text" name="name" value="{{ $hospital->name }}" required style="width: 100%; background: rgba(255,255,255,0.05); border: 1px solid var(--glass-border); padding: 12px; border-radius: 8px; color: white;">
                        </div>

                        <div class="form-group">
                            <label class="field-label">General Beds (Available)</label>
                            <div class="bed-counter" style="margin-bottom: 0; padding: 5px 15px;">
                                <input type="number" name="available_beds" value="{{ $hospital->available_beds }}" style="background: transparent; border: none; color: white; font-size: 1.2rem; font-weight: 800; width: 60px; text-align: center;">
                                <span style="font-size: 0.7rem; opacity: 0.5;">/ {{ $hospital->total_beds }}</span>
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="field-label">ICU Units (Available)</label>
                            <div class="bed-counter" style="margin-bottom: 0; padding: 5px 15px;">
                                <input type="number" name="icu_beds" value="{{ $hospital->icu_beds }}" style="background: transparent; border: none; color: white; font-size: 1.2rem; font-weight: 800; width: 60px; text-align: center;">
                                <span style="font-size: 0.7rem; opacity: 0.5;">Active</span>
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="field-label">Contact Phone</label>
                            <input type="tel" name="contact_phone" value="{{ $hospital->contact_phone }}" required style="width: 100%; background: rgba(255,255,255,0.05); border: 1px solid var(--glass-border); padding: 12px; border-radius: 8px; color: white;">
                        </div>

                        <div class="form-group">
                            <label class="field-label">Latitude/Longitude</label>
                            <div style="display: flex; gap: 10px;">
                                <input type="text" name="lat" value="{{ $hospital->lat }}" required style="width: 50%; background: rgba(255,255,255,0.05); border: 1px solid var(--glass-border); padding: 12px; border-radius: 8px; color: white; font-size: 0.8rem;">
                                <input type="text" name="lng" value="{{ $hospital->lng }}" required style="width: 50%; background: rgba(255,255,255,0.05); border: 1px solid var(--glass-border); padding: 12px; border-radius: 8px; color: white; font-size: 0.8rem;">
                            </div>
                        </div>
                    </div>

                    <button type="submit" class="btn-primary" style="margin-top: 30px; width: 100%; padding: 15px; border-radius: 12px; font-weight: 700;">UPDATE CAPACITY</button>
                </form>
            </div>
        </div>

        <div style="display: flex; flex-direction: column; gap: 25px;">
            <div class="facility-card">
                <h3><i data-lucide="map-pin"></i> Facility Location</h3>
                <div id="map" style="height: 250px; border-radius: 15px; margin-top: 20px; border: 1px solid var(--glass-border);"></div>
            </div>

            <div class="facility-card">
                <h3><i data-lucide="activity"></i> Capacity Analysis</h3>
                <div style="margin-top: 25px; display: flex; flex-direction: column; gap: 20px;">
                    @php
                        $occupancy = $hospital->total_beds > 0 ? round((($hospital->total_beds - $hospital->available_beds) / $hospital->total_beds) * 100) : 0;
                        $color = $occupancy > 80 ? '#ef4444' : ($occupancy > 50 ? '#f59e0b' : '#22c55e');
                    @endphp
                    <div>
                        <div style="display: flex; justify-content: space-between; margin-bottom: 10px;">
                            <span style="font-size: 0.85rem; opacity: 0.7;">General Bed Occupancy</span>
                            <span style="font-size: 0.85rem; font-weight: 800; color: {{ $color }};">{{ $occupancy }}%</span>
                        </div>
                        <div style="width: 100%; height: 10px; background: rgba(255,255,255,0.05); border-radius: 5px; overflow: hidden;">
                            <div style="width: {{ $occupancy }}%; height: 100%; background: {{ $color }}; transition: width 0.5s ease;"></div>
                        </div>
                    </div>

                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px; margin-top: 10px;">
                        <div style="background: rgba(255,255,255,0.03); padding: 15px; border-radius: 12px; text-align: center; border: 1px solid var(--glass-border);">
                            <span style="font-size: 0.65rem; opacity: 0.6; text-transform: uppercase;">Total Patients</span>
                            <div style="font-size: 1.5rem; font-weight: 800; margin-top: 5px;">{{ $hospital->total_beds - $hospital->available_beds }}</div>
                        </div>
                        <div style="background: rgba(255,255,255,0.03); padding: 15px; border-radius: 12px; text-align: center; border: 1px solid var(--glass-border);">
                            <span style="font-size: 0.65rem; opacity: 0.6; text-transform: uppercase;">Available ICU</span>
                            <div style="font-size: 1.5rem; font-weight: 800; margin-top: 5px; color: #3b82f6;">{{ $hospital->icu_beds }}</div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="facility-card" style="background: linear-gradient(135deg, rgba(34, 197, 94, 0.1) 0%, rgba(15, 23, 42, 0.1) 100%);">
                <h4 style="margin: 0; font-size: 0.9rem; color: #22c55e;">Status: ACTIVE</h4>
                <p style="font-size: 0.75rem; opacity: 0.7; margin-top: 5px;">Your facility is currently receiving emergency routing from the ResQLink dispatch engine.</p>
            </div>

            <!-- BED RESERVATIONS -->
            <div class="facility-card" style="border-left: 4px solid #3b82f6;">
                <h3 style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px;">
                    <span><i data-lucide="bed" style="color:#3b82f6;"></i> Incoming Reservations</span>
                    <span id="reservationBadge" style="background:rgba(59,130,246,0.1);color:#3b82f6;font-size:0.68rem;padding:2px 10px;border-radius:12px;font-weight:800;">LIVE</span>
                </h3>
                <div id="reservationList" style="display: flex; flex-direction: column; gap: 10px;">
                    <div style="text-align:center;padding:20px;opacity:0.5;font-size:0.85rem;">No pending reservations.</div>
                </div>
            </div>
        </div>
    </div>
</main>

<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/leaflet@1.9.4/dist/leaflet.min.css"/>
<script src="https://cdn.jsdelivr.net/npm/leaflet@1.9.4/dist/leaflet.min.js"></script>
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
    })();

    const lat = {{ $hospital->lat }};
    const lng = {{ $hospital->lng }};

    const map = L.map('map').setView([lat, lng], 15);
    L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a>',
        maxZoom: 19
    }).addTo(map);
    const hPane = map.getPane('tilePane');
    if (hPane) hPane.style.filter = 'invert(92%) hue-rotate(180deg) brightness(95%) contrast(90%)';
    L.marker([lat, lng]).addTo(map).bindPopup('{{ $hospital->name }}').openPopup();

    // ── Bed Reservation Polling ──────────────────────────────────────
    const CSRF = document.querySelector('meta[name="csrf-token"]')?.content || '{{ csrf_token() }}';

    function renderReservations(reservations) {
        const list = document.getElementById('reservationList');
        if (!reservations.length) {
            list.innerHTML = '<div style="text-align:center;padding:20px;opacity:0.5;font-size:0.85rem;">No pending reservations.</div>';
            return;
        }
        const badge = document.getElementById('reservationBadge');
        badge.textContent = reservations.length + ' PENDING';
        list.innerHTML = reservations.map(r => `
            <div style="background:rgba(255,255,255,0.03);border:1px solid rgba(59,130,246,0.2);border-radius:10px;padding:14px;">
                <div style="display:flex;justify-content:space-between;align-items:flex-start;margin-bottom:10px;">
                    <div>
                        <p style="margin:0;font-weight:700;font-size:0.88rem;">Patient: ${r.patient}</p>
                        <p style="margin:2px 0 0;font-size:0.73rem;color:var(--grey);">Unit: ${r.responder} · ETA: ${r.eta_minutes ?? '?'} mins</p>
                    </div>
                    <span style="font-size:0.65rem;font-weight:800;padding:2px 8px;border-radius:6px;background:${r.status==='confirmed'?'rgba(34,197,94,0.1)':'rgba(245,158,11,0.1)'};color:${r.status==='confirmed'?'#22c55e':'#f59e0b'};">${r.status.toUpperCase()}</span>
                </div>
                ${r.status === 'pending' ? `
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:8px;">
                    <button onclick="respondReservation(${r.id},'confirmed')" style="padding:8px;background:rgba(34,197,94,0.15);color:#22c55e;border:1px solid rgba(34,197,94,0.3);border-radius:8px;cursor:pointer;font-weight:700;font-size:0.78rem;">Confirm</button>
                    <button onclick="respondReservation(${r.id},'declined')" style="padding:8px;background:rgba(229,9,20,0.08);color:var(--red);border:1px solid rgba(229,9,20,0.2);border-radius:8px;cursor:pointer;font-weight:700;font-size:0.78rem;">Decline</button>
                </div>` : ''}
            </div>
        `).join('');
    }

    function respondReservation(id, action) {
        fetch(`/bed/respond/${id}`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF },
            body: JSON.stringify({ action })
        }).then(() => pollReservations()).catch(() => {});
    }

    function pollReservations() {
        fetch('/bed/pending')
            .then(r => r.json())
            .then(data => renderReservations(data))
            .catch(() => {});
    }

    pollReservations();
    setInterval(pollReservations, 10000);
</script>
<script src="/js/pwa.js" defer></script>
@include('partials.profile-modal')
</body>
</html>
