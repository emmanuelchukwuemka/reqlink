<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Login | ResQLink</title>
    <link rel="stylesheet" href="{{ asset('css/auth.css') }}">
    <script src="https://unpkg.com/lucide@latest"></script>
</head>
<body class="auth-page">

<a href="{{ url('/') }}" class="back-home">
    <i data-lucide="arrow-left"></i>
    Back to Home
</a>

<div class="auth-card">
    <div class="auth-header">
        <div class="auth-logo">
            <div class="logo-icon">R</div>
            Resq<span style="color:var(--red)">Link</span>
        </div>
        <h2>Welcome Back</h2>
        <p>Login to your emergency dashboard</p>
    </div>

    <form class="auth-form" action="{{ route('login') }}" method="POST">
        @csrf
        <div class="form-group">
            <label><i data-lucide="phone" class="lucide-icon sm"></i> Phone Number</label>
            <input type="tel" name="phone" placeholder="Enter your phone number" required>
        </div>
        
        <div class="form-group">
            <label><i data-lucide="lock" class="lucide-icon sm"></i> Password</label>
            <input type="password" name="password" placeholder="••••••••" required>
        </div>

        <button type="submit" class="btn-primary" style="width:100%; padding: 16px;">Login</button>
    </form>

    <div class="auth-footer">
        Don't have an account? <a href="{{ route('register') }}">Create Account</a>
    </div>
</div>

<script>lucide.createIcons();</script>
</body>
</html>
