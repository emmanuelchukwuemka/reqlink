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
        .tab-pane { display: none; }
        .tab-pane.active { display: block; }
        .nav-badge { margin-left: auto; background: var(--red); color: #fff; font-size: 0.68rem; font-weight: 800; padding: 2px 7px; border-radius: 100px; }

        /* My Unit tab — section headers, icon chips, forms, stats, account card */
        .section-head-icon { display: flex; align-items: flex-start; gap: 14px; margin-bottom: 22px; }
        .section-head-sub { margin: 4px 0 0; font-size: 0.8rem; color: var(--grey); }
        .icon-chip { width: 42px; height: 42px; border-radius: 12px; display: flex; align-items: center; justify-content: center; flex-shrink: 0; }
        .icon-chip i { width: 20px; height: 20px; }
        .icon-chip-red { background: rgba(229,9,20,0.12); color: var(--red); }
        .icon-chip-blue { background: rgba(59,130,246,0.12); color: #3b82f6; }

        .unit-form { display: flex; flex-direction: column; gap: 18px; }
        .unit-form-group label { display: flex; align-items: center; gap: 6px; font-size: 0.72rem; color: var(--grey); text-transform: uppercase; letter-spacing: 0.6px; font-weight: 800; margin-bottom: 8px; }
        .unit-form-group input {
            width: 100%; background: rgba(255,255,255,0.04); border: 1px solid var(--glass-border);
            padding: 12px 14px; border-radius: 10px; color: var(--white); font-size: 0.9rem; outline: none;
            transition: border-color 0.2s ease, box-shadow 0.2s ease;
        }
        .unit-form-group input:focus { border-color: var(--red); box-shadow: 0 0 0 3px rgba(229,9,20,0.1); }

        .unit-stats-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 14px; margin-top: 18px; }
        .unit-stat { background: rgba(255,255,255,0.02); border: 1px solid var(--glass-border); border-radius: 14px; padding: 16px 10px; text-align: center; }
        .unit-stat i { width: 18px; height: 18px; margin-bottom: 8px; }
        .unit-stat-value { font-size: 1.35rem; font-weight: 800; }
        .unit-stat-label { font-size: 0.66rem; color: var(--grey); text-transform: uppercase; letter-spacing: 0.6px; margin-top: 3px; }

        .account-card-head { display: flex; align-items: center; gap: 14px; margin-bottom: 18px; }
        .account-avatar { width: 46px; height: 46px; border-radius: 50%; overflow: hidden; background: #22c55e; color: #fff; display: flex; align-items: center; justify-content: center; font-weight: 800; font-size: 1.1rem; flex-shrink: 0; }
        .account-detail-list { display: flex; flex-direction: column; gap: 10px; padding-bottom: 16px; margin-bottom: 16px; border-bottom: 1px solid var(--glass-border); }
        .account-detail { display: flex; align-items: center; gap: 10px; font-size: 0.86rem; color: var(--white); }
        .account-detail i { color: var(--grey); flex-shrink: 0; }
        .edit-settings-link {
            display: flex; align-items: center; justify-content: center; gap: 8px; text-decoration: none;
            padding: 11px 16px; border-radius: 10px; font-size: 0.82rem; font-weight: 700;
            background: var(--glass); border: 1px solid var(--glass-border); color: var(--white);
            transition: background 0.2s ease;
        }
        .edit-settings-link:hover { background: rgba(255,255,255,0.08); }
        @media (max-width: 480px) { .unit-stats-grid { grid-template-columns: 1fr; } }

        :root.light-mode .unit-form-group input,
        :root.light-mode .unit-stat,
        :root.light-mode .duty-status-container,
        :root.light-mode .edit-settings-link { background: #ffffff; border-color: rgba(15, 23, 42, 0.1); }
        :root.light-mode .account-detail-list { border-bottom-color: rgba(15, 23, 42, 0.1); }
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

        /* Duty Toggle — single dynamic label, compact pill */
        .duty-status-container { display: flex; align-items: center; gap: 10px; background: var(--glass); padding: 7px 14px 7px 16px; border-radius: 100px; border: 1px solid var(--glass-border); cursor: pointer; white-space: nowrap; }
        .duty-status-text { font-size: 0.74rem; font-weight: 800; text-transform: uppercase; letter-spacing: 0.6px; color: var(--red); transition: color 0.2s ease; }
        .duty-status-text.is-on { color: #22c55e; }
        .duty-toggle { position: relative; width: 38px; height: 20px; flex-shrink: 0; }
        .duty-toggle input { opacity: 0; width: 0; height: 0; position: absolute; }
        .duty-slider { position: absolute; cursor: pointer; top: 0; left: 0; right: 0; bottom: 0; background-color: var(--grey); transition: .3s; border-radius: 34px; }
        .duty-slider:before { position: absolute; content: ""; height: 14px; width: 14px; left: 3px; bottom: 3px; background-color: white; transition: .3s; border-radius: 50%; }
        .duty-toggle input:checked + .duty-slider { background-color: #22c55e; }
        .duty-toggle input:checked + .duty-slider:before { transform: translateX(18px); }

        /* Request Backup button */
        .backup-request-btn { display: flex; align-items: center; gap: 6px; background: rgba(245,158,11,0.12); color: #f59e0b; border: 1px solid rgba(245,158,11,0.3); padding: 8px 14px; border-radius: 100px; font-size: 0.78rem; font-weight: 800; cursor: pointer; white-space: nowrap; transition: background 0.2s ease; }
        .backup-request-btn:hover { background: rgba(245,158,11,0.2); }
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
        <a class="nav-item active" data-tab="missions"><i data-lucide="layout-dashboard"></i> Missions <span class="nav-badge" id="missionsBadge" style="display:none;"></span></a>
        <a class="nav-item" data-tab="livemap"><i data-lucide="map"></i> Live Map</a>
        <a class="nav-item" data-tab="ambulance"><i data-lucide="truck"></i> Ambulance</a>

        <a class="nav-item" data-tab="fire"><i data-lucide="flame"></i> Fire Services</a>
        <a class="nav-item" data-tab="hospitals"><i data-lucide="hospital"></i> Hospitals</a>
        <a class="nav-item" data-tab="backup"><i data-lucide="radio-tower"></i> Backup Requests <span class="nav-badge" id="backupBadge" style="display:none;"></span></a>
        <a class="nav-item" data-tab="profile"><i data-lucide="id-card"></i> My Unit</a>
        <a class="nav-item" data-tab="history"><i data-lucide="history"></i> History &amp; Reviews</a>
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
        
        <div class="topbar-actions">
            <button type="button" class="backup-request-btn" onclick="openBackupModal()">
                <i data-lucide="radio-tower" style="width:14px;height:14px;"></i> Request Backup
            </button>

            <!-- Duty Toggle -->
            <label class="duty-status-container">
                <span class="duty-status-text {{ $responder && $responder->is_on_duty ? 'is-on' : '' }}" id="dutyText">{{ $responder && $responder->is_on_duty ? 'On Duty' : 'Off Duty' }}</span>
                <span class="duty-toggle">
                    <input type="checkbox" id="dutySwitch" {{ $responder && $responder->is_on_duty ? 'checked' : '' }} onchange="toggleDuty(this)">
                    <span class="duty-slider"></span>
                </span>
            </label>

            @include('partials.lang-switcher')
            <button id="themeToggle" class="theme-toggle" aria-label="Toggle Dark Mode">
                <i data-lucide="sun" id="themeIcon"></i>
            </button>
            <div class="user-profile">
                <div class="user-info">
                    <span>{{ Auth::user()->name }}</span>
                    <small>Unit: {{ ucfirst(Auth::user()->role) }}</small>
                </div>
                <div class="avatar" style="background: #22c55e">
                    @if(Auth::user()->avatar)
                        <img src="{{ Auth::user()->avatar }}" alt="">
                    @else
                        {{ substr(Auth::user()->name, 0, 1) }}
                    @endif
                </div>
            </div>
        </div>
    </header>

    @include('partials.announcement-banner')

    <div id="backupBanners" style="display: flex; flex-direction: column; gap: 10px; margin-bottom: 20px;"></div>

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
                        <div class="stat-box">
                            <h4 style="color: #f59e0b;">{{ $avgRating ?? '—' }}</h4>
                            <p>Avg Rating</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- LIVE MAP TAB -->
    <div id="livemap" class="tab-pane">
        <div class="dash-card">
            <div class="section-head-icon" style="margin-bottom: 16px;">
                <div class="icon-chip icon-chip-red"><i data-lucide="map-pin"></i></div>
                <div>
                    <h3 style="margin:0;">Live Map</h3>
                    <p class="section-head-sub" id="liveMapSub">No active mission — showing your current location.</p>
                </div>
            </div>

            <div id="liveMapMissionPanel" style="display:none; background: rgba(229,9,20,0.06); border: 1px solid rgba(229,9,20,0.25); border-radius: 12px; padding: 14px 18px; margin-bottom: 16px;">
                <p style="margin:0; font-weight:700;" id="liveMapPatientName">Patient: —</p>
                <p style="margin:2px 0 0; font-size:0.8rem; color: var(--grey);" id="liveMapStatusText">Status: —</p>
                <div style="display:flex; gap:10px; margin-top:12px; flex-wrap: wrap;">
                    <a href="#" target="_blank" id="liveMapNavLink" style="display:flex; align-items:center; gap:6px; background:#2563eb; color:#fff; text-decoration:none; padding:10px 16px; border-radius:10px; font-weight:700; font-size:0.8rem;">
                        <i data-lucide="navigation"></i> Navigate
                    </a>
                    <button onclick="markArrived()" id="liveMapArrivedBtn" style="display:flex; align-items:center; gap:6px; background: rgba(59,130,246,0.12); color: #3b82f6; border: 1px solid rgba(59,130,246,0.3); padding:10px 16px; border-radius:10px; font-weight:700; font-size:0.8rem; cursor:pointer;">
                        <i data-lucide="map-pin-check"></i> Mark Arrived
                    </button>
                    <button onclick="completeMission()" style="display:flex; align-items:center; gap:6px; background:#22c55e; color:#fff; border:none; padding:10px 16px; border-radius:10px; font-weight:700; font-size:0.8rem; cursor:pointer;">
                        <i data-lucide="check-circle-2"></i> Complete Mission
                    </button>
                </div>
            </div>

            <div id="responderMap" style="height: 65vh; border-radius: 12px; border: 1px solid var(--glass-border); background: var(--dark2);"></div>
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
                    <div id="unitDetails{{ $unit->id }}" style="display:none; margin-bottom:12px; padding:12px; background:rgba(255,255,255,0.03); border-radius:10px; font-size:0.8rem; color:var(--grey);">
                        <p style="margin:0 0 4px;"><strong style="color:var(--white);">Vehicle Reg:</strong> {{ $unit->vehicle_reg ?: 'Not provided' }}</p>
                        <p style="margin:0 0 4px;"><strong style="color:var(--white);">Capacity:</strong> {{ $unit->capacity ?: 'Not provided' }}</p>
                        <p style="margin:0;"><strong style="color:var(--white);">Phone:</strong> {{ $unit->user->phone ?? 'N/A' }}</p>
                    </div>
                    <button class="btn-primary" style="width: 100%; padding: 12px; font-size: 0.85rem; border-radius: 8px;" onclick="toggleUnitDetails({{ $unit->id }})">View Profile</button>
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
                    <div id="unitDetails{{ $unit->id }}" style="display:none; margin-bottom:12px; padding:12px; background:rgba(255,255,255,0.03); border-radius:10px; font-size:0.8rem; color:var(--grey);">
                        <p style="margin:0 0 4px;"><strong style="color:var(--white);">Vehicle Reg:</strong> {{ $unit->vehicle_reg ?: 'Not provided' }}</p>
                        <p style="margin:0 0 4px;"><strong style="color:var(--white);">Capacity:</strong> {{ $unit->capacity ?: 'Not provided' }}</p>
                        <p style="margin:0;"><strong style="color:var(--white);">Phone:</strong> {{ $unit->user->phone ?? 'N/A' }}</p>
                    </div>
                    <button class="btn-primary" style="width: 100%; padding: 12px; font-size: 0.85rem; border-radius: 8px; background: #f97316;" onclick="toggleUnitDetails({{ $unit->id }})">View Profile</button>
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
                    <div id="hospitalDetails{{ $hospital->id }}" style="display:none; margin-bottom:12px; padding:12px; background:rgba(255,255,255,0.03); border-radius:10px; font-size:0.8rem; color:var(--grey);">
                        <p style="margin:0 0 4px;"><strong style="color:var(--white);">Beds:</strong> {{ $hospital->available_beds ?? 0 }} available / {{ $hospital->total_beds ?? 0 }} total</p>
                        <p style="margin:0 0 4px;"><strong style="color:var(--white);">ICU Beds:</strong> {{ $hospital->icu_beds ?? 0 }}</p>
                        <p style="margin:0 0 4px;"><strong style="color:var(--white);">Specialties:</strong> {{ !empty($hospital->specialties) ? implode(', ', $hospital->specialties) : 'Not listed' }}</p>
                        <p style="margin:0;"><strong style="color:var(--white);">Contact:</strong> {{ $hospital->contact_phone ?? 'N/A' }}</p>
                    </div>
                    <button class="btn-primary" style="width: 100%; padding: 12px; font-size: 0.85rem; border-radius: 8px;" onclick="toggleHospitalDetails({{ $hospital->id }})">View Details</button>
                </div>
                @empty
                <p>No hospitals registered yet.</p>
                @endforelse
            </div>
        </div>
    </div>

    <!-- BACKUP REQUESTS TAB -->
    <div id="backup" class="tab-pane">
        <div class="dash-card" style="margin-bottom: 24px;">
            <h3><i data-lucide="radio-tower" style="color:#f59e0b;"></i> Requests From Nearby Units</h3>
            <div id="peerBackupList" style="margin-top:15px;">
                <div style="text-align:center;padding:30px;opacity:0.5;">
                    <i data-lucide="radio-tower" style="width:36px;height:36px;margin-bottom:10px;"></i>
                    <p>No pending backup requests from other units.</p>
                </div>
            </div>
        </div>

        <div class="dash-card">
            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:15px; flex-wrap:wrap; gap:10px;">
                <h3 style="margin:0;"><i data-lucide="send"></i> My Requests</h3>
                <button type="button" onclick="openBackupModal()" style="background:#f59e0b; color:#000; border:none; padding:8px 14px; border-radius:8px; font-weight:800; font-size:0.78rem; cursor:pointer;">+ New Request</button>
            </div>
            <div class="history-list" id="myBackupList">
                @forelse($myBackupRequests as $req)
                <div class="history-item" id="myBackupRow{{ $req->id }}">
                    <div class="history-info">
                        <div style="width:36px;height:36px;background:rgba(245,158,11,0.1);border-radius:50%;display:flex;align-items:center;justify-content:center;color:#f59e0b;">
                            <i data-lucide="radio-tower" style="width:16px;height:16px;"></i>
                        </div>
                        <div>
                            <p style="font-weight:700;margin:0;font-size:0.88rem;">{{ $req->message ?: 'No details provided' }}</p>
                            <p style="font-size:0.75rem;color:var(--grey);margin:2px 0 0;">{{ $req->created_at->format('M d, Y g:i A') }}</p>
                        </div>
                    </div>
                    <div style="display:flex;align-items:center;gap:8px;">
                        <span class="status-badge {{ $req->status === 'resolved' ? 'status-resolved' : 'status-pending' }}">{{ strtoupper($req->status) }}</span>
                        @if($req->status !== 'resolved')
                        <button type="button" onclick="resolveBackupRequest({{ $req->id }})" style="background:var(--glass); border:1px solid var(--glass-border); color:var(--white); padding:6px 10px; border-radius:6px; font-size:0.72rem; font-weight:700; cursor:pointer;">Mark Resolved</button>
                        @endif
                    </div>
                </div>
                @empty
                <div style="text-align:center;padding:30px;opacity:0.5;">
                    <i data-lucide="send" style="width:36px;height:36px;margin-bottom:10px;"></i>
                    <p>You haven't sent any backup requests yet.</p>
                </div>
                @endforelse
            </div>
        </div>
    </div>

    <!-- PROFILE / UNIT TAB -->
    <div id="profile" class="tab-pane">
        <div class="mission-grid">
            <div class="dash-card">
                <div class="section-head-icon">
                    <span class="icon-chip icon-chip-red"><i data-lucide="id-card"></i></span>
                    <div>
                        <h3 style="margin:0;">Unit Profile</h3>
                        <p class="section-head-sub">Vehicle and capacity details shown to dispatch.</p>
                    </div>
                </div>
                <form id="profileForm" class="unit-form">
                    <div class="unit-form-group">
                        <label><i data-lucide="truck" style="width:12px;height:12px;"></i> Vehicle Registration</label>
                        <input type="text" name="vehicle_reg" value="{{ $responder->vehicle_reg ?? '' }}" placeholder="e.g. LND-234-XY">
                    </div>
                    <div class="unit-form-group">
                        <label><i data-lucide="users" style="width:12px;height:12px;"></i> Capacity (patients/crew)</label>
                        <input type="number" name="capacity" value="{{ $responder->capacity ?? '' }}" min="1" max="20">
                    </div>
                    <button type="submit" class="btn-primary" style="padding:13px; border-radius:10px; font-weight:700; border:none;">Save Changes</button>
                    <div id="profileSaveMsg" style="display:none; padding:10px; border-radius:8px; font-size:0.82rem;"></div>
                </form>
            </div>

            <div>
                <div class="dash-card" style="margin-bottom:24px;">
                    <div class="section-head-icon">
                        <span class="icon-chip icon-chip-blue"><i data-lucide="activity"></i></span>
                        <h3 style="margin:0;">Unit Stats</h3>
                    </div>
                    <div class="unit-stats-grid">
                        <div class="unit-stat">
                            <i data-lucide="check-circle-2" style="color: var(--red);"></i>
                            <div class="unit-stat-value">{{ $missionsDone }}</div>
                            <div class="unit-stat-label">Completed</div>
                        </div>
                        <div class="unit-stat">
                            <i data-lucide="star" style="color: #f59e0b;"></i>
                            <div class="unit-stat-value">{{ $avgRating ?? '—' }}</div>
                            <div class="unit-stat-label">Avg Rating</div>
                        </div>
                        <div class="unit-stat">
                            <i data-lucide="{{ $responder && $responder->is_on_duty ? 'radio' : 'power-off' }}" style="color: {{ $responder && $responder->is_on_duty ? '#22c55e' : 'var(--grey)' }};"></i>
                            <div class="unit-stat-value" style="color: {{ $responder && $responder->is_on_duty ? '#22c55e' : 'var(--grey)' }};">{{ $responder && $responder->is_on_duty ? 'On' : 'Off' }}</div>
                            <div class="unit-stat-label">Duty Status</div>
                        </div>
                    </div>
                </div>
                <div class="dash-card">
                    <div class="account-card-head">
                        <div class="account-avatar">
                            @if(Auth::user()->avatar)
                                <img src="{{ Auth::user()->avatar }}" alt="">
                            @else
                                {{ substr(Auth::user()->name, 0, 1) }}
                            @endif
                        </div>
                        <div>
                            <h3 style="margin:0; font-size:1rem;">{{ Auth::user()->name }}</h3>
                            <p style="margin:2px 0 0; font-size:0.78rem; color:var(--grey);">{{ ucfirst(Auth::user()->role) }} Unit</p>
                        </div>
                    </div>
                    <div class="account-detail-list">
                        <div class="account-detail"><i data-lucide="phone" style="width:14px;height:14px;"></i> {{ Auth::user()->phone ?? 'N/A' }}</div>
                        <div class="account-detail"><i data-lucide="mail" style="width:14px;height:14px;"></i> {{ Auth::user()->email ?? 'N/A' }}</div>
                    </div>
                    <a href="{{ route('settings') }}" class="edit-settings-link">
                        <i data-lucide="settings" style="width:14px;height:14px;"></i> Edit Full Profile in Settings
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- HISTORY & REVIEWS TAB -->
    <div id="history" class="tab-pane">
        <div class="dash-card" style="margin-bottom: 24px;">
            <h3><i data-lucide="history"></i> Mission History</h3>
            <div class="history-list" style="margin-top: 15px;">
                @forelse($missionHistory as $mission)
                <div class="history-item">
                    <div class="history-info">
                        <div style="width: 36px; height: 36px; background: rgba(255,255,255,0.05); border-radius: 50%; display: flex; align-items: center; justify-content: center; color: var(--grey);">
                            <i data-lucide="heart-pulse" style="width:16px;height:16px;"></i>
                        </div>
                        <div>
                            <p style="font-weight:700; margin:0; font-size:0.88rem;">{{ $mission->user->name ?? 'Unknown Patient' }}</p>
                            <p style="font-size:0.75rem; color:var(--grey); margin:2px 0 0;">{{ $mission->emergencyType->name ?? 'General Emergency' }} &middot; {{ $mission->created_at->format('M d, Y g:i A') }}</p>
                        </div>
                    </div>
                    <span class="status-badge {{ $mission->status === 'resolved' ? 'status-resolved' : 'status-pending' }}">{{ strtoupper($mission->status) }}</span>
                </div>
                @empty
                <div style="text-align: center; padding: 40px; opacity: 0.5;">
                    <i data-lucide="history" style="width: 40px; height: 40px; margin-bottom: 10px;"></i>
                    <p>No completed missions yet.</p>
                </div>
                @endforelse
            </div>
        </div>

        <div class="dash-card">
            <h3><i data-lucide="star"></i> Patient Reviews @if($avgRating) <span style="font-size:0.75rem; color:#f59e0b; font-weight:700; margin-left:8px;">★ {{ $avgRating }} average</span> @endif</h3>
            <div class="history-list" style="margin-top: 15px;">
                @forelse($responderReviews as $review)
                <div class="history-item">
                    <div class="history-info">
                        <div>
                            <p style="font-weight:700; margin:0; font-size:0.88rem;">{{ $review->user->name ?? 'Unknown' }}</p>
                            <p style="font-size:0.8rem; color:#f59e0b; margin:2px 0 0;">{{ str_repeat('★', $review->rating) }}{{ str_repeat('☆', 5 - $review->rating) }}</p>
                            @if($review->comment)
                            <p style="font-size:0.78rem; color:var(--grey); margin:4px 0 0;">{{ $review->comment }}</p>
                            @endif
                        </div>
                    </div>
                    <span style="font-size:0.72rem; color:var(--grey);">{{ $review->created_at->diffForHumans() }}</span>
                </div>
                @empty
                <div style="text-align: center; padding: 40px; opacity: 0.5;">
                    <i data-lucide="star" style="width: 40px; height: 40px; margin-bottom: 10px;"></i>
                    <p>No reviews yet.</p>
                </div>
                @endforelse
            </div>
        </div>
    </div>
</main>

<!-- EMERGENCY ALERT MODAL -->
<div id="emergencyModal" style="display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.7); z-index: 1000; align-items: center; justify-content: center; backdrop-filter: blur(10px); overflow-y: auto; padding: 20px 0;">
    <div style="background: var(--dark); border: 1px solid var(--red); width: 100%; max-width: 500px; border-radius: 20px; padding: 40px; text-align: center; box-shadow: 0 0 50px rgba(229, 9, 20, 0.3); color: var(--white); margin: auto;">
        <div style="width: 80px; height: 80px; background: rgba(229, 9, 20, 0.1); border-radius: 50%; display: flex; align-items: center; justify-content: center; color: var(--red); margin: 0 auto 24px;">
            <i data-lucide="siren" style="width: 48px; height: 48px;"></i>
        </div>
        <h2 id="alertHeading" style="font-size: 2rem; font-weight: 900; margin-bottom: 10px; color: var(--white);">CRITICAL ALERT</h2>
        <p id="alertUser" style="font-size: 1.1rem; color: var(--grey); margin-bottom: 5px;">Patient: John Doe</p>
        <p id="alertLoc" style="font-size: 0.9rem; color: var(--red); font-weight: 700; margin-bottom: 12px;">LOCATION: 1.2km away</p>
        <div style="text-align: left; margin-bottom: 20px;">
            <label style="font-size: 0.75rem; color: var(--grey); text-transform: uppercase; display: block; margin-bottom: 6px;">Destination Hospital</label>
            <div id="hospitalAcceptedNotice" style="display:none; font-size: 0.8rem; color: var(--grey); background: rgba(255,255,255,0.03); border-radius: 8px; padding: 10px;"></div>
            <div id="hospitalSelectRow" style="display:flex; gap:8px;">
                <select id="targetHospitalSelect" style="flex:1; background: rgba(255,255,255,0.05); border: 1px solid var(--glass-border); border-radius: 8px; padding: 10px; color: var(--white); font-size: 0.85rem;">
                    <option value="">Select a hospital…</option>
                    @foreach($hospitals as $h)
                    <option value="{{ $h->id }}">{{ $h->name }}</option>
                    @endforeach
                </select>
                <button onclick="setTargetHospital()" style="padding: 8px 16px; font-size: 0.78rem; font-weight: 700; background: rgba(59,130,246,0.12); color: #3b82f6; border: 1px solid rgba(59,130,246,0.3); border-radius: 8px; cursor: pointer; white-space: nowrap;">Set</button>
            </div>
        </div>

        <div style="background: var(--glass); border: 1px solid var(--glass-border); border-radius: 12px; padding: 20px; margin-bottom: 30px; text-align: left;">
            <p style="font-size: 0.75rem; color: var(--grey); margin-bottom: 5px; text-transform: uppercase;">Medical ID Summary</p>
            <p id="alertMedical" style="font-weight: 600; color: var(--white);">Blood: O+ | Allergies: None | Asthma</p>
            <div id="alertMamaCare" style="display:none; margin-top:12px; padding-top:12px; border-top:1px solid var(--glass-border);"></div>
        </div>

        <div style="text-align: left; margin-bottom: 20px;">
            <label style="font-size: 0.75rem; color: var(--grey); text-transform: uppercase; display: block; margin-bottom: 6px;">Handoff Notes for Receiving Hospital</label>
            <textarea id="handoffNotes" placeholder="Vitals, treatment given en route..." style="width: 100%; min-height: 60px; background: rgba(255,255,255,0.05); border: 1px solid var(--glass-border); border-radius: 8px; padding: 10px; color: var(--white); font-size: 0.85rem; font-family: inherit;"></textarea>
            <button onclick="saveHandoffNotes()" style="margin-top: 8px; padding: 8px 16px; font-size: 0.78rem; font-weight: 700; background: rgba(59,130,246,0.12); color: #3b82f6; border: 1px solid rgba(59,130,246,0.3); border-radius: 8px; cursor: pointer;">Save Notes</button>
        </div>

        <!-- Pre-accept actions -->
        <div id="preAcceptActions" style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 10px;">
            <button onclick="declineMission()" style="background: rgba(255,255,255,0.05); color: var(--white); border: 1px solid var(--glass-border); padding: 16px; border-radius: 12px; font-weight: 700; cursor: pointer;">Decline</button>
            <button onclick="acceptMission()" style="background: var(--red); color: #fff; border: none; padding: 16px; border-radius: 12px; font-weight: 700; cursor: pointer; box-shadow: 0 10px 20px rgba(229, 9, 20, 0.3);">Accept</button>
            <button onclick="openResponderChat()" style="background: rgba(34,197,94,0.15); color: #22c55e; border: 1px solid rgba(34,197,94,0.3); padding: 16px; border-radius: 12px; font-weight: 700; cursor: pointer;">Chat</button>
        </div>

        <!-- Post-accept actions (mission in progress) -->
        <div id="activeMissionActions" style="display: none; flex-direction: column; gap: 10px;">
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 10px;">
                <button id="markArrivedBtn" onclick="markArrived()" style="background: rgba(59,130,246,0.12); color: #3b82f6; border: 1px solid rgba(59,130,246,0.3); padding: 16px; border-radius: 12px; font-weight: 700; cursor: pointer;">
                    <i data-lucide="map-pin-check" style="width:16px;height:16px;vertical-align:-3px;"></i> Mark Arrived
                </button>
                <button onclick="openResponderChat()" style="background: rgba(34,197,94,0.15); color: #22c55e; border: 1px solid rgba(34,197,94,0.3); padding: 16px; border-radius: 12px; font-weight: 700; cursor: pointer;">Chat</button>
            </div>
            <button onclick="completeMission()" style="width: 100%; background: #22c55e; color: #fff; border: none; padding: 16px; border-radius: 12px; font-weight: 700; cursor: pointer;">
                <i data-lucide="check-circle-2" style="width:16px;height:16px;vertical-align:-3px;"></i> Complete Mission
            </button>
        </div>

        <button onclick="requestDoctorConsult()" style="width: 100%; margin-top: 10px; background: rgba(59,130,246,0.12); color: #3b82f6; border: 1px solid rgba(59,130,246,0.3); padding: 12px; border-radius: 12px; font-weight: 700; font-size: 0.85rem; cursor: pointer; display: flex; align-items: center; justify-content: center; gap: 8px;">
            <i data-lucide="stethoscope" style="width: 16px; height: 16px;"></i> Request Doctor Consult
        </button>
        <a id="navLink" href="#" target="_blank" style="display: none; background: #2563eb; color: #fff; text-decoration: none; padding: 12px; border-radius: 12px; font-weight: 700; font-size: 0.8rem; align-items: center; justify-content: center; gap: 8px; margin-top: 10px;">
            <i data-lucide="navigation"></i> Open Navigation
        </a>
        <button onclick="closeAlert()" style="width: 100%; margin-top: 10px; background: transparent; color: var(--grey); border: none; padding: 8px; font-weight: 700; font-size: 0.8rem; cursor: pointer;">Close</button>
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

<!-- BACKUP REQUEST MODAL -->
<div id="backupModal" style="display:none; position:fixed; inset:0; background:rgba(0,0,0,0.6); z-index:5000; align-items:center; justify-content:center;">
    <div style="background:var(--dark2); border:1px solid rgba(245,158,11,0.3); border-radius:18px; padding:24px; width:90%; max-width:420px;">
        <h3 style="margin:0 0 6px; display:flex; align-items:center; gap:8px;"><i data-lucide="radio-tower" style="color:#f59e0b;"></i> Request Backup</h3>
        <p style="margin:0 0 16px; font-size:0.82rem; color:var(--grey);">Alerts other on-duty {{ Auth::user()->role }} units nearby.</p>
        <textarea id="backupMessage" placeholder="e.g. Need a second unit at scene, situation escalating..." style="width:100%; min-height:90px; background: rgba(255,255,255,0.05); border:1px solid var(--glass-border); color:var(--white); padding:12px 14px; border-radius:12px; font-size:0.88rem; font-family:inherit;"></textarea>
        <div style="display:flex; gap:10px; margin-top:16px; justify-content:flex-end;">
            <button type="button" onclick="closeBackupModal()" style="background: var(--glass); color: var(--white); border:1px solid var(--glass-border); padding:10px 16px; border-radius:10px; font-weight:700; cursor:pointer;">Cancel</button>
            <button type="button" onclick="submitBackupRequest()" style="background: #f59e0b; color: #fff; border:none; padding:10px 16px; border-radius:10px; font-weight:700; cursor:pointer;">Send Alert</button>
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

    function addSatelliteToggle(mapObj, osmLayer) {
        let sat = false;
        const satLayer = L.tileLayer(
            'https://server.arcgisonline.com/ArcGIS/rest/services/World_Imagery/MapServer/tile/{z}/{y}/{x}',
            // Esri's free imagery has real coverage everywhere only up to ~z13; beyond that,
            // many regions return a grey "Map data not yet available" placeholder instead of a
            // tile. Capping maxNativeZoom makes Leaflet re-scale the last real tile instead of
            // requesting zoom levels Esri doesn't have.
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

            // Leaflet renders a blank/mis-sized map if initialized (or resized) while its
            // container is display:none — nudge it once the tab-pane becomes visible.
            if (tabId === 'livemap' && typeof responderMap !== 'undefined') {
                setTimeout(() => responderMap.invalidateSize(), 50);
            }
        });
    });

    let currentAlertId = null;
    let activeMissionUuid = null;
    let modalOpen = false;
    const dismissedAlerts = new Set();
    const siren = new Audio('https://assets.mixkit.co/active_storage/sfx/2568/2568-preview.mp3');
    siren.loop = true;

    function pollAlerts() {
        if (!document.getElementById('dutySwitch').checked) return;

        fetch('{{ route("responder.alerts") }}')
            .then(res => res.json())
            .then(data => {
                renderMissionsList(data);

                const needsAction = data.filter(e => ['pending', 'dispatched'].includes(e.status)).length;
                const badge = document.getElementById('missionsBadge');
                badge.textContent = needsAction;
                badge.style.display = needsAction > 0 ? 'inline-flex' : 'none';

                // Keep the Live Map tab in sync even across a page reload mid-mission —
                // acceptMission() only starts tracking in the live tab it was clicked from,
                // so resync here from whatever the backend says is actually active.
                const mine = data.find(e => ['enroute', 'arrived'].includes(e.status));
                if (mine) {
                    if (activeMissionUuid !== mine.uuid) {
                        activeMissionUuid = mine.uuid;
                        startMissionTracking(mine.uuid);
                    }
                    updateLiveMapPanel(mine);
                } else if (activeMissionUuid) {
                    activeMissionUuid = null;
                    clearLiveMapPanel();
                }

                // Never yank the modal away from a responder actively reviewing it —
                // whether that's a fresh alert or a mission they reopened via "View".
                if (modalOpen) return;

                const next = data.find(e => !dismissedAlerts.has(e.uuid) && ['pending', 'dispatched'].includes(e.status));
                if (next && currentAlertId !== next.uuid) {
                    showEmergency(next);
                }
            });
    }

    function updateLiveMapPanel(mission) {
        document.getElementById('liveMapSub').textContent = 'Tracking active mission.';
        document.getElementById('liveMapMissionPanel').style.display = 'block';
        document.getElementById('liveMapPatientName').textContent = `Patient: ${mission.user ? mission.user.name : 'Unknown'}`;
        document.getElementById('liveMapStatusText').textContent = `Status: ${mission.status.toUpperCase()}`;

        document.getElementById('liveMapNavLink').href =
            `https://www.google.com/maps/dir/?api=1&destination=${mission.latitude},${mission.longitude}`;

        const arrivedBtn = document.getElementById('liveMapArrivedBtn');
        arrivedBtn.disabled = mission.status === 'arrived';
        arrivedBtn.style.opacity = mission.status === 'arrived' ? '0.6' : '1';
        arrivedBtn.innerHTML = mission.status === 'arrived'
            ? '<i data-lucide="map-pin-check"></i> Arrived ✓'
            : '<i data-lucide="map-pin-check"></i> Mark Arrived';
        lucide.createIcons();
    }

    function clearLiveMapPanel() {
        document.getElementById('liveMapMissionPanel').style.display = 'none';
        document.getElementById('liveMapSub').textContent = 'No active mission — showing your current location.';
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
        modalOpen = true;
        const isActive = ['enroute', 'arrived'].includes(alert.status);

        document.getElementById('alertHeading').textContent = isActive ? 'ACTIVE MISSION' : 'CRITICAL ALERT';
        document.getElementById('alertUser').textContent = `Patient: ${alert.user ? alert.user.name : 'Unknown'}`;
        document.getElementById('alertLoc').textContent = `LOCATION: ${alert.latitude}, ${alert.longitude}`;
        document.getElementById('alertMedical').textContent = `Blood: ${alert.user?.blood_group || 'N/A'} | Allergies: ${alert.user?.allergies || 'None'}`;
        document.getElementById('handoffNotes').value = alert.responder_notes || '';

        const hospitalNotice = document.getElementById('hospitalAcceptedNotice');
        const hospitalRow = document.getElementById('hospitalSelectRow');
        if (alert.hospital_accepted_at) {
            hospitalNotice.style.display = 'block';
            hospitalNotice.textContent = `Locked in: ${alert.target_hospital ? alert.target_hospital.name : 'hospital'} has already accepted this patient.`;
            hospitalRow.style.display = 'none';
        } else {
            hospitalNotice.style.display = 'none';
            hospitalRow.style.display = 'flex';
            document.getElementById('targetHospitalSelect').value = alert.target_hospital_id || '';
        }

        const mamaCareDiv = document.getElementById('alertMamaCare');
        if (alert.subtype === 'Labor / Maternity' && alert.user) {
            const highRisk = alert.user.pregnancy_high_risk ? '<span style="color:var(--red);font-weight:bold;margin-left:5px;">(HIGH RISK)</span>' : '';
            mamaCareDiv.style.display = 'block';
            mamaCareDiv.innerHTML = `
                <p style="font-size: 0.75rem; color: #ec4899; margin-bottom: 5px; text-transform: uppercase; font-weight: 800;">
                    <i data-lucide="baby" style="width:14px;height:14px;vertical-align:text-bottom;"></i> Maternity Details
                </p>
                <p style="font-size: 0.8rem; color: var(--white); margin-bottom: 3px;">Due Date: ${alert.user.pregnancy_due_date || 'Unknown'} ${highRisk}</p>
                <p style="font-size: 0.8rem; color: var(--white); margin-bottom: 3px;">Preferred Hospital: ${alert.user.preferred_maternity_hospital || 'None stated'}</p>
                <p style="font-size: 0.8rem; color: var(--white); margin-bottom: 0;">OB/GYN: ${alert.user.obgyn_contact || 'None'}</p>
            `;
        } else {
            mamaCareDiv.style.display = 'none';
            mamaCareDiv.innerHTML = '';
        }

        const navLink = document.getElementById('navLink');
        navLink.href = `https://www.google.com/maps/dir/?api=1&destination=${alert.latitude},${alert.longitude}`;
        navLink.style.display = 'flex';

        document.getElementById('preAcceptActions').style.display = isActive ? 'none' : 'grid';
        document.getElementById('activeMissionActions').style.display = isActive ? 'flex' : 'none';
        const arrivedBtn = document.getElementById('markArrivedBtn');
        arrivedBtn.disabled = alert.status === 'arrived';
        arrivedBtn.innerHTML = alert.status === 'arrived'
            ? '<i data-lucide="map-pin-check" style="width:16px;height:16px;vertical-align:-3px;"></i> Arrived ✓'
            : '<i data-lucide="map-pin-check" style="width:16px;height:16px;vertical-align:-3px;"></i> Mark Arrived';
        arrivedBtn.style.opacity = alert.status === 'arrived' ? '0.6' : '1';

        document.getElementById('emergencyModal').style.display = 'flex';
        lucide.createIcons();

        if (!isActive) {
            siren.play().catch(e => console.log('Audio blocked'));
        }
    }

    function closeAlert() {
        document.getElementById('emergencyModal').style.display = 'none';
        modalOpen = false;
        currentAlertId = null;
        siren.pause();
        siren.currentTime = 0;
    }

    function declineMission() {
        if (!currentAlertId) return;
        const uuid = currentAlertId;

        fetch(`/emergency/decline/${uuid}`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            }
        })
        .then(res => res.json())
        .then(() => {
            dismissedAlerts.add(uuid);
            closeAlert();
        })
        .catch(() => closeAlert());
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
                const acceptedUuid = currentAlertId;
                closeAlert();
                activeMissionUuid = acceptedUuid;
                startMissionTracking(acceptedUuid);
            }
        });
    }

    function markArrived() {
        const uuid = currentAlertId || activeMissionUuid;
        if (!uuid) return;
        fetch(`/emergency/arrived/${uuid}`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            }
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                const arrivedBtn = document.getElementById('markArrivedBtn');
                arrivedBtn.disabled = true;
                arrivedBtn.innerHTML = '<i data-lucide="map-pin-check" style="width:16px;height:16px;vertical-align:-3px;"></i> Arrived ✓';
                arrivedBtn.style.opacity = '0.6';

                const liveMapArrivedBtn = document.getElementById('liveMapArrivedBtn');
                liveMapArrivedBtn.disabled = true;
                liveMapArrivedBtn.innerHTML = '<i data-lucide="map-pin-check"></i> Arrived ✓';
                liveMapArrivedBtn.style.opacity = '0.6';
                lucide.createIcons();
            }
        })
        .catch(() => alert('Network error. Please try again.'));
    }

    function completeMission() {
        const uuid = currentAlertId || activeMissionUuid;
        if (!uuid) return;
        if (!confirm('Complete this mission? This marks it resolved and frees you up for new dispatches.')) return;

        fetch(`/emergency/resolve/${uuid}`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            }
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                alert('Mission completed. You are now available for new dispatches.');
                closeAlert();
                activeMissionUuid = null;
                clearLiveMapPanel();
                if (missionPolling) { clearInterval(missionPolling); missionPolling = null; }
                if (userMarker) { responderMap.removeLayer(userMarker); userMarker = null; }
            }
        })
        .catch(() => alert('Network error. Please try again.'));
    }

    function saveHandoffNotes() {
        if (!currentAlertId) return;
        const notes = document.getElementById('handoffNotes').value;
        fetch(`/emergency/responder-notes/${currentAlertId}`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            },
            body: JSON.stringify({ responder_notes: notes })
        })
        .then(res => res.json())
        .then(data => {
            alert(data.success ? 'Handoff notes saved for the receiving hospital.' : 'Could not save notes.');
        })
        .catch(() => alert('Network error. Please try again.'));
    }

    function setTargetHospital() {
        if (!currentAlertId) return;
        const hospitalId = document.getElementById('targetHospitalSelect').value;
        if (!hospitalId) {
            alert('Please select a hospital first.');
            return;
        }
        fetch(`/emergency/${currentAlertId}/choose-hospital`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            },
            body: JSON.stringify({ hospital_id: hospitalId })
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                alert(`Destination set to ${data.hospital.name}.`);
            } else {
                alert(data.message || 'Could not set destination hospital.');
            }
        })
        .catch(() => alert('Network error. Please try again.'));
    }

    function requestDoctorConsult() {
        if (!currentAlertId) return;
        fetch(`/emergency/request-doctor-consult/${currentAlertId}`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            }
        })
        .then(res => res.json())
        .then(data => {
            alert(data.success ? 'Doctor consult requested. A doctor will join this case.' : 'Could not request a doctor consult.');
        })
        .catch(() => alert('Network error. Please try again.'));
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

                        const liveMapNavLink = document.getElementById('liveMapNavLink');
                        if (liveMapNavLink) {
                            liveMapNavLink.href = `https://www.google.com/maps/dir/?api=1&destination=${userPos[0]},${userPos[1]}`;
                        }
                    }

                    const liveMapStatusText = document.getElementById('liveMapStatusText');
                    if (liveMapStatusText) liveMapStatusText.textContent = `Status: ${data.status.toUpperCase()}`;

                    if (data.status === 'resolved' || data.status === 'cancelled') {
                        clearInterval(missionPolling);
                        missionPolling = null;
                        if (userMarker) responderMap.removeLayer(userMarker);
                        userMarker = null;
                        activeMissionUuid = null;
                        clearLiveMapPanel();
                        alert('Mission ended: ' + data.status);
                    }
                });
        }, 5000);
    }

    // Initialize Map
    let responderMap = L.map('responderMap').setView([6.5244, 3.3792], 13);
    let rTileLayer = L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a>',
        maxZoom: 19
    }).addTo(responderMap);
    // Dark mode filter
    const rPane = responderMap.getPane('tilePane');
    if (rPane) rPane.style.filter = 'invert(92%) hue-rotate(180deg) brightness(95%) contrast(90%)';
    addSatelliteToggle(responderMap, rTileLayer);

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
        dutyText.textContent = isOnDuty ? 'On Duty' : 'Off Duty';
        dutyText.classList.toggle('is-on', isOnDuty);

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

    // Auto-start tracking if already on duty (label already rendered correctly server-side)
    if (document.getElementById('dutySwitch').checked) {
        startTracking();
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

    // ── Unit / hospital profile expand-in-place (fixes dead "View Profile"/"View Details") ──
    function toggleUnitDetails(id) {
        const el = document.getElementById('unitDetails' + id);
        if (el) el.style.display = el.style.display === 'none' ? 'block' : 'none';
    }

    function toggleHospitalDetails(id) {
        const el = document.getElementById('hospitalDetails' + id);
        if (el) el.style.display = el.style.display === 'none' ? 'block' : 'none';
    }

    // ── Backup / mutual-aid requests ──────────────────────────────────────
    function openBackupModal() {
        document.getElementById('backupMessage').value = '';
        document.getElementById('backupModal').style.display = 'flex';
    }

    function closeBackupModal() {
        document.getElementById('backupModal').style.display = 'none';
    }

    function submitBackupRequest() {
        const message = document.getElementById('backupMessage').value.trim();
        const payload = { message, emergency_uuid: currentAlertId || null };

        function send(lat, lng) {
            if (lat !== undefined) { payload.lat = lat; payload.lng = lng; }
            fetch('{{ route("backup-requests.store") }}', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content },
                body: JSON.stringify(payload)
            }).then(res => res.json()).then(data => {
                closeBackupModal();
                if (data.success) {
                    alert('Backup request sent to nearby units.');
                    refreshMyBackupRequests();
                }
            }).catch(() => { closeBackupModal(); alert('Network error. Please try again.'); });
        }

        if (navigator.geolocation) {
            navigator.geolocation.getCurrentPosition(
                (pos) => send(pos.coords.latitude, pos.coords.longitude),
                () => send()
            );
        } else {
            send();
        }
    }

    function renderBackupBanners(requests) {
        const html = requests.map(r => `
            <div style="display:flex; align-items:flex-start; gap:12px; background:rgba(245,158,11,0.08); border:1px solid rgba(245,158,11,0.3); border-radius:12px; padding:14px 16px;">
                <i data-lucide="radio-tower" style="width:18px;height:18px;color:#f59e0b;flex-shrink:0;margin-top:2px;"></i>
                <div style="flex:1; min-width:0;">
                    <strong style="display:block; font-size:0.88rem; color:var(--white); margin-bottom:2px;">${r.unit_name} needs backup</strong>
                    <p style="margin:0; font-size:0.82rem; color:var(--grey);">${r.message || 'No additional details provided.'} &middot; ${r.created_at}</p>
                </div>
                <button onclick="acknowledgeBackup(${r.id})" style="background:#f59e0b; color:#000; border:none; padding:8px 14px; border-radius:8px; font-weight:800; font-size:0.75rem; cursor:pointer; flex-shrink:0;">I'm Responding</button>
            </div>
        `).join('');

        document.getElementById('backupBanners').innerHTML = html;

        const peerList = document.getElementById('peerBackupList');
        peerList.innerHTML = html || '<div style="text-align:center;padding:30px;opacity:0.5;"><i data-lucide="radio-tower" style="width:36px;height:36px;margin-bottom:10px;"></i><p>No pending backup requests from other units.</p></div>';

        const badge = document.getElementById('backupBadge');
        badge.textContent = requests.length;
        badge.style.display = requests.length > 0 ? 'inline-flex' : 'none';

        lucide.createIcons();
    }

    function acknowledgeBackup(id) {
        fetch(`/backup-requests/${id}/acknowledge`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content }
        }).then(() => pollBackupRequests()).catch(() => {});
    }

    function resolveBackupRequest(id) {
        if (!confirm('Mark this backup request as resolved?')) return;
        fetch(`/backup-requests/${id}/resolve`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content }
        }).then(res => res.json()).then(data => {
            if (data.success) refreshMyBackupRequests();
        }).catch(() => alert('Network error. Please try again.'));
    }

    function renderMyBackupList(requests) {
        const list = document.getElementById('myBackupList');
        if (requests.length === 0) {
            list.innerHTML = '<div style="text-align:center;padding:30px;opacity:0.5;"><i data-lucide="send" style="width:36px;height:36px;margin-bottom:10px;"></i><p>You haven\'t sent any backup requests yet.</p></div>';
            lucide.createIcons();
            return;
        }
        list.innerHTML = requests.map(r => `
            <div class="history-item" id="myBackupRow${r.id}">
                <div class="history-info">
                    <div style="width:36px;height:36px;background:rgba(245,158,11,0.1);border-radius:50%;display:flex;align-items:center;justify-content:center;color:#f59e0b;">
                        <i data-lucide="radio-tower" style="width:16px;height:16px;"></i>
                    </div>
                    <div>
                        <p style="font-weight:700;margin:0;font-size:0.88rem;">${r.message || 'No details provided'}</p>
                        <p style="font-size:0.75rem;color:var(--grey);margin:2px 0 0;">${r.created_at}</p>
                    </div>
                </div>
                <div style="display:flex;align-items:center;gap:8px;">
                    <span class="status-badge ${r.status === 'resolved' ? 'status-resolved' : 'status-pending'}">${r.status.toUpperCase()}</span>
                    ${r.status !== 'resolved' ? `<button type="button" onclick="resolveBackupRequest(${r.id})" style="background:var(--glass); border:1px solid var(--glass-border); color:var(--white); padding:6px 10px; border-radius:6px; font-size:0.72rem; font-weight:700; cursor:pointer;">Mark Resolved</button>` : ''}
                </div>
            </div>
        `).join('');
        lucide.createIcons();
    }

    function refreshMyBackupRequests() {
        fetch('{{ route("backup-requests.mine") }}')
            .then(res => res.json())
            .then(renderMyBackupList)
            .catch(() => {});
    }

    function pollBackupRequests() {
        if (!document.getElementById('dutySwitch').checked) return;
        fetch('{{ route("backup-requests.index") }}')
            .then(res => res.json())
            .then(renderBackupBanners)
            .catch(() => {});
    }

    setInterval(pollBackupRequests, 10000);
    pollBackupRequests();

    // ── Unit profile (vehicle reg / capacity) ──────────────────────────
    const profileForm = document.getElementById('profileForm');
    if (profileForm) {
        profileForm.addEventListener('submit', (e) => {
            e.preventDefault();
            const fd = new FormData(profileForm);
            fetch('{{ route("responder.update-profile") }}', {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content },
                body: fd
            })
            .then(res => res.json())
            .then(data => {
                const msg = document.getElementById('profileSaveMsg');
                msg.textContent = data.success ? 'Saved.' : 'Could not save. Please try again.';
                msg.style.background = data.success ? 'rgba(34,197,94,0.1)' : 'rgba(229,9,20,0.1)';
                msg.style.color = data.success ? '#22c55e' : 'var(--red)';
                msg.style.display = 'block';
                setTimeout(() => { msg.style.display = 'none'; }, 4000);
            })
            .catch(() => alert('Network error. Please try again.'));
        });
    }
</script>
<script src="/js/chat.js"></script>
<script src="/js/pwa.js" defer></script>
@include('partials.profile-modal')
</body>
</html>
