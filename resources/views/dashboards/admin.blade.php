<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>National Oversight | ResQLink Admin</title>
    <link rel="manifest" href="/manifest.json">
    <link rel="stylesheet" href="/css/dashboard.css">
    <script src="https://unpkg.com/lucide@latest"></script>
    <style>
        .admin-table { width: 100%; border-collapse: separate; border-spacing: 0 10px; margin-top: 20px; }
        .admin-table th { text-align: left; padding: 15px; color: var(--grey); font-size: 0.8rem; text-transform: uppercase; letter-spacing: 1px; }
        .admin-table tr { background: rgba(255,255,255,0.02); transition: all 0.3s; }
        .admin-table tr:hover { background: rgba(255,255,255,0.05); transform: scale(1.005); }
        .admin-table td { padding: 15px; border-top: 1px solid var(--glass-border); border-bottom: 1px solid var(--glass-border); }
        .admin-table td:first-child { border-left: 1px solid var(--glass-border); border-top-left-radius: 12px; border-bottom-left-radius: 12px; }
        .admin-table td:last-child { border-right: 1px solid var(--glass-border); border-top-right-radius: 12px; border-bottom-right-radius: 12px; }
        .role-badge { padding: 4px 12px; border-radius: 100px; font-size: 0.75rem; font-weight: 700; text-transform: uppercase; }
        .role-civilian { background: rgba(59, 130, 246, 0.1); color: #3b82f6; }
        .role-responder { background: rgba(34, 197, 94, 0.1); color: #22c55e; }
        .role-admin { background: rgba(229, 9, 20, 0.1); color: var(--red); }
        .theme-toggle { background: transparent; border: none; color: var(--grey); cursor: pointer; padding: 8px; border-radius: 50%; transition: all 0.3s; display: flex; align-items: center; justify-content: center; }
        .theme-toggle:hover { background: var(--glass); color: var(--white); }
        :root.light-mode .theme-toggle:hover { background: rgba(0,0,0,0.05); color: var(--black); }
        @media (max-width: 768px) { .top-bar { flex-wrap: wrap; gap: 8px; } }

        /* SOS toast */
        #sosToast {
            position: fixed; top: 24px; right: 24px; z-index: 9999;
            background: #0a0a0a; border: 1px solid var(--red); border-radius: 16px;
            padding: 18px 22px; color: #fff; min-width: 260px;
            box-shadow: 0 0 40px rgba(229,9,20,0.35);
            display: none; animation: slideDown 0.4s ease;
        }
        @keyframes slideDown { from { transform: translateY(-20px); opacity:0; } to { transform: translateY(0); opacity:1; } }
        .sos-badge { position: relative; display: inline-flex; }
        .sos-badge-count {
            position: absolute; top: -8px; right: -8px;
            background: var(--red); color: #fff; font-size: 0.65rem; font-weight: 900;
            width: 18px; height: 18px; border-radius: 50%; display: flex; align-items: center; justify-content: center;
        }
        .incident-row { display:flex; justify-content:space-between; align-items:center; padding:12px; background:rgba(229,9,20,0.04); border:1px solid rgba(229,9,20,0.15); border-radius:10px; margin-bottom:8px; }
    </style>
    <script src="/js/theme.js"></script>
</head>
<body class="dashboard-layout">

<div class="sidebar-overlay" id="sidebarOverlay"></div>

<aside class="sidebar">
    <div class="sidebar-header">
        <div class="auth-logo" style="margin-bottom: 0;">
            <img src="{{ asset('images/logo.png') }}" alt="ResQLink" style="height: 50px; width: auto; object-fit: contain;">
        </div>
        <div style="font-size: 0.6rem; color: var(--red); font-weight: 900; text-transform: uppercase; margin-top: 5px; letter-spacing: 2px;">Admin Portal</div>
    </div>
    
    <nav class="sidebar-nav">
        <a href="#" class="nav-item active"><i data-lucide="users"></i> User Management</a>
        <a href="{{ route('admin.command-center') }}" class="nav-item"><i data-lucide="shield-alert" style="color: var(--red);"></i> Command Center</a>
        <a href="#" class="nav-item"><i data-lucide="activity"></i> Global Incidents</a>
        <a href="#" class="nav-item"><i data-lucide="building-2"></i> Agency Oversight</a>
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
            <h1 style="font-size: 1.5rem; font-weight: 800;">National User Registry</h1>
            <p style="color: var(--grey); font-size: 0.9rem;">Total Registered Accounts: {{ $users->count() }}</p>
        </div>
        <div style="display: flex; align-items: center; gap: 20px;">
            @include('partials.lang-switcher')
            <div class="sos-badge">
                <a href="{{ route('admin.command-center') }}" class="btn-primary" style="padding: 10px 20px; font-size: 0.8rem; display: flex; align-items: center; gap: 8px; text-decoration: none;">
                    <i data-lucide="radar" style="width: 18px; height: 18px;"></i>
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

    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-value">{{ $users->where('role', 'civilian')->count() }}</div>
            <div class="stat-label">Total Civilians</div>
        </div>
        <div class="stat-card">
            <div class="stat-value" style="color: #22c55e;">{{ $onDutyRespondersCount }}</div>
            <div class="stat-label">Units On-Duty</div>
        </div>
        <div class="stat-card">
            <div class="stat-value" style="color: var(--red);">{{ $activeEmergenciesCount }}</div>
            <div class="stat-label">Active Incidents</div>
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
            <button onclick="document.getElementById('sosToast').style.display='none'" style="flex:1;background:rgba(255,255,255,0.05);border:1px solid var(--glass-border);color:#fff;padding:8px;border-radius:8px;font-weight:700;font-size:0.8rem;cursor:pointer;">Dismiss</button>
        </div>
    </div>

    <!-- Live Incidents Feed -->
    <div class="dash-card" style="margin-top: 30px; border-left: 3px solid var(--red);">
        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:16px;">
            <h3 style="margin:0;display:flex;align-items:center;gap:8px;">
                <i data-lucide="siren" style="color:var(--red);"></i> Live Incidents
            </h3>
            <span style="font-size:0.75rem;color:var(--red);font-weight:700;background:rgba(229,9,20,0.1);padding:4px 10px;border-radius:4px;">
                LIVE · <span id="incidentCount">{{ $activeEmergenciesCount }}</span> ACTIVE
            </span>
        </div>
        <div id="incidentFeed">
            <div style="text-align:center;padding:30px;opacity:0.5;">
                <p>Loading live feed...</p>
            </div>
        </div>
    </div>

    <div class="dash-card" style="margin-top: 30px;">
        <div class="table-scroll">
        <table class="admin-table" style="min-width: 700px;">
            <thead>
                <tr>
                    <th>User / Entity</th>
                    <th>Role</th>
                    <th>Contact</th>
                    <th>Medical ID</th>
                    <th>Joined</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach($users as $user)
                <tr>
                    <td>
                        <div style="display: flex; align-items: center; gap: 12px;">
                            <div class="avatar sm" style="background: {{ $user->role == 'civilian' ? '#3b82f6' : 'var(--red)' }}">
                                {{ substr($user->name, 0, 1) }}
                            </div>
                            <div>
                                <div style="font-weight: 700;">{{ $user->name }}</div>
                                <div style="font-size: 0.75rem; color: var(--grey);">{{ $user->email ?? 'No Email' }}</div>
                            </div>
                        </div>
                    </td>
                    <td>
                        <span class="role-badge role-{{ $user->role == 'civilian' ? 'civilian' : ($user->role == 'admin' ? 'admin' : 'responder') }}">
                            {{ $user->role }}
                        </span>
                    </td>
                    <td>
                        <div style="font-size: 0.85rem;">{{ $user->phone }}</div>
                    </td>
                    <td>
                        @if($user->blood_group)
                            <div style="display: flex; gap: 5px;">
                                <span style="font-size: 0.7rem; background: rgba(229, 9, 20, 0.1); color: var(--red); padding: 2px 6px; border-radius: 4px;">{{ $user->blood_group }}</span>
                                @if($user->allergies)
                                    <i data-lucide="info" style="width: 12px; color: var(--grey);" title="{{ $user->allergies }}"></i>
                                @endif
                            </div>
                        @else
                            <span style="color: var(--grey); font-size: 0.75rem;">None</span>
                        @endif
                    </td>
                    <td>
                        <div style="font-size: 0.8rem; color: var(--grey);">{{ $user->created_at->format('M d, Y') }}</div>
                    </td>
                    <td>
                        @if($user->is_suspended)
                            <span style="color: var(--red); font-size: 0.75rem; display: flex; align-items: center; gap: 4px;">
                                <i data-lucide="shield-off" style="width: 14px;"></i>
                                Suspended
                            </span>
                        @else
                            <span style="color: #22c55e; font-size: 0.75rem; display: flex; align-items: center; gap: 4px;">
                                <span style="width: 6px; height: 6px; background: #22c55e; border-radius: 50%;"></span>
                                Active
                            </span>
                        @endif
                    </td>
                    <td>
                        @if($user->id !== Auth::id())
                        <form action="{{ route('admin.user.toggle-status', $user->id) }}" method="POST">
                            @csrf
                            <button type="submit" style="background: transparent; border: 1px solid {{ $user->is_suspended ? '#22c55e' : 'var(--red)' }}; color: {{ $user->is_suspended ? '#22c55e' : 'var(--red)' }}; padding: 4px 10px; border-radius: 6px; font-size: 0.7rem; font-weight: 700; cursor: pointer; transition: all 0.3s;">
                                {{ $user->is_suspended ? 'RE-ACTIVATE' : 'SUSPEND' }}
                            </button>
                        </form>
                        @endif
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
        </div>
    </div>
</main>

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

    // ── Live admin polling ────────────────────────────────────────────
    const knownIds = new Set();
    let toastTimer = null;

    function statusColor(s) {
        if (s === 'dispatched' || s === 'enroute') return '#22c55e';
        if (s === 'pending') return '#f59e0b';
        return 'var(--grey)';
    }

    function renderFeed(emergencies) {
        const feed = document.getElementById('incidentFeed');
        document.getElementById('incidentCount').textContent = emergencies.length;

        const badge = document.getElementById('cmdBadge');
        if (emergencies.length > 0) {
            badge.textContent = emergencies.length;
            badge.style.display = 'flex';
        } else {
            badge.style.display = 'none';
        }

        if (emergencies.length === 0) {
            feed.innerHTML = '<div style="text-align:center;padding:30px;opacity:0.5;"><p>No active incidents right now.</p></div>';
            return;
        }

        feed.innerHTML = emergencies.map(e => `
            <div class="incident-row">
                <div style="display:flex;gap:12px;align-items:center;">
                    <div style="width:36px;height:36px;background:rgba(229,9,20,0.1);border-radius:50%;display:flex;align-items:center;justify-content:center;color:var(--red);flex-shrink:0;">
                        <i data-lucide="heart-pulse" style="width:16px;height:16px;"></i>
                    </div>
                    <div>
                        <p style="margin:0;font-weight:700;font-size:0.9rem;">${e.user ? e.user.name : 'Unknown patient'}</p>
                        <p style="margin:0;font-size:0.72rem;color:var(--grey);">${new Date(e.created_at).toLocaleTimeString()}</p>
                    </div>
                </div>
                <span style="font-size:0.7rem;font-weight:800;color:${statusColor(e.status)};text-transform:uppercase;background:rgba(255,255,255,0.04);padding:4px 10px;border-radius:6px;">${e.status}</span>
            </div>
        `).join('');
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
                        if (knownIds.size > 1) showToast(e); // don't toast on first load
                    }
                });
                renderFeed(data.emergencies);
            })
            .catch(() => {});
    }

    pollAdmin(); // immediate first load
    setInterval(pollAdmin, 5000);
</script>
</body>
</html>
