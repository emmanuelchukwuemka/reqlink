<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Login | ResQLink</title>
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

<div class="auth-card">
    <div class="auth-header">
        <div class="auth-logo">
            <img src="{{ asset('images/logo.png') }}" alt="ResQLink" style="height: 60px; width: auto; object-fit: contain;">
        </div>
        <h2>Welcome Back</h2>
        <p>Log in to access your dashboard</p>

        @if (session('status'))
            <div style="background: rgba(34, 197, 94, 0.1); color: #22c55e; padding: 16px; border-radius: 12px; margin-top: 24px; font-size: 0.9rem; border: 1px solid rgba(34, 197, 94, 0.2);">
                {{ session('status') }}
            </div>
        @endif

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

    <form class="auth-form" action="{{ route('login') }}" method="POST">
        @csrf
        <div class="form-group">
            <label class="field-label"><i data-lucide="user" class="lucide-icon sm"></i> Email or Phone Number</label>
            <input type="text" name="login" placeholder="Email or Phone Number" value="{{ old('login') }}" required>
        </div>
        
        <div class="form-group">
            <div style="display: flex; justify-content: space-between; align-items: center;">
                <label class="field-label"><i data-lucide="lock" class="lucide-icon sm"></i> Access Password</label>
                <a href="{{ route('password.request') }}" style="font-size: 0.8rem; color: var(--red); font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px;">Forgot?</a>
            </div>
            <input type="password" name="password" placeholder="••••••••" required>
        </div>

        <button type="submit" class="btn-primary" style="width:100%; padding: 18px; margin-top: 10px; border-radius: 16px; font-size: 1rem;">Secure Login</button>
    </form>

    <div class="auth-footer">
        Don't have an account?<br>
        <div style="margin-top: 10px;">
            <a href="{{ route('register') }}">Individual Signup</a> &nbsp;•&nbsp; <a href="{{ route('register.partner') }}">Partner Signup</a>
        </div>
    </div>
</div>

<script>lucide.createIcons();</script>
</body>
</html>
