<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>National Oversight | ResQLink Admin</title>
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
        <a href="#" class="nav-item"><i data-lucide="bar-chart-3"></i> System Analytics</a>
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
            <a href="{{ route('admin.command-center') }}" class="btn-primary" style="padding: 10px 20px; font-size: 0.8rem; display: flex; align-items: center; gap: 8px; text-decoration: none;">
                <i data-lucide="radar" style="width: 18px; height: 18px;"></i>
                LIVE COMMAND
            </a>
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
</script>
</body>
</html>
