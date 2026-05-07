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

<div class="auth-card" style="max-width: 500px;">
    <div class="auth-header">
        <div class="auth-logo">
            <img src="{{ asset('images/logo.png') }}" alt="ResQLink" style="height: 80px; width: auto; object-fit: contain;">
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

        <!-- OPTIONAL MEDICAL INFO -->
        <div style="margin-top: 30px; border-top: 1px solid var(--glass-border); padding-top: 20px;">
            <p style="font-size: 0.8rem; color: var(--grey); margin-bottom: 15px; font-weight: 700; text-transform: uppercase; letter-spacing: 1px;">
                Medical ID (Optional - Life Saving)
            </p>
            
            <div class="form-group">
                <label class="field-label">Blood Group</label>
                <select name="blood_group" class="styled-select" style="width: 100%; background: rgba(255,255,255,0.05); border: 1px solid var(--glass-border); border-radius: 8px; padding: 12px; color: white;">
                    <option value="" style="color: #000;">Select Blood Group</option>
                    @foreach(['A+', 'A-', 'B+', 'B-', 'AB+', 'AB-', 'O+', 'O-'] as $bg)
                        <option value="{{ $bg }}" style="color: #000;">{{ $bg }}</option>
                    @endforeach
                </select>
            </div>

            <div class="form-group">
                <label class="field-label">Allergies (if any)</label>
                <input type="text" name="allergies" placeholder="e.g. Peanuts, Penicillin">
            </div>

            <div class="form-group">
                <label class="field-label">Emergency Contact Phone</label>
                <input type="tel" name="emergency_contact_phone" placeholder="+234...">
            </div>
        </div>

        <button type="submit" class="btn-primary" style="width:100%; padding: 16px; margin-top: 20px;">Create Account</button>
    </form>

    <div class="auth-footer">
        Already have an account? <a href="{{ route('login') }}">Login here</a>
    </div>
</div>

<script>lucide.createIcons();</script>
</body>
</html>
