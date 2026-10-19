@extends('layouts.app')

@section('title', 'My Characters | AION PTS — By Zyvalle')
@section('meta_description', 'View the characters linked to your AION PTS game account.')

@section('content')
<main class="container account-workspace player-dashboard">
    <aside class="account-side-nav" aria-label="Account navigation">
        <div class="account-side-heading"><span class="account-side-mark" aria-hidden="true">✦</span><div><span class="account-side-kicker">PLAYER PORTAL</span><strong>My Account</strong></div></div>
        <nav class="account-side-links">
            <a href="{{ route('account') }}" class="account-side-link"><span class="account-side-icon" aria-hidden="true">⌂</span><span><strong>Account Overview</strong><small>Profile &amp; account status</small></span><span class="account-side-arrow" aria-hidden="true">›</span></a>
            @if ($aionFeatures['account.characters'] ?? false)
            <a href="{{ route('account.characters') }}" class="account-side-link is-active" aria-current="page"><span class="account-side-icon" aria-hidden="true">♙</span><span><strong>Characters</strong><small>Your game characters</small></span><span class="account-side-arrow" aria-hidden="true">›</span></a>
            @endif
            @if ($aionFeatures['account.password'] ?? false)
            <a href="{{ route('account.password.edit') }}" class="account-side-link"><span class="account-side-icon" aria-hidden="true">◇</span><span><strong>Change Password</strong><small>Protect your account</small></span><span class="account-side-arrow" aria-hidden="true">›</span></a>
            @endif
        @foreach (($aionAccountPluginNavigation ?? []) as $pluginNav)
        <a href="{{ $pluginNav['url'] }}" class="account-side-link {{ request()->is(ltrim($pluginNav['url'], '/').'*') ? 'is-active' : '' }}" {{ request()->is(ltrim($pluginNav['url'], '/').'*') ? 'aria-current=page' : '' }}><span class="account-side-icon" aria-hidden="true">✦</span><span><strong>{{ $pluginNav['label'] }}</strong><small>Player service</small></span><span class="account-side-arrow" aria-hidden="true">›</span></a>
        @endforeach
        </nav>
        <div class="account-side-bottom"><span class="account-online-dot"></span><span>ACCOUNT SESSION ACTIVE</span></div>
    </aside>

    <div class="account-main-content">
        <section class="page-intro">
            <div class="eyebrow">Player portal / Characters</div>
            <h1>My Characters</h1>
            <p>Characters linked to <strong>{{ $accountUsername }}</strong> in the game database.</p>
        </section>

        @if (! $charactersAvailable)
            <section class="character-empty" role="status">
                <span class="eyebrow">DATABASE UNAVAILABLE</span>
                <h2>Character data could not be loaded</h2>
                <p>The game database is temporarily unavailable. Please try again later. No character data has been changed.</p>
            </section>
        @elseif ($characters->isEmpty())
            <section class="character-empty">
                <span class="character-empty-mark" aria-hidden="true">✧</span>
                <span class="eyebrow">NO CHARACTERS FOUND</span>
                <h2>Your journey awaits</h2>
                <p>No active characters were found for this account on the configured world database.</p>
            </section>
        @else
            <div class="character-results-heading">
                <div><span class="eyebrow">CHARACTER ROSTER</span><h2>{{ $characters->count() }} {{ \Illuminate\Support\Str::plural('Character', $characters->count()) }}</h2></div>
                <span class="account-chip"><span class="account-chip-dot"></span> Read-only game data</span>
            </div>
            <section class="character-grid" aria-label="Your characters">
                @foreach ($characters as $character)
                    @php
                        // Class IDs and icons follow the mapping supplied for this AION server.
                        // Existing assets are served from public/images/icon/.
                        $classMeta = [
                            0  => ['Warrior', 'warrior.png'],
                            1  => ['Gladiator', 'gladiator.png'],
                            2  => ['Templar', 'templar.png'],
                            3  => ['Scout', 'scout.png'],
                            4  => ['Assassin', 'assassin.png'],
                            5  => ['Ranger', 'ranger.png'],
                            6  => ['Mage', 'mage.png'],
                            7  => ['Sorcerer', 'sorcerer.png'],
                            8  => ['Spirit Master', 'spiritmaster.png'],
                            9  => ['Priest', 'priest.png'],
                            10 => ['Cleric', 'cleric.png'],
                            11 => ['Chanter', 'chanter.png'],
                            12 => ['Rider', 'rider.png'],
                            13 => ['Aethertech', 'rider.png'],
                            14 => ['Gunner', 'gunner.png'],
                            15 => ['Bard', 'bard.png'],
                            16 => ['Songweaver', 'bard.png'],
                        ];
                        $classId = (int) $character->class;
                        // Match the legacy helper default: show the raw ID rather than guessing a class.
                        $classInfo = $classMeta[$classId] ?? [(string) $classId, 'warrior.png'];
                        $classLabel = $classInfo[0];
                        $classIcon = $classInfo[1];
                        $raceInfo = match ((int) $character->race) {
                            0 => ['Elyos', 'elyos-ico.png', 'is-elyos'],
                            1 => ['Asmodians', 'asmodian-ico.png', 'is-asmodian'],
                            default => ['Unknown Race', null, ''],
                        };
                        $genderInfo = (int) $character->gender === 1 ? ['Female', 'female.png'] : ['Male', 'male.png'];
                        $worldName = $worldMapNames[(int) $character->world] ?? 'Unknown Location';
                        $playHours = (int) floor(max(0, (int) $character->playtime) / 3600);
                    @endphp
                    <article class="character-card">
                        <div class="character-card-top">
                            <span class="character-class-emblem" title="{{ $classLabel }}" aria-label="{{ $classLabel }}"><img src="{{ asset('images/icon/' . $classIcon) }}" alt="{{ $classLabel }}" loading="lazy"></span>
                            <span class="character-level">LEVEL {{ (int) $character->lev }}</span>
                        </div>
                        <h3>{{ $character->user_id }}</h3>
                        <p class="character-subtitle">{{ $classLabel }} <span>·</span> Level {{ (int) $character->lev }}</p>
                        <div class="character-tags">
                            <span class="character-race {{ $raceInfo[2] }}">@if ($raceInfo[1])<img src="{{ asset('images/icon/' . $raceInfo[1]) }}" alt="" loading="lazy">@endif{{ $raceInfo[0] }}</span>
                            <span class="character-gender"><img src="{{ asset('images/icon/' . $genderInfo[1]) }}" alt="" loading="lazy">{{ $genderInfo[0] }}</span>
                        </div>
                        <div class="character-stats">
                            <div class="character-stat-world"><span>WORLD</span><strong title="{{ $worldName }}">{{ $worldName }}</strong></div>
                            <div><span>ABYSS POINTS</span><strong>{{ number_format((int) $character->abyss_point) }}</strong></div>
                            <div><span>PLAY TIME</span><strong>{{ number_format($playHours) }} hrs</strong></div>
                            <div><span>GUILD</span><strong>{{ trim((string) ($character->guild_nickname ?? '')) !== '' ? $character->guild_nickname : ((int) $character->guild_id > 0 ? 'Guild Member' : 'No Guild') }}</strong></div>
                        </div>
                        <div class="character-card-footer">
                            <span class="character-state {{ (bool) $character->is_banned ? 'is-restricted' : '' }}"><i></i>{{ (bool) $character->is_banned ? 'Restricted' : 'Active Account' }}</span>
                            <span class="character-last-seen">Last login {{ $character->last_login_time ? \Illuminate\Support\Carbon::parse($character->last_login_time)->format('d M Y') : 'Unknown' }}</span>
                        </div>
                    </article>
                @endforeach
            </section>
            <p class="player-footnote">Character information is read-only. Values are loaded from the configured AION world database; online status is not inferred from login timestamps.</p>
        @endif
    </div>
