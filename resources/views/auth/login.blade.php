@extends('layouts.app')

@section('title', 'Player Login | AION PTS — By Zyvalle')
@section('meta_description', 'Sign in to your AION PTS game account.')

@section('content')
<main class="container aion-auth-page">
    <section class="auth-shell">
        <div class="auth-visual">
            <div class="auth-visual-inner">
                <div class="eyebrow">AION MMORPG 4.6 PTS</div>
                <h1>WELCOME<br><span>BACK.</span></h1>
                <p>Return to Atreia. Sign in with your existing AION PTS game account.</p>
                <div class="auth-visual-note">POWERED BY ZYVALLE <span>·</span> ELYOS &amp; ASMODIANS</div>
            </div>
        </div>
        <section class="auth-card">
            <div class="eyebrow">Player portal</div>
            <h2>Sign In</h2>
            <p class="auth-subtitle">Use your game username and password.</p>

            @if (session('status'))
                <div class="auth-alert auth-alert-success" role="status">{{ session('status') }}</div>
            @endif
            @if ($errors->any())
                <div class="auth-alert auth-alert-error" role="alert">
                    @foreach ($errors->all() as $error)<div>{{ $error }}</div>@endforeach
                </div>
            @endif

            <form method="POST" action="{{ route('login.store') }}" class="auth-form">
                @csrf
                <label for="username">Game Username</label>
                <input id="username" name="username" value="{{ old('username') }}" autocomplete="username" minlength="4" maxlength="14" pattern="[A-Za-z0-9]+" required autofocus>

                <label for="password">Game Password</label>
                <input id="password" type="password" name="password" autocomplete="current-password" minlength="6" maxlength="15" pattern="[A-Za-z0-9._@-]+" required>

                <button class="btn auth-submit" type="submit">LOGIN TO AION <span aria-hidden="true">→</span></button>
            </form>
            @if ($aionFeatures['account.registration'] ?? false)<p class="auth-switch">Don't have a game account? <a href="{{ route('register') }}">Create one</a>.</p>@endif
        </section>
    </section>
</main>
@endsection

@push('head')
<style>
.aion-auth-page{padding:54px 0 84px}.auth-shell{display:grid;grid-template-columns:minmax(0,1fr) minmax(360px,.82fr);max-width:1040px;margin:0 auto;border:1px solid var(--line);background:#0e1118;box-shadow:0 25px 90px rgba(0,0,0,.28)}
.auth-visual{position:relative;display:flex;align-items:flex-end;min-height:540px;overflow:hidden;background:radial-gradient(ellipse at 68% 20%,rgba(200,170,112,.22),transparent 34%),radial-gradient(ellipse at 35% 80%,rgba(45,67,100,.4),transparent 50%),linear-gradient(145deg,#20232b,#0b0d12 72%);border-right:1px solid var(--line)}
.auth-visual:before{content:"";position:absolute;inset:0;background-image:radial-gradient(circle at 20% 30%,rgba(225,204,158,.8) 1px,transparent 1.5px),radial-gradient(circle at 70% 60%,rgba(170,190,220,.65) 1px,transparent 1.5px);background-size:83px 91px,127px 113px;opacity:.25;mask-image:linear-gradient(to top,#000,transparent)}
.auth-visual-inner{position:relative;padding:clamp(28px,5vw,58px)}.auth-visual h1{font-size:clamp(40px,5vw,64px);line-height:1.06;margin:20px 0}.auth-visual h1 span{color:var(--gold)}.auth-visual p{max-width:360px;color:#c0c4cd;font-size:13px;line-height:1.9}.auth-visual-note{margin-top:32px;color:#a5a9b2;font-size:9px;letter-spacing:1.5px}.auth-visual-note span{color:var(--gold);padding:0 5px}
.auth-card{padding:clamp(25px,4vw,44px);align-self:center}.auth-card h2{font-size:clamp(28px,3vw,36px);margin:8px 0}.auth-subtitle,.auth-switch{color:var(--muted);font-size:12px;line-height:1.8}.auth-form{display:grid;gap:9px;margin-top:28px}.auth-form label{margin-top:8px;color:#e8e4dc;font-size:10px;font-weight:700;letter-spacing:1px;text-transform:uppercase}.auth-form input{width:100%;min-height:48px;padding:12px 14px;background:#090c12;color:var(--text);border:1px solid rgba(255,255,255,.14);outline:none}.auth-form input:focus{border-color:var(--gold);box-shadow:0 0 0 2px rgba(200,170,112,.1)}.auth-submit{width:100%;margin-top:15px;gap:12px;cursor:pointer}.auth-switch{margin-top:24px}.auth-switch a{color:var(--gold);text-decoration:underline;text-underline-offset:3px}.auth-alert{padding:12px 14px;margin:18px 0;border:1px solid var(--line);font-size:12px;line-height:1.7;overflow-wrap:anywhere}.auth-alert-success{border-color:rgba(92,180,125,.35);background:rgba(30,90,55,.12)}.auth-alert-error{border-color:rgba(220,100,100,.35);background:rgba(110,30,30,.12)}
@media(max-width:760px){.aion-auth-page{padding:30px 0 55px}.auth-shell{grid-template-columns:1fr}.auth-visual{min-height:250px;border-right:0;border-bottom:1px solid var(--line)}.auth-visual-inner{padding:28px}.auth-visual h1{font-size:40px;margin:12px 0}.auth-visual p{margin:8px 0}.auth-visual-note{margin-top:18px}.auth-card{padding:28px 22px}}
</style>
@endpush
