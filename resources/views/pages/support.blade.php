@extends('layouts.app')

@section('title', 'Player Support | AION PTS — By Zyvalle')
@section('meta_description', 'Find verified help and frequently asked questions for AION PTS by Zyvalle.')

@section('content')
<main class="container aion-support">
    <section class="page-intro support-intro">
        <div class="eyebrow">Atreia Assistance</div>
        <h1>Player Support</h1>
        <p>Find guidance for your AION PTS journey. Official contact channels and account services will appear here once configured.</p>
        <div class="support-intro-actions">
            @if ($aionFeatures['news'] ?? false)<a href="{{ route('news.index') }}" class="btn">LATEST NEWS <span aria-hidden="true">→</span></a>@endif
            @if ($aionFeatures['download'] ?? false)<a href="{{ route('download') }}" class="btn btn-outline">DOWNLOAD CENTER</a>@endif
        </div>
    </section>

    <section class="support-shortcuts" aria-label="Support categories">
        <a class="support-shortcut" href="#support-faq">
            <span class="support-shortcut-icon" aria-hidden="true">?</span>
            <span><strong>Frequently Asked Questions</strong><small>Browse verified answers</small></span>
            <span class="support-shortcut-arrow" aria-hidden="true">↗</span>
        </a>
        @if ($aionFeatures['news'] ?? false)
        @if ($aionFeatures['news'] ?? false)
        <a class="support-shortcut" href="{{ route('news.index') }}">
            <span class="support-shortcut-icon" aria-hidden="true">N</span>
            <span><strong>News &amp; Announcements</strong><small>Maintenance and official updates</small></span>
            <span class="support-shortcut-arrow" aria-hidden="true">↗</span>
        </a>
        @endif
        @endif
        <a class="support-shortcut" href="{{ route('account') }}">
            <span class="support-shortcut-icon" aria-hidden="true">A</span>
            <span><strong>Account Assistance</strong><small>Account portal status</small></span>
            <span class="support-shortcut-arrow" aria-hidden="true">↗</span>
        </a>
    </section>

    <section id="support-faq" class="section support-faq-section">
        <div class="section-heading">
            <div class="eyebrow">Knowledge Base</div>
            <h2>Frequently Asked Questions</h2>
            <p>Answers published by the server team appear below. Unconfirmed instructions are not presented as official guidance.</p>
        </div>

        @if ($supportFaqs->isNotEmpty())
            <div class="support-faq-list">
                @foreach ($supportFaqs as $faq)
                    <details class="support-faq-item">
                        <summary>
                            <span class="support-faq-category">{{ $faq->category }}</span>
                            <span class="support-faq-question">{{ $faq->question }}</span>
                            <span class="support-faq-plus" aria-hidden="true">+</span>
                        </summary>
                        <div class="support-faq-answer">{{ $faq->answer }}</div>
                    </details>
                @endforeach
            </div>
        @else
            <div class="support-empty">
                <div class="support-empty-mark" aria-hidden="true">A</div>
                <div class="eyebrow">Knowledge Base In Progress</div>
                <h3>Official Answers Are Coming</h3>
                <p>The support team has not published any FAQs yet. Please check official announcements for confirmed information.</p>
                @if ($aionFeatures['news'] ?? false)<a href="{{ route('news.index') }}" class="btn btn-outline">CHECK ANNOUNCEMENTS <span aria-hidden="true">→</span></a>@endif
            </div>
        @endif
    </section>

    <section class="support-contact">
        <div class="support-contact-copy">
            <div class="eyebrow">Need Further Assistance?</div>
            <h2>We’ll Guide You When Support Opens</h2>
            <p>Direct support contact and ticket submission are not configured yet. We will publish the official channel here when it is ready.</p>
        </div>
        <div class="support-contact-actions">
            <span class="support-contact-status"><span aria-hidden="true"></span> CONTACT CHANNEL PENDING</span>
            @if ($aionFeatures['news'] ?? false)<a href="{{ route('news.index') }}" class="info-text-link">Follow official announcements ↗</a>@endif
        </div>
    </section>
</main>
@endsection

