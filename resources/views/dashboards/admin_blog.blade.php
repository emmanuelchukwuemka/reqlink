<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Blog & News | ResQLink Admin</title>
    <link rel="manifest" href="/manifest.json">
    <link rel="stylesheet" href="/css/dashboard.css">
    <script src="https://unpkg.com/lucide@latest"></script>
    <script src="https://cdn.ckeditor.com/ckeditor5/41.4.2/super-build/ckeditor.js"></script>
    <style>
        .page-shell { display: flex; flex-direction: column; gap: 24px; }
        .page-hero {
            display: flex;
            flex-direction: column;
            gap: 16px;
            padding: 22px;
            border: 1px solid var(--glass-border);
            border-radius: 20px;
            background:
                radial-gradient(circle at top right, rgba(229,9,20,0.14), transparent 36%),
                linear-gradient(135deg, rgba(255,255,255,0.03), rgba(255,255,255,0.015));
        }
        .page-kicker {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 7px 12px;
            border-radius: 999px;
            background: rgba(229,9,20,0.1);
            color: var(--red);
            font-size: 0.74rem;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0.7px;
            margin-bottom: 12px;
        }
        .page-hero h2 { font-size: 1.5rem; line-height: 1.15; margin: 0 0 6px; max-width: 760px; }
        .page-hero p { margin: 0; color: var(--grey); max-width: 760px; line-height: 1.65; font-size: 0.88rem; }
        .page-hero-main { display: flex; flex-direction: column; gap: 12px; }
        .page-hero-actions { display: flex; gap: 12px; flex-wrap: wrap; margin-top: 4px; }
        .page-meta-inline { display: flex; gap: 10px; flex-wrap: wrap; margin-top: 4px; }
        .page-meta-pill {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 8px 12px;
            border-radius: 999px;
            border: 1px solid var(--glass-border);
            background: rgba(255,255,255,0.03);
            color: var(--grey);
            font-size: 0.78rem;
            font-weight: 700;
        }
        .page-meta-pill strong { color: var(--white); font-size: 0.84rem; }
        .page-hero-side {
            border: 1px solid rgba(255,255,255,0.08);
            border-radius: 16px;
            padding: 14px 16px;
            background: rgba(8, 11, 18, 0.58);
            display: flex;
            flex-direction: column;
            gap: 10px;
            max-width: 420px;
        }
        .hero-side-label {
            font-size: 0.72rem;
            color: var(--grey);
            text-transform: uppercase;
            letter-spacing: 1px;
            font-weight: 800;
        }
        .hero-side-value {
            font-size: 1.6rem;
            line-height: 1;
            font-weight: 900;
            color: var(--white);
        }
        .hero-side-copy {
            color: var(--grey);
            font-size: 0.82rem;
            line-height: 1.55;
        }
        .hero-side-list {
            margin-top: auto;
            display: flex;
            flex-direction: column;
            gap: 10px;
        }
        .hero-side-row {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            padding-top: 10px;
            border-top: 1px solid var(--glass-border);
            font-size: 0.82rem;
            color: var(--grey);
        }
        .hero-side-row strong {
            color: var(--white);
            font-size: 0.88rem;
        }
        .workspace-grid {
            display: block;
            gap: 24px;
            align-items: start;
        }
        .panel-card {
            background: var(--dark);
            border: 1px solid var(--glass-border);
            border-radius: 24px;
            padding: 24px;
        }
        .section-head { display: flex; justify-content: space-between; align-items: center; gap: 12px; margin-bottom: 18px; }
        .section-title {
            margin: 0;
            display: flex;
            align-items: center;
            gap: 10px;
            font-size: 1.05rem;
            font-weight: 800;
        }
        .section-copy { color: var(--grey); font-size: 0.84rem; line-height: 1.7; margin: 0 0 22px; }
        .section-badge {
            padding: 7px 11px;
            border-radius: 999px;
            border: 1px solid var(--glass-border);
            background: rgba(255,255,255,0.03);
            color: var(--grey);
            font-size: 0.72rem;
            font-weight: 800;
            letter-spacing: 0.6px;
            text-transform: uppercase;
        }
        .compose-card { padding-bottom: 22px; }
        .compose-form { display: flex; flex-direction: column; gap: 18px; }
        .form-section {
            border: 1px solid var(--glass-border);
            border-radius: 16px;
            padding: 16px;
            background: rgba(255,255,255,0.02);
        }
        .form-section-head {
            display: flex;
            flex-wrap: wrap;
            gap: 8px 14px;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 14px;
        }
        .form-section-title {
            font-size: 0.92rem;
            font-weight: 800;
            color: var(--white);
        }
        .form-section-copy {
            font-size: 0.78rem;
            color: var(--grey);
            line-height: 1.5;
        }
        .form-grid { display: flex; flex-direction: column; gap: 14px; }
        .form-group { display: flex; flex-direction: column; gap: 8px; }
        .form-group label { font-size: 0.72rem; color: var(--grey); font-weight: 800; text-transform: uppercase; letter-spacing: 0.8px; }
        .form-group input, .form-group textarea { width: 100%; background: rgba(255,255,255,0.03); border: 1px solid var(--glass-border); color: var(--white); padding: 12px 14px; border-radius: 14px; font-size: 0.9rem; outline: none; }
        .form-group textarea { min-height: 150px; resize: vertical; }
        .form-group input:focus, .form-group textarea:focus { border-color: var(--red); box-shadow: 0 0 0 3px rgba(229,9,20,0.08); }
        .file-hint { color: var(--grey); font-size: 0.76rem; line-height: 1.5; }
        .cover-preview { width: 100%; max-width: 220px; height: 120px; object-fit: cover; border-radius: 12px; border: 1px solid var(--glass-border); margin-top: 10px; background: rgba(255,255,255,0.03); }
        .file-input-native { position: absolute; width: 1px; height: 1px; opacity: 0; pointer-events: none; }
        .upload-card {
            display: flex;
            align-items: center;
            gap: 14px;
            padding: 16px;
            border: 1px dashed rgba(229,9,20,0.35);
            border-radius: 16px;
            background: linear-gradient(135deg, rgba(229,9,20,0.08), rgba(255,255,255,0.02));
            cursor: pointer;
            transition: all 0.25s ease;
            text-transform: none;
            letter-spacing: normal;
            font-weight: 400;
        }
        .upload-card:hover {
            border-color: var(--red);
            background: linear-gradient(135deg, rgba(229,9,20,0.12), rgba(255,255,255,0.04));
            transform: translateY(-1px);
        }
        .upload-card:focus-within {
            border-color: var(--red);
            box-shadow: 0 0 0 3px rgba(229,9,20,0.12);
        }
        .upload-icon {
            width: 46px;
            height: 46px;
            border-radius: 14px;
            background: rgba(229,9,20,0.14);
            color: var(--red);
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }
        .upload-copy { min-width: 0; flex: 1; }
        .upload-title { display: block; font-size: 0.9rem; font-weight: 800; color: var(--white); margin-bottom: 4px; }
        .upload-subtitle { display: block; font-size: 0.78rem; color: var(--grey); line-height: 1.5; }
        .upload-badge {
            padding: 7px 12px;
            border-radius: 999px;
            background: rgba(229,9,20,0.12);
            color: var(--red);
            font-size: 0.72rem;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0.6px;
            white-space: nowrap;
        }
        .file-selected { margin-top: 10px; font-size: 0.78rem; color: var(--grey); }
        .file-selected strong { color: var(--white); }
        .editor-source { display: none; }
        .editor-block { display: flex; flex-direction: column; gap: 12px; }
        .editor-shell {
            border: 1px solid var(--glass-border);
            border-radius: 18px;
            overflow: hidden;
            background: rgba(6, 8, 15, 0.7);
        }
        .editor-shell .ck.ck-toolbar {
            background: rgba(255,255,255,0.04);
            border: none;
            border-bottom: 1px solid var(--glass-border);
        }
        .editor-shell .ck.ck-toolbar .ck-button,
        .editor-shell .ck.ck-toolbar .ck-toolbar__separator,
        .editor-shell .ck.ck-toolbar .ck-dropdown__button {
            color: var(--white);
        }
        .editor-shell .ck.ck-editor__main > .ck-editor__editable {
            min-height: 320px;
            background: transparent;
            color: var(--white);
            border: none;
            box-shadow: none !important;
        }
        .editor-shell .ck.ck-editor__main > .ck-editor__editable.ck-focused {
            border: none;
        }
        .editor-shell .ck-content {
            font-family: 'Inter', sans-serif;
            font-size: 0.96rem;
            line-height: 1.8;
        }
        .editor-shell .ck-content a { color: #7dd3fc; }
        .editor-shell .ck-content figure.image img,
        .editor-shell .ck-content img {
            max-width: 100%;
            border-radius: 14px;
        }
        .editor-shell .ck-content iframe,
        .editor-shell .ck-content video {
            width: 100%;
            min-height: 320px;
            border: none;
            border-radius: 16px;
        }
        .editor-shell .ck-content audio { width: 100%; }
        .editor-tools { display: flex; flex-direction: column; gap: 14px; }
        .media-tool-card {
            padding: 16px;
            border: 1px solid var(--glass-border);
            border-radius: 16px;
            background: rgba(255,255,255,0.03);
        }
        .media-tool-title { color: var(--white); font-size: 0.88rem; font-weight: 800; margin-bottom: 6px; }
        .media-tool-copy { color: var(--grey); font-size: 0.78rem; line-height: 1.6; margin-bottom: 12px; }
        .media-tool-row { display: flex; gap: 10px; align-items: center; }
        .media-tool-row input[type="url"] {
            flex: 1;
            width: auto;
        }
        .media-tool-row button {
            border: none;
            flex-shrink: 0;
        }
        .editor-note {
            color: var(--grey);
            font-size: 0.76rem;
            line-height: 1.6;
            padding: 12px 14px;
            border: 1px solid rgba(125, 211, 252, 0.16);
            border-radius: 14px;
            background: rgba(125, 211, 252, 0.05);
        }
        .checkbox-row { display: flex; align-items: center; gap: 10px; margin-top: 14px; color: var(--grey); font-size: 0.88rem; }
        .checkbox-row input { width: auto; }
        .btn-row { display: flex; gap: 10px; flex-wrap: wrap; margin-top: 18px; }
        .compose-submit-row {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 14px;
            flex-wrap: wrap;
            margin-top: 2px;
        }
        .compose-submit-note {
            color: var(--grey);
            font-size: 0.8rem;
            line-height: 1.55;
            max-width: 430px;
        }
        .btn-soft { padding: 11px 16px; border-radius: 12px; border: 1px solid var(--glass-border); background: rgba(255,255,255,0.03); color: var(--white); font-size: 0.82rem; font-weight: 700; cursor: pointer; }
        .flash-success { background: rgba(34,197,94,0.12); border: 1px solid rgba(34,197,94,0.3); color: #22c55e; padding: 12px 18px; border-radius: 10px; margin-bottom: 20px; display: flex; align-items: center; gap: 10px; font-size: 0.85rem; font-weight: 600; }
        .flash-error { background: rgba(229,9,20,0.1); border: 1px solid rgba(229,9,20,0.3); color: var(--red); padding: 12px 18px; border-radius: 10px; margin-bottom: 20px; }
        .post-list { display: flex; flex-direction: column; gap: 18px; }
        .post-card { background: var(--dark); border: 1px solid var(--glass-border); border-radius: 20px; overflow: hidden; }
        .post-card[open] { box-shadow: 0 18px 40px rgba(0,0,0,0.18); }
        .post-card summary { list-style: none; }
        .post-card summary::-webkit-details-marker { display: none; }
        .post-summary {
            display: flex;
            justify-content: space-between;
            gap: 16px;
            align-items: center;
            padding: 18px 20px;
            cursor: pointer;
        }
        .post-summary:hover { background: rgba(255,255,255,0.02); }
        .post-summary-main { min-width: 0; }
        .post-title-row {
            display: flex;
            align-items: center;
            gap: 10px;
            flex-wrap: wrap;
            margin-bottom: 8px;
        }
        .post-title-row h4 {
            margin: 0;
            font-size: 1rem;
            font-weight: 800;
            line-height: 1.3;
        }
        .post-meta { color: var(--grey); font-size: 0.78rem; display: flex; gap: 12px; flex-wrap: wrap; margin-top: 6px; }
        .post-excerpt { color: var(--grey); font-size: 0.84rem; line-height: 1.6; margin-top: 10px; max-width: 820px; }
        .post-summary-side {
            display: flex;
            align-items: center;
            gap: 12px;
            flex-shrink: 0;
        }
        .post-toggle {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 10px 14px;
            border-radius: 12px;
            border: 1px solid var(--glass-border);
            color: var(--white);
            font-size: 0.8rem;
            font-weight: 700;
            background: rgba(255,255,255,0.03);
        }
        .post-card[open] .post-toggle-text::before { content: 'Close'; }
        .post-toggle-text::before { content: 'Edit'; }
        .post-card[open] .post-toggle i { transform: rotate(180deg); }
        .post-toggle i { transition: transform 0.2s ease; }
        .post-status { padding: 5px 10px; border-radius: 999px; font-size: 0.72rem; font-weight: 800; text-transform: uppercase; white-space: nowrap; }
        .post-status.published { background: rgba(34,197,94,0.12); color: #22c55e; }
        .post-status.draft { background: rgba(245,158,11,0.12); color: #f59e0b; }
        .post-body { padding: 0 20px 20px; border-top: 1px solid var(--glass-border); }
        .post-edit-shell { padding-top: 20px; }
        .post-actions { display: flex; gap: 10px; align-items: center; }
        .danger-btn { background: rgba(229,9,20,0.1); border: 1px solid rgba(229,9,20,0.3); color: var(--red); padding: 11px 14px; border-radius: 10px; cursor: pointer; font-weight: 700; }
        .theme-toggle { background: transparent; border: none; color: var(--grey); cursor: pointer; padding: 8px; border-radius: 50%; transition: all 0.3s; display: flex; align-items: center; justify-content: center; }
        .theme-toggle:hover { background: var(--glass); color: var(--white); }
        .manage-card {
            background:
                linear-gradient(180deg, rgba(255,255,255,0.02), rgba(255,255,255,0.01)),
                var(--dark);
            margin-top: 24px;
        }
        .posts-toolbar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 12px;
            flex-wrap: wrap;
            margin-bottom: 18px;
        }
        .posts-count {
            padding: 8px 12px;
            border-radius: 999px;
            background: rgba(255,255,255,0.04);
            border: 1px solid var(--glass-border);
            color: var(--grey);
            font-size: 0.78rem;
            font-weight: 700;
        }
        :root.light-mode .editor-shell { background: #ffffff; border-color: rgba(15, 23, 42, 0.1); }
        :root.light-mode .editor-shell .ck.ck-toolbar { background: #f8fafc; border-bottom-color: rgba(15, 23, 42, 0.08); }
        :root.light-mode .editor-shell .ck.ck-toolbar .ck-button,
        :root.light-mode .editor-shell .ck.ck-toolbar .ck-toolbar__separator,
        :root.light-mode .editor-shell .ck.ck-toolbar .ck-dropdown__button,
        :root.light-mode .editor-shell .ck-content,
        :root.light-mode .editor-shell .ck-content * { color: #0f172a; }
        :root.light-mode .editor-shell .ck-content a { color: #dc2626; }
        :root.light-mode .media-tool-card { background: #ffffff; border-color: rgba(15, 23, 42, 0.08); }
        :root.light-mode .media-tool-title,
        :root.light-mode .file-selected strong { color: #0f172a; }
        :root.light-mode .editor-note { background: rgba(37, 99, 235, 0.05); border-color: rgba(37, 99, 235, 0.12); }
        :root.light-mode .page-hero,
        :root.light-mode .page-hero-side,
        :root.light-mode .panel-card,
        :root.light-mode .post-card,
        :root.light-mode .posts-count { background: #ffffff; border-color: rgba(15, 23, 42, 0.08); }
        :root.light-mode .form-section { background: #ffffff; border-color: rgba(15, 23, 42, 0.08); }
        :root.light-mode .page-meta-pill,
        :root.light-mode .section-badge,
        :root.light-mode .hero-side-row { border-color: rgba(15, 23, 42, 0.08); }
        :root.light-mode .form-group input,
        :root.light-mode .form-group textarea,
        :root.light-mode .btn-soft,
        :root.light-mode .post-toggle { background: #ffffff; }
        @media (max-width: 1180px) { .page-hero { padding: 20px; } }
        @media (max-width: 1024px) { .page-hero-actions { justify-content: flex-start; } }
        @media (max-width: 768px) { .post-summary { flex-direction: column; align-items: flex-start; } .post-summary-side { width: 100%; justify-content: space-between; } .media-tool-row { flex-direction: column; align-items: stretch; } .editor-shell .ck.ck-editor__main > .ck-editor__editable, .editor-shell .ck-content iframe, .editor-shell .ck-content video { min-height: 240px; } }
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
        <a href="{{ route('admin.analytics') }}" class="nav-item"><i data-lucide="bar-chart-3"></i> System Analytics</a>
        <a href="{{ route('admin.blog.index') }}" class="nav-item active"><i data-lucide="newspaper"></i> Blog & News</a>
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
            <h1 style="font-size: 1.4rem; font-weight: 800;">Blog & News</h1>
            <p style="color: var(--grey); font-size: 0.85rem;">Publish updates, announcements, and emergency awareness content</p>
        </div>
        <div class="topbar-actions">
            @include('partials.lang-switcher')
            <a href="{{ route('blog.index') }}" class="btn-primary" style="padding: 9px 18px; font-size: 0.8rem; display: flex; align-items: center; gap: 8px; text-decoration: none;">
                <i data-lucide="external-link" style="width: 16px; height: 16px;"></i>
                VIEW BLOG
            </a>
            <form action="{{ route('logout') }}" method="POST" class="topbar-logout-form">
                @csrf
                <button type="submit" class="topbar-logout">
                    <i data-lucide="log-out" style="width:16px;height:16px;"></i>
                    Logout
                </button>
            </form>
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

    @if(session('success'))
        <div class="flash-success"><i data-lucide="check-circle" style="width:16px;height:16px;flex-shrink:0;"></i> {{ session('success') }}</div>
    @endif
    @if($errors->any())
        <div class="flash-error">
            {{ $errors->first() }}
        </div>
    @endif

    <div class="page-shell">
        <section class="page-hero">
            <div class="page-hero-main">
                <div class="page-kicker"><i data-lucide="newspaper" style="width:14px;height:14px;"></i> Publishing Workspace</div>
                <h2>Write faster, publish cleanly, and manage your public updates from one professional workspace.</h2>
                <p>Create blog posts, upload media, keep drafts organized, and update live news without the page feeling crowded or hard to control.</p>
                <div class="page-meta-inline">
                    <span class="page-meta-pill"><strong>{{ $stats['total'] }}</strong> total posts</span>
                    <span class="page-meta-pill"><strong>{{ $stats['published'] }}</strong> live</span>
                    <span class="page-meta-pill"><strong>{{ $stats['drafts'] }}</strong> drafts</span>
                </div>
                <div class="page-hero-actions">
                    <a href="{{ route('blog.index') }}" class="btn-primary" style="padding: 11px 18px; display: inline-flex; align-items: center; gap: 8px; text-decoration: none;">
                        <i data-lucide="external-link" style="width:16px;height:16px;"></i>
                        Open Public Blog
                    </a>
                </div>
            </div>
            <div class="page-hero-side">
                <div class="hero-side-label">Publishing overview</div>
                <div class="hero-side-value">{{ $stats['published'] }}</div>
                <div class="hero-side-copy">Posts are currently live on the public blog and visible to visitors.</div>
                <div class="hero-side-list">
                    <div class="hero-side-row">
                        <span>Draft queue</span>
                        <strong>{{ $stats['drafts'] }}</strong>
                    </div>
                    <div class="hero-side-row">
                        <span>Created this month</span>
                        <strong>{{ $stats['this_month'] }}</strong>
                    </div>
                    <div class="hero-side-row">
                        <span>Editing style</span>
                        <strong>Rich editor</strong>
                    </div>
                </div>
            </div>
        </section>

        <div class="workspace-grid">
            <section class="panel-card compose-card">
                <div class="section-head">
                    <div>
                        <h3 class="section-title"><i data-lucide="square-pen"></i> Create New Post</h3>
                        <p class="section-copy" style="margin:8px 0 0;">Build a clean post with headline, cover image, rich content, and publishing state in one flow.</p>
                    </div>
                    <span class="section-badge">Composer</span>
                </div>
            <form action="{{ route('admin.blog.store') }}" method="POST" enctype="multipart/form-data" class="compose-form">
                @csrf
                <div class="form-section">
                    <div class="form-section-head">
                        <div class="form-section-title">Post Details</div>
                        <div class="form-section-copy">Add the title, slug, and short excerpt used across the public blog listing.</div>
                    </div>
                    <div class="form-grid">
                        <div class="form-group">
                            <label>Title</label>
                            <input type="text" name="title" value="{{ old('title') }}" required>
                        </div>
                        <div class="form-group">
                            <label>Slug</label>
                            <input type="text" name="slug" value="{{ old('slug') }}" placeholder="optional-auto-generated">
                        </div>
                        <div class="form-group" style="grid-column: 1 / -1;">
                            <label>Excerpt</label>
                            <textarea name="excerpt" placeholder="Short summary shown in blog cards">{{ old('excerpt') }}</textarea>
                        </div>
                    </div>
                </div>

                <div class="form-section">
                    <div class="form-section-head">
                        <div class="form-section-title">Cover Media</div>
                        <div class="form-section-copy">Use either a direct image URL or upload a cover image from your device.</div>
                    </div>
                    <div class="form-grid">
                        <div class="form-group" style="grid-column: 1 / -1;">
                            <label>Cover Image URL</label>
                            <input type="url" name="cover_image" value="{{ old('cover_image') }}" placeholder="https://example.com/image.jpg">
                        </div>
                        <div class="form-group" style="grid-column: 1 / -1;">
                            <label>Upload Cover Image</label>
                            <input type="file" name="cover_image_file" id="createCoverImageFile" class="file-input-native" accept=".jpg,.jpeg,.png,.webp,.gif,image/*">
                            <label for="createCoverImageFile" class="upload-card">
                                <span class="upload-icon"><i data-lucide="image-plus" style="width:20px;height:20px;"></i></span>
                                <span class="upload-copy">
                                    <span class="upload-title">Choose a cover image</span>
                                    <span class="upload-subtitle">Select a clean featured image from your device for this blog post.</span>
                                </span>
                                <span class="upload-badge">Browse</span>
                            </label>
                            <div class="file-selected" id="createCoverImageFileName">No file selected</div>
                            <div class="file-hint">Supported: JPG, PNG, WEBP, GIF. Max 5MB. Uploaded image overrides the URL field.</div>
                        </div>
                    </div>
                </div>

                <div class="form-section">
                    <div class="form-section-head">
                        <div class="form-section-title">Article Content</div>
                        <div class="form-section-copy">Write in the editor below, paste images directly, or upload and insert media into the post.</div>
                    </div>
                    <div class="form-grid">
                        <div class="form-group" style="grid-column: 1 / -1;">
                            <label>Content</label>
                            <div class="editor-block">
                                <textarea name="content" id="contentCreate" class="editor-source" data-editor-id="blogEditorCreate" required>{{ old('content') }}</textarea>
                                <div id="blogEditorCreate" class="editor-shell"></div>
                                <div class="editor-tools">
                                    <div class="media-tool-card">
                                        <div class="media-tool-title">Upload Media Into Content</div>
                                        <div class="media-tool-copy">Paste screenshots directly into the editor, or upload images, videos, audio, PDFs, and office files and insert them where your cursor is.</div>
                                        <input type="file" id="contentMediaFileCreate" class="file-input-native" accept="image/*,video/*,audio/*,.pdf,.doc,.docx,.xls,.xlsx,.ppt,.pptx,.txt,.zip">
                                        <label for="contentMediaFileCreate" class="upload-card">
                                            <span class="upload-icon"><i data-lucide="files" style="width:20px;height:20px;"></i></span>
                                            <span class="upload-copy">
                                                <span class="upload-title">Upload and insert media</span>
                                                <span class="upload-subtitle">The uploaded file is inserted into the content editor automatically.</span>
                                            </span>
                                            <span class="upload-badge">Insert</span>
                                        </label>
                                        <div class="file-selected" id="contentMediaFileCreateName">No file selected</div>
                                    </div>
                                    <div class="media-tool-card">
                                        <div class="media-tool-title">Insert Media URL</div>
                                        <div class="media-tool-copy">Paste an image link, video link, audio file, document URL, YouTube link, or Vimeo link and insert it directly into the article.</div>
                                        <div class="media-tool-row">
                                            <input type="url" id="contentMediaUrlCreate" placeholder="https://example.com/image.jpg or YouTube link">
                                            <button type="button" class="btn-soft insert-media-url" data-editor-ref="blogEditorCreate" data-url-input="contentMediaUrlCreate">Insert URL</button>
                                        </div>
                                    </div>
                                </div>
                                <div class="editor-note">Supports professional rich content: headings, links, tables, pasted images, uploaded media, and external media URLs. Pasted images upload automatically.</div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="form-section">
                    <div class="form-section-head">
                        <div class="form-section-title">Publishing</div>
                        <div class="form-section-copy">Choose whether this post should go live now or remain in draft while you continue editing.</div>
                    </div>
                    <div class="compose-submit-row">
                        <div>
                            <label class="checkbox-row" style="margin-top:0;">
                                <input type="checkbox" name="is_published" value="1" {{ old('is_published') ? 'checked' : '' }}>
                                Publish immediately
                            </label>
                            <div class="compose-submit-note">Leave this unchecked if you want the post saved as a draft for later review.</div>
                        </div>
                        <div class="btn-row" style="margin-top:0;">
                            <button type="submit" class="btn-primary" style="border:none;">Create Post</button>
                        </div>
                    </div>
                </div>
            </form>
            </section>

        </div>

        <section class="panel-card manage-card">
            <div class="posts-toolbar">
                <div>
                    <h3 class="section-title" style="margin:0;"><i data-lucide="files"></i> Manage Posts</h3>
                    <p class="section-copy" style="margin:8px 0 0;">Review published posts, open only the one you want to edit, and keep the publishing workspace tidy.</p>
                </div>
                <span class="posts-count">{{ $posts->total() }} post(s)</span>
            </div>

            @if($posts->count())
                <div class="post-list">
                    @foreach($posts as $post)
                        <details class="post-card">
                            <summary class="post-summary">
                                <div class="post-summary-main">
                                    <div class="post-title-row">
                                        <h4>{{ $post->title }}</h4>
                                        <span class="post-status {{ $post->is_published ? 'published' : 'draft' }}">
                                            {{ $post->is_published ? 'Published' : 'Draft' }}
                                        </span>
                                    </div>
                                    <div class="post-meta">
                                        <span>Slug: {{ $post->slug }}</span>
                                        <span>Author: {{ $post->author->name ?? 'Admin' }}</span>
                                        <span>Updated: {{ $post->updated_at->format('M d, Y H:i') }}</span>
                                    </div>
                                    <div class="post-excerpt">{{ $post->excerpt ?: \Illuminate\Support\Str::limit(trim(strip_tags($post->content)), 160) }}</div>
                                </div>
                                <div class="post-summary-side">
                                    <span class="post-toggle">
                                        <span class="post-toggle-text"></span>
                                        <i data-lucide="chevron-down" style="width:16px;height:16px;"></i>
                                    </span>
                                </div>
                            </summary>
                            <div class="post-body">
                                <div class="post-edit-shell">
                                    <form action="{{ route('admin.blog.update', $post) }}" method="POST" enctype="multipart/form-data">
                                        @csrf
                                        @method('PUT')
                                        <div class="form-grid">
                                            <div class="form-group">
                                                <label>Title</label>
                                                <input type="text" name="title" value="{{ $post->title }}" required>
                                            </div>
                                            <div class="form-group">
                                                <label>Slug</label>
                                                <input type="text" name="slug" value="{{ $post->slug }}">
                                            </div>
                                            <div class="form-group" style="grid-column: 1 / -1;">
                                                <label>Excerpt</label>
                                                <textarea name="excerpt">{{ $post->excerpt }}</textarea>
                                            </div>
                                            <div class="form-group" style="grid-column: 1 / -1;">
                                                <label>Cover Image URL</label>
                                                <input type="url" name="cover_image" value="{{ $post->cover_image }}">
                                                @if($post->cover_image)
                                                    <img src="{{ $post->cover_image }}" alt="{{ $post->title }}" class="cover-preview">
                                                @endif
                                            </div>
                                            <div class="form-group" style="grid-column: 1 / -1;">
                                                <label>Upload New Cover Image</label>
                                                <input type="file" name="cover_image_file" id="coverImageFile{{ $post->id }}" class="file-input-native" accept=".jpg,.jpeg,.png,.webp,.gif,image/*">
                                                <label for="coverImageFile{{ $post->id }}" class="upload-card">
                                                    <span class="upload-icon"><i data-lucide="upload" style="width:20px;height:20px;"></i></span>
                                                    <span class="upload-copy">
                                                        <span class="upload-title">Replace cover image</span>
                                                        <span class="upload-subtitle">Choose a new image to refresh the look of this published or draft post.</span>
                                                    </span>
                                                    <span class="upload-badge">Upload</span>
                                                </label>
                                                <div class="file-selected" id="coverImageFileName{{ $post->id }}">No new file selected</div>
                                                <div class="file-hint">Choose a file from your device to replace the current cover image. Uploaded image overrides the URL field.</div>
                                            </div>
                                            <div class="form-group" style="grid-column: 1 / -1;">
                                                <label>Content</label>
                                                <div class="editor-block">
                                                    <textarea name="content" id="content{{ $post->id }}" class="editor-source" data-editor-id="blogEditor{{ $post->id }}" required>{{ $post->content }}</textarea>
                                                    <div id="blogEditor{{ $post->id }}" class="editor-shell"></div>
                                                    <div class="editor-tools">
                                                        <div class="media-tool-card">
                                                            <div class="media-tool-title">Upload Media Into Content</div>
                                                            <div class="media-tool-copy">Add screenshots, flyers, videos, audio clips, or document files directly inside this post content.</div>
                                                            <input type="file" id="contentMediaFile{{ $post->id }}" class="file-input-native" accept="image/*,video/*,audio/*,.pdf,.doc,.docx,.xls,.xlsx,.ppt,.pptx,.txt,.zip">
                                                            <label for="contentMediaFile{{ $post->id }}" class="upload-card">
                                                                <span class="upload-icon"><i data-lucide="files" style="width:20px;height:20px;"></i></span>
                                                                <span class="upload-copy">
                                                                    <span class="upload-title">Upload and insert media</span>
                                                                    <span class="upload-subtitle">The uploaded file is placed into the editor at the current cursor position.</span>
                                                                </span>
                                                                <span class="upload-badge">Insert</span>
                                                            </label>
                                                            <div class="file-selected" id="contentMediaFileName{{ $post->id }}">No file selected</div>
                                                        </div>
                                                        <div class="media-tool-card">
                                                            <div class="media-tool-title">Insert Media URL</div>
                                                            <div class="media-tool-copy">Paste an image or media link and insert it directly into the article body.</div>
                                                            <div class="media-tool-row">
                                                                <input type="url" id="contentMediaUrl{{ $post->id }}" placeholder="https://example.com/file or embed link">
                                                                <button type="button" class="btn-soft insert-media-url" data-editor-ref="blogEditor{{ $post->id }}" data-url-input="contentMediaUrl{{ $post->id }}">Insert URL</button>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="editor-note">Images from clipboard and drag/drop are supported here too. Remote media links are inserted without removing your formatting.</div>
                                                </div>
                                            </div>
                                        </div>
                                        <label class="checkbox-row">
                                            <input type="checkbox" name="is_published" value="1" {{ $post->is_published ? 'checked' : '' }}>
                                            Published
                                        </label>
                                        <div class="btn-row">
                                            <button type="submit" class="btn-primary" style="border:none;">Save Changes</button>
                                            @if($post->is_published)
                                                <a href="{{ route('blog.show', $post) }}" class="btn-soft" style="text-decoration:none;display:inline-flex;align-items:center;">Open Live Post</a>
                                            @else
                                                <span class="btn-soft" style="display:inline-flex;align-items:center;opacity:0.65;">Draft Preview Unavailable</span>
                                            @endif
                                        </div>
                                    </form>
                                    <form action="{{ route('admin.blog.destroy', $post) }}" method="POST" style="margin-top:12px;" onsubmit="return confirm('Delete this post permanently?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="danger-btn">Delete Post</button>
                                    </form>
                                </div>
                            </div>
                        </details>
                    @endforeach
                </div>

                <div style="margin-top: 24px;">
                    {{ $posts->links() }}
                </div>
            @else
                <div style="text-align:center;padding:40px 20px;color:var(--grey); border: 1px dashed var(--glass-border); border-radius: 18px;">
                    <i data-lucide="newspaper" style="width:40px;height:40px;opacity:0.5;margin-bottom:12px;"></i>
                    <p style="margin:0;">No blog posts yet. Create your first post above.</p>
                </div>
            @endif
        </section>
    </div>
</main>

<script>
    lucide.createIcons();
    const BLOG_MEDIA_UPLOAD_URL = '{{ route('admin.blog.media-upload') }}';
    const BLOG_CSRF = document.querySelector('meta[name="csrf-token"]')?.content || '{{ csrf_token() }}';
    const blogEditors = {};

    class BlogUploadAdapter {
        constructor(loader) {
            this.loader = loader;
        }

        async upload() {
            const file = await this.loader.file;
            const result = await uploadBlogMedia(file);

            if (result.media_type !== 'image') {
                throw new Error('Only image files can be pasted directly. Use the media uploader for other files.');
            }

            return { default: result.url };
        }

        abort() {}
    }

    function BlogUploadAdapterPlugin(editor) {
        editor.plugins.get('FileRepository').createUploadAdapter = (loader) => new BlogUploadAdapter(loader);
    }

    function createEditorConfig() {
        return {
            extraPlugins: [BlogUploadAdapterPlugin],
            toolbar: {
                items: [
                    'heading', '|',
                    'bold', 'italic', 'underline', 'strikethrough', 'link', '|',
                    'bulletedList', 'numberedList', 'outdent', 'indent', '|',
                    'alignment', 'blockQuote', 'insertTable', 'mediaEmbed', '|',
                    'imageUpload', 'undo', 'redo'
                ],
                shouldNotGroupWhenFull: true
            },
            heading: {
                options: [
                    { model: 'paragraph', title: 'Paragraph', class: 'ck-heading_paragraph' },
                    { model: 'heading2', view: 'h2', title: 'Heading 2', class: 'ck-heading_heading2' },
                    { model: 'heading3', view: 'h3', title: 'Heading 3', class: 'ck-heading_heading3' },
                    { model: 'heading4', view: 'h4', title: 'Heading 4', class: 'ck-heading_heading4' }
                ]
            },
            image: {
                toolbar: [
                    'imageTextAlternative', '|',
                    'imageStyle:inline', 'imageStyle:block', 'imageStyle:side'
                ]
            },
            mediaEmbed: {
                previewsInData: true
            },
            table: {
                contentToolbar: ['tableColumn', 'tableRow', 'mergeTableCells']
            },
            removePlugins: [
                'AIAssistant',
                'CKBox',
                'CKFinder',
                'EasyImage',
                'RealTimeCollaborativeComments',
                'RealTimeCollaborativeTrackChanges',
                'RealTimeCollaborativeRevisionHistory',
                'PresenceList',
                'Comments',
                'TrackChanges',
                'TrackChangesData',
                'RevisionHistory',
                'Pagination',
                'WProofreader',
                'MathType',
                'SlashCommand',
                'Template',
                'DocumentOutline',
                'FormatPainter',
                'TableOfContents'
            ]
        };
    }

    function escapeHtml(value) {
        return String(value).replace(/[&<>"']/g, function (char) {
            return ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' })[char];
        });
    }

    function insertHtml(editor, html) {
        const viewFragment = editor.data.processor.toView(html);
        const modelFragment = editor.data.toModel(viewFragment);
        editor.model.insertContent(modelFragment, editor.model.document.selection);
    }

    function detectMediaTypeFromUrl(url) {
        const cleanUrl = url.split('?')[0].toLowerCase();
        if (/\.(jpg|jpeg|png|gif|webp|bmp|avif)$/i.test(cleanUrl)) return 'image';
        if (/\.(mp4|mov|webm|m4v)$/i.test(cleanUrl)) return 'video';
        if (/\.(mp3|wav|ogg|m4a)$/i.test(cleanUrl)) return 'audio';
        if (/youtube\.com\/watch\?v=|youtu\.be\/|vimeo\.com\//i.test(url)) return 'embed';
        return 'file';
    }

    function toEmbedUrl(url) {
        const youtubeMatch = url.match(/(?:youtube\.com\/watch\?v=|youtu\.be\/)([^&?/]+)/i);
        if (youtubeMatch) {
            return 'https://www.youtube.com/embed/' + youtubeMatch[1];
        }

        const vimeoMatch = url.match(/vimeo\.com\/(\d+)/i);
        if (vimeoMatch) {
            return 'https://player.vimeo.com/video/' + vimeoMatch[1];
        }

        return url;
    }

    function buildMediaHtml(url, mediaType, fileName) {
        const safeUrl = escapeHtml(url);
        const safeName = escapeHtml(fileName || 'Media file');

        if (mediaType === 'image') {
            return '<figure class="image"><img src="' + safeUrl + '" alt="' + safeName + '"></figure>';
        }

        if (mediaType === 'video') {
            return '<figure class="media"><video controls preload="metadata"><source src="' + safeUrl + '"></video></figure>';
        }

        if (mediaType === 'audio') {
            return '<p><audio controls preload="metadata" src="' + safeUrl + '"></audio></p>';
        }

        if (mediaType === 'embed') {
            return '<figure class="media"><iframe src="' + escapeHtml(toEmbedUrl(url)) + '" title="' + safeName + '" loading="lazy" referrerpolicy="strict-origin-when-cross-origin" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe></figure>';
        }

        return '<p><a href="' + safeUrl + '" target="_blank" rel="noopener noreferrer">' + safeName + '</a></p>';
    }

    async function uploadBlogMedia(file) {
        const formData = new FormData();
        formData.append('upload', file);

        const response = await fetch(BLOG_MEDIA_UPLOAD_URL, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': BLOG_CSRF,
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: formData
        });

        const data = await response.json();

        if (!response.ok) {
            const message = data.message || 'Media upload failed.';
            throw new Error(message);
        }

        return data;
    }

    async function initializeBlogEditors() {
        const sources = document.querySelectorAll('.editor-source');

        for (const source of sources) {
            const editorId = source.dataset.editorId;
            const host = document.getElementById(editorId);

            if (!host || blogEditors[editorId]) {
                continue;
            }

            host.innerHTML = source.value;

            const editor = await CKEDITOR.ClassicEditor.create(host, createEditorConfig());
            blogEditors[editorId] = editor;
            source.value = editor.getData();

            editor.model.document.on('change:data', () => {
                source.value = editor.getData();
            });

            source.closest('form')?.addEventListener('submit', () => {
                source.value = editor.getData();
            });
        }
    }

    function bindFileName(inputId, outputId, emptyText) {
        const input = document.getElementById(inputId);
        const output = document.getElementById(outputId);

        if (!input || !output) {
            return;
        }

        input.addEventListener('change', () => {
            if (input.files && input.files.length > 0) {
                output.innerHTML = 'Selected file: <strong>' + input.files[0].name + '</strong>';
            } else {
                output.textContent = emptyText;
            }
        });
    }

    function bindContentMediaUpload(inputId, outputId, editorId) {
        const input = document.getElementById(inputId);
        const output = document.getElementById(outputId);

        if (!input || !output) {
            return;
        }

        input.addEventListener('change', async () => {
            if (!input.files || input.files.length === 0) {
                output.textContent = 'No file selected';
                return;
            }

            const file = input.files[0];
            output.innerHTML = 'Uploading: <strong>' + escapeHtml(file.name) + '</strong>';

            try {
                const result = await uploadBlogMedia(file);
                const editor = blogEditors[editorId];

                if (editor) {
                    insertHtml(editor, buildMediaHtml(result.url, result.media_type, result.file_name));
                }

                output.innerHTML = 'Inserted: <strong>' + escapeHtml(result.file_name || file.name) + '</strong>';
                input.value = '';
            } catch (error) {
                output.textContent = error.message || 'Upload failed';
            }
        });
    }

    function bindInsertMediaUrlButtons() {
        document.querySelectorAll('.insert-media-url').forEach((button) => {
            button.addEventListener('click', () => {
                const editor = blogEditors[button.dataset.editorRef];
                const input = document.getElementById(button.dataset.urlInput);
                const url = input?.value.trim();

                if (!editor || !input || !url) {
                    return;
                }

                insertHtml(editor, buildMediaHtml(url, detectMediaTypeFromUrl(url), url));
                input.value = '';
            });
        });
    }

    bindFileName('createCoverImageFile', 'createCoverImageFileName', 'No file selected');
    bindFileName('contentMediaFileCreate', 'contentMediaFileCreateName', 'No file selected');
    @foreach($posts as $post)
    bindFileName('coverImageFile{{ $post->id }}', 'coverImageFileName{{ $post->id }}', 'No new file selected');
    bindFileName('contentMediaFile{{ $post->id }}', 'contentMediaFileName{{ $post->id }}', 'No file selected');
    @endforeach

    initializeBlogEditors().then(() => {
        bindContentMediaUpload('contentMediaFileCreate', 'contentMediaFileCreateName', 'blogEditorCreate');
        @foreach($posts as $post)
        bindContentMediaUpload('contentMediaFile{{ $post->id }}', 'contentMediaFileName{{ $post->id }}', 'blogEditor{{ $post->id }}');
        @endforeach
        bindInsertMediaUrlButtons();
    }).catch((error) => {
        console.error(error);
    });

    (function () {
        const btn = document.getElementById('hamburgerBtn');
        const sidebar = document.querySelector('.sidebar');
        const overlay = document.getElementById('sidebarOverlay');
        btn.addEventListener('click', () => {
            sidebar.classList.toggle('open');
            overlay.classList.toggle('active');
        });
        overlay.addEventListener('click', () => {
            sidebar.classList.remove('open');
            overlay.classList.remove('active');
        });
    })();
</script>
@include('partials.profile-modal')
</body>
</html>
