<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Dashboard | ResQLink</title>
    <link rel="manifest" href="/manifest.json">
    <link rel="stylesheet" href="/css/dashboard.css">
    <link rel="stylesheet" href="/css/chat.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/leaflet@1.9.4/dist/leaflet.min.css"/>
    <script src="https://cdn.jsdelivr.net/npm/leaflet@1.9.4/dist/leaflet.min.js"></script>
    <script src="https://unpkg.com/lucide@latest"></script>
    <style>
        .tab-pane { display: none; }
        .tab-pane.active { display: block; }
        .nav-item { cursor: pointer; }
        .theme-toggle { background: transparent; border: none; color: var(--grey); cursor: pointer; padding: 8px; border-radius: 50%; transition: all 0.3s; display: flex; align-items: center; justify-content: center; }
        .theme-toggle:hover { background: var(--glass); color: var(--white); }
        :root.light-mode .theme-toggle:hover { background: rgba(0,0,0,0.05); color: var(--black); }
        
        /* Voice SOS Styles */
        .voice-toggle { background: transparent; border: none; color: var(--grey); cursor: pointer; padding: 8px; border-radius: 50%; transition: all 0.3s; display: flex; align-items: center; justify-content: center; position: relative; }
        .voice-toggle.active { color: var(--red); background: rgba(229, 9, 20, 0.1); }
        .voice-toggle.active::after {
            content: '';
            position: absolute;
            inset: 0;
            border-radius: 50%;
            border: 2px solid var(--red);
            animation: pulse-voice 1.5s infinite;
        }
        @keyframes pulse-voice {
            0% { transform: scale(1); opacity: 1; }
            100% { transform: scale(1.8); opacity: 0; }
        }

        /* Live Alert Overlay */
        .live-alert-overlay {
            position: fixed;
            bottom: 30px;
            right: 30px;
            width: 350px;
            background: var(--dark);
            border: 2px solid var(--red);
            border-radius: 20px;
            padding: 25px;
            z-index: 2000;
            box-shadow: 0 20px 50px rgba(229, 9, 20, 0.4);
            display: none;
            animation: slide-up 0.5s ease-out;
        }
        @keyframes slide-up { from { transform: translateY(100px); opacity: 0; } to { transform: translateY(0); opacity: 1; } }

        @media (max-width: 600px) {
            .live-alert-overlay {
                width: calc(100vw - 40px) !important;
                right: 20px !important;
                left: 20px !important;
                bottom: 20px !important;
            }
            #supportWindow {
                width: calc(100vw - 40px) !important;
                left: 20px !important;
                right: 20px !important;
            }
            #supportTrigger {
                bottom: 20px !important;
                left: 20px !important;
            }
        }
    </style>
    <script src="/js/theme.js"></script>
</head>
<body class="dashboard-layout">

<div class="sidebar-overlay" id="sidebarOverlay"></div>

<aside class="sidebar">
    <div class="sidebar-header">
        <div class="auth-logo" style="margin-bottom: 0;">
            <img src="{{ asset('images/logo.png') }}" alt="ResQLink" style="height: 100px; width: auto; object-fit: contain;">
        </div>
    </div>
    
    <nav class="sidebar-nav">
        <a class="nav-item active" data-tab="overview"><i data-lucide="layout-grid"></i> Overview</a>
        <a class="nav-item" data-tab="ambulance"><i data-lucide="truck"></i> Ambulance</a>

        <a class="nav-item" data-tab="fire"><i data-lucide="flame"></i> Fire Services</a>
        <a class="nav-item" data-tab="hospitals"><i data-lucide="hospital"></i> Hospitals</a>
        <a class="nav-item" data-tab="history"><i data-lucide="history"></i> Incident History</a>
        <a class="nav-item" data-tab="wallet"><i data-lucide="wallet"></i> My Wallet</a>
        <a href="{{ route('settings') }}" class="nav-item"><i data-lucide="settings"></i> Settings</a>
    </nav>

    <div class="sidebar-footer">
        <form action="{{ route('logout') }}" method="POST" id="logoutForm">
            @csrf
            <a href="#" onclick="document.getElementById('logoutForm').submit()" class="nav-item" style="color: var(--red);">
                <i data-lucide="log-out"></i> Logout
            </a>
        </form>
    </div>
</aside>

