<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex,nofollow">
    <title>{{ $siteName }} — Maintenance</title>
    <style>
        *{box-sizing:border-box}body{margin:0;min-height:100vh;display:grid;place-items:center;background:radial-gradient(ellipse at top,#252016 0,#0b0d12 55%);color:#f3f0e9;font-family:Inter,Arial,sans-serif;text-align:center;padding:24px}.card{max-width:680px;padding:48px 36px;border:1px solid rgba(200,170,112,.35);background:rgba(17,21,29,.88);box-shadow:0 25px 90px #0008}.mark{font-size:12px;letter-spacing:5px;color:#c8aa70;text-transform:uppercase}.sigil{font-size:44px;color:#c8aa70;margin:22px 0}h1{font-family:Georgia,serif;font-size:clamp(30px,6vw,48px);letter-spacing:2px;margin:0 0 16px}p{color:#a1a6b1;line-height:1.8;margin:0}.line{height:1px;background:linear-gradient(90deg,transparent,#c8aa70,transparent);margin:28px 0}
    </style>
</head>
<body><main class="card"><div class="mark">{{ $siteName }}</div><div class="sigil">✦</div><h1>THE GATES ARE TEMPORARILY CLOSED</h1><div class="line"></div><p>{{ $message ?: 'We will be back soon.' }}</p></main></body>
</html>
