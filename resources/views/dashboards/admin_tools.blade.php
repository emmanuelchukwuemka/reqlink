<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Platform Tools | ResQLink Admin</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="manifest" href="/manifest.json">
    <link rel="stylesheet" href="/css/dashboard.css">
    <script src="https://unpkg.com/lucide@latest"></script>
    <style>
        .tab-pane { display: none; }
        .tab-pane.active { display: block; }
        .nav-item { cursor: pointer; }
        .theme-toggle { background: transparent; border: none; color: var(--grey); cursor: pointer; padding: 8px; border-radius: 50%; transition: all 0.3s; display: flex; align-items: center; justify-content: center; }
        .theme-toggle:hover { background: var(--glass); color: var(--white); }
        .panel-card { background: var(--dark); border: 1px solid var(--glass-border); border-radius: 24px; padding: 24px; margin-bottom: 24px; }
        .section-head-icon { display: flex; align-items: flex-start; gap: 14px; margin-bottom: 22px; }
        .icon-box { width: 46px; height: 46px; border-radius: 14px; display: flex; align-items: center; justify-content: center; flex-shrink: 0; }
        .icon-box i { width: 20px; height: 20px; }
        .icon-box-blue { background: rgba(59,130,246,0.12); color: #3b82f6; }
        .icon-box-green { background: rgba(34,197,94,0.12); color: #22c55e; }
        .icon-box-purple { background: rgba(139,92,246,0.12); color: #8b5cf6; }
        .icon-box-red { background: rgba(229,9,20,0.12); color: var(--red); }
        .icon-box-neutral { background: var(--glass); color: var(--text-muted); }
        .section-subtitle { margin: 4px 0 0; font-size: 0.82rem; color: var(--grey); }
        .sub-tabs { display: flex; gap: 8px; flex-wrap: wrap; margin-bottom: 24px; border-bottom: 1px solid var(--glass-border); padding-bottom: 14px; }
        .sub-tab { padding: 9px 16px; border-radius: 10px; border: 1px solid var(--glass-border); background: var(--glass); color: var(--grey); font-size: 0.82rem; font-weight: 700; cursor: pointer; display: flex; align-items: center; gap: 8px; }
        .sub-tab.active { background: rgba(229,9,20,0.1); color: var(--red); border-color: rgba(229,9,20,0.25); }
        .row-card { background: rgba(255,255,255,0.02); border: 1px solid var(--glass-border); border-radius: 14px; padding: 16px 18px; margin-bottom: 12px; }
        .row-top { display: flex; justify-content: space-between; align-items: flex-start; gap: 12px; flex-wrap: wrap; }
        .row-meta { color: var(--grey); font-size: 0.78rem; margin-top: 4px; display: flex; gap: 12px; flex-wrap: wrap; }
        .mini-btn { padding: 8px 14px; font-size: 0.75rem; font-weight: 700; border-radius: 8px; cursor: pointer; border: 1px solid transparent; }
        .empty-state { text-align: center; padding: 40px 20px; color: var(--grey); border: 1px dashed var(--glass-border); border-radius: 18px; }
        .badge { display: inline-flex; align-items: center; padding: 3px 11px; border-radius: 6px; font-size: 0.7rem; font-weight: 600; text-transform: capitalize; border: 1px solid transparent; white-space: nowrap; }
        .badge-critical { background: rgba(229,9,20,0.12); color: var(--red); border-color: rgba(229,9,20,0.22); }
        .badge-positive { background: rgba(34,197,94,0.12); color: #22c55e; border-color: rgba(34,197,94,0.22); }
        .badge-neutral { background: var(--glass); color: var(--text-muted); border-color: var(--glass-border); }
        .badge-amber { background: rgba(245,158,11,0.12); color: #f59e0b; border-color: rgba(245,158,11,0.25); }
        .stats-mini-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(160px, 1fr)); gap: 16px; margin-bottom: 24px; }
        .stat-card-sm { background: var(--glass); border: 1px solid var(--glass-border); border-radius: 14px; padding: 18px 20px; }
        .stat-card-sm .stat-value { font-size: 1.6rem; font-weight: 900; }
        .stat-card-sm .stat-label { font-size: 0.72rem; color: var(--grey); text-transform: uppercase; letter-spacing: 1px; margin-top: 4px; }
        .form-group { display: flex; flex-direction: column; gap: 8px; }
        .form-group label { font-size: 0.72rem; color: var(--grey); font-weight: 800; text-transform: uppercase; letter-spacing: 0.8px; }
        .form-group input, .form-group textarea, .form-group select { width: 100%; background: rgba(255,255,255,0.03); border: 1px solid var(--glass-border); color: var(--white); padding: 12px 14px; border-radius: 14px; font-size: 0.9rem; outline: none; }
        .form-group textarea { min-height: 90px; resize: vertical; }
        .form-grid-2 { display: grid; grid-template-columns: 1fr 1fr; gap: 14px; }
        .type-chip { display: inline-flex; align-items: center; gap: 8px; padding: 8px 14px; border-radius: 10px; background: var(--glass); border: 1px solid var(--glass-border); font-size: 0.82rem; font-weight: 700; margin: 0 8px 8px 0; }
        .star-rating { color: #f59e0b; letter-spacing: 2px; }
        .table-scroll { overflow-x: auto; }
        .admin-table { width: 100%; border-collapse: separate; border-spacing: 0 8px; }
        .admin-table th { text-align: left; padding: 10px 14px; color: var(--grey); font-size: 0.72rem; text-transform: uppercase; letter-spacing: 1px; }
        .admin-table tr { background: rgba(255,255,255,0.02); }
        .admin-table td { padding: 10px 14px; border-top: 1px solid var(--glass-border); border-bottom: 1px solid var(--glass-border); font-size: 0.84rem; }
        .admin-table td:first-child { border-left: 1px solid var(--glass-border); border-top-left-radius: 10px; border-bottom-left-radius: 10px; }
        .admin-table td:last-child { border-right: 1px solid var(--glass-border); border-top-right-radius: 10px; border-bottom-right-radius: 10px; }
        :root.light-mode .panel-card,
        :root.light-mode .row-card,
        :root.light-mode .type-chip { background: #ffffff; border-color: rgba(15, 23, 42, 0.08); }
        @media (max-width: 768px) { .form-grid-2 { grid-template-columns: 1fr; } }
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
        <div style="font-size: 0.6rem; color: var(--red); font-weight: 900; text-transform: uppercase; margin-top: 5px; letter-spacing: 2px;">Admin Portal</div>
    </div>
    <nav class="sidebar-nav">
        <a href="{{ route('dashboard') }}" class="nav-item"><i data-lucide="users"></i> User Management</a>
        <a href="{{ route('admin.command-center') }}" class="nav-item"><i data-lucide="shield-alert" style="color: var(--red);"></i> Command Center</a>
        <a href="{{ route('admin.incidents') }}" class="nav-item"><i data-lucide="activity"></i> Global Incidents</a>
        <a href="{{ route('admin.agencies') }}" class="nav-item"><i data-lucide="building-2"></i> Agency Oversight</a>
        <a href="{{ route('admin.verifications.index') }}" class="nav-item"><i data-lucide="badge-check"></i> Verifications</a>
        <a href="{{ route('admin.analytics') }}" class="nav-item"><i data-lucide="bar-chart-3"></i> System Analytics</a>
        <a href="{{ route('admin.blog.index') }}" class="nav-item"><i data-lucide="newspaper"></i> Blog & News</a>
        <a href="{{ route('admin.tools') }}" class="nav-item active"><i data-lucide="wrench"></i> Platform Tools</a>
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
            <h1 id="pageTitle" style="font-size: 1.4rem; font-weight: 800;">Platform Tools</h1>
            <p style="color: var(--grey); font-size: 0.85rem;">Support, finance, reviews, announcements, and system settings</p>
        </div>
        <div class="topbar-actions">
            @include('partials.lang-switcher')
            <button id="themeToggle" class="theme-toggle" aria-label="Toggle Dark Mode">
                <i data-lucide="sun" id="themeIcon"></i>
            </button>
            <div class="user-profile">
                <div class="user-info">
                    <span>{{ Auth::user()->name }}</span>
                    <small>System Administrator</small>
                </div>
                <div class="avatar" style="background: var(--red)">{{ substr(Auth::user()->name, 0, 1) }}</div>
            </div>
        </div>
    </header>

    @include('partials.announcement-banner')

    @if(session('success'))
    <div class="flash-success"><i data-lucide="check-circle" style="width:16px;height:16px;flex-shrink:0;"></i> {{ session('success') }}</div>
    @endif
    @if(session('error'))
    <div class="flash-error"><i data-lucide="alert-circle" style="width:16px;height:16px;flex-shrink:0;"></i> {{ session('error') }}</div>
    @endif

    <div class="sub-tabs">
        <span class="sub-tab active" data-tab="support"><i data-lucide="life-buoy" style="width:14px;height:14px;"></i> Support Inbox @if($unreadSupportCount) <span class="badge badge-critical">{{ $unreadSupportCount }}</span> @endif</span>
        <span class="sub-tab" data-tab="finance"><i data-lucide="wallet" style="width:14px;height:14px;"></i> Financial Ledger</span>
        <span class="sub-tab" data-tab="reviews"><i data-lucide="star" style="width:14px;height:14px;"></i> Reviews</span>
        <span class="sub-tab" data-tab="announcements"><i data-lucide="megaphone" style="width:14px;height:14px;"></i> Announcements</span>
        <span class="sub-tab" data-tab="emergency-types"><i data-lucide="siren" style="width:14px;height:14px;"></i> Emergency Types</span>
        <span class="sub-tab" data-tab="activity-log"><i data-lucide="history" style="width:14px;height:14px;"></i> Activity Log</span>
    </div>

    {{-- SUPPORT INBOX --}}
    <div id="support" class="tab-pane active">
        <section class="panel-card">
            <div class="section-head-icon">
                <span class="icon-box icon-box-blue"><i data-lucide="life-buoy"></i></span>
                <div>
                    <h3 class="section-title" style="margin:0;">Support Inbox</h3>
                    <p class="section-subtitle">Messages submitted through the support form.</p>
                </div>
            </div>

            @forelse($supportMessages as $message)
            <div class="row-card">
                <div class="row-top">
                    <div>
                        <strong>{{ $message->name ?: 'Anonymous' }}</strong>
                        @if(!$message->is_read)<span class="badge badge-critical" style="margin-left:8px;">Unread</span>@endif
                        @if($message->replied_at)<span class="badge badge-positive" style="margin-left:8px;">Replied</span>@endif
                        <div class="row-meta">
                            <span>{{ $message->email ?: 'No email' }}</span>
                            <span>{{ $message->created_at->diffForHumans() }}</span>
                        </div>
                        <p style="margin: 10px 0 0; font-size: 0.86rem;">{{ $message->message }}</p>
                        @if($message->admin_reply)
                        <div style="margin-top:10px; padding:10px 12px; background: rgba(34,197,94,0.06); border-radius:10px; border:1px solid rgba(34,197,94,0.15);">
                            <p style="margin:0; font-size:0.68rem; text-transform:uppercase; font-weight:800; color:#22c55e; letter-spacing:0.6px;">Your Reply</p>
                            <p style="margin:4px 0 0; font-size:0.82rem;">{{ $message->admin_reply }}</p>
                        </div>
                        @endif
                    </div>
                    <div style="display:flex; gap:8px; flex-shrink:0;">
                        @if(!$message->is_read)
                        <form action="{{ route('admin.support.read', $message->id) }}" method="POST">
                            @csrf
                            <button type="submit" class="mini-btn" style="background: var(--glass); color: var(--white);">Mark Read</button>
                        </form>
                        @endif
                        <button type="button" class="mini-btn" style="background: rgba(59,130,246,0.12); color: #3b82f6; border-color: rgba(59,130,246,0.3);" onclick="openReplyModal({{ $message->id }}, '{{ addslashes($message->name ?: 'Anonymous') }}')">Reply</button>
                        <form action="{{ route('admin.support.destroy', $message->id) }}" method="POST" onsubmit="return confirm('Delete this message?');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="mini-btn" style="background: rgba(229,9,20,0.08); color: var(--red); border-color: rgba(229,9,20,0.2);">Delete</button>
                        </form>
                    </div>
                </div>
            </div>
            @empty
            <div class="empty-state"><i data-lucide="life-buoy" style="width:40px;height:40px;opacity:0.5;margin-bottom:12px;"></i><p style="margin:0;">No support messages yet.</p></div>
            @endforelse
            <div style="margin-top: 16px;">{{ $supportMessages->links() }}</div>
        </section>
    </div>

    {{-- FINANCIAL LEDGER --}}
    <div id="finance" class="tab-pane">
        <section class="panel-card">
            <div class="section-head-icon">
                <span class="icon-box icon-box-green"><i data-lucide="wallet"></i></span>
                <div>
                    <h3 class="section-title" style="margin:0;">Financial Ledger</h3>
                    <p class="section-subtitle">All wallet transactions across the platform.</p>
                </div>
            </div>

            <div class="stats-mini-grid">
                <div class="stat-card-sm">
                    <div class="stat-value" style="color:#22c55e;">₦{{ number_format($financeStats['total_credits'], 2) }}</div>
                    <div class="stat-label">Total Credits</div>
                </div>
                <div class="stat-card-sm">
                    <div class="stat-value" style="color: var(--red);">₦{{ number_format($financeStats['total_debits'], 2) }}</div>
                    <div class="stat-label">Total Debits</div>
                </div>
                <div class="stat-card-sm">
                    <div class="stat-value">{{ $financeStats['flagged_count'] }}</div>
                    <div class="stat-label">Flagged Transactions</div>
                </div>
            </div>

            <a href="{{ route('admin.finance.export') }}" class="mini-btn" style="background: rgba(59,130,246,0.1); color: #3b82f6; border-color: rgba(59,130,246,0.3); text-decoration: none; display:inline-flex; align-items:center; gap:6px; margin-bottom: 16px;">
                <i data-lucide="download" style="width:12px;height:12px;"></i> Export CSV
            </a>

            @forelse($transactions as $t)
            <div class="row-card" style="{{ $t->is_flagged ? 'border-color: rgba(245,158,11,0.4);' : '' }}">
                <div class="row-top">
                    <div>
                        <strong>{{ $t->user->name ?? 'Unknown' }}</strong>
                        <span class="badge {{ $t->type === 'credit' ? 'badge-positive' : 'badge-neutral' }}" style="margin-left:8px;">{{ ucfirst($t->type) }}</span>
                        <span class="badge {{ $t->status === 'success' ? 'badge-positive' : ($t->status === 'failed' ? 'badge-critical' : 'badge-neutral') }}" style="margin-left:6px;">{{ ucfirst($t->status) }}</span>
                        @if($t->is_flagged)<span class="badge badge-amber" style="margin-left:6px;">Flagged</span>@endif
                        <div class="row-meta">
                            <span>{{ $t->reference }}</span>
                            <span>{{ $t->description }}</span>
                            <span>{{ $t->created_at->format('M d, Y g:i A') }}</span>
                        </div>
                        @if($t->flag_note)<p style="margin:8px 0 0; font-size:0.8rem; color:#f59e0b;"><strong>Flag note:</strong> {{ $t->flag_note }}</p>@endif
                    </div>
                    <div style="display:flex; gap:8px; align-items:flex-start; flex-shrink:0;">
                        <strong style="font-size:1rem; color: {{ $t->type === 'credit' ? '#22c55e' : 'var(--red)' }};">{{ $t->type === 'credit' ? '+' : '-' }}₦{{ number_format($t->amount, 2) }}</strong>
                        @if(!$t->is_flagged)
                        <button type="button" class="mini-btn" style="background: rgba(245,158,11,0.12); color: #f59e0b; border-color: rgba(245,158,11,0.3);" onclick="openFlagModal({{ $t->id }})">Flag</button>
                        @else
                        <form action="{{ route('admin.finance.unflag', $t->id) }}" method="POST">
                            @csrf
                            <button type="submit" class="mini-btn" style="background: var(--glass); color: var(--white);">Unflag</button>
                        </form>
                        @endif
                    </div>
                </div>
            </div>
            @empty
            <div class="empty-state"><i data-lucide="wallet" style="width:40px;height:40px;opacity:0.5;margin-bottom:12px;"></i><p style="margin:0;">No transactions recorded yet.</p></div>
            @endforelse
            <div style="margin-top: 16px;">{{ $transactions->links() }}</div>
        </section>
    </div>

    {{-- REVIEWS --}}
    <div id="reviews" class="tab-pane">
        <section class="panel-card">
            <div class="section-head-icon">
                <span class="icon-box icon-box-amber" style="background: rgba(245,158,11,0.12); color: #f59e0b;"><i data-lucide="star"></i></span>
                <div>
                    <h3 class="section-title" style="margin:0;">Review Moderation</h3>
                    <p class="section-subtitle">Ratings and comments left for responders.</p>
                </div>
            </div>

            @forelse($reviews as $review)
            <div class="row-card">
                <div class="row-top">
                    <div>
                        <strong>{{ $review->user->name ?? 'Unknown' }}</strong>
                        <span style="color: var(--grey); font-size: 0.82rem;"> reviewed </span>
                        <strong>{{ $review->responder->user->name ?? 'Unknown Responder' }}</strong>
                        <div class="star-rating" style="margin-top:6px;">{{ str_repeat('★', $review->rating) }}{{ str_repeat('☆', 5 - $review->rating) }}</div>
                        @if($review->comment)<p style="margin: 8px 0 0; font-size: 0.86rem;">{{ $review->comment }}</p>@endif
                        <div class="row-meta">{{ $review->created_at->diffForHumans() }}</div>
                    </div>
                    <form action="{{ route('admin.reviews.destroy', $review->id) }}" method="POST" onsubmit="return confirm('Remove this review permanently?');">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="mini-btn" style="background: rgba(229,9,20,0.08); color: var(--red); border-color: rgba(229,9,20,0.2);">Remove</button>
                    </form>
                </div>
            </div>
            @empty
            <div class="empty-state"><i data-lucide="star" style="width:40px;height:40px;opacity:0.5;margin-bottom:12px;"></i><p style="margin:0;">No reviews yet.</p></div>
            @endforelse
            <div style="margin-top: 16px;">{{ $reviews->links() }}</div>
        </section>
    </div>

    {{-- ANNOUNCEMENTS --}}
    <div id="announcements" class="tab-pane">
        <section class="panel-card">
            <div class="section-head-icon">
                <span class="icon-box icon-box-blue"><i data-lucide="megaphone"></i></span>
                <div>
                    <h3 class="section-title" style="margin:0;">Send an Announcement</h3>
                    <p class="section-subtitle">Shows as a dismissible banner on dashboards. No email is sent.</p>
                </div>
            </div>
            <form action="{{ route('admin.announcements.store') }}" method="POST">
                @csrf
                <div class="form-grid-2">
                    <div class="form-group">
                        <label>Title</label>
                        <input type="text" name="title" required placeholder="e.g. Scheduled maintenance tonight">
                    </div>
                    <div class="form-group">
                        <label>Target Audience</label>
                        <select name="target_role">
                            <option value="">All users</option>
                            <option value="civilian">Civilians</option>
                            <option value="doctor">Doctors</option>
                            <option value="hospital">Hospitals</option>
                            <option value="ambulance">Ambulance</option>
                            <option value="security">Security</option>
                            <option value="fire">Fire</option>
                        </select>
                    </div>
                </div>
                <div class="form-group" style="margin-top: 14px;">
                    <label>Message</label>
                    <textarea name="message" required placeholder="Keep it short — this shows as a banner."></textarea>
                </div>
                <div class="form-group" style="margin-top: 14px; max-width: 260px;">
                    <label>Expires (optional)</label>
                    <input type="datetime-local" name="expires_at">
                </div>
                <button type="submit" class="btn-primary" style="margin-top: 18px; border:none; padding: 12px 20px; border-radius: 10px;">Publish Announcement</button>
            </form>
        </section>

        <section class="panel-card">
            <h3 class="section-title" style="margin-bottom: 18px;"><i data-lucide="list"></i> Active & Recent Announcements</h3>
            @forelse($announcements as $a)
            <div class="row-card">
                <div class="row-top">
                    <div>
                        <strong>{{ $a->title }}</strong>
                        <span class="badge badge-neutral" style="margin-left:8px;">{{ $a->target_role ? ucfirst($a->target_role) : 'All users' }}</span>
                        @if($a->expires_at && $a->expires_at->isPast())<span class="badge badge-neutral" style="margin-left:6px;">Expired</span>@endif
                        <p style="margin: 8px 0 0; font-size: 0.86rem;">{{ $a->message }}</p>
                        <div class="row-meta">
                            <span>By {{ $a->admin->name ?? 'Admin' }}</span>
                            <span>{{ $a->created_at->diffForHumans() }}</span>
                            @if($a->expires_at)<span>Expires {{ $a->expires_at->format('M d, Y g:i A') }}</span>@endif
                        </div>
                    </div>
                    <form action="{{ route('admin.announcements.destroy', $a->id) }}" method="POST" onsubmit="return confirm('Remove this announcement?');">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="mini-btn" style="background: rgba(229,9,20,0.08); color: var(--red); border-color: rgba(229,9,20,0.2);">Remove</button>
                    </form>
                </div>
            </div>
            @empty
            <div class="empty-state"><i data-lucide="megaphone" style="width:40px;height:40px;opacity:0.5;margin-bottom:12px;"></i><p style="margin:0;">No announcements sent yet.</p></div>
            @endforelse
        </section>
    </div>

    {{-- EMERGENCY TYPES --}}
    <div id="emergency-types" class="tab-pane">
        <section class="panel-card">
            <div class="section-head-icon">
                <span class="icon-box icon-box-red"><i data-lucide="siren"></i></span>
                <div>
                    <h3 class="section-title" style="margin:0;">Add Emergency Type</h3>
                    <p class="section-subtitle">Categories used across triage, dispatch, and reporting.</p>
                </div>
            </div>
            <form action="{{ route('admin.emergency-types.store') }}" method="POST">
                @csrf
                <div class="form-grid-2">
                    <div class="form-group">
                        <label>Name</label>
                        <input type="text" name="name" required placeholder="e.g. Cardiac Arrest">
                    </div>
                    <div class="form-group">
                        <label>Icon (Lucide name, optional)</label>
                        <input type="text" name="icon" placeholder="e.g. heart-pulse">
                    </div>
                </div>
                <div class="form-group" style="margin-top: 14px;">
                    <label>Description (optional)</label>
                    <textarea name="description" placeholder="Shown to responders/staff for context"></textarea>
                </div>
                <button type="submit" class="btn-primary" style="margin-top: 18px; border:none; padding: 12px 20px; border-radius: 10px;">Add Type</button>
            </form>
        </section>

        <section class="panel-card">
            <h3 class="section-title" style="margin-bottom: 18px;"><i data-lucide="list"></i> Existing Types</h3>
            @forelse($emergencyTypes as $type)
            <div class="row-card">
                <div class="row-top">
                    <div>
                        <strong>{{ $type->name }}</strong>
                        @if($type->icon)<span class="badge badge-neutral" style="margin-left:8px;"><i data-lucide="{{ $type->icon }}" style="width:11px;height:11px;"></i> {{ $type->icon }}</span>@endif
                        <span class="badge badge-neutral" style="margin-left:6px;">{{ $type->emergencies_count }} emergencies</span>
                        @if($type->description)<p style="margin: 8px 0 0; font-size: 0.84rem; color: var(--grey);">{{ $type->description }}</p>@endif
                    </div>
                    <div style="display:flex; gap:8px;">
                        <button type="button" class="mini-btn" style="background: rgba(59,130,246,0.12); color: #3b82f6; border-color: rgba(59,130,246,0.3);" onclick="openEditTypeModal({{ $type->id }}, '{{ addslashes($type->name) }}', '{{ addslashes($type->icon ?? '') }}', '{{ addslashes($type->description ?? '') }}')">Edit</button>
                        <form action="{{ route('admin.emergency-types.destroy', $type->id) }}" method="POST" onsubmit="return confirm('Delete this emergency type?');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="mini-btn" style="background: rgba(229,9,20,0.08); color: var(--red); border-color: rgba(229,9,20,0.2);">Delete</button>
                        </form>
                    </div>
                </div>
            </div>
            @empty
            <div class="empty-state"><i data-lucide="siren" style="width:40px;height:40px;opacity:0.5;margin-bottom:12px;"></i><p style="margin:0;">No emergency types configured yet.</p></div>
            @endforelse
        </section>
    </div>

    {{-- ACTIVITY LOG --}}
    <div id="activity-log" class="tab-pane">
        <section class="panel-card">
            <div class="section-head-icon">
                <span class="icon-box icon-box-neutral"><i data-lucide="history"></i></span>
                <div>
                    <h3 class="section-title" style="margin:0;">Admin Activity Log</h3>
                    <p class="section-subtitle">Who did what, and when.</p>
                </div>
            </div>
            <div class="table-scroll">
                <table class="admin-table">
                    <thead><tr><th>Admin</th><th>Action</th><th>Description</th><th>When</th></tr></thead>
                    <tbody>
                        @forelse($activityLogs as $log)
                        <tr>
                            <td>{{ $log->admin->name ?? 'Unknown' }}</td>
                            <td><span class="badge badge-neutral">{{ str_replace('_', ' ', $log->action) }}</span></td>
                            <td>{{ $log->description }}</td>
                            <td style="white-space:nowrap; color:var(--grey);">{{ $log->created_at->diffForHumans() }}</td>
                        </tr>
                        @empty
                        <tr><td colspan="4"><div class="empty-state"><i data-lucide="history" style="width:40px;height:40px;opacity:0.5;margin-bottom:12px;"></i><p style="margin:0;">No activity recorded yet.</p></div></td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div style="margin-top: 16px;">{{ $activityLogs->links() }}</div>
        </section>
    </div>
</main>

<div id="replyModal" style="display:none; position:fixed; inset:0; background:rgba(0,0,0,0.6); z-index:5000; align-items:center; justify-content:center;">
    <div style="background:var(--dark2); border:1px solid var(--glass-border); border-radius:18px; padding:24px; width:90%; max-width:420px;">
        <h3 style="margin:0 0 6px;">Reply to <span id="replyUserName"></span></h3>
        <p style="margin:0 0 16px; font-size:0.82rem; color:var(--grey);">Sent by email if they provided one.</p>
        <form id="replyForm" method="POST">
            @csrf
            <textarea name="reply" required placeholder="Type your reply..." style="width:100%; min-height:110px; background: rgba(255,255,255,0.03); border:1px solid var(--glass-border); color:var(--white); padding:12px 14px; border-radius:12px; font-size:0.88rem;"></textarea>
            <div style="display:flex; gap:10px; margin-top:16px; justify-content:flex-end;">
                <button type="button" class="mini-btn" style="background: var(--glass); color: var(--white);" onclick="closeModal('replyModal')">Cancel</button>
                <button type="submit" class="mini-btn" style="background: #3b82f6; color: white;">Send Reply</button>
            </div>
        </form>
    </div>
</div>

<div id="flagModal" style="display:none; position:fixed; inset:0; background:rgba(0,0,0,0.6); z-index:5000; align-items:center; justify-content:center;">
    <div style="background:var(--dark2); border:1px solid var(--glass-border); border-radius:18px; padding:24px; width:90%; max-width:420px;">
        <h3 style="margin:0 0 6px;">Flag Transaction</h3>
        <p style="margin:0 0 16px; font-size:0.82rem; color:var(--grey);">Add a note explaining why this looks suspicious.</p>
        <form id="flagForm" method="POST">
            @csrf
            <textarea name="flag_note" placeholder="e.g. Duplicate reference, unusually large amount..." style="width:100%; min-height:90px; background: rgba(255,255,255,0.03); border:1px solid var(--glass-border); color:var(--white); padding:12px 14px; border-radius:12px; font-size:0.88rem;"></textarea>
            <div style="display:flex; gap:10px; margin-top:16px; justify-content:flex-end;">
                <button type="button" class="mini-btn" style="background: var(--glass); color: var(--white);" onclick="closeModal('flagModal')">Cancel</button>
                <button type="submit" class="mini-btn" style="background: #f59e0b; color: white;">Flag Transaction</button>
            </div>
        </form>
    </div>
</div>

<div id="editTypeModal" style="display:none; position:fixed; inset:0; background:rgba(0,0,0,0.6); z-index:5000; align-items:center; justify-content:center;">
    <div style="background:var(--dark2); border:1px solid var(--glass-border); border-radius:18px; padding:24px; width:90%; max-width:420px;">
        <h3 style="margin:0 0 16px;">Edit Emergency Type</h3>
        <form id="editTypeForm" method="POST">
            @csrf
            @method('PUT')
            <div class="form-group">
                <label>Name</label>
                <input type="text" name="name" id="editTypeName" required>
            </div>
            <div class="form-group" style="margin-top: 12px;">
                <label>Icon</label>
                <input type="text" name="icon" id="editTypeIcon">
            </div>
            <div class="form-group" style="margin-top: 12px;">
                <label>Description</label>
                <textarea name="description" id="editTypeDescription"></textarea>
            </div>
            <div style="display:flex; gap:10px; margin-top:16px; justify-content:flex-end;">
                <button type="button" class="mini-btn" style="background: var(--glass); color: var(--white);" onclick="closeModal('editTypeModal')">Cancel</button>
                <button type="submit" class="mini-btn" style="background: #3b82f6; color: white;">Save Changes</button>
            </div>
        </form>
    </div>
</div>

<script>
    lucide.createIcons();

    (function () {
        const btn = document.getElementById('hamburgerBtn');
        const sidebar = document.querySelector('.sidebar');
        const overlay = document.getElementById('sidebarOverlay');
        btn.addEventListener('click', () => { sidebar.classList.toggle('open'); overlay.classList.toggle('active'); });
        overlay.addEventListener('click', () => { sidebar.classList.remove('open'); overlay.classList.remove('active'); });
    })();

    document.querySelectorAll('.sub-tab').forEach((tab) => {
        tab.addEventListener('click', () => {
            document.querySelectorAll('.sub-tab').forEach((t) => t.classList.remove('active'));
            tab.classList.add('active');
            document.querySelectorAll('.tab-pane').forEach((p) => p.classList.remove('active'));
            document.getElementById(tab.dataset.tab).classList.add('active');
        });
    });

    function closeModal(id) {
        document.getElementById(id).style.display = 'none';
    }

    function openReplyModal(id, name) {
        document.getElementById('replyUserName').textContent = name;
        document.getElementById('replyForm').action = '/admin/support/' + id + '/reply';
        document.getElementById('replyModal').style.display = 'flex';
    }

    function openFlagModal(id) {
        document.getElementById('flagForm').action = '/admin/finance/' + id + '/flag';
        document.getElementById('flagModal').style.display = 'flex';
    }

    function openEditTypeModal(id, name, icon, description) {
        document.getElementById('editTypeForm').action = '/admin/emergency-types/' + id;
        document.getElementById('editTypeName').value = name;
        document.getElementById('editTypeIcon').value = icon;
        document.getElementById('editTypeDescription').value = description;
        document.getElementById('editTypeModal').style.display = 'flex';
    }
</script>
@include('partials.profile-modal')
</body>
</html>