<main class="main-content">
    <header class="top-bar">
        <button class="hamburger-btn" id="hamburgerBtn" aria-label="Toggle Menu">
            <i data-lucide="menu"></i>
        </button>
        <div class="topbar-title">
            <h1 id="pageTitle" style="font-size: 1.5rem; font-weight: 800;">Command Center</h1>
            <p style="color: var(--grey); font-size: 0.9rem;">Welcome back, {{ Auth::user()->name }}</p>
        </div>
            @if(Auth::user()->is_good_samaritan)
            <div class="duty-status-container" style="padding: 4px 12px; background: rgba(34, 197, 94, 0.05);">
                <span class="duty-label" id="samaritanText" style="font-size: 0.65rem;">{{ Auth::user()->samaritan_active ? 'ACTIVE SAMARITAN' : 'SAMARITAN OFF' }}</span>
                <label class="duty-toggle" style="width: 36px; height: 18px;">
                    <input type="checkbox" id="samaritanSwitch" {{ Auth::user()->samaritan_active ? 'checked' : '' }} onchange="toggleSamaritan(this)">
                    <span class="duty-slider" style="border-radius: 20px;"></span>
                </label>
            </div>
            @endif
            @include('partials.lang-switcher')
            <button id="voiceToggle" class="voice-toggle" title="AI Voice SOS Mode">
                <i data-lucide="mic" id="voiceIcon"></i>
            </button>
            <button id="themeToggle" class="theme-toggle" aria-label="Toggle Dark Mode">
                <i data-lucide="sun" id="themeIcon"></i>
            </button>
            <a data-tab="wallet" class="nav-item" style="background: rgba(229,9,20,0.1); border: 1px solid rgba(229,9,20,0.2); border-radius: 20px; padding: 6px 14px; font-size: 0.8rem; font-weight: 700; color: var(--red); cursor: pointer; text-decoration: none;">
                <i data-lucide="wallet" style="width:14px;height:14px;"></i>
                ₦{{ number_format(Auth::user()->wallet_balance, 2) }}
            </a>
            <div class="user-profile">
                <span style="font-size: 0.85rem; font-weight: 600;">Safety Status: Secure</span>
                <div class="avatar-sm">{{ substr(Auth::user()->name, 0, 1) }}</div>
            </div>
        </div>
    </header>

    <!-- OVERVIEW TAB -->
    <div id="overview" class="tab-pane active">
        <!-- SOS TRIGGER -->
        <div class="dash-card sos-trigger-card" style="margin-bottom: 24px;">
            <div class="sos-content">
                <div class="hero-badge" style="background: rgba(229, 9, 20, 0.1); border-color: rgba(229, 9, 20, 0.2); margin-bottom: 16px;">
                    <span class="pulse-dot"></span>
                    Emergency Panic Mode
                </div>
                <h2>Need Immediate Help?</h2>
                <p>Tap the SOS button once. Your location will be captured and responders dispatched instantly.</p>
            </div>
            <div class="panic-btn" id="panicBtn">
                <span>SOS</span>
                <small>Tap to Alert</small>
            </div>
        </div>

        <!-- MAMA CARE FEATURE -->
        <div class="dash-card mama-care-card" style="margin-bottom: 24px;">
            <div class="mama-care-header">
                <h3>
                    <i data-lucide="baby"></i>
                    Mama Care
                </h3>
                <label class="mama-care-toggle-container">
                    <span id="mamaCareText">{{ Auth::user()->mama_care_active ? 'ACTIVE' : 'OFF' }}</span>
                    <label class="duty-toggle" style="width: 36px; height: 18px; margin: 0;">
                        <input type="checkbox" id="mamaCareSwitch" {{ Auth::user()->mama_care_active ? 'checked' : '' }} onchange="toggleMamaCare(this)">
                        <span class="duty-slider" style="border-radius: 20px;"></span>
                    </label>
                </label>
            </div>

            <div id="mamaCareContent" class="mama-care-content {{ Auth::user()->mama_care_active ? 'active' : '' }}">
                <p style="color: var(--grey); font-size: 0.85rem; margin-bottom: 20px;">
                    Maternal emergency tools are active. In case of labor or complications, responders will be alerted with your priority status.
                </p>

                <!-- Profile Form -->
                <form action="/user/update-mamacare-profile" method="POST" class="mama-care-form">
                    @csrf
                    <div class="mc-form-group">
                        <label>Expected Due Date</label>
                        <input type="date" name="pregnancy_due_date" value="{{ Auth::user()->pregnancy_due_date }}">
                    </div>
                    <div class="mc-form-group">
                        <label>Preferred Maternity Hospital</label>
                        <input type="text" name="preferred_maternity_hospital" value="{{ Auth::user()->preferred_maternity_hospital }}" placeholder="e.g. Lagos Island Maternity">
                    </div>
                    <div class="mc-form-group">
                        <label>OB/GYN Contact (Optional)</label>
                        <input type="tel" name="obgyn_contact" value="{{ Auth::user()->obgyn_contact }}" placeholder="Phone number">
                    </div>
                    <div class="mc-form-group" style="flex-direction: row; align-items: center; gap: 10px; margin-top: 20px;">
                        <input type="checkbox" name="pregnancy_high_risk" value="1" {{ Auth::user()->pregnancy_high_risk ? 'checked' : '' }} style="width: auto; padding: 0;">
                        <label style="margin: 0; color: #ec4899;">Mark as High-Risk Pregnancy</label>
                    </div>
                    <div style="grid-column: 1 / -1; text-align: right;">
                        <button type="submit" class="mc-tool-btn" style="background: rgba(236,72,153,0.1);">Save Profile</button>
                    </div>
                </form>

                <!-- Labor SOS Button -->
                <button class="labor-sos-btn" id="laborSosBtn">
                    <i data-lucide="siren"></i>
                    TRIGGER LABOR / MATERNITY SOS
                </button>

                <!-- Interactive Tools -->
                <div class="mc-tools-grid">
                    <div class="mc-tool-card">
                        <h4>Contraction Timer</h4>
                        <div class="mc-tool-value" id="contractionTimerDisplay">00:00</div>
                        <button class="mc-tool-btn" id="contractionBtn" onclick="toggleContractionTimer()">Start Timer</button>
                        <p id="contractionLog" style="font-size: 0.7rem; color: var(--grey); margin-top: 10px;"></p>
                    </div>
                    <div class="mc-tool-card">
                        <h4>Kick Counter</h4>
                        <div class="mc-tool-value" id="kickCounterDisplay">0</div>
                        <button class="mc-tool-btn" onclick="recordKick()">Record Kick</button>
                        <button class="mc-tool-btn" style="border: none; color: var(--grey); margin-left: 5px;" onclick="resetKicks()">Reset</button>
                    </div>
                </div>
            </div>
        </div>

        <div class="dashboard-grid">
            <div class="dash-card">
                <h3><i data-lucide="map"></i> Real-time Network</h3>
                <div id="map" style="height: 350px; border-radius: 12px; border: 1px solid var(--glass-border); background: var(--dark2);"></div>
            </div>

            <div>
                <div class="dash-card">
                    <h3><i data-lucide="history"></i> Recent Activity</h3>
                    <div class="history-list">
                        @forelse($history as $item)
                        <div class="history-item" style="padding: 12px; background: rgba(255,255,255,0.02); border-radius: 8px; margin-bottom: 10px; display: flex; align-items: center; gap: 12px;">
                            <i data-lucide="alert-circle" style="color: var(--red)"></i>
                            <div>
                                <p style="font-size: 0.85rem; font-weight: 600; margin: 0;">Emergency Alert</p>
                                <p style="font-size: 0.7rem; color: var(--grey); margin: 0;">{{ $item->created_at->diffForHumans() }}</p>
                            </div>
                            <span style="margin-left: auto; font-size: 0.7rem; text-transform: uppercase; font-weight: 700; color: {{ $item->status == 'resolved' ? '#22c55e' : 'var(--red)' }}">{{ $item->status }}</span>
                        </div>
                        @empty
                        <p style="color: var(--grey); font-size: 0.8rem; text-align: center; padding: 20px;">No recent alerts.</p>
                        @endforelse
                    </div>
                </div>

                @if(Auth::user()->is_good_samaritan && Auth::user()->samaritan_active)
                <div class="dash-card" style="margin-top: 24px; border: 1px solid #22c55e; background: rgba(34, 197, 94, 0.05);">
                    <h3 style="color: #22c55e;"><i data-lucide="heart"></i> Nearby Samaritan Missions</h3>
                    <div class="history-list">
                        @forelse($samaritanMissions as $mission)
                        <div style="padding: 15px; background: rgba(255,255,255,0.03); border-radius: 12px; margin-bottom: 12px;">
                            <div style="display: flex; justify-content: space-between; margin-bottom: 10px;">
                                <span style="font-size: 0.75rem; font-weight: 800; color: #22c55e;">MEDICAL FIRST AID</span>
                                <small style="color: var(--grey);">{{ $mission->created_at->diffForHumans() }}</small>
                            </div>
                            <p style="font-size: 0.85rem; margin-bottom: 15px;">A user nearby needs assistance. You are a verified <strong>{{ Auth::user()->samaritan_profession }}</strong>.</p>
                            <button class="btn-primary" style="width: 100%; background: #22c55e; font-size: 0.8rem; padding: 10px;">Accept & Provide Aid</button>
                        </div>
                        @empty
                        <p style="color: var(--grey); font-size: 0.8rem; text-align: center;">No active emergencies nearby.</p>
                        @endforelse
                    </div>
                </div>
                @endif
            </div>
        </div>
    </div>

    <!-- HOSPITALS TAB -->
    <div id="hospitals" class="tab-pane">
        <div class="dash-card">
            <h3><i data-lucide="hospital"></i> Medical Facilities</h3>
            <div class="hospitals-grid" style="display: grid; grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); gap: 20px; margin-top: 20px;">
                @forelse($hospitals as $hospital)
                <div class="sub-card" style="background: rgba(255,255,255,0.02); border: 1px solid var(--glass-border); border-radius: 16px; padding: 20px;">
                    <div style="display: flex; align-items: center; gap: 15px; margin-bottom: 15px;">
                        <div style="width: 45px; height: 45px; background: rgba(34, 197, 94, 0.1); border-radius: 12px; display: flex; align-items: center; justify-content: center; color: #22c55e;">
                            <i data-lucide="hospital"></i>
                        </div>
                        <div>
                            <h4 style="margin: 0;">{{ $hospital->name }}</h4>
                            <small style="color: var(--grey);">Verified Hospital</small>
                        </div>
                    </div>
                    <button class="btn-primary" style="width: 100%; padding: 12px; font-size: 0.85rem; border-radius: 8px;">View Details</button>
                </div>
                @empty
                <p>No hospitals registered yet.</p>
                @endforelse
            </div>
        </div>
    </div>

    <!-- AMBULANCE TAB -->
    <div id="ambulance" class="tab-pane">
        <div class="dash-card">
            <h3><i data-lucide="truck"></i> Active Ambulance Units</h3>
            <div class="hospitals-grid" style="display: grid; grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); gap: 20px; margin-top: 20px;">
                @forelse($ambulances as $unit)
                <div class="sub-card" style="background: rgba(255,255,255,0.02); border: 1px solid var(--glass-border); border-radius: 16px; padding: 20px;">
                    <div style="display: flex; align-items: center; gap: 15px; margin-bottom: 15px;">
                        <div style="width: 45px; height: 45px; background: rgba(229, 9, 20, 0.1); border-radius: 12px; display: flex; align-items: center; justify-content: center; color: var(--red);">
                            <i data-lucide="truck"></i>
                        </div>
                        <div>
                            <h4 style="margin: 0;">{{ $unit->user->name }}</h4>
                            <small style="color: {{ $unit->is_on_duty ? '#22c55e' : 'var(--grey)' }};">
                                {{ $unit->is_on_duty ? '● On Duty' : '○ Off Duty' }}
                            </small>
                        </div>
                    </div>
                    <div style="font-size: 0.8rem; color: var(--grey); margin-bottom: 15px;">
                        <p style="margin: 4px 0;">Reg: {{ $unit->vehicle_reg ?? 'N/A' }}</p>
                        <p style="margin: 4px 0;">Capacity: {{ $unit->capacity ?? 'Standard' }}</p>
                    </div>
                    <button class="btn-primary" style="width: 100%; padding: 12px; font-size: 0.85rem; border-radius: 8px;">Request Unit</button>
                </div>
                @empty
                <div style="grid-column: 1/-1; text-align: center; padding: 40px;">
                    <p style="color: var(--grey);">No active ambulance units in your area.</p>
                </div>
                @endforelse
            </div>
        </div>
    </div>

    <!-- FIRE TAB -->
    <div id="fire" class="tab-pane">
        <div class="dash-card">
            <h3><i data-lucide="flame"></i> Fire & Rescue Services</h3>
            <div class="hospitals-grid" style="display: grid; grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); gap: 20px; margin-top: 20px;">
                @forelse($fireUnits as $unit)
                <div class="sub-card" style="background: rgba(255,255,255,0.02); border: 1px solid var(--glass-border); border-radius: 16px; padding: 20px;">
                    <div style="display: flex; align-items: center; gap: 15px; margin-bottom: 15px;">
                        <div style="width: 45px; height: 45px; background: rgba(249, 115, 22, 0.1); border-radius: 12px; display: flex; align-items: center; justify-content: center; color: #f97316;">
                            <i data-lucide="flame"></i>
                        </div>
                        <div>
                            <h4 style="margin: 0;">{{ $unit->user->name }}</h4>
                            <small style="color: {{ $unit->is_on_duty ? '#22c55e' : 'var(--grey)' }};">
                                {{ $unit->is_on_duty ? '● Station Ready' : '○ Offline' }}
                            </small>
                        </div>
                    </div>
                    <button class="btn-primary" style="width: 100%; padding: 12px; font-size: 0.85rem; border-radius: 8px; background: #f97316;">Report Fire</button>
                </div>
                @empty
                <div style="grid-column: 1/-1; text-align: center; padding: 40px;">
                    <p style="color: var(--grey);">No fire stations registered in this sector.</p>
                </div>
                @endforelse
            </div>
        </div>
    </div>

    <!-- HISTORY TAB -->
    <div id="history" class="tab-pane">
        <div class="dash-card">
            <h3><i data-lucide="history"></i> Complete Incident Logs</h3>
            <div class="table-scroll">
            <table style="width: 100%; border-collapse: collapse; margin-top: 20px; min-width: 480px;">
                <thead>
                    <tr style="text-align: left; color: var(--grey); font-size: 0.8rem; text-transform: uppercase;">
                        <th style="padding: 15px;">Date</th>
                        <th>Status</th>
                        <th>Coordinates</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($history as $item)
                    <tr style="border-top: 1px solid var(--glass-border);">
                        <td style="padding: 15px;">{{ $item->created_at->format('M d, Y H:i') }}</td>
                        <td><span style="color: {{ $item->status == 'resolved' ? '#22c55e' : 'var(--red)' }}">{{ ucfirst($item->status) }}</span></td>
                        <td>{{ round($item->latitude, 4) }}, {{ round($item->longitude, 4) }}</td>
                        <td><a href="#" style="color: var(--red); font-weight: 700; font-size: 0.85rem;">View Report</a></td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
            </div>
        </div>
    </div>

    <!-- WALLET TAB -->
    <div id="wallet" class="tab-pane">
        @if(session('wallet_success'))
            <div style="background: rgba(34,197,94,0.1); color: #22c55e; border: 1px solid rgba(34,197,94,0.2); border-radius: 12px; padding: 16px; margin-bottom: 20px; display: flex; align-items: center; gap: 10px;">
                <i data-lucide="check-circle" style="width:18px;height:18px;"></i>
                {{ session('wallet_success') }}
            </div>
        @endif

        @if($errors->has('wallet'))
            <div style="background: rgba(229,9,20,0.1); color: var(--red); border: 1px solid rgba(229,9,20,0.2); border-radius: 12px; padding: 16px; margin-bottom: 20px;">
                {{ $errors->first('wallet') }}
            </div>
        @endif

        <!-- Balance Card -->
        <div class="dash-card" style="background: linear-gradient(135deg, #1a0505 0%, #2d0a0a 100%); border: 1px solid rgba(229,9,20,0.3); margin-bottom: 24px;">
            <div style="display: flex; justify-content: space-between; align-items: flex-start; flex-wrap: wrap; gap: 20px;">
                <div>
                    <p style="color: var(--grey); font-size: 0.85rem; margin: 0 0 8px; text-transform: uppercase; letter-spacing: 1px;">Available Balance</p>
                    <h1 style="font-size: 2.8rem; font-weight: 900; margin: 0; color: #fff;">₦{{ number_format(Auth::user()->wallet_balance, 2) }}</h1>
                    <p style="color: rgba(255,255,255,0.4); font-size: 0.8rem; margin: 8px 0 0;">Used for emergency dispatch & services</p>
                </div>
                <div style="display: flex; flex-direction: column; gap: 10px; align-items: flex-end;">
                    <button onclick="document.getElementById('fundModal').style.display='flex'" style="background: var(--red); color: #fff; border: none; padding: 14px 28px; border-radius: 12px; font-weight: 700; font-size: 0.95rem; cursor: pointer; display: flex; align-items: center; gap: 8px;">
                        <i data-lucide="plus-circle" style="width:18px;height:18px;"></i> Fund Wallet
                    </button>
                    <p style="color: rgba(255,255,255,0.3); font-size: 0.75rem; margin: 0;">Powered by Paystack</p>
                </div>
            </div>
        </div>

        <!-- Stats row -->
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(160px, 1fr)); gap: 16px; margin-bottom: 24px;">
            <div class="dash-card" style="text-align: center; padding: 20px;">
                <i data-lucide="arrow-down-circle" style="color: #22c55e; width:28px; height:28px; margin-bottom: 8px;"></i>
                <p style="color: var(--grey); font-size: 0.75rem; margin: 0 0 4px; text-transform: uppercase;">Total Funded</p>
                <p style="font-size: 1.3rem; font-weight: 800; margin: 0;">₦{{ number_format($walletTransactions->where('type','credit')->sum('amount'), 2) }}</p>
            </div>
            <div class="dash-card" style="text-align: center; padding: 20px;">
                <i data-lucide="arrow-up-circle" style="color: var(--red); width:28px; height:28px; margin-bottom: 8px;"></i>
                <p style="color: var(--grey); font-size: 0.75rem; margin: 0 0 4px; text-transform: uppercase;">Total Spent</p>
                <p style="font-size: 1.3rem; font-weight: 800; margin: 0;">₦{{ number_format($walletTransactions->where('type','debit')->sum('amount'), 2) }}</p>
            </div>
            <div class="dash-card" style="text-align: center; padding: 20px;">
                <i data-lucide="receipt" style="color: var(--text-main); width:28px; height:28px; margin-bottom: 8px;"></i>
                <p style="color: var(--grey); font-size: 0.75rem; margin: 0 0 4px; text-transform: uppercase;">Transactions</p>
                <p style="font-size: 1.3rem; font-weight: 800; margin: 0;">{{ $walletTransactions->count() }}</p>
            </div>
        </div>

        <!-- Transaction History -->
        <div class="dash-card">
            <h3 style="margin-bottom: 20px;"><i data-lucide="list"></i> Transaction History</h3>
            @if($walletTransactions->isEmpty())
                <div style="text-align: center; padding: 40px 20px; color: var(--grey);">
                    <i data-lucide="wallet" style="width:48px;height:48px;opacity:0.3;margin-bottom:12px;"></i>
                    <p>No transactions yet. Fund your wallet to get started.</p>
                </div>
            @else
                <div class="table-scroll">
                <table style="width:100%; border-collapse:collapse; min-width:480px;">
                    <thead>
                        <tr style="text-align:left; color:var(--grey); font-size:0.8rem; text-transform:uppercase;">
                            <th style="padding:12px 15px;">Date</th>
                            <th>Description</th>
                            <th>Reference</th>
                            <th style="text-align:right; padding-right:15px;">Amount</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($walletTransactions as $tx)
                        <tr style="border-top: 1px solid var(--glass-border);">
                            <td style="padding:14px 15px; font-size:0.85rem; color:var(--grey);">{{ $tx->created_at->format('M d, Y H:i') }}</td>
                            <td style="font-size:0.9rem;">{{ $tx->description }}</td>
                            <td style="font-size:0.75rem; color:var(--grey); font-family:monospace;">{{ $tx->reference }}</td>
                            <td style="text-align:right; padding-right:15px; font-weight:700; color:{{ $tx->type === 'credit' ? '#22c55e' : 'var(--red)' }};">
                                {{ $tx->type === 'credit' ? '+' : '-' }}₦{{ number_format($tx->amount, 2) }}
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
                </div>
            @endif
        </div>
    </div>

    <!-- FUND WALLET MODAL -->
    <div id="fundModal" style="display:none; position:fixed; inset:0; background:rgba(0,0,0,0.6); z-index:9999; align-items:center; justify-content:center;">
        <div style="background:var(--dark); border:1px solid var(--glass-border); border-radius:20px; padding:32px; width:100%; max-width:400px; margin:20px; color:var(--white);">
            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:24px;">
                <h3 style="margin:0;"><i data-lucide="wallet"></i> Fund Wallet</h3>
                <button onclick="document.getElementById('fundModal').style.display='none'" style="background:none;border:none;color:var(--grey);cursor:pointer;font-size:1.5rem;">&times;</button>
            </div>
            <form action="{{ route('wallet.fund') }}" method="POST">
                @csrf
                <p style="color:var(--grey); font-size:0.85rem; margin-bottom:20px;">Select an amount or enter a custom value (minimum ₦100)</p>

                <!-- Quick amounts -->
                <div style="display:grid; grid-template-columns:1fr 1fr 1fr; gap:10px; margin-bottom:20px;">
                    @foreach([500, 1000, 2000, 5000, 10000, 20000] as $amt)
                    <button type="button" onclick="document.getElementById('fundAmount').value='{{ $amt }}'"
                        style="padding:12px; border:1px solid var(--glass-border); border-radius:10px; background:rgba(255,255,255,0.03); color:var(--white); cursor:pointer; font-weight:600; font-size:0.9rem; transition:all 0.2s;"
                        onmouseover="this.style.borderColor='var(--red)'" onmouseout="this.style.borderColor='var(--glass-border)'">
                        ₦{{ number_format($amt) }}
                    </button>
                    @endforeach
                </div>

                <div style="margin-bottom:20px;">
                    <label style="font-size:0.85rem; color:var(--grey); display:block; margin-bottom:8px;">Custom Amount (₦)</label>
                    <input type="number" name="amount" id="fundAmount" min="100" placeholder="e.g. 3000"
                        style="width:100%; padding:14px; border-radius:12px; border:1px solid var(--glass-border); background:rgba(255,255,255,0.05); color:var(--white); font-size:1rem; box-sizing:border-box;">
                </div>

                <button type="submit" style="width:100%; padding:16px; background:var(--red); color:#fff; border:none; border-radius:12px; font-weight:700; font-size:1rem; cursor:pointer;">
                    Proceed to Payment
                </button>
            </form>
        </div>
    </div>

    <!-- LIVE ALERT OVERLAY -->
    <div id="liveAlert" class="live-alert-overlay">
        <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 20px;">
            <div>
                <h3 style="color: var(--red); margin: 0; font-size: 1.1rem; display: flex; align-items: center; gap: 8px;">
                    <span class="pulse-dot"></span> HELP IS EN ROUTE
                </h3>
                <p id="emergencyStatus" style="font-size: 0.8rem; color: var(--grey); margin: 5px 0 0;">Responder dispatched...</p>
            </div>
            <i data-lucide="siren" style="color: var(--red); width: 24px;"></i>
        </div>

        <div style="background: var(--glass); border: 1px solid var(--glass-border); border-radius: 12px; padding: 15px; margin-bottom: 20px;">
            <div style="display: flex; align-items: center; gap: 12px;">
                <div id="responderAvatar" style="width: 40px; height: 40px; background: var(--red); border-radius: 10px; display: flex; align-items: center; justify-content: center; font-weight: 900; color: #fff;">R</div>
                <div>
                    <div id="responderName" style="font-weight: 700; font-size: 0.9rem; color: var(--white);">Medical Unit 101</div>
                    <div id="responderETA" style="font-size: 0.75rem; color: #22c55e; font-weight: 700;">ETA: Calculating...</div>
                </div>
            </div>
        </div>

        <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 10px; margin-top: 10px;">
            <button class="btn-primary" style="padding: 10px; font-size: 0.75rem; background: #2563eb;">Call Unit</button>
            <button onclick="openEmergencyChat()" style="padding: 10px; font-size: 0.75rem; background: rgba(34,197,94,0.15); color: #22c55e; border: 1px solid rgba(34,197,94,0.3); border-radius: 8px; cursor: pointer;">Chat</button>
            <button onclick="cancelEmergency()" style="padding: 10px; font-size: 0.75rem; background: rgba(255,255,255,0.05); color: var(--white); border: 1px solid var(--glass-border); border-radius: 8px; cursor: pointer;">Cancel</button>
        </div>
    </div>

