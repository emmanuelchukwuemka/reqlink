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

<div class="auth-card">
    <div class="auth-header">
        <div class="auth-logo">
            <img src="{{ asset('images/logo.png') }}" alt="ResQLink" style="height: 80px; width: auto; object-fit: contain;">
        </div>
        @if (session('status'))
            <div style="background: rgba(34, 197, 94, 0.1); color: #22c55e; padding: 12px; border-radius: 8px; margin-top: 20px; font-size: 0.85rem; border: 1px solid rgba(34, 197, 94, 0.2);">
                {{ session('status') }}
            </div>
        @endif

        @if ($errors->any())
            <div style="background: rgba(229, 9, 20, 0.1); color: var(--red); padding: 12px; border-radius: 8px; margin-top: 20px; font-size: 0.85rem; border: 1px solid rgba(229, 9, 20, 0.2);">
                @foreach ($errors->all() as $error)
                    <p>{{ $error }}</p>
                @endforeach
            </div>
        @endif
    </div>

    <form class="auth-form" action="{{ route('login') }}" method="POST">
        @csrf
        <div class="form-group">
            <label><i data-lucide="phone" class="lucide-icon sm"></i> Phone Number</label>
            <input type="tel" name="phone" placeholder="Enter your phone number" required>
        </div>
        
        <div class="form-group">
            <div style="display: flex; justify-content: space-between; align-items: center;">
                <label><i data-lucide="lock" class="lucide-icon sm"></i> Password</label>
                <a href="{{ route('password.request') }}" style="font-size: 0.8rem; color: var(--red); font-weight: 600;">Forgot Password?</a>
            </div>
            <input type="password" name="password" placeholder="••••••••" required>
        </div>

        <button type="submit" class="btn-primary" style="width:100%; padding: 16px;">Login</button>
    </form>

    <div class="auth-footer">
        Don't have an account? <a href="{{ route('register') }}">User Signup</a> | <a href="{{ route('register.partner') }}">Partner Signup</a>
    </div>
</div>

<script>lucide.createIcons();</script>
</body>
</html>