</main>
@endsection

@push('head')
<style>
.account-workspace{display:grid;grid-template-columns:248px minmax(0,1fr);gap:26px;align-items:start;padding-bottom:80px}.account-side-nav{position:sticky;top:24px;border:1px solid rgba(200,170,112,.22);background:linear-gradient(160deg,rgba(20,24,33,.98),rgba(10,12,17,.98));box-shadow:0 20px 55px rgba(0,0,0,.2)}.account-side-heading{display:flex;align-items:center;gap:12px;padding:22px 18px;border-bottom:1px solid var(--line)}.account-side-mark{display:grid;place-items:center;width:38px;height:38px;flex:0 0 38px;border:1px solid rgba(200,170,112,.42);color:var(--gold);font-size:20px}.account-side-heading div{display:grid;gap:3px}.account-side-kicker{color:var(--gold);font-size:8px;letter-spacing:1.8px;font-weight:700}.account-side-heading strong{font-family:'Cinzel',serif;font-size:17px;font-weight:600}.account-side-links{display:grid;gap:5px;padding:12px}.account-side-link{position:relative;display:flex;align-items:center;gap:11px;min-width:0;padding:13px 11px;border:1px solid transparent;color:#b9bdc7;transition:background .18s,border-color .18s,color .18s;text-decoration:none}.account-side-link:hover{color:#f3f0e9;background:rgba(255,255,255,.035);border-color:rgba(255,255,255,.07)}.account-side-link.is-active{color:#f3f0e9;background:linear-gradient(100deg,rgba(200,170,112,.15),rgba(200,170,112,.035));border-color:rgba(200,170,112,.28)}.account-side-link.is-active:before{content:"";position:absolute;left:-1px;top:10px;bottom:10px;width:2px;background:var(--gold)}.account-side-icon{display:grid;place-items:center;width:30px;height:30px;flex:0 0 30px;border:1px solid rgba(255,255,255,.1);font-size:17px;color:var(--gold)}.account-side-link>span:nth-child(2){display:grid;gap:3px;min-width:0}.account-side-link strong{font-size:11px;font-weight:700;letter-spacing:.15px}.account-side-link small{color:var(--muted);font-size:9px;line-height:1.4}.account-side-arrow{margin-left:auto;color:var(--gold);font-size:20px}.account-side-bottom{display:flex;align-items:center;gap:8px;padding:14px 18px;border-top:1px solid var(--line);color:#8c929e;font-size:8px;letter-spacing:1px}.account-online-dot{width:6px;height:6px;border-radius:50%;background:#68c88c;box-shadow:0 0 10px rgba(104,200,140,.45)}.account-main-content{min-width:0}.account-main-content .page-intro{margin-top:0}.character-results-heading{display:flex;align-items:center;justify-content:space-between;gap:16px;margin:28px 0 16px}.character-results-heading h2,.character-empty h2{margin:8px 0 0;font-family:'Cinzel',serif;font-size:clamp(21px,3vw,29px)}.character-grid{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:16px}.character-card,.character-empty{border:1px solid rgba(200,170,112,.2);background:linear-gradient(150deg,rgba(20,24,33,.98),rgba(10,12,17,.98));box-shadow:0 18px 50px rgba(0,0,0,.18)}.character-card{padding:22px;min-width:0}.character-card-top{display:flex;align-items:center;justify-content:space-between}.character-emblem{display:grid;place-items:center;width:38px;height:38px;border:1px solid rgba(200,170,112,.35);color:var(--gold);font-size:21px}.character-level{font-size:9px;font-weight:700;letter-spacing:1.2px;color:var(--gold);padding:7px 9px;border:1px solid rgba(200,170,112,.22)}.character-card h3{font-family:'Cinzel',serif;font-size:clamp(20px,2.5vw,27px);overflow-wrap:anywhere;margin:18px 0 4px}.character-subtitle{margin:0;color:#c9c3b7;font-size:12px}.character-subtitle span{color:var(--gold);padding:0 4px}.character-stats{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:1px;margin-top:20px;background:var(--line);border:1px solid var(--line)}.character-stats div{display:grid;gap:6px;padding:12px;background:#0d1118;min-width:0}.character-stats span{font-size:8px;letter-spacing:1px;color:var(--muted)}.character-stats strong{font-size:12px;color:#eee8dc;overflow-wrap:anywhere}.character-card-footer{display:flex;align-items:center;justify-content:space-between;gap:12px;margin-top:16px;padding-top:14px;border-top:1px solid var(--line)}.character-state{display:inline-flex;align-items:center;gap:7px;color:#9edbb2;font-size:10px}.character-state i{width:6px;height:6px;border-radius:50%;background:#66c58a}.character-state.is-restricted{color:#e6bc77}.character-state.is-restricted i{background:#e6bc77}.character-guild{color:var(--muted);font-size:10px}.character-empty{padding:clamp(28px,5vw,54px);text-align:center}.character-empty-mark{display:block;color:var(--gold);font-size:35px;margin-bottom:14px}.character-empty p,.player-footnote{color:var(--muted);font-size:12px;line-height:1.8;max-width:620px}.character-empty p{margin:12px auto 0}.player-footnote{margin-top:20px}@media(max-width:900px){.account-workspace{grid-template-columns:1fr;gap:18px}.account-side-nav{position:static}.account-side-heading{padding:15px 17px}.account-side-links{grid-template-columns:repeat(3,minmax(0,1fr));padding:8px;gap:6px}.account-side-link{align-items:flex-start;gap:7px;padding:10px 8px}.account-side-icon{width:25px;height:25px;flex-basis:25px;font-size:14px}.account-side-link strong{font-size:10px}.account-side-link small{font-size:8px}.account-side-arrow{display:none}}@media(max-width:620px){.character-grid{grid-template-columns:1fr}.character-results-heading{align-items:flex-start;flex-direction:column}.account-side-links{grid-template-columns:1fr}.account-side-link{align-items:center;padding:10px}.account-side-link small{font-size:9px}}

.character-class-emblem{display:grid;place-items:center;width:48px;height:48px;flex:0 0 48px;border:1px solid rgba(200,170,112,.45);background:radial-gradient(circle,rgba(200,170,112,.18),rgba(12,15,22,.92));overflow:hidden}.character-class-emblem img{display:block;width:34px;height:34px;object-fit:contain;filter:drop-shadow(0 0 5px rgba(200,170,112,.18))}
.character-tags{display:flex;flex-wrap:wrap;gap:8px;margin:14px 0 18px}.character-race,.character-gender{display:inline-flex;align-items:center;gap:6px;padding:6px 9px;border:1px solid rgba(255,255,255,.09);background:rgba(255,255,255,.025);color:#c9cbd2;font-size:10px;letter-spacing:.35px}.character-race img,.character-gender img{display:block;width:18px;height:18px;flex:0 0 18px;object-fit:contain}.character-race.is-elyos{border-color:rgba(228,190,107,.24)}.character-race.is-asmodian{border-color:rgba(153,130,218,.25)}.character-stat-world strong{overflow-wrap:anywhere}.character-last-seen{font-size:9px;color:var(--muted);text-align:right}.character-card h3{overflow-wrap:anywhere}.character-stats{grid-template-columns:repeat(2,minmax(0,1fr))}
@media(max-width:640px){.character-tags{margin:10px 0 14px}.character-stats{grid-template-columns:repeat(2,minmax(0,1fr))}.character-last-seen{max-width:48%;line-height:1.4}}
</style>
@endpush