<!-- EMERGENCY CHAT WINDOW -->
<div id="emergencyChat" style="display:none; position:fixed; bottom:110px; right:30px; width:340px; height:440px; background:var(--dark2); border:1px solid rgba(34,197,94,0.3); border-radius:20px; flex-direction:column; overflow:hidden; z-index:6000; box-shadow:0 20px 50px rgba(0,0,0,0.3);">
    <div style="background:rgba(34,197,94,0.1); padding:16px 20px; display:flex; align-items:center; gap:12px; border-bottom:1px solid var(--glass-border);">
        <div style="width:36px;height:36px;background:#22c55e;border-radius:10px;display:flex;align-items:center;justify-content:center;font-size:0.9rem;font-weight:900;color:#fff;">R</div>
        <div style="flex:1;">
            <h4 style="margin:0;font-size:0.9rem;color:var(--white);" id="chatResponderName">Responder</h4>
            <small style="color:#22c55e;font-weight:700;font-size:0.7rem;">Emergency Chat</small>
        </div>
        <button onclick="document.getElementById('emergencyChat').style.display='none'" style="background:none;border:none;color:var(--grey);cursor:pointer;font-size:1.2rem;">&times;</button>
    </div>
    <div id="emergencyChatBody" style="flex:1;padding:16px;overflow-y:auto;display:flex;flex-direction:column;gap:10px;background:var(--dark2);">
        <div style="text-align:center;color:var(--grey);font-size:0.8rem;padding:20px;">Chat with your assigned responder</div>
    </div>
    <div style="padding:12px;border-top:1px solid var(--glass-border);display:flex;gap:8px;background:var(--dark2);">
        <input id="emergencyChatInput" type="text" placeholder="Type a message..." style="flex:1;background:var(--glass);border:1px solid var(--glass-border);border-radius:10px;padding:10px 14px;color:var(--white);font-size:0.85rem;" onkeypress="if(event.key==='Enter') sendEmergencyChat()">
        <button onclick="sendEmergencyChat()" style="background:#22c55e;border:none;color:#fff;width:40px;border-radius:10px;cursor:pointer;font-size:1rem;">➤</button>
    </div>
