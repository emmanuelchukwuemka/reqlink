<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Forgot Password | ResQLink</title>
    <link rel="stylesheet" href="{{ asset('css/auth.css') }}">
    <script src="https://unpkg.com/lucide@latest"></script>
</head>
<body class="auth-page">

<a href="{{ route('login') }}" class="back-home">
    <i data-lucide="arrow-left"></i>
    Back to Login
</a>

<div class="auth-card">
    <div class="auth-header">
        <div class="auth-logo">
            <div class="logo-icon">R</div>
            Resq<span style="color:var(--red)">Link</span>
        </div>
        <h2>Reset Password</h2>
        <p>Enter your email and we'll send you a reset link</p>

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

    <form class="auth-form" action="{{ route('password.email') }}" method="POST">
        @csrf
        <div class="form-group">
            <label><i data-lucide="mail" class="lucide-icon sm"></i> Email Address</label>
            <input type="email" name="email" value="{{ old('email') }}" placeholder="Enter your email" required autofocus>
        </div>

        <button type="submit" class="btn-primary" style="width:100%; padding: 16px;">Send Reset Link</button>
    </form>

    <div class="auth-footer">
        Remember your password? <a href="{{ route('login') }}">Login here</a>
    </div>
</div>

<script>lucide.createIcons();</script>
</body>
</html>