@push('head')
<style>
.aion-support { padding-bottom:76px; }
.support-intro { padding-top:58px; padding-bottom:34px; }
.support-intro p { max-width:700px; line-height:1.9; }
.support-intro-actions { display:flex; flex-wrap:wrap; gap:11px; margin-top:23px; }
.support-intro-actions .btn { gap:10px; }
.support-shortcuts { display:grid; grid-template-columns:repeat(3,minmax(0,1fr)); gap:14px; }
.support-shortcut { display:flex; align-items:center; gap:13px; min-width:0; padding:18px; border:1px solid var(--line); background:linear-gradient(145deg,#171c26,#101319); transition:border-color .2s ease,transform .2s ease; }
.support-shortcut:hover { border-color:rgba(200,170,112,.55); transform:translateY(-2px); }
.support-shortcut-icon { display:grid; place-items:center; flex:0 0 38px; width:38px; height:38px; border:1px solid rgba(200,170,112,.38); color:var(--gold); font-family:'Cinzel',serif; font-size:20px; }
.support-shortcut strong,.support-shortcut small { display:block; }
.support-shortcut strong { font-size:12px; line-height:1.5; }
.support-shortcut small { margin-top:4px; color:var(--muted); font-size:10px; line-height:1.5; }
.support-shortcut-arrow { margin-left:auto; color:var(--gold); }
.support-faq-section { padding-top:70px; padding-bottom:55px; }
.support-faq-section .section-heading { margin-bottom:24px; }
.support-faq-list { display:grid; gap:10px; }
.support-faq-item { border:1px solid var(--line); background:linear-gradient(145deg,#141923,#0f1218); }
.support-faq-item[open] { border-color:rgba(200,170,112,.4); }
.support-faq-item summary { display:flex; align-items:center; gap:15px; padding:19px 21px; cursor:pointer; list-style:none; }
.support-faq-item summary::-webkit-details-marker { display:none; }
.support-faq-category { flex:0 0 auto; min-width:95px; color:var(--gold); font-size:9px; font-weight:700; letter-spacing:1.2px; text-transform:uppercase; }
.support-faq-question { flex:1; font-size:13px; font-weight:600; }
.support-faq-plus { color:var(--gold); font-size:22px; line-height:1; transition:transform .2s ease; }
.support-faq-item[open] .support-faq-plus { transform:rotate(45deg); }
.support-faq-answer { padding:0 24px 22px 131px; color:#b7bdc8; font-size:13px; line-height:1.9; white-space:pre-line; }
.support-empty { padding:35px; border:1px dashed rgba(200,170,112,.28); background:radial-gradient(ellipse at 100% 0%,rgba(200,170,112,.08),transparent 45%),#101319; }
.support-empty-mark { float:right; color:rgba(200,170,112,.22); font-family:'Cinzel',serif; font-size:70px; line-height:1; }
.support-empty h3 { margin:12px 0; font-size:24px; }
.support-empty p { max-width:640px; color:var(--muted); font-size:13px; line-height:1.8; }
.support-empty .btn { margin-top:10px; }
.support-contact { display:flex; justify-content:space-between; align-items:center; gap:28px; padding:clamp(24px,4vw,38px); border:1px solid rgba(200,170,112,.3); background:radial-gradient(ellipse at 90% 0%,rgba(200,170,112,.1),transparent 45%),linear-gradient(120deg,#171c26,#101319); }
.support-contact-copy { max-width:650px; }
.support-contact h2 { margin:10px 0; font-size:clamp(22px,3vw,32px); }
.support-contact p { margin-bottom:0; color:var(--muted); font-size:13px; line-height:1.8; }
.support-contact-actions { display:flex; flex-direction:column; align-items:flex-start; gap:15px; flex:0 0 auto; }
.support-contact-status { display:flex; align-items:center; gap:8px; color:var(--muted); font-size:9px; font-weight:700; letter-spacing:1px; }
.support-contact-status span { width:7px; height:7px; border-radius:50%; background:#8c9098; box-shadow:0 0 12px rgba(140,144,152,.3); }
.info-text-link { color:var(--gold); font-size:11px; font-weight:600; }
.info-text-link:hover { color:#e0c794; }
@media(max-width:800px) {
    .support-shortcuts { grid-template-columns:1fr; }
    .support-contact { align-items:flex-start; flex-direction:column; }
    .support-contact-actions { flex-direction:row; flex-wrap:wrap; align-items:center; }
}
@media(max-width:560px) {
    .support-intro { padding-top:38px; }
    .support-faq-item summary { align-items:flex-start; gap:10px; padding:16px; }
    .support-faq-category { min-width:0; width:76px; padding-top:3px; font-size:8px; }
    .support-faq-question { font-size:12px; }
    .support-faq-answer { padding:0 16px 18px; font-size:12px; }
    .support-empty { padding:22px; }
    .support-empty-mark { font-size:48px; }
}
</style>
@endpush
