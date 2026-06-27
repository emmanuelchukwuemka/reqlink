@php $currentLocale = session('locale', 'en'); @endphp
<div class="lang-switcher" title="Switch Language">
    <button class="lang-btn" onclick="document.getElementById('langMenu').classList.toggle('open')" aria-label="Language">
        @if($currentLocale === 'yo') 🇳🇬 YO
        @elseif($currentLocale === 'ha') 🇳🇬 HA
        @elseif($currentLocale === 'ig') 🇳🇬 IG
        @else 🌐 EN
        @endif
    </button>
    <div class="lang-menu" id="langMenu">
        <a href="{{ route('lang.switch', 'en') }}" class="{{ $currentLocale === 'en' ? 'active' : '' }}">🌐 English</a>
        <a href="{{ route('lang.switch', 'yo') }}" class="{{ $currentLocale === 'yo' ? 'active' : '' }}">🇳🇬 Yoruba</a>
        <a href="{{ route('lang.switch', 'ha') }}" class="{{ $currentLocale === 'ha' ? 'active' : '' }}">🇳🇬 Hausa</a>
        <a href="{{ route('lang.switch', 'ig') }}" class="{{ $currentLocale === 'ig' ? 'active' : '' }}">🇳🇬 Igbo</a>
    </div>
</div>

<style>
.lang-switcher { position: relative; display: inline-block; }
.lang-btn {
    background: var(--glass, rgba(255,255,255,0.05));
    border: 1px solid var(--glass-border, rgba(255,255,255,0.08));
    color: var(--white, #fff);
    padding: 6px 12px;
    border-radius: 8px;
    font-size: 0.78rem;
    font-weight: 700;
    cursor: pointer;
    transition: all 0.2s;
    letter-spacing: 0.5px;
}
.lang-btn:hover { border-color: var(--red, #e50914); }
.lang-menu {
    display: none;
    position: absolute;
    top: calc(100% + 6px);
    right: 0;
    background: #111;
    border: 1px solid var(--glass-border, rgba(255,255,255,0.08));
    border-radius: 10px;
    overflow: hidden;
    min-width: 130px;
    z-index: 9999;
    box-shadow: 0 8px 24px rgba(0,0,0,0.5);
}
.lang-menu.open { display: block; }
.lang-menu a {
    display: block;
    padding: 10px 14px;
    color: var(--white, #fff);
    text-decoration: none;
    font-size: 0.82rem;
    font-weight: 600;
    transition: background 0.15s;
}
.lang-menu a:hover { background: rgba(229,9,20,0.12); }
.lang-menu a.active { color: var(--red, #e50914); background: rgba(229,9,20,0.07); }
:root.light-mode .lang-btn { color: #111; }
:root.light-mode .lang-menu { background: #fff; color: #111; }
:root.light-mode .lang-menu a { color: #111; }
</style>
