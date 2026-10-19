@extends('layouts.app')

@section('title', 'Change Password | AION PTS — By Zyvalle')
@section('meta_description', 'Securely change your AION PTS game account password.')

@section('content')
<main class="container account-workspace change-password-page">
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
    </nav>
    <div class="account-side-bottom"><span class="account-online-dot"></span><span>ACCOUNT SESSION ACTIVE</span></div>
</aside>
    <div class="account-main-content">
    <section class="page-intro">
        <div class="eyebrow">Account security</div>
        <h1>Change Password</h1>
        <p>Update the password used to sign in to your AION game account.</p>
    </section>

    <section class="password-shell">
        <div class="password-context">
            <div class="password-symbol" aria-hidden="true">✦</div>
            <div class="eyebrow">Player account</div>
            <h2>{{ $accountUsername }}</h2>
            <p>Your password is updated through the legacy AION account procedure so the website and game account remain synchronized.</p>
            <ul>
                <li>Passwords are case-sensitive.</li>
                <li>Use 8–16 characters and meet every live requirement.</li>
                <li>Only supported printable ASCII characters are accepted by the legacy AION system.</li>
            </ul>
        </div>

        <div class="password-form-panel">
            <h2>New Credentials</h2>
            <p class="password-subtitle">Confirm your current password before making changes.</p>

            @if ($errors->any())
                <div class="password-alert password-alert-error" role="alert">
                    @foreach ($errors->all() as $error)<div>{{ $error }}</div>@endforeach
                </div>
            @endif

            <form method="POST" action="{{ route('account.password.update') }}" class="password-form">
                @csrf
                <label for="current_password">Current Password</label>
                <input id="current_password" name="current_password" type="password" autocomplete="current-password" maxlength="16" required autofocus>

                <label for="new_password">New Password</label>
                <input id="new_password" name="new_password" type="password" autocomplete="new-password" minlength="8" maxlength="16" aria-describedby="change-password-rules" required>
                <div class="password-rules" id="change-password-rules" aria-live="polite">
                    <p class="password-rules-title">PASSWORD REQUIREMENTS</p>
                    <ul class="password-rule-list">
                        <li data-rule="length"><span class="rule-mark">×</span><span>8–16 characters</span></li>
                        <li data-rule="upper"><span class="rule-mark">×</span><span>Uppercase letter (A–Z)</span></li>
                        <li data-rule="lower"><span class="rule-mark">×</span><span>Lowercase letter (a–z)</span></li>
                        <li data-rule="digit"><span class="rule-mark">×</span><span>Digit (0–9)</span></li>
                        <li data-rule="special"><span class="rule-mark">×</span><span>Special character</span></li>
                        <li data-rule="ascii"><span class="rule-mark">×</span><span>English letters and ASCII characters only</span></li>
                        <li data-rule="username"><span class="rule-mark">×</span><span>Does not contain the login username</span></li>
                    </ul>
                </div>

                <label for="new_password_confirmation">Confirm New Password</label>
                <input id="new_password_confirmation" name="new_password_confirmation" type="password" autocomplete="new-password" minlength="8" maxlength="16" aria-describedby="change-confirm-feedback" required><small class="field-validation-message" id="change-confirm-feedback" aria-live="polite"></small>

                <button class="btn password-submit" type="submit">UPDATE PASSWORD <span aria-hidden="true">→</span></button>
                <a href="{{ route('account') }}" class="password-cancel">Cancel and return to account</a>
            </form>
        </div>
    </section>
    </div>
</main>
@endsection

@push('head')
<style>