</div>
</main>

<script>
    lucide.createIcons();

    function addSatelliteToggle(mapObj, osmLayer) {
        let sat = false;
        const satLayer = L.tileLayer(
            'https://server.arcgisonline.com/ArcGIS/rest/services/World_Imagery/MapServer/tile/{z}/{y}/{x}',
            // Esri's free imagery has real coverage everywhere only up to ~z13; beyond that,
            // many regions return a grey "Map data not yet available" placeholder instead of a
            // tile. Capping maxNativeZoom makes Leaflet re-scale the last real tile instead of
            // requesting zoom levels Esri doesn't have.
            { attribution: 'Tiles &copy; Esri', maxZoom: 19, maxNativeZoom: 13 }
        );
        const ctrl = document.createElement('div');
        ctrl.className = 'map-type-ctrl';
        ctrl.innerHTML = '<button class="mtb active" data-t="map">Map</button><button class="mtb" data-t="satellite">Satellite</button>';
        mapObj.getContainer().appendChild(ctrl);
        ctrl.addEventListener('click', e => {
            const btn = e.target.closest('.mtb');
            if (!btn) return;
            ctrl.querySelectorAll('.mtb').forEach(b => b.classList.remove('active'));
            btn.classList.add('active');
            const pane = mapObj.getPane('tilePane');
            if (btn.dataset.t === 'satellite' && !sat) {
                osmLayer.remove(); satLayer.addTo(mapObj);
                if (pane) pane.style.filter = ''; sat = true;
            } else if (btn.dataset.t === 'map' && sat) {
                satLayer.remove(); osmLayer.addTo(mapObj);
                const light = document.documentElement.classList.contains('light-mode');
                if (pane && !light) pane.style.filter = 'invert(92%) hue-rotate(180deg) brightness(95%) contrast(90%)';
                sat = false;
            }
        });
        return () => sat;
    }

    // Listen for global theme changes to restyle map
    document.addEventListener('themeChanged', (e) => {
        if (typeof applyMapTheme === 'function') applyMapTheme(e.detail.isLight);
    });
    // Tab Switching
    document.querySelectorAll('.nav-item[data-tab]').forEach(item => {
        item.addEventListener('click', () => {
            // Update UI
            document.querySelectorAll('.nav-item').forEach(i => i.classList.remove('active'));
            item.classList.add('active');

            // Switch Panes
            const tabId = item.getAttribute('data-tab');
            document.querySelectorAll('.tab-pane').forEach(p => p.classList.remove('active'));
            const pane = document.getElementById(tabId);
            if (pane) pane.classList.add('active');

            // Update Title
            document.getElementById('pageTitle').textContent = item.textContent.trim();

            // Reflow Map if needed
            if(tabId === 'overview') {
                setTimeout(() => map.invalidateSize(), 100);
            }
        });
    });

    // Map Initialization
    let map = L.map('map').setView([6.5244, 3.3792], 13);

    let tileLayer = L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a>',
        maxZoom: 19
    }).addTo(map);

    let isSat = () => false;
    function applyMapTheme(isLight) {
        const pane = map.getPane('tilePane');
        if (pane && !isSat()) pane.style.filter = isLight ? '' : 'invert(92%) hue-rotate(180deg) brightness(95%) contrast(90%)';
    }
    applyMapTheme(document.documentElement.classList.contains('light-mode'));
    isSat = addSatelliteToggle(map, tileLayer);

    let userMarker;

    if (navigator.geolocation) {
        navigator.geolocation.getCurrentPosition(
            (position) => {
                const { latitude, longitude } = position.coords;
                map.setView([latitude, longitude], 15);
                userMarker = L.marker([latitude, longitude]).addTo(map)
                    .bindPopup('Your Current Location').openPopup();
            },
            () => { /* location denied — stay at Lagos default */ },
            { enableHighAccuracy: true, timeout: 10000 }
        );
    }

    // ── Real-time map markers ─────────────────────────────────────────
    function makeIcon(bg, label) {
        return L.divIcon({
            html: `<div style="background:${bg};color:#fff;font-weight:700;font-size:11px;width:26px;height:26px;border-radius:50%;display:flex;align-items:center;justify-content:center;border:2px solid #fff;box-shadow:0 2px 8px rgba(0,0,0,0.5);">${label}</div>`,
            className: 'custom-div-icon',
            iconSize: [26, 26],
            iconAnchor: [13, 13],
            popupAnchor: [0, -14]
        });
    }

    const typeConfig = {
        ambulance: { color: '#2563eb', label: 'A' },
        fire:      { color: '#ea580c', label: 'F' },
    };

    // Hospital markers (static — hospitals don't move)
    @json($hospitals->filter(fn($h) => $h->lat && $h->lng)->values()).forEach(h => {
        L.marker([parseFloat(h.lat), parseFloat(h.lng)], { icon: makeIcon('#dc2626', 'H') })
            .addTo(map)
            .bindPopup(`<b>${h.name}</b><br>Beds available: ${h.available_beds ?? '?'}/${h.total_beds ?? '?'}`);
    });

    // Responder markers — seeded from initial server data then kept live
    const liveMarkers = {};

    function placeOrUpdate(r) {
        const lat = parseFloat(r.current_lat ?? r.lat);
        const lng = parseFloat(r.current_lng ?? r.lng);
        if (!lat || !lng) return;
        const cfg = typeConfig[r.responder_type ?? r.type] || { color: '#6b7280', label: 'R' };
        const name = (r.user ? r.user.name : null) ?? r.name ?? ('Responder #' + r.id);
        const status = (r.is_available ?? r.available) ? 'Available' : 'Busy';
        const popup = `<b>${name}</b><br>${(r.responder_type ?? r.type)} — ${status}`;
        if (liveMarkers[r.id]) {
            liveMarkers[r.id].setLatLng([lat, lng]);
            liveMarkers[r.id].bindPopup(popup);
        } else {
            liveMarkers[r.id] = L.marker([lat, lng], { icon: makeIcon(cfg.color, cfg.label) })
                .addTo(map).bindPopup(popup);
        }
    }

    [
        ...@json($ambulances->values()),
        ...@json($fireUnits->values()),
    ].forEach(placeOrUpdate);

    // Poll every 15 s for position updates
    setInterval(() => {
        fetch('/map/live-data')
            .then(r => r.json())
            .then(data => data.responders.forEach(r => {
                const lat = r.lat, lng = r.lng;
                if (!lat || !lng) return;
                const cfg = typeConfig[r.type] || { color: '#6b7280', label: 'R' };
                const popup = `<b>${r.name}</b><br>${r.type} — ${r.available ? 'Available' : 'Busy'}`;
                if (liveMarkers[r.id]) {
                    liveMarkers[r.id].setLatLng([lat, lng]);
                    liveMarkers[r.id].bindPopup(popup);
                } else {
                    liveMarkers[r.id] = L.marker([lat, lng], { icon: makeIcon(cfg.color, cfg.label) })
                        .addTo(map).bindPopup(popup);
                }
            }))
            .catch(() => {});
    }, 15000);

    // Map legend
    const mapLegend = L.control({ position: 'bottomright' });
    mapLegend.onAdd = () => {
        const div = L.DomUtil.create('div');
        div.style.cssText = 'background:rgba(10,10,10,0.82);border-radius:8px;padding:8px 10px;color:#fff;font-size:11px;line-height:1.9;border:1px solid rgba(255,255,255,0.1);pointer-events:none;';
        div.innerHTML =
            '<div style="font-weight:700;margin-bottom:2px;letter-spacing:.5px;">LEGEND</div>' +
            '<div><span style="display:inline-block;width:10px;height:10px;border-radius:50%;background:#dc2626;margin-right:6px;"></span>Hospital</div>' +
            '<div><span style="display:inline-block;width:10px;height:10px;border-radius:50%;background:#2563eb;margin-right:6px;"></span>Ambulance</div>' +
            '<div><span style="display:inline-block;width:10px;height:10px;border-radius:50%;background:#ea580c;margin-right:6px;"></span>Fire Unit</div>';
        return div;
    };
    mapLegend.addTo(map);
    // ─────────────────────────────────────────────────────────────────

    // Panic Button — single tap/click triggers immediately
    const panicBtn = document.getElementById('panicBtn');

    panicBtn.addEventListener('click', () => {
        triggerEmergency();
    });

    let activeEmergencyUuid = @json($activeEmergency ? $activeEmergency->uuid : null);
    let responderMarker = null;
    let pollingInterval = null;

    let sosTriggering = false;

    function resetPanicBtn() {
        sosTriggering = false;
        panicBtn.innerHTML = '<span>SOS</span><small>Tap to Alert</small>';
    }

    function triggerEmergency() {
        if (sosTriggering) return; // ignore repeat taps while already in flight
        if (!window.isSecureContext) {
            return alert('This page must be loaded over HTTPS for SOS to access your location. Please reload using https://');
        }
        if (!navigator.geolocation) return alert('Geolocation not supported on this device/browser.');

        sosTriggering = true;
        panicBtn.innerHTML = '<span>...</span><small>Locating</small>';

        navigator.geolocation.getCurrentPosition((position) => {
            const { latitude, longitude } = position.coords;
            fetch('{{ route("emergency.trigger") }}', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                body: JSON.stringify({ latitude, longitude })
            })
            .then(res => {
                if (!res.ok) {
                    const status = res.status;
                    return res.text().then(body => {
                        throw new Error('HTTP_' + status + ':' + body.substring(0, 300));
                    });
                }
                return res.json();
            })
            .then(data => {
                sosTriggering = false;
                activeEmergencyUuid = data.uuid;
                startPollingStatus();
                startPanicRecording(data.uuid);

                if (data.status === 'dispatched') {
                    panicBtn.innerHTML = '<span>SOS</span><small>Help En-Route</small>';
                    document.getElementById('emergencyStatus').textContent = data.message;
                    if (data.responder) {
                        document.getElementById('responderName').textContent = data.responder.name;
                    }
                } else if (data.no_responders) {
                    panicBtn.innerHTML = '<span>...</span><small>Searching</small>';
                    document.getElementById('emergencyStatus').textContent = 'Searching for nearest help...';
                } else {
                    panicBtn.innerHTML = '<span>SOS</span><small>Alert Sent</small>';
                    document.getElementById('emergencyStatus').textContent = data.message;
                }

                document.getElementById('liveAlert').style.display = 'block';
            })
            .catch(err => {
                console.error('SOS dispatch failed:', err);
                resetPanicBtn();
                let msg = 'Could not reach ResQLink. Please tap SOS again.';
                if (err.message && err.message.startsWith('HTTP_')) {
                    const status = parseInt(err.message.split(':')[0].replace('HTTP_', ''));
                    if (status === 419) msg = 'Session expired. Please refresh the page, then tap SOS again.';
                    else if (status >= 500) msg = 'ResQLink server error (' + status + '). Please try again in a moment.';
                    else if (status === 401 || status === 403) msg = 'Please log in again, then tap SOS.';
                    else msg = 'SOS request failed (error ' + status + '). Please try again.';
                }
                setTimeout(() => alert(msg), 50);
            });
        }, (error) => {
            resetPanicBtn();
            const messages = {
                1: 'Location permission was denied. Please allow location access in your browser settings, then tap SOS again.',
                2: 'Your location could not be determined. Please try again, ideally outdoors or near a window.',
                3: 'Getting your location took too long. Please tap SOS again.'
            };
            setTimeout(() => alert(messages[error.code] || 'Could not get your location. Please tap SOS again.'), 50);
        }, {
            enableHighAccuracy: true,
            timeout: 15000,
            maximumAge: 0
        });
    }

    function startPanicRecording(uuid) {
        if (!navigator.mediaDevices || !navigator.mediaDevices.getUserMedia) {
            console.warn('Media recording not supported in this browser.');
            return;
        }

        // Audio-only — camera permission not required, works on all devices
        navigator.mediaDevices.getUserMedia({ audio: true, video: false })
            .then(stream => doRecord(stream, uuid, 'audio/webm', 'panic_audio.webm'))
            .catch(err => console.warn('Microphone access denied — evidence not recorded:', err.message));
    }

    function doRecord(stream, uuid, mimeType, filename) {
        const options = MediaRecorder.isTypeSupported(mimeType) ? { mimeType } : {};
        const mediaRecorder = new MediaRecorder(stream, options);
        const chunks = [];

        mediaRecorder.ondataavailable = (e) => {
            if (e.data.size > 0) chunks.push(e.data);
        };

        mediaRecorder.onstop = () => {
            const blob = new Blob(chunks, { type: mimeType });
            const formData = new FormData();
            formData.append('evidence', blob, filename);
            formData.append('_token', '{{ csrf_token() }}');

            fetch(`/emergency/evidence/${uuid}`, {
                method: 'POST',
                body: formData
            })
            .then(res => res.json())
            .then(data => console.log('Voice evidence uploaded:', data))
            .catch(err => console.error('Evidence upload failed:', err))
            .finally(() => stream.getTracks().forEach(t => t.stop()));
        };

        mediaRecorder.start();
        console.log('Voice note recording started...');

        // Record for 30 seconds then auto-stop
        setTimeout(() => {
            if (mediaRecorder.state === 'recording') {
                mediaRecorder.stop();
                console.log('Voice note recording complete.');
            }
        }, 30000);
    }

    let userLocationWatcher = null;

    function startPollingStatus() {
        if (!activeEmergencyUuid) return;
        
        if (pollingInterval) clearInterval(pollingInterval);
        
        pollingInterval = setInterval(() => {
            fetch(`/emergency/status/${activeEmergencyUuid}`)
                .then(res => res.json())
                .then(data => {
                    updateLiveUI(data);
                    if (data.status === 'resolved' || data.status === 'cancelled') {
                        stopPollingStatus();
                    }
                });
        }, 5000);

        // Start watching user location to share with responder
        if (navigator.geolocation && !userLocationWatcher) {
            userLocationWatcher = navigator.geolocation.watchPosition((position) => {
                const { latitude, longitude } = position.coords;
                
                // Update marker on local map
                if (userMarker) {
                    userMarker.setLatLng([latitude, longitude]);
                } else {
                    userMarker = L.marker([latitude, longitude]).addTo(map).bindPopup('Your Current Location').openPopup();
                }

                // Send update to server for responder to see
                fetch(`/emergency/update-location/${activeEmergencyUuid}`, {
                    method: 'POST',
                    headers: { 
                        'Content-Type': 'application/json', 
                        'X-CSRF-TOKEN': '{{ csrf_token() }}' 
                    },
                    body: JSON.stringify({ latitude, longitude })
                });
            }, (error) => console.error('Location Watch Error:', error), {
                enableHighAccuracy: true,
                timeout: 5000,
                maximumAge: 0
            });
        }
    }

    function updateLiveUI(data) {
        const overlay = document.getElementById('liveAlert');
        overlay.style.display = 'block';
        
        document.getElementById('emergencyStatus').textContent = `Status: ${data.status.toUpperCase()}`;
        
        if (data.responder) {
            document.getElementById('responderName').textContent = data.responder.name;
            document.getElementById('responderETA').textContent = `ETA: ${data.eta || '5'} mins`;
            document.getElementById('responderAvatar').textContent = data.responder.name.charAt(0);
            
            if (data.responder.lat && data.responder.lng) {
                const pos = [data.responder.lat, data.responder.lng];
                if (!responderMarker) {
                    responderMarker = L.marker(pos, {
                        icon: L.divIcon({ 
                            html: '<div style="background:var(--red); border:2px solid white; border-radius:50%; width:15px; height:15px; box-shadow: 0 0 10px rgba(229,9,20,0.5);"></div>', 
                            className: 'custom-div-icon' 
                        })
                    }).addTo(map).bindPopup('Assigned Responder');
                } else {
                    responderMarker.setLatLng(pos);
                }
                
                // Adjust map to show both user and responder
                if (userMarker) {
                    const group = new L.featureGroup([userMarker, responderMarker]);
                    map.fitBounds(group.getBounds().pad(0.5));
                }
            }
        }
    }

    function stopPollingStatus() {
        clearInterval(pollingInterval);
        if (userLocationWatcher) {
            navigator.geolocation.clearWatch(userLocationWatcher);
            userLocationWatcher = null;
        }
        document.getElementById('liveAlert').style.display = 'none';
        if (responderMarker) {
            map.removeLayer(responderMarker);
            responderMarker = null;
        }
        activeEmergencyUuid = null;
    }

    function cancelEmergency() {
        if (confirm('Are you sure you want to cancel this emergency?')) {
            stopPollingStatus();
            alert('Emergency cancelled.');
        }
    }

    // AI Voice SOS Implementation
    const voiceToggle = document.getElementById('voiceToggle');
    const voiceIcon = document.getElementById('voiceIcon');
    let recognition = null;
    let isListening = false;
    let sosSaid = false; // prevent double-trigger

    if ('webkitSpeechRecognition' in window || 'SpeechRecognition' in window) {
        const SpeechRecognition = window.SpeechRecognition || window.webkitSpeechRecognition;
        recognition = new SpeechRecognition();
        recognition.continuous = true;
        recognition.interimResults = false; // only fire on final results
        recognition.lang = 'en-US';

        recognition.onresult = (event) => {
            // Only check the newest result, not the whole accumulated list
            const latest = event.results[event.results.length - 1][0].transcript.toLowerCase().trim();
            console.log('Voice heard:', latest);

            if (!sosSaid && (
                latest.includes('emergency') ||
                latest.includes('help') ||
                latest.includes('resqlink') ||
                latest.includes('sos')
            )) {
                sosSaid = true;
                speak("Emergency detected. Triggering SOS. Help is on the way.");
                triggerEmergency();
                stopVoiceSOS();
            }
        };

        recognition.onerror = (event) => {
            console.warn('Voice recognition error:', event.error);
            const fatal = ['not-allowed', 'service-not-allowed', 'audio-capture'];
            if (fatal.includes(event.error)) {
                // Permission denied or no mic — stop instead of looping forever in onend
                stopVoiceSOS();
                const messages = {
                    'not-allowed': 'Microphone access was denied. Please allow microphone access in your browser settings to use Voice SOS.',
                    'service-not-allowed': 'Microphone access was denied. Please allow microphone access in your browser settings to use Voice SOS.',
                    'audio-capture': 'No microphone was found on this device. Voice SOS requires a working microphone.'
                };
                alert(messages[event.error]);
            } else if (isListening) {
                setTimeout(() => { if (isListening) recognition.start(); }, 1000);
            }
        };

        recognition.onend = () => {
            if (isListening) recognition.start();
        };
    }

    voiceToggle.addEventListener('click', () => {
        if (!window.isSecureContext) {
            alert('Voice SOS requires a secure (https://) connection. Please reload this page using https://.');
            return;
        }
        if (!recognition) {
            alert('Voice recognition is not supported in this browser. Please use Chrome or Edge.');
            return;
        }
        isListening ? stopVoiceSOS() : startVoiceSOS();
    });

    function startVoiceSOS() {
        isListening = true;
        sosSaid = false;
        voiceToggle.classList.add('active');
        voiceToggle.title = 'Voice SOS Active — tap to stop';
        voiceIcon.setAttribute('data-lucide', 'mic-off');
        lucide.createIcons();
        try { recognition.start(); } catch(e) {}
        speak("Voice mode active. Say help or emergency to trigger SOS.");
    }

    function stopVoiceSOS() {
        isListening = false;
        voiceToggle.classList.remove('active');
        voiceToggle.title = 'AI Voice SOS Mode';
        voiceIcon.setAttribute('data-lucide', 'mic');
        lucide.createIcons();
        try { recognition.stop(); } catch(e) {}
        speak("Voice mode deactivated.");
    }

    function speak(text) {
        if ('speechSynthesis' in window) {
            window.speechSynthesis.cancel(); // clear queue first
            const utterance = new SpeechSynthesisUtterance(text);
            utterance.rate = 1.0;
            utterance.pitch = 1.0;
            window.speechSynthesis.speak(utterance);
        }
    }

    // Samaritan Toggle Logic
    function toggleSamaritan(checkbox) {
        const isActive = checkbox.checked;
        const text = document.getElementById('samaritanText');
        text.textContent = isActive ? 'ACTIVE SAMARITAN' : 'SAMARITAN OFF';

        fetch('/user/toggle-samaritan', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({ active: isActive })
        }).then(() => {
            window.location.reload(); // Reload to show/hide missions
        });
    }

    // Initialize polling if there's an active emergency from page load
    if (activeEmergencyUuid) {
        startPollingStatus();
    }

    // Mobile sidebar toggle
    const hamburgerBtn = document.getElementById('hamburgerBtn');
    const sidebar = document.querySelector('.sidebar');
    const sidebarOverlay = document.getElementById('sidebarOverlay');

    hamburgerBtn.addEventListener('click', () => {
        sidebar.classList.toggle('open');
        sidebarOverlay.classList.toggle('active');
    });
    sidebarOverlay.addEventListener('click', () => {
        sidebar.classList.remove('open');
        sidebarOverlay.classList.remove('active');
    });
    document.querySelectorAll('.nav-item[data-tab]').forEach(item => {
        item.addEventListener('click', () => {
            if (window.innerWidth <= 768) {
                sidebar.classList.remove('open');
                sidebarOverlay.classList.remove('active');
            }
        });
    });
