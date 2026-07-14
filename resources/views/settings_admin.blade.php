<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Admin Settings | ResQLink</title>
    <link rel="stylesheet" href="/css/dashboard.css">
    <script src="https://unpkg.com/lucide@latest"></script>
    <script src="/js/theme.js"></script>
    <style>
        .settings-shell { display: flex; gap: 24px; align-items: flex-start; }
        .settings-menu {
            width: 250px; flex-shrink: 0; position: sticky; top: 86px;
            background: var(--glass); border: 1px solid var(--glass-border);
            border-radius: 18px; padding: 14px;
        }
        .settings-menu-label {
            font-size: 0.72rem; color: var(--grey); text-transform: uppercase;
            letter-spacing: 1.4px; margin: 6px 10px 10px; font-weight: 800;
        }
        .settings-menu-item {
            width: 100%; border: none; background: transparent; color: var(--grey);
            display: flex; align-items: center; gap: 10px; padding: 12px 14px;
            border-radius: 12px; text-align: left; cursor: pointer; font-size: 0.88rem;
            font-weight: 700; transition: all 0.2s ease;
        }
        .settings-menu-item:hover { background: rgba(255,255,255,0.05); color: var(--white); }
        .settings-menu-item.active {
            background: rgba(229,9,20,0.12); color: var(--red);
            border: 1px solid rgba(229,9,20,0.22);
        }
        .settings-menu-divider { height: 1px; background: var(--glass-border); margin: 12px 4px; }

        .settings-content { flex: 1; min-width: 0; }
        .settings-panel { display: none; }
        .settings-panel.active { display: block; }

        .settings-card {
            background: var(--glass); border: 1px solid var(--glass-border);
            border-radius: 18px;
        }
        .settings-card { padding: 24px; margin-bottom: 20px; }
        .settings-card-header {
            display: flex; align-items: flex-start; justify-content: space-between;
            gap: 16px; margin-bottom: 20px;
        }
        .settings-card-title {
            display: flex; align-items: center; gap: 10px; font-size: 1rem; font-weight: 800;
        }
        .settings-card-title i { color: var(--red); width: 18px; height: 18px; }
        .settings-card-copy { color: var(--grey); font-size: 0.84rem; line-height: 1.5; margin-top: 6px; }
        .section-badge {
            padding: 6px 10px; border-radius: 999px; font-size: 0.72rem; font-weight: 800;
            color: var(--grey); border: 1px solid var(--glass-border);
        }

        .toast {
            display: none; padding: 12px 16px; border-radius: 12px;
            font-size: 0.86rem; margin-bottom: 16px;
        }
        .toast.success { background: rgba(34,197,94,0.12); border: 1px solid rgba(34,197,94,0.3); color: #22c55e; }
        .toast.error { background: rgba(229,9,20,0.1); border: 1px solid rgba(229,9,20,0.3); color: #ff6b6b; }

        .form-grid { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 18px; }
        .form-group label {
            display: block; font-size: 0.73rem; color: var(--grey); margin-bottom: 8px;
            font-weight: 800; text-transform: uppercase; letter-spacing: 0.8px;
        }
        .form-group input, .form-group select, .form-group textarea {
            width: 100%; box-sizing: border-box; border-radius: 12px;
            border: 1px solid var(--glass-border); background: rgba(0,0,0,0.2);
            padding: 12px 14px; color: var(--white); font-size: 0.9rem; transition: all 0.2s ease;
        }
        .form-group textarea { resize: vertical; min-height: 96px; }
        .form-group input:focus, .form-group select:focus, .form-group textarea:focus {
            outline: none; border-color: var(--red); background: rgba(0,0,0,0.35);
            box-shadow: 0 0 0 3px rgba(229,9,20,0.08);
        }
        :root.light-mode .form-group input,
        :root.light-mode .form-group select,
        :root.light-mode .form-group textarea { color: var(--black); background: rgba(255,255,255,0.92); }

        .info-grid { display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 14px; }
        .info-tile {
            border: 1px solid var(--glass-border); border-radius: 14px;
            padding: 16px; background: rgba(255,255,255,0.02);
        }
        .info-tile-label {
            font-size: 0.72rem; color: var(--grey); text-transform: uppercase;
            letter-spacing: 1px; margin-bottom: 8px; font-weight: 800;
        }
        .info-tile-value { font-size: 0.92rem; font-weight: 700; line-height: 1.45; word-break: break-word; }

        .toggle-row {
            display: flex; align-items: center; justify-content: space-between; gap: 18px;
            padding: 16px 0; border-bottom: 1px solid var(--glass-border);
        }
        .toggle-row:last-child { border-bottom: none; }
        .toggle-label { font-size: 0.92rem; font-weight: 700; }
        .toggle-desc { font-size: 0.8rem; color: var(--grey); margin-top: 4px; line-height: 1.45; max-width: 560px; }
        .toggle-switch { position: relative; width: 48px; height: 26px; flex-shrink: 0; }
        .toggle-switch input { opacity: 0; width: 0; height: 0; }
        .toggle-slider {
            position: absolute; inset: 0; border-radius: 999px; cursor: pointer;
            background: rgba(255,255,255,0.1); transition: 0.25s ease;
        }
        .toggle-slider::before {
            content: ''; position: absolute; width: 20px; height: 20px; left: 3px; top: 3px;
            border-radius: 50%; background: #fff; transition: 0.25s ease;
        }
        .toggle-switch input:checked + .toggle-slider { background: var(--red); }
        .toggle-switch input:checked + .toggle-slider::before { transform: translateX(22px); }

        .btn-save, .btn-secondary {
            margin-top: 18px; padding: 12px 22px; border-radius: 12px; font-size: 0.88rem;
            font-weight: 800; cursor: pointer; transition: opacity 0.2s ease; border: none;
        }
        .btn-save { background: var(--red); color: #fff; }
        .btn-secondary { background: rgba(255,255,255,0.06); color: var(--white); border: 1px solid var(--glass-border); }
        .btn-save:hover, .btn-secondary:hover { opacity: 0.88; }
        .btn-save:disabled { opacity: 0.55; cursor: not-allowed; }

        .inline-actions { display: flex; gap: 12px; flex-wrap: wrap; }
        .support-box {
            display: flex; align-items: flex-start; justify-content: space-between; gap: 18px; flex-wrap: wrap;
            padding: 16px 18px; margin-top: 18px; border-radius: 14px;
            background: rgba(255,255,255,0.02); border: 1px solid var(--glass-border);
        }
        .danger-box {
            border: 1px solid rgba(229,9,20,0.22); background: rgba(229,9,20,0.06);
            border-radius: 16px; padding: 18px;
        }
        .danger-title { color: #ff7b84; font-size: 0.95rem; font-weight: 800; margin-bottom: 6px; }
        .danger-copy { color: var(--grey); font-size: 0.82rem; line-height: 1.5; }

        @media (max-width: 1100px) {
            .info-grid { grid-template-columns: 1fr 1fr; }
        }
        @media (max-width: 820px) {
            .settings-shell { flex-direction: column; }
            .settings-menu { width: 100%; position: static; }
            .form-grid, .info-grid { grid-template-columns: 1fr; }
        }
    </style>
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
        <a href="{{ route('admin.agencies') }}" class="nav-item"><i data-lucide="building-2"></i> Agency Oversight</a>
        <a href="{{ route('admin.analytics') }}" class="nav-item"><i data-lucide="bar-chart-3"></i> System Analytics</a>
        <a href="{{ route('admin.blog.index') }}" class="nav-item"><i data-lucide="newspaper"></i> Blog & News</a>
        <a href="{{ route('settings') }}" class="nav-item active"><i data-lucide="settings"></i> Settings</a>
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
            <h1 style="font-size: 1.4rem; font-weight: 800;">Admin Settings</h1>
            <p style="color: var(--grey); font-size: 0.85rem;">Manage your admin account and workspace preferences</p>
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
                    <small>System Administrator</small>
                </div>
                <div class="avatar" style="background: var(--red)">{{ strtoupper(substr(Auth::user()->name, 0, 1)) }}</div>
            </div>
        </div>
    </header>

    <div class="settings-shell">
        <div class="settings-menu">
            <div class="settings-menu-label">Admin panels</div>
            <button class="settings-menu-item active" data-panel="profile">
                <i data-lucide="user-cog"></i> Profile
            </button>
            <button class="settings-menu-item" data-panel="operations">
                <i data-lucide="map"></i> Command Center
            </button>
            <button class="settings-menu-item" data-panel="alerts">
                <i data-lucide="bell-ring"></i> Alerts
            </button>
            <button class="settings-menu-item" data-panel="appearance">
                <i data-lucide="palette"></i> Appearance
            </button>
            <div class="settings-menu-divider"></div>
            <button class="settings-menu-item" data-panel="account">
                <i data-lucide="badge-info"></i> Account Info
            </button>
        </div>

        <div class="settings-content">
            <section id="panel-profile" class="settings-panel active">
                <div class="settings-card">
                    <div class="toast" id="toast-profile"></div>
                    <div class="settings-card-header">
                        <div>
                            <div class="settings-card-title"><i data-lucide="user-cog"></i> Admin Profile</div>
                            <div class="settings-card-copy">Update the identity details shown across the admin workspace and internal records.</div>
                        </div>
                        <div class="section-badge">Saved to account</div>
                    </div>
                    <form id="form-profile">
                        @csrf
                        <div class="form-grid">
                            <div class="form-group">
                                <label>Full Name</label>
                                <input type="text" name="name" value="{{ Auth::user()->name }}" required>
                            </div>
                            <div class="form-group">
                                <label>Phone Number</label>
                                <input type="text" name="phone" value="{{ Auth::user()->phone }}" required>
                            </div>
                            <div class="form-group" style="grid-column: span 2;">
                                <label>Email Address</label>
                                <input type="email" name="email" value="{{ Auth::user()->email }}" required>
                            </div>
                        </div>
                        <button type="submit" class="btn-save" data-label="Save Profile">Save Profile</button>
                    </form>
                </div>
            </section>

            <section id="panel-operations" class="settings-panel">
                <div class="settings-card">
                    <div class="toast" id="toast-operations"></div>
                    <div class="settings-card-header">
                        <div>
                            <div class="settings-card-title"><i data-lucide="radar"></i> Command Center Preferences</div>
                            <div class="settings-card-copy">Tune how your live map and dispatch workspace behaves during monitoring and response operations.</div>
                        </div>
                        <div class="section-badge">Workspace only</div>
                    </div>
                    <div class="toggle-row">
                        <div>
                            <div class="toggle-label">Auto-focus newest incident</div>
                            <div class="toggle-desc">When a new live emergency arrives, automatically highlight and focus that case in the command center.</div>
                        </div>
                        <label class="toggle-switch">
                            <input type="checkbox" id="pref-auto-focus">
                            <span class="toggle-slider"></span>
                        </label>
                    </div>
                    <div class="toggle-row">
                        <div>
                            <div class="toggle-label">Show route guidance by default</div>
                            <div class="toggle-desc">Automatically draw route lines and ETA details as soon as an incident is selected on the map.</div>
                        </div>
                        <label class="toggle-switch">
                            <input type="checkbox" id="pref-route-default">
                            <span class="toggle-slider"></span>
                        </label>
                    </div>
                    <div class="toggle-row">
                        <div>
                            <div class="toggle-label">Keep map on satellite view</div>
                            <div class="toggle-desc">Start the command center in satellite mode to improve terrain and building visibility.</div>
                        </div>
                        <label class="toggle-switch">
                            <input type="checkbox" id="pref-satellite-default">
                            <span class="toggle-slider"></span>
                        </label>
                    </div>
                    <div class="toggle-row">
                        <div>
                            <div class="toggle-label">Live refresh hints</div>
                            <div class="toggle-desc">Show subtle admin prompts when live incidents, responders, or route data refresh in the dashboard.</div>
                        </div>
                        <label class="toggle-switch">
                            <input type="checkbox" id="pref-refresh-hints">
                            <span class="toggle-slider"></span>
                        </label>
                    </div>
                    <button type="button" class="btn-save" onclick="saveLocalPrefs('operations')">Save Command Center Preferences</button>
                </div>
            </section>

            <section id="panel-alerts" class="settings-panel">
                <div class="settings-card">
                    <div class="toast" id="toast-alerts"></div>
                    <div class="settings-card-header">
                        <div>
                            <div class="settings-card-title"><i data-lucide="bell-ring"></i> Admin Alert Preferences</div>
                            <div class="settings-card-copy">Control which operational events deserve your attention inside the admin environment.</div>
                        </div>
                        <div class="section-badge">Workspace only</div>
                    </div>
                    <div class="toggle-row">
                        <div>
                            <div class="toggle-label">Critical incident alerts</div>
                            <div class="toggle-desc">Highlight life-threatening or escalated emergencies with a stronger visual alert in the admin panel.</div>
                        </div>
                        <label class="toggle-switch">
                            <input type="checkbox" id="alert-critical">
                            <span class="toggle-slider"></span>
                        </label>
                    </div>
                    <div class="toggle-row">
                        <div>
                            <div class="toggle-label">Responder dispatch confirmations</div>
                            <div class="toggle-desc">Show confirmations whenever a responder is assigned, reassigned, arrives, or is marked off-duty during a live case.</div>
                        </div>
                        <label class="toggle-switch">
                            <input type="checkbox" id="alert-dispatch">
                            <span class="toggle-slider"></span>
                        </label>
                    </div>
                    <div class="toggle-row">
                        <div>
                            <div class="toggle-label">New partner registration alerts</div>
                            <div class="toggle-desc">Notify the admin workspace when hospitals, ambulance teams, fire units, or security partners register.</div>
                        </div>
                        <label class="toggle-switch">
                            <input type="checkbox" id="alert-partners">
                            <span class="toggle-slider"></span>
                        </label>
                    </div>
                    <div class="toggle-row">
                        <div>
                            <div class="toggle-label">Blog publishing reminders</div>
                            <div class="toggle-desc">Show reminders for draft posts, unpublished updates, and blog content awaiting release.</div>
                        </div>
                        <label class="toggle-switch">
                            <input type="checkbox" id="alert-blog">
                            <span class="toggle-slider"></span>
                        </label>
                    </div>
                    <button type="button" class="btn-save" onclick="saveLocalPrefs('alerts')">Save Alert Preferences</button>
                </div>
            </section>

            <section id="panel-appearance" class="settings-panel">
                <div class="settings-card">
                    <div class="toast" id="toast-appearance"></div>
                    <div class="settings-card-header">
                        <div>
                            <div class="settings-card-title"><i data-lucide="palette"></i> Appearance & Workspace Style</div>
                            <div class="settings-card-copy">Keep the admin console readable and professional across long monitoring sessions.</div>
                        </div>
                        <div class="section-badge">Workspace only</div>
                    </div>
                    <div class="toggle-row">
                        <div>
                            <div class="toggle-label">Dark mode</div>
                            <div class="toggle-desc">Use the darker workspace theme across admin pages and live operations screens.</div>
                        </div>
                        <label class="toggle-switch">
                            <input type="checkbox" id="theme-toggle-setting">
                            <span class="toggle-slider"></span>
                        </label>
                    </div>
                    <div class="toggle-row">
                        <div>
                            <div class="toggle-label">Compact cards</div>
                            <div class="toggle-desc">Prefer a denser dashboard layout with tighter cards and less whitespace for more data on screen.</div>
                        </div>
                        <label class="toggle-switch">
                            <input type="checkbox" id="appearance-compact">
                            <span class="toggle-slider"></span>
                        </label>
                    </div>
                    <div class="toggle-row">
                        <div>
                            <div class="toggle-label">High-contrast badges</div>
                            <div class="toggle-desc">Use stronger status colors for incident labels, cards, and command center indicators.</div>
                        </div>
                        <label class="toggle-switch">
                            <input type="checkbox" id="appearance-contrast">
                            <span class="toggle-slider"></span>
                        </label>
                    </div>
                    <div class="inline-actions">
                        <button type="button" class="btn-save" onclick="saveLocalPrefs('appearance')">Save Appearance</button>
                        <button type="button" class="btn-secondary" onclick="document.getElementById('themeToggle').click()">Toggle Theme Now</button>
                    </div>
                </div>
            </section>

            <section id="panel-account" class="settings-panel">
                <div class="settings-card">
                    <div class="settings-card-header">
                        <div>
                            <div class="settings-card-title"><i data-lucide="badge-info"></i> Administrator Account Details</div>
                            <div class="settings-card-copy">Reference information for this administrator profile and workspace access.</div>
                        </div>
                        <div class="section-badge">Read only</div>
                    </div>
                    <div class="info-grid">
                        <div class="info-tile">
                            <div class="info-tile-label">Admin ID</div>
                            <div class="info-tile-value">RESQ-ADMIN-{{ Auth::user()->id }}</div>
                        </div>
                        <div class="info-tile">
                            <div class="info-tile-label">Current IP</div>
                            <div class="info-tile-value">{{ request()->ip() }}</div>
                        </div>
                        <div class="info-tile">
                            <div class="info-tile-label">Role</div>
                            <div class="info-tile-value">{{ ucfirst(Auth::user()->role) }}</div>
                        </div>
                        <div class="info-tile">
                            <div class="info-tile-label">Phone</div>
                            <div class="info-tile-value">{{ Auth::user()->phone ?: 'Not set' }}</div>
                        </div>
                        <div class="info-tile">
                            <div class="info-tile-label">Email</div>
                            <div class="info-tile-value">{{ Auth::user()->email ?: 'Not set' }}</div>
                        </div>
                        <div class="info-tile">
                            <div class="info-tile-label">Device</div>
                            <div class="info-tile-value">{{ request()->userAgent() ? substr(request()->userAgent(), 0, 90) . '...' : 'Unknown browser session' }}</div>
                        </div>
                    </div>

                    <div class="support-box">
                        <div>
                            <div style="font-size: 0.9rem; font-weight: 800; margin-bottom: 6px;">Admin support</div>
                            <div style="font-size: 0.82rem; color: var(--grey); line-height: 1.5;">For role changes, admin account deactivation, or access recovery, contact the ResQLink technical support team or another verified super administrator.</div>
                        </div>
                        <a href="mailto:support@resqlink.org.ng" class="btn-secondary" style="text-decoration:none; display:inline-flex; align-items:center;">Contact Support</a>
                    </div>

                    <div class="danger-box" style="margin-top: 18px;">
                        <div class="danger-title">Protected administrator account</div>
                        <div class="danger-copy">Deletion and deactivation are intentionally not exposed here to avoid accidental lockouts of core platform administrators. Use formal support or controlled backend procedures for sensitive account changes.</div>
                    </div>
                </div>
            </section>
        </div>
    </div>
</main>

<script>
lucide.createIcons();

(function () {
    const btn = document.getElementById('hamburgerBtn');
    const sidebar = document.querySelector('.sidebar');
    const overlay = document.getElementById('sidebarOverlay');
    btn.addEventListener('click', () => {
        sidebar.classList.toggle('open');
        overlay.classList.toggle('active');
    });
    overlay.addEventListener('click', () => {
        sidebar.classList.remove('open');
        overlay.classList.remove('active');
    });
})();

document.querySelectorAll('.settings-menu-item[data-panel]').forEach((btn) => {
    btn.addEventListener('click', () => {
        document.querySelectorAll('.settings-menu-item').forEach((item) => item.classList.remove('active'));
        document.querySelectorAll('.settings-panel').forEach((panel) => panel.classList.remove('active'));
        btn.classList.add('active');
        document.getElementById('panel-' + btn.dataset.panel).classList.add('active');
    });
});

const themeCheck = document.getElementById('theme-toggle-setting');
themeCheck.checked = !document.documentElement.classList.contains('light-mode');
themeCheck.addEventListener('change', () => document.getElementById('themeToggle').click());

const PREF_GROUPS = {
    operations: ['pref-auto-focus', 'pref-route-default', 'pref-satellite-default', 'pref-refresh-hints'],
    alerts: ['alert-critical', 'alert-dispatch', 'alert-partners', 'alert-blog'],
    appearance: ['appearance-compact', 'appearance-contrast']
};

Object.values(PREF_GROUPS).flat().forEach((id) => {
    const el = document.getElementById(id);
    const stored = localStorage.getItem('admin_' + id);
    if (stored !== null) {
        el.checked = stored === '1';
    }
});

function showToast(id, type, message) {
    const toast = document.getElementById(id);
    toast.className = 'toast ' + type;
    toast.textContent = message;
    toast.style.display = 'block';
    setTimeout(() => {
        toast.style.display = 'none';
    }, 3500);
}

function saveLocalPrefs(group) {
    (PREF_GROUPS[group] || []).forEach((id) => {
        const el = document.getElementById(id);
        localStorage.setItem('admin_' + id, el.checked ? '1' : '0');
    });
    showToast('toast-' + group, 'success', 'Preferences saved for this admin workspace.');
}

function submitSettings(formId, toastId) {
    const form = document.getElementById(formId);
    const toast = document.getElementById(toastId);
    const btn = form.querySelector('.btn-save');
    const data = new FormData(form);

    btn.disabled = true;
    btn.textContent = 'Saving...';
    toast.style.display = 'none';

    fetch('{{ route("settings.update") }}', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: data
    })
    .then((response) => response.json())
    .then((json) => {
        if (json.status) {
            toast.className = 'toast success';
            toast.textContent = json.status;
        } else {
            const errors = json.errors ? Object.values(json.errors).flat().join(' ') : (json.message || 'Could not save.');
            toast.className = 'toast error';
            toast.textContent = errors;
        }
        toast.style.display = 'block';
    })
    .catch(() => {
        toast.className = 'toast error';
        toast.textContent = 'Network error. Please try again.';
        toast.style.display = 'block';
    })
    .finally(() => {
        btn.disabled = false;
        btn.textContent = btn.dataset.label || 'Save';
        setTimeout(() => {
            toast.style.display = 'none';
        }, 4000);
    });
}

document.getElementById('form-profile').addEventListener('submit', (event) => {
    event.preventDefault();
    submitSettings('form-profile', 'toast-profile');
});

</script>
</body>
</html>
