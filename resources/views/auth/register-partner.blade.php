<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Partner Registration | ResQLink</title>
    <link rel="stylesheet" href="/css/auth.css">
    <script src="https://unpkg.com/lucide@latest"></script>
    <script src="/js/theme.js"></script>
    <style>
        /* Custom Select Dropdown */
        .custom-select-wrap {
            position: relative;
            width: 100%;
        }
        .custom-select-trigger {
            width: 100%;
            padding: 16px 20px;
            background: rgba(255, 255, 255, 0.04);
            border: 1px solid var(--glass-border);
            border-radius: 16px;
            color: var(--text-muted);
            font-size: 0.95rem;
            font-weight: 500;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: space-between;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            box-sizing: border-box;
        }
        .custom-select-trigger.has-value {
            color: var(--text-main);
        }
        .custom-select-trigger:hover,
        .custom-select-wrap.open .custom-select-trigger {
            border-color: var(--red);
            background: rgba(229, 9, 20, 0.04);
            box-shadow: 0 0 0 3px rgba(229, 9, 20, 0.12);
        }
        .custom-select-trigger svg {
            flex-shrink: 0;
            opacity: 0.5;
            transition: transform 0.2s;
        }
        .custom-select-wrap.open .custom-select-trigger svg {
            transform: rotate(180deg);
        }
        .custom-select-options {
            display: none;
            position: absolute;
            top: calc(100% + 6px);
            left: 0;
            right: 0;
            background: #1a1a1a;
            border: 1px solid rgba(255, 255, 255, 0.12);
            border-radius: 14px;
            padding: 6px;
            z-index: 100;
            box-shadow: 0 16px 48px rgba(0, 0, 0, 0.5);
            max-height: 240px;
            overflow-y: auto;
        }
        .custom-select-wrap.open .custom-select-options {
            display: block;
        }
        .custom-select-option {
            padding: 12px 16px;
            color: #fff;
            font-size: 0.92rem;
            font-weight: 500;
            border-radius: 10px;
            cursor: pointer;
            transition: background 0.15s;
        }
        .custom-select-option:hover {
            background: rgba(229, 9, 20, 0.15);
            color: #fff;
        }
        .custom-select-option.selected {
            background: rgba(229, 9, 20, 0.2);
            color: var(--red);
            font-weight: 700;
        }

        /* Light mode overrides */
        :root.light-mode .custom-select-trigger {
            background: rgba(0, 0, 0, 0.03);
            color: #999;
        }
        :root.light-mode .custom-select-trigger.has-value {
            color: #111;
        }
        :root.light-mode .custom-select-options {
            background: #fff;
            border-color: #ddd;
            box-shadow: 0 16px 48px rgba(0, 0, 0, 0.12);
        }
        :root.light-mode .custom-select-option {
            color: #333;
        }
        :root.light-mode .custom-select-option:hover {
            background: rgba(229, 9, 20, 0.08);
            color: #111;
        }
        :root.light-mode .custom-select-option.selected {
            background: rgba(229, 9, 20, 0.1);
            color: var(--red);
        }
    </style>
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

    <form class="auth-form" action="{{ route('register') }}" method="POST">
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
                <input type="hidden" name="role" id="roleInput" value="{{ old('role', '') }}" required>
                <div class="custom-select-wrap" id="roleSelect">
                    <div class="custom-select-trigger {{ old('role') ? 'has-value' : '' }}" id="roleTrigger">
                        <span id="roleLabel">Select Category</span>
                        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="6 9 12 15 18 9"></polyline></svg>
                    </div>
                    <div class="custom-select-options" id="roleOptions">
                        <div class="custom-select-option {{ old('role') === 'doctor' ? 'selected' : '' }}" data-value="doctor">Private Practitioner (Doctor)</div>
                        <div class="custom-select-option {{ old('role') === 'hospital' ? 'selected' : '' }}" data-value="hospital">Hospital / Clinic</div>
                        <div class="custom-select-option {{ old('role') === 'ambulance' ? 'selected' : '' }}" data-value="ambulance">Ambulance Service Provider</div>
                        <div class="custom-select-option {{ old('role') === 'fire' ? 'selected' : '' }}" data-value="fire">Fire Service Unit</div>
                    </div>
                </div>
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
                    2. Legal &amp; Licensing
                </h3>
            </div>

            <div class="form-group">
                <label class="field-label"><i data-lucide="file-check" class="lucide-icon sm"></i> Primary License</label>
                <input type="url" name="license" value="{{ old('license') }}" placeholder="https://drive.google.com/... (link to your license)" required>
                <span class="upload-hint">Paste a shareable link (Google Drive, Dropbox, etc.) to your PDF, JPG, or PNG</span>
            </div>

            <div class="form-group">
                <label class="field-label"><i data-lucide="files" class="lucide-icon sm"></i> Supporting Docs</label>
                <input type="url" name="additional_docs" value="{{ old('additional_docs') }}" placeholder="https://drive.google.com/... (link to CAC, ID, etc.)" required>
                <span class="upload-hint">Paste a shareable link to your CAC, ID, or Certificates</span>
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
        Already a partner? <a href="{{ route('login') }}">Responder</a>
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

    // Custom select dropdown logic
    (function() {
        const wrap    = document.getElementById('roleSelect');
        const trigger = document.getElementById('roleTrigger');
        const label   = document.getElementById('roleLabel');
        const options = document.getElementById('roleOptions');
        const hidden  = document.getElementById('roleInput');

        trigger.addEventListener('click', function(e) {
            e.stopPropagation();
            wrap.classList.toggle('open');
        });

        options.querySelectorAll('.custom-select-option').forEach(function(opt) {
            opt.addEventListener('click', function(e) {
                e.stopPropagation();
                // Update hidden input
                hidden.value = this.dataset.value;
                // Update label
                label.textContent = this.textContent;
                trigger.classList.add('has-value');
                // Mark selected
                options.querySelectorAll('.custom-select-option').forEach(o => o.classList.remove('selected'));
                this.classList.add('selected');
                // Close
                wrap.classList.remove('open');
            });
        });

        // Close on outside click
        document.addEventListener('click', function() {
            wrap.classList.remove('open');
        });

        // Restore selection on page load (when old('role') has a value)
        if (hidden.value) {
            var selected = options.querySelector('[data-value="' + hidden.value + '"]');
            if (selected) {
                label.textContent = selected.textContent;
                trigger.classList.add('has-value');
                selected.classList.add('selected');
            }
        }
    })();
</script>
</body>
</html>
