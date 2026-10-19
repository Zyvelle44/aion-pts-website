@extends('layouts.app')

@section('title', (($websiteSettings['site_name'] ?? 'AION PTS') . ' — ' . ($websiteSettings['tagline'] ?? 'By Zyvalle')))
@section('meta_description', $websiteSettings['meta_description'] ?? 'Aion PTS By Zyvalle — Aion MMORPG 4.6 Private Test Server.')

@section('content')

<main>
        @php
            $slides = isset($heroSlides) && $heroSlides->isNotEmpty()
                ? $heroSlides
                : collect([[
                    'eyebrow' => 'A WORLD BEYOND THE SKY',
                    'title' => 'AION PTS',
                    'highlight' => 'BY ZYVALLE',
                    'description' => 'Discover the timeless world of Atreia. Two factions, one destiny. Begin your journey in AION 4.6 PTS.',
                    'image_path' => 'hero-slides/aion-hero.jpg',
                    'primary_label' => 'EXPLORE DOWNLOADS',
                    'primary_url' => route('download'),
                    'secondary_label' => 'LATEST CHRONICLES',
                    'secondary_url' => route('news.index'),
                ]]);
        @endphp

        <section class="hero aion-hero" aria-label="AION PTS featured introduction">
            <div class="aion-hero-slides" data-aion-slider>
                @foreach ($slides as $index => $slide)
                    @php
                        $imagePath = data_get($slide, 'image_path', 'hero-slides/aion-hero.jpg');
                        $imageUrl = is_string($imagePath) && preg_match('/^https?:\/\//i', $imagePath)
                            ? $imagePath
                            : (is_string($imagePath) && \Illuminate\Support\Facades\Storage::disk('public')->exists($imagePath)
                                ? \Illuminate\Support\Facades\Storage::disk('public')->url($imagePath)
                                : asset('storage/' . ($imagePath ?: 'hero-slides/aion-hero.jpg')));
                        $primaryUrl = data_get($slide, 'primary_url') ?: route('download');
                        $secondaryUrl = data_get($slide, 'secondary_url') ?: route('news.index');
                    @endphp

                    <article
                        class="aion-hero-slide {{ $index === 0 ? 'is-active' : '' }}"
                        data-slide
                        aria-hidden="{{ $index === 0 ? 'false' : 'true' }}"
                        style="--hero-image: url('{{ $imageUrl }}');"
                    >
                        <div class="aion-hero-image" aria-hidden="true"></div>
                        <div class="aion-hero-vignette" aria-hidden="true"></div>
                        <div class="aion-hero-particles" aria-hidden="true"></div>

                        <div class="container aion-hero-content">
                            <div class="aion-hero-copy">
                                <div class="aion-hero-kicker"><span></span>{{ data_get($slide, 'eyebrow', 'A WORLD BEYOND THE SKY') }}<span></span></div>
                                <h1>
                                    {{ data_get($slide, 'title', 'AION PTS') }}
                                    @if (filled(data_get($slide, 'highlight')))
                                        <em>{{ data_get($slide, 'highlight') }}</em>
                                    @endif
                                </h1>
                                <p>{{ data_get($slide, 'description', 'Discover the timeless world of Atreia. Two factions, one destiny. Begin your journey in AION 4.6 PTS.') }}</p>

                                @if (($aionFeatures['download'] ?? false) || ($aionFeatures['news'] ?? false))
                                    <div class="hero-buttons aion-hero-buttons">
                                        @if ($aionFeatures['download'] ?? false)
                                            <a href="{{ $primaryUrl }}" class="btn aion-primary-btn">
                                                {{ data_get($slide, 'primary_label', 'EXPLORE DOWNLOADS') }}
                                                <span aria-hidden="true">↗</span>
                                            </a>
                                        @endif
                                        @if ($aionFeatures['news'] ?? false)
                                            <a href="{{ $secondaryUrl }}" class="btn btn-outline aion-secondary-btn">
                                                {{ data_get($slide, 'secondary_label', 'LATEST CHRONICLES') }}
                                                <span aria-hidden="true">→</span>
                                            </a>
                                        @endif
                                    </div>
                                @endif

                                <div class="aion-hero-note">
                                    <span></span>
                                    <small>AION MMORPG 4.6 PTS</small>
                                    <b>/</b>
                                    <small>BY ZYVALLE</small>
                                </div>
                            </div>
                        </div>
                    </article>
                @endforeach
            </div>

            @if ($slides->count() > 1)
                <div class="aion-slider-controls" aria-label="Hero slider controls">
                    <div class="aion-slider-count"><span data-slide-current>01</span><i>/</i>{{ str_pad((string) $slides->count(), 2, '0', STR_PAD_LEFT) }}</div>
                    <div class="aion-slider-dots">
                        @foreach ($slides as $index => $slide)
                            <button type="button" data-slide-to="{{ $index }}" aria-label="Show slide {{ $index + 1 }}" class="{{ $index === 0 ? 'is-active' : '' }}"></button>
                        @endforeach
                    </div>
                    <div class="aion-slider-arrows">
                        <button type="button" data-slide-prev aria-label="Previous slide">←</button>
                        <button type="button" data-slide-next aria-label="Next slide">→</button>
                    </div>
                </div>
            @endif

            <div class="aion-hero-stats" aria-label="AION PTS server highlights">
                <div class="aion-stat">
                    <span class="aion-stat-icon" aria-hidden="true">♧</span>
                    <div><small>GAME VERSION</small><strong>{{ $websiteSettings['server_name'] ?: 'AION PTS' }} {{ $websiteSettings['server_version'] ?: '4.6' }} PTS</strong><p>{{ $websiteSettings['server_rate'] ? 'Rates: ' . $websiteSettings['server_rate'] . '.' : 'Classic fantasy MMORPG experience.' }}{{ $websiteSettings['level_cap'] ? ' Level cap: ' . $websiteSettings['level_cap'] . '.' : '' }}</p></div>
                </div>
                @php
                    $serverMetrics = $serverMetrics ?? [
                        'connection' => 'unknown',
                        'server_status' => 'UNKNOWN',
                        'server_status_detail' => 'Live status is not available.',
                        'online_players' => null,
                        'players_detail' => 'Live player statistics are unavailable.',
                    ];
                    $statusLabel = $serverMetrics['server_status'] ?? 'UNKNOWN';
                    $statusColor = $statusLabel === 'ONLINE' ? '#62d6a3' : '#a4a8b2';
                @endphp
                @if ($aionFeatures['server.status'] ?? false)
                    <div class="aion-stat">
                        <span class="aion-stat-icon aion-stat-orb" aria-hidden="true">✺</span>
                        <div>
                            <small>SERVER STATUS</small>
                            <strong data-live-server-status><i class="aion-status-dot" style="background:{{ $statusColor }}"></i> {{ $statusLabel }}</strong>
                            <p data-live-server-status-detail>{{ $serverMetrics['server_status_detail'] ?? 'Live status is not available.' }}</p>
                        </div>
                    </div>
                    <div class="aion-stat">
                        <span class="aion-stat-icon" aria-hidden="true">⚜</span>
                        <div>
                            <small>ONLINE PLAYERS</small>
                            <strong data-live-online-players>{{ is_numeric($serverMetrics['online_players'] ?? null) ? number_format((int) $serverMetrics['online_players']) : 'UNAVAILABLE' }}</strong>
                            <p data-live-players-detail>{{ $serverMetrics['players_detail'] ?? 'Live player statistics are unavailable.' }}</p>
                        </div>
                    </div>
                @endif
            </div>

            @if ($aionFeatures['news'] ?? false)
                <a class="aion-scroll-cue" href="#news" aria-label="Scroll to latest news">
                    <span>SCROLL TO EXPLORE</span><i></i>
                </a>
            @endif
        </section>

        @if ($aionFeatures['news'] ?? false)
        <section class="section news-section" id="news">
            <div class="container">
                <div class="section-heading">
                    <div class="eyebrow">From the world of Atreia</div>
                    <h2>LATEST NEWS</h2>
                    <p>Announcements and updates from the server team.</p>
                </div>
                
                <div class="news-grid aion-news-grid">
                    @forelse($latestNews as $article)
                        @php
                            $newsImage = $article->cover_image ?? null;
                            $newsImageUrl = $newsImage
                                ? (\Illuminate\Support\Facades\Storage::disk('public')->exists($newsImage)
                                    ? \Illuminate\Support\Facades\Storage::disk('public')->url($newsImage)
                                    : asset('storage/' . $newsImage))
                                : null;
                        @endphp
                        <article class="news-card aion-news-card">
                            <a class="aion-news-image" href="{{ route('news.show', $article->slug) }}" aria-label="Read {{ $article->title }}">
                                @if ($newsImageUrl)
                                    <img src="{{ $newsImageUrl }}" alt="{{ $article->title }}" loading="lazy" decoding="async">
                                @else
                                    <span class="aion-news-image-fallback" aria-hidden="true">
                                        <i></i><b>AION PTS</b><small>CHRONICLES OF ATREIA</small>
                                    </span>
                                @endif
                                <span class="aion-news-image-shade" aria-hidden="true"></span>
                                <span class="aion-news-category">{{ $article->category ?: 'Announcement' }}</span>
                            </a>
                            <div class="aion-news-card-content">
                                <div class="aion-news-meta">
                                    <span>{{ $article->published_at?->format('d M Y') ?: $article->created_at?->format('d M Y') }}</span>
                                    @if ($article->is_featured)
                                        <span class="aion-news-featured">FEATURED</span>
                                    @endif
                                </div>
                                <h3><a href="{{ route('news.show', $article->slug) }}">{{ $article->title }}</a></h3>
                                <p>{{ $article->excerpt ?: \Illuminate\Support\Str::limit(strip_tags($article->body), 120) }}</p>
                                <a href="{{ route('news.show', $article->slug) }}" class="aion-news-read">READ CHRONICLE <span aria-hidden="true">↗</span></a>
                            </div>
                        </article>
                    @empty
                        <article class="news-card aion-news-card aion-news-empty">
                            <div class="aion-news-image">
                                <span class="aion-news-image-fallback" aria-hidden="true">
                                    <i></i><b>AION PTS</b><small>CHRONICLES OF ATREIA</small>
                                </span>
                            </div>
                            <div class="aion-news-card-content">
                                <div class="aion-news-meta"><span>ANNOUNCEMENTS</span></div>
                                <h3>Welcome to AION PTS</h3>
                                <p>News and server announcements will appear here when published.</p>
                            </div>
                        </article>
                    @endforelse
                </div>
                <div style="margin-top:24px">
                    <a href="{{ route('news.index') }}" class="btn btn-outline">
                        VIEW ALL NEWS →
                    </a>
                </div>

            </div>
        </section>
        @endif

        @php
            $homeExploreCards = [
                [
                    'key' => 'info', 'route' => 'info', 'eyebrow' => 'KNOW ATREIA',
                    'title' => 'SERVER INFORMATION', 'description' => 'Review server status, game version, factions, and published configuration.',
                    'mark' => '✧', 'action' => 'EXPLORE SERVER',
                ],
                [
                    'key' => 'download', 'route' => 'download', 'eyebrow' => 'PREPARE YOUR CLIENT',
                    'title' => 'DOWNLOAD CENTER', 'description' => 'Find official client files, patches, and installation information when available.',
                    'mark' => '↧', 'action' => 'VIEW DOWNLOADS',
                ],
                [
                    'key' => 'donation', 'route' => 'donation', 'eyebrow' => 'SUPPORT THE REALM',
                    'title' => 'DONATION', 'description' => 'View the support options configured by the server team.',
                    'mark' => '✦', 'action' => 'SUPPORT AION PTS',
                ],
                [
                    'key' => 'support', 'route' => 'support', 'eyebrow' => 'NEED ASSISTANCE',
                    'title' => 'PLAYER SUPPORT', 'description' => 'Find help and answers to common questions about the server.',
                    'mark' => '?', 'action' => 'VISIT SUPPORT',
                ],
            ];
            $availableExploreCards = collect($homeExploreCards)
                ->filter(fn (array $card) => $aionFeatures[$card['key']] ?? false)
                ->values();
        @endphp

        @if ($availableExploreCards->isNotEmpty())
            <section class="section aion-home-explore" aria-labelledby="aion-home-explore-title">
                <div class="container">
                    <div class="section-heading">
                        <div class="eyebrow">Your next step in Atreia</div>
                        <h2 id="aion-home-explore-title">EXPLORE AION PTS</h2>
                        <p>Everything you need to prepare, discover the server, and get support for your journey.</p>
                    </div>
                    <div class="aion-home-explore-grid">
                        @foreach ($availableExploreCards as $card)
                            <article class="aion-home-explore-card">
                                <div class="aion-home-explore-top">
                                    <span class="aion-home-explore-mark" aria-hidden="true">{{ $card['mark'] }}</span>
                                    <span class="aion-home-explore-eyebrow">{{ $card['eyebrow'] }}</span>
                                </div>
                                <h3>{{ $card['title'] }}</h3>
                                <p>{{ $card['description'] }}</p>
                                <a href="{{ route($card['route']) }}" class="aion-home-explore-link">{{ $card['action'] }} <span aria-hidden="true">↗</span></a>
                            </article>
                        @endforeach
                    </div>
                </div>
            </section>
        @endif

        @if (($aionFeatures['download'] ?? false) || ($aionFeatures['news'] ?? false))
        @php
            $ctaSlide = $slides->first();
            $ctaImagePath = data_get($ctaSlide, 'image_path');
            $ctaImageUrl = is_string($ctaImagePath) && preg_match('/^https?:\\/\\//i', $ctaImagePath)
                ? $ctaImagePath
                : (is_string($ctaImagePath) && $ctaImagePath !== ''
                    ? (\Illuminate\Support\Facades\Storage::disk('public')->exists($ctaImagePath)
                        ? \Illuminate\Support\Facades\Storage::disk('public')->url($ctaImagePath)
                        : asset('storage/' . $ctaImagePath))
                    : null);
        @endphp
        <section class="cta aion-adventure-cta"
            @if ($ctaImageUrl)
                style="--cta-art: url('{{ $ctaImageUrl }}')"
            @endif
        >
            <div class="aion-cta-frame">
                <div class="eyebrow">Your adventure starts now</div>
                <h2>ANSWER THE CALL <em>OF ATREIA</em></h2>
                <p>Prepare your client and follow the latest announcements before your journey begins.</p>
                <div class="aion-cta-actions">
                    @if ($aionFeatures['download'] ?? false)
                        <a href="{{ route('download') }}" class="btn aion-primary-btn">PREPARE YOUR JOURNEY <span aria-hidden="true">↗</span></a>
                    @endif
                    @if ($aionFeatures['news'] ?? false)
                        <a href="{{ route('news.index') }}" class="btn btn-outline aion-secondary-btn">READ CHRONICLES <span aria-hidden="true">→</span></a>
                    @endif
                </div>
            </div>
        </section>
        @endif
    </main>
