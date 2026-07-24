<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Verifications | ResQLink Admin</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="manifest" href="/manifest.json">
    <link rel="stylesheet" href="/css/dashboard.css">
    <script src="https://unpkg.com/lucide@latest"></script>
    <style>
        .theme-toggle { background: transparent; border: none; color: var(--grey); cursor: pointer; padding: 8px; border-radius: 50%; transition: all 0.3s; display: flex; align-items: center; justify-content: center; }
        .theme-toggle:hover { background: var(--glass); color: var(--white); }
        .panel-card { background: var(--dark); border: 1px solid var(--glass-border); border-radius: 24px; padding: 24px; }
        .section-head-icon { display: flex; align-items: flex-start; gap: 14px; margin-bottom: 22px; }
        .icon-box { width: 46px; height: 46px; border-radius: 14px; display: flex; align-items: center; justify-content: center; flex-shrink: 0; }
        .icon-box i { width: 20px; height: 20px; }
        .icon-box-amber { background: rgba(245,158,11,0.12); color: #f59e0b; }
        .icon-box-green { background: rgba(34,197,94,0.12); color: #22c55e; }
        .section-subtitle { margin: 4px 0 0; font-size: 0.82rem; color: var(--grey); }
        .verify-row { background: rgba(255,255,255,0.02); border: 1px solid var(--glass-border); border-radius: 14px; padding: 16px 18px; margin-bottom: 12px; }
        .verify-row-top { display: flex; justify-content: space-between; align-items: flex-start; gap: 12px; flex-wrap: wrap; }
        .verify-meta { color: var(--grey); font-size: 0.78rem; margin-top: 4px; display: flex; gap: 12px; flex-wrap: wrap; }
        .doc-links { display: flex; gap: 10px; margin-top: 12px; flex-wrap: wrap; }
        .doc-link { display: inline-flex; align-items: center; gap: 6px; padding: 7px 12px; border-radius: 8px; border: 1px solid var(--glass-border); background: rgba(255,255,255,0.03); color: #3b82f6; font-size: 0.78rem; font-weight: 700; text-decoration: none; }
        .verify-actions { display: flex; gap: 8px; flex-shrink: 0; }
        .mini-btn { padding: 8px 14px; font-size: 0.75rem; font-weight: 700; border-radius: 8px; cursor: pointer; border: 1px solid transparent; }
        .empty-state { text-align: center; padding: 40px 20px; color: var(--grey); border: 1px dashed var(--glass-border); border-radius: 18px; }
        .badge { display: inline-flex; align-items: center; padding: 3px 11px; border-radius: 6px; font-size: 0.7rem; font-weight: 600; text-transform: capitalize; border: 1px solid transparent; white-space: nowrap; }
        .badge-critical { background: rgba(229,9,20,0.12); color: var(--red); border-color: rgba(229,9,20,0.22); }
        .badge-positive { background: rgba(34,197,94,0.12); color: #22c55e; border-color: rgba(34,197,94,0.22); }
        .badge-neutral { background: var(--glass); color: var(--text-muted); border-color: var(--glass-border); }
        :root.light-mode .panel-card,
        :root.light-mode .verify-row { background: #ffffff; border-color: rgba(15, 23, 42, 0.08); }
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
        <a href="{{ route('admin.verifications.index') }}" class="nav-item active"><i data-lucide="badge-check"></i> Verifications</a>
        <a href="{{ route('admin.analytics') }}" class="nav-item"><i data-lucide="bar-chart-3"></i> System Analytics</a>
        <a href="{{ route('admin.blog.index') }}" class="nav-item"><i data-lucide="newspaper"></i> Blog & News</a>
        <a href="{{ route('admin.tools') }}" class="nav-item"><i data-lucide="wrench"></i> Platform Tools</a>
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
            <h1 style="font-size: 1.4rem; font-weight: 800;">Verifications</h1>
            <p style="color: var(--grey); font-size: 0.85rem;">Review and approve partner accounts (doctors, hospitals, responders)</p>
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

    <section class="panel-card" style="margin-bottom: 24px;">
        <div class="section-head-icon">
            <span class="icon-box icon-box-amber"><i data-lucide="clock"></i></span>
            <div>
                <h3 class="section-title" style="margin:0;">Pending Verification <span class="badge badge-neutral" style="margin-left:6px;">{{ $pending->count() }}</span></h3>
                <p class="section-subtitle">Partner accounts waiting for document review.</p>
            </div>
        </div>

        @forelse($pending as $user)
        <div class="verify-row">
            <div class="verify-row-top">
                <div>
                    <strong style="font-size: 0.95rem;">{{ $user->name }}</strong>
                    <span class="badge badge-neutral" style="margin-left: 8px;">{{ ucfirst($user->role) }}</span>
                    <div class="verify-meta">
                        <span>{{ $user->email }}</span>
                        <span>{{ $user->phone }}</span>
                        <span>Registered {{ $user->created_at->diffForHumans() }}</span>
                    </div>
                    <div class="doc-links">
                        @if($user->license_path)
                        <a href="{{ $user->license_path }}" target="_blank" rel="noopener noreferrer" class="doc-link"><i data-lucide="file-text" style="width:14px;height:14px;"></i> License</a>
                        @endif
                        @if($user->additional_docs_path)
                        <a href="{{ $user->additional_docs_path }}" target="_blank" rel="noopener noreferrer" class="doc-link"><i data-lucide="folder-open" style="width:14px;height:14px;"></i> Additional Docs</a>
                        @endif
                        @if(!$user->license_path && !$user->additional_docs_path)
                        <span style="font-size: 0.78rem; color: var(--grey);">No documents submitted.</span>
                        @endif
                    </div>
                </div>
                <div class="verify-actions">
                    <form action="{{ route('admin.verifications.approve', $user->id) }}" method="POST">
                        @csrf
                        <button type="submit" class="mini-btn" style="background: rgba(34,197,94,0.15); color: #22c55e; border-color: rgba(34,197,94,0.3);">Approve</button>
                    </form>
                    <button type="button" class="mini-btn" style="background: rgba(229,9,20,0.08); color: var(--red); border-color: rgba(229,9,20,0.2);" onclick="openRejectModal({{ $user->id }}, '{{ addslashes($user->name) }}')">Reject</button>
                </div>
            </div>
        </div>
        @empty
        <div class="empty-state">
            <i data-lucide="badge-check" style="width:40px;height:40px;opacity:0.5;margin-bottom:12px;"></i>
            <p style="margin:0;">No pending verifications right now.</p>
        </div>
        @endforelse
    </section>

    <section class="panel-card">
        <div class="section-head-icon">
            <span class="icon-box icon-box-green"><i data-lucide="history"></i></span>
            <div>
                <h3 class="section-title" style="margin:0;">Recently Reviewed</h3>
                <p class="section-subtitle">The last 30 verification decisions.</p>
            </div>
        </div>

        @forelse($reviewed as $user)
        <div class="verify-row">
            <div class="verify-row-top">
                <div>
                    <strong style="font-size: 0.92rem;">{{ $user->name }}</strong>
                    <span class="badge badge-neutral" style="margin-left: 8px;">{{ ucfirst($user->role) }}</span>
                    <span class="badge {{ $user->is_verified ? 'badge-positive' : 'badge-critical' }}" style="margin-left: 6px;">{{ $user->is_verified ? 'Approved' : 'Rejected' }}</span>
                    <div class="verify-meta">
                        <span>Reviewed {{ $user->verification_reviewed_at?->diffForHumans() }}</span>
                    </div>
                    @if(!$user->is_verified && $user->verification_rejected_reason)
                    <p style="margin: 8px 0 0; font-size: 0.8rem; color: var(--grey);"><strong>Reason:</strong> {{ $user->verification_rejected_reason }}</p>
                    @endif
                </div>
            </div>
        </div>
        @empty
        <div class="empty-state">
            <i data-lucide="history" style="width:40px;height:40px;opacity:0.5;margin-bottom:12px;"></i>
            <p style="margin:0;">No verification decisions have been made yet.</p>
        </div>
        @endforelse
    </section>
</main>

<div id="rejectModal" style="display:none; position:fixed; inset:0; background:rgba(0,0,0,0.6); z-index:5000; align-items:center; justify-content:center;">
    <div style="background:var(--dark2); border:1px solid var(--glass-border); border-radius:18px; padding:24px; width:90%; max-width:420px;">
        <h3 style="margin:0 0 6px;">Reject Verification</h3>
        <p style="margin:0 0 16px; font-size:0.85rem; color:var(--grey);">Rejecting <strong id="rejectUserName"></strong>. Give a reason so they know what to fix.</p>
        <form id="rejectForm" method="POST">
            @csrf
            <textarea name="reason" required placeholder="e.g. License document is unreadable, please re-upload." style="width:100%; min-height:100px; background: rgba(255,255,255,0.03); border:1px solid var(--glass-border); color:var(--white); padding:12px 14px; border-radius:12px; font-size:0.88rem;"></textarea>
            <div style="display:flex; gap:10px; margin-top:16px; justify-content:flex-end;">
                <button type="button" class="mini-btn" style="background: var(--glass); color: var(--white);" onclick="closeRejectModal()">Cancel</button>
                <button type="submit" class="mini-btn" style="background: var(--red); color: white;">Reject</button>
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

    function openRejectModal(userId, userName) {
        document.getElementById('rejectUserName').textContent = userName;
        document.getElementById('rejectForm').action = '/admin/verifications/' + userId + '/reject';
        document.getElementById('rejectModal').style.display = 'flex';
    }

    function closeRejectModal() {
        document.getElementById('rejectModal').style.display = 'none';
    }
</script>
@include('partials.profile-modal')
</body>
</html>
