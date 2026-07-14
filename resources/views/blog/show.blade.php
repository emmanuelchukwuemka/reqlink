<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $blogPost->title }} | ResQLink Blog</title>
    <meta name="description" content="{{ $blogPost->excerpt }}">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700;800;900&family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/css/landing.css?v={{ filemtime(public_path('css/landing.css')) }}">
    <script src="https://unpkg.com/lucide@latest"></script>
    <style>
        .theme-toggle { background: transparent; border: none; color: var(--grey); cursor: pointer; padding: 8px; border-radius: 50%; transition: all 0.3s; display: flex; align-items: center; justify-content: center; }
        .theme-toggle:hover { background: var(--glass); color: var(--white); }
        :root.light-mode .theme-toggle:hover { background: rgba(0,0,0,0.05); color: var(--text-main); }
        .article-shell { padding-top: 110px; padding-bottom: 80px; background: linear-gradient(180deg, var(--black) 0%, var(--dark2) 100%); min-height: 100vh; }
        .article-grid { display: grid; grid-template-columns: minmax(0, 2fr) 340px; gap: 28px; align-items: start; }
        .article-card, .article-side { background: var(--glass); border: 1px solid var(--glass-border); border-radius: 24px; overflow: hidden; }
        .article-cover { height: 340px; background: linear-gradient(135deg, rgba(229,9,20,0.18), rgba(229,9,20,0.04)); }
        .article-cover img { width: 100%; height: 100%; object-fit: cover; display: block; }
        .article-body { padding: 34px; }
        .article-meta { display: flex; gap: 14px; flex-wrap: wrap; color: var(--grey); font-size: 0.84rem; margin: 16px 0 12px; }
        .article-title { font-size: clamp(2rem, 4vw, 3.4rem); line-height: 1.08; margin: 0; }
        .article-excerpt { color: var(--text-muted); font-size: 1.02rem; line-height: 1.8; margin-top: 18px; }
        .article-content { margin-top: 26px; font-size: 1rem; line-height: 1.85; color: var(--text-main); }
        .article-content p { margin-bottom: 18px; }
        .article-content h2, .article-content h3, .article-content h4 { margin: 28px 0 12px; }
        .article-content ul, .article-content ol { padding-left: 20px; margin: 0 0 18px; }
        .article-content a { color: var(--red); text-decoration: underline; text-underline-offset: 3px; }
        .article-content figure { margin: 24px 0; }
        .article-content img { width: 100%; max-width: 100%; border-radius: 18px; display: block; }
        .article-content iframe, .article-content video { width: 100%; min-height: 360px; border: 0; border-radius: 18px; background: rgba(0,0,0,0.25); }
        .article-content audio { width: 100%; margin: 12px 0 20px; }
        .article-content blockquote {
            margin: 24px 0;
            padding: 16px 20px;
            border-left: 4px solid var(--red);
            border-radius: 0 16px 16px 0;
            background: rgba(229,9,20,0.08);
        }
        .article-content table { width: 100%; border-collapse: collapse; margin: 24px 0; overflow: hidden; border-radius: 14px; }
        .article-content th, .article-content td { border: 1px solid var(--glass-border); padding: 12px 14px; text-align: left; }
        .article-content thead th { background: rgba(255,255,255,0.05); }
        .article-content pre {
            padding: 16px;
            border-radius: 16px;
            overflow-x: auto;
            background: rgba(0,0,0,0.35);
        }
        .article-content code { font-family: Consolas, Monaco, monospace; }
        .side-body { padding: 26px; }
        .side-item { padding-bottom: 18px; margin-bottom: 18px; border-bottom: 1px solid var(--glass-border); }
        .side-item:last-child { padding-bottom: 0; margin-bottom: 0; border-bottom: none; }
        .back-link, .read-link { display: inline-flex; align-items: center; gap: 8px; color: var(--red); font-weight: 700; text-decoration: none; }
        @media (max-width: 1024px) { .article-grid { grid-template-columns: 1fr; } }
        @media (max-width: 768px) { .article-cover { height: 240px; } .article-body { padding: 24px; } .article-content iframe, .article-content video { min-height: 220px; } }
    </style>
    <script src="/js/theme.js"></script>
</head>
<body>
<nav class="nav" id="navbar">
    <div class="container">
        <a href="{{ url('/') }}" class="nav-logo">
            <img src="{{ asset('images/logo.png') }}" alt="ResQLink" style="height: 50px; width: auto; object-fit: contain;">
        </a>
        <ul class="nav-links" id="navLinks">
            <li><a href="{{ url('/') }}" class="nav-link-item">Home</a></li>
            <li><a href="{{ route('blog.index') }}" class="nav-link-item" style="color: var(--red);">Blog</a></li>
            <li class="nav-divider-line"></li>
            <li><a href="{{ route('login') }}" class="nav-link-item">Login</a></li>
            <li><a href="{{ route('register') }}" class="btn-primary btn-sm">Register</a></li>
            <li>
                <button id="themeToggle" class="theme-toggle" aria-label="Toggle Dark Mode" style="margin-left: 8px;">
                    <i data-lucide="sun" id="themeIcon"></i>
                </button>
            </li>
        </ul>
        <button class="hamburger" id="hamburger" aria-label="Menu">
            <span></span><span></span><span></span>
        </button>
    </div>
</nav>

<main class="article-shell">
    <div class="container">
        <a href="{{ route('blog.index') }}" class="back-link" style="margin-bottom: 18px;">
            <i data-lucide="arrow-left" style="width:16px;height:16px;"></i>
            Back to blog
        </a>

        <div class="article-grid">
            <article class="article-card">
                <div class="article-cover">
                    @if($blogPost->cover_image)
                        <img src="{{ $blogPost->cover_image }}" alt="{{ $blogPost->title }}">
                    @endif
                </div>
                <div class="article-body">
                    <span class="blog-badge" style="display:inline-flex;align-items:center;gap:8px;padding:6px 12px;border-radius:999px;background:rgba(229,9,20,0.1);color:var(--red);font-size:0.72rem;font-weight:800;letter-spacing:0.6px;text-transform:uppercase;">News & Update</span>
                    <div class="article-meta">
                        <span>{{ optional($blogPost->published_at)->format('M d, Y') }}</span>
                        <span>By {{ $blogPost->author->name ?? 'ResQLink Team' }}</span>
                    </div>
                    <h1 class="article-title">{{ $blogPost->title }}</h1>
                    <p class="article-excerpt">{{ $blogPost->excerpt }}</p>
                    <div class="article-content">{!! $blogPost->content !!}</div>
                </div>
            </article>

            <aside class="article-side">
                <div class="side-body">
                    <h3 style="margin: 0 0 18px;">Latest Posts</h3>
                    @forelse($latestPosts as $post)
                    <div class="side-item">
                        <div style="color: var(--grey); font-size: 0.78rem; margin-bottom: 8px;">{{ optional($post->published_at)->format('M d, Y') }}</div>
                        <div style="font-size: 1rem; font-weight: 800; line-height: 1.35;">{{ $post->title }}</div>
                        <a href="{{ route('blog.show', $post) }}" class="read-link" style="margin-top: 10px;">Read article</a>
                    </div>
                    @empty
                    <p style="color: var(--grey); margin: 0;">More blog posts will appear here once they are published.</p>
                    @endforelse
                </div>
            </aside>
        </div>
    </div>
</main>

<script src="/js/landing.js"></script>
<script>lucide.createIcons();</script>
</body>
</html>
