@extends('layouts.app')

@section('title', 'Donation | AION PTS — By Zyvalle')
@section('meta_description', 'Support the AION PTS community. Official donation methods are shown only when configured by the server team.')

@section('content')
<main class="container aion-donation">
    <section class="page-intro donation-intro">
        <div class="eyebrow">Support the Journey</div>
        <h1>Support AION PTS</h1>
        <p>Your contribution can help support the community and ongoing server operations. Please use only payment methods published by the server team on this page.</p>
        <div class="donation-intro-note"><span aria-hidden="true">✦</span> Transparent information. No payment is collected directly by this page.</div>
    </section>

    <section class="section donation-options-section">
        <div class="section-heading">
            <div class="eyebrow">Official Contribution Channels</div>
            <h2>Donation Options</h2>
            <p>Available methods and verified instructions will be listed here by the server team.</p>
        </div>

        @if ($donationOptions->isNotEmpty())
            <div class="donation-grid">
                @foreach ($donationOptions as $option)
                    <article class="donation-card">
                        <div class="donation-card-top">
                            <span class="donation-symbol" aria-hidden="true">✦</span>
                            <span class="donation-method">{{ $option->method }}</span>
                        </div>
                        <h3>{{ $option->title }}</h3>
                        @if (filled($option->description))
                            <p class="donation-description">{{ $option->description }}</p>
                        @endif
                        @if (filled($option->instructions))
                            <div class="donation-instructions">{{ $option->instructions }}</div>
                        @endif
                        @if (filled($option->payment_url))
                            <a class="donation-action" href="{{ $option->payment_url }}" target="_blank" rel="noopener noreferrer">OPEN OFFICIAL PAYMENT PAGE <span aria-hidden="true">↗</span></a>
                        @else
                            <div class="donation-pending"><span aria-hidden="true">●</span> PAYMENT LINK NOT CONFIGURED</div>
                        @endif
                    </article>
                @endforeach
            </div>
        @else
            <div class="donation-empty">
                <div class="donation-empty-emblem" aria-hidden="true">A</div>
                <div class="eyebrow">Contribution Channels Pending</div>
                <h3>Official Donation Options Are Coming</h3>
                <p>The server team has not published a verified donation method yet. No payment link is available, and no payment is being collected here.</p>
                @if ($aionFeatures['news'] ?? false)<a href="{{ route('news.index') }}" class="btn btn-outline">VIEW ANNOUNCEMENTS <span aria-hidden="true">→</span></a>@endif
            </div>
        @endif
    </section>

    <section class="donation-trust-panel">
        <div class="donation-trust-icon" aria-hidden="true">◇</div>
        <div>
            <div class="eyebrow">A Note to Players</div>
            <h2>Use Verified Channels Only</h2>
            <p>Do not send funds to personal accounts or unofficial links claiming to represent AION PTS. Donation rewards, prices, and payment policies will be announced only after they are formally confirmed.</p>
        </div>
        @if ($aionFeatures['support'] ?? false)<a href="{{ route('support') }}" class="info-text-link">VISIT SUPPORT ↗</a>@endif
    </section>
</main>
@endsection

