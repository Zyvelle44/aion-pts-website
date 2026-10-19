<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="theme-color" content="#0b0d12">
    <meta name="description" content="@yield('meta_description', $websiteSettings['meta_description'] ?? 'Aion PTS By Zyvalle — Aion MMORPG 4.6 Private Test Server.')">
    <meta property="og:site_name" content="{{ $websiteSettings['site_name'] ?: 'AION PTS' }}">
    <meta property="og:type" content="@yield('og_type', 'website')">
    <meta property="og:title" content="@yield('og_title', (($websiteSettings['site_name'] ?? 'AION PTS') . ' — ' . ($websiteSettings['tagline'] ?? 'By Zyvalle')))">
    <meta property="og:description" content="@yield('og_description', $websiteSettings['meta_description'] ?? 'Aion MMORPG 4.6 Private Test Server.')">
    @hasSection('og_image')
        <meta property="og:image" content="@yield('og_image')">
    @elseif(filled($websiteSettings['logo_path'] ?? ''))
        <meta property="og:image" content="{{ asset('storage/' . $websiteSettings['logo_path']) }}">
    @endif
    @hasSection('canonical_url')
        @yield('canonical_url')
    @elseif(!($isPreview ?? false))
        <link rel="canonical" href="{{ url()->current() }}">
        <meta property="og:url" content="{{ url()->current() }}">
    @endif
    @if (filled($websiteSettings['favicon_path'] ?? ''))
        <link rel="icon" href="{{ asset('storage/' . $websiteSettings['favicon_path']) }}">
    @else
        <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">
    @endif
    @yield('meta_extra')
    <title>@yield('title', (($websiteSettings['site_name'] ?? 'AION PTS') . ' — ' . ($websiteSettings['tagline'] ?? 'By Zyvalle')))</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cinzel:wght@400;500;600;700&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

    <style>
        :root {
            --bg: #0b0d12;
            --panel: #11151d;
            --gold: #c8aa70;
            --text: #f3f0e9;
            --muted: #a1a6b1;
            --line: rgba(255,255,255,.10);
        }

        * { box-sizing: border-box; }

        html { scroll-behavior: smooth; }

        body {
            margin: 0;
            background: var(--bg);
            color: var(--text);
            font-family: 'Inter', sans-serif;
            line-height: 1.6;
        }

        a { color: inherit; text-decoration: none; }

        .container {
            width: min(1160px, 92%);
            margin: auto;
        }

        .topbar {
            background: #080a0e;
            border-bottom: 1px solid var(--line);
            color: var(--muted);
            font-size: 11px;
            letter-spacing: 1px;
        }

        .topbar-inner {
            min-height: 37px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 12px;
        }

        .brand-gold { color: var(--gold); }

        .navbar {
            position: relative;
            z-index: 10;
            background: rgba(13,16,22,.96);
            border-bottom: 1px solid var(--line);
        }

        .nav-inner {
            min-height: 78px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 24px;
        }

        .brand {
            display: inline-flex;
            flex-direction: column;
            justify-content: center;
            align-items: flex-start;
            font-family: 'Cinzel', serif;
            font-size: 24px;
            font-weight: 700;
            letter-spacing: 2px;
            line-height: 1.2;
            white-space: nowrap;
        }

        .brand small {
            display: block;
            margin-top: 5px;
            font: 10px 'Inter', sans-serif;
            letter-spacing: 4px;
            color: var(--gold);
        }

        .nav-links {
            display: flex;
            align-items: center;
            gap: 22px;
            font-size: 12px;
            font-weight: 600;
            letter-spacing: .6px;
        }

        .nav-links a { color: #d4d5da; }
        .nav-links a:hover { color: var(--gold); }

        .nav-actions {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .menu-toggle {
            display: none;
            border: 1px solid var(--line);
            background: transparent;
            color: white;
            padding: 9px 12px;
            cursor: pointer;
        }

        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-height: 44px;
            padding: 0 19px;
            border: 1px solid var(--gold);
            background: var(--gold);
            color: #111219;
            font-size: 11px;
            font-weight: 700;
            letter-spacing: 1px;
            transition: .2s ease;
        }

        .btn:hover {
            background: #e0c794;
            border-color: #e0c794;
            transform: translateY(-1px);
        }

        .btn-outline {
            background: transparent;
            color: var(--text);
            border-color: rgba(255,255,255,.25);
        }

        .btn-outline:hover {
            color: var(--gold);
            border-color: var(--gold);
            background: transparent;
        }

        .hero {
            position: relative;
            overflow: hidden;
            min-height: 580px;
            display: flex;
            align-items: center;
            background:
                radial-gradient(ellipse at 76% 36%, rgba(118,94,60,.24), transparent 36%),
                radial-gradient(ellipse at 65% 100%, rgba(37,58,83,.36), transparent 50%),
                linear-gradient(110deg, #0b0d12 10%, rgba(11,13,18,.94) 45%, rgba(11,13,18,.62) 100%);
            border-bottom: 1px solid var(--line);
        }

        .hero::before {
            content: "";
            position: absolute;
            inset: 0;
            pointer-events: none;
            opacity: .22;
            background-image:
                radial-gradient(circle at 20% 30%, #d5bd88 1px, transparent 1.5px),
                radial-gradient(circle at 70% 65%, #aabbd0 1px, transparent 1.5px);
            background-size: 83px 91px, 127px 113px;
            mask-image: linear-gradient(to right, black, transparent);
        }

        .hero-content {
            position: relative;
            max-width: 690px;
            padding: 95px 0;
        }

        .eyebrow {
            color: var(--gold);
            font-size: 11px;
            font-weight: 700;
            letter-spacing: 4px;
            text-transform: uppercase;
        }

        h1, h2, h3 {
            font-family: 'Cinzel', serif;
            font-weight: 500;
            line-height: 1.2;
        }

        h1 {
            margin: 20px 0;
            font-size: clamp(39px, 6.3vw, 76px);
            letter-spacing: 1px;
        }

        .hero p {
            max-width: 530px;
            color: #b9bdc6;
            font-size: 15px;
            line-height: 1.9;
        }

        .hero-buttons {
            display: flex;
            flex-wrap: wrap;
            gap: 12px;
            margin-top: 30px;
        }

        .hero-note {
            margin-top: 28px;
            color: #858b96;
            font-size: 11px;
            letter-spacing: 1px;
        }

        .section { padding: 76px 0; }

        .section-heading {
            margin-bottom: 28px;
        }

        .section-heading h2 {
            margin: 8px 0;
            font-size: clamp(25px, 4vw, 36px);
        }

        .section-heading p {
            color: var(--muted);
            font-size: 13px;
        }

        .server-grid {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 16px;
        }

        .server-card {
            padding: 24px;
            border: 1px solid var(--line);
            background: linear-gradient(145deg, #151a23, #101319);
        }

        .server-card .label {
            color: var(--muted);
            font-size: 10px;
            letter-spacing: 2px;
            text-transform: uppercase;
        }

        .server-card h3 {
            margin: 14px 0 8px;
            font-size: 21px;
        }

        .server-card p {
            margin: 0;
            color: var(--muted);
            font-size: 12px;
        }

        .dot {
            display: inline-block;
            width: 7px;
            height: 7px;
            margin-right: 7px;
            border-radius: 50%;
            background: #9a9da4;
        }

        .news-section {
            background: #0e1117;
            border-top: 1px solid var(--line);
            border-bottom: 1px solid var(--line);
        }

        .news-grid {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 16px;
        }

        .news-card {
            min-height: 190px;
            padding: 24px;
            background: var(--panel);
            border: 1px solid var(--line);
            transition: border-color .2s ease;
        }

        .news-card:hover { border-color: var(--gold); }

        .news-card .date {
            color: var(--gold);
            font-size: 10px;
            letter-spacing: 2px;
            text-transform: uppercase;
        }

        .news-card h3 {
            margin: 14px 0 10px;
            font-size: 18px;
        }

        .news-card p {
            color: var(--muted);
            font-size: 12px;
        }

        .cta {
            text-align: center;
            padding: 75px 20px;
            background: radial-gradient(ellipse at center, #20202a 0%, #0b0d12 70%);
        }

        .cta h2 {
            margin: 12px 0;
            font-size: clamp(27px, 4vw, 42px);
        }

        .cta p {
            margin: 0 auto 25px;
            max-width: 530px;
            color: var(--muted);
            font-size: 13px;
        }

        footer {
            padding: 26px 0;
            border-top: 1px solid var(--line);
            color: #858b96;
            font-size: 11px;
        }

        .footer-community { display:flex; flex-wrap:wrap; gap:16px; }
        .footer-community a { color:var(--gold); }
        .footer-community a:hover { color:var(--text); }

        .footer-inner {
            display: flex;
            justify-content: space-between;
            gap: 15px;
            flex-wrap: wrap;
        }

        @media (max-width: 1000px) {
            .nav-links { gap: 13px; font-size: 11px; }
            .nav-actions .btn { padding: 0 12px; }
        }

        @media (max-width: 780px) {
            .nav-inner { min-height: 70px; flex-wrap: wrap; padding: 14px 0; }
            .menu-toggle { display: block; }
            .nav-links {
                display: none;
                width: 100%;
                flex-direction: column;
                align-items: stretch;
                gap: 0;
                order: 3;
            }
            .nav-links.open { display: flex; }
            .nav-links a { padding: 12px 0; border-top: 1px solid var(--line); }
            .nav-actions { margin-left: auto; }
            .server-grid, .news-grid { grid-template-columns: 1fr; }
            .hero { min-height: 490px; }
            .hero-content { padding: 70px 0; }
            .topbar-inner { font-size: 9px; }
            .topbar-inner span:last-child { text-align: right; }
        }

        @media (max-width: 430px) {
            .brand { font-size: 19px; }
            .brand small { font-size: 9px; }
            .nav-actions .btn { min-height: 38px; font-size: 10px; padding: 0 10px; }
            .hero-buttons .btn { width: 100%; }
        }


/* Shared news pages — use the exact Home palette and typography. */
.page-intro { padding: 54px 0 28px; }
.page-intro .eyebrow { margin-bottom: 8px; }
.page-intro h1 { margin: 10px 0; font-size: clamp(32px, 5vw, 46px); }
.page-intro p { color: var(--muted); font-size: 13px; }
.news-page-grid { display:grid; grid-template-columns:repeat(3,minmax(0,1fr)); gap:16px; padding-bottom:32px; }
.news-page-card { min-width:0; overflow:hidden; background:var(--panel); border:1px solid var(--line); transition:border-color .2s ease, transform .2s ease; }
.news-page-card:hover { border-color:var(--gold); transform:translateY(-2px); }
.news-page-cover, .news-page-placeholder { display:block; width:100%; aspect-ratio:16/9; object-fit:cover; background:#0b0d12; }
.news-page-placeholder { display:grid; place-items:center; color:var(--gold); font-family:'Cinzel',serif; font-size:25px; letter-spacing:4px; background:radial-gradient(ellipse at center,#24242d,#101319); }
.news-page-card-content { padding:22px; }
.news-page-card h2 { margin:10px 0; font-size:19px; overflow-wrap:anywhere; }
.news-page-card p { color:var(--muted); font-size:12px; }
.news-page-date { color:var(--muted); font-size:10px; letter-spacing:1px; }
.news-page-category { color:var(--gold); font-size:10px; letter-spacing:2px; text-transform:uppercase; }
.news-page-button { display:inline-flex; margin-top:10px; padding:10px 14px; border:1px solid var(--gold); color:var(--text); font-size:10px; font-weight:700; letter-spacing:1px; }
.news-page-button:hover { background:var(--gold); color:#111219; }
.news-page-empty { grid-column:1/-1; padding:28px; border:1px solid var(--line); background:var(--panel); color:var(--muted); }
.news-page-pagination { padding:10px 0 50px; color:var(--muted); }
.news-page-pagination nav { display:flex; justify-content:space-between; gap:12px; }
.news-page-pagination a, .news-page-pagination span { display:inline-block; padding:8px 12px; border:1px solid var(--line); font-size:12px; }
.news-page-pagination [aria-current="page"] span { background:var(--gold); color:#111219; }
.news-detail-layout { display:grid; grid-template-columns:minmax(0,1fr) 280px; align-items:start; gap:22px; padding-bottom:70px; }
.news-detail-card { min-width:0; background:var(--panel); border:1px solid var(--line); overflow:hidden; }
.news-detail-header { padding:28px; }
.news-detail-header h1 { margin:12px 0; font-size:clamp(25px,4vw,38px); overflow-wrap:anywhere; }
.news-detail-meta { color:var(--muted); font-size:11px; }
.news-detail-cover { display:block; width:100%; max-height:520px; aspect-ratio:16/9; object-fit:cover; border-top:1px solid var(--line); border-bottom:1px solid var(--line); }
.news-detail-body { padding:28px; color:#c5c9d2; font-size:14px; line-height:1.9; overflow-wrap:anywhere; }
.news-detail-excerpt { margin-bottom:24px; padding:16px 18px; border-left:3px solid var(--gold); background:rgba(200,170,112,.06); color:#e3d5b7; }
.news-detail-content p { margin:0 0 18px; }
.news-detail-content h2, .news-detail-content h3, .news-detail-content h4 { color:#e8d4aa; font-family:'Cinzel',serif; line-height:1.4; }
.news-detail-content a { color:var(--gold); text-decoration:underline; }
.news-detail-content img { max-width:100%; height:auto; }
.news-detail-footer { padding:20px 28px; border-top:1px solid var(--line); }
.news-detail-sidebar { display:grid; gap:16px; }
.news-sidebar-panel { padding:22px; background:var(--panel); border:1px solid var(--line); }
.news-sidebar-panel h2 { margin:0 0 15px; font-size:17px; }
.news-sidebar-item { display:block; padding:12px 0; border-bottom:1px solid var(--line); }
.news-sidebar-item:last-child { border-bottom:0; }
.news-sidebar-item strong { display:block; font-size:12px; }
.news-sidebar-item small { color:var(--muted); font-size:10px; }
@media(max-width:780px) {
  .news-page-grid { grid-template-columns:repeat(2,minmax(0,1fr)); }
  .news-detail-layout { grid-template-columns:1fr; }
}
@media(max-width:560px) {
  .news-page-grid { grid-template-columns:1fr; }
  .page-intro { padding-top:38px; }
  .news-detail-header, .news-detail-body { padding:20px; }
  .news-detail-footer { padding:18px 20px; }
}

.article-content {
    max-width: 820px;
    margin-inline: auto;
    color: #d4d7df;
    font-family: 'Inter', sans-serif;
    font-size: 15px;
    line-height: 1.95;
    overflow-wrap: anywhere;
}

.article-content > *:first-child {
    margin-top: 0;
}

.article-content p {
    margin: 0 0 1.35rem;
}

.article-content h1,
.article-content h2,
.article-content h3 {
    color: #f3f0e9;
    font-family: 'Cinzel', serif;
    line-height: 1.4;
    margin: 2rem 0 1rem;
}

.article-content h1 {
    font-size: clamp(1.8rem, 4vw, 2.4rem);
}

.article-content h2 {
    font-size: clamp(1.45rem, 3vw, 1.9rem);
    border-bottom: 1px solid rgba(200, 170, 112, .25);
    padding-bottom: .65rem;
}

.article-content h3 {
    font-size: 1.25rem;
}

.article-content strong {
    color: #f5e4bd;
    font-weight: 700;
}

.article-content a {
    color: #c8aa70;
    text-decoration: underline;
    text-underline-offset: 3px;
}

.article-content ul,
.article-content ol {
    margin: 0 0 1.5rem;
    padding-left: 1.5rem;
}

.article-content li {
    margin-bottom: .5rem;
    padding-left: .25rem;
}

.article-content blockquote {
    border-left: 3px solid #c8aa70;
    margin: 1.75rem 0;
    padding: .75rem 1.25rem;
    background: rgba(200, 170, 112, .06);
    color: #e3d4b5;
}

.article-content img {
    display: block;
    max-width: 100%;
    height: auto;
    margin: 1.75rem auto;
    border: 1px solid rgba(255,255,255,.12);
    border-radius: 8px;
}

.article-content table {
    width: 100%;
    margin: 1.5rem 0;
    border-collapse: collapse;
    display: block;
    overflow-x: auto;
}

.article-content th,
.article-content td {
    padding: .75rem 1rem;
    border: 1px solid rgba(255,255,255,.12);
    text-align: left;
}

.article-content th {
    background: rgba(200, 170, 112, .12);
    color: #f5e4bd;
}

@media (max-width: 640px) {
    .article-content {
        font-size: 14px;
        line-height: 1.85;
    }
}
    </style>
    @stack('head')
</head>
<body>
    @if (filled($aionPluginHooks['topbar'] ?? null))
        @include($aionPluginHooks['topbar'])
    @else
        <div class="topbar">
            <div class="container topbar-inner">
                <span>{{ $websiteSettings['server_name'] ?: $websiteSettings['site_name'] }} <span class="brand-gold">{{ $websiteSettings['server_version'] ? $websiteSettings['server_version'] . ' PTS' : 'PTS' }}</span></span>
                <span>{{ strtoupper($websiteSettings['tagline'] ?: 'OFFICIAL WEBSITE') }}</span>
            </div>
        </div>
    @endif

    <header class="navbar">
        <div class="container nav-inner">
            <a href="{{ url('/') }}" class="brand" aria-label="{{ $websiteSettings['site_name'] ?: 'AION PTS' }} home">
                @if (filled($websiteSettings['logo_path'] ?? ''))
                    <img src="{{ asset('storage/' . $websiteSettings['logo_path']) }}" alt="{{ $websiteSettings['site_name'] ?: 'AION PTS' }}" style="display:block;width:auto;max-width:220px;max-height:58px;object-fit:contain">
                @else
                    {{ $websiteSettings['site_name'] ?: 'AION PTS' }}
                    <small>{{ $websiteSettings['tagline'] ?: 'BY ZYVALLE' }}</small>
                @endif
            </a>

            <button class="menu-toggle" id="menuToggle"
                    aria-label="Open navigation" aria-expanded="false">
                MENU ☰
            </button>

            <nav class="nav-links" id="navLinks">
                @if ($aionFeatures['home'] ?? false)<a href="{{ route('home') }}">HOME</a>@endif
                @if ($aionFeatures['news'] ?? false)<a href="{{ route('news.index') }}">NEWS</a>@endif
                @if ($aionFeatures['info'] ?? false)<a href="{{ route('info') }}">INFO</a>@endif
                @if ($aionFeatures['download'] ?? false)<a href="{{ route('download') }}">DOWNLOAD</a>@endif
                @if ($aionFeatures['donation'] ?? false)<a href="{{ route('donation') }}">DONATION</a>@endif
                @if ($aionFeatures['support'] ?? false)<a href="{{ route('support') }}">SUPPORT</a>@endif
                @foreach (($aionPluginNavigation ?? []) as $pluginNav)
                    <a href="{{ $pluginNav['url'] }}">{{ strtoupper($pluginNav['label']) }}</a>
                @endforeach
            </nav>

            <div class="nav-actions">
                @if (session()->has('aion_account_username'))
                    <a href="{{ route('account') }}" class="btn btn-outline">MY ACCOUNT</a>
                    <form method="POST" action="{{ route('logout') }}" style="display:inline-flex;margin:0">
                        @csrf
                        <button type="submit" class="btn" style="cursor:pointer">LOG OUT</button>
                    </form>
                @else
                    @if ($aionFeatures['account.login'] ?? false)<a href="{{ route('login') }}" class="btn btn-outline">LOGIN</a>@endif
                    @if ($aionFeatures['account.registration'] ?? false)<a href="{{ route('register') }}" class="btn">REGISTER</a>@endif
                @endif
            </div>
        </div>
    </header>

    @yield('content')

    @if (filled($aionPluginHooks['footer'] ?? null))
        @include($aionPluginHooks['footer'])
    @else
    <footer>
        <div class="container footer-inner">
            <span>© {{ date('Y') }} {{ $websiteSettings['site_name'] ?: 'AION PTS' }} — {{ strtoupper($websiteSettings['tagline'] ?: 'BY ZYVALLE') }}</span>
            <span class="footer-community">
                @if (filled($websiteSettings['contact_email']))
                    <a href="mailto:{{ $websiteSettings['contact_email'] }}">SUPPORT</a>
                @endif
                @if (filled($websiteSettings['discord_url']))
                    <a href="{{ $websiteSettings['discord_url'] }}" target="_blank" rel="noopener noreferrer">DISCORD</a>
                @endif
                @if (filled($websiteSettings['facebook_url']))
                    <a href="{{ $websiteSettings['facebook_url'] }}" target="_blank" rel="noopener noreferrer">FACEBOOK</a>
                @endif
                @if (!filled($websiteSettings['contact_email']) && !filled($websiteSettings['discord_url']) && !filled($websiteSettings['facebook_url']))
                    COMMUNITY · ADVENTURE · ATREIA
                @endif
            </span>
        </div>
    </footer>
    @endif

<script>
        const menuToggle = document.getElementById('menuToggle');
        const navLinks = document.getElementById('navLinks');

        menuToggle.addEventListener('click', () => {
            const isOpen = navLinks.classList.toggle('open');
            menuToggle.setAttribute('aria-expanded', String(isOpen));
        });
    </script>
    @stack('scripts')
</body>
</html>
