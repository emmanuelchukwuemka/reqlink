<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Admin Dashboard | ResQLink</title>
    <link rel="manifest" href="/manifest.json">
    <link rel="stylesheet" href="/css/dashboard.css">
    <script src="https://unpkg.com/lucide@latest"></script>
    <style>
        .admin-table { width: 100%; border-collapse: separate; border-spacing: 0 8px; margin-top: 16px; }
        .admin-table th { text-align: left; padding: 12px 15px; color: var(--grey); font-size: 0.75rem; text-transform: uppercase; letter-spacing: 1px; }
        .admin-table tr { background: rgba(255,255,255,0.02); transition: all 0.3s; }
        .admin-table tr:hover { background: rgba(255,255,255,0.05); }
        .admin-table td { padding: 12px 15px; border-top: 1px solid var(--glass-border); border-bottom: 1px solid var(--glass-border); }
        .admin-table td:first-child { border-left: 1px solid var(--glass-border); border-top-left-radius: 10px; border-bottom-left-radius: 10px; }
        .admin-table td:last-child { border-right: 1px solid var(--glass-border); border-top-right-radius: 10px; border-bottom-right-radius: 10px; }
        .role-badge { padding: 3px 10px; border-radius: 100px; font-size: 0.7rem; font-weight: 700; text-transform: uppercase; }
        .role-civilian  { background: rgba(59,130,246,0.12); color: #3b82f6; }
        .role-responder { background: rgba(34,197,94,0.12);  color: #22c55e; }
        .role-admin     { background: rgba(229,9,20,0.12);   color: var(--red); }
        .role-hospital  { background: rgba(168,85,247,0.12); color: #a855f7; }
        .role-fire      { background: rgba(245,158,11,0.12); color: #f59e0b; }
        .role-security  { background: rgba(14,165,233,0.12); color: #0ea5e9; }
        .theme-toggle { background: transparent; border: none; color: var(--grey); cursor: pointer; padding: 8px; border-radius: 50%; transition: all 0.3s; display: flex; align-items: center; justify-content: center; }
        .theme-toggle:hover { background: var(--glass); color: var(--white); }
        :root.light-mode .theme-toggle:hover { background: rgba(0,0,0,0.05); }
        @media (max-width: 768px) { .top-bar { flex-wrap: wrap; gap: 8px; } }

        /* SOS toast */
        #sosToast { position: fixed; top: 24px; right: 24px; z-index: 9999; background: var(--dark); border: 1px solid var(--red); border-radius: 16px; padding: 18px 22px; color: var(--white); min-width: 260px; box-shadow: 0 0 40px rgba(229,9,20,0.35); display: none; animation: slideDown 0.4s ease; }
        @keyframes slideDown { from { transform: translateY(-20px); opacity:0; } to { transform: translateY(0); opacity:1; } }
        .sos-badge { position: relative; display: inline-flex; }
        .sos-badge-count { position: absolute; top: -8px; right: -8px; background: var(--red); color: #fff; font-size: 0.65rem; font-weight: 900; width: 18px; height: 18px; border-radius: 50%; display: flex; align-items: center; justify-content: center; }
        .incident-row { display:flex; justify-content:space-between; align-items:center; padding:12px; background:rgba(229,9,20,0.04); border:1px solid rgba(229,9,20,0.15); border-radius:10px; margin-bottom:8px; }

        /* Stats grid extended */
        .stats-grid-lg { display: grid; grid-template-columns: repeat(auto-fit, minmax(160px, 1fr)); gap: 16px; margin-bottom: 30px; }
        .stat-card-sm { background: var(--glass); border: 1px solid var(--glass-border); border-radius: 14px; padding: 18px 20px; }
        .stat-card-sm .stat-value { font-size: 1.8rem; font-weight: 900; }
        .stat-card-sm .stat-label { font-size: 0.72rem; color: var(--grey); text-transform: uppercase; letter-spacing: 1px; margin-top: 4px; }
        .stat-card-sm .stat-icon { width: 36px; height: 36px; border-radius: 10px; display: flex; align-items: center; justify-content: center; margin-bottom: 12px; }

        /* Search/filter bar */
        .filter-bar { display: flex; gap: 10px; flex-wrap: wrap; margin-bottom: 16px; }
        .filter-bar input, .filter-bar select { background: var(--glass); border: 1px solid var(--glass-border); color: var(--white); padding: 9px 14px; border-radius: 10px; font-size: 0.82rem; outline: none; }
        .filter-bar input { flex: 1; min-width: 200px; }
        .filter-bar input::placeholder { color: var(--grey); }
        .filter-bar input:focus, .filter-bar select:focus { border-color: var(--red); }
        :root.light-mode .filter-bar select option { background: #fff; color: #111; }

        /* Role select inline */
        .role-select-form { display: flex; gap: 5px; align-items: center; }
        .role-select-form select { background: var(--glass); border: 1px solid var(--glass-border); color: var(--white); padding: 4px 8px; border-radius: 7px; font-size: 0.7rem; outline: none; cursor: pointer; }
        .role-select-form button { background: rgba(255,255,255,0.06); border: 1px solid var(--glass-border); color: var(--white); padding: 4px 8px; border-radius: 7px; font-size: 0.7rem; font-weight: 700; cursor: pointer; transition: all 0.2s; }
        .role-select-form button:hover { background: rgba(255,255,255,0.12); }

        /* Flash messages */
        .flash-success { background: rgba(34,197,94,0.12); border: 1px solid rgba(34,197,94,0.3); color: #22c55e; padding: 12px 18px; border-radius: 10px; margin-bottom: 20px; display: flex; align-items: center; gap: 10px; font-size: 0.85rem; font-weight: 600; }
        .flash-error   { background: rgba(229,9,20,0.1);  border: 1px solid rgba(229,9,20,0.3);  color: var(--red);   padding: 12px 18px; border-radius: 10px; margin-bottom: 20px; display: flex; align-items: center; gap: 10px; font-size: 0.85rem; font-weight: 600; }
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
        <a href="{{ route('dashboard') }}" class="nav-item active"><i data-lucide="users"></i> User Management</a>
        <a href="{{ route('admin.command-center') }}" class="nav-item"><i data-lucide="shield-alert" style="color: var(--red);"></i> Command Center</a>
        <a href="{{ route('admin.incidents') }}" class="nav-item"><i data-lucide="activity"></i> Global Incidents</a>
        <a href="{{ route('admin.agencies') }}" class="nav-item"><i data-lucide="building-2"></i> Agency Oversight</a>
        <a href="{{ route('admin.analytics') }}" class="nav-item"><i data-lucide="bar-chart-3"></i> System Analytics</a>
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
            <h1 style="font-size: 1.4rem; font-weight: 800;">User Management</h1>
            <p style="color: var(--grey); font-size: 0.85rem;">{{ $users->count() }} registered accounts</p>
        </div>
        <div style="display: flex; align-items: center; gap: 16px;">
            @include('partials.lang-switcher')
            <div class="sos-badge">
                <a href="{{ route('admin.command-center') }}" class="btn-primary" style="padding: 9px 18px; font-size: 0.8rem; display: flex; align-items: center; gap: 8px; text-decoration: none;">
                    <i data-lucide="radar" style="width: 16px; height: 16px;"></i>
                    LIVE COMMAND
                </a>
                <span class="sos-badge-count" id="cmdBadge" style="display:none;">0</span>
            </div>
            <button id="themeToggle" class="theme-toggle" aria-label="Toggle Dark Mode">
                <i data-lucide="sun" id="themeIcon"></i>
            </button>
            <div class="user-profile">
                <div class="user-info">
                    <span>{{ Auth::user()->name }}</span>
                    <small>System Administrator</small>
                </div>
                <div class="avatar" style="background: var(--red)">{{ substr(Auth::user()->name, 0, 1) }}</div>
            </div>
        </div>
    </header>

    @if(session('success'))
    <div class="flash-success"><i data-lucide="check-circle" style="width:16px;height:16px;flex-shrink:0;"></i> {{ session('success') }}</div>
    @endif
    @if(session('error'))
    <div class="flash-error"><i data-lucide="alert-circle" style="width:16px;height:16px;flex-shrink:0;"></i> {{ session('error') }}</div>
    @endif

    <!-- Stats Grid -->
    <div class="stats-grid-lg">
        <div class="stat-card-sm">
            <div class="stat-icon" style="background:rgba(59,130,246,0.12);color:#3b82f6;">
                <i data-lucide="user" style="width:18px;height:18px;"></i>
            </div>
            <div class="stat-value">{{ $users->where('role', 'civilian')->count() }}</div>
            <div class="stat-label">Civilians</div>
        </div>
        <div class="stat-card-sm">
            <div class="stat-icon" style="background:rgba(168,85,247,0.12);color:#a855f7;">
                <i data-lucide="building-2" style="width:18px;height:18px;"></i>
            </div>
            <div class="stat-value">{{ $hospitalsCount }}</div>
            <div class="stat-label">Hospitals</div>
        </div>
        <div class="stat-card-sm">
            <div class="stat-icon" style="background:rgba(34,197,94,0.12);color:#22c55e;">
                <i data-lucide="truck" style="width:18px;height:18px;"></i>
            </div>
            <div class="stat-value" style="color:#22c55e;">{{ $onDutyRespondersCount }}</div>
            <div class="stat-label">Units On-Duty</div>
        </div>
        <div class="stat-card-sm">
            <div class="stat-icon" style="background:rgba(229,9,20,0.1);color:var(--red);">
                <i data-lucide="siren" style="width:18px;height:18px;"></i>
            </div>
            <div class="stat-value" style="color:var(--red);">{{ $activeEmergenciesCount }}</div>
            <div class="stat-label">Active Incidents</div>
        </div>
        <div class="stat-card-sm">
            <div class="stat-icon" style="background:rgba(245,158,11,0.12);color:#f59e0b;">
                <i data-lucide="shield" style="width:18px;height:18px;"></i>
            </div>
            <div class="stat-value">{{ $respondersCount }}</div>
            <div class="stat-label">Total Responders</div>
        </div>
        <div class="stat-card-sm">
            <div class="stat-icon" style="background:rgba(34,197,94,0.12);color:#22c55e;">
                <i data-lucide="check-circle-2" style="width:18px;height:18px;"></i>
            </div>
            <div class="stat-value" style="color:#22c55e;">{{ $resolvedTodayCount }}</div>
            <div class="stat-label">Resolved Today</div>
        </div>
    </div>

    <!-- SOS toast notification -->
    <div id="sosToast">
        <div style="display:flex;align-items:center;gap:10px;margin-bottom:8px;">
            <i data-lucide="siren" style="color:var(--red);width:18px;height:18px;"></i>
            <span style="font-weight:800;font-size:0.85rem;color:var(--red);">NEW SOS RECEIVED</span>
        </div>
        <p id="toastPatient" style="margin:0 0 2px;font-weight:700;font-size:0.95rem;"></p>
        <p id="toastTime" style="margin:0 0 12px;font-size:0.72rem;color:var(--grey);"></p>
        <div style="display:flex;gap:8px;">
            <a href="{{ route('admin.command-center') }}" style="flex:1;text-align:center;background:var(--red);color:#fff;text-decoration:none;padding:8px;border-radius:8px;font-weight:700;font-size:0.8rem;">View Map</a>
            <button onclick="document.getElementById('sosToast').style.display='none'" style="flex:1;background:rgba(255,255,255,0.05);border:1px solid var(--glass-border);color:var(--white);padding:8px;border-radius:8px;font-weight:700;font-size:0.8rem;cursor:pointer;">Dismiss</button>
        </div>
    </div>

    <!-- Live Incidents Feed -->
    <div class="dash-card" style="margin-bottom: 24px; border-left: 3px solid var(--red);">
        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:14px;">
            <h3 style="margin:0;display:flex;align-items:center;gap:8px;">
                <i data-lucide="siren" style="color:var(--red);"></i> Live Incidents
            </h3>
            <div style="display:flex;align-items:center;gap:10px;">
                <span style="font-size:0.75rem;color:var(--red);font-weight:700;background:rgba(229,9,20,0.1);padding:4px 10px;border-radius:4px;">
                    LIVE · <span id="incidentCount">{{ $activeEmergenciesCount }}</span> ACTIVE
                </span>
                <a href="{{ route('admin.incidents') }}" style="font-size:0.75rem;color:var(--grey);text-decoration:none;padding:4px 10px;border:1px solid var(--glass-border);border-radius:6px;transition:all 0.2s;" onmouseover="this.style.color='var(--white)'" onmouseout="this.style.color='var(--grey)'">
                    View All →
                </a>
            </div>
        </div>
        <div id="incidentFeed">
            <div style="text-align:center;padding:20px;opacity:0.5;"><p>Loading live feed...</p></div>
        </div>
    </div>

    <!-- User Table -->
    <div class="dash-card">
        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:14px;flex-wrap:wrap;gap:10px;">
            <h3 style="margin:0;">User Registry</h3>
            <span style="font-size:0.78rem;color:var(--grey);" id="tableCount"></span>
        </div>

        <!-- Filters -->
        <div class="filter-bar">
            <input type="text" id="userSearch" placeholder="Search by name, email or phone…" oninput="filterTable()">
            <select id="roleFilter" onchange="filterTable()">
                <option value="">All Roles</option>
                <option value="civilian">Civilian</option>
                <option value="ambulance">Ambulance</option>
                <option value="fire">Fire</option>
                <option value="security">Security</option>
                <option value="hospital">Hospital</option>
                <option value="admin">Admin</option>
            </select>
            <select id="statusFilter" onchange="filterTable()">
                <option value="">All Status</option>
                <option value="active">Active</option>
                <option value="suspended">Suspended</option>
            </select>
        </div>

        <div class="table-scroll">
        <table class="admin-table" style="min-width: 800px;" id="userTable">
            <thead>
                <tr>
                    <th>User</th>
                    <th>Role</th>
                    <th>Contact</th>
                    <th>Medical ID</th>
                    <th>Joined</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody id="userTableBody">
                @foreach($users as $user)
                <tr
                    data-name="{{ strtolower($user->name) }}"
                    data-email="{{ strtolower($user->email ?? '') }}"
                    data-phone="{{ $user->phone ?? '' }}"
                    data-role="{{ $user->role }}"
                    data-status="{{ $user->is_suspended ? 'suspended' : 'active' }}"
                >
                    <td>
                        <div style="display: flex; align-items: center; gap: 10px;">
                            <div class="avatar sm" style="background: {{ $user->role === 'civilian' ? '#3b82f6' : ($user->role === 'admin' ? 'var(--red)' : '#22c55e') }}">
                                {{ substr($user->name, 0, 1) }}
                            </div>
                            <div>
                                <div style="font-weight: 700; font-size: 0.88rem;">{{ $user->name }}</div>
                                <div style="font-size: 0.72rem; color: var(--grey);">{{ $user->email ?? 'No Email' }}</div>
                            </div>
                        </div>
                    </td>
                    <td>
                        <span class="role-badge role-{{ in_array($user->role, ['ambulance','fire','security']) ? 'responder' : ($user->role === 'hospital' ? 'hospital' : ($user->role === 'fire' ? 'fire' : ($user->role === 'security' ? 'security' : $user->role))) }}">
                            {{ $user->role }}
                        </span>
                    </td>
                    <td><div style="font-size: 0.82rem;">{{ $user->phone ?? '—' }}</div></td>
                    <td>
                        @if($user->blood_group)
                            <span style="font-size: 0.7rem; background: rgba(229,9,20,0.1); color: var(--red); padding: 2px 6px; border-radius: 4px;">{{ $user->blood_group }}</span>
                        @else
                            <span style="color: var(--grey); font-size: 0.75rem;">—</span>
                        @endif
                    </td>
                    <td><div style="font-size: 0.78rem; color: var(--grey);">{{ $user->created_at->format('M d, Y') }}</div></td>
                    <td>
                        @if($user->is_suspended)
                            <span style="color: var(--red); font-size: 0.75rem; display:flex; align-items:center; gap:4px;">
                                <i data-lucide="shield-off" style="width:13px;"></i> Suspended
                            </span>
                        @else
                            <span style="color: #22c55e; font-size: 0.75rem; display:flex; align-items:center; gap:4px;">
                                <span style="width:6px;height:6px;background:#22c55e;border-radius:50%;"></span> Active
                            </span>
                        @endif
                    </td>
                    <td>
                        @if($user->id !== Auth::id())
                        <div style="display:flex;flex-direction:column;gap:6px;">
                            <form action="{{ route('admin.user.toggle-status', $user->id) }}" method="POST">
                                @csrf
                                <button type="submit" style="width:100%;background:transparent;border:1px solid {{ $user->is_suspended ? '#22c55e' : 'var(--red)' }};color:{{ $user->is_suspended ? '#22c55e' : 'var(--red)' }};padding:4px 10px;border-radius:6px;font-size:0.68rem;font-weight:700;cursor:pointer;">
                                    {{ $user->is_suspended ? 'ACTIVATE' : 'SUSPEND' }}
                                </button>
                            </form>
                            <form action="{{ route('admin.user.role', $user->id) }}" method="POST" class="role-select-form">
                                @csrf
                                <select name="role">
                                    <option value="civilian"  {{ $user->role === 'civilian'  ? 'selected' : '' }}>Civilian</option>
                                    <option value="ambulance" {{ $user->role === 'ambulance' ? 'selected' : '' }}>Ambulance</option>
                                    <option value="fire"      {{ $user->role === 'fire'      ? 'selected' : '' }}>Fire</option>
                                    <option value="security"  {{ $user->role === 'security'  ? 'selected' : '' }}>Security</option>
                                    <option value="hospital"  {{ $user->role === 'hospital'  ? 'selected' : '' }}>Hospital</option>
                                    <option value="admin"     {{ $user->role === 'admin'     ? 'selected' : '' }}>Admin</option>
                                </select>
                                <button type="submit" title="Update role">✓</button>
                            </form>
                        </div>
                        @else
                        <span style="color:var(--grey);font-size:0.72rem;">You</span>
                        @endif
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
        </div>
        <div id="noResults" style="display:none;text-align:center;padding:30px;opacity:0.5;">No users match the current filters.</div>
    </div>
</main>

<script>
    lucide.createIcons();

    // Sidebar toggle
    (function() {
        const btn = document.getElementById('hamburgerBtn');
        const sidebar = document.querySelector('.sidebar');
        const overlay = document.getElementById('sidebarOverlay');
        btn.addEventListener('click', () => { sidebar.classList.toggle('open'); overlay.classList.toggle('active'); });
        overlay.addEventListener('click', () => { sidebar.classList.remove('open'); overlay.classList.remove('active'); });
    })();

    // Client-side table filter
    function filterTable() {
        const search = document.getElementById('userSearch').value.toLowerCase();
        const role   = document.getElementById('roleFilter').value;
        const status = document.getElementById('statusFilter').value;
        const rows   = document.querySelectorAll('#userTableBody tr');
        let visible  = 0;

        rows.forEach(row => {
            const matchSearch = !search ||
                row.dataset.name.includes(search) ||
                row.dataset.email.includes(search) ||
                row.dataset.phone.includes(search);
            const matchRole   = !role   || row.dataset.role   === role;
            const matchStatus = !status || row.dataset.status === status;
            const show = matchSearch && matchRole && matchStatus;
            row.style.display = show ? '' : 'none';
            if (show) visible++;
        });

        document.getElementById('tableCount').textContent = visible + ' of ' + rows.length + ' users';
        document.getElementById('noResults').style.display = visible === 0 ? 'block' : 'none';
    }

    // Init count
    document.getElementById('tableCount').textContent = document.querySelectorAll('#userTableBody tr').length + ' users';

    // Live polling
    const knownIds = new Set();
    let toastTimer = null;

    function statusColor(s) {
        if (s === 'dispatched' || s === 'enroute') return '#22c55e';
        if (s === 'pending') return '#f59e0b';
        if (s === 'arrived') return '#a855f7';
        return 'var(--grey)';
    }

    function renderFeed(emergencies) {
        const feed = document.getElementById('incidentFeed');
        document.getElementById('incidentCount').textContent = emergencies.length;
        const badge = document.getElementById('cmdBadge');
        if (emergencies.length > 0) { badge.textContent = emergencies.length; badge.style.display = 'flex'; }
        else { badge.style.display = 'none'; }

        if (emergencies.length === 0) {
            feed.innerHTML = '<div style="text-align:center;padding:24px;opacity:0.5;"><p>No active incidents right now.</p></div>';
            return;
        }
        const shown = emergencies.slice(0, 5);
        feed.innerHTML = shown.map(e => `
            <div class="incident-row">
                <div style="display:flex;gap:10px;align-items:center;">
                    <div style="width:34px;height:34px;background:rgba(229,9,20,0.1);border-radius:50%;display:flex;align-items:center;justify-content:center;color:var(--red);flex-shrink:0;">
                        <i data-lucide="heart-pulse" style="width:15px;height:15px;"></i>
                    </div>
                    <div>
                        <p style="margin:0;font-weight:700;font-size:0.88rem;">${e.user ? e.user.name : 'Unknown'}</p>
                        <p style="margin:0;font-size:0.7rem;color:var(--grey);">${new Date(e.created_at).toLocaleTimeString()}</p>
                    </div>
                </div>
                <div style="display:flex;align-items:center;gap:8px;">
                    <span style="font-size:0.68rem;font-weight:800;color:${statusColor(e.status)};text-transform:uppercase;background:rgba(255,255,255,0.04);padding:3px 8px;border-radius:5px;">${e.status}</span>
                </div>
            </div>
        `).join('') + (emergencies.length > 5 ? `<div style="text-align:center;padding:10px;"><a href="{{ route('admin.incidents') }}" style="color:var(--grey);font-size:0.78rem;text-decoration:none;">+ ${emergencies.length - 5} more → View all</a></div>` : '');
        lucide.createIcons();
    }

    function showToast(e) {
        document.getElementById('toastPatient').textContent = 'Patient: ' + (e.user ? e.user.name : 'Unknown');
        document.getElementById('toastTime').textContent = new Date(e.created_at).toLocaleTimeString();
        const toast = document.getElementById('sosToast');
        toast.style.display = 'block';
        lucide.createIcons();
        clearTimeout(toastTimer);
        toastTimer = setTimeout(() => { toast.style.display = 'none'; }, 10000);
    }

    function pollAdmin() {
        fetch('/admin/live-data')
            .then(r => r.json())
            .then(data => {
                data.emergencies.forEach(e => {
                    if (!knownIds.has(e.id)) {
                        knownIds.add(e.id);
                        if (knownIds.size > 1) showToast(e);
                    }
                });
                renderFeed(data.emergencies);
            })
            .catch(() => {});
    }

    pollAdmin();
    setInterval(pollAdmin, 5000);
</script>
@include('partials.profile-modal')
</body>
</html>
