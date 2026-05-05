<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Reset Password | ResQLink</title>
    <link rel="stylesheet" href="{{ asset('css/auth.css') }}">
    <script src="https://unpkg.com/lucide@latest"></script>
</head>
<body class="auth-page">

<div class="auth-card">
    <div class="auth-header">
        <div class="auth-logo">
            <div class="logo-icon">R</div>
            Resq<span style="color:var(--red)">Link</span>
        </div>
        <h2>New Password</h2>
        <p>Set a secure new password for your account</p>

        @if ($errors->any())
            <div style="background: rgba(229, 9, 20, 0.1); color: var(--red); padding: 12px; border-radius: 8px; margin-top: 20px; font-size: 0.85rem; border: 1px solid rgba(229, 9, 20, 0.2);">
                @foreach ($errors->all() as $error)
                    <p>{{ $error }}</p>
                @endforeach
            </div>
        @endif
    </div>

    <form class="auth-form" action="{{ route('password.update') }}" method="POST">
        @csrf
        <input type="hidden" name="token" value="{{ $token }}">

        <div class="form-group">
            <label><i data-lucide="mail" class="lucide-icon sm"></i> Email Address</label>
            <input type="email" name="email" value="{{ old('email', $email) }}" placeholder="Enter your email" required readonly>
        </div>

        <div class="form-group">
            <label><i data-lucide="lock" class="lucide-icon sm"></i> New Password</label>
            <input type="password" name="password" placeholder="••••••••" required autofocus>
        </div>

        <div class="form-group">
            <label><i data-lucide="shield-check" class="lucide-icon sm"></i> Confirm New Password</label>
            <input type="password" name="password_confirmation" placeholder="••••••••" required>
        </div>

        <button type="submit" class="btn-primary" style="width:100%; padding: 16px;">Reset Password</button>
    </form>
</div>

<script>lucide.createIcons();</script>
</body>
</html>
