<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Daftar | SSLGuard</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            min-height: 100vh;
            background: linear-gradient(160deg, #f8fbff 0%, #eef4ff 100%);
            color: #0f172a;
            overflow-x: hidden;
        }
        .sg-auth {
            position: relative;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 40px 16px;
            overflow: hidden;
        }
        .sg-circle {
            position: absolute;
            border-radius: 50%;
            background: radial-gradient(circle at 30% 30%, #bfdbfe, #60a5fa 60%, #3b82f6);
            box-shadow: 0 0 80px rgba(96, 165, 250, 0.45);
            pointer-events: none;
            z-index: 0;
        }
        .sg-circle-1 { width: clamp(280px, 30vw, 530px); height: clamp(280px, 30vw, 530px); top: -14%; right: 22%; }
        .sg-circle-2 { width: clamp(320px, 34vw, 630px); height: clamp(320px, 34vw, 630px); bottom: -30%; left: 20%; }
        .sg-lines {
            position: absolute;
            inset: 0;
            width: 100%;
            height: 100%;
            pointer-events: none;
            z-index: 1;
        }
        .sg-card {
            position: relative;
            z-index: 10;
            width: 100%;
            max-width: 420px;
            padding: 36px 34px;
            border-radius: 20px;
            background: rgba(255, 255, 255, 0.65);
            backdrop-filter: blur(18px);
            -webkit-backdrop-filter: blur(18px);
            border: 1px solid rgba(255, 255, 255, 0.85);
            box-shadow: 0 20px 50px rgba(37, 99, 235, 0.15);
        }
        .sg-logo {
            width: 48px; height: 48px;
            margin: 0 auto 14px;
            border-radius: 12px;
            background: linear-gradient(135deg, #2563eb, #3b82f6);
            display: flex; align-items: center; justify-content: center;
            box-shadow: 0 8px 20px rgba(37, 99, 235, 0.3);
        }
        .sg-title { text-align: center; font-size: 28px; font-weight: 800; margin-bottom: 28px; }
        .sg-subtitle { text-align: center; font-size: 13px; color: #64748b; margin: 6px 0 26px; }
        .sg-field { margin-bottom: 14px; }
        .sg-input-wrap { position: relative; }
        .sg-input {
            width: 100%;
            height: 48px;
            padding: 0 22px;
            border-radius: 9999px;
            border: 1px solid #dbe4f3;
            background: rgba(255, 255, 255, 0.8);
            font: inherit;
            font-size: 14px;
            color: #0f172a;
            outline: none;
            transition: border-color .2s, box-shadow .2s;
        }
        .sg-input::placeholder { color: #94a3b8; }
        .sg-input:focus { border-color: #3b82f6; box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.25); }
        .sg-input:-webkit-autofill {
            -webkit-box-shadow: 0 0 0 1000px #ffffff inset;
            -webkit-text-fill-color: #0f172a;
        }
        .sg-input.has-toggle { padding-right: 52px; }
        .sg-toggle {
            position: absolute; right: 16px; top: 50%; transform: translateY(-50%);
            background: none; border: none; cursor: pointer; color: #94a3b8; display: flex;
        }
        .sg-toggle:hover { color: #2563eb; }
        .sg-error { color: #dc2626; font-size: 12px; margin: 6px 0 0 18px; }
        .sg-btn {
            width: 100%;
            height: 46px;
            margin-top: 8px;
            border: none;
            border-radius: 9999px;
            background: linear-gradient(90deg, #2563eb, #3b82f6);
            color: #fff;
            font: inherit;
            font-weight: 600;
            font-size: 15px;
            cursor: pointer;
            box-shadow: 0 8px 20px rgba(37, 99, 235, 0.25);
            transition: background .2s, transform .1s;
        }
        .sg-btn:hover { background: #1d4ed8; }
        .sg-btn:active { transform: scale(0.99); }
        .sg-footer { text-align: center; font-size: 13px; color: #64748b; margin-top: 18px; }
        .sg-footer a { color: #2563eb; font-weight: 700; text-decoration: none; }
        .sg-footer a:hover { text-decoration: underline; }
        @media (max-width: 480px) {
            .sg-card { padding: 30px 22px; }
        }
    </style>
</head>
<body>
<div class="sg-auth">
    <div class="sg-circle sg-circle-1"></div>
    <div class="sg-circle sg-circle-2"></div>

    <svg class="sg-lines" viewBox="0 0 1000 1000" preserveAspectRatio="none" aria-hidden="true">
        <defs>
            <filter id="sgGlow" x="-20%" y="-20%" width="140%" height="140%">
                <feGaussianBlur stdDeviation="3" result="b" />
                <feMerge><feMergeNode in="b" /><feMergeNode in="SourceGraphic" /></feMerge>
            </filter>
        </defs>
        <line x1="320" y1="210" x2="680" y2="690" stroke="#93c5fd" stroke-width="1.2" opacity="0.7" />
        <path d="M 520 20 C 440 80, 430 180, 455 260" fill="none" stroke="#2563eb" stroke-width="2.5" filter="url(#sgGlow)" />
        <path d="M 480 720 C 420 800, 330 840, 250 845" fill="none" stroke="#2563eb" stroke-width="2.5" filter="url(#sgGlow)" />
    </svg>

    <div class="sg-card">
        <h1 class="sg-title">Daftar Akun SSLGuard</h1>

        <form method="POST" action="{{ route('register') }}">
            @csrf

            <div class="sg-field">
                <input class="sg-input" type="text" name="name" value="{{ old('name') }}" placeholder="Nama lengkap" required autofocus autocomplete="name">
                @if ($errors->has('name'))
                    <p class="sg-error">{{ $errors->first('name') }}</p>
                @endif
            </div>

            <div class="sg-field">
                <input class="sg-input" type="email" name="email" value="{{ old('email') }}" placeholder="Email" required autocomplete="username">
                @if ($errors->has('email'))
                    <p class="sg-error">{{ $errors->first('email') }}</p>
                @endif
            </div>

            <div class="sg-field">
                <div class="sg-input-wrap">
                    <input class="sg-input has-toggle" type="password" name="password" id="password" placeholder="Password" required autocomplete="new-password">
                    <button type="button" class="sg-toggle" data-target="password" aria-label="Tampilkan password">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7S2 12 2 12z"/><circle cx="12" cy="12" r="3"/></svg>
                    </button>
                </div>
                @if ($errors->has('password'))
                    <p class="sg-error">{{ $errors->first('password') }}</p>
                @endif
            </div>

            <div class="sg-field">
                <div class="sg-input-wrap">
                    <input class="sg-input has-toggle" type="password" name="password_confirmation" id="password_confirmation" placeholder="Konfirmasi password" required autocomplete="new-password">
                    <button type="button" class="sg-toggle" data-target="password_confirmation" aria-label="Tampilkan konfirmasi password">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7S2 12 2 12z"/><circle cx="12" cy="12" r="3"/></svg>
                    </button>
                </div>
                @if ($errors->has('password_confirmation'))
                    <p class="sg-error">{{ $errors->first('password_confirmation') }}</p>
                @endif
            </div>

            <button type="submit" class="sg-btn">Daftar</button>
        </form>

        <p class="sg-footer">Sudah punya akun? <a href="{{ route('login') }}">Masuk</a></p>
    </div>
</div>

<script>
    document.querySelectorAll('.sg-toggle').forEach(function (btn) {
        btn.addEventListener('click', function () {
            var input = document.getElementById(btn.dataset.target);
            input.type = input.type === 'password' ? 'text' : 'password';
        });
    });
</script>
</body>
</html>