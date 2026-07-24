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
        .page-stats-row { display: flex; gap: 10px; flex-wrap: wrap; }
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
        .section-head-icon { display: flex; align-items: flex-start; gap: 14px; }
        .section-subtitle { margin: 4px 0 0; font-size: 0.82rem; color: var(--grey); }
        .icon-box { width: 46px; height: 46px; border-radius: 14px; display: flex; align-items: center; justify-content: center; flex-shrink: 0; }
        .icon-box i { width: 20px; height: 20px; }
        .icon-box-sm { width: 34px; height: 34px; border-radius: 10px; }
        .icon-box-sm i { width: 16px; height: 16px; }
        .icon-box-red { background: rgba(229,9,20,0.12); color: var(--red); }
        .icon-box-green { background: rgba(34,197,94,0.12); color: #22c55e; }
        .icon-box-purple { background: rgba(139,92,246,0.12); color: #8b5cf6; }
        .icon-box-blue { background: rgba(59,130,246,0.12); color: #3b82f6; }
        .icon-box-neutral { background: var(--glass); color: var(--text-muted); }
        .compose-card { padding-bottom: 22px; }
        .compose-form { display: flex; flex-direction: column; gap: 18px; }
        .compose-layout { display: grid; grid-template-columns: 1fr 340px; gap: 20px; align-items: start; }
        .compose-main, .compose-sidebar { display: flex; flex-direction: column; gap: 18px; min-width: 0; }
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
        .form-section-head-icon { display: flex; align-items: center; gap: 12px; margin-bottom: 16px; }
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
        .field-hint { font-size: 0.74rem; color: var(--grey); line-height: 1.5; }
        .req { color: var(--red); }
        .form-grid { display: flex; flex-direction: column; gap: 14px; }
        .form-grid-2 { display: grid; grid-template-columns: 1fr 1fr; gap: 14px; }
        .form-group { display: flex; flex-direction: column; gap: 8px; }
        .form-group label { font-size: 0.72rem; color: var(--grey); font-weight: 800; text-transform: uppercase; letter-spacing: 0.8px; display: flex; align-items: center; gap: 6px; }
        .form-group input, .form-group textarea, .form-group select { width: 100%; background: rgba(255,255,255,0.03); border: 1px solid var(--glass-border); color: var(--white); padding: 12px 14px; border-radius: 14px; font-size: 0.9rem; outline: none; }
        .form-group textarea { min-height: 150px; resize: vertical; }
        .form-group input:focus, .form-group textarea:focus, .form-group select:focus { border-color: var(--red); box-shadow: 0 0 0 3px rgba(229,9,20,0.08); }
        .char-counter { text-align: right; font-size: 0.72rem; color: var(--grey); margin-top: 4px; }
        .char-counter.over-limit { color: #f59e0b; }
        .file-hint { color: var(--grey); font-size: 0.76rem; line-height: 1.5; }
        .cover-preview { width: 100%; max-width: 220px; height: 120px; object-fit: cover; border-radius: 12px; border: 1px solid var(--glass-border); margin-top: 10px; background: rgba(255,255,255,0.03); }
        .file-input-native { position: absolute; width: 1px; height: 1px; opacity: 0; pointer-events: none; }
        .dropzone {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            gap: 6px;
            padding: 28px 16px;
            border: 2px dashed var(--glass-border);
            border-radius: 16px;
            background: rgba(255,255,255,0.02);
            cursor: pointer;
            text-align: center;
            transition: all 0.2s ease;
            color: var(--grey);
        }
        .dropzone:hover, .dropzone.dragover { border-color: var(--red); background: rgba(229,9,20,0.05); }
        .dropzone-title { font-size: 0.84rem; color: var(--white); font-weight: 600; }
        .dropzone-or { font-size: 0.74rem; color: var(--grey); }
        .dropzone-btn { margin-top: 4px; pointer-events: none; }
        .media-upload-row {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 12px 14px;
            border: 1px solid var(--glass-border);
            border-radius: 12px;
            background: rgba(255,255,255,0.02);
            cursor: pointer;
            transition: all 0.2s ease;
        }
        .media-upload-row:hover { border-color: #8b5cf6; background: rgba(139,92,246,0.05); }
        .upload-icon-sm { width: 36px; height: 36px; border-radius: 10px; background: rgba(139,92,246,0.12); color: #8b5cf6; display: flex; align-items: center; justify-content: center; flex-shrink: 0; }
        .upload-icon-sm i { width: 16px; height: 16px; }
        .btn-soft-sm { padding: 7px 12px; border-radius: 8px; border: 1px solid rgba(139,92,246,0.3); background: rgba(139,92,246,0.12); color: #8b5cf6; font-size: 0.76rem; font-weight: 700; white-space: nowrap; cursor: pointer; pointer-events: none; }
        .publish-options { display: flex; flex-direction: column; gap: 10px; }
        .publish-option {
            display: flex;
            align-items: flex-start;
            gap: 12px;
            padding: 12px 14px;
            border: 1px solid var(--glass-border);
            border-radius: 12px;
            cursor: pointer;
            background: rgba(255,255,255,0.02);
            transition: all 0.2s ease;
        }
        .publish-option:has(input:checked), .publish-option.selected { border-color: #22c55e; background: rgba(34,197,94,0.06); }
        .publish-option input[type="radio"] { width: auto; margin-top: 3px; accent-color: #22c55e; }
        .publish-option span { display: flex; flex-direction: column; gap: 2px; }
        .publish-option strong { font-size: 0.86rem; color: var(--white); }
        .publish-option small { font-size: 0.74rem; color: var(--grey); }
        .schedule-datetime-row { margin-top: 2px; padding: 12px 14px; border: 1px solid var(--glass-border); border-radius: 12px; background: rgba(255,255,255,0.02); }
        .schedule-datetime-row label { display: block; font-size: 0.72rem; color: var(--grey); font-weight: 800; text-transform: uppercase; letter-spacing: 0.6px; margin-bottom: 8px; }
        .schedule-datetime-row input { width: 100%; background: rgba(255,255,255,0.03); border: 1px solid var(--glass-border); color: var(--white); padding: 10px 12px; border-radius: 10px; }
        .compose-actions-bar { display: flex; justify-content: flex-end; gap: 12px; margin-top: 4px; padding-top: 20px; border-top: 1px solid var(--glass-border); flex-wrap: wrap; }
        .upload-copy { min-width: 0; flex: 1; }
        .upload-title { display: block; font-size: 0.9rem; font-weight: 800; color: var(--white); margin-bottom: 4px; }
        .upload-subtitle { display: block; font-size: 0.78rem; color: var(--grey); line-height: 1.5; }
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
        .media-tool-row { display: flex; gap: 10px; align-items: center; }
        .media-tool-row input[type="url"] {
            flex: 1;
            width: auto;
        }
        .media-tool-row button {
            border: none;
            flex-shrink: 0;
        }
        .btn-soft { padding: 11px 16px; border-radius: 12px; border: 1px solid var(--glass-border); background: rgba(255,255,255,0.03); color: var(--white); font-size: 0.82rem; font-weight: 700; cursor: pointer; display: inline-flex; align-items: center; gap: 6px; }
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
        .post-status.scheduled { background: rgba(59,130,246,0.12); color: #3b82f6; }
        .post-status.draft { background: var(--glass); color: var(--text-muted); }
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
        :root.light-mode .file-selected strong { color: #0f172a; }
        :root.light-mode .panel-card,
        :root.light-mode .post-card,
        :root.light-mode .posts-count { background: #ffffff; border-color: rgba(15, 23, 42, 0.08); }
        :root.light-mode .form-section { background: #ffffff; border-color: rgba(15, 23, 42, 0.08); }
        :root.light-mode .page-meta-pill { border-color: rgba(15, 23, 42, 0.08); }
        :root.light-mode .form-group input,
        :root.light-mode .form-group textarea,
        :root.light-mode .form-group select,
        :root.light-mode .btn-soft,
        :root.light-mode .post-toggle { background: #ffffff; }
        :root.light-mode .dropzone,
        :root.light-mode .publish-option,
        :root.light-mode .media-upload-row,
        :root.light-mode .schedule-datetime-row { background: #ffffff; border-color: rgba(15, 23, 42, 0.08); }
        :root.light-mode .schedule-datetime-row input { background: #ffffff; }
        @media (max-width: 1100px) { .compose-layout { grid-template-columns: 1fr; } }
        @media (max-width: 768px) { .post-summary { flex-direction: column; align-items: flex-start; } .post-summary-side { width: 100%; justify-content: space-between; } .media-tool-row { flex-direction: column; align-items: stretch; } .form-grid-2 { grid-template-columns: 1fr; } .compose-actions-bar { justify-content: stretch !important; } .compose-actions-bar button, .compose-actions-bar > div { width: 100%; } .editor-shell .ck.ck-editor__main > .ck-editor__editable, .editor-shell .ck-content iframe, .editor-shell .ck-content video { min-height: 240px; } }
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
        <a href="{{ route('admin.blog.index') }}" class="nav-item active"><i data-lucide="newspaper"></i> Blog & News</a>
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
            <h1 style="font-size: 1.4rem; font-weight: 800;">Blog & News</h1>
            <p style="color: var(--grey); font-size: 0.85rem;">Publish updates, announcements, and emergency awareness content</p>
        </div>
        <div class="topbar-actions">
            @include('partials.lang-switcher')
            <a href="{{ route('blog.index') }}" class="btn-primary" style="padding: 9px 18px; font-size: 0.8rem; display: flex; align-items: center; gap: 8px; text-decoration: none;">
                <i data-lucide="external-link" style="width: 16px; height: 16px;"></i>
                VIEW BLOG
            </a>
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
        <div class="page-stats-row">
            <span class="page-meta-pill"><strong>{{ $stats['total'] }}</strong> total</span>
            <span class="page-meta-pill"><strong>{{ $stats['published'] }}</strong> published</span>
            <span class="page-meta-pill"><strong>{{ $stats['scheduled'] }}</strong> scheduled</span>
            <span class="page-meta-pill"><strong>{{ $stats['drafts'] }}</strong> drafts</span>
        </div>

        <section class="panel-card compose-card">
            <div class="section-head-icon" style="margin-bottom: 22px;">
                <span class="icon-box icon-box-red"><i data-lucide="square-pen"></i></span>
                <div>
                    <h3 class="section-title" style="margin: 0;">Create New Post</h3>
                    <p class="section-subtitle">Share important updates with your audience.</p>
                </div>
            </div>
            <form action="{{ route('admin.blog.store') }}" method="POST" enctype="multipart/form-data" class="compose-form" id="createPostForm">
                @csrf
                <div class="compose-layout">
                    <div class="compose-main">
                        <div class="form-section">
                            <div class="form-section-head-icon">
                                <span class="icon-box icon-box-red icon-box-sm"><i data-lucide="file-text"></i></span>
                                <div class="form-section-title">Post Information</div>
                            </div>
                            <div class="form-grid-2">
                                <div class="form-group">
                                    <label>Post Title <span class="req">*</span></label>
                                    <input type="text" name="title" value="{{ old('title') }}" required placeholder="Enter an engaging title for your post">
                                </div>
                                <div class="form-group">
                                    <label><i data-lucide="info" style="width:12px;height:12px;"></i> Slug (URL)</label>
                                    <input type="text" name="slug" value="{{ old('slug') }}" placeholder="post-url-slug (optional)">
                                    <span class="field-hint">A URL-friendly version of the title.</span>
                                </div>
                            </div>
                            <div class="form-group" style="margin-top: 14px;">
                                <label>Excerpt</label>
                                <span class="field-hint" style="margin-bottom: 2px;">Write a short summary that will appear in blog cards and previews.</span>
                                <textarea name="excerpt" id="excerptCreate" maxlength="500" placeholder="Write a short summary of your post...">{{ old('excerpt') }}</textarea>
                                <div class="char-counter" data-counter-for="excerptCreate"><span class="char-count">0</span> / 160</div>
                            </div>
                        </div>

                        <div class="form-section">
                            <div class="form-section-head-icon">
                                <span class="icon-box icon-box-red icon-box-sm"><i data-lucide="file-code-2"></i></span>
                                <div class="form-section-title">Content <span class="req">*</span></div>
                            </div>
                            <div class="editor-block">
                                <textarea name="content" id="contentCreate" class="editor-source" data-editor-id="blogEditorCreate" required>{{ old('content') }}</textarea>
                                <div id="blogEditorCreate" class="editor-shell"></div>
                            </div>
                        </div>
                    </div>

                    <div class="compose-sidebar">
                        <div class="form-section">
                            <div class="form-section-head-icon">
                                <span class="icon-box icon-box-neutral icon-box-sm"><i data-lucide="image"></i></span>
                                <div>
                                    <div class="form-section-title">Cover Image</div>
                                    <span class="field-hint">This image will be displayed at the top of your post.</span>
                                </div>
                            </div>
                            <input type="file" name="cover_image_file" id="createCoverImageFile" class="file-input-native" accept=".jpg,.jpeg,.png,.webp,.gif,image/*">
                            <label for="createCoverImageFile" class="dropzone" id="createDropzone">
                                <i data-lucide="upload-cloud" style="width: 28px; height: 28px;"></i>
                                <span class="dropzone-title">Drag &amp; drop an image here</span>
                                <span class="dropzone-or">or</span>
                                <span class="btn-soft dropzone-btn">Choose Image</span>
                            </label>
                            <div class="file-selected" id="createCoverImageFileName">No file selected</div>
                            <p class="field-hint" style="margin-top: 8px;">Recommended: JPG, PNG, WEBP or GIF. Max 5MB.</p>
                            <div class="form-group" style="margin-top: 14px;">
                                <label><i data-lucide="link" style="width: 12px; height: 12px;"></i> Image URL (optional)</label>
                                <input type="url" name="cover_image" value="{{ old('cover_image') }}" placeholder="https://example.com/image.jpg">
                            </div>
                        </div>

                        <div class="form-section">
                            <div class="form-section-head-icon">
                                <span class="icon-box icon-box-purple icon-box-sm"><i data-lucide="sparkles"></i></span>
                                <div class="form-section-title">Insert Media at Cursor</div>
                            </div>
                            <input type="file" id="contentMediaFileCreate" class="file-input-native" accept="image/*,video/*,audio/*,.pdf,.doc,.docx,.xls,.xlsx,.ppt,.pptx,.txt,.zip">
                            <label for="contentMediaFileCreate" class="media-upload-row">
                                <span class="upload-icon-sm"><i data-lucide="file-up"></i></span>
                                <span class="upload-copy">
                                    <span class="upload-title">Upload a file</span>
                                    <span class="upload-subtitle">Images, video, audio, or documents</span>
                                </span>
                                <span class="btn-soft-sm">Upload File</span>
                            </label>
                            <div class="file-selected" id="contentMediaFileCreateName">No file selected</div>
                            <p class="field-hint" style="margin: 12px 0 6px;">Or paste a media/YouTube/Vimeo link</p>
                            <div class="media-tool-row">
                                <input type="url" id="contentMediaUrlCreate" placeholder="https://youtube.com/watch?v=...">
                                <button type="button" class="btn-soft insert-media-url" data-editor-ref="blogEditorCreate" data-url-input="contentMediaUrlCreate">Insert Link</button>
                            </div>
                        </div>

                        <div class="form-section">
                            <div class="form-section-head-icon">
                                <span class="icon-box icon-box-green icon-box-sm"><i data-lucide="calendar-check"></i></span>
                                <div>
                                    <div class="form-section-title">Publishing Options</div>
                                    <span class="field-hint">Choose when and how to publish this post.</span>
                                </div>
                            </div>
                            <div class="publish-options" data-scope="Create">
                                <label class="publish-option">
                                    <input type="radio" name="publish_mode" value="now" checked>
                                    <span><strong>Publish immediately</strong><small>Post will go live right away.</small></span>
                                </label>
                                <label class="publish-option">
                                    <input type="radio" name="publish_mode" value="draft">
                                    <span><strong>Save as draft</strong><small>Save for later review and publishing.</small></span>
                                </label>
                                <label class="publish-option">
                                    <input type="radio" name="publish_mode" value="schedule">
                                    <span><strong>Schedule for later</strong><small>Select date and time to publish.</small></span>
                                </label>
                            </div>
                            <div class="schedule-datetime-row" id="scheduleRowCreate" style="display: none;">
                                <label>Publish date &amp; time</label>
                                <input type="datetime-local" name="scheduled_at" id="scheduledAtCreate">
                            </div>
                        </div>
                    </div>
                </div>

                <div class="compose-actions-bar">
                    <button type="button" class="btn-soft save-draft-btn" data-form-scope="Create"><i data-lucide="save" style="width:14px;height:14px;"></i> Save as Draft</button>
                    <button type="submit" class="btn-primary submit-publish-btn" id="submitBtnCreate" style="border:none; display:flex; align-items:center; gap:8px;">
                        <i data-lucide="send" style="width: 16px; height: 16px;"></i> <span>Publish Post</span>
                    </button>
                </div>
            </form>
        </section>

        <section class="panel-card manage-card">
            <div class="posts-toolbar">
                <h3 class="section-title" style="margin:0;"><i data-lucide="files"></i> Manage Posts</h3>
                <span class="posts-count">{{ $posts->total() }} post(s)</span>
            </div>

            @if($posts->count())
                <div class="post-list">
                    @foreach($posts as $post)
                        <details class="post-card">
                            <summary class="post-summary">
                                <div class="post-summary-main">
                                    @php
                                        $postState = !$post->is_published ? 'draft' : (($post->published_at && $post->published_at->isFuture()) ? 'scheduled' : 'published');
                                        $postStateLabel = ['published' => 'Published', 'scheduled' => 'Scheduled', 'draft' => 'Draft'][$postState];
                                    @endphp
                                    <div class="post-title-row">
                                        <h4>{{ $post->title }}</h4>
                                        <span class="post-status {{ $postState }}">
                                            {{ $postStateLabel }}
                                        </span>
                                        @if($postState === 'scheduled')
                                        <span class="field-hint">for {{ $post->published_at->format('M d, Y g:i A') }}</span>
                                        @endif
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
                                    <form action="{{ route('admin.blog.update', $post) }}" method="POST" enctype="multipart/form-data" id="editForm{{ $post->id }}">
                                        @csrf
                                        @method('PUT')
                                        <div class="compose-layout">
                                            <div class="compose-main">
                                                <div class="form-section">
                                                    <div class="form-section-head-icon">
                                                        <span class="icon-box icon-box-red icon-box-sm"><i data-lucide="file-text"></i></span>
                                                        <div class="form-section-title">Post Information</div>
                                                    </div>
                                                    <div class="form-grid-2">
                                                        <div class="form-group">
                                                            <label>Post Title <span class="req">*</span></label>
                                                            <input type="text" name="title" value="{{ $post->title }}" required>
                                                        </div>
                                                        <div class="form-group">
                                                            <label><i data-lucide="info" style="width:12px;height:12px;"></i> Slug (URL)</label>
                                                            <input type="text" name="slug" value="{{ $post->slug }}">
                                                            <span class="field-hint">A URL-friendly version of the title.</span>
                                                        </div>
                                                    </div>
                                                    <div class="form-group" style="margin-top: 14px;">
                                                        <label>Excerpt</label>
                                                        <textarea name="excerpt" id="excerpt{{ $post->id }}" maxlength="500">{{ $post->excerpt }}</textarea>
                                                        <div class="char-counter" data-counter-for="excerpt{{ $post->id }}"><span class="char-count">{{ strlen($post->excerpt ?? '') }}</span> / 160</div>
                                                    </div>
                                                </div>

                                                <div class="form-section">
                                                    <div class="form-section-head-icon">
                                                        <span class="icon-box icon-box-red icon-box-sm"><i data-lucide="file-code-2"></i></span>
                                                        <div class="form-section-title">Content <span class="req">*</span></div>
                                                    </div>
                                                    <div class="editor-block">
                                                        <textarea name="content" id="content{{ $post->id }}" class="editor-source" data-editor-id="blogEditor{{ $post->id }}" required>{{ $post->content }}</textarea>
                                                        <div id="blogEditor{{ $post->id }}" class="editor-shell"></div>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="compose-sidebar">
                                                <div class="form-section">
                                                    <div class="form-section-head-icon">
                                                        <span class="icon-box icon-box-neutral icon-box-sm"><i data-lucide="image"></i></span>
                                                        <div class="form-section-title">Cover Image</div>
                                                    </div>
                                                    @if($post->cover_image)
                                                        <img src="{{ $post->cover_image }}" alt="{{ $post->title }}" class="cover-preview">
                                                    @endif
                                                    <input type="file" name="cover_image_file" id="coverImageFile{{ $post->id }}" class="file-input-native" accept=".jpg,.jpeg,.png,.webp,.gif,image/*">
                                                    <label for="coverImageFile{{ $post->id }}" class="dropzone" id="dropzone{{ $post->id }}" style="margin-top: 10px;">
                                                        <i data-lucide="upload-cloud" style="width: 24px; height: 24px;"></i>
                                                        <span class="dropzone-title">Drag &amp; drop to replace</span>
                                                        <span class="dropzone-or">or</span>
                                                        <span class="btn-soft dropzone-btn">Choose Image</span>
                                                    </label>
                                                    <div class="file-selected" id="coverImageFileName{{ $post->id }}">No new file selected</div>
                                                    <div class="form-group" style="margin-top: 14px;">
                                                        <label><i data-lucide="link" style="width:12px;height:12px;"></i> Image URL (optional)</label>
                                                        <input type="url" name="cover_image" value="{{ $post->cover_image }}">
                                                    </div>
                                                </div>

                                                <div class="form-section">
                                                    <div class="form-section-head-icon">
                                                        <span class="icon-box icon-box-purple icon-box-sm"><i data-lucide="sparkles"></i></span>
                                                        <div class="form-section-title">Insert Media at Cursor</div>
                                                    </div>
                                                    <input type="file" id="contentMediaFile{{ $post->id }}" class="file-input-native" accept="image/*,video/*,audio/*,.pdf,.doc,.docx,.xls,.xlsx,.ppt,.pptx,.txt,.zip">
                                                    <label for="contentMediaFile{{ $post->id }}" class="media-upload-row">
                                                        <span class="upload-icon-sm"><i data-lucide="file-up"></i></span>
                                                        <span class="upload-copy">
                                                            <span class="upload-title">Upload a file</span>
                                                            <span class="upload-subtitle">Images, video, audio, or documents</span>
                                                        </span>
                                                        <span class="btn-soft-sm">Upload File</span>
                                                    </label>
                                                    <div class="file-selected" id="contentMediaFileName{{ $post->id }}">No file selected</div>
                                                    <p class="field-hint" style="margin: 12px 0 6px;">Or paste a media/YouTube/Vimeo link</p>
                                                    <div class="media-tool-row">
                                                        <input type="url" id="contentMediaUrl{{ $post->id }}" placeholder="https://youtube.com/watch?v=...">
                                                        <button type="button" class="btn-soft insert-media-url" data-editor-ref="blogEditor{{ $post->id }}" data-url-input="contentMediaUrl{{ $post->id }}">Insert Link</button>
                                                    </div>
                                                </div>

                                                <div class="form-section">
                                                    <div class="form-section-head-icon">
                                                        <span class="icon-box icon-box-green icon-box-sm"><i data-lucide="calendar-check"></i></span>
                                                        <div class="form-section-title">Publishing Options</div>
                                                    </div>
                                                    <div class="publish-options" data-scope="{{ $post->id }}">
                                                        <label class="publish-option">
                                                            <input type="radio" name="publish_mode" value="now" {{ $postState !== 'draft' && $postState !== 'scheduled' ? 'checked' : '' }}>
                                                            <span><strong>Publish immediately</strong><small>Post will go live right away.</small></span>
                                                        </label>
                                                        <label class="publish-option">
                                                            <input type="radio" name="publish_mode" value="draft" {{ $postState === 'draft' ? 'checked' : '' }}>
                                                            <span><strong>Save as draft</strong><small>Save for later review and publishing.</small></span>
                                                        </label>
                                                        <label class="publish-option">
                                                            <input type="radio" name="publish_mode" value="schedule" {{ $postState === 'scheduled' ? 'checked' : '' }}>
                                                            <span><strong>Schedule for later</strong><small>Select date and time to publish.</small></span>
                                                        </label>
                                                    </div>
                                                    <div class="schedule-datetime-row" id="scheduleRow{{ $post->id }}" style="{{ $postState === 'scheduled' ? '' : 'display: none;' }}">
                                                        <label>Publish date &amp; time</label>
                                                        <input type="datetime-local" name="scheduled_at" id="scheduledAt{{ $post->id }}" value="{{ $postState === 'scheduled' ? $post->published_at->format('Y-m-d\TH:i') : '' }}">
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="compose-actions-bar" style="justify-content: space-between;">
                                            <div class="post-actions">
                                                @if($postState === 'published')
                                                    <a href="{{ route('blog.show', $post) }}" class="btn-soft" style="text-decoration:none;display:inline-flex;align-items:center;">Open Live Post</a>
                                                @else
                                                    <span class="btn-soft" style="display:inline-flex;align-items:center;opacity:0.65;">Preview Unavailable</span>
                                                @endif
                                            </div>
                                            <div style="display:flex; gap:12px; flex-wrap:wrap;">
                                                <button type="button" class="btn-soft save-draft-btn" data-form-scope="{{ $post->id }}"><i data-lucide="save" style="width:14px;height:14px;"></i> Save as Draft</button>
                                                <button type="submit" class="btn-primary submit-publish-btn" id="submitBtn{{ $post->id }}" style="border:none; display:flex; align-items:center; gap:8px;">
                                                    <i data-lucide="send" style="width: 16px; height: 16px;"></i> <span>Save Changes</span>
                                                </button>
                                            </div>
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

    function bindDropzone(fileInputId, dropzoneId) {
        const input = document.getElementById(fileInputId);
        const zone = document.getElementById(dropzoneId);

        if (!input || !zone) {
            return;
        }

        zone.addEventListener('dragover', (e) => {
            e.preventDefault();
            zone.classList.add('dragover');
        });
        zone.addEventListener('dragleave', () => zone.classList.remove('dragover'));
        zone.addEventListener('drop', (e) => {
            e.preventDefault();
            zone.classList.remove('dragover');

            if (e.dataTransfer.files && e.dataTransfer.files.length > 0) {
                input.files = e.dataTransfer.files;
                input.dispatchEvent(new Event('change'));
            }
        });
    }

    function bindExcerptCounter(textareaId) {
        const textarea = document.getElementById(textareaId);
        const counter = document.querySelector('.char-counter[data-counter-for="' + textareaId + '"]');

        if (!textarea || !counter) {
            return;
        }

        const countEl = counter.querySelector('.char-count');

        const update = () => {
            const len = textarea.value.length;
            countEl.textContent = len;
            counter.classList.toggle('over-limit', len > 160);
        };

        textarea.addEventListener('input', update);
        update();
    }

    function bindPublishOptions(scope) {
        const group = document.querySelector('.publish-options[data-scope="' + scope + '"]');
        const scheduleRow = document.getElementById('scheduleRow' + scope);
        const scheduleInput = document.getElementById('scheduledAt' + scope);
        const submitBtn = document.getElementById('submitBtn' + scope);

        if (!group) {
            return;
        }

        const labels = {
            now: { text: scope === 'Create' ? 'Publish Post' : 'Save Changes', icon: 'send' },
            draft: { text: 'Save as Draft', icon: 'save' },
            schedule: { text: 'Schedule Post', icon: 'calendar-clock' },
        };

        const applyState = () => {
            const options = group.querySelectorAll('.publish-option');
            const checked = group.querySelector('input[type="radio"]:checked');
            const mode = checked ? checked.value : 'now';

            options.forEach((opt) => opt.classList.toggle('selected', opt.contains(checked)));

            if (scheduleRow) {
                scheduleRow.style.display = mode === 'schedule' ? '' : 'none';
            }

            if (scheduleInput) {
                scheduleInput.required = mode === 'schedule';
            }

            if (submitBtn) {
                const config = labels[mode] || labels.now;
                submitBtn.querySelector('span').textContent = config.text;
                const iconEl = submitBtn.querySelector('i');
                if (iconEl) {
                    iconEl.setAttribute('data-lucide', config.icon);
                }
                lucide.createIcons();
            }
        };

        group.querySelectorAll('input[type="radio"]').forEach((radio) => {
            radio.addEventListener('change', applyState);
        });

        applyState();
    }

    function bindSaveDraftButton(scope) {
        const btn = document.querySelector('.save-draft-btn[data-form-scope="' + scope + '"]');
        const group = document.querySelector('.publish-options[data-scope="' + scope + '"]');
        const form = document.getElementById((scope === 'Create' ? 'createPostForm' : 'editForm' + scope));

        if (!btn || !group || !form) {
            return;
        }

        btn.addEventListener('click', () => {
            const draftRadio = group.querySelector('input[value="draft"]');
            if (draftRadio) {
                draftRadio.checked = true;
                draftRadio.dispatchEvent(new Event('change'));
            }
            // requestSubmit (not submit()) so the "submit" listener that syncs the
            // CKEditor content into the hidden textarea still runs.
            form.requestSubmit();
        });
    }

    bindFileName('createCoverImageFile', 'createCoverImageFileName', 'No file selected');
    bindFileName('contentMediaFileCreate', 'contentMediaFileCreateName', 'No file selected');
    bindDropzone('createCoverImageFile', 'createDropzone');
    bindExcerptCounter('excerptCreate');
    bindPublishOptions('Create');
    bindSaveDraftButton('Create');
    @foreach($posts as $post)
    bindFileName('coverImageFile{{ $post->id }}', 'coverImageFileName{{ $post->id }}', 'No new file selected');
    bindFileName('contentMediaFile{{ $post->id }}', 'contentMediaFileName{{ $post->id }}', 'No file selected');
    bindDropzone('coverImageFile{{ $post->id }}', 'dropzone{{ $post->id }}');
    bindExcerptCounter('excerpt{{ $post->id }}');
    bindPublishOptions('{{ $post->id }}');
    bindSaveDraftButton('{{ $post->id }}');
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
