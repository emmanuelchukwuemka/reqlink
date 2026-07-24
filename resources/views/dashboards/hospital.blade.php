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
        .tab-pane { display: none; }
        .tab-pane.active { display: block; }
        .nav-item { cursor: pointer; }
        .management-grid { display: grid; grid-template-columns: 1fr 400px; gap: 30px; }

        /* ── Navy sidebar (fixed brand chrome — stays navy in both themes) ── */
        .dashboard-layout .sidebar { background: #0f1c3d; border-right: 1px solid rgba(255,255,255,0.08); }
        .dashboard-layout .sidebar-header { border-bottom: 1px solid rgba(255,255,255,0.08); }
        .dashboard-layout .sidebar-footer { border-top: 1px solid rgba(255,255,255,0.08); }
        .dashboard-layout .nav-item { color: rgba(255,255,255,0.6); }
        .dashboard-layout .nav-item:hover { background: rgba(255,255,255,0.06); color: #fff; }
        .dashboard-layout .nav-item.active { background: rgba(59,130,246,0.25); color: #fff; }
        .dashboard-layout .nav-item.active i { color: #93c5fd; }
        .dashboard-layout .nav-item[style*="var(--red)"] { color: #f87171 !important; }

        .verify-card { margin: 0 0 14px; padding: 14px 16px; border-radius: 14px; }
        .verify-card.pending { background: rgba(245,158,11,0.12); border: 1px solid rgba(245,158,11,0.25); }
        .verify-card.live { background: rgba(34,197,94,0.12); border: 1px solid rgba(34,197,94,0.25); }
        .verify-card strong { display: flex; align-items: center; gap: 6px; font-size: 0.72rem; text-transform: uppercase; letter-spacing: 0.5px; }
        .verify-card.pending strong { color: #fbbf24; }
        .verify-card.live strong { color: #4ade80; }
        .verify-card p { margin: 6px 0 0; font-size: 0.74rem; color: rgba(255,255,255,0.55); line-height: 1.5; }

        /* ── Cards (solid, theme-aware via --dark) ── */
        .facility-card { background: var(--dark); border: 1px solid var(--glass-border); border-radius: 18px; padding: 28px; box-shadow: 0 1px 2px rgba(0,0,0,0.04); }
        :root.light-mode .facility-card { box-shadow: 0 1px 3px rgba(15,23,42,0.07), 0 1px 2px rgba(15,23,42,0.04); }

        /* ── Stat tiles row ── */
        .hstats-row { display: grid; grid-template-columns: repeat(4, 1fr); gap: 20px; margin-bottom: 24px; }
        .hstat-tile { background: var(--dark); border: 1px solid var(--glass-border); border-radius: 18px; padding: 20px 22px; box-shadow: 0 1px 2px rgba(0,0,0,0.04); }
        :root.light-mode .hstat-tile { box-shadow: 0 1px 3px rgba(15,23,42,0.07), 0 1px 2px rgba(15,23,42,0.04); }
        .hstat-top { display: flex; align-items: center; gap: 14px; margin-bottom: 14px; }
        .hstat-chip { width: 44px; height: 44px; border-radius: 12px; display: flex; align-items: center; justify-content: center; flex-shrink: 0; }
        .hstat-chip.blue   { background: rgba(59,130,246,0.12); color: #3b82f6; }
        .hstat-chip.green  { background: rgba(34,197,94,0.12); color: #22c55e; }
        .hstat-chip.purple { background: rgba(139,92,246,0.12); color: #8b5cf6; }
        .hstat-chip.orange { background: rgba(245,158,11,0.12); color: #f59e0b; }
        .hstat-label { font-size: 0.8rem; color: var(--text-muted); font-weight: 600; }
        .hstat-value { font-size: 1.7rem; font-weight: 800; color: var(--text-main); line-height: 1.2; }
        .hstat-sub { font-size: 0.72rem; color: var(--text-muted); margin-top: 2px; }
        .hstat-spark { width: 100%; height: 28px; margin-top: 14px; display: block; }

        .bed-counter { display: flex; align-items: center; justify-content: space-between; padding: 20px; background: var(--glass); border-radius: 12px; margin-bottom: 15px; }
        .counter-controls { display: flex; align-items: center; gap: 15px; }
        .count-btn { width: 32px; height: 32px; border-radius: 8px; border: 1px solid var(--glass-border); background: var(--glass); color: var(--text-main); cursor: pointer; display: flex; align-items: center; justify-content: center; }
        .count-value { font-size: 1.2rem; font-weight: 800; min-width: 30px; text-align: center; }
        .locate-btn { display: flex; align-items: center; gap: 6px; background: rgba(59,130,246,0.1); color: #3b82f6; border: 1px solid rgba(59,130,246,0.3); padding: 8px 14px; border-radius: 8px; font-size: 0.78rem; font-weight: 700; cursor: pointer; white-space: nowrap; }
        .locate-btn:hover { background: rgba(59,130,246,0.18); }
        .map-empty-state { margin-top: 20px; padding: 30px 20px; text-align: center; background: var(--glass); border: 1px dashed var(--glass-border); border-radius: 15px; }
        .map-empty-state p { margin: 10px 0 16px; font-size: 0.82rem; color: var(--text-muted); }
        .map-empty-state .locate-btn { margin: 0 auto; }

        .field-input { width: 100%; background: var(--glass); border: 1px solid var(--glass-border); padding: 12px 14px; border-radius: 10px; color: var(--text-main); font-size: 0.88rem; font-family: inherit; box-sizing: border-box; }

        .patient-row { background: var(--glass); border: 1px solid var(--glass-border); border-radius: 12px; padding: 15px; margin-bottom: 12px; }
        .patient-row-top { display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 10px; }
        .status-pill { font-size: 0.68rem; font-weight: 800; text-transform: uppercase; padding: 3px 10px; border-radius: 12px; }
        .verified-badge { display: inline-flex; align-items: center; gap: 4px; background: rgba(59,130,246,0.12); color: #3b82f6; font-size: 0.65rem; font-weight: 800; padding: 3px 10px; border-radius: 12px; text-transform: uppercase; }
        .unverified-badge { display: inline-flex; align-items: center; gap: 4px; background: rgba(245,158,11,0.12); color: #f59e0b; font-size: 0.65rem; font-weight: 800; padding: 3px 10px; border-radius: 12px; text-transform: uppercase; }
        .mini-btn { padding: 8px 14px; font-size: 0.75rem; font-weight: 700; border-radius: 8px; cursor: pointer; border: 1px solid transparent; }
        .case-notes { margin-top: 12px; padding: 12px; background: var(--glass); border-radius: 8px; font-size: 0.8rem; }

        /* ── Notification bell + theme toggle ── */
        .bell-btn { position: relative; width: 38px; height: 38px; border-radius: 10px; border: 1px solid var(--glass-border); background: var(--glass); color: var(--text-main); display: flex; align-items: center; justify-content: center; cursor: pointer; }
        .bell-dot { position: absolute; top: 6px; right: 6px; width: 8px; height: 8px; border-radius: 50%; background: var(--red); border: 1.5px solid var(--dark); }
        .theme-toggle { width: 38px; height: 38px; border-radius: 10px; border: 1px solid var(--glass-border); background: var(--glass); color: var(--text-main); display: flex; align-items: center; justify-content: center; cursor: pointer; }
        .theme-toggle:hover { background: rgba(59,130,246,0.1); border-color: rgba(59,130,246,0.3); }
        .profile-chevron { color: var(--text-muted); }

        @media (max-width: 1180px) { .hstats-row { grid-template-columns: repeat(2, 1fr); } }
        @media (max-width: 900px) {
            .management-grid { grid-template-columns: 1fr; }
        }
        @media (max-width: 768px) {
            .hstats-row { grid-template-columns: 1fr 1fr; gap: 12px; }
            .facility-card { padding: 20px; border-radius: 16px; }
            .form-grid { grid-template-columns: 1fr !important; }
            .form-grid .form-group[style*="grid-column"] { grid-column: span 1 !important; }
            .top-bar { flex-wrap: wrap; gap: 8px; }
        }
        @media (max-width: 480px) {
            .facility-card { padding: 16px; }
            .hstats-row { grid-template-columns: 1fr; }
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
        <a href="#" class="nav-item active" data-tab="facility"><i data-lucide="building-2"></i> Facility</a>
        <a href="#" class="nav-item" data-tab="patients"><i data-lucide="users"></i> Patients</a>
        <a href="#" class="nav-item" data-tab="admissions"><i data-lucide="history"></i> Admissions</a>
        <a href="#" class="nav-item" data-tab="reservations"><i data-lucide="bed"></i> Reservations</a>
        <a href="#" class="nav-item" data-tab="maptab"><i data-lucide="map"></i> Map</a>
        <a href="{{ route('settings') }}" class="nav-item"><i data-lucide="settings"></i> Settings</a>
    </nav>

    <div class="sidebar-footer">
        @if(Auth::user()->is_verified)
        <div class="verify-card live">
            <strong><i data-lucide="badge-check" style="width:13px;height:13px;"></i> Verified Facility</strong>
            <p>Your facility is live and receiving emergency routing.</p>
        </div>
        @else
        <div class="verify-card pending">
            <strong><i data-lucide="clock" style="width:13px;height:13px;"></i> Pending Verification</strong>
            <p>Complete your facility verification to go live.</p>
        </div>
        @endif
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
            <h1 id="pageTitle" style="font-size: 1.5rem; font-weight: 800;">Facility</h1>
            <p style="color: var(--grey); font-size: 0.9rem;">{{ $hospital->name }}</p>
        </div>

        <div style="display: flex; align-items: center; gap: 14px;">
            @include('partials.lang-switcher')
            <button id="themeToggle" class="theme-toggle" aria-label="Toggle Dark Mode">
                <i data-lucide="sun" id="themeIcon"></i>
            </button>
            <button type="button" class="bell-btn" aria-label="Alerts">
                <i data-lucide="bell" style="width:18px;height:18px;"></i>
                @if($incomingEmergencies->count() > 0)
                <span class="bell-dot"></span>
                @endif
            </button>
            <div class="user-profile">
                <div class="user-info">
                    <span>{{ Auth::user()->name }}</span>
                    <small>Administrator</small>
                </div>
                <div class="avatar" style="background: #22c55e">{{ substr($hospital->name, 0, 1) }}</div>
                <i data-lucide="chevron-down" class="profile-chevron" style="width:16px;height:16px;"></i>
            </div>
        </div>
    </header>

    @include('partials.announcement-banner')

    @if(session('success'))
        <div style="background: rgba(34, 197, 94, 0.1); color: #22c55e; padding: 15px; border-radius: 12px; margin-bottom: 25px; border: 1px solid rgba(34, 197, 94, 0.2);">
            {{ session('success') }}
        </div>
    @endif

    <div id="facility" class="tab-pane active">
    @php
        $admittedCount = $hospitalEmergencies->whereNotNull('hospital_accepted_at')->where('status', '!=', 'resolved')->count();
        $occupancy = $hospital->total_beds > 0 ? round((($hospital->total_beds - $hospital->available_beds) / $hospital->total_beds) * 100) : 0;
    @endphp

    <div class="hstats-row">
        <div class="hstat-tile">
            <div class="hstat-top">
                <div class="hstat-chip blue"><i data-lucide="bed" style="width:20px;height:20px;"></i></div>
                <div><div class="hstat-label">General Beds (Available)</div></div>
            </div>
            <div class="hstat-value">{{ $hospital->available_beds }}</div>
            <div class="hstat-sub">/ {{ $hospital->total_beds }} Total</div>
            <svg class="hstat-spark" viewBox="0 0 120 28" style="color:#3b82f6" fill="none" preserveAspectRatio="none">
                <path d="M2 20 C 14 22, 22 10, 34 14 S 54 24, 66 16 S 86 6, 98 12 S 112 18, 118 14" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
            </svg>
        </div>
        <div class="hstat-tile">
            <div class="hstat-top">
                <div class="hstat-chip green"><i data-lucide="activity" style="width:20px;height:20px;"></i></div>
                <div><div class="hstat-label">ICU Units (Available)</div></div>
            </div>
            <div class="hstat-value">{{ $hospital->icu_beds }}</div>
            <div class="hstat-sub">Active</div>
            <svg class="hstat-spark" viewBox="0 0 120 28" style="color:#22c55e" fill="none" preserveAspectRatio="none">
                <path d="M2 14 C 14 8, 22 22, 34 18 S 54 6, 66 12 S 86 22, 98 16 S 112 10, 118 14" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
            </svg>
        </div>
        <div class="hstat-tile">
            <div class="hstat-top">
                <div class="hstat-chip purple"><i data-lucide="users" style="width:20px;height:20px;"></i></div>
                <div><div class="hstat-label">Total Patients</div></div>
            </div>
            <div class="hstat-value">{{ $admittedCount }}</div>
            <div class="hstat-sub">Currently Admitted</div>
            <svg class="hstat-spark" viewBox="0 0 120 28" style="color:#8b5cf6" fill="none" preserveAspectRatio="none">
                <path d="M2 18 C 14 16, 22 8, 34 12 S 54 20, 66 10 S 86 14, 98 8 S 112 16, 118 10" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
            </svg>
        </div>
        <div class="hstat-tile">
            <div class="hstat-top">
                <div class="hstat-chip orange"><i data-lucide="gauge" style="width:20px;height:20px;"></i></div>
                <div><div class="hstat-label">Bed Occupancy</div></div>
            </div>
            <div class="hstat-value">{{ $occupancy }}%</div>
            <div class="hstat-sub">Overall</div>
            <svg class="hstat-spark" viewBox="0 0 120 28" style="color:#f59e0b" fill="none" preserveAspectRatio="none">
                <path d="M2 22 C 14 18, 22 20, 34 12 S 54 8, 66 14 S 86 10, 98 6 S 112 12, 118 8" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
            </svg>
        </div>
    </div>

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
                    <div class="patient-row">
                        <div class="patient-row-top">
                            <div>
                                <div style="display: flex; align-items: center; gap: 10px;">
                                    <span style="font-size: 0.7rem; font-weight: 800; color: {{ $emergency->priority >= 4 ? '#ef4444' : '#f59e0b' }}; text-transform: uppercase;">{{ $emergency->emergencyType->name }}</span>
                                    <span style="font-size: 0.6rem; opacity: 0.6;">{{ $emergency->created_at->diffForHumans() }}</span>
                                </div>
                                <h4 style="margin: 5px 0 0 0; font-size: 1rem;">Patient: {{ $emergency->user->name }}</h4>
                                <p style="margin: 3px 0 0 0; font-size: 0.75rem; color: var(--grey);">ETA: {{ $emergency->eta_minutes ?? '--' }} mins | Status: {{ strtoupper($emergency->status) }}</p>
                            </div>

                            <div style="display: flex; gap: 8px; flex-wrap: wrap;">
                                @if(!$emergency->hospital_accepted_at)
                                <button type="button" class="mini-btn" style="background: #22c55e; color: white;" onclick="acceptPatient('{{ $emergency->uuid }}')">Accept Patient</button>
                                <button type="button" class="mini-btn" style="background: rgba(229,9,20,0.08); color: var(--red); border-color: rgba(229,9,20,0.2);" onclick="declinePatient('{{ $emergency->uuid }}')">Decline</button>
                                @else
                                <div style="display: flex; align-items: center; gap: 8px; color: #22c55e; font-size: 0.8rem; font-weight: 700;">
                                    <i data-lucide="check-circle" style="width: 16px;"></i> ACCEPTED
                                </div>
                                @endif
                                <button type="button" class="mini-btn" style="background: rgba(34,197,94,0.15); color: #22c55e; border-color: rgba(34,197,94,0.3);" onclick="joinPatientChat('{{ $emergency->uuid }}', '{{ addslashes($emergency->user->name ?? 'Patient') }}')">Chat</button>
                                <button type="button" class="mini-btn" style="background: rgba(59,130,246,0.12); color: #3b82f6; border-color: rgba(59,130,246,0.3);" onclick="requestDoctorConsult('{{ $emergency->uuid }}')">Doctor Consult</button>
                            </div>
                        </div>

                        @if(is_array($emergency->triage_data))
                        <div class="case-notes">
                            <p style="font-size: 0.68rem; color: var(--red); text-transform: uppercase; font-weight: 800; margin: 0 0 6px;">AI Triage: {{ strtoupper($emergency->triage_data['protocol'] ?? 'General') }}</p>
                            @foreach(($emergency->triage_data['diagnostics'] ?? []) as $diag)
                            <p style="margin: 3px 0;">• {{ $diag['question'] ?? '' }}: <strong>{{ strtoupper($diag['answer'] ?? '') }}</strong></p>
                            @endforeach
                        </div>
                        @endif

                        @if($emergency->responder_notes)
                        <div class="case-notes">
                            <p style="font-size: 0.68rem; color: #3b82f6; text-transform: uppercase; font-weight: 800; margin: 0 0 6px;">Ambulance Handoff Notes</p>
                            <p style="margin: 0;">{{ $emergency->responder_notes }}</p>
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
                <form id="hospitalUpdateForm" action="{{ route('hospital.update') }}" method="POST" style="margin-top: 25px;">
                    @csrf
                    <div class="form-grid" style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                        <div class="form-group" style="grid-column: span 2;">
                            <label class="field-label">Official Facility Name</label>
                            <input type="text" name="name" value="{{ $hospital->name }}" required class="field-input">
                        </div>

                        <div class="form-group">
                            <label class="field-label">General Beds (Available)</label>
                            <div class="bed-counter" style="margin-bottom: 0; padding: 5px 15px;">
                                <input type="number" name="available_beds" value="{{ $hospital->available_beds }}" style="background: transparent; border: none; color: var(--text-main); font-size: 1.2rem; font-weight: 800; width: 60px; text-align: center;">
                                <span style="font-size: 0.7rem; opacity: 0.5;">/ {{ $hospital->total_beds }}</span>
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="field-label">ICU Units (Available)</label>
                            <div class="bed-counter" style="margin-bottom: 0; padding: 5px 15px;">
                                <input type="number" name="icu_beds" value="{{ $hospital->icu_beds }}" style="background: transparent; border: none; color: var(--text-main); font-size: 1.2rem; font-weight: 800; width: 60px; text-align: center;">
                                <span style="font-size: 0.7rem; opacity: 0.5;">Active</span>
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="field-label">Contact Phone</label>
                            <input type="tel" name="contact_phone" value="{{ $hospital->contact_phone }}" required class="field-input">
                        </div>

                        <div class="form-group">
                            <label class="field-label">Latitude/Longitude</label>
                            <div style="display: flex; gap: 10px;">
                                <input type="text" name="lat" id="latInput" value="{{ $hospital->lat }}" required class="field-input" style="width: 50%; font-size: 0.8rem;">
                                <input type="text" name="lng" id="lngInput" value="{{ $hospital->lng }}" required class="field-input" style="width: 50%; font-size: 0.8rem;">
                            </div>
                            <button type="button" class="locate-btn" style="margin-top: 10px;" onclick="useMyLocation()">
                                <i data-lucide="crosshair" style="width: 14px; height: 14px;"></i> Use My Current Location
                            </button>
                        </div>

                        <div class="form-group" style="grid-column: span 2;">
                            <label class="field-label">Specialties (comma-separated)</label>
                            <input type="text" name="specialties" value="{{ implode(', ', $hospital->specialties ?? []) }}" placeholder="e.g. Cardiology, Pediatrics, Trauma" class="field-input">
                        </div>

                        <div class="form-group" style="grid-column: span 2;">
                            <label class="field-label">Resources (one per line: name: quantity)</label>
                            <textarea name="resources" placeholder="O+ blood: 10&#10;Ventilators: 3" style="min-height: 80px;" class="field-input">{{ collect($hospital->resources ?? [])->map(fn($v, $k) => "$k: $v")->implode("\n") }}</textarea>
                        </div>
                    </div>

                    <button type="submit" class="btn-primary" style="margin-top: 30px; width: 100%; padding: 15px; border-radius: 12px; font-weight: 700;">UPDATE CAPACITY</button>
                </form>
            </div>
        </div>

        <div style="display: flex; flex-direction: column; gap: 25px;">
            <div class="facility-card">
                <h3><i data-lucide="map-pin"></i> Facility Location</h3>
                <div id="mapPlaceholder" class="map-empty-state" style="{{ $hospital->hasLocation() ? 'display:none;' : '' }}">
                    <i data-lucide="map-pin-off" style="width: 32px; height: 32px; opacity: 0.5;"></i>
                    <p>Your facility location hasn't been set yet, so the map can't show where you are.</p>
                    <button type="button" class="locate-btn" onclick="setInitialHospitalLocation()">
                        <i data-lucide="crosshair" style="width: 14px; height: 14px;"></i> Use My Current Location
                    </button>
                </div>
                <div id="map" style="height: 250px; border-radius: 15px; margin-top: 20px; border: 1px solid var(--glass-border); {{ $hospital->hasLocation() ? '' : 'display:none;' }}"></div>
            </div>

            <div class="facility-card">
                <h3><i data-lucide="activity"></i> Capacity Analysis</h3>
                <div style="margin-top: 25px; display: flex; flex-direction: column; gap: 20px;">
                    @php
                        $color = $occupancy > 80 ? '#ef4444' : ($occupancy > 50 ? '#f59e0b' : '#22c55e');
                    @endphp
                    <div>
                        <div style="display: flex; justify-content: space-between; margin-bottom: 10px;">
                            <span style="font-size: 0.85rem; color: var(--text-muted);">General Bed Occupancy</span>
                            <span style="font-size: 0.85rem; font-weight: 800; color: {{ $color }};">{{ $occupancy }}%</span>
                        </div>
                        <div style="width: 100%; height: 10px; background: var(--glass); border-radius: 5px; overflow: hidden;">
                            <div style="width: {{ $occupancy }}%; height: 100%; background: {{ $color }}; transition: width 0.5s ease;"></div>
                        </div>
                    </div>

                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px; margin-top: 10px;">
                        <div style="background: var(--glass); padding: 15px; border-radius: 12px; text-align: center; border: 1px solid var(--glass-border);">
                            <span style="font-size: 0.65rem; color: var(--text-muted); text-transform: uppercase;">Total Patients</span>
                            <div style="font-size: 1.5rem; font-weight: 800; margin-top: 5px; color: var(--text-main);">{{ $hospital->total_beds - $hospital->available_beds }}</div>
                        </div>
                        <div style="background: var(--glass); padding: 15px; border-radius: 12px; text-align: center; border: 1px solid var(--glass-border);">
                            <span style="font-size: 0.65rem; color: var(--text-muted); text-transform: uppercase;">Available ICU</span>
                            <div style="font-size: 1.5rem; font-weight: 800; margin-top: 5px; color: #3b82f6;">{{ $hospital->icu_beds }}</div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="facility-card" style="background: linear-gradient(135deg, rgba(34, 197, 94, 0.1) 0%, rgba(15, 23, 42, 0.1) 100%);">
                <h4 style="margin: 0; font-size: 0.9rem; color: #22c55e;">Status: ACTIVE</h4>
                <p style="font-size: 0.75rem; opacity: 0.7; margin-top: 5px;">Your facility is currently receiving emergency routing from the ResQLink dispatch engine.</p>
            </div>
        </div>
    </div>
    </div>{{-- end #facility --}}

    <!-- PATIENTS TAB -->
    <div id="patients" class="tab-pane">
        <div class="facility-card" style="margin-bottom: 24px;">
            <h3><i data-lucide="user-plus"></i> Add Walk-in Patient</h3>
            <form action="{{ route('hospital.patients.store') }}" method="POST" style="margin-top: 20px;">
                @csrf
                <div class="form-grid" style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px;">
                    <div class="form-group">
                        <label class="field-label">Patient Name</label>
                        <input type="text" name="name" required class="field-input" placeholder="Full name">
                    </div>
                    <div class="form-group">
                        <label class="field-label">Phone (optional)</label>
                        <input type="tel" name="phone" class="field-input" placeholder="08012345678">
                    </div>
                    <div class="form-group">
                        <label class="field-label">Reason (optional)</label>
                        <input type="text" name="reason" class="field-input" placeholder="e.g. Fracture, Malaria">
                    </div>
                    <div class="form-group">
                        <label class="field-label">Bed Type</label>
                        <select name="bed_type" class="field-input">
                            <option value="general">General</option>
                            <option value="icu">ICU</option>
                        </select>
                    </div>
                    <div class="form-group" style="grid-column: span 2;">
                        <label class="field-label">Notes (optional)</label>
                        <textarea name="notes" class="field-input" style="min-height: 60px;" placeholder="Any additional notes"></textarea>
                    </div>
                </div>
                <button type="submit" class="btn-primary" style="margin-top: 20px; padding: 13px 22px; border-radius: 10px; font-weight: 700;">Admit Patient</button>
            </form>
        </div>

        @if($hospitalPatients->isNotEmpty())
        <div class="facility-card" style="margin-bottom: 24px;">
            <h3><i data-lucide="clipboard-list"></i> Walk-in Patients</h3>
            <div style="margin-top: 20px; display: flex; flex-direction: column;">
                @foreach($hospitalPatients as $patient)
                <div class="patient-row" id="walkin-row-{{ $patient->id }}">
                    <div class="patient-row-top">
                        <div>
                            <h4 style="margin: 0; font-size: 0.95rem;">{{ $patient->name }}</h4>
                            <p style="margin: 4px 0 0; font-size: 0.78rem; color: var(--grey);">
                                {{ $patient->reason ?: 'General' }} · {{ strtoupper($patient->bed_type) }} bed · Admitted {{ $patient->admitted_at?->format('M d, Y g:i A') }}
                            </p>
                        </div>
                        <div style="display: flex; align-items: center; gap: 8px;">
                            <span class="status-pill" style="color: {{ $patient->status === 'discharged' ? 'var(--text-muted)' : '#22c55e' }}; background: {{ $patient->status === 'discharged' ? 'var(--glass)' : 'rgba(34,197,94,0.1)' }};">{{ strtoupper($patient->status) }}</span>
                            @if($patient->status === 'admitted')
                            <button type="button" class="mini-btn" style="background: rgba(34,197,94,0.15); color: #22c55e; border-color: rgba(34,197,94,0.3);" onclick="dischargeManualPatient({{ $patient->id }})">Discharge</button>
                            @endif
                            <button type="button" class="mini-btn" style="background: rgba(229,9,20,0.08); color: var(--red); border-color: rgba(229,9,20,0.2);" onclick="deleteManualPatient({{ $patient->id }})">Remove</button>
                        </div>
                    </div>
                    @if($patient->notes)
                    <div class="case-notes"><p style="margin:0;">{{ $patient->notes }}</p></div>
                    @endif
                </div>
                @endforeach
            </div>
        </div>
        @endif

        <div class="facility-card">
            <h3><i data-lucide="users"></i> Patients Routed to This Facility</h3>
            <div style="margin-top: 20px; display: flex; flex-direction: column;">
                @forelse($hospitalEmergencies as $emergency)
                @php
                    $statusColors = [
                        'pending' => ['var(--red)', 'rgba(229,9,20,0.1)'],
                        'resolved' => ['#22c55e', 'rgba(34,197,94,0.1)'],
                    ];
                    [$sColor, $sBg] = $statusColors[$emergency->status] ?? ['var(--text-muted)', 'var(--glass)'];
                @endphp
                <div class="patient-row">
                    <div class="patient-row-top">
                        <div>
                            <h4 style="margin: 0; font-size: 0.95rem;">{{ $emergency->user->name ?? 'Unknown Patient' }}</h4>
                            <p style="margin: 4px 0 0; font-size: 0.78rem; color: var(--grey);">
                                {{ $emergency->emergencyType->name ?? 'General Emergency' }} · {{ $emergency->created_at->format('M d, Y g:i A') }}
                            </p>
                        </div>
                        <div style="display: flex; align-items: center; gap: 8px;">
                            @if($emergency->hospital_decline_reason)
                            <span class="status-pill" style="color: var(--grey); background: rgba(107,114,128,0.1);">DECLINED</span>
                            @else
                            <span class="status-pill" style="color: {{ $sColor }}; background: {{ $sBg }};">{{ strtoupper($emergency->status) }}</span>
                            @endif
                            <button type="button" class="mini-btn" style="background: rgba(59,130,246,0.12); color: #3b82f6; border-color: rgba(59,130,246,0.3);" onclick="requestDoctorConsult('{{ $emergency->uuid }}')">Doctor Consult</button>
                        </div>
                    </div>
                    @if($emergency->hospital_decline_reason)
                    <div class="case-notes"><p style="margin:0;"><strong>Decline reason:</strong> {{ $emergency->hospital_decline_reason }}</p></div>
                    @endif
                    @if($emergency->responder_notes)
                    <div class="case-notes"><p style="margin:0;"><strong>Handoff notes:</strong> {{ $emergency->responder_notes }}</p></div>
                    @endif
                </div>
                @empty
                <div style="text-align: center; padding: 30px; opacity: 0.5;">
                    <i data-lucide="users" style="width: 40px; height: 40px; margin-bottom: 10px;"></i>
                    <p>No patients have been routed to this facility yet.</p>
                </div>
                @endforelse
            </div>
        </div>
    </div>

    <!-- ADMISSIONS TAB -->
    <div id="admissions" class="tab-pane">
        <div class="facility-card">
            <h3 style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 10px;">
                <span><i data-lucide="history"></i> Admissions Log</span>
                <a href="{{ route('hospital.export-admissions') }}" class="mini-btn" style="background: rgba(59,130,246,0.1); color: #3b82f6; border-color: rgba(59,130,246,0.3); text-decoration: none;">
                    <i data-lucide="download" style="width: 12px; height: 12px; vertical-align: text-bottom;"></i> Export CSV
                </a>
            </h3>
            <p style="margin: 8px 0 0; font-size: 0.78rem; color: var(--grey);">Includes patients routed via ResQLink and walk-ins added from the Patients tab.</p>
            <div style="margin-top: 20px; display: flex; flex-direction: column;">
                @php
                    $admissions = $hospitalEmergencies->whereNotNull('hospital_accepted_at')->sortByDesc('hospital_accepted_at');
                    $combinedAdmissions = collect();
                    foreach ($admissions as $e) {
                        $combinedAdmissions->push((object) [
                            'name' => $e->user->name ?? 'Unknown Patient',
                            'detail' => $e->emergencyType->name ?? 'General Emergency',
                            'admitted_at' => \Illuminate\Support\Carbon::parse($e->hospital_accepted_at),
                            'discharged_at' => ($e->status === 'resolved' && $e->resolved_at) ? \Illuminate\Support\Carbon::parse($e->resolved_at) : null,
                            'is_resolved' => $e->status === 'resolved',
                            'notes' => $e->responder_notes,
                            'discharge_action' => "dischargePatient('{$e->uuid}')",
                        ]);
                    }
                    foreach ($hospitalPatients as $p) {
                        $combinedAdmissions->push((object) [
                            'name' => $p->name,
                            'detail' => ($p->reason ?: 'General') . ' · ' . strtoupper($p->bed_type) . ' bed (Walk-in)',
                            'admitted_at' => $p->admitted_at,
                            'discharged_at' => $p->discharged_at,
                            'is_resolved' => $p->status === 'discharged',
                            'notes' => $p->notes,
                            'discharge_action' => "dischargeManualPatient({$p->id})",
                        ]);
                    }
                    $combinedAdmissions = $combinedAdmissions->sortByDesc('admitted_at');
                @endphp
                @forelse($combinedAdmissions as $item)
                <div class="patient-row">
                    <div class="patient-row-top">
                        <div>
                            <h4 style="margin: 0; font-size: 0.95rem;">{{ $item->name }}</h4>
                            <p style="margin: 4px 0 0; font-size: 0.78rem; color: var(--grey);">
                                {{ $item->detail }} · Admitted {{ $item->admitted_at?->format('M d, Y g:i A') }}
                                @if($item->discharged_at)
                                    · Discharged {{ $item->discharged_at->format('M d, Y g:i A') }}
                                @endif
                            </p>
                        </div>
                        <div style="display: flex; align-items: center; gap: 8px;">
                            <span class="status-pill" style="color: {{ $item->is_resolved ? '#22c55e' : 'var(--text-muted)' }}; background: {{ $item->is_resolved ? 'rgba(34,197,94,0.1)' : 'var(--glass)' }};">{{ $item->is_resolved ? 'DISCHARGED' : 'ADMITTED' }}</span>
                            @if(!$item->is_resolved)
                            <button type="button" class="mini-btn" style="background: rgba(34,197,94,0.15); color: #22c55e; border-color: rgba(34,197,94,0.3);" onclick="{{ $item->discharge_action }}">Discharge</button>
                            @endif
                        </div>
                    </div>
                    @if($item->notes)
                    <div class="case-notes"><p style="margin:0;">{{ $item->notes }}</p></div>
                    @endif
                </div>
                @empty
                <div style="text-align: center; padding: 30px; opacity: 0.5;">
                    <i data-lucide="history" style="width: 40px; height: 40px; margin-bottom: 10px;"></i>
                    <p>No admissions recorded yet.</p>
                </div>
                @endforelse
            </div>
        </div>
    </div>

    <!-- RESERVATIONS TAB -->
    <div id="reservations" class="tab-pane">
        <div class="facility-card" style="margin-bottom: 24px;">
            <h3><i data-lucide="calendar-plus"></i> Reserve a Bed</h3>
            <form action="{{ route('hospital.reservations.store') }}" method="POST" style="margin-top: 20px;">
                @csrf
                <div class="form-grid" style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px;">
                    <div class="form-group">
                        <label class="field-label">Patient Name</label>
                        <input type="text" name="patient_name" required class="field-input" placeholder="Full name">
                    </div>
                    <div class="form-group">
                        <label class="field-label">Bed Type</label>
                        <select name="bed_type" class="field-input">
                            <option value="general">General</option>
                            <option value="icu">ICU</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="field-label">Expected Arrival (optional)</label>
                        <input type="datetime-local" name="expected_at" class="field-input">
                    </div>
                    <div class="form-group">
                        <label class="field-label">Notes (optional)</label>
                        <input type="text" name="notes" class="field-input" placeholder="e.g. Transfer from City Clinic">
                    </div>
                </div>
                <button type="submit" class="btn-primary" style="margin-top: 20px; padding: 13px 22px; border-radius: 10px; font-weight: 700;">Reserve Bed</button>
            </form>
        </div>

        @if($manualReservations->isNotEmpty())
        <div class="facility-card" style="margin-bottom: 24px;">
            <h3><i data-lucide="calendar-clock"></i> Manual Reservations</h3>
            <div style="margin-top: 20px; display: flex; flex-direction: column; gap: 10px;" id="manualReservationList">
                @foreach($manualReservations as $r)
                <div class="patient-row" id="manual-res-row-{{ $r->id }}">
                    <div class="patient-row-top">
                        <div>
                            <h4 style="margin: 0; font-size: 0.95rem;">{{ $r->patient_name }}</h4>
                            <p style="margin: 4px 0 0; font-size: 0.78rem; color: var(--grey);">
                                {{ strtoupper($r->bed_type) }} bed
                                @if($r->expected_at) · Expected {{ $r->expected_at->format('M d, Y g:i A') }} @endif
                                · Reserved {{ $r->created_at->format('M d, Y g:i A') }}
                            </p>
                        </div>
                        <div style="display: flex; align-items: center; gap: 8px;">
                            @php
                                $rColors2 = ['reserved' => ['#3b82f6','rgba(59,130,246,0.1)'], 'admitted' => ['#22c55e','rgba(34,197,94,0.1)'], 'cancelled' => ['var(--text-muted)','var(--glass)']];
                                [$rColor2, $rBg2] = $rColors2[$r->status] ?? ['var(--text-muted)','var(--glass)'];
                            @endphp
                            <span class="status-pill" style="color: {{ $rColor2 }}; background: {{ $rBg2 }};">{{ strtoupper($r->status) }}</span>
                            @if($r->status === 'reserved')
                            <button type="button" class="mini-btn" style="background: rgba(34,197,94,0.15); color: #22c55e; border-color: rgba(34,197,94,0.3);" onclick="admitManualReservation({{ $r->id }})">Admit</button>
                            <button type="button" class="mini-btn" style="background: rgba(229,9,20,0.08); color: var(--red); border-color: rgba(229,9,20,0.2);" onclick="cancelManualReservation({{ $r->id }})">Cancel</button>
                            @endif
                        </div>
                    </div>
                    @if($r->notes)
                    <div class="case-notes"><p style="margin:0;">{{ $r->notes }}</p></div>
                    @endif
                </div>
                @endforeach
            </div>
        </div>
        @endif

        <div class="facility-card" style="border-left: 4px solid #3b82f6; margin-bottom: 24px;">
            <h3 style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px;">
                <span><i data-lucide="bed" style="color:#3b82f6;"></i> Incoming Reservations</span>
                <span id="reservationBadge" style="background:rgba(59,130,246,0.1);color:#3b82f6;font-size:0.68rem;padding:2px 10px;border-radius:12px;font-weight:800;">LIVE</span>
            </h3>
            <div id="reservationList" style="display: flex; flex-direction: column; gap: 10px;">
                <div style="text-align:center;padding:20px;opacity:0.5;font-size:0.85rem;">No pending reservations.</div>
            </div>
        </div>

        <div class="facility-card">
            <h3><i data-lucide="history"></i> Reservation History</h3>
            <div style="margin-top: 20px;">
                @forelse($bedReservationHistory as $reservation)
                <div class="patient-row">
                    <div class="patient-row-top">
                        <div>
                            <h4 style="margin: 0; font-size: 0.95rem;">{{ $reservation->emergency->user->name ?? 'Unknown Patient' }}</h4>
                            <p style="margin: 4px 0 0; font-size: 0.78rem; color: var(--grey);">
                                Unit: {{ $reservation->responder->user->name ?? 'Unknown' }} · ETA: {{ $reservation->eta_minutes ?? '?' }} mins · {{ $reservation->created_at->format('M d, Y g:i A') }}
                            </p>
                        </div>
                        @php
                            $rColors = ['pending' => ['var(--red)','rgba(229,9,20,0.1)'], 'confirmed' => ['#22c55e','rgba(34,197,94,0.1)']];
                            [$rColor, $rBg] = $rColors[$reservation->status] ?? ['var(--text-muted)','var(--glass)'];
                        @endphp
                        <span class="status-pill" style="color: {{ $rColor }}; background: {{ $rBg }};">{{ strtoupper($reservation->status) }}</span>
                    </div>
                </div>
                @empty
                <div style="text-align: center; padding: 30px; opacity: 0.5;">
                    <i data-lucide="history" style="width: 40px; height: 40px; margin-bottom: 10px;"></i>
                    <p>No reservation history yet.</p>
                </div>
                @endforelse
            </div>
        </div>
    </div>

    <!-- MAP TAB (bigger dedicated view) -->
    <div id="maptab" class="tab-pane">
        <div class="facility-card">
            <h3><i data-lucide="map-pin"></i> Facility Location — Full View</h3>
            <div id="mapFullPlaceholder" class="map-empty-state" style="{{ $hospital->hasLocation() ? 'display:none;' : '' }}">
                <i data-lucide="map-pin-off" style="width: 32px; height: 32px; opacity: 0.5;"></i>
                <p>Your facility location hasn't been set yet, so the map can't show where you are.</p>
                <button type="button" class="locate-btn" onclick="setInitialHospitalLocation()">
                    <i data-lucide="crosshair" style="width: 14px; height: 14px;"></i> Use My Current Location
                </button>
            </div>
            <div id="map-full" style="height: 560px; border-radius: 15px; margin-top: 20px; border: 1px solid var(--glass-border); {{ $hospital->hasLocation() ? '' : 'display:none;' }}"></div>
        </div>
    </div>
</main>

<!-- PATIENT CHAT WINDOW -->
<div id="hospitalChat" style="display:none; position:fixed; bottom:30px; right:30px; width:340px; height:440px; background:var(--dark2); border:1px solid rgba(34,197,94,0.3); border-radius:20px; flex-direction:column; overflow:hidden; z-index:6000; box-shadow:0 20px 50px rgba(0,0,0,0.3);">
    <div style="background:rgba(34,197,94,0.1); padding:16px 20px; display:flex; align-items:center; gap:12px; border-bottom:1px solid var(--glass-border);">
        <div style="width:36px;height:36px;background:#22c55e;border-radius:10px;display:flex;align-items:center;justify-content:center;font-weight:900;color:#fff;">H</div>
        <div style="flex:1;">
            <h4 style="margin:0;font-size:0.9rem;color:var(--white);" id="hChatPatientName">Patient</h4>
            <small style="color:#22c55e;font-weight:700;font-size:0.7rem;">Facility Chat</small>
        </div>
        <button onclick="document.getElementById('hospitalChat').style.display='none'" style="background:none;border:none;color:var(--grey);cursor:pointer;font-size:1.2rem;">&times;</button>
    </div>
    <div id="hChatBody" style="flex:1;padding:16px;overflow-y:auto;display:flex;flex-direction:column;gap:10px;background:var(--dark2);">
        <div style="text-align:center;color:var(--grey);font-size:0.8rem;padding:20px;">Chat with the incoming patient</div>
    </div>
    <div style="padding:12px;border-top:1px solid var(--glass-border);display:flex;gap:8px;background:var(--dark2);">
        <input id="hChatInput" type="text" placeholder="Type a message..." style="flex:1;background:var(--glass);border:1px solid var(--glass-border);border-radius:10px;padding:10px 14px;color:var(--white);font-size:0.85rem;" onkeypress="if(event.key==='Enter') sendPatientChat()">
        <button onclick="sendPatientChat()" style="background:#22c55e;border:none;color:#fff;width:40px;border-radius:10px;cursor:pointer;font-size:1rem;">➤</button>
    </div>
</div>

<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/leaflet@1.9.4/dist/leaflet.min.css"/>
<script src="https://cdn.jsdelivr.net/npm/leaflet@1.9.4/dist/leaflet.min.js"></script>
<script>
    lucide.createIcons();

    function addSatelliteToggle(mapObj, osmLayer) {
        let sat = false;
        const satLayer = L.tileLayer(
            'https://server.arcgisonline.com/ArcGIS/rest/services/World_Imagery/MapServer/tile/{z}/{y}/{x}',
            // Esri's free imagery has real coverage everywhere only up to ~z13; beyond that,
            // many regions (this one included) return a grey "Map data not yet available"
            // placeholder instead of a tile. Capping maxNativeZoom makes Leaflet re-scale the
            // last real tile instead of requesting zoom levels Esri doesn't have.
            { attribution: 'Tiles &copy; Esri', maxZoom: 19, maxNativeZoom: 13 }
        );
        const ctrl = document.createElement('div');
        ctrl.className = 'map-type-ctrl';
        ctrl.innerHTML = '<button class="mtb active" data-t="map">Map</button><button class="mtb" data-t="satellite">Satellite</button>';
        mapObj.getContainer().appendChild(ctrl);
        ctrl.addEventListener('click', e => {
            const btn = e.target.closest('.mtb');
            if (!btn) return;
            ctrl.querySelectorAll('.mtb').forEach(b => b.classList.remove('active'));
            btn.classList.add('active');
            const pane = mapObj.getPane('tilePane');
            if (btn.dataset.t === 'satellite' && !sat) {
                osmLayer.remove(); satLayer.addTo(mapObj);
                if (pane) pane.style.filter = ''; sat = true;
            } else if (btn.dataset.t === 'map' && sat) {
                satLayer.remove(); osmLayer.addTo(mapObj);
                const light = document.documentElement.classList.contains('light-mode');
                if (pane && !light) pane.style.filter = 'invert(92%) hue-rotate(180deg) brightness(95%) contrast(90%)';
                sat = false;
            }
        });
        return () => sat;
    }

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

    // Tab switching is registered before the map setup below so that a map/Leaflet
    // failure (bad coordinates, CDN hiccup) can never block sidebar navigation.
    let map = null, marker = null, bigMap = null, markerFull = null;
    let isMapSat = () => false, isMapFullSat = () => false;

    document.querySelectorAll('.nav-item[data-tab]').forEach(item => {
        item.addEventListener('click', () => {
            document.querySelectorAll('.nav-item').forEach(i => i.classList.remove('active'));
            item.classList.add('active');

            const tabId = item.getAttribute('data-tab');
            document.querySelectorAll('.tab-pane').forEach(p => p.classList.remove('active'));
            const pane = document.getElementById(tabId);
            if (pane) pane.classList.add('active');

            document.getElementById('pageTitle').textContent = item.textContent.trim();

            if (tabId === 'maptab' && bigMap) {
                setTimeout(() => bigMap.invalidateSize(), 100);
            }
        });
    });

    // Map tiles follow the light/dark toggle instead of always forcing a dark filter
    function applyHospitalMapTheme(isLight) {
        if (map) {
            const hPane = map.getPane('tilePane');
            if (hPane && !isMapSat()) hPane.style.filter = isLight ? '' : 'invert(92%) hue-rotate(180deg) brightness(95%) contrast(90%)';
        }
        if (bigMap) {
            const hPaneFull = bigMap.getPane('tilePane');
            if (hPaneFull && !isMapFullSat()) hPaneFull.style.filter = isLight ? '' : 'invert(92%) hue-rotate(180deg) brightness(95%) contrast(90%)';
        }
    }
    document.addEventListener('themeChanged', (e) => applyHospitalMapTheme(e.detail.isLight));

    // Keep both maps + the lat/lng inputs in sync from one place
    function updateHospitalLocation(newLat, newLng) {
        document.getElementById('latInput').value = newLat;
        document.getElementById('lngInput').value = newLng;
        if (map) marker.setLatLng([newLat, newLng]), map.setView([newLat, newLng], 15);
        if (bigMap) markerFull.setLatLng([newLat, newLng]), bigMap.setView([newLat, newLng], 15);
    }

    function getBrowserLocation(onSuccess) {
        if (!window.isSecureContext) {
            return alert('This page must be loaded over HTTPS to access your location. Please reload using https://');
        }
        if (!navigator.geolocation) {
            return alert('Geolocation is not supported on this device/browser.');
        }
        navigator.geolocation.getCurrentPosition((position) => {
            onSuccess(position.coords.latitude, position.coords.longitude);
        }, (error) => {
            const messages = {
                1: 'Location permission was denied. Please allow location access in your browser settings, then try again.',
                2: 'Your location could not be determined. Please try again, ideally outdoors or near a window.',
                3: 'Getting your location took too long. Please try again.'
            };
            alert(messages[error.code] || 'Could not get your location. Please try again.');
        }, {
            enableHighAccuracy: true,
            timeout: 15000,
            maximumAge: 0
        });
    }

    function useMyLocation() {
        getBrowserLocation(updateHospitalLocation);
    }

    // Used from the "location not set yet" empty state on the map cards — captures
    // the browser's location and saves it immediately (real coordinates were never
    // set at registration, so there's nothing meaningful to preview first).
    function setInitialHospitalLocation() {
        getBrowserLocation((lat, lng) => {
            document.getElementById('latInput').value = lat;
            document.getElementById('lngInput').value = lng;
            document.getElementById('hospitalUpdateForm').submit();
        });
    }

    // Leaflet map initialization — wrapped so a failure here (bad coordinates,
    // tile CDN unreachable) never breaks tabs, forms, or anything registered above.
    // Skipped entirely until the facility has a real location (registration
    // defaults lat/lng to 0,0 — the middle of the ocean — which is not worth
    // rendering as if it were a valid map).
    const hospitalHasLocation = {{ $hospital->hasLocation() ? 'true' : 'false' }};
    if (hospitalHasLocation) {
        try {
            const lat = {{ $hospital->lat }};
            const lng = {{ $hospital->lng }};

            map = L.map('map').setView([lat, lng], 15);
            const hTileLayer = L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a>',
                maxZoom: 19
            }).addTo(map);
            isMapSat = addSatelliteToggle(map, hTileLayer);
            marker = L.marker([lat, lng]).addTo(map).bindPopup('{{ $hospital->name }}').openPopup();

            // Bigger dedicated map (Map tab) — separate Leaflet instance, same coordinates
            bigMap = L.map('map-full').setView([lat, lng], 15);
            const hTileLayerFull = L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a>',
                maxZoom: 19
            }).addTo(bigMap);
            isMapFullSat = addSatelliteToggle(bigMap, hTileLayerFull);
            markerFull = L.marker([lat, lng]).addTo(bigMap).bindPopup('{{ $hospital->name }}').openPopup();

            applyHospitalMapTheme(document.documentElement.classList.contains('light-mode'));
        } catch (err) {
            console.warn('Hospital map failed to initialize:', err);
        }
    }

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
            <div style="background:var(--glass);border:1px solid rgba(59,130,246,0.2);border-radius:10px;padding:14px;">
                <div style="display:flex;justify-content:space-between;align-items:flex-start;margin-bottom:10px;">
                    <div>
                        <p style="margin:0;font-weight:700;font-size:0.88rem;">Patient: ${r.patient}</p>
                        <p style="margin:2px 0 0;font-size:0.73rem;color:var(--grey);">Unit: ${r.responder} · ETA: ${r.eta_minutes ?? '?'} mins</p>
                    </div>
                    <span style="font-size:0.7rem;font-weight:800;padding:2px 8px;border-radius:6px;background:${r.status==='confirmed'?'rgba(34,197,94,0.1)':'rgba(229,9,20,0.1)'};color:${r.status==='confirmed'?'#22c55e':'var(--red)'};">${r.status.toUpperCase()}</span>
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

    // ── Accept / Decline / Discharge / Consult ──────────────────────────
    function acceptPatient(uuid) {
        fetch(`/hospital/accept/${uuid}`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF, 'X-Requested-With': 'XMLHttpRequest' }
        }).then(r => r.json()).then(data => { if (data.success) location.reload(); }).catch(() => {});
    }

    function declinePatient(uuid) {
        const reason = prompt('Reason for declining this patient?');
        if (reason === null) return;
        fetch(`/hospital/decline/${uuid}`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF },
            body: JSON.stringify({ reason })
        }).then(r => r.json()).then(data => { if (data.success) location.reload(); }).catch(() => {});
    }

    function dischargePatient(uuid) {
        if (!confirm('Discharge this patient? This frees the bed and credits the admission fee.')) return;
        fetch(`/hospital/discharge/${uuid}`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF }
        })
        .then(r => r.json())
        .then(data => {
            alert(data.message || (data.success ? 'Patient discharged.' : 'Could not discharge patient.'));
            if (data.success) location.reload();
        })
        .catch(() => alert('Network error. Please try again.'));
    }

    // ── Walk-in patients / manual reservations ──────────────────────────
    function dischargeManualPatient(id) {
        if (!confirm('Discharge this patient? This frees the bed.')) return;
        fetch(`/hospital/patients/${id}/discharge`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF }
        })
        .then(r => r.json())
        .then(data => {
            if (data.success) location.reload();
            else alert(data.message || 'Could not discharge patient.');
        })
        .catch(() => alert('Network error. Please try again.'));
    }

    function deleteManualPatient(id) {
        if (!confirm('Remove this patient record? This cannot be undone.')) return;
        fetch(`/hospital/patients/${id}`, {
            method: 'DELETE',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF }
        })
        .then(r => r.json())
        .then(data => { if (data.success) location.reload(); })
        .catch(() => alert('Network error. Please try again.'));
    }

    function admitManualReservation(id) {
        fetch(`/hospital/reservations/${id}/admit`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF }
        })
        .then(r => r.json())
        .then(data => {
            if (data.success) location.reload();
            else alert(data.message || 'Could not admit this reservation.');
        })
        .catch(() => alert('Network error. Please try again.'));
    }

    function cancelManualReservation(id) {
        if (!confirm('Cancel this reservation? This frees the reserved bed.')) return;
        fetch(`/hospital/reservations/${id}/cancel`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF }
        })
        .then(r => r.json())
        .then(data => {
            if (data.success) location.reload();
            else alert(data.message || 'Could not cancel this reservation.');
        })
        .catch(() => alert('Network error. Please try again.'));
    }

    function requestDoctorConsult(uuid) {
        fetch(`/emergency/request-doctor-consult/${uuid}`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF }
        })
        .then(res => res.json())
        .then(data => alert(data.success ? 'Doctor consult requested.' : 'Could not request a doctor consult.'))
        .catch(() => alert('Network error. Please try again.'));
    }

    // ── Patient Chat ──────────────────────────────────────
    let hChatPolling = null;
    let hLastChatId = 0;
    let hCurrentChatUuid = null;

    function joinPatientChat(uuid, patientName) {
        hCurrentChatUuid = uuid;
        hLastChatId = 0;
        document.getElementById('hChatPatientName').textContent = patientName || 'Patient';
        document.getElementById('hChatBody').innerHTML = '<div style="text-align:center;color:var(--grey);font-size:0.8rem;padding:20px;">Chat with the incoming patient</div>';
        document.getElementById('hospitalChat').style.display = 'flex';
        pollPatientChat();
        if (!hChatPolling) hChatPolling = setInterval(pollPatientChat, 3000);
    }

    function pollPatientChat() {
        if (!hCurrentChatUuid) return;
        fetch(`/chat/${hCurrentChatUuid}/messages`)
            .then(r => r.json())
            .then(msgs => {
                const body = document.getElementById('hChatBody');
                const newMsgs = msgs.filter(m => m.id > hLastChatId);
                newMsgs.forEach(m => {
                    hLastChatId = Math.max(hLastChatId, m.id);
                    const isMe = m.sender_role === 'responder';
                    const div = document.createElement('div');
                    div.className = isMe ? 'echat-msg me green' : 'echat-msg them';
                    div.textContent = m.message;
                    body.appendChild(div);
                });
                body.scrollTop = body.scrollHeight;
            }).catch(() => {});
    }

    function sendPatientChat() {
        if (!hCurrentChatUuid) return;
        const input = document.getElementById('hChatInput');
        const msg = input.value.trim();
        if (!msg) return;
        input.value = '';
        fetch(`/chat/${hCurrentChatUuid}/send`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF },
            body: JSON.stringify({ message: msg })
        }).then(() => pollPatientChat()).catch(() => {});
    }

    // ── Live ambulance ETA markers on both maps ──────────────────────────
    let ambulanceMarkers = {};
    let ambulanceMarkersFull = {};

    function pollAmbulanceLocations() {
        if (!map || !bigMap) return;
        fetch('/hospital/incoming-locations')
            .then(r => r.json())
            .then(list => {
                const seen = {};
                list.forEach(loc => {
                    seen[loc.uuid] = true;
                    if (!ambulanceMarkers[loc.uuid]) {
                        ambulanceMarkers[loc.uuid] = L.marker([loc.lat, loc.lng], {
                            icon: L.divIcon({ html: '<div style="background:#3b82f6;border:2px solid white;border-radius:50%;width:14px;height:14px;box-shadow:0 0 8px rgba(59,130,246,0.6);"></div>', className: 'custom-div-icon' })
                        }).addTo(map).bindPopup(loc.type.toUpperCase());
                        ambulanceMarkersFull[loc.uuid] = L.marker([loc.lat, loc.lng], {
                            icon: L.divIcon({ html: '<div style="background:#3b82f6;border:2px solid white;border-radius:50%;width:14px;height:14px;box-shadow:0 0 8px rgba(59,130,246,0.6);"></div>', className: 'custom-div-icon' })
                        }).addTo(bigMap).bindPopup(loc.type.toUpperCase());
                    } else {
                        ambulanceMarkers[loc.uuid].setLatLng([loc.lat, loc.lng]);
                        ambulanceMarkersFull[loc.uuid].setLatLng([loc.lat, loc.lng]);
                    }
                });
                Object.keys(ambulanceMarkers).forEach(uuid => {
                    if (!seen[uuid]) {
                        map.removeLayer(ambulanceMarkers[uuid]);
                        bigMap.removeLayer(ambulanceMarkersFull[uuid]);
                        delete ambulanceMarkers[uuid];
                        delete ambulanceMarkersFull[uuid];
                    }
                });
            }).catch(() => {});
    }

    pollAmbulanceLocations();
    setInterval(pollAmbulanceLocations, 10000);
</script>
<script src="/js/pwa.js" defer></script>
@include('partials.profile-modal')
</body>
</html>
