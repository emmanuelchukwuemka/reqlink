<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Partner Registration | ResQLink</title>
    <link rel="stylesheet" href="/css/auth.css">
    <script src="https://unpkg.com/lucide@latest"></script>
    <script src="/js/theme.js"></script>
</head>
<body class="auth-page">

<a href="{{ url('/') }}" class="back-home">
    <i data-lucide="arrow-left"></i>
    Back to Home
</a>

<div class="theme-toggle-floating" style="display: flex; align-items: center; gap: 10px;">
    @include('partials.lang-switcher')
    <button id="themeToggle" class="theme-toggle-btn" title="Toggle Theme">
        <i id="themeIcon" data-lucide="sun"></i>
    </button>
</div>

<div class="auth-card" style="max-width: 700px;">
    <div class="auth-header">
        <div class="auth-logo">
            <img src="{{ asset('images/logo.png') }}" alt="ResQLink" style="height: 60px; width: auto; object-fit: contain;">
        </div>
        <h2>Partner Network</h2>
        <p>Register your professional emergency entity</p>

        @if ($errors->any())
            <div style="background: rgba(229, 9, 20, 0.1); color: var(--red); padding: 16px; border-radius: 12px; margin-top: 24px; font-size: 0.9rem; border: 1px solid rgba(229, 9, 20, 0.2); text-align: left;">
                <ul style="margin: 0; padding-left: 20px;">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif
    </div>

    <form class="auth-form" action="{{ route('register') }}" method="POST" enctype="multipart/form-data">
        @csrf
        
        <div class="form-grid" style="grid-template-columns: 1fr 1fr;">
            <!-- ===== SECTION 1: ENTITY DETAILS ===== -->
            <div style="grid-column: span 2; margin-bottom: 10px;">
                <h3 style="font-size: 1.1rem; font-weight: 800; color: var(--red); text-transform: uppercase; letter-spacing: 1px; border-bottom: 1px solid var(--glass-border); padding-bottom: 10px; margin-bottom: 20px;">
                    1. Organization Details
                </h3>
            </div>

            <div class="form-group" style="grid-column: span 2;">
                <label class="field-label"><i data-lucide="building" class="lucide-icon sm"></i> Registered Entity Name</label>
                <input type="text" name="name" value="{{ old('name') }}" placeholder="e.g. Lagos City Medical Center" required>
            </div>

            <div class="form-group">
                <label class="field-label"><i data-lucide="briefcase" class="lucide-icon sm"></i> Partner Category</label>
                <select name="role" required>
                    <option value="" disabled selected>Select Category</option>
                    <option value="doctor">Private Practitioner (Doctor)</option>
                    <option value="hospital">Hospital / Clinic</option>
                    <option value="ambulance">Ambulance Service Provider</option>
                    <option value="security">Security & Rapid Response</option>
                    <option value="fire">Fire Service Unit</option>
                </select>
            </div>

            <div class="form-group">
                <label class="field-label"><i data-lucide="phone" class="lucide-icon sm"></i> Operational Phone</label>
                <input type="tel" name="phone" value="{{ old('phone') }}" placeholder="+234 800 000 0000" required>
            </div>

            <div class="form-group" style="grid-column: span 2;">
                <label class="field-label"><i data-lucide="mail" class="lucide-icon sm"></i> Official Email Address</label>
                <input type="email" name="email" value="{{ old('email') }}" placeholder="admin@organization.com" required>
            </div>

            <!-- ===== SECTION 2: VERIFICATION ===== -->
            <div style="grid-column: span 2; margin-top: 20px; margin-bottom: 10px;">
                <h3 style="font-size: 1.1rem; font-weight: 800; color: var(--red); text-transform: uppercase; letter-spacing: 1px; border-bottom: 1px solid var(--glass-border); padding-bottom: 10px; margin-bottom: 20px;">
                    2. Legal & Licensing
                </h3>
            </div>

            <div class="form-group">
                <label class="field-label"><i data-lucide="file-check" class="lucide-icon sm"></i> Primary License</label>
                <div class="file-upload-wrapper">
                    <input type="file" name="license" required onchange="updateFileName(this)">
                    <div class="file-upload-design">
                        <i data-lucide="upload-cloud"></i>
                        <span class="upload-text">Click to Upload License</span>
                        <span class="upload-hint">PDF, JPG, or PNG (Max 5MB)</span>
                    </div>
                    <div class="file-name-display"></div>
                </div>
            </div>

            <div class="form-group">
                <label class="field-label"><i data-lucide="files" class="lucide-icon sm"></i> Supporting Docs</label>
                <div class="file-upload-wrapper">
                    <input type="file" name="additional_docs" required onchange="updateFileName(this)">
                    <div class="file-upload-design">
                        <i data-lucide="folder-plus"></i>
                        <span class="upload-text">Attach Other Files</span>
                        <span class="upload-hint">CAC, ID, or Certificates</span>
                    </div>
                    <div class="file-name-display"></div>
                </div>
            </div>

            <!-- ===== SECTION 3: SECURITY ===== -->
            <div style="grid-column: span 2; margin-top: 20px; margin-bottom: 10px;">
                <h3 style="font-size: 1.1rem; font-weight: 800; color: var(--red); text-transform: uppercase; letter-spacing: 1px; border-bottom: 1px solid var(--glass-border); padding-bottom: 10px; margin-bottom: 20px;">
                    3. Account Access
                </h3>
            </div>

            <div class="form-group">
                <label class="field-label"><i data-lucide="lock" class="lucide-icon sm"></i> Secure Password</label>
                <div class="auth-input-wrap">
                    <input type="password" name="password" id="partnerPwd" placeholder="••••••••" required>
                    <button type="button" class="auth-eye-btn" onclick="togglePwd('partnerPwd','eyeP1')">
                        <i data-lucide="eye" id="eyeP1"></i>
                    </button>
                </div>
            </div>

            <div class="form-group">
                <label class="field-label"><i data-lucide="shield-check" class="lucide-icon sm"></i> Confirm Access</label>
                <div class="auth-input-wrap">
                    <input type="password" name="password_confirmation" id="partnerPwdConfirm" placeholder="••••••••" required>
                    <button type="button" class="auth-eye-btn" onclick="togglePwd('partnerPwdConfirm','eyeP2')">
                        <i data-lucide="eye" id="eyeP2"></i>
                    </button>
                </div>
            </div>
        </div>

        <button type="submit" class="btn-primary" style="width:100%; padding: 20px; margin-top: 30px; border-radius: 16px; font-size: 1rem;">Submit Partner Application</button>
    </form>

    <div class="auth-footer">
        Looking for individual access? <a href="{{ route('register') }}">Register as User</a><br>
        Already a partner? <a href="{{ route('login') }}">Partner Login</a>
    </div>
</div>

<script>
    lucide.createIcons();
    function togglePwd(id, iconId) {
        const input = document.getElementById(id);
        const icon  = document.getElementById(iconId);
        const show  = input.type === 'password';
        input.type  = show ? 'text' : 'password';
        icon.setAttribute('data-lucide', show ? 'eye-off' : 'eye');
        lucide.createIcons();
    }

    function updateFileName(input) {
        const display = input.parentElement.querySelector('.file-name-display');
        const design = input.parentElement.querySelector('.file-upload-design');
        
        if (input.files && input.files[0]) {
            display.textContent = 'Selected: ' + input.files[0].name;
            display.style.display = 'block';
            design.style.borderColor = 'var(--red)';
            design.style.background = 'rgba(229, 9, 20, 0.08)';
        } else {
            display.style.display = 'none';
            design.style.borderColor = '';
            design.style.background = '';
        }
    }
</script>
</body>
</html>
