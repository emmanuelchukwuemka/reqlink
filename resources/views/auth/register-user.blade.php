<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Join ResQLink | Create Account</title>
    <link rel="stylesheet" href="/css/auth.css">
    <script src="https://unpkg.com/lucide@latest"></script>
    <script src="/js/theme.js"></script>
</head>
<body class="auth-split-page">

<div class="auth-split-wrap">

    <!-- LEFT PANEL -->
    <div class="auth-split-left">
        <a href="{{ url('/') }}" class="auth-back-link">
            <i data-lucide="arrow-left"></i> Back to Home
        </a>
        <div class="auth-split-brand">
            <img src="{{ asset('images/logo.png') }}" alt="ResQLink" class="auth-brand-logo">
            <h1 class="auth-brand-name">ResQLink</h1>
            <p class="auth-brand-tagline">Saving lives through instant connection</p>
        </div>
        <div class="auth-split-perks">
            <div class="auth-perk">
                <div class="auth-perk-icon"><i data-lucide="zap"></i></div>
                <div>
                    <strong>One-Tap SOS</strong>
                    <span>Trigger emergency help instantly from anywhere</span>
                </div>
            </div>
            <div class="auth-perk">
                <div class="auth-perk-icon"><i data-lucide="map-pin"></i></div>
                <div>
                    <strong>Live GPS Tracking</strong>
                    <span>Responders reach you with real-time location</span>
                </div>
            </div>
            <div class="auth-perk">
                <div class="auth-perk-icon"><i data-lucide="hospital"></i></div>
                <div>
                    <strong>Smart Hospital Routing</strong>
                    <span>Connected to nearest hospitals with live bed data</span>
                </div>
            </div>
            <div class="auth-perk">
                <div class="auth-perk-icon"><i data-lucide="shield-check"></i></div>
                <div>
                    <strong>24/7 Emergency Coverage</strong>
                    <span>Always active, always protecting you</span>
                </div>
            </div>
        </div>
        <div class="auth-split-badge">
            <div class="auth-split-badge-dot"></div>
            <span>ResQLink is live and active in your region</span>
        </div>
    </div>

    <!-- RIGHT PANEL -->
    <div class="auth-split-right">
        <button id="themeToggle" class="auth-theme-toggle" title="Toggle Theme">
            <i id="themeIcon" data-lucide="sun"></i>
        </button>
        <div class="auth-split-form-wrap">

            <div class="auth-form-header">
                <img src="{{ asset('images/logo.png') }}" alt="ResQLink" class="auth-form-logo">
                <h2>Create Account</h2>
                <p>Join the network that saves lives</p>
            </div>

            @if ($errors->any())
                <div class="auth-errors">
                    <i data-lucide="alert-circle"></i>
                    <ul>
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form class="auth-form-body" action="{{ route('register') }}" method="POST">
                @csrf
                <input type="hidden" name="role" value="civilian">

                <div class="auth-field-row">
                    <div class="auth-field">
                        <label><i data-lucide="user"></i> Full Name</label>
                        <input type="text" name="name" value="{{ old('name') }}" placeholder="John Doe" required>
                    </div>
                    <div class="auth-field">
                        <label><i data-lucide="phone"></i> Phone Number</label>
                        <input type="tel" name="phone" value="{{ old('phone') }}" placeholder="+234 800 000 0000" required>
                    </div>
                </div>

                <div class="auth-field">
                    <label><i data-lucide="mail"></i> Email Address</label>
                    <input type="email" name="email" value="{{ old('email') }}" placeholder="john@example.com">
                </div>

                <div class="auth-field-row">
                    <div class="auth-field">
                        <label><i data-lucide="lock"></i> Password</label>
                        <div class="auth-input-wrap">
                            <input type="password" name="password" id="password" placeholder="••••••••" required>
                            <button type="button" class="auth-eye-btn" onclick="togglePwd('password','eyeIcon1')">
                                <i data-lucide="eye" id="eyeIcon1"></i>
                            </button>
                        </div>
                    </div>
                    <div class="auth-field">
                        <label><i data-lucide="shield-check"></i> Confirm Password</label>
                        <div class="auth-input-wrap">
                            <input type="password" name="password_confirmation" id="password_confirmation" placeholder="••••••••" required>
                            <button type="button" class="auth-eye-btn" onclick="togglePwd('password_confirmation','eyeIcon2')">
                                <i data-lucide="eye" id="eyeIcon2"></i>
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Medical ID -->
                <div class="auth-section-divider">
                    <span>Medical ID <em>optional — life saving</em></span>
                </div>

                <div class="auth-field-row">
                    <div class="auth-field">
                        <label><i data-lucide="droplets"></i> Blood Group</label>
                        <select name="blood_group">
                            <option value="">Select Blood Group</option>
                            @foreach(['A+', 'A-', 'B+', 'B-', 'AB+', 'AB-', 'O+', 'O-'] as $bg)
                                <option value="{{ $bg }}" {{ old('blood_group') === $bg ? 'selected' : '' }}>{{ $bg }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="auth-field">
                        <label><i data-lucide="phone-call"></i> Emergency Contact</label>
                        <input type="tel" name="emergency_contact_phone" value="{{ old('emergency_contact_phone') }}" placeholder="+234 800 000 0000">
                    </div>
                </div>

                <div class="auth-field">
                    <label><i data-lucide="alert-triangle"></i> Allergies <span class="auth-optional">if any</span></label>
                    <input type="text" name="allergies" value="{{ old('allergies') }}" placeholder="e.g. Peanuts, Penicillin">
                </div>

                <button type="submit" class="auth-submit-btn">
                    <i data-lucide="user-plus"></i>
                    Create Account
                </button>
            </form>

            <div class="auth-form-footer">
                <p>Already have an account? <a href="{{ route('login') }}">Sign in</a></p>
                <p>Are you an organization? <a href="{{ route('register.partner') }}">Register as Partner</a></p>
            </div>

        </div>
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
</script>
</body>
</html>
