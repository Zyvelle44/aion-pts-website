@extends('layouts.app')

@section('title', 'Create Game Account | AION PTS — By Zyvalle')
@section('meta_description', 'Create your AION PTS 4.6 game account and begin your journey through Atreia.')

@section('content')
<main class="aion-register-page">


    <section class="container register-content">
        <aside class="register-side">
            <div class="register-side-mark">A</div>
            <div class="eyebrow">Enter Atreia</div>
            <h2>Forge your<br>own legend.</h2>
            <p>One account brings you closer to the world of AION. Choose your credentials carefully and keep them private.</p>
            <div class="register-side-rule"></div>
            <div class="register-side-detail"><span class="register-side-icon">01</span><span><strong>Game Account</strong><small>Used to access the AION PTS game service.</small></span></div>
            <div class="register-side-detail"><span class="register-side-icon">02</span><span><strong>Secure Credentials</strong><small>Never share your password with anyone.</small></span></div>
            <div class="register-side-detail"><span class="register-side-icon">03</span><span><strong>Ready for Atreia</strong><small>Download details are available in Download Center.</small></span></div>
            @if ($aionFeatures['download'] ?? false)<a class="register-back-link" href="{{ route('download') }}">VISIT DOWNLOAD CENTER <span aria-hidden="true">↗</span></a>@endif
        </aside>

        <section class="register-card" aria-labelledby="register-title">
            <div class="register-card-topline"><span></span><span>ACCOUNT SERVICES</span><span></span></div>
            <div class="register-card-heading">
                <div class="eyebrow">Create account</div>
                <h2 id="register-title">Join AION PTS</h2>
                <p>Fill in the details below to create your game account.</p>
            </div>

            @if (session('status'))
                <div class="register-alert register-alert-success" role="status"><span aria-hidden="true">✓</span><div>{{ session('status') }}</div></div>
            @endif
            @if ($errors->any())
                <div class="register-alert register-alert-error" role="alert">
                    <strong>PLEASE CHECK YOUR DETAILS</strong>
                    <ul>@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
                </div>
            @endif

            <form method="POST" action="{{ route('register.store') }}" class="register-form">
                @csrf
                <div class="register-field">
                    <label for="username">Game Username <span>REQUIRED</span></label>
                    <div class="register-input-wrap"><span class="register-input-icon" aria-hidden="true">◈</span><input id="username" name="username" value="{{ old('username') }}" autocomplete="username" minlength="4" maxlength="14" pattern="[A-Za-z0-9]+" placeholder="Choose a username" required></div>
                    <small>4–14 characters. English letters and numbers only.</small>
                </div>

                <div class="register-field">
                    <label for="email">Email Address <span>REQUIRED</span></label>
                    <div class="register-input-wrap"><span class="register-input-icon" aria-hidden="true">✉</span><input id="email" type="email" name="email" value="{{ old('email') }}" autocomplete="email" maxlength="100" placeholder="you@example.com" required></div>
                    <small>Enter a valid email address, such as name@example.com or name@example.co.id.</small><small class="field-validation-message" id="email-feedback" aria-live="polite"></small>
                </div>

                <div class="register-field">
                    <label for="password">Game Password <span>8–16 CHARACTERS</span></label>
                    <div class="register-input-wrap"><span class="register-input-icon" aria-hidden="true">◆</span><input id="password" type="password" name="password" autocomplete="new-password" minlength="8" maxlength="16" aria-describedby="register-password-rules" placeholder="Create a secure game password" required></div>
                    <div class="password-rules" id="register-password-rules" aria-live="polite">
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
                </div>

                <div class="register-field">
                    <label for="password_confirmation">Confirm Password <span>REQUIRED</span></label>
                    <div class="register-input-wrap"><span class="register-input-icon" aria-hidden="true">◆</span><input id="password_confirmation" type="password" name="password_confirmation" autocomplete="new-password" minlength="8" maxlength="16" placeholder="Enter your password again" aria-describedby="register-confirm-feedback" required></div><small class="field-validation-message" id="register-confirm-feedback" aria-live="polite"></small>
                </div>

                <div class="register-security-note"><span aria-hidden="true">◇</span><p>Passwords are case-sensitive. Use only supported ASCII characters so your password remains compatible with the legacy AION game account system.</p></div>
                <button class="register-submit" type="submit"><span>CREATE GAME ACCOUNT</span><b aria-hidden="true">↗</b></button>
                <p class="register-form-foot">By registering, you are creating an <strong>AION game account</strong>, not a website administrator account.</p>
            </form>
        </section>
    </section>