</script>
<!-- Support Widget -->
<div class="support-trigger" id="supportTrigger" style="position: fixed; bottom: 30px; left: 30px; width: 60px; height: 60px; background: var(--dark); border-radius: 50%; display: flex; align-items: center; justify-content: center; color: var(--white); cursor: pointer; box-shadow: 0 10px 30px rgba(0,0,0,0.3); z-index: 5000; transition: transform 0.3s; border: 1px solid var(--glass-border);">
    <i data-lucide="mail"></i>
</div>

<div class="support-window" id="supportWindow" style="position: fixed; bottom: 100px; left: 30px; width: 350px; background: var(--glass); border: 1px solid var(--glass-border); border-radius: 20px; display: none; flex-direction: column; overflow: hidden; z-index: 5000; box-shadow: 0 20px 50px rgba(0,0,0,0.5); backdrop-filter: blur(20px);">
    <div style="background: rgba(0,0,0,0.5); padding: 15px; display: flex; align-items: center; justify-content: space-between; border-bottom: 1px solid var(--glass-border);">
        <h4 style="margin: 0; color: white;">Contact Admin</h4>
        <button onclick="toggleSupport()" style="background: transparent; border: none; color: var(--grey); cursor: pointer;"><i data-lucide="x" style="width: 20px;"></i></button>
    </div>
    <div style="padding: 20px;">
        @if(session('success'))
            <div style="color: #22c55e; margin-bottom: 15px; font-size: 0.9rem;">{{ session('success') }}</div>
        @endif
        <form action="{{ route('support.message') }}" method="POST">
            @csrf
            <textarea name="message" placeholder="How can we help you?" required rows="4" style="width: 100%; margin-bottom: 10px; padding: 10px; border-radius: 8px; border: 1px solid var(--glass-border); background: rgba(255,255,255,0.05); color: white; resize: none;"></textarea>
            <button type="submit" class="btn-primary" style="width: 100%; border: none; padding: 12px; border-radius: 8px; cursor: pointer;">Send Message</button>
        </form>
    </div>
