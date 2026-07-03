<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Profile Settings | ResQLink</title>
    <link rel="stylesheet" href="/css/dashboard.css">
    <script src="https://unpkg.com/lucide@latest"></script>
    <style>
        .settings-container { max-width: 800px; margin-top: 30px; }
        .settings-section {
            background: var(--glass);
            border: 1px solid var(--glass-border);
            border-radius: 16px;
            padding: 30px;
            margin-bottom: 30px;
            transition: all 0.3s;
        }
        .settings-section:hover { background: var(--glass); border-color: var(--glass-border); }
        .form-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 24px; }
        .form-group label { display: block; font-size: 0.8rem; color: var(--grey); margin-bottom: 10px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px; }
        .form-group input, .form-group select, .form-group textarea {
            width: 100%;
            background: rgba(0,0,0,0.2);
            border: 1px solid var(--glass-border);
            border-radius: 12px;
            padding: 14px 16px;
            color: white;
            font-size: 0.95rem;
            transition: all 0.3s;
        }
        .form-group input:focus, .form-group select:focus, .form-group textarea:focus {
            outline: none;
            border-color: var(--red);
            background: rgba(0,0,0,0.4);
            box-shadow: 0 0 0 4px rgba(229, 9, 20, 0.1);
        }
        .profile-header { display: flex; align-items: center; gap: 20px; margin-bottom: 40px; }
        .avatar-box { width: 80px; height: 80px; border-radius: 50%; background: linear-gradient(45deg, var(--red), #ff4d4d); display: flex; align-items: center; justify-content: center; font-size: 2rem; font-weight: 900; color: white; border: 4px solid rgba(255,255,255,0.1); }
        .theme-toggle { background: transparent; border: none; color: var(--grey); cursor: pointer; padding: 8px; border-radius: 50%; transition: all 0.3s; display: flex; align-items: center; justify-content: center; margin-left: auto; }
        .theme-toggle:hover { background: var(--glass); color: var(--white); }
        :root.light-mode .theme-toggle:hover { background: rgba(0,0,0,0.05); color: var(--black); }
    </style>
    <script src="/js/theme.js"></script>
</head>
<body class="dashboard-layout">

<aside class="sidebar">
    <div class="sidebar-header">
        <div class="auth-logo" style="margin-bottom: 0;">
            <img src="{{ asset('images/logo.png') }}" alt="ResQLink" style="height: 100px; width: auto; object-fit: contain;">
        </div>
    </div>
    
    <nav class="sidebar-nav">
        <a href="{{ route('dashboard') }}" class="nav-item"><i data-lucide="layout-grid"></i> Overview</a>
        <a href="#" class="nav-item active"><i data-lucide="settings"></i> Profile Settings</a>
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
    <header class="top-bar" style="display: flex; align-items: center;">
        <div class="profile-header" style="margin-bottom: 0;">
            <div class="avatar-box" style="width: 50px; height: 50px; font-size: 1.2rem;">
                {{ substr(Auth::user()->name, 0, 1) }}
            </div>
            <div>
                <h1 style="font-size: 1.5rem; font-weight: 900; margin-bottom: 4px;">{{ Auth::user()->name }}</h1>
                <p style="color: var(--grey); font-size: 0.8rem;">Member ID: RESQ-{{ Auth::user()->id }}-{{ date('Y') }}</p>
            </div>
        </div>
        <button id="themeToggle" class="theme-toggle" aria-label="Toggle Dark Mode">
            <i data-lucide="sun" id="themeIcon"></i>
        </button>
    </header>

    @if (session('status'))
        <div style="background: rgba(34, 197, 94, 0.1); color: #22c55e; padding: 16px; border-radius: 8px; margin-bottom: 24px; border: 1px solid rgba(34, 197, 94, 0.2);">
            {{ session('status') }}
        </div>
    @endif

    <form action="{{ route('settings.update') }}" method="POST" class="settings-container">
        @csrf
        <!-- BASIC INFO -->
        <div class="settings-section">
            <h3 style="margin-bottom: 20px;"><i data-lucide="user" style="color: var(--red); vertical-align: middle; margin-right: 10px;"></i> Basic Information</h3>
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
                    <input type="email" name="email" value="{{ Auth::user()->email }}">
                </div>
            </div>
        </div>

        <!-- MEDICAL ID -->
        <div class="settings-section" style="border-color: rgba(229, 9, 20, 0.3);">
            <h3 style="margin-bottom: 20px;"><i data-lucide="heart-pulse" style="color: var(--red); vertical-align: middle; margin-right: 10px;"></i> Medical ID (Life Saving Data)</h3>
            <div class="form-grid">
                <div class="form-group">
                    <label>Blood Group</label>
                    <select name="blood_group">
                        <option value="">Select Blood Group</option>
                        @foreach(['A+', 'A-', 'B+', 'B-', 'AB+', 'AB-', 'O+', 'O-'] as $bg)
                            <option value="{{ $bg }}" {{ Auth::user()->blood_group == $bg ? 'selected' : '' }}>{{ $bg }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group">
                    <label>Emergency Contact Name</label>
                    <input type="text" name="emergency_contact_name" value="{{ Auth::user()->emergency_contact_name }}" placeholder="Next of Kin Name">
                </div>
                <div class="form-group">
                    <label>Emergency Contact Phone</label>
                    <input type="text" name="emergency_contact_phone" value="{{ Auth::user()->emergency_contact_phone }}" placeholder="+234...">
                </div>
                <div class="form-group" style="grid-column: span 2;">
                    <label>Allergies (e.g., Penicillin, Peanuts)</label>
                    <textarea name="allergies" rows="2">{{ Auth::user()->allergies }}</textarea>
                </div>
                <div class="form-group" style="grid-column: span 2;">
                    <label>Medical Conditions (e.g., Asthma, Diabetes)</label>
                    <textarea name="medical_conditions" rows="2">{{ Auth::user()->medical_conditions }}</textarea>
                </div>
            </div>
        </div>

        <button type="submit" class="btn-primary" style="width: 100%; padding: 16px; font-size: 1rem;">Save All Changes</button>
    </form>
</main>

<script>lucide.createIcons();</script>
</body>
</html>