.account-workspace{display:grid;grid-template-columns:248px minmax(0,1fr);gap:26px;align-items:start;padding-bottom:80px}.account-side-nav{position:sticky;top:24px;border:1px solid rgba(200,170,112,.22);background:linear-gradient(160deg,rgba(20,24,33,.98),rgba(10,12,17,.98));box-shadow:0 20px 55px rgba(0,0,0,.2)}.account-side-heading{display:flex;align-items:center;gap:12px;padding:22px 18px;border-bottom:1px solid var(--line)}.account-side-mark{display:grid;place-items:center;width:38px;height:38px;flex:0 0 38px;border:1px solid rgba(200,170,112,.42);color:var(--gold);font-size:20px}.account-side-heading div{display:grid;gap:3px}.account-side-kicker{color:var(--gold);font-size:8px;letter-spacing:1.8px;font-weight:700}.account-side-heading strong{font-family:'Cinzel',serif;font-size:17px;font-weight:600}.account-side-links{display:grid;gap:5px;padding:12px}.account-side-link{position:relative;display:flex;align-items:center;gap:11px;min-width:0;padding:13px 11px;border:1px solid transparent;color:#b9bdc7;transition:background .18s,border-color .18s,color .18s}.account-side-link:hover{color:#f3f0e9;background:rgba(255,255,255,.035);border-color:rgba(255,255,255,.07)}.account-side-link.is-active{color:#f3f0e9;background:linear-gradient(100deg,rgba(200,170,112,.15),rgba(200,170,112,.035));border-color:rgba(200,170,112,.28)}.account-side-link.is-active:before{content:"";position:absolute;left:-1px;top:10px;bottom:10px;width:2px;background:var(--gold)}.account-side-icon{display:grid;place-items:center;width:30px;height:30px;flex:0 0 30px;border:1px solid rgba(255,255,255,.1);font-size:17px;color:var(--gold)}.account-side-link>span:nth-child(2){display:grid;gap:3px;min-width:0}.account-side-link strong{font-size:11px;font-weight:700;letter-spacing:.15px}.account-side-link small{color:var(--muted);font-size:9px;line-height:1.4}.account-side-arrow{margin-left:auto;color:var(--gold);font-size:20px}.account-side-link.is-disabled{opacity:.55;cursor:not-allowed}.account-coming-soon{margin-left:auto;padding:4px 5px;border:1px solid rgba(200,170,112,.25);color:var(--gold);font-size:7px;letter-spacing:.7px}.account-side-bottom{display:flex;align-items:center;gap:8px;padding:14px 18px;border-top:1px solid var(--line);color:#8c929e;font-size:8px;letter-spacing:1px}.account-online-dot{width:6px;height:6px;border-radius:50%;background:#68c88c;box-shadow:0 0 10px rgba(104,200,140,.45)}.account-main-content{min-width:0}.account-main-content .page-intro{margin-top:0}.account-main-content .player-grid{margin-top:24px}.account-main-content .password-shell{max-width:none;margin:0}.account-main-content .password-context{min-width:0}.account-main-content .password-form-panel{min-width:0}
@media(max-width:850px){.account-workspace{grid-template-columns:1fr;gap:18px}.account-side-nav{position:static}.account-side-heading{padding:15px 17px}.account-side-links{grid-template-columns:repeat(3,minmax(0,1fr));padding:8px;gap:6px}.account-side-link{align-items:flex-start;gap:7px;padding:10px 8px}.account-side-icon{width:25px;height:25px;flex-basis:25px;font-size:14px}.account-side-link>span:nth-child(2){gap:4px}.account-side-link strong{font-size:10px}.account-side-link small{font-size:8px}.account-side-arrow{display:none}.account-coming-soon{position:absolute;right:5px;top:5px;font-size:6px}.account-side-bottom{padding:10px 15px}}
@media(max-width:520px){.account-side-links{grid-template-columns:1fr}.account-side-link{align-items:center;padding:10px}.account-side-link small{font-size:9px}.account-coming-soon{position:static;margin-left:auto}}

.change-password-page{padding-bottom:80px}.password-shell{display:grid;grid-template-columns:minmax(0,.9fr) minmax(0,1.1fr);max-width:980px;margin:0 auto 35px;border:1px solid var(--line);background:linear-gradient(135deg,#11151d,#0d1016);box-shadow:0 24px 75px rgba(0,0,0,.22)}.password-context{padding:clamp(24px,4vw,42px);border-right:1px solid var(--line);background:radial-gradient(ellipse at 18% 0,rgba(200,170,112,.12),transparent 48%),linear-gradient(150deg,#151923,#0d1016)}.password-symbol{display:grid;place-items:center;width:44px;height:44px;margin-bottom:30px;border:1px solid rgba(200,170,112,.4);color:var(--gold);font-size:21px}.password-context h2{margin:10px 0 14px;font-size:clamp(25px,3vw,34px);overflow-wrap:anywhere}.password-context p,.password-context li,.password-subtitle{color:var(--muted);font-size:12px;line-height:1.9}.password-context ul{padding-left:18px;margin-top:22px}.password-context li{padding:3px 0}.password-form-panel{padding:clamp(24px,4vw,42px)}.password-form-panel h2{margin:0;font-size:27px}.password-subtitle{margin:8px 0 22px}.password-form{display:grid;gap:9px}.password-form label{margin-top:8px;color:#e8e4dc;font-size:10px;font-weight:700;letter-spacing:1px;text-transform:uppercase}.password-form input{width:100%;min-height:48px;padding:12px 14px;background:#090c12;color:var(--text);border:1px solid rgba(255,255,255,.14);outline:none;border-radius:0}.password-form input:focus{border-color:var(--gold);box-shadow:0 0 0 2px rgba(200,170,112,.1)}.password-submit{width:100%;margin-top:14px;gap:12px;cursor:pointer}.password-cancel{text-align:center;color:var(--muted);font-size:11px;margin-top:12px}.password-cancel:hover{color:var(--gold)}.password-alert{padding:12px 14px;margin:12px 0 18px;border:1px solid var(--line);font-size:12px;line-height:1.7;overflow-wrap:anywhere}.password-alert-error{border-color:rgba(220,100,100,.35);background:rgba(110,30,30,.12)}
.password-rules{margin:2px 0 8px;padding:12px 14px;border:1px solid rgba(255,255,255,.09);background:rgba(4,6,10,.42)}.password-rules-title{margin:0 0 8px;color:#c8aa70;font-size:9px;font-weight:700;letter-spacing:1.6px}.password-rule-list{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:7px 12px;margin:0;padding:0;list-style:none}.password-rule-list li{display:flex;align-items:flex-start;gap:7px;color:#a0a6b1;font-size:10px;line-height:1.5}.password-rule-list .rule-mark{display:grid;place-items:center;flex:0 0 15px;width:15px;height:15px;border:1px solid rgba(220,105,105,.5);border-radius:50%;color:#f08a8a;font-size:11px;font-weight:700;line-height:1}.password-rule-list li.is-valid{color:#cfe7d5}.password-rule-list li.is-valid .rule-mark{border-color:#66c58a;color:#66c58a}.password-rule-list li.is-invalid .rule-mark{border-color:rgba(220,105,105,.5);color:#f08a8a}@media(max-width:560px){.password-rule-list{grid-template-columns:1fr}}
@media(max-width:760px){.password-shell{grid-template-columns:1fr}.password-context{border-right:0;border-bottom:1px solid var(--line)}.password-symbol{margin-bottom:18px}}

.password-form input.field-invalid{border-color:#e66b73!important;box-shadow:0 0 0 2px rgba(230,107,115,.09)!important}.password-form input.field-valid{border-color:#66c58a!important;box-shadow:0 0 0 2px rgba(102,197,138,.08)}.field-validation-message{display:block;min-height:0;margin-top:2px;font-size:10px;line-height:1.5}.field-validation-message.is-error{color:#f08a8a}.field-validation-message.is-success{color:#75d69a}
</style>
@endpush


@push('scripts')
<script>
(() => {
    const password = document.getElementById('new_password');
    const confirmation = document.getElementById('new_password_confirmation');
    const username = @json((string) $accountUsername);
    const form = password?.form;
    if (!password || !confirmation || !form) return;
    const rules = {
        length: value => value.length >= 8 && value.length <= 16,
        upper: value => /[A-Z]/.test(value),
        lower: value => /[a-z]/.test(value),
        digit: value => /[0-9]/.test(value),
        special: value => /[!#$&()*+.\/:<>?@\[\]^_{|}~]/.test(value),
        ascii: value => value.length > 0 && /^[\x21-\x7E]+$/.test(value) && !/[\\',;\-`=%"]/.test(value),
        username: value => username.length > 0 && !value.toLowerCase().includes(username.toLowerCase()),
    };
    function updateConfirmation() {
        const valid = confirmation.value.length > 0 && confirmation.value === password.value;
        confirmation.classList.toggle('field-invalid', !valid);
        confirmation.classList.toggle('field-valid', valid);
        confirmation.setCustomValidity(valid ? '' : 'New password confirmation does not match.');
        const message = document.getElementById('change-confirm-feedback');
        if (message) {
            message.textContent = confirmation.value.length === 0 ? 'Please confirm your new password.' : (valid ? 'Passwords match.' : 'Passwords do not match.');
            message.classList.toggle('is-error', !valid);
            message.classList.toggle('is-success', valid);
        }
        return valid;
    }
    function updateRules() {
        const value = password.value;
        Object.entries(rules).forEach(([name, test]) => {
            const item = document.querySelector(`#change-password-rules [data-rule="${name}"]`);
            if (!item) return;
            const valid = test(value);
            item.classList.toggle('is-valid', valid);
            item.classList.toggle('is-invalid', !valid);
            item.querySelector('.rule-mark').textContent = valid ? '✓' : '×';
        });
        const valid = Object.values(rules).every(test => test(value));
        password.setCustomValidity(value.length > 0 && !valid ? 'Please meet all password requirements.' : '');
        password.classList.toggle('field-invalid', value.length > 0 && !valid);
        password.classList.toggle('field-valid', value.length > 0 && valid);
        updateConfirmation();
    }
    password.addEventListener('input', updateRules);
    confirmation.addEventListener('input', updateConfirmation);
    form.addEventListener('submit', event => {
        updateRules();
        const passwordValid = Object.values(rules).every(test => test(password.value));
        const confirmationValid = updateConfirmation();
        if (!passwordValid || !confirmationValid || !form.checkValidity()) {
            event.preventDefault();
            if (!passwordValid) { password.reportValidity(); password.focus(); }
            else { confirmation.reportValidity(); confirmation.focus(); }
        }
    });
    updateRules();
})();
</script>
@endpush
