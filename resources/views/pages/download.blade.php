@extends('layouts.app')

@section('title', 'Download Center | AION PTS')
@section('meta_description', 'Official AION PTS 4.6 client and patch download center by Zyvalle.')

@section('content')
<main class="container download-center">
    <section class="page-intro download-intro">
        <div class="eyebrow">Prepare for Atreia</div>
        <h1>Download Center</h1>
        <p>Official client files and patches will be listed here when their sources are confirmed.</p>
    </section>

    <section class="download-list" aria-label="Available downloads">
        @forelse ($downloadItems as $item)
            <article class="download-card">
                <div class="download-card-top">
                    <span class="download-kind">{{ $item->type === 'manual_patch' ? 'Manual Patch' : 'Game Client' }}</span>
                    @if ($item->version)
                        <span class="download-version">Version {{ $item->version }}</span>
                    @endif
                </div>
                <h2>{{ $item->title }}</h2>
                @if ($item->description)
                    <p class="download-description">{{ $item->description }}</p>
                @endif

                <div class="download-meta">
                    @if (!is_null($item->file_size_bytes))
                        <span>File size: {{ $item->file_size_bytes >= 1073741824
                            ? number_format($item->file_size_bytes / 1073741824, 2) . ' GB'
                            : ($item->file_size_bytes >= 1048576
                                ? number_format($item->file_size_bytes / 1048576, 2) . ' MB'
                                : number_format($item->file_size_bytes / 1024, 0) . ' KB') }}</span>
                    @endif
                    @if ($item->sha256)
                        <details class="checksum">
                            <summary>Verify SHA-256</summary>
                            <code>{{ $item->sha256 }}</code>
                        </details>
                    @endif
                </div>

                @if ($item->release_notes)
                    <details class="release-notes">
                        <summary>Release notes</summary>
                        <p>{{ $item->release_notes }}</p>
                    </details>
                @endif

                @if ($item->download_url)
                    <a class="btn download-button" href="{{ $item->download_url }}" target="_blank" rel="noopener noreferrer">
                        DOWNLOAD FILE <span aria-hidden="true">↗</span>
                    </a>
                @else
                    <button class="btn download-button is-disabled" type="button" disabled aria-disabled="true">
                        DOWNLOAD LINK PENDING
                    </button>
                    <p class="download-pending">The official file URL has not been configured yet.</p>
                @endif
            </article>
        @empty
            <article class="download-card download-empty">
                <div class="download-emblem" aria-hidden="true">AION</div>
                <div class="eyebrow">Client preparation</div>
                <h2>Official Client Coming Soon</h2>
                <p>The client download source has not been confirmed yet. No third-party or placeholder download links are shown.</p>
                @if ($aionFeatures['news'] ?? false)<a href="{{ route('news.index') }}" class="btn btn-outline">CHECK LATEST NEWS <span aria-hidden="true">→</span></a>@endif
            </article>
        @endforelse
    </section>

    <section class="download-notice">
        <div class="notice-mark" aria-hidden="true">!</div>
        <div>
            <h2>Installation Guide</h2>
            <p>Official installation steps and system requirements will be published after the client package and supported environment are confirmed. Please use the release notes supplied with the official download.</p>
        </div>
    </section>
</main>
@endsection

@push('head')
<style>
.download-center { padding-bottom: 72px; }
.download-intro { padding-top: 58px; padding-bottom: 34px; }
.download-intro p { max-width: 680px; }
.download-list { display:grid; grid-template-columns:repeat(2,minmax(0,1fr)); gap:18px; padding:0 0 30px; }
.download-card { min-width:0; padding:clamp(22px,3vw,32px); background:linear-gradient(145deg,#171c26 0%,#101319 76%); border:1px solid rgba(200,170,112,.22); box-shadow:0 18px 50px rgba(0,0,0,.12); }
.download-card h2 { margin:15px 0 10px; font-size:clamp(21px,3vw,28px); overflow-wrap:anywhere; }
.download-kind { display:inline-flex; padding:6px 9px; border:1px solid rgba(200,170,112,.4); color:var(--gold); font-size:9px; font-weight:700; letter-spacing:1.5px; text-transform:uppercase; }
.download-version { color:var(--muted); font-size:10px; letter-spacing:.7px; }
.download-card-top { display:flex; flex-wrap:wrap; align-items:center; justify-content:space-between; gap:10px; }
.download-description, .download-empty > p { color:#b2b7c2; font-size:13px; line-height:1.8; white-space:pre-line; }
.download-meta { display:flex; flex-wrap:wrap; align-items:center; gap:14px; margin:18px 0; color:var(--muted); font-size:11px; }
.checksum summary, .release-notes summary { color:var(--gold); cursor:pointer; font-size:11px; }
.checksum code { display:block; margin-top:8px; color:#c8cbd2; font-size:10px; overflow-wrap:anywhere; }
.release-notes { margin:16px 0 20px; padding-top:14px; border-top:1px solid var(--line); }
.release-notes p { color:var(--muted); font-size:12px; line-height:1.8; white-space:pre-line; }
.download-button { margin-top:8px; gap:12px; }
.download-button.is-disabled { cursor:not-allowed; opacity:.58; background:transparent; color:var(--muted); border-color:rgba(255,255,255,.16); transform:none; }
.download-pending { margin:10px 0 0; color:#8c929e; font-size:10px; }
.download-empty { grid-column:1/-1; padding:clamp(30px,6vw,58px); text-align:center; background:radial-gradient(ellipse at 50% 0%,rgba(200,170,112,.10),transparent 55%),linear-gradient(145deg,#171c26,#101319); }
.download-empty .eyebrow { margin-top:12px; }
.download-empty h2 { font-size:clamp(26px,4vw,38px); }
.download-empty > p { max-width:620px; margin:0 auto 22px; }
.download-emblem { display:grid; place-items:center; width:66px; height:66px; margin:0 auto 18px; border:1px solid rgba(200,170,112,.55); color:var(--gold); font-family:'Cinzel',serif; letter-spacing:2px; transform:rotate(45deg); }
.download-notice { display:flex; align-items:flex-start; gap:16px; padding:22px 24px; border:1px solid var(--line); background:rgba(255,255,255,.025); }
.notice-mark { display:grid; flex:0 0 28px; place-items:center; width:28px; height:28px; border:1px solid rgba(200,170,112,.55); color:var(--gold); font-family:'Cinzel',serif; }
.download-notice h2 { margin:0 0 8px; font-size:17px; }
.download-notice p { margin:0; color:var(--muted); font-size:12px; line-height:1.8; }
@media(max-width:720px) { .download-list { grid-template-columns:1fr; } .download-notice { padding:18px; } }
</style>
@endpush
