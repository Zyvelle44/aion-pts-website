@extends('layouts.app')

@section('title', $errorTitle . ' | ' . ($websiteSettings['site_name'] ?? 'AION PTS'))
@section('meta_description', $errorMessage)

@section('content')
<main class="container" style="min-height:62vh;display:grid;place-items:center;padding-top:64px;padding-bottom:72px;">
    <section style="width:min(720px,100%);text-align:center;border:1px solid var(--line);background:linear-gradient(145deg,rgba(255,255,255,.035),rgba(255,255,255,.012));padding:clamp(28px,6vw,58px);">
        <div style="font-size:12px;letter-spacing:5px;text-transform:uppercase;color:var(--gold);margin-bottom:14px;">AION PTS · PLAYER PORTAL</div>
        <div style="font-family:'Cinzel',serif;font-size:clamp(54px,12vw,100px);line-height:1;color:var(--gold);margin-bottom:18px;">{{ $code }}</div>
        <h1 style="font-family:'Cinzel',serif;font-size:clamp(23px,4vw,34px);font-weight:500;letter-spacing:.8px;margin:0 0 12px;">{{ $errorTitle }}</h1>
        <p style="max-width:520px;margin:0 auto 28px;color:var(--muted);">{{ $errorMessage }}</p>
        <div style="display:flex;justify-content:center;gap:12px;flex-wrap:wrap;">
            <a href="{{ url('/') }}" style="display:inline-flex;align-items:center;justify-content:center;min-height:44px;padding:0 22px;border:1px solid var(--gold);color:var(--gold);font-size:12px;font-weight:700;letter-spacing:1px;text-transform:uppercase;">Return Home</a>
            @if (!empty($showBack))
                <a href="{{ url()->previous() }}" style="display:inline-flex;align-items:center;justify-content:center;min-height:44px;padding:0 22px;border:1px solid var(--line);color:var(--text);font-size:12px;font-weight:700;letter-spacing:1px;text-transform:uppercase;">Go Back</a>
            @endif
        </div>
        @if (!empty($reference))
            <p style="font-size:11px;color:var(--muted);margin:24px 0 0;">Reference: {{ $reference }}</p>
        @endif
    </section>
</main>
@endsection