@push('head')
<style>
.aion-donation { padding-bottom:76px; }
.donation-intro { padding-top:58px; padding-bottom:34px; }
.donation-intro p { max-width:760px; line-height:1.9; }
.donation-intro-note { display:flex; align-items:center; gap:9px; margin-top:20px; color:var(--muted); font-size:10px; letter-spacing:.6px; }
.donation-intro-note span { color:var(--gold); }
.donation-options-section { padding-top:30px; }
.donation-options-section .section-heading { margin-bottom:24px; }
.donation-grid { display:grid; grid-template-columns:repeat(2,minmax(0,1fr)); gap:16px; }
.donation-card { min-width:0; padding:clamp(22px,3vw,30px); border:1px solid var(--line); background:radial-gradient(ellipse at 100% 0%,rgba(200,170,112,.07),transparent 42%),linear-gradient(145deg,#171c26,#101319); }
.donation-card-top { display:flex; align-items:center; gap:12px; margin-bottom:19px; }
.donation-symbol { display:grid; place-items:center; width:38px; height:38px; border:1px solid rgba(200,170,112,.4); color:var(--gold); font-size:18px; }
.donation-method { color:var(--gold); font-size:9px; font-weight:700; letter-spacing:1.6px; text-transform:uppercase; }
.donation-card h3 { margin:0 0 12px; font-size:22px; overflow-wrap:anywhere; }
.donation-description { color:#b9bdc6; font-size:13px; line-height:1.8; white-space:pre-line; }
.donation-instructions { margin-top:16px; padding:15px; border-left:2px solid rgba(200,170,112,.55); background:rgba(200,170,112,.05); color:#c5c9d2; font-size:12px; line-height:1.8; white-space:pre-line; overflow-wrap:anywhere; }
.donation-action { display:flex; justify-content:space-between; align-items:center; gap:12px; margin-top:22px; padding:13px 15px; border:1px solid var(--gold); color:var(--text); font-size:10px; font-weight:700; letter-spacing:.8px; }
.donation-action span { color:var(--gold); font-size:15px; }
.donation-action:hover { background:var(--gold); color:#111219; }
.donation-action:hover span { color:#111219; }
.donation-pending { display:flex; align-items:center; gap:9px; margin-top:22px; padding:13px 15px; border:1px solid var(--line); color:var(--muted); font-size:9px; font-weight:700; letter-spacing:1px; }
.donation-pending span { color:#9296a0; font-size:8px; }
.donation-empty { position:relative; overflow:hidden; padding:clamp(25px,5vw,42px); border:1px dashed rgba(200,170,112,.3); background:radial-gradient(ellipse at 100% 0%,rgba(200,170,112,.1),transparent 48%),#101319; }
.donation-empty-emblem { position:absolute; top:12px; right:25px; color:rgba(200,170,112,.15); font-family:'Cinzel',serif; font-size:100px; line-height:1; }
.donation-empty h3 { position:relative; margin:12px 0; font-size:clamp(21px,3vw,28px); }
.donation-empty p { position:relative; max-width:680px; color:var(--muted); font-size:13px; line-height:1.9; }
.donation-empty .btn { position:relative; margin-top:12px; }
.donation-trust-panel { display:flex; align-items:center; gap:20px; margin-top:32px; padding:25px; border:1px solid var(--line); background:linear-gradient(120deg,#151a23,#101319); }
.donation-trust-icon { flex:0 0 46px; display:grid; place-items:center; width:46px; height:46px; border:1px solid rgba(200,170,112,.35); color:var(--gold); font-size:25px; }
.donation-trust-panel>div:nth-child(2) { flex:1; }
.donation-trust-panel h2 { margin:7px 0; font-size:clamp(18px,2.4vw,24px); }
.donation-trust-panel p { margin:0; color:var(--muted); font-size:12px; line-height:1.8; }
.info-text-link { flex:0 0 auto; color:var(--gold); font-size:10px; font-weight:700; letter-spacing:.8px; }
.info-text-link:hover { color:#e0c794; }
@media(max-width:760px) { .donation-grid { grid-template-columns:1fr; } .donation-trust-panel { align-items:flex-start; flex-wrap:wrap; } .donation-trust-panel>div:nth-child(2) { flex-basis:calc(100% - 70px); } .info-text-link { margin-left:66px; } }
@media(max-width:560px) { .donation-intro { padding-top:38px; } .donation-empty-emblem { font-size:68px; } .donation-trust-panel { padding:20px; gap:14px; } .donation-trust-panel>div:nth-child(2) { flex-basis:100%; } .info-text-link { margin-left:0; } }
</style>
@endpush
