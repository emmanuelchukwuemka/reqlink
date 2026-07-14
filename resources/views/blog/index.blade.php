<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Blog & News | ResQLink</title>
    <meta name="description" content="Latest ResQLink blog posts, emergency news, updates, and safety insights.">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700;800;900&family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/css/landing.css?v={{ filemtime(public_path('css/landing.css')) }}">
    <script src="https://unpkg.com/lucide@latest"></script>
    <style>
        .theme-toggle { background: transparent; border: none; color: var(--grey); cursor: pointer; padding: 8px; border-radius: 50%; transition: all 0.3s; display: flex; align-items: center; justify-content: center; }
        .theme-toggle:hover { background: var(--glass); color: var(--white); }
        :root.light-mode .theme-toggle:hover { background: rgba(0,0,0,0.05); color: var(--text-main); }
        .blog-shell { padding-top: 110px; padding-bottom: 80px; min-height: 100vh; background: linear-gradient(180deg, var(--black) 0%, var(--dark2) 100%); }
        .blog-hero { margin-bottom: 36px; }
        .blog-hero-grid { display: grid; grid-template-columns: 1.3fr 0.9fr; gap: 28px; align-items: stretch; }
        .blog-featured, .blog-side-card, .blog-card { background: var(--glass); border: 1px solid var(--glass-border); border-radius: 24px; overflow: hidden; }
        .blog-featured { display: grid; grid-template-columns: 1fr 1fr; min-height: 380px; }
        .blog-cover { background: linear-gradient(135deg, rgba(229,9,20,0.18), rgba(229,9,20,0.04)); min-height: 100%; }
        .blog-cover img { width: 100%; height: 100%; object-fit: cover; display: block; }
        .blog-featured-body, .blog-card-body, .blog-side-body { padding: 28px; }
        .blog-badge { display: inline-flex; align-items: center; gap: 8px; padding: 6px 12px; border-radius: 999px; background: rgba(229,9,20,0.1); color: var(--red); font-size: 0.72rem; font-weight: 800; letter-spacing: 0.6px; text-transform: uppercase; }
        .blog-meta { color: var(--grey); font-size: 0.82rem; margin: 14px 0; display: flex; gap: 14px; flex-wrap: wrap; }
        .blog-title { font-size: clamp(1.5rem, 3vw, 2.4rem); font-weight: 900; line-height: 1.1; margin: 14px 0; }
        .blog-excerpt { color: var(--text-muted); font-size: 0.96rem; line-height: 1.7; }
        .blog-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 22px; margin-top: 28px; }
        .blog-card-cover { height: 180px; background: linear-gradient(135deg, rgba(229,9,20,0.18), rgba(229,9,20,0.04)); }
        .blog-card-cover img { width: 100%; height: 100%; object-fit: cover; display: block; }
        .blog-card-title { font-size: 1.15rem; font-weight: 800; margin: 12px 0 10px; line-height: 1.25; }
        .blog-empty { text-align: center; padding: 50px 24px; background: var(--glass); border: 1px solid var(--glass-border); border-radius: 24px; color: var(--grey); }
        .blog-side-list { display: flex; flex-direction: column; gap: 18px; }
        .blog-side-item { padding-bottom: 18px; border-bottom: 1px solid var(--glass-border); }
        .blog-side-item:last-child { padding-bottom: 0; border-bottom: none; }
        .read-link { display: inline-flex; align-items: center; gap: 8px; margin-top: 16px; color: var(--red); font-size: 0.86rem; font-weight: 700; }
        .pagination { display: flex; gap: 8px; justify-content: center; margin-top: 34px; flex-wrap: wrap; }
        .pagination .page-link, .pagination .page-item span { padding: 10px 14px; border-radius: 10px; border: 1px solid var(--glass-border); color: var(--grey); background: var(--glass); text-decoration: none; }
        .pagination .active span { background: var(--red); color: #fff; border-color: var(--red); }
        .pagination .disabled span { opacity: 0.45; }
        @media (max-width: 1024px) {
            .blog-hero-grid { grid-template-columns: 1fr; }
            .blog-grid { grid-template-columns: repeat(2, 1fr); }
        }
        @media (max-width: 768px) {
            .blog-featured { grid-template-columns: 1fr; }
            .blog-grid { grid-template-columns: 1fr; }
        }
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

<main class="blog-shell">
    <div class="container">
        <div class="blog-hero">
            <div class="section-label">Blog & News</div>
            <h1 class="section-title">Emergency Insights, Product Updates, and Community News</h1>
            <p class="section-desc">Stay updated with safety tips, platform improvements, response stories, and important announcements from ResQLink.</p>
        </div>

        @if($featuredPost)
        <div class="blog-hero-grid">
            <article class="blog-featured">
                <div class="blog-cover">
                    @if($featuredPost->cover_image)
                        <img src="{{ $featuredPost->cover_image }}" alt="{{ $featuredPost->title }}">
                    @endif
                </div>
                <div class="blog-featured-body">
                    <span class="blog-badge"><i data-lucide="sparkles" style="width:14px;height:14px;"></i> Featured Story</span>
                    <div class="blog-meta">
                        <span>{{ optional($featuredPost->published_at)->format('M d, Y') }}</span>
                        <span>By {{ $featuredPost->author->name ?? 'ResQLink Team' }}</span>
                    </div>
                    <h2 class="blog-title">{{ $featuredPost->title }}</h2>
                    <p class="blog-excerpt">{{ $featuredPost->excerpt }}</p>
                    <a href="{{ route('blog.show', $featuredPost) }}" class="read-link">
                        Read full article
                        <i data-lucide="arrow-right" style="width:16px;height:16px;"></i>
                    </a>
                </div>
            </article>

            <aside class="blog-side-card">
                <div class="blog-side-body">
                    <h3 style="margin:0 0 18px;">Latest Posts</h3>
                    <div class="blog-side-list">
                        @forelse($posts->take(3) as $post)
                        <div class="blog-side-item">
                            <div class="blog-meta" style="margin:0 0 8px;">
                                <span>{{ optional($post->published_at)->format('M d, Y') }}</span>
                            </div>
                            <div style="font-size:1rem;font-weight:800;line-height:1.35;">{{ $post->title }}</div>
                            <a href="{{ route('blog.show', $post) }}" class="read-link" style="margin-top:10px;">Open post</a>
                        </div>
                        @empty
                        <p style="color: var(--grey); margin: 0;">More updates will appear here as soon as they are published.</p>
                        @endforelse
                    </div>
                </div>
            </aside>
        </div>
        @endif

        @if($posts->count())
        <div class="blog-grid">
            @foreach($posts as $post)
            <article class="blog-card">
                <div class="blog-card-cover">
                    @if($post->cover_image)
                        <img src="{{ $post->cover_image }}" alt="{{ $post->title }}">
                    @endif
                </div>
                <div class="blog-card-body">
                    <div class="blog-meta">
                        <span>{{ optional($post->published_at)->format('M d, Y') }}</span>
                        <span>{{ $post->author->name ?? 'ResQLink Team' }}</span>
                    </div>
                    <h3 class="blog-card-title">{{ $post->title }}</h3>
                    <p class="blog-excerpt">{{ $post->excerpt }}</p>
                    <a href="{{ route('blog.show', $post) }}" class="read-link">
                        Continue reading
                        <i data-lucide="arrow-right" style="width:16px;height:16px;"></i>
                    </a>
                </div>
            </article>
            @endforeach
        </div>

        <div class="pagination">
            {{ $posts->links() }}
        </div>
        @elseif(!$featuredPost)
        <div class="blog-empty">
            <h3 style="margin-bottom: 10px;">No blog posts published yet</h3>
            <p>Admin updates and news articles will appear here once they are published.</p>
        </div>
        @endif
    </div>
</main>

<script src="/js/landing.js"></script>
<script>lucide.createIcons();</script>
</body>
</html>
