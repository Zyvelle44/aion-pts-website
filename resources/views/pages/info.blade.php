@extends('layouts.app')

@section('title', 'Server Information | AION PTS — By Zyvalle')
@section('meta_description', 'Explore AION PTS 4.6 server information, connection status, factions, and confirmed launch details.')

@section('content')
<main class="container aion-info">
    <section class="page-intro info-intro">
        <div class="eyebrow">The World of Atreia</div>
        <h1>Server Information</h1>
        <p>A clear overview of AION PTS — By Zyvalle. Confirmed configuration details will be published as they become available.</p>
        <div class="info-intro-actions">
            @if ($aionFeatures['download'] ?? false)<a href="{{ route('download') }}" class="btn">DOWNLOAD CENTER <span aria-hidden="true">→</span></a>@endif
            @if ($aionFeatures['news'] ?? false)<a href="{{ route('news.index') }}" class="btn btn-outline">LATEST ANNOUNCEMENTS</a>@endif
        </div>
    </section>

    @php
        $serverState = strtoupper((string) ($serverMetrics['server_status'] ?? 'UNKNOWN'));
        $statusClass = match ($serverState) {
            'ONLINE' => 'status-online',
            'OFFLINE' => 'status-offline',
            default => 'status-unknown',
        };
        $onlinePlayers = $serverMetrics['online_players'] ?? null;
    @endphp

    <section class="info-status-grid" aria-label="Server overview">
        <article class="info-stat-card info-stat-featured">
            <div class="info-stat-top"><span class="info-stat-label">Reachability</span><span class="status-pill {{ $statusClass }}" data-live-info-status><span class="status-indicator"></span><span data-live-info-status-label>{{ $serverState }}</span></span></div>
            <h2>{{ $serverState === 'ONLINE' ? 'Connection Detected' : ($serverState === 'OFFLINE' ? 'Connection Unavailable' : 'Status Unverified') }}</h2>
            <p data-live-info-status-detail>{{ $serverMetrics['server_status_detail'] ?? 'Server reachability has not been verified.' }}</p>
            <span class="info-stat-footnote">TCP reachability check · Not a full game-world health check</span>
        </article>
        <article class="info-stat-card">
            <span class="info-stat-label">Online Players</span>
            <div class="info-stat-value" data-live-info-players>{{ is_numeric($onlinePlayers) ? number_format((int) $onlinePlayers) : '—' }}</div>
            <p data-live-info-players-detail>{{ $serverMetrics['players_detail'] ?? 'Live player statistics are not currently available.' }}</p>
        </article>
        <article class="info-stat-card">
            <span class="info-stat-label">Game Version</span>
            <div class="info-stat-value info-version">AION 4.6 <span>PTS</span></div>
            <p>Private test server. Additional gameplay configuration is pending official confirmation.</p>
        </article>
    </section>

    <section class="section info-section">
        <div class="section-heading">
            <div class="eyebrow">Choose Your Allegiance</div>
            <h2>Two Factions. One Atreia.</h2>
            <p>Pick a side when character creation and server access are available.</p>
        </div>
        <div class="info-faction-grid">
            <article class="info-faction-card info-elyos">
                <div class="faction-mark" aria-hidden="true">E</div>
                <div class="eyebrow">The Light</div>
                <h3>Elyos</h3>
                <p>Discover the Elyos faction and its place in the world of Atreia.</p>
                <span class="faction-note">Faction available in AION · Server-specific settings pending</span>
            </article>
            <article class="info-faction-card info-asmodian">
                <div class="faction-mark" aria-hidden="true">A</div>
                <div class="eyebrow">The Shadow</div>
                <h3>Asmodians</h3>
                <p>Discover the Asmodian faction and its place in the world of Atreia.</p>
                <span class="faction-note">Faction available in AION · Server-specific settings pending</span>
            </article>
        </div>
    </section>

    <section class="section info-section info-configuration">
        <div class="section-heading">
            <div class="eyebrow">Configuration Brief</div>
            <h2>Server Details</h2>
            <p>Only verified information is presented here. Unconfirmed settings are intentionally left pending.</p>
        </div>
        <div class="info-detail-list">
            <div class="info-detail-row"><span>Game Version</span><strong>AION 4.6 PTS</strong></div>
            <div class="info-detail-row"><span>Playable Factions</span><strong>Elyos &amp; Asmodians</strong></div>
            <div class="info-detail-row"><span>Experience / Quest Rates</span><span class="pending-value">To be announced</span></div>
            <div class="info-detail-row"><span>Item Drop / Kinah Rates</span><span class="pending-value">To be announced</span></div>
            <div class="info-detail-row"><span>Level Cap</span><span class="pending-value">To be confirmed</span></div>
            <div class="info-detail-row"><span>Opening Date / Season</span><span class="pending-value">To be announced</span></div>
            <div class="info-detail-row"><span>PvP / PvE Configuration</span><span class="pending-value">To be confirmed</span></div>
        </div>
    </section>

    <section class="info-next-step">
        <div class="info-next-copy">
            <div class="eyebrow">Prepare for Your Journey</div>
            <h2>Stay Close to Official Updates</h2>
            <p>Follow announcements for confirmed server settings, client availability, maintenance notices, and launch information.</p>
        </div>
        <div class="info-next-actions">
            @if ($aionFeatures['news'] ?? false)<a href="{{ route('news.index') }}" class="btn">READ NEWS <span aria-hidden="true">→</span></a>@endif
            @if ($aionFeatures['download'] ?? false)<a href="{{ route('download') }}" class="btn btn-outline">DOWNLOAD CENTER</a>@endif
            @if ($aionFeatures['support'] ?? false)<a href="{{ route('support') }}" class="info-text-link">Need help? Visit Support ↗</a>@endif
        </div>
    </section>
