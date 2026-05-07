<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Join ResQLink | Save Lives</title>
    <link rel="stylesheet" href="{{ asset('css/auth.css') }}">
    <script src="https://unpkg.com/lucide@latest"></script>
    <script src="{{ asset('js/theme.js') }}"></script>
</head>
<body class="auth-page">

<a href="{{ url('/') }}" class="back-home">
    <i data-lucide="arrow-left"></i>
    Back to Home
</a>

<div class="theme-toggle-floating">
    <button id="themeToggle" class="theme-toggle-btn" title="Toggle Theme">
        <i id="themeIcon" data-lucide="sun"></i>
    </button>
</div>

<div class="auth-card" style="max-width: 550px;">
    <div class="auth-header">
        <div class="auth-logo">
            <img src="{{ asset('images/logo.png') }}" alt="ResQLink" style="height: 60px; width: auto; object-fit: contain;">
        </div>
        <h2>Create Account</h2>
        <p>Join the professional rescue network</p>

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
        
        <div class="form-grid">
            <div class="form-group">
                <label class="field-label"><i data-lucide="user" class="lucide-icon sm"></i> Full Legal Name</label>
                <input type="text" name="name" value="{{ old('name') }}" placeholder="e.g. John Doe" required>
            </div>

            <div class="form-group">
                <label class="field-label"><i data-lucide="phone" class="lucide-icon sm"></i> Mobile Number</label>
                <input type="tel" name="phone" value="{{ old('phone') }}" placeholder="+234 800 000 0000" required>
            </div>

            <div class="form-group">
                <label class="field-label"><i data-lucide="mail" class="lucide-icon sm"></i> Email Address (Optional)</label>
                <input type="email" name="email" value="{{ old('email') }}" placeholder="john@example.com">
            </div>

            <div class="form-group">
                <label class="field-label"><i data-lucide="lock" class="lucide-icon sm"></i> Password</label>
                <input type="password" name="password" placeholder="••••••••" required>
            </div>

            <div class="form-group">
                <label class="field-label"><i data-lucide="shield-check" class="lucide-icon sm"></i> Confirm Password</label>
                <input type="password" name="password_confirmation" placeholder="••••••••" required>
            </div>
        </div>

        <button type="submit" class="btn-primary" style="width:100%; padding: 18px; margin-top: 20px; border-radius: 16px; font-size: 1rem;">Create Secure Account</button>
    </form>

    <div class="auth-footer">
        Already have an account? <a href="{{ route('login') }}">Login here</a><br>
        Are you an organization? <a href="{{ route('register.partner') }}">Register as Partner</a>
    </div>
</div>

<script>lucide.createIcons();</script>
</body>
</html>
