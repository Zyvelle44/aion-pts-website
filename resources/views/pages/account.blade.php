@extends('layouts.app')

@section('title', 'Player Account | AION PTS — By Zyvalle')
@section('meta_description', 'Your AION PTS player account dashboard.')

@section('content')
<main class="container account-workspace player-dashboard">
    <aside class="account-side-nav" aria-label="Account navigation">
    <div class="account-side-heading"><span class="account-side-mark" aria-hidden="true">✦</span><div><span class="account-side-kicker">PLAYER PORTAL</span><strong>My Account</strong></div></div>
    <nav class="account-side-links">
        <a href="{{ route('account') }}" class="account-side-link {{ request()->routeIs('account') ? 'is-active' : '' }}" {{ request()->routeIs('account') ? 'aria-current=page' : '' }}><span class="account-side-icon" aria-hidden="true">⌂</span><span><strong>Account Overview</strong><small>Profile &amp; account status</small></span><span class="account-side-arrow" aria-hidden="true">›</span></a>
        @if ($aionFeatures['account.characters'] ?? false)
        <a href="{{ route('account.characters') }}" class="account-side-link {{ request()->routeIs('account.characters') ? 'is-active' : '' }}" {{ request()->routeIs('account.characters') ? 'aria-current=page' : '' }}><span class="account-side-icon" aria-hidden="true">♙</span><span><strong>Characters</strong><small>Your game characters</small></span><span class="account-side-arrow" aria-hidden="true">›</span></a>
        @endif
        @if ($aionFeatures['account.password'] ?? false)
        <a href="{{ route('account.password.edit') }}" class="account-side-link {{ request()->routeIs('account.password.*') ? 'is-active' : '' }}" {{ request()->routeIs('account.password.*') ? 'aria-current=page' : '' }}><span class="account-side-icon" aria-hidden="true">◇</span><span><strong>Change Password</strong><small>Protect your account</small></span><span class="account-side-arrow" aria-hidden="true">›</span></a>
        @endif
        @foreach (($aionAccountPluginNavigation ?? []) as $pluginNav)
        <a href="{{ $pluginNav['url'] }}" class="account-side-link {{ request()->fullUrlIs(url($pluginNav['url'])) || request()->is(ltrim($pluginNav['url'], '/').'*') ? 'is-active' : '' }}" {{ request()->is(ltrim($pluginNav['url'], '/').'*') ? 'aria-current=page' : '' }}><span class="account-side-icon" aria-hidden="true">✦</span><span><strong>{{ $pluginNav['label'] }}</strong><small>Player service</small></span><span class="account-side-arrow" aria-hidden="true">›</span></a>
        @endforeach
    </nav>
    <div class="account-side-bottom"><span class="account-online-dot"></span><span>ACCOUNT SESSION ACTIVE</span></div>
</aside>
    <div class="account-main-content">
    <section class="page-intro">
        <div class="eyebrow">Player portal</div>
        <h1>Welcome back, {{ $accountUsername }}</h1>
        <p>Your AION PTS game account is signed in to this website session.</p>
    </section>

    @if (session('status'))
        <div class="player-notice" role="status">{{ session('status') }}</div>
    @endif

    <section class="account-panel">
        <div class="account-panel-heading">
            <div>
                <div class="eyebrow">Account identity</div>
                <h2>{{ $accountUsername }}</h2>
            </div>
            <span class="account-chip"><span class="account-chip-dot"></span> Website session active</span>
        </div>
        <div class="account-details-grid">
            <article class="account-detail-card account-detail-primary">
                <span class="account-detail-label">Game Username</span>
                <strong>{{ $accountUsername }}</strong>
                <small>Your AION login ID</small>
            </article>
            <article class="account-detail-card">
                <span class="account-detail-label">Registered Email</span>
                <strong class="account-email-value">{{ $accountEmail !== '' ? $accountEmail : 'Not available' }}</strong>
                <small>Stored in the game account record</small>
            </article>
            <article class="account-detail-card">
                <span class="account-detail-label">Registered Since</span>
                <strong>{{ !empty($accountProfile?->account_created_at) ? \Illuminate\Support\Carbon::parse($accountProfile->account_created_at)->format('d M Y') : 'Not available' }}</strong>
                <small>Account creation date</small>
            </article>
            <article class="account-detail-card">
                <span class="account-detail-label">Last Game Login</span>
                <strong>{{ !empty($accountProfile?->last_login) ? \Illuminate\Support\Carbon::parse($accountProfile->last_login)->format('d M Y, H:i') : 'No record' }}</strong>
                <small>From the AION account database</small>
            </article>
            <article class="account-detail-card">
                <span class="account-detail-label">Last Game Logout</span>
                <strong>{{ !empty($accountProfile?->last_logout) ? \Illuminate\Support\Carbon::parse($accountProfile->last_logout)->format('d M Y, H:i') : 'No record' }}</strong>
                <small>From the AION account database</small>
            </article>
            <article class="account-detail-card">
                <span class="account-detail-label">Donation Coins</span>
                <strong>{{ number_format((int) ($accountProfile?->donate_coin ?? 0)) }}</strong>
                <small>Value from web_account.donate_coin</small>
            </article>
            <article class="account-detail-card">
                <span class="account-detail-label">Game Account Status</span>
                @if ($accountProfile === null)
                    <strong class="account-status-unknown">Unavailable</strong>
                    <small>Could not retrieve account status</small>
                @elseif ((int) ($accountProfile->block_flag ?? 0) !== 0 || (int) ($accountProfile->secondary_block_flag ?? 0) !== 0)
                    <strong class="account-status-warning">Restricted flag present</strong>
                    <small>Contact support if you believe this is incorrect</small>
                @else
                    <strong class="account-status-ok">No block flag reported</strong>
                    <small>Based on the account block flags</small>
                @endif
            </article>
            <article class="account-detail-card">
                <span class="account-detail-label">Web Login Flag</span>
                <strong>{{ isset($accountProfile?->web_login_flag) ? ((int) $accountProfile->web_login_flag === 0 ? 'Not flagged' : 'Flag: '.$accountProfile->web_login_flag) : 'Not available' }}</strong>
                <small>Raw value from the game database</small>
            </article>
            <article class="account-detail-card">
                <span class="account-detail-label">Subscription Flag</span>
                <strong>{{ isset($accountProfile?->subscription_flag) ? $accountProfile->subscription_flag : 'Not available' }}</strong>
                <small>Raw value from the game database</small>
            </article>
        </div>
    </section>


    <p class="player-footnote">Account information is read from the configured AION SQL Server. Some values may be unavailable when the game database cannot be reached.</p>
    </div>
