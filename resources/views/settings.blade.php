<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Settings | ResQLink</title>
    <link rel="stylesheet" href="/css/dashboard.css">
    <script src="https://cdn.jsdelivr.net/npm/lucide@latest/dist/umd/lucide.min.js"></script>
    <script src="/js/theme.js"></script>
    <style>
        .settings-layout { display: flex; gap: 28px; max-width: 960px; margin: 28px auto 0; }

        /* ── Left menu ── */
        .settings-menu {
            width: 220px; flex-shrink: 0;
            background: var(--glass); border: 1px solid var(--glass-border);
            border-radius: 16px; padding: 12px; height: fit-content; position: sticky; top: 90px;
        }
        .settings-menu-item {
            display: flex; align-items: center; gap: 10px;
            padding: 11px 14px; border-radius: 10px; cursor: pointer;
            font-size: 0.875rem; font-weight: 500; color: var(--grey);
            transition: all 0.2s; border: none; background: none; width: 100%; text-align: left;
        }
        .settings-menu-item:hover { background: rgba(255,255,255,0.05); color: var(--white); }
        .settings-menu-item.active { background: rgba(229,9,20,0.12); color: var(--red); font-weight: 700; }
        .settings-menu-item i { width: 16px; height: 16px; }
        .settings-menu-divider { height: 1px; background: var(--glass-border); margin: 8px 0; }

        /* ── Panels ── */
        .settings-panels { flex: 1; min-width: 0; }
        .settings-panel { display: none; }
        .settings-panel.active { display: block; }

        .settings-card {
            background: var(--glass); border: 1px solid var(--glass-border);
            border-radius: 16px; padding: 28px; margin-bottom: 20px;
        }
        .settings-card-title {
            font-size: 1rem; font-weight: 700; margin-bottom: 20px;
            display: flex; align-items: center; gap: 10px;
        }
        .settings-card-title i { color: var(--red); width: 18px; height: 18px; }

        .form-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 18px; }
        .form-group label { display: block; font-size: 0.75rem; color: var(--grey); margin-bottom: 8px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px; }
        .form-group input, .form-group select, .form-group textarea {
            width: 100%; background: rgba(0,0,0,0.2); border: 1px solid var(--glass-border);
            border-radius: 10px; padding: 12px 14px; color: var(--white); font-size: 0.9rem; transition: all 0.2s; box-sizing: border-box;
        }
        .form-group input:focus, .form-group select:focus, .form-group textarea:focus {
            outline: none; border-color: var(--red); background: rgba(0,0,0,0.4);
            box-shadow: 0 0 0 3px rgba(229,9,20,0.1);
        }
        :root.light-mode .form-group input,
        :root.light-mode .form-group select,
        :root.light-mode .form-group textarea { color: var(--black); }

        .btn-save {
            margin-top: 20px; padding: 12px 28px; background: var(--red); color: #fff;
            border: none; border-radius: 10px; font-size: 0.9rem; font-weight: 700; cursor: pointer; transition: opacity .2s;
        }
        .btn-save:hover { opacity: .85; }
        .btn-save:disabled { opacity: .5; cursor: not-allowed; }

        .toast {
            display: none; padding: 12px 16px; border-radius: 10px;
            font-size: 0.875rem; margin-bottom: 16px;
        }
        .toast.success { background: rgba(34,197,94,0.12); border: 1px solid rgba(34,197,94,0.3); color: #22c55e; }
        .toast.error   { background: rgba(229,9,20,0.1);  border: 1px solid rgba(229,9,20,0.3);  color: #ff6b6b; }

        /* ── Toggle switch ── */
        .toggle-row {
            display: flex; align-items: center; justify-content: space-between;
            padding: 14px 0; border-bottom: 1px solid var(--glass-border);
        }
        .toggle-row:last-child { border-bottom: none; }
        .toggle-label { font-size: 0.9rem; font-weight: 500; }
        .toggle-desc  { font-size: 0.78rem; color: var(--grey); margin-top: 2px; }
        .toggle-switch { position: relative; width: 44px; height: 24px; flex-shrink: 0; }
        .toggle-switch input { opacity: 0; width: 0; height: 0; }
        .toggle-slider {
            position: absolute; inset: 0; background: rgba(255,255,255,0.1);
            border-radius: 24px; cursor: pointer; transition: .3s;
        }
        .toggle-slider::before {
            content: ''; position: absolute; width: 18px; height: 18px;
            left: 3px; top: 3px; background: #fff; border-radius: 50%; transition: .3s;
        }
        .toggle-switch input:checked + .toggle-slider { background: var(--red); }
        .toggle-switch input:checked + .toggle-slider::before { transform: translateX(20px); }

        /* ── Danger zone ── */
        .danger-card { border-color: rgba(229,9,20,0.3) !important; }
        .btn-danger {
            padding: 10px 22px; background: transparent; color: var(--red);
            border: 1px solid var(--red); border-radius: 10px; font-size: 0.875rem;
            font-weight: 600; cursor: pointer; transition: all .2s;
        }
        .btn-danger:hover { background: rgba(229,9,20,0.1); }

        /* ── Avatar ── */
        .profile-avatar-wrap { display: flex; align-items: center; gap: 20px; margin-bottom: 24px; }
        .profile-avatar-circle {
            width: 72px; height: 72px; border-radius: 50%;
            background: linear-gradient(45deg, var(--red), #ff4d4d);
            display: flex; align-items: center; justify-content: center;
            font-size: 1.8rem; font-weight: 900; color: #fff; flex-shrink: 0;
        }

        @media (max-width: 768px) {
            .settings-layout { flex-direction: column; }
            .settings-menu { width: 100%; position: static; display: flex; flex-wrap: wrap; gap: 6px; }
            .settings-menu-divider { display: none; }
            .form-grid { grid-template-columns: 1fr; }
        }
    </style>
</head>
<body class="dashboard-layout">

<aside class="sidebar">
    <div class="sidebar-header">
        <div class="auth-logo" style="margin-bottom:0;">
            <img src="{{ asset('images/logo.png') }}" alt="ResQLink" style="height:100px;width:auto;object-fit:contain;">
        </div>
    </div>
    <nav class="sidebar-nav">
        <a href="{{ route('dashboard') }}" class="nav-item"><i data-lucide="layout-grid"></i> Overview</a>
        <a href="{{ route('settings') }}" class="nav-item active"><i data-lucide="settings"></i> Settings</a>
    </nav>
    <div class="sidebar-footer">
        <form action="{{ route('logout') }}" method="POST" id="logoutForm">@csrf
            <a href="#" onclick="document.getElementById('logoutForm').submit()" class="nav-item" style="color:var(--red);">
                <i data-lucide="log-out"></i> Logout
            </a>
        </form>
    </div>
</aside>

<main class="main-content">
    <header class="top-bar" style="display:flex;align-items:center;justify-content:space-between;">
        <div>
            <h1 style="font-size:1.4rem;font-weight:900;">Settings</h1>
            <p style="color:var(--grey);font-size:0.8rem;">Manage your account, security and preferences</p>
        </div>
        <button id="themeToggle" class="theme-toggle" aria-label="Toggle Dark Mode">
            <i data-lucide="sun" id="themeIcon"></i>
        </button>
    </header>

    <div class="settings-layout">

        <!-- ── LEFT MENU ── -->
        <div class="settings-menu">
            <button class="settings-menu-item active" data-panel="profile">
                <i data-lucide="user"></i> Edit Profile
            </button>
            <button class="settings-menu-item" data-panel="medical">
                <i data-lucide="heart-pulse"></i> Medical ID
            </button>
            <div class="settings-menu-divider"></div>
            <button class="settings-menu-item" data-panel="security">
                <i data-lucide="lock"></i> Security
            </button>
            <button class="settings-menu-item" data-panel="notifications">
                <i data-lucide="bell"></i> Notifications
            </button>
            <button class="settings-menu-item" data-panel="appearance">
                <i data-lucide="palette"></i> Appearance
            </button>
            <div class="settings-menu-divider"></div>
            <button class="settings-menu-item" data-panel="account" style="color:#ef4444;">
                <i data-lucide="trash-2"></i> Account
            </button>
        </div>

        <!-- ── PANELS ── -->
        <div class="settings-panels">

            {{-- ── PROFILE ── --}}
            <div id="panel-profile" class="settings-panel active">
                <div class="settings-card">
                    <div class="profile-avatar-wrap">
                        <div class="profile-avatar-circle">{{ substr(Auth::user()->name, 0, 1) }}</div>
                        <div>
                            <div style="font-weight:700;font-size:1.05rem;">{{ Auth::user()->name }}</div>
                            <div style="color:var(--grey);font-size:0.8rem;">{{ Auth::user()->email }}</div>
                            <div style="color:var(--grey);font-size:0.75rem;margin-top:2px;">Member ID: RESQ-{{ Auth::user()->id }}-{{ date('Y') }}</div>
                        </div>
                    </div>
                    <div class="toast" id="toast-profile"></div>
                    <div class="settings-card-title"><i data-lucide="user"></i> Basic Information</div>
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
                            <div class="form-group" style="grid-column:span 2;">
                                <label>Email Address</label>
                                <input type="email" name="email" value="{{ Auth::user()->email }}" required>
                            </div>
                        </div>
                        <button type="submit" class="btn-save">Save Profile</button>
                    </form>
                </div>
            </div>

            {{-- ── MEDICAL ID ── --}}
            <div id="panel-medical" class="settings-panel">
                <div class="settings-card">
                    <div class="toast" id="toast-medical"></div>
                    <div class="settings-card-title"><i data-lucide="heart-pulse"></i> Medical ID <span style="font-size:0.75rem;font-weight:400;color:var(--grey);margin-left:6px;">Shared with responders during emergencies</span></div>
                    <form id="form-medical">
                        @csrf
                        <div class="form-grid">
                            <div class="form-group">
                                <label>Blood Group</label>
                                <select name="blood_group">
                                    <option value="">Select Blood Group</option>
                                    @foreach(['A+','A-','B+','B-','AB+','AB-','O+','O-'] as $bg)
                                        <option value="{{ $bg }}" {{ Auth::user()->blood_group == $bg ? 'selected' : '' }}>{{ $bg }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="form-group">
                                <label>Emergency Contact Name</label>
                                <input type="text" name="emergency_contact_name" value="{{ Auth::user()->emergency_contact_name }}" placeholder="Next of kin name">
                            </div>
                            <div class="form-group">
                                <label>Emergency Contact Phone</label>
                                <input type="text" name="emergency_contact_phone" value="{{ Auth::user()->emergency_contact_phone }}" placeholder="+234...">
                            </div>
                            <div class="form-group" style="grid-column:span 2;">
                                <label>Known Allergies</label>
                                <textarea name="allergies" rows="2" placeholder="e.g. Penicillin, Peanuts">{{ Auth::user()->allergies }}</textarea>
                            </div>
                            <div class="form-group" style="grid-column:span 2;">
                                <label>Medical Conditions</label>
                                <textarea name="medical_conditions" rows="2" placeholder="e.g. Asthma, Diabetes, Hypertension">{{ Auth::user()->medical_conditions }}</textarea>
                            </div>
                        </div>
                        <button type="submit" class="btn-save">Save Medical ID</button>
                    </form>
                </div>
            </div>

            {{-- ── SECURITY ── --}}
            <div id="panel-security" class="settings-panel">
                <div class="settings-card">
                    <div class="toast" id="toast-security"></div>
                    <div class="settings-card-title"><i data-lucide="lock"></i> Change Password</div>
                    <form id="form-security">
                        @csrf
                        <div class="form-grid">
                            <div class="form-group" style="grid-column:span 2;">
                                <label>Current Password</label>
                                <input type="password" name="current_password" placeholder="Enter your current password" required>
                            </div>
                            <div class="form-group">
                                <label>New Password</label>
                                <input type="password" name="new_password" placeholder="Min 8 characters" required>
                            </div>
                            <div class="form-group">
                                <label>Confirm New Password</label>
                                <input type="password" name="new_password_confirmation" placeholder="Repeat new password" required>
                            </div>
                        </div>
                        <button type="submit" class="btn-save">Update Password</button>
                    </form>
                </div>
                <div class="settings-card">
                    <div class="settings-card-title"><i data-lucide="shield-check"></i> Active Sessions</div>
                    <div style="color:var(--grey);font-size:0.875rem;">
                        <div style="display:flex;align-items:center;justify-content:space-between;padding:12px 0;border-bottom:1px solid var(--glass-border);">
                            <div>
                                <div style="font-weight:600;color:var(--white);">Current Session</div>
                                <div style="font-size:0.78rem;margin-top:2px;">{{ request()->ip() }} &nbsp;·&nbsp; {{ request()->userAgent() ? substr(request()->userAgent(), 0, 60).'...' : 'Unknown' }}</div>
                            </div>
                            <span style="background:rgba(34,197,94,0.15);color:#22c55e;font-size:0.75rem;font-weight:700;padding:4px 10px;border-radius:20px;">Active</span>
                        </div>
                    </div>
                </div>
            </div>

            {{-- ── NOTIFICATIONS ── --}}
            <div id="panel-notifications" class="settings-panel">
                <div class="settings-card">
                    <div class="settings-card-title"><i data-lucide="bell"></i> Notification Preferences</div>
                    <div class="toggle-row">
                        <div>
                            <div class="toggle-label">Emergency Alerts</div>
                            <div class="toggle-desc">Get notified when a nearby emergency is detected</div>
                        </div>
                        <label class="toggle-switch">
                            <input type="checkbox" checked id="notif-emergency">
                            <span class="toggle-slider"></span>
                        </label>
                    </div>
                    <div class="toggle-row">
                        <div>
                            <div class="toggle-label">Responder Dispatched</div>
                            <div class="toggle-desc">Alert when a responder is assigned to your SOS</div>
                        </div>
                        <label class="toggle-switch">
                            <input type="checkbox" checked id="notif-dispatch">
                            <span class="toggle-slider"></span>
                        </label>
                    </div>
                    <div class="toggle-row">
                        <div>
                            <div class="toggle-label">Good Samaritan Requests</div>
                            <div class="toggle-desc">Notify when someone nearby needs a first-aider</div>
                        </div>
                        <label class="toggle-switch">
                            <input type="checkbox" id="notif-samaritan">
                            <span class="toggle-slider"></span>
                        </label>
                    </div>
                    <div class="toggle-row">
                        <div>
                            <div class="toggle-label">Wallet Transactions</div>
                            <div class="toggle-desc">Receive alerts for credits and debits</div>
                        </div>
                        <label class="toggle-switch">
                            <input type="checkbox" checked id="notif-wallet">
                            <span class="toggle-slider"></span>
                        </label>
                    </div>
                    <div class="toggle-row">
                        <div>
                            <div class="toggle-label">ResQLink Updates</div>
                            <div class="toggle-desc">Platform news and feature announcements</div>
                        </div>
                        <label class="toggle-switch">
                            <input type="checkbox" id="notif-updates">
                            <span class="toggle-slider"></span>
                        </label>
                    </div>
                    <button class="btn-save" onclick="saveNotifPrefs()">Save Preferences</button>
                </div>
            </div>

            {{-- ── APPEARANCE ── --}}
            <div id="panel-appearance" class="settings-panel">
                <div class="settings-card">
                    <div class="settings-card-title"><i data-lucide="palette"></i> Theme</div>
                    <div class="toggle-row">
                        <div>
                            <div class="toggle-label">Dark Mode</div>
                            <div class="toggle-desc">Use dark background across all dashboards</div>
                        </div>
                        <label class="toggle-switch">
                            <input type="checkbox" id="theme-toggle-setting">
                            <span class="toggle-slider"></span>
                        </label>
                    </div>
                </div>
                <div class="settings-card">
                    <div class="settings-card-title"><i data-lucide="languages"></i> Language</div>
                    <div class="form-group" style="max-width:260px;">
                        <label>Display Language</label>
                        <select onchange="document.documentElement.lang=this.value">
                            <option value="en" selected>English</option>
                            <option value="yo">Yoruba</option>
                            <option value="ha">Hausa</option>
                            <option value="ig">Igbo</option>
                        </select>
                    </div>
                </div>
            </div>

            {{-- ── ACCOUNT ── --}}
            <div id="panel-account" class="settings-panel">
                <div class="settings-card danger-card">
                    <div class="settings-card-title" style="color:#ef4444;"><i data-lucide="alert-triangle"></i> Danger Zone</div>
                    <div style="display:flex;flex-direction:column;gap:20px;">
                        <div style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:12px;">
                            <div>
                                <div style="font-weight:600;font-size:0.9rem;">Deactivate Account</div>
                                <div style="font-size:0.8rem;color:var(--grey);margin-top:2px;">Temporarily disable your account. You can reactivate anytime.</div>
                            </div>
                            <button class="btn-danger" onclick="if(confirm('Deactivate your account? You will be logged out.')) alert('Please contact support@resqlink.org.ng to deactivate.')">Deactivate</button>
                        </div>
                        <div style="border-top:1px solid rgba(229,9,20,0.2);padding-top:20px;display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:12px;">
                            <div>
                                <div style="font-weight:600;font-size:0.9rem;">Delete Account</div>
                                <div style="font-size:0.8rem;color:var(--grey);margin-top:2px;">Permanently delete your account and all data. This cannot be undone.</div>
                            </div>
                            <button class="btn-danger" onclick="deleteAccount()">Delete Account</button>
                        </div>
                    </div>
                </div>
            </div>

        </div>{{-- end panels --}}
    </div>{{-- end layout --}}
</main>

<script>
lucide.createIcons();

// ── Tab switching ──
document.querySelectorAll('.settings-menu-item[data-panel]').forEach(btn => {
    btn.addEventListener('click', () => {
        document.querySelectorAll('.settings-menu-item').forEach(b => b.classList.remove('active'));
        document.querySelectorAll('.settings-panel').forEach(p => p.classList.remove('active'));
        btn.classList.add('active');
        document.getElementById('panel-' + btn.dataset.panel).classList.add('active');
    });
});

// ── Appearance: sync theme toggle ──
const themeCheck = document.getElementById('theme-toggle-setting');
themeCheck.checked = !document.documentElement.classList.contains('light-mode');
themeCheck.addEventListener('change', () => document.getElementById('themeToggle').click());

// ── Notification prefs (localStorage) ──
['emergency','dispatch','samaritan','wallet','updates'].forEach(key => {
    const el = document.getElementById('notif-' + key);
    const stored = localStorage.getItem('notif_' + key);
    if (stored !== null) el.checked = stored === '1';
});
function saveNotifPrefs() {
    ['emergency','dispatch','samaritan','wallet','updates'].forEach(key => {
        localStorage.setItem('notif_' + key, document.getElementById('notif-' + key).checked ? '1' : '0');
    });
    showToast('toast-security', false);
    const btn = event.target;
    btn.textContent = 'Saved!'; setTimeout(() => btn.textContent = 'Save Preferences', 1500);
}

// ── AJAX form submit helper ──
function submitSettings(formId, toastId, extraFields) {
    const form   = document.getElementById(formId);
    const toast  = document.getElementById(toastId);
    const btn    = form.querySelector('.btn-save');
    const data   = new FormData(form);
    if (extraFields) Object.entries(extraFields).forEach(([k,v]) => data.append(k, v));

    btn.disabled = true; btn.textContent = 'Saving…'; toast.style.display = 'none';

    fetch('{{ route("settings.update") }}', {
        method: 'POST',
        headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'X-Requested-With': 'XMLHttpRequest' },
        body: data,
    })
    .then(r => r.json())
    .then(json => {
        if (json.status) {
            toast.className = 'toast success'; toast.textContent = json.status;
        } else {
            const errs = json.errors ? Object.values(json.errors).flat().join(' ') : (json.message || 'Could not save.');
            toast.className = 'toast error'; toast.textContent = errs;
        }
        toast.style.display = 'block';
    })
    .catch(() => {
        toast.className = 'toast error'; toast.textContent = 'Network error. Please try again.';
        toast.style.display = 'block';
    })
    .finally(() => {
        btn.disabled = false; btn.textContent = btn.dataset.label || 'Save';
        setTimeout(() => toast.style.display = 'none', 4000);
    });
}

function deleteAccount() {
    if (!confirm('Permanently delete your account? This cannot be undone.')) return;
    const password = prompt('Re-enter your password to confirm account deletion:');
    if (!password) return;

    fetch('{{ route("settings.delete-account") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: JSON.stringify({ password }),
    })
    .then(r => r.json().then(data => ({ ok: r.ok, data })))
    .then(({ ok, data }) => {
        if (ok && data.redirect) {
            window.location.href = data.redirect;
        } else {
            const errs = data.errors ? Object.values(data.errors).flat().join(' ') : (data.message || 'Could not delete account.');
            alert(errs);
        }
    })
    .catch(() => alert('Network error. Please try again.'));
}

document.getElementById('form-profile').addEventListener('submit', e => {
    e.preventDefault(); submitSettings('form-profile', 'toast-profile');
});
document.getElementById('form-medical').addEventListener('submit', e => {
    e.preventDefault(); submitSettings('form-medical', 'toast-medical');
});
document.getElementById('form-security').addEventListener('submit', e => {
    e.preventDefault(); submitSettings('form-security', 'toast-security');
});
</script>
</body>
</html>
