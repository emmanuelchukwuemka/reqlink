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
        
        <div style="margin-top: 15px; font-size: 0.85rem; padding: 10px; background: rgba(229, 9, 20, 0.05); border-radius: 8px; border: 1px solid rgba(229, 9, 20, 0.1);">
            Are you a responder? <a href="{{ route('register.partner') }}" style="color: var(--red); font-weight: 700;">Register as Partner</a>
        </div>

        @if ($errors->any())
            <div style="background: rgba(229, 9, 20, 0.1); color: var(--red); padding: 12px; border-radius: 8px; margin-top: 20px; font-size: 0.85rem; border: 1px solid rgba(229, 9, 20, 0.2);">
                @foreach ($errors->all() as $error)
                    <p>{{ $error }}</p>
                @endforeach
            </div>
        @endif
    </div>

    <form class="auth-form" action="{{ route('register') }}" method="POST">
        @csrf
        <input type="hidden" name="role" value="civilian">

        <div class="form-group">
            <label class="field-label"><i data-lucide="user" class="lucide-icon sm"></i> Full Name</label>
            <input type="text" name="name" value="{{ old('name') }}" placeholder="John Doe" required>
        </div>

        <div class="form-group">
            <label class="field-label"><i data-lucide="phone" class="lucide-icon sm"></i> Phone Number</label>
            <input type="tel" name="phone" value="{{ old('phone') }}" placeholder="+234..." required>
        </div>
        
        <div class="form-group">
            <label class="field-label"><i data-lucide="mail" class="lucide-icon sm"></i> Email (Optional)</label>
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

        <button type="submit" class="btn-primary" style="width:100%; padding: 16px;">Create Account</button>
    </form>

    <div class="auth-footer">
        Already have an account? <a href="{{ route('login') }}">Login here</a>
    </div>
</div>

<script>lucide.createIcons();</script>
</body>
</html>