</main>
@endsection

@push('head')
<style>


.account-workspace{display:grid;grid-template-columns:248px minmax(0,1fr);gap:26px;align-items:start;padding-bottom:80px}.account-side-nav{position:sticky;top:24px;border:1px solid rgba(200,170,112,.22);background:linear-gradient(160deg,rgba(20,24,33,.98),rgba(10,12,17,.98));box-shadow:0 20px 55px rgba(0,0,0,.2)}.account-side-heading{display:flex;align-items:center;gap:12px;padding:22px 18px;border-bottom:1px solid var(--line)}.account-side-mark{display:grid;place-items:center;width:38px;height:38px;flex:0 0 38px;border:1px solid rgba(200,170,112,.42);color:var(--gold);font-size:20px}.account-side-heading div{display:grid;gap:3px}.account-side-kicker{color:var(--gold);font-size:8px;letter-spacing:1.8px;font-weight:700}.account-side-heading strong{font-family:'Cinzel',serif;font-size:17px;font-weight:600}.account-side-links{display:grid;gap:5px;padding:12px}.account-side-link{position:relative;display:flex;align-items:center;gap:11px;min-width:0;padding:13px 11px;border:1px solid transparent;color:#b9bdc7;transition:background .18s,border-color .18s,color .18s}.account-side-link:hover{color:#f3f0e9;background:rgba(255,255,255,.035);border-color:rgba(255,255,255,.07)}.account-side-link.is-active{color:#f3f0e9;background:linear-gradient(100deg,rgba(200,170,112,.15),rgba(200,170,112,.035));border-color:rgba(200,170,112,.28)}.account-side-link.is-active:before{content:"";position:absolute;left:-1px;top:10px;bottom:10px;width:2px;background:var(--gold)}.account-side-icon{display:grid;place-items:center;width:30px;height:30px;flex:0 0 30px;border:1px solid rgba(255,255,255,.1);font-size:17px;color:var(--gold)}.account-side-link>span:nth-child(2){display:grid;gap:3px;min-width:0}.account-side-link strong{font-size:11px;font-weight:700;letter-spacing:.15px}.account-side-link small{color:var(--muted);font-size:9px;line-height:1.4}.account-side-arrow{margin-left:auto;color:var(--gold);font-size:20px}.account-side-link.is-disabled{opacity:.55;cursor:not-allowed}.account-coming-soon{margin-left:auto;padding:4px 5px;border:1px solid rgba(200,170,112,.25);color:var(--gold);font-size:7px;letter-spacing:.7px}.account-side-bottom{display:flex;align-items:center;gap:8px;padding:14px 18px;border-top:1px solid var(--line);color:#8c929e;font-size:8px;letter-spacing:1px}.account-online-dot{width:6px;height:6px;border-radius:50%;background:#68c88c;box-shadow:0 0 10px rgba(104,200,140,.45)}.account-main-content{min-width:0}.account-main-content .page-intro{margin-top:0}.account-main-content .player-grid{margin-top:24px}.account-main-content .password-shell{max-width:none;margin:0}.account-main-content .password-context{min-width:0}.account-main-content .password-form-panel{min-width:0}
@media(max-width:850px){.account-workspace{grid-template-columns:1fr;gap:18px}.account-side-nav{position:static}.account-side-heading{padding:15px 17px}.account-side-links{grid-template-columns:repeat(3,minmax(0,1fr));padding:8px;gap:6px}.account-side-link{align-items:flex-start;gap:7px;padding:10px 8px}.account-side-icon{width:25px;height:25px;flex-basis:25px;font-size:14px}.account-side-link>span:nth-child(2){gap:4px}.account-side-link strong{font-size:10px}.account-side-link small{font-size:8px}.account-side-arrow{display:none}.account-coming-soon{position:absolute;right:5px;top:5px;font-size:6px}.account-side-bottom{padding:10px 15px}}
@media(max-width:520px){.account-side-links{grid-template-columns:1fr}.account-side-link{align-items:center;padding:10px}.account-side-link small{font-size:9px}.account-coming-soon{position:static;margin-left:auto}}

.player-dashboard{padding-bottom:76px}.player-grid{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:16px}.player-card{min-width:0}.player-card h2{font-family:'Cinzel',serif;font-size:clamp(20px,3vw,29px);overflow-wrap:anywhere}.player-email{font-family:'Inter',sans-serif!important;font-size:clamp(15px,2vw,20px)!important}.player-card p,.player-footnote{color:var(--muted);font-size:12px;line-height:1.8}.player-notice{padding:14px 16px;margin:0 0 20px;border:1px solid rgba(92,180,125,.35);background:rgba(30,90,55,.12);font-size:12px}.player-actions{display:flex;flex-wrap:wrap;gap:10px;margin-top:24px}.player-actions form{margin:0}.player-footnote{margin-top:24px}@media(max-width:650px){.player-grid{grid-template-columns:1fr}.player-actions>*{width:100%}.player-actions .btn{width:100%}}


.player-dashboard{padding-bottom:76px}.account-intro{margin-bottom:28px}.account-panel{border:1px solid var(--line);background:linear-gradient(145deg,rgba(20,24,33,.98),rgba(10,13,19,.98));box-shadow:0 24px 75px rgba(0,0,0,.2)}.account-panel-heading{display:flex;align-items:center;justify-content:space-between;gap:20px;padding:clamp(20px,3vw,30px);border-bottom:1px solid var(--line);background:radial-gradient(ellipse at 8% 0,rgba(200,170,112,.1),transparent 55%)}.account-panel-heading h2{margin:8px 0 0;font-family:'Cinzel',serif;font-size:clamp(24px,3vw,34px);overflow-wrap:anywhere}.account-chip{display:inline-flex;align-items:center;gap:8px;padding:8px 11px;border:1px solid rgba(102,197,138,.24);color:#b8d9c2;font-size:10px;letter-spacing:.5px;white-space:nowrap}.account-chip-dot{width:6px;height:6px;border-radius:50%;background:#66c58a;box-shadow:0 0 10px rgba(102,197,138,.6)}.account-details-grid{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:1px;background:var(--line)}.account-detail-card{display:flex;flex-direction:column;gap:8px;min-width:0;padding:22px;background:#0d1118}.account-detail-label{color:var(--muted);font-size:9px;font-weight:700;letter-spacing:1.4px;text-transform:uppercase}.account-detail-card strong{font-family:'Cinzel',serif;font-size:clamp(14px,1.5vw,19px);font-weight:600;color:#eee8dc;overflow-wrap:anywhere}.account-detail-card small{color:var(--muted);font-size:10px;line-height:1.6}.account-detail-card .account-email-value{font-family:'Inter',sans-serif;font-size:14px}.account-status-ok{color:#9edbb2!important}.account-status-warning{color:#e6bc77!important}.account-status-unknown{color:#a0a6b1!important}.account-security-panel{display:flex;align-items:center;justify-content:space-between;gap:24px;margin-top:18px;padding:clamp(20px,3vw,30px);border:1px solid var(--line);background:linear-gradient(110deg,rgba(200,170,112,.08),rgba(13,16,22,.9) 48%)}.account-security-copy h2{margin:8px 0;font-family:'Cinzel',serif;font-size:clamp(19px,2.5vw,25px)}.account-security-copy p,.player-footnote{max-width:660px;color:var(--muted);font-size:12px;line-height:1.8}.account-security-action{display:inline-flex;align-items:center;gap:12px;white-space:nowrap}.player-notice{padding:14px 16px;margin:0 0 20px;border:1px solid rgba(92,180,125,.35);background:rgba(30,90,55,.12);font-size:12px}.player-footnote{margin-top:20px}@media(max-width:850px){.account-details-grid{grid-template-columns:repeat(2,minmax(0,1fr))}}@media(max-width:560px){.account-panel-heading{align-items:flex-start;flex-direction:column}.account-details-grid{grid-template-columns:1fr}.account-detail-card{padding:18px}.account-security-panel{align-items:stretch;flex-direction:column}.account-security-action{justify-content:center;width:100%}}

</style>
@endpush
