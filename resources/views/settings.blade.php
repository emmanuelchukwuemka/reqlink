<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Profile Settings | ResQLink</title>
    <link rel="stylesheet" href="{{ asset('css/dashboard.css') }}">
    <script src="https://unpkg.com/lucide@latest"></script>
    <style>
        .settings-container { max-width: 800px; }
        .settings-section { 
            background: rgba(255, 255, 255, 0.02); 
            border: 1px solid var(--glass-border); 
            border-radius: 12px; 
            padding: 24px; 
            margin-bottom: 24px; 
        }
        .form-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; }
        .form-group label { display: block; font-size: 0.8rem; color: var(--grey); margin-bottom: 8px; font-weight: 600; }
        .form-group input, .form-group select, .form-group textarea {
            width: 100%;
            background: rgba(255,255,255,0.05);
            border: 1px solid var(--glass-border);
            border-radius: 8px;
            padding: 12px;
            color: white;
            font-size: 0.9rem;
        }
    </style>
</head>
<body class="dashboard-layout">

<aside class="sidebar">
    <div class="sidebar-header">
        <div class="auth-logo" style="margin-bottom: 0;">
            <div class="logo-icon">R</div>
            Resq<span style="color:var(--red)">Link</span>
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
    <header class="top-bar">
        <div>
            <h1 style="font-size: 1.5rem; font-weight: 800;">Account Settings</h1>
            <p style="color: var(--grey); font-size: 0.9rem;">Manage your personal and medical information</p>
        </div>
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
