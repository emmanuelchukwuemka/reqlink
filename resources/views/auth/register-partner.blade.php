<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Partner Registration | ResQLink</title>
    <link rel="stylesheet" href="{{ asset('css/auth.css') }}">
    <script src="https://unpkg.com/lucide@latest"></script>
</head>
<body class="auth-page">

<a href="{{ url('/') }}" class="back-home">
    <i data-lucide="arrow-left"></i>
    Back to Home
</a>

<div class="auth-card" style="max-width: 600px;">
    <div class="auth-header">
        <div class="auth-logo">
            <div class="logo-icon">R</div>
            Resq<span style="color:var(--red)">Link</span>
        </div>
        <h2>Partner Registration</h2>
        <p>Join the professional emergency network</p>

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
        
        <div class="form-group" style="margin-bottom: 30px;">
            <label class="field-label" style="font-weight: 700; color: var(--white); font-size: 0.9rem;">
                Select Partner Type
            </label>
            <div style="position: relative;">
                <select name="role" class="styled-select" required style="width: 100%; background: rgba(255,255,255,0.05); border: 1px solid var(--glass-border); border-radius: 12px; padding: 14px 16px; color: white; font-size: 0.9rem; appearance: none; cursor: pointer;">
                    <option value="doctor">Doctor</option>
                    <option value="hospital">Hospital</option>
                    <option value="ambulance">Ambulance Service</option>
                    <option value="security">Security Firm</option>
                    <option value="fire">Fire Unit</option>
                </select>
                <i data-lucide="chevron-down" style="position: absolute; right: 16px; top: 50%; transform: translateY(-50%); pointer-events: none; color: var(--grey); width: 18px; height: 18px;"></i>
            </div>
        </div>

        <div class="form-grid" style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
            <div class="form-group" style="grid-column: span 2;">
                <label class="field-label"><i data-lucide="building" class="lucide-icon sm"></i> Entity Name / Full Name</label>
                <input type="text" name="name" value="{{ old('name') }}" placeholder="City Central Hospital" required>
            </div>

            <div class="form-group">
                <label class="field-label"><i data-lucide="phone" class="lucide-icon sm"></i> Contact Phone</label>
                <input type="tel" name="phone" value="{{ old('phone') }}" placeholder="+234..." required>
            </div>
            
            <div class="form-group">
                <label class="field-label"><i data-lucide="mail" class="lucide-icon sm"></i> Business Email</label>
                <input type="email" name="email" value="{{ old('email') }}" placeholder="contact@entity.com">
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

        <button type="submit" class="btn-primary" style="width:100%; padding: 16px; margin-top: 20px;">Register Partner</button>
    </form>

    <div class="auth-footer">
        Already registered? <a href="{{ route('login') }}">Login here</a><br>
        Are you an individual user? <a href="{{ route('register') }}">Join as User</a>
    </div>
</div>

<script>lucide.createIcons();</script>
</body>
</html>