</main>
@endsection

@push('head')
<style>
.aion-register-page{overflow:hidden;background:radial-gradient(ellipse at 50% 38%,rgba(45,54,70,.18),transparent 55%),#0b0d12;padding-bottom:82px}
.register-kicker{display:flex;align-items:center;gap:12px;color:#c8aa70;font-size:10px;font-weight:700;letter-spacing:3px;text-transform:uppercase}.register-kicker span{width:24px;height:1px;background:#c8aa70;opacity:.8}
.register-hero h1{margin:18px 0 10px;font:500 clamp(34px,5vw,55px)/1.1 'Cinzel',serif;letter-spacing:1px;color:#f3f0e9}.register-hero h1 em{font-style:normal;color:#c8aa70}.register-hero p{margin:0;color:#b5bac4;font-size:13px;line-height:1.8}.register-hero-meta span{width:6px;height:6px;border-radius:50%;background:#c8aa70;box-shadow:0 0 12px rgba(200,170,112,.65)}.register-hero-meta b{color:#c8aa70;font-weight:400}
.register-content{display:grid;grid-template-columns:minmax(0,.8fr) minmax(0,1.2fr);gap:clamp(28px,6vw,78px);align-items:start;padding-top:54px}
.register-side{position:relative;padding:20px 0 0;max-width:390px}.register-side-mark{position:absolute;top:-24px;right:15px;font:180px/.9 'Cinzel',serif;color:rgba(200,170,112,.045);pointer-events:none}.register-side .eyebrow,.register-card .eyebrow{color:#c8aa70;font-size:10px;font-weight:700;letter-spacing:3px;text-transform:uppercase}.register-side h2{position:relative;margin:14px 0 16px;font:500 clamp(28px,3.4vw,39px)/1.2 'Cinzel',serif;color:#f3f0e9}.register-side>p{position:relative;max-width:340px;color:#a1a6b1;font-size:13px;line-height:1.95}.register-side-rule{height:1px;width:100%;margin:30px 0;border-top:1px solid rgba(255,255,255,.1);border-bottom:1px solid rgba(200,170,112,.08)}.register-side-detail{display:flex;align-items:flex-start;gap:14px;margin:20px 0}.register-side-icon{display:flex;flex:0 0 35px;width:35px;height:35px;align-items:center;justify-content:center;border:1px solid rgba(200,170,112,.32);color:#c8aa70;font:11px 'Cinzel',serif}.register-side-detail strong,.register-side-detail small{display:block}.register-side-detail strong{color:#e9e5dc;font-size:12px;font-weight:600}.register-side-detail small{margin-top:4px;color:#8e95a1;font-size:11px;line-height:1.7}.register-back-link{display:inline-flex;gap:14px;align-items:center;margin-top:24px;padding-bottom:7px;border-bottom:1px solid rgba(200,170,112,.45);color:#c8aa70;font-size:10px;font-weight:700;letter-spacing:1.2px}.register-back-link span{font-size:15px}
.register-card{position:relative;padding:clamp(23px,4vw,42px);border:1px solid rgba(200,170,112,.26);background:linear-gradient(145deg,rgba(23,28,38,.98),rgba(13,16,22,.99));box-shadow:0 24px 70px rgba(0,0,0,.28)}.register-card:before,.register-card:after{content:'';position:absolute;width:22px;height:22px;pointer-events:none}.register-card:before{top:-1px;left:-1px;border-top:2px solid #c8aa70;border-left:2px solid #c8aa70}.register-card:after{right:-1px;bottom:-1px;border-right:2px solid #c8aa70;border-bottom:2px solid #c8aa70}.register-card-topline{display:flex;align-items:center;gap:10px;color:#858b96;font-size:8px;letter-spacing:2.3px}.register-card-topline span:first-child,.register-card-topline span:last-child{height:1px;flex:1;background:rgba(200,170,112,.24)}.register-card-heading{margin:22px 0 27px}.register-card-heading h2{margin:8px 0 9px;color:#f3f0e9;font:500 clamp(25px,3vw,33px)/1.2 'Cinzel',serif}.register-card-heading p{margin:0;color:#9da4b0;font-size:12px;line-height:1.8}
.register-form{display:grid;gap:20px}.register-field{min-width:0}.register-field label{display:flex;justify-content:space-between;align-items:center;gap:12px;margin-bottom:9px;color:#e7e3db;font-size:10px;font-weight:700;letter-spacing:.8px;text-transform:uppercase}.register-field label span{color:#8d929d;font-size:8px;letter-spacing:1px;white-space:nowrap}.register-input-wrap{display:flex;align-items:center;min-height:49px;border:1px solid rgba(255,255,255,.12);background:#0a0d12;transition:border-color .2s,box-shadow .2s}.register-input-wrap:focus-within{border-color:#c8aa70;box-shadow:0 0 0 3px rgba(200,170,112,.07)}.register-input-icon{display:flex;align-items:center;justify-content:center;flex:0 0 43px;color:#c8aa70;font-size:15px;border-right:1px solid rgba(255,255,255,.08)}.register-input-wrap input{width:100%;min-width:0;min-height:47px;padding:12px 14px;border:0;outline:0;background:transparent;color:#f3f0e9;font:12px 'Inter',sans-serif}.register-input-wrap input::placeholder{color:#626976}.register-field>small{display:block;margin-top:7px;color:#858c98;font-size:10px;line-height:1.65}.register-security-note{display:flex;gap:12px;align-items:flex-start;padding:13px 14px;border:1px solid rgba(200,170,112,.16);background:rgba(200,170,112,.045)}.register-security-note>span{color:#c8aa70;font-size:18px;line-height:1.2}.register-security-note p{margin:0;color:#a5aab4;font-size:10px;line-height:1.8}.register-submit{display:flex;justify-content:space-between;align-items:center;gap:20px;width:100%;min-height:52px;padding:0 18px;border:1px solid #c8aa70;background:linear-gradient(110deg,#c8aa70,#e0c794);color:#111219;font:700 10px 'Inter',sans-serif;letter-spacing:1.4px;cursor:pointer;transition:transform .2s,filter .2s}.register-submit:hover{filter:brightness(1.07);transform:translateY(-1px)}.register-submit b{font-size:20px;font-weight:400}.register-form-foot{margin:0;color:#7f8692;text-align:center;font-size:9px;line-height:1.8}.register-form-foot strong{color:#b9bdc6;font-weight:600}
.register-alert{padding:13px 15px;margin:0 0 20px;border:1px solid rgba(255,255,255,.12);font-size:11px;line-height:1.7}.register-alert ul{margin:7px 0 0;padding-left:18px}.register-alert-success{display:flex;gap:10px;border-color:rgba(92,180,125,.35);background:rgba(30,90,55,.12);color:#bfe6cc}.register-alert-success>span{font-size:16px}.register-alert-error{border-color:rgba(220,100,100,.35);background:rgba(110,30,30,.12);color:#f0bcbc}.register-alert-error strong{font-size:9px;letter-spacing:1.2px}
@media(max-width:850px){.register-content{grid-template-columns:1fr;max-width:720px}.register-side{max-width:620px;padding-top:0}.register-side-mark{top:-38px}.register-side-rule{margin:22px 0}.register-side-detail{display:inline-flex;vertical-align:top;width:31%;margin:12px 1% 12px 0}.register-side-detail .register-side-icon{flex-basis:30px;width:30px;height:30px}.register-back-link{margin-top:16px}}
@media(max-width:560px){.aion-register-page{padding-bottom:45px}.register-hero h1{font-size:34px}.register-content{padding-top:32px;gap:30px}.register-side{padding:0 2px}.register-side h2{font-size:29px}.register-side>p{font-size:12px}.register-side-detail{width:100%;margin:14px 0}.register-card{padding:23px 17px}.register-field label{font-size:9px}.register-field label span{font-size:7px}.register-form{gap:17px}}

.password-rules{margin-top:11px;padding:12px 14px;border:1px solid rgba(255,255,255,.09);background:rgba(4,6,10,.42)}.password-rules-title{margin:0 0 8px;color:#c8aa70;font-size:9px;font-weight:700;letter-spacing:1.6px}.password-rule-list{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:7px 12px;margin:0;padding:0;list-style:none}.password-rule-list li{display:flex;align-items:flex-start;gap:7px;color:#a0a6b1;font-size:10px;line-height:1.5}.password-rule-list .rule-mark{display:grid;place-items:center;flex:0 0 15px;width:15px;height:15px;border:1px solid rgba(220,105,105,.5);border-radius:50%;color:#f08a8a;font-size:11px;font-weight:700;line-height:1}.password-rule-list li.is-valid{color:#cfe7d5}.password-rule-list li.is-valid .rule-mark{border-color:#66c58a;color:#66c58a}.password-rule-list li.is-invalid .rule-mark{border-color:rgba(220,105,105,.5);color:#f08a8a}@media(max-width:560px){.password-rule-list{grid-template-columns:1fr}}

.register-input-wrap input.field-invalid{box-shadow:inset 0 0 0 1px #e66b73;color:#ffd5d7}.register-input-wrap:has(input.field-invalid){border-color:#e66b73;box-shadow:0 0 0 2px rgba(230,107,115,.09)}.register-input-wrap input.field-valid{box-shadow:inset 0 0 0 1px #66c58a}.register-input-wrap:has(input.field-valid){border-color:#66c58a}.field-validation-message{display:block;min-height:0;margin-top:5px;font-size:10px;line-height:1.5}.field-validation-message.is-error{color:#f08a8a}.field-validation-message.is-success{color:#75d69a}.register-submit:disabled{opacity:.55;cursor:not-allowed;transform:none}
</style>
@endpush


@push('scripts')
<script>
(() => {
    const form = document.querySelector('.register-form');
    const password = document.getElementById('password');
    const username = document.getElementById('username');
    const confirmation = document.getElementById('password_confirmation');
    const email = document.getElementById('email');
    if (!form || !password || !username || !confirmation || !email) return;

    const rules = {
        length: value => value.length >= 8 && value.length <= 16,
        upper: value => /[A-Z]/.test(value),
        lower: value => /[a-z]/.test(value),
        digit: value => /[0-9]/.test(value),
        special: value => /[!#$&()*+.\/:<>?@\[\]^_{|}~]/.test(value),
        ascii: value => value.length > 0 && /^[\x21-\x7E]+$/.test(value) && !/[\\',;\-`=%"]/.test(value),
        username: value => username.value.length > 0 && !value.toLowerCase().includes(username.value.toLowerCase()),
    };
    const setFieldState = (input, message, valid, text) => {
        input.classList.toggle('field-invalid', !valid);
        input.classList.toggle('field-valid', valid);
        input.setCustomValidity(valid ? '' : text);
        if (message) {
            message.textContent = text || (valid ? 'Looks good.' : 'Please check this field.');
            message.classList.toggle('is-error', !valid);
            message.classList.toggle('is-success', valid);
        }
    };
    function updatePasswordRules() {
        const value = password.value;
        Object.entries(rules).forEach(([name, test]) => {
            const item = document.querySelector(`#register-password-rules [data-rule="${name}"]`);
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
    function updateEmail() {
        const value = email.value.trim();
        const valid = value.length > 0 && !email.validity.typeMismatch && !email.validity.badInput && /^[^\s@]+@[^\s@]+\.[^\s@.]{2,}$/.test(value);
        const message = document.getElementById('email-feedback');
        setFieldState(email, message, valid, value.length === 0 ? 'Email is required.' : 'Enter a valid email address with @ and a domain extension, for example .com, .net, .org, or .co.id.');
        return valid;
    }
    function updateConfirmation() {
        const value = confirmation.value;
        const valid = value.length > 0 && value === password.value;
        const message = document.getElementById('register-confirm-feedback');
        setFieldState(confirmation, message, valid, value.length === 0 ? 'Please confirm your password.' : 'Passwords do not match.');
        return valid;
    }
    password.addEventListener('input', updatePasswordRules);
    username.addEventListener('input', updatePasswordRules);
    confirmation.addEventListener('input', updateConfirmation);
    email.addEventListener('input', updateEmail);
    email.addEventListener('blur', updateEmail);
    form.addEventListener('submit', event => {
        updatePasswordRules();
        const emailValid = updateEmail();
        const confirmationValid = updateConfirmation();
        const passwordValid = Object.values(rules).every(test => test(password.value));
        if (!emailValid || !confirmationValid || !passwordValid || !form.checkValidity()) {
            event.preventDefault();
            if (!emailValid) { email.reportValidity(); email.focus(); }
            else if (!passwordValid) { password.reportValidity(); password.focus(); }
            else { confirmation.reportValidity(); confirmation.focus(); }
        }
    });
    updatePasswordRules();
    if (email.value) updateEmail();
    if (confirmation.value) updateConfirmation();
})();
</script>
@endpush
