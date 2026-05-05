<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Join ResQLink | Save Lives</title>
    <link rel="stylesheet" href="{{ asset('css/auth.css') }}">
    <script src="https://unpkg.com/lucide@latest"></script>
</head>
<body class="auth-page">

<a href="{{ url('/') }}" class="back-home">
    <i data-lucide="arrow-left"></i>
    Back to Home
</a>

<div class="auth-card" style="max-width: 500px;">
    <div class="auth-header">
        <div class="auth-logo">
            <div class="logo-icon">R</div>
            Resq<span style="color:var(--red)">Link</span>
        </div>
        <h2>Create Account</h2>
        <p>Join the network that saves lives</p>
    </div>

    <form class="auth-form" action="{{ route('register') }}" method="POST">
        @csrf
        <div class="form-group">
            <label><i data-lucide="user" class="lucide-icon sm"></i> Full Name</label>
            <input type="text" name="name" placeholder="John Doe" required>
        </div>

        <div class="form-group">
            <label><i data-lucide="phone" class="lucide-icon sm"></i> Phone Number</label>
            <input type="tel" name="phone" placeholder="+234..." required>
        </div>
        
        <div class="form-group">
            <label><i data-lucide="mail" class="lucide-icon sm"></i> Email (Optional)</label>
            <input type="email" name="email" placeholder="john@example.com">
        </div>

        <div class="form-group">
            <label><i data-lucide="lock" class="lucide-icon sm"></i> Password</label>
            <input type="password" name="password" placeholder="••••••••" required>
        </div>

        <div class="form-group">
            <label><i data-lucide="shield-check" class="lucide-icon sm"></i> Confirm Password</label>
            <input type="password" name="password_confirmation" placeholder="••••••••" required>
        </div>

        <button type="submit" class="btn-primary" style="width:100%; padding: 16px;">Create Account</button>
    </form>

    <div class="auth-footer">
        Already have an account? <a href="{{ route('login') }}">Login here</a>
    </div>
</div>

<script>lucide.createIcons();</script>
</body>
</html>