</div>

<script>
    document.getElementById('supportTrigger').addEventListener('click', toggleSupport);
    function toggleSupport() {
        const w = document.getElementById('supportWindow');
        w.style.display = w.style.display === 'flex' ? 'none' : 'flex';
    }
    
    @if(session('success'))
        toggleSupport();
    @endif
    
    lucide.createIcons();
</script>
<script src="/js/chat.js"></script>
<script src="/js/pwa.js" defer></script>
<script>
    // ── Emergency Real-Time Chat ──────────────────────────────────────
    let chatPolling = null;
    let lastChatId = 0;

    function openEmergencyChat() {
        if (!activeEmergencyUuid) return;
        const el = document.getElementById('emergencyChat');
        el.style.display = el.style.display === 'flex' ? 'none' : 'flex';
        if (el.style.display === 'flex') {
            pollEmergencyChat();
            if (!chatPolling) chatPolling = setInterval(pollEmergencyChat, 3000);
        }
    }

    function pollEmergencyChat() {
        if (!activeEmergencyUuid) return;
        fetch(`/chat/${activeEmergencyUuid}/messages`)
            .then(r => r.json())
            .then(msgs => {
                const body = document.getElementById('emergencyChatBody');
                if (!msgs.length) return;
                const newMsgs = msgs.filter(m => m.id > lastChatId);
                newMsgs.forEach(m => {
                    lastChatId = Math.max(lastChatId, m.id);
                    const isMe = m.sender_role === 'user';
                    const div = document.createElement('div');
                    div.className = isMe ? 'echat-msg me' : 'echat-msg them';
                    div.textContent = m.message;
                    body.appendChild(div);
                });
                body.scrollTop = body.scrollHeight;
            }).catch(() => {});
    }

    function sendEmergencyChat() {
        if (!activeEmergencyUuid) return;
        const input = document.getElementById('emergencyChatInput');
        const msg = input.value.trim();
        if (!msg) return;
        input.value = '';
        fetch(`/chat/${activeEmergencyUuid}/send`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
            body: JSON.stringify({ message: msg })
        }).then(() => pollEmergencyChat()).catch(() => {});
    }