</main>
@endsection

@push('head')
<style>
.aion-info { padding-bottom:76px; }
.info-intro { position:relative; padding-top:66px; padding-bottom:42px; }
.info-intro::after { content:""; position:absolute; z-index:-1; top:12px; right:0; width:min(42%,420px); height:210px; background:radial-gradient(ellipse at center,rgba(200,170,112,.12),transparent 68%); pointer-events:none; }
.info-intro p { max-width:720px; line-height:1.9; }
.info-intro-actions { display:flex; flex-wrap:wrap; gap:11px; margin-top:23px; }
.info-intro-actions .btn, .info-next-actions .btn { gap:10px; }
.info-status-grid { display:grid; grid-template-columns:1.25fr 1fr 1fr; gap:15px; }
.info-stat-card { min-width:0; padding:23px; border:1px solid var(--line); background:linear-gradient(145deg,#171c26 0%,#101319 82%); }
.info-stat-featured { border-color:rgba(200,170,112,.32); background:radial-gradient(ellipse at 100% 0%,rgba(200,170,112,.09),transparent 55%),linear-gradient(145deg,#171c26,#101319); }
.info-stat-top { display:flex; flex-wrap:wrap; justify-content:space-between; align-items:center; gap:10px; }
.info-stat-label { color:var(--muted); font-size:10px; letter-spacing:1.8px; text-transform:uppercase; }
.status-pill { display:inline-flex; align-items:center; gap:7px; padding:5px 8px; border:1px solid var(--line); font-size:9px; font-weight:700; letter-spacing:1.2px; }
.status-indicator { width:6px; height:6px; border-radius:50%; background:#a0a5ae; }
.status-online { color:#a8d9b5; border-color:rgba(104,190,128,.3); }
.status-online .status-indicator { background:#68be80; box-shadow:0 0 10px rgba(104,190,128,.5); }
.status-offline { color:#e3a5a5; border-color:rgba(220,105,105,.3); }
.status-offline .status-indicator { background:#dc6969; }
.status-unknown { color:#d0c5a8; border-color:rgba(200,170,112,.3); }
.info-stat-card h2 { margin:18px 0 10px; font-size:clamp(19px,2.2vw,25px); }
.info-stat-card p { margin:12px 0 0; color:var(--muted); font-size:11px; line-height:1.8; overflow-wrap:anywhere; }
.info-stat-value { margin-top:18px; font-family:'Cinzel',serif; font-size:clamp(29px,4vw,42px); line-height:1.2; color:var(--text); }
.info-version { font-size:clamp(24px,3vw,34px); }
.info-version span { color:var(--gold); font-size:.55em; letter-spacing:2px; }
.info-stat-footnote { display:block; margin-top:17px; color:#828894; font-size:9px; line-height:1.6; }
.info-section { padding-top:70px; padding-bottom:0; }
.info-section .section-heading { margin-bottom:23px; }
.info-faction-grid { display:grid; grid-template-columns:repeat(2,minmax(0,1fr)); gap:16px; }
.info-faction-card { position:relative; overflow:hidden; min-height:235px; padding:30px; border:1px solid var(--line); background:linear-gradient(135deg,rgba(50,67,91,.35),rgba(15,18,25,.97) 65%); }
.info-asmodian { background:linear-gradient(135deg,rgba(91,47,65,.28),rgba(15,18,25,.97) 65%); }
.info-faction-card::after { content:""; position:absolute; width:190px; height:190px; right:-70px; bottom:-90px; border:1px solid rgba(200,170,112,.13); border-radius:50%; box-shadow:0 0 0 20px rgba(200,170,112,.025),0 0 0 40px rgba(200,170,112,.02); pointer-events:none; }
.faction-mark { float:right; display:grid; place-items:center; width:52px; height:52px; border:1px solid rgba(200,170,112,.42); color:var(--gold); font:28px 'Cinzel',serif; transform:rotate(45deg); margin:10px 12px 0 0; }
.faction-mark::first-letter { transform:rotate(-45deg); }
.info-faction-card h3 { margin:9px 0 12px; font-size:30px; }
.info-faction-card p { max-width:440px; color:#b2b7c2; font-size:12px; line-height:1.8; }
.faction-note { display:block; margin-top:22px; color:#858b96; font-size:9px; letter-spacing:.4px; }
.info-configuration { padding-bottom:0; }
.info-detail-list { border:1px solid var(--line); background:linear-gradient(145deg,#141923,#0f1218); }
.info-detail-row { display:flex; justify-content:space-between; align-items:center; gap:18px; padding:16px 20px; border-bottom:1px solid var(--line); font-size:12px; }
.info-detail-row:last-child { border-bottom:0; }
.info-detail-row > span:first-child { color:#c8cbd2; }
.info-detail-row strong { color:var(--text); font-weight:600; text-align:right; }
.pending-value { color:#a6aab3; font-size:11px; text-align:right; }
.info-next-step { display:flex; align-items:center; justify-content:space-between; gap:30px; margin-top:70px; padding:clamp(25px,4vw,40px); border:1px solid rgba(200,170,112,.24); background:radial-gradient(ellipse at 0% 50%,rgba(200,170,112,.09),transparent 55%),#11151d; }
.info-next-copy { max-width:590px; }
.info-next-copy h2 { margin:10px 0; font-size:clamp(23px,3.2vw,32px); }
.info-next-copy p { margin:0; color:var(--muted); font-size:12px; line-height:1.8; }
.info-next-actions { display:flex; flex-direction:column; align-items:stretch; gap:10px; min-width:190px; }
.info-text-link { padding:7px 0; color:var(--muted); font-size:10px; text-align:center; }
.info-text-link:hover { color:var(--gold); }
@media(max-width:850px) { .info-status-grid { grid-template-columns:1fr 1fr; } .info-stat-featured { grid-column:1/-1; } .info-next-step { align-items:flex-start; flex-direction:column; } .info-next-actions { width:100%; max-width:320px; } }
@media(max-width:560px) { .info-intro { padding-top:40px; } .info-status-grid,.info-faction-grid { grid-template-columns:1fr; } .info-stat-featured { grid-column:auto; } .info-stat-card { padding:20px; } .info-faction-card { padding:24px; } .info-detail-row { align-items:flex-start; padding:14px; font-size:11px; } .pending-value { max-width:48%; } .info-next-step { margin-top:48px; } }
@media(prefers-reduced-motion:reduce) { .info-stat-card,.info-faction-card { transition:none; } }
</style>
@endpush

@push('scripts')
<script>
(() => {
    const status = document.querySelector('[data-live-info-status]');
    const label = document.querySelector('[data-live-info-status-label]');
    const statusDetail = document.querySelector('[data-live-info-status-detail]');
    const players = document.querySelector('[data-live-info-players]');
    const playersDetail = document.querySelector('[data-live-info-players-detail]');
    if (!status && !players) return;
    const endpoint = @json(url('/api/server-status'));
    let active = true;
    const refresh = async () => {
        if (!active || document.hidden) return;
        try {
            const response = await fetch(endpoint, { headers: { Accept: 'application/json' }, cache: 'no-store', credentials: 'same-origin' });
            if (!response.ok) return;
            const data = await response.json();
            const value = typeof data.server_status === 'string' ? data.server_status.toUpperCase() : 'UNKNOWN';
            if (label) label.textContent = value;
            if (status) {
                status.classList.remove('status-online', 'status-offline', 'status-unknown');
                status.classList.add(value === 'ONLINE' ? 'status-online' : (value === 'OFFLINE' ? 'status-offline' : 'status-unknown'));
            }
            if (statusDetail && typeof data.server_status_detail === 'string') statusDetail.textContent = data.server_status_detail;
            if (players) players.textContent = Number.isFinite(Number(data.online_players)) && data.online_players !== null ? Number(data.online_players).toLocaleString() : '—';
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
