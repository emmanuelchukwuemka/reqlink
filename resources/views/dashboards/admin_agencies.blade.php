<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Agency Oversight | ResQLink Admin</title>
    <link rel="manifest" href="/manifest.json">
    <link rel="stylesheet" href="/css/dashboard.css">
    <script src="https://unpkg.com/lucide@latest"></script>
    <style>
        .stats-grid-lg { display: grid; grid-template-columns: repeat(auto-fit, minmax(140px, 1fr)); gap: 12px; margin-bottom: 24px; }
        .stat-card-sm { background: var(--glass); border: 1px solid var(--glass-border); border-radius: 14px; padding: 16px 18px; }
        .stat-card-sm .stat-value { font-size: 1.7rem; font-weight: 900; }
        .stat-card-sm .stat-label { font-size: 0.7rem; color: var(--grey); text-transform: uppercase; letter-spacing: 1px; margin-top: 4px; }
        .stat-card-sm .stat-icon { width: 34px; height: 34px; border-radius: 9px; display: flex; align-items: center; justify-content: center; margin-bottom: 10px; }

        /* Section tabs */
        .section-tabs { display: flex; gap: 0; border-bottom: 2px solid var(--glass-border); margin-bottom: 24px; }
        .sec-tab { padding: 12px 28px; font-size: 0.82rem; font-weight: 700; cursor: pointer; border: none; background: none; color: var(--grey); border-bottom: 2px solid transparent; margin-bottom: -2px; transition: all 0.2s; display: flex; align-items: center; gap: 8px; }
        .sec-tab.active { color: var(--white); border-bottom-color: var(--red); }
        .sec-tab:hover { color: var(--white); }

        /* Tables */
        .agency-table { width: 100%; border-collapse: separate; border-spacing: 0 7px; }
        .agency-table th { text-align: left; padding: 10px 14px; color: var(--grey); font-size: 0.7rem; text-transform: uppercase; letter-spacing: 1px; }
        .agency-table tr { background: rgba(255,255,255,0.02); transition: background 0.2s; }
        .agency-table tr:hover { background: rgba(255,255,255,0.05); }
        .agency-table td { padding: 12px 14px; border-top: 1px solid var(--glass-border); border-bottom: 1px solid var(--glass-border); vertical-align: middle; }
        .agency-table td:first-child { border-left: 1px solid var(--glass-border); border-top-left-radius: 10px; border-bottom-left-radius: 10px; }
        .agency-table td:last-child { border-right: 1px solid var(--glass-border); border-top-right-radius: 10px; border-bottom-right-radius: 10px; }

        /* Responder type badge */
        .type-badge { padding: 3px 9px; border-radius: 100px; font-size: 0.68rem; font-weight: 800; text-transform: uppercase; }
        .type-ambulance { background: rgba(34,197,94,0.12);  color: #22c55e; }
        .type-fire      { background: rgba(245,158,11,0.12); color: #f59e0b; }
        .type-security  { background: rgba(14,165,233,0.12); color: #0ea5e9; }

        /* Duty toggle */
        .duty-btn { padding: 5px 12px; border-radius: 7px; font-size: 0.7rem; font-weight: 700; cursor: pointer; border: 1px solid; transition: all 0.2s; }
        .duty-btn.on  { background: rgba(34,197,94,0.1);  border-color: #22c55e; color: #22c55e; }
        .duty-btn.off { background: rgba(229,9,20,0.08);  border-color: var(--red); color: var(--red); }
        .duty-btn:hover { opacity: 0.75; }

        /* Bed indicator */
        .bed-bar { height: 4px; border-radius: 2px; background: var(--glass-border); margin-top: 4px; overflow: hidden; }
        .bed-fill { height: 100%; border-radius: 2px; background: #22c55e; }

        /* Flash */
        .flash-success { background: rgba(34,197,94,0.1); border: 1px solid rgba(34,197,94,0.3); color: #22c55e; padding: 12px 18px; border-radius: 10px; margin-bottom: 20px; display: flex; align-items: center; gap: 10px; font-size: 0.85rem; font-weight: 600; }

        .theme-toggle { background: transparent; border: none; color: var(--grey); cursor: pointer; padding: 8px; border-radius: 50%; transition: all 0.3s; display: flex; align-items: center; justify-content: center; }
        .theme-toggle:hover { background: var(--glass); color: var(--white); }

        /* Specialty tag */
        .spec-tag { display: inline-block; padding: 2px 7px; background: rgba(14,165,233,0.1); color: #0ea5e9; border-radius: 5px; font-size: 0.65rem; font-weight: 700; margin: 2px; }
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
        <div style="font-size: 0.6rem; color: var(--red); font-weight: 900; text-transform: uppercase; margin-top: 5px; letter-spacing: 2px;">Admin Portal</div>
    </div>
    <nav class="sidebar-nav">
        <a href="{{ route('dashboard') }}" class="nav-item"><i data-lucide="users"></i> User Management</a>
        <a href="{{ route('admin.command-center') }}" class="nav-item"><i data-lucide="shield-alert" style="color: var(--red);"></i> Command Center</a>
        <a href="{{ route('admin.incidents') }}" class="nav-item"><i data-lucide="activity"></i> Global Incidents</a>
        <a href="{{ route('admin.agencies') }}" class="nav-item active"><i data-lucide="building-2"></i> Agency Oversight</a>
        <a href="{{ route('admin.analytics') }}" class="nav-item"><i data-lucide="bar-chart-3"></i> System Analytics</a>
        <a href="{{ route('admin.blog.index') }}" class="nav-item"><i data-lucide="newspaper"></i> Blog & News</a>
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
            <h1 style="font-size: 1.4rem; font-weight: 800;">Agency Oversight</h1>
            <p style="color: var(--grey); font-size: 0.85rem;">Hospitals, responder units, and partner agencies</p>
        </div>
        <div class="topbar-actions">
            @include('partials.lang-switcher')
            <a href="{{ route('admin.command-center') }}" class="btn-primary" style="padding: 9px 18px; font-size: 0.8rem; display: flex; align-items: center; gap: 8px; text-decoration: none;">
                <i data-lucide="radar" style="width: 16px; height: 16px;"></i>
                LIVE COMMAND
            </a>
            <form action="{{ route('logout') }}" method="POST" class="topbar-logout-form">
                @csrf
                <button type="submit" class="topbar-logout">
                    <i data-lucide="log-out" style="width:16px;height:16px;"></i>
                    Logout
                </button>
            </form>
            <button id="themeToggle" class="theme-toggle" aria-label="Toggle Dark Mode">
                <i data-lucide="sun" id="themeIcon"></i>
            </button>
            <div class="user-profile">
                <div class="user-info">
                    <span>{{ Auth::user()->name }}</span>
                    <small>Administrator</small>
                </div>
                <div class="avatar" style="background: var(--red)">{{ substr(Auth::user()->name, 0, 1) }}</div>
            </div>
        </div>
    </header>

    @if(session('success'))
    <div class="flash-success"><i data-lucide="check-circle" style="width:16px;height:16px;flex-shrink:0;"></i> {{ session('success') }}</div>
    @endif

    <!-- Stats -->
    <div class="stats-grid-lg">
        <div class="stat-card-sm">
            <div class="stat-icon" style="background:rgba(168,85,247,0.12);color:#a855f7;">
                <i data-lucide="building-2" style="width:16px;height:16px;"></i>
            </div>
            <div class="stat-value" style="color:#a855f7;">{{ $stats['hospitals'] }}</div>
            <div class="stat-label">Hospitals</div>
        </div>
        <div class="stat-card-sm">
            <div class="stat-icon" style="background:rgba(34,197,94,0.12);color:#22c55e;">
                <i data-lucide="bed" style="width:16px;height:16px;"></i>
            </div>
            <div class="stat-value" style="color:#22c55e;">{{ number_format($stats['available_beds']) }}</div>
            <div class="stat-label">Available Beds</div>
        </div>
        <div class="stat-card-sm">
            <div class="stat-icon" style="background:rgba(255,255,255,0.05);color:var(--grey);">
                <i data-lucide="bed-double" style="width:16px;height:16px;"></i>
            </div>
            <div class="stat-value">{{ number_format($stats['total_beds']) }}</div>
            <div class="stat-label">Total Beds</div>
        </div>
        <div class="stat-card-sm">
            <div class="stat-icon" style="background:rgba(229,9,20,0.1);color:var(--red);">
                <i data-lucide="shield" style="width:16px;height:16px;"></i>
            </div>
            <div class="stat-value">{{ $stats['responders'] }}</div>
            <div class="stat-label">Total Units</div>
        </div>
        <div class="stat-card-sm">
            <div class="stat-icon" style="background:rgba(34,197,94,0.12);color:#22c55e;">
                <i data-lucide="radio" style="width:16px;height:16px;"></i>
            </div>
            <div class="stat-value" style="color:#22c55e;">{{ $stats['on_duty'] }}</div>
            <div class="stat-label">On Duty</div>
        </div>
        <div class="stat-card-sm" style="display:flex;flex-direction:column;justify-content:center;gap:4px;padding:12px 16px;">
            <div style="display:flex;justify-content:space-between;font-size:0.78rem;">
                <span style="color:#22c55e;font-weight:700;">🚑 {{ $stats['ambulances'] }}</span>
                <span style="color:var(--grey);">Ambulance</span>
            </div>
            <div style="display:flex;justify-content:space-between;font-size:0.78rem;">
                <span style="color:#f59e0b;font-weight:700;">🔥 {{ $stats['fire'] }}</span>
                <span style="color:var(--grey);">Fire</span>
            </div>
            <div style="display:flex;justify-content:space-between;font-size:0.78rem;">
                <span style="color:#0ea5e9;font-weight:700;">🛡 {{ $stats['security'] }}</span>
                <span style="color:var(--grey);">Security</span>
            </div>
        </div>
    </div>

    <!-- Section Tabs -->
    <div class="section-tabs">
        <button class="sec-tab active" id="tab-hospitals" onclick="showTab('hospitals')">
            <i data-lucide="building-2" style="width:16px;height:16px;"></i> Hospitals ({{ $stats['hospitals'] }})
        </button>
        <button class="sec-tab" id="tab-responders" onclick="showTab('responders')">
            <i data-lucide="shield" style="width:16px;height:16px;"></i> Responder Units ({{ $stats['responders'] }})
        </button>
    </div>

    <!-- Hospitals Section -->
    <div id="section-hospitals">
        @if($hospitals->isEmpty())
        <div style="text-align:center;padding:60px;opacity:0.5;">
            <i data-lucide="building-2" style="width:48px;height:48px;margin:0 auto 16px;display:block;"></i>
            <p>No hospitals registered yet.</p>
        </div>
        @else
        <div class="dash-card" style="padding:0;overflow:hidden;">
            <div class="table-scroll">
            <table class="agency-table" style="min-width: 900px;">
                <thead>
                    <tr>
                        <th>Hospital</th>
                        <th>Account</th>
                        <th>Beds (Total / Available / ICU)</th>
                        <th>Availability</th>
                        <th>Specialties</th>
                        <th>Contact</th>
                        <th>Location</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($hospitals as $hospital)
                    <tr>
                        <td>
                            <div style="display:flex;align-items:center;gap:10px;">
                                <div style="width:36px;height:36px;border-radius:10px;background:rgba(168,85,247,0.12);display:flex;align-items:center;justify-content:center;color:#a855f7;flex-shrink:0;">
                                    <i data-lucide="building-2" style="width:16px;height:16px;"></i>
                                </div>
                                <div>
                                    <div style="font-weight:700;font-size:0.88rem;">{{ $hospital->name }}</div>
                                    <div style="font-size:0.7rem;color:var(--grey);">ID #{{ $hospital->id }}</div>
                                </div>
                            </div>
                        </td>
                        <td>
                            @if($hospital->user)
                            <div style="font-size:0.82rem;font-weight:600;">{{ $hospital->user->name }}</div>
                            <div style="font-size:0.7rem;color:var(--grey);">{{ $hospital->user->email ?? $hospital->user->phone ?? '' }}</div>
                            @else
                            <span style="color:var(--grey);font-size:0.75rem;">—</span>
                            @endif
                        </td>
                        <td>
                            <div style="font-size:0.82rem;">
                                <span style="font-weight:700;">{{ $hospital->total_beds ?? 0 }}</span>
                                <span style="color:var(--grey);"> / </span>
                                <span style="color:#22c55e;font-weight:700;">{{ $hospital->available_beds ?? 0 }}</span>
                                <span style="color:var(--grey);"> / </span>
                                <span style="color:#a855f7;font-weight:700;">{{ $hospital->icu_beds ?? 0 }}</span>
                            </div>
                        </td>
                        <td style="min-width:100px;">
                            @php $total = max($hospital->total_beds ?? 0, 1); $avail = $hospital->available_beds ?? 0; $pct = round($avail / $total * 100); @endphp
                            <div style="font-size:0.7rem;color:var(--grey);">{{ $pct }}% free</div>
                            <div class="bed-bar" style="width:80px;">
                                <div class="bed-fill" style="width:{{ $pct }}%;background:{{ $pct > 30 ? '#22c55e' : ($pct > 10 ? '#f59e0b' : 'var(--red)') }};"></div>
                            </div>
                        </td>
                        <td>
                            @if($hospital->specialties && count($hospital->specialties) > 0)
                                @foreach(array_slice($hospital->specialties, 0, 3) as $spec)
                                    <span class="spec-tag">{{ $spec }}</span>
                                @endforeach
                                @if(count($hospital->specialties) > 3)
                                    <span style="font-size:0.65rem;color:var(--grey);">+{{ count($hospital->specialties) - 3 }}</span>
                                @endif
                            @else
                                <span style="color:var(--grey);font-size:0.72rem;">General</span>
                            @endif
                        </td>
                        <td style="font-size:0.82rem;">{{ $hospital->contact_phone ?? '—' }}</td>
                        <td>
                            @if($hospital->lat && $hospital->lng)
                                <a href="https://maps.google.com/?q={{ $hospital->lat }},{{ $hospital->lng }}" target="_blank"
                                   style="font-size:0.72rem;color:#0ea5e9;text-decoration:none;display:flex;align-items:center;gap:4px;">
                                    <i data-lucide="map-pin" style="width:12px;height:12px;"></i> View Map
                                </a>
                            @else
                                <span style="color:var(--grey);font-size:0.72rem;">—</span>
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
            </div>
        </div>
        @endif
    </div>

    <!-- Responders Section -->
    <div id="section-responders" style="display:none;">
        @if($responders->isEmpty())
        <div style="text-align:center;padding:60px;opacity:0.5;">
            <i data-lucide="shield" style="width:48px;height:48px;margin:0 auto 16px;display:block;"></i>
            <p>No responder units registered yet.</p>
        </div>
        @else
        <div class="dash-card" style="padding:0;overflow:hidden;">
            <div class="table-scroll">
            <table class="agency-table" style="min-width: 860px;">
                <thead>
                    <tr>
                        <th>Responder</th>
                        <th>Type</th>
                        <th>Vehicle Reg</th>
                        <th>Duty Status</th>
                        <th>Availability</th>
                        <th>Missions</th>
                        <th>Last Ping</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($responders as $responder)
                    <tr>
                        <td>
                            <div style="display:flex;align-items:center;gap:10px;">
                                <div class="avatar sm" style="background: {{ $responder->responder_type === 'ambulance' ? '#22c55e' : ($responder->responder_type === 'fire' ? '#f59e0b' : '#0ea5e9') }};">
                                    {{ $responder->user ? substr($responder->user->name, 0, 1) : 'U' }}
                                </div>
                                <div>
                                    <div style="font-weight:700;font-size:0.88rem;">{{ $responder->user?->name ?? 'Unit #' . $responder->id }}</div>
                                    <div style="font-size:0.7rem;color:var(--grey);">{{ $responder->user?->phone ?? $responder->user?->email ?? '' }}</div>
                                </div>
                            </div>
                        </td>
                        <td><span class="type-badge type-{{ $responder->responder_type ?? 'security' }}">{{ $responder->responder_type ?? '—' }}</span></td>
                        <td style="font-size:0.82rem;font-family:monospace;">{{ $responder->vehicle_reg ?? '—' }}</td>
                        <td>
                            @if($responder->is_on_duty)
                                <span style="color:#22c55e;font-size:0.78rem;display:flex;align-items:center;gap:5px;">
                                    <span style="width:7px;height:7px;background:#22c55e;border-radius:50%;box-shadow:0 0 6px #22c55e;"></span> On Duty
                                </span>
                            @else
                                <span style="color:var(--grey);font-size:0.78rem;display:flex;align-items:center;gap:5px;">
                                    <span style="width:7px;height:7px;background:var(--grey);border-radius:50%;"></span> Off Duty
                                </span>
                            @endif
                        </td>
                        <td>
                            @if($responder->is_available)
                                <span style="font-size:0.72rem;color:#22c55e;font-weight:700;">Available</span>
                            @else
                                <span style="font-size:0.72rem;color:#f59e0b;font-weight:700;">Busy</span>
                            @endif
                        </td>
                        <td style="text-align:center;">
                            <div style="font-size:0.88rem;font-weight:700;">{{ $responder->assigned_emergencies_count ?? 0 }}</div>
                            <div style="font-size:0.65rem;color:var(--grey);">total</div>
                        </td>
                        <td>
                            @if($responder->last_ping)
                                <div style="font-size:0.78rem;">{{ $responder->last_ping->diffForHumans() }}</div>
                            @elseif($responder->current_lat)
                                <div style="font-size:0.72rem;color:var(--grey);">GPS: {{ number_format($responder->current_lat, 3) }}</div>
                            @else
                                <span style="color:var(--grey);font-size:0.72rem;">No ping</span>
                            @endif
                        </td>
                        <td>
                            <form action="{{ route('admin.responder.toggle-duty', $responder->id) }}" method="POST">
                                @csrf
                                <button type="submit" class="duty-btn {{ $responder->is_on_duty ? 'off' : 'on' }}">
                                    {{ $responder->is_on_duty ? 'Pull Off' : 'Activate' }}
                                </button>
                            </form>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
            </div>
        </div>
        @endif
    </div>
</main>

<script>
    lucide.createIcons();

    (function() {
        const btn = document.getElementById('hamburgerBtn');
        const sidebar = document.querySelector('.sidebar');
        const overlay = document.getElementById('sidebarOverlay');
        btn.addEventListener('click', () => { sidebar.classList.toggle('open'); overlay.classList.toggle('active'); });
        overlay.addEventListener('click', () => { sidebar.classList.remove('open'); overlay.classList.remove('active'); });
    })();

    function showTab(name) {
        document.getElementById('section-hospitals').style.display  = name === 'hospitals'  ? '' : 'none';
        document.getElementById('section-responders').style.display = name === 'responders' ? '' : 'none';
        document.getElementById('tab-hospitals').classList.toggle('active',  name === 'hospitals');
        document.getElementById('tab-responders').classList.toggle('active', name === 'responders');
    }

    // Restore tab from URL hash
    if (location.hash === '#responders') showTab('responders');
</script>
</body>
</html>