</script>

<!-- MAMA CARE LOGIC -->
<script>
    function toggleMamaCare(checkbox) {
        const isActive = checkbox.checked;
        const textLabel = document.getElementById('mamaCareText');
        const contentArea = document.getElementById('mamaCareContent');
        
        textLabel.textContent = isActive ? 'ACTIVE' : 'OFF';
        if (isActive) {
            contentArea.classList.add('active');
        } else {
            contentArea.classList.remove('active');
        }

        fetch('/user/toggle-mamacare', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({ active: isActive })
        })
        .then(response => response.json())
        .then(data => {
            if(!data.success) {
                console.error("Failed to toggle Mama Care");
            }
        });
    }

    // Labor SOS Trigger
    document.getElementById('laborSosBtn')?.addEventListener('click', () => {
        if (!navigator.geolocation) {
            alert('Location services are required to trigger an SOS.');
            return;
        }

        const btn = document.getElementById('laborSosBtn');
        const originalText = btn.innerHTML;
        btn.innerHTML = '<i data-lucide="loader" class="spin"></i> DISPATCHING...';
        btn.disabled = true;

        navigator.geolocation.getCurrentPosition(position => {
            const payload = {
                type_id: 1, // Usually Medical
                subtype: 'Labor / Maternity',
                latitude: position.coords.latitude,
                longitude: position.coords.longitude
            };

            fetch('{{ route("emergency.trigger") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify(payload)
            })
            .then(res => res.json())
            .then(data => {
                if(data.success) {
                    window.location.reload();
                } else {
                    alert('SOS Failed: ' + data.message);
                    btn.innerHTML = originalText;
                    btn.disabled = false;
                }
            })
            .catch(err => {
                console.error(err);
                alert('Connection error. Please try again.');
                btn.innerHTML = originalText;
                btn.disabled = false;
            });
        }, error => {
            alert('Failed to get location. Please enable GPS.');
            btn.innerHTML = originalText;
            btn.disabled = false;
        });
    });

    // Kick Counter
    let kicks = 0;
    function recordKick() {
        kicks++;
        document.getElementById('kickCounterDisplay').textContent = kicks;
    }
    function resetKicks() {
        kicks = 0;
        document.getElementById('kickCounterDisplay').textContent = kicks;
    }

    // Contraction Timer
    let contractionInterval = null;
    let contractionStartTime = null;
    let isContractionRunning = false;

    function toggleContractionTimer() {
        const btn = document.getElementById('contractionBtn');
        const display = document.getElementById('contractionTimerDisplay');
        const log = document.getElementById('contractionLog');

        if (!isContractionRunning) {
            // Start
            isContractionRunning = true;
            contractionStartTime = new Date();
            btn.textContent = "Stop Timer";
            btn.style.background = "#ec4899";
            btn.style.color = "white";

            contractionInterval = setInterval(() => {
                const now = new Date();
                const diff = Math.floor((now - contractionStartTime) / 1000);
                const m = String(Math.floor(diff / 60)).padStart(2, '0');
                const s = String(diff % 60).padStart(2, '0');
                display.textContent = m + ':' + s;
            }, 1000);
        } else {
            // Stop
            clearInterval(contractionInterval);
            isContractionRunning = false;
            btn.textContent = "Start Timer";
            btn.style.background = "transparent";
            btn.style.color = "#ec4899";

            const duration = display.textContent;
            log.innerHTML = `Last: ${duration} <br> ${log.innerHTML}`;
            display.textContent = "00:00";
        }
    }
</script>
@include('partials.profile-modal')
</body>
</html>