@endsection


@push('head')
<style>
    .atreia-hero {
        min-height: min(790px, calc(100svh - 115px));
        min-height: clamp(650px, 78vh, 820px);
        isolation: isolate;
        background: #080b13;
    }
    .atreia-hero::before { z-index: 2; opacity: .28; }
    .atreia-hero::after {
        content: ""; position:absolute; z-index:1; inset:0; pointer-events:none;
        background: linear-gradient(90deg,rgba(6,8,14,.97) 0%,rgba(7,10,17,.86) 37%,rgba(7,10,17,.28) 72%,rgba(7,10,17,.32) 100%),
                    linear-gradient(0deg,#0b0d12 0%,transparent 27%,rgba(7,9,15,.12) 100%);
    }
    .atreia-scene { position:absolute; inset:0; overflow:hidden; pointer-events:none; }
    .atreia-sky-glow { position:absolute; width:70vw; height:70vw; max-width:1000px; max-height:1000px; right:-4%; top:-37%; border-radius:50%; background:radial-gradient(circle,rgba(120,145,190,.18),rgba(76,92,133,.06) 38%,transparent 70%); animation:atreia-breathe 9s ease-in-out infinite alternate; }
    .atreia-moon { position:absolute; width:clamp(180px,26vw,360px); aspect-ratio:1; right:15%; top:12%; border-radius:50%; background:radial-gradient(circle at 35% 30%,rgba(236,229,205,.92),rgba(170,177,192,.55) 36%,rgba(107,126,158,.22) 61%,transparent 72%); box-shadow:0 0 45px rgba(167,184,218,.14),0 0 140px rgba(111,137,192,.13); opacity:.72; animation:atreia-moon-float 12s ease-in-out infinite alternate; }
    .atreia-moon::after { content:""; position:absolute; inset:-9%; border:1px solid rgba(209,195,156,.13); border-radius:50%; transform:rotate(-19deg) scaleY(.36); }
    .atreia-stars { position:absolute; inset:-20%; opacity:.5; background-image:radial-gradient(1px 1px at 12% 22%,#fff 99%,transparent),radial-gradient(1px 1px at 42% 17%,#e8d8b0 99%,transparent),radial-gradient(1.5px 1.5px at 71% 34%,#fff 99%,transparent),radial-gradient(1px 1px at 82% 11%,#c6d6f4 99%,transparent),radial-gradient(1px 1px at 59% 65%,#fff 99%,transparent),radial-gradient(1px 1px at 92% 76%,#e8d8b0 99%,transparent); background-size:390px 310px; animation:atreia-star-drift 65s linear infinite; }
    .atreia-stars-two { opacity:.25; transform:scale(1.25); background-size:530px 420px; animation-duration:100s; animation-direction:reverse; }
    .atreia-mountain { position:absolute; bottom:7%; width:80%; height:48%; clip-path:polygon(0 100%,9% 57%,17% 72%,29% 22%,37% 52%,49% 5%,59% 49%,67% 28%,77% 65%,87% 38%,100% 100%); }
    .atreia-mountain-back { right:-7%; bottom:17%; background:linear-gradient(150deg,rgba(93,108,135,.28),rgba(30,40,61,.6) 62%,rgba(12,18,31,.9)); filter:blur(1px); transform:scale(1.1); }
    .atreia-mountain-mid { right:-2%; bottom:8%; width:78%; height:39%; background:linear-gradient(155deg,rgba(72,83,109,.55),rgba(23,31,48,.94) 66%,#0b0d12); clip-path:polygon(0 100%,13% 57%,23% 73%,35% 31%,43% 56%,55% 13%,66% 62%,75% 36%,85% 67%,94% 48%,100% 100%); }
    .atreia-mountain-front { left:20%; bottom:-1%; width:90%; height:29%; background:linear-gradient(180deg,#171c29,#0b0d12 78%); clip-path:polygon(0 100%,0 77%,14% 59%,24% 75%,38% 33%,50% 69%,65% 43%,75% 65%,89% 31%,100% 61%,100% 100%); }
    .atreia-ground { position:absolute; left:0; right:0; bottom:0; height:17%; background:linear-gradient(0deg,#0b0d12 8%,rgba(11,13,18,.92) 37%,transparent); }
    .atreia-ruins { position:absolute; bottom:17%; width:120px; height:230px; opacity:.5; filter:drop-shadow(0 0 12px rgba(170,155,116,.08)); }
    .atreia-ruins-left { left:54%; transform:scale(.7); transform-origin:bottom; }
    .atreia-ruins-right { right:4%; bottom:13%; transform:scale(1.08); transform-origin:bottom; opacity:.32; }
    .atreia-ruins::before { content:""; position:absolute; left:20px; bottom:0; width:18px; height:100%; background:linear-gradient(90deg,#171d2b,#596075 50%,#171d2b); clip-path:polygon(22% 0,78% 0,100% 8%,84% 8%,84% 100%,16% 100%,16% 8%,0 8%); }
    .atreia-ruins::after { content:""; position:absolute; left:5px; bottom:76%; width:50px; height:15px; border:2px solid #4b5264; border-bottom:0; transform:skewY(-12deg); }
    .atreia-ruins i { position:absolute; bottom:0; width:12px; height:58%; background:linear-gradient(90deg,#111723,#454d61,#151b28); }
    .atreia-ruins i:nth-child(1) { left:62px; height:76%; }
    .atreia-ruins i:nth-child(2) { left:86px; height:48%; }
    .atreia-ruins i:nth-child(3) { left:105px; height:65%; }
    .atreia-hero-inner { position:relative; z-index:3; min-height:inherit; display:flex; align-items:center; }
    .atreia-hero-content { max-width:650px; padding:100px 0 120px; animation:atreia-content-enter 1.1s cubic-bezier(.2,.7,.2,1) both; }
    .atreia-kicker { display:flex; align-items:center; gap:12px; margin-bottom:23px; color:#c8cbd5; font-size:9px; letter-spacing:3.2px; font-weight:700; }
    .atreia-kicker span { display:block; width:28px; height:1px; background:linear-gradient(90deg,transparent,var(--gold)); }
    .atreia-kicker span:last-child { transform:rotate(180deg); }
    .atreia-hero .eyebrow { letter-spacing:4.5px; }
    .atreia-hero h1 { margin:17px 0 22px; font-size:clamp(48px,7.4vw,92px); line-height:.99; letter-spacing:1.5px; text-shadow:0 8px 35px rgba(0,0,0,.42); }
    .atreia-hero h1 em { color:#d8c18d; font-style:normal; text-shadow:0 0 36px rgba(200,170,112,.19); }
    .atreia-hero-content > p { max-width:490px; color:#c0c4cf; font-size:14px; line-height:1.95; }
    .atreia-hero-buttons { margin-top:29px; gap:13px; }
    .atreia-primary-btn { gap:18px; box-shadow:0 8px 28px rgba(200,170,112,.12); }
    .atreia-primary-btn span { font-size:16px; }
    .atreia-secondary-btn { border-color:rgba(224,218,201,.3); background:rgba(11,13,18,.26); backdrop-filter:blur(8px); }
    .atreia-hero-note { display:flex; align-items:center; gap:12px; margin-top:31px; font-size:9px; letter-spacing:2px; color:#a2a8b6; }
    .atreia-note-line { display:block; width:32px; height:1px; background:var(--gold); }
    .atreia-note-separator { color:var(--gold); }
    .atreia-side-mark { position:absolute; right:3%; top:50%; display:flex; align-items:center; gap:13px; transform:translateY(-50%) rotate(90deg); transform-origin:right center; color:rgba(224,218,201,.65); letter-spacing:5px; font:10px 'Cinzel',serif; }
    .atreia-side-mark i { width:60px; height:1px; background:rgba(200,170,112,.55); }
    .atreia-side-mark small { font:8px 'Inter',sans-serif; letter-spacing:2px; }
    .atreia-scroll-cue { position:absolute; right:20px; bottom:31px; display:flex; align-items:center; gap:13px; color:#9da3b1; font-size:8px; letter-spacing:2px; writing-mode:vertical-rl; }
    .atreia-scroll-cue i { display:block; height:44px; width:1px; background:linear-gradient(var(--gold),transparent); transform-origin:top; animation:atreia-scroll-pulse 1.8s ease-in-out infinite; }
    .atreia-hero .hero-buttons .btn { min-height:48px; }
    .atreia-hero .btn:focus-visible, .atreia-scroll-cue:focus-visible { outline:2px solid #f0dba9; outline-offset:4px; }
    .atreia-hero .btn:hover { box-shadow:0 0 24px rgba(200,170,112,.18); }
    @keyframes atreia-breathe { from { transform:scale(.94); opacity:.7; } to { transform:scale(1.08); opacity:1; } }
    @keyframes atreia-moon-float { from { transform:translate3d(0,0,0); } to { transform:translate3d(-12px,12px,0); } }
    @keyframes atreia-star-drift { from { transform:translate3d(0,0,0); } to { transform:translate3d(-70px,35px,0); } }
    @keyframes atreia-content-enter { from { opacity:0; transform:translateY(18px); } to { opacity:1; transform:translateY(0); } }
    @keyframes atreia-scroll-pulse { 0%,100% { transform:scaleY(.65); opacity:.45; } 50% { transform:scaleY(1); opacity:1; } }
    @media(max-width:780px) {
        .atreia-hero { min-height:680px; }
        .atreia-hero-inner { align-items:flex-end; }
        .atreia-hero-content { padding:105px 0 105px; }
        .atreia-moon { right:-1%; top:10%; width:clamp(180px,48vw,280px); }
        .atreia-mountain { width:110%; }
        .atreia-ruins-left { left:57%; transform:scale(.55); }
        .atreia-side-mark { display:none; }
        .atreia-scroll-cue { right:5px; bottom:20px; }
        .atreia-hero::after { background:linear-gradient(90deg,rgba(6,8,14,.92),rgba(7,10,17,.57)),linear-gradient(0deg,#0b0d12 0%,transparent 35%); }
    }
    @media(max-width:430px) {
        .atreia-hero { min-height:650px; }
        .atreia-hero-content { padding:88px 0 95px; }
        .atreia-kicker { font-size:8px; letter-spacing:2px; }
        .atreia-hero h1 { font-size:clamp(43px,13vw,61px); }
        .atreia-hero-content > p { font-size:13px; }
        .atreia-hero-buttons { align-items:stretch; flex-direction:column; max-width:290px; }
        .atreia-hero-buttons .btn { width:100%; }
        .atreia-hero-note { flex-wrap:wrap; gap:8px; font-size:8px; }
        .atreia-ruins-right { right:-15%; }
    }
    @media(prefers-reduced-motion:reduce) {
        .atreia-hero *, .atreia-hero *::before, .atreia-hero *::after { animation:none !important; scroll-behavior:auto !important; }
    }

    /* Admin-editable AION hero slider */
    .aion-hero { position:relative; min-height:max(clamp(650px,82vh,790px), var(--aion-dynamic-height, 0px)); isolation:isolate; background:#080b13; }
    .aion-hero-slides { touch-action:pan-y; position:absolute; inset:0; }
    .aion-hero-slide { position:absolute; inset:0; overflow:hidden; opacity:0; visibility:hidden; transition:opacity .9s ease, visibility .9s ease; }
    .aion-hero-slide.is-active { opacity:1; visibility:visible; z-index:1; }
    .aion-hero-image { position:absolute; inset:0; background-image:var(--hero-image); background-size:cover; background-position:center 38%; transform:scale(1.025); transition:transform 8s ease; }
    .aion-hero-slide.is-active .aion-hero-image { transform:scale(1.09); }
    .aion-hero-vignette { position:absolute; inset:0; background:linear-gradient(90deg,rgba(5,8,14,.94) 0%,rgba(5,8,14,.80) 27%,rgba(5,8,14,.40) 48%,rgba(5,8,14,.06) 75%),linear-gradient(0deg,rgba(5,8,14,.78),transparent 36%,rgba(5,8,14,.12)); }
    .aion-hero-particles { position:absolute; inset:0; opacity:.5; pointer-events:none; background-image:radial-gradient(1px 1px at 14% 23%,#f4dfad 98%,transparent),radial-gradient(2px 2px at 38% 72%,#d6e5ff 98%,transparent),radial-gradient(1px 1px at 53% 24%,#fff 98%,transparent),radial-gradient(1px 1px at 78% 54%,#f4dfad 98%,transparent); background-size:230px 190px,330px 270px,290px 220px,410px 300px; animation:aion-particle-drift 38s linear infinite; }
    .aion-hero-content { position:relative; z-index:2; min-height:inherit; display:flex; align-items:flex-start; padding-top:clamp(105px,13vh,135px); padding-bottom:300px; }
    .aion-hero-copy { width:min(100%,520px); max-width:520px; min-width:0; animation:aion-hero-enter .9s ease both; }
    .aion-hero-kicker { display:flex; align-items:center; gap:12px; color:#e1d4b7; font-size:9px; font-weight:700; letter-spacing:3px; margin-bottom:20px; }
    .aion-hero-kicker span { width:25px; height:1px; background:#d3b575; }
    .aion-hero-copy h1 { display:flex; flex-direction:column; align-items:flex-start; gap:0; margin:0 0 18px; max-width:510px; font-family:'Cinzel',serif; font-size:clamp(42px,5.2vw,68px); line-height:1.08; letter-spacing:1px; text-shadow:0 4px 28px rgba(0,0,0,.38); }
    .aion-hero-copy h1 em { color:#d9bd80; font-size:.78em; font-style:normal; letter-spacing:3px; }
    .aion-hero-copy p { max-width:430px; margin:0; color:#e0e2e8; font-size:14px; line-height:1.9; text-shadow:0 2px 14px rgba(0,0,0,.7); }
    .aion-hero-buttons { margin-top:27px; gap:12px; }
    .aion-primary-btn { display:inline-flex; align-items:center; gap:18px; box-shadow:0 8px 26px rgba(200,170,112,.17); }
    .aion-primary-btn span,.aion-secondary-btn span { font-size:16px; }
    .aion-secondary-btn { display:inline-flex; align-items:center; gap:18px; background:rgba(8,11,18,.48); border-color:rgba(255,255,255,.3); backdrop-filter:blur(8px); }
    .aion-hero-note { display:flex; align-items:center; gap:11px; margin-top:28px; color:#c4c5cc; font-size:9px; letter-spacing:2px; }
    .aion-hero-note>span { width:29px; height:1px; background:#c8aa70; }
    .aion-hero-note b { color:#c8aa70; font-weight:400; }
    .aion-slider-controls { position:absolute; z-index:4; left:max(4%,calc((100% - 1160px)/2)); right:max(4%,calc((100% - 1160px)/2)); bottom:27px; display:flex; align-items:center; gap:18px; }
    .aion-slider-count { color:#dfc58d; font:11px 'Cinzel',serif; letter-spacing:1px; white-space:nowrap; }
    .aion-slider-count i { margin:0 7px; color:#7d8290; font-style:normal; }
    .aion-slider-dots { display:flex; align-items:center; gap:7px; }
    .aion-slider-dots button { width:28px; height:2px; border:0; padding:0; background:rgba(255,255,255,.35); cursor:pointer; transition:width .25s,background .25s; }
    .aion-slider-dots button.is-active { width:48px; background:#d5b879; }
    .aion-slider-arrows { display:flex; gap:8px; margin-left:auto; }
    .aion-slider-arrows button { width:38px; height:38px; border:1px solid rgba(220,201,159,.6); border-radius:50%; background:rgba(8,11,18,.32); color:#f4e8c9; font-size:19px; cursor:pointer; transition:background .2s,color .2s; }
    .aion-slider-arrows button:hover { background:#d3b575; color:#10131a; }
    .aion-scroll-cue { position:absolute; z-index:4; left:50%; bottom:29px; transform:translateX(-50%); display:flex; align-items:center; gap:10px; color:#d5d4d0; font-size:8px; letter-spacing:2px; white-space:nowrap; }
    .aion-scroll-cue i { width:1px; height:24px; background:linear-gradient(#d3b575,transparent); }
    .aion-hero-stats {
        position:absolute; z-index:4; left:max(4%,calc((100% - 1160px)/2));
        right:max(4%,calc((100% - 1160px)/2)); bottom:88px;
        display:grid; grid-template-columns:repeat(3,minmax(0,1fr));
        background:rgba(8,12,20,.78); border:1px solid rgba(215,190,137,.34);
        box-shadow:0 16px 50px rgba(0,0,0,.32); backdrop-filter:blur(12px);
    }
    .aion-stat { min-width:0; display:flex; align-items:center; gap:16px; padding:17px 22px; position:relative; }
    .aion-stat + .aion-stat { border-left:1px solid rgba(215,190,137,.22); }
    .aion-stat-icon { flex:0 0 42px; display:grid; place-items:center; color:#d7bd7f; font-size:29px; text-shadow:0 0 20px rgba(215,189,127,.35); }
    .aion-stat-orb { color:#69c7ff; text-shadow:0 0 20px rgba(70,177,255,.65); }
    .aion-stat small { display:block; margin-bottom:5px; color:#a6a8b0; font-size:8px; letter-spacing:1.7px; }
    .aion-stat strong { display:block; color:#f0e7d4; font:500 15px/1.25 'Cinzel',serif; }
    .aion-stat p { margin:5px 0 0; color:#aeb3bf; font-size:10px; line-height:1.4; }
    .aion-status-dot { display:inline-block; width:6px; height:6px; margin:0 5px 2px 0; border-radius:50%; background:#a4a8b2; }
    @keyframes aion-hero-enter { from {opacity:0;transform:translateY(16px)} to {opacity:1;transform:translateY(0)} }
    @keyframes aion-particle-drift { from {background-position:0 0,0 0,0 0,0 0} to {background-position:-180px 120px,140px -100px,-120px 160px,200px -130px} }
    @media(max-width:780px) {
        .aion-hero { min-height:max(760px, var(--aion-dynamic-height, 0px)); }
        .aion-hero-image { background-position:58% center; }
        .aion-hero-vignette { background:linear-gradient(90deg,rgba(5,8,14,.89),rgba(5,8,14,.56) 72%,rgba(5,8,14,.32)),linear-gradient(0deg,rgba(5,8,14,.85),transparent 55%); }
        .aion-hero-content { align-items:flex-start; padding-top:105px; padding-bottom:340px; }
        .aion-hero-copy { max-width:480px; }
        .aion-hero-copy h1 { font-size:clamp(40px,9vw,58px); }
        .aion-hero-stats { left:4%; right:4%; bottom:82px; }
        .aion-stat { padding:12px 10px; gap:8px; }
        .aion-stat-icon { flex-basis:25px; font-size:21px; }
        .aion-stat strong { font-size:11px; }
        .aion-stat p { font-size:9px; }
        .aion-slider-controls { left:4%; right:4%; bottom:20px; gap:10px; }
        .aion-scroll-cue { display:none; }
    }
    @media(max-width:430px) {
        .aion-hero { min-height:max(800px, var(--aion-dynamic-height, 0px)); }
        .aion-hero-content { padding-top:100px; padding-bottom:390px; }
        .aion-hero-copy h1 { font-size:clamp(38px,11vw,50px); }
        .aion-hero-copy p { font-size:13px; }
        .aion-hero-buttons { flex-direction:column; align-items:stretch; max-width:300px; }
        .aion-hero-buttons .btn { width:100%; }
        .aion-hero-stats { grid-template-columns:1fr; bottom:74px; }
        .aion-stat { padding:8px 12px; }
        .aion-stat + .aion-stat { border-left:0; border-top:1px solid rgba(215,190,137,.18); }
        .aion-stat-icon { flex-basis:28px; }
        .aion-stat p { display:none; }
        .aion-hero-note { flex-wrap:wrap; font-size:8px; }
        .aion-slider-count { font-size:10px; }
        .aion-slider-dots button { width:17px; }
        .aion-slider-dots button.is-active { width:30px; }
        .aion-slider-arrows button { width:32px; height:32px; }
    }
    @media(prefers-reduced-motion:reduce) {
        .aion-hero *, .aion-hero *::before, .aion-hero *::after { animation:none !important; transition:none !important; }
    }


    /* Cinematic latest-news cards with cover art from the News CMS */
    .aion-news-grid { align-items:stretch; gap:20px; }
    .aion-news-card { display:flex; flex-direction:column; min-width:0; padding:0; overflow:hidden; background:linear-gradient(155deg,#151a24 0%,#0d1017 100%); border:1px solid rgba(218,199,155,.14); transition:transform .28s ease,border-color .28s ease,box-shadow .28s ease; }
    .aion-news-card:hover { transform:translateY(-5px); border-color:rgba(200,170,112,.62); box-shadow:0 18px 42px rgba(0,0,0,.25); }
    .aion-news-image { position:relative; display:block; height:clamp(170px,19vw,230px); overflow:hidden; background:#111722; }
    .aion-news-image img { display:block; width:100%; height:100%; object-fit:cover; transition:transform .7s cubic-bezier(.2,.7,.2,1),filter .5s ease; filter:saturate(.82) brightness(.8); }
    .aion-news-card:hover .aion-news-image img { transform:scale(1.055); filter:saturate(1) brightness(.98); }
    .aion-news-image-shade { position:absolute; inset:0; background:linear-gradient(0deg,rgba(7,9,14,.82),transparent 65%),linear-gradient(90deg,rgba(7,9,14,.18),transparent); pointer-events:none; }
    .aion-news-category { position:absolute; left:18px; bottom:15px; padding:6px 9px; color:#e6d1a0; background:rgba(8,11,17,.72); border:1px solid rgba(200,170,112,.4); backdrop-filter:blur(8px); font-size:9px; font-weight:700; letter-spacing:1.6px; text-transform:uppercase; }
    .aion-news-image-fallback { position:absolute; inset:0; display:flex; flex-direction:column; align-items:center; justify-content:center; gap:8px; color:#d8c18d; background:radial-gradient(ellipse at 50% 30%,rgba(89,113,157,.28),transparent 58%),linear-gradient(135deg,#151c2a,#090c13 72%); }
    .aion-news-image-fallback::before,.aion-news-image-fallback::after { content:""; position:absolute; width:180px; height:180px; border:1px solid rgba(200,170,112,.16); border-radius:50%; transform:scaleY(.45) rotate(-25deg); }
    .aion-news-image-fallback::after { width:225px; height:225px; transform:scaleY(.45) rotate(35deg); border-color:rgba(175,195,225,.12); }
    .aion-news-image-fallback i { width:32px; height:1px; background:#c8aa70; }
    .aion-news-image-fallback b { z-index:1; font:600 24px 'Cinzel',serif; letter-spacing:3px; }
    .aion-news-image-fallback small { z-index:1; font-size:8px; letter-spacing:2.5px; color:#9ca7ba; }
    .aion-news-card-content { display:flex; flex:1; flex-direction:column; align-items:flex-start; padding:22px 22px 24px; }
    .aion-news-meta { display:flex; flex-wrap:wrap; align-items:center; gap:10px; color:#8f98aa; font-size:9px; letter-spacing:1.4px; text-transform:uppercase; }
    .aion-news-featured { color:#d8c18d; }
    .aion-news-card h3 { margin:14px 0 10px; font-size:clamp(17px,1.55vw,21px); line-height:1.4; }
    .aion-news-card h3 a { color:#f1eee7; transition:color .2s ease; }
    .aion-news-card h3 a:hover { color:#d8c18d; }
    .aion-news-card-content p { margin:0 0 20px; color:#aeb5c3; font-size:12px; line-height:1.85; }
    .aion-news-read { display:inline-flex; align-items:center; gap:12px; margin-top:auto; padding-top:5px; color:#d8c18d; font-size:10px; font-weight:700; letter-spacing:1.4px; }
    .aion-news-read span { transition:transform .2s ease; }
    .aion-news-read:hover span { transform:translate(3px,-3px); }
    .aion-news-empty { min-height:320px; }
    /* CTA uses the current hero artwork as a subtle cinematic backdrop */
    .aion-adventure-cta { position:relative; isolation:isolate; overflow:hidden; margin-top:0; padding:clamp(58px,8vw,105px) 20px; background:#0b0d12; border-top:1px solid rgba(255,255,255,.08); border-bottom:1px solid rgba(255,255,255,.08); }
    .aion-adventure-cta::before { content:""; position:absolute; z-index:-2; inset:0; background-image:var(--cta-art); background-position:center 42%; background-size:cover; opacity:.33; transform:scale(1.03); }
    .aion-adventure-cta::after { content:""; position:absolute; z-index:-1; inset:0; background:linear-gradient(90deg,rgba(6,8,14,.96),rgba(8,11,18,.72) 50%,rgba(6,8,14,.92)),linear-gradient(0deg,#0b0d12,transparent 45%,#0b0d12); }
    .aion-cta-frame { width:min(860px,100%); margin:auto; padding:clamp(25px,4vw,48px); text-align:center; border:1px solid rgba(200,170,112,.28); background:rgba(8,11,17,.48); box-shadow:0 20px 60px rgba(0,0,0,.2); backdrop-filter:blur(4px); }
    .aion-adventure-cta h2 { margin:14px 0; font-size:clamp(27px,4.2vw,46px); line-height:1.2; }
    .aion-adventure-cta h2 em { color:#d8c18d; font-style:normal; }
    .aion-adventure-cta p { max-width:560px; margin:0 auto 25px; color:#c0c4cf; font-size:13px; line-height:1.9; }
    .aion-cta-actions { display:flex; justify-content:center; flex-wrap:wrap; gap:12px; }
    .aion-cta-actions .btn { min-height:46px; }
    @media(max-width:780px) {
        .aion-news-grid { grid-template-columns:repeat(2,minmax(0,1fr)); }
        .aion-news-image { height:190px; }
        .aion-news-card-content { padding:18px; }
        .aion-adventure-cta { padding:55px 16px; }
    }
    @media(max-width:560px) {
        .aion-news-grid { grid-template-columns:1fr; }
        .aion-news-image { height:clamp(190px,58vw,270px); }
        .aion-news-card h3 { font-size:19px; }
        .aion-cta-frame { padding:25px 18px; }
        .aion-cta-actions { flex-direction:column; }
        .aion-cta-actions .btn { width:100%; }
    }

    .aion-home-explore { padding-top: 64px; padding-bottom: clamp(52px, 6vw, 76px); }
    .aion-home-explore .section-heading { margin-bottom: 24px; }
    .aion-home-explore-grid { display:grid; grid-template-columns:repeat(4,minmax(0,1fr)); gap:14px; }
    .aion-home-explore-card { min-width:0; display:flex; flex-direction:column; padding:22px; border:1px solid rgba(200,170,112,.2); background:linear-gradient(145deg,rgba(25,30,41,.96),rgba(12,15,21,.98)); transition:border-color .2s ease,transform .2s ease,background .2s ease; }
    .aion-home-explore-card:hover { border-color:rgba(200,170,112,.48); transform:translateY(-3px); background:linear-gradient(145deg,rgba(31,37,50,.98),rgba(12,15,21,.98)); }
    .aion-home-explore-top { display:flex; align-items:center; gap:11px; min-height:38px; }
    .aion-home-explore-mark { display:grid; place-items:center; flex:0 0 36px; width:36px; height:36px; border:1px solid rgba(200,170,112,.38); color:var(--gold); font:21px 'Cinzel',serif; }
    .aion-home-explore-eyebrow { color:var(--muted); font-size:9px; line-height:1.5; letter-spacing:1.35px; }
    .aion-home-explore-card h3 { margin:22px 0 10px; font:600 16px/1.45 'Cinzel',serif; letter-spacing:.3px; }
    .aion-home-explore-card p { flex:1; margin:0 0 22px; color:var(--muted); font-size:11px; line-height:1.85; }
    .aion-home-explore-link { display:flex; align-items:center; justify-content:space-between; gap:10px; padding-top:13px; border-top:1px solid var(--line); color:var(--gold); font-size:9px; font-weight:700; letter-spacing:1.2px; }
    .aion-home-explore-link span { font-size:15px; transition:transform .2s ease; }
    .aion-home-explore-link:hover span { transform:translate(2px,-2px); }
    @media(max-width:1000px) { .aion-home-explore-grid { grid-template-columns:repeat(2,minmax(0,1fr)); } }
    @media(max-width:560px) { .aion-home-explore { padding-top:46px; padding-bottom:48px; } .aion-home-explore-grid { grid-template-columns:1fr; gap:10px; } .aion-home-explore-card { padding:19px; } .aion-home-explore-card h3 { margin-top:16px; } }
    @media(prefers-reduced-motion:reduce) { .aion-home-explore-card,.aion-home-explore-link span { transition:none; } .aion-home-explore-card:hover { transform:none; } }
</style>
    <script>
    document.addEventListener('DOMContentLoaded', () => {
      (() => {
        const root = document.querySelector('[data-aion-slider]');
        if (!root) return;
        const slides = Array.from(root.querySelectorAll('[data-slide]'));
        const hero = root.closest('.aion-hero');
        const stats = hero?.querySelector('.aion-hero-stats');
        const dots = Array.from(document.querySelectorAll('[data-slide-to]'));

        // Keep a generous gap between the copy and the fixed bottom information panel.
        // The hero grows when the active slide contains longer text.
        const fitHeroHeight = () => {
            if (!hero) return;
            const activeSlide = slides.find((slide) => slide.classList.contains('is-active')) || slides[0];
            const copy = activeSlide?.querySelector('.aion-hero-copy');
            if (!copy) return;

            const mobile = window.matchMedia('(max-width: 780px)').matches;
            const copyHeight = Math.ceil(copy.getBoundingClientRect().height);
            const topSpace = mobile ? 110 : 140;
            const bottomSpace = mobile ? 390 : 300;
            const minimum = mobile ? 800 : 650;
            const needed = Math.max(minimum, copyHeight + topSpace + bottomSpace);
            hero.style.setProperty('--aion-dynamic-height', `${needed}px`);
        };

        if (slides.length < 2) {
            requestAnimationFrame(fitHeroHeight);
            window.addEventListener('resize', fitHeroHeight, { passive: true });
            return;
        }
        const current = document.querySelector('[data-slide-current]');
        let active = 0;
        let timer = null;
        const show = (next) => {
            active = (next + slides.length) % slides.length;
            slides.forEach((slide, i) => {
                slide.classList.toggle('is-active', i === active);
                slide.setAttribute('aria-hidden', i === active ? 'false' : 'true');
            });
            dots.forEach((dot, i) => {
                dot.classList.toggle('is-active', i === active);
                dot.setAttribute('aria-current', i === active ? 'true' : 'false');
            });
            if (current) current.textContent = String(active + 1).padStart(2, '0');
            requestAnimationFrame(fitHeroHeight);
        };
        const stop = () => { if (timer) window.clearInterval(timer); timer = null; };
        const start = () => {
            stop();
            if (!window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
                timer = window.setInterval(() => show(active + 1), 6500);
            }
        };
        document.querySelector('[data-slide-prev]')?.addEventListener('click', () => { show(active - 1); start(); });
        document.querySelector('[data-slide-next]')?.addEventListener('click', () => { show(active + 1); start(); });
        dots.forEach((dot) => dot.addEventListener('click', () => { show(Number(dot.dataset.slideTo)); start(); }));
        root.addEventListener('mouseenter', stop);
        root.addEventListener('mouseleave', start);

        // Touch and pointer swipe support for mobile and desktop touch screens.
        let startX = null;
        let startY = null;
        root.addEventListener('pointerdown', (event) => {
            if (event.pointerType === 'mouse' && event.button !== 0) return;
            startX = event.clientX;
            startY = event.clientY;
        }, { passive: true });
        root.addEventListener('pointerup', (event) => {
            if (startX === null || startY === null) return;
            const deltaX = event.clientX - startX;
            const deltaY = event.clientY - startY;
            startX = null;
            startY = null;
            if (Math.abs(deltaX) > 45 && Math.abs(deltaX) > Math.abs(deltaY) * 1.2) {
                show(active + (deltaX < 0 ? 1 : -1));
                start();
            }
        }, { passive: true });
        root.addEventListener('pointercancel', () => { startX = null; startY = null; }, { passive: true });

        show(0);
        start();
        window.addEventListener('resize', fitHeroHeight, { passive: true });
        if ('ResizeObserver' in window) {
            const observer = new ResizeObserver(() => fitHeroHeight());
            slides.forEach((slide) => {
                const copy = slide.querySelector('.aion-hero-copy');
                if (copy) observer.observe(copy);
            });
            if (stats) observer.observe(stats);
        }
      })();
    });
    </script>

@endpush

@push('scripts')
<script>
(() => {
    const status = document.querySelector('[data-live-server-status]');
    const statusDetail = document.querySelector('[data-live-server-status-detail]');
    const players = document.querySelector('[data-live-online-players]');
    const playersDetail = document.querySelector('[data-live-players-detail]');
    if (!status && !players) return;
    const endpoint = @json(url('/api/server-status'));
    let active = true;
    const refresh = async () => {
        if (!active || document.hidden) return;
        try {
            const response = await fetch(endpoint, { headers: { Accept: 'application/json' }, cache: 'no-store', credentials: 'same-origin' });
            if (!response.ok) return;
            const data = await response.json();
            if (status && typeof data.server_status === 'string') {
                const label = data.server_status.toUpperCase();
                const dot = status.querySelector('.aion-status-dot');
                if (dot) dot.style.background = label === 'ONLINE' ? '#62d6a3' : (label === 'OFFLINE' ? '#dc6969' : '#a4a8b2');
                status.lastChild.textContent = ' ' + label;
            }
            if (statusDetail && typeof data.server_status_detail === 'string') statusDetail.textContent = data.server_status_detail;
            if (players) players.textContent = Number.isFinite(Number(data.online_players)) && data.online_players !== null ? Number(data.online_players).toLocaleString() : 'UNAVAILABLE';
            if (playersDetail && typeof data.players_detail === 'string') playersDetail.textContent = data.players_detail;
        } catch (_) { /* Keep the last server-rendered values when polling fails. */ }
    };
    refresh();
    const timer = window.setInterval(refresh, 30000);
    document.addEventListener('visibilitychange', refresh);
    window.addEventListener('pagehide', () => { active = false; window.clearInterval(timer); }, { once: true });
})();
</script>
@endpush
