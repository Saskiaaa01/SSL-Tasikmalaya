<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Login | SSLGuard</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <!-- Font -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600&display=swap" rel="stylesheet" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        body {
            font-family: 'Inter', sans-serif;
            margin: 0; padding: 0;
            overflow: hidden;
        }
        .login-bg {
            position: relative;
            min-height: 100vh;
            background: linear-gradient(to bottom right, #f8fbff, #eef4ff);
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .circle-1, .circle-2 {
            position: absolute;
            border-radius: 50%;
            filter: blur(80px);
            z-index: 0;
        }
        .circle-1 {
            width: 530px;
            height: 530px;
            top: -120px;
            right: 8%;
            background: radial-gradient(circle at 30% 30%, #bfdbfe, #3b82f6);
            box-shadow: 0 0 80px rgba(96,165,250,0.45);
        }
        .circle-2 {
            width: 630px;
            height: 630px;
            bottom: -260px;
            left: 18%;
            background: radial-gradient(circle at 30% 30%, #93c5fd, #60a5fa);
            box-shadow: 0 0 80px rgba(96,165,250,0.45);
        }
        .curved-line {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            pointer-events: none;
            z-index: 0;
        }
        .diagonal-line {
            position: absolute;
            width: 100%;
            height: 100%;
            pointer-events: none;
            z-index: 0;
        }
        .login-card {
            position: relative;
            z-index: 10;
            width: 100%;
            max-width: 400px;
            background: rgba(255,255,255,0.65);
            backdrop-filter: blur(18px);
            -webkit-backdrop-filter: blur(18px);
            border: 1px solid rgba(255,255,255,0.8);
            box-shadow: 0 20px 50px rgba(37,99,235,0.15);
            border-radius: 20px;
            padding: 36px 34px;
        }
        .login-title {
            font-size: 30px;
            font-weight: 700;
            color: #0f172a;
            text-align: center;
            margin-bottom: 28px;
        }
        .login-input {
            width: 100%;
            height: 48px;
            border-radius: 9999px;
            padding: 0 22px;
            background: rgba(255,255,255,0.8);
            border: 1px solid #dbe4f3;
            color: #0f172a;
            font-size: 16px;
            margin-bottom: 16px;
        }
        .login-input::placeholder {
            color: #94a3b8;
        }
        .login-input:focus {
            outline: none;
            border-color: #3b82f6;
            box-shadow: 0 0 0 3px rgba(59,130,246,0.25);
        }
        .login-input:-webkit-autofill,
        .login-input:-webkit-autofill:hover,
        .login-input:-webkit-autofill:focus {
            -webkit-box-shadow: inset 0 0 0px 1000px rgba(255,255,255,0.8) !important;
            -webkit-text-fill-color: #0f172a !important;
        }
        .login-options {
            display: flex;
            justify-content: space-between;
            align-items: center;
            font-size: 14px;
            margin: 6px 0 22px;
        }
        .login-options a {
            color: #2563eb;
            text-decoration: none;
        }
        .login-options a:hover {
            text-decoration: underline;
        }
        .login-checkbox {
            accent-color: #2563eb;
            margin-right: 6px;
        }
        .login-btn {
            width: 100%;
            height: 46px;
            border-radius: 9999px;
            background: linear-gradient(90deg, #2563eb, #3b82f6);
            color: #fff;
            font-weight: 600;
            border: none;
            cursor: pointer;
            font-size: 16px;
        }
        .login-btn:hover {
            opacity: 0.95;
        }
        .register-link {
            text-align: center;
            margin-top: 18px;
            font-size: 14px;
            color: #64748b;
        }
        .register-link a {
            color: #2563eb;
            font-weight: 500;
            text-decoration: none;
        }
        .register-link a:hover {
            text-decoration: underline;
        }
        .error-msg {
            color: #dc2626;
            font-size: 14px;
            margin-top: 4px;
        }
    </style>
</head>
<body class="login-bg">
    <div class="circle-1"></div>
    <div class="circle-2"></div>
    <!-- Curved SVG line -->
    <svg class="curved-line" viewBox="0 0 1440 300" preserveAspectRatio="none" xmlns="http://www.w3.org/2000/svg">
        <path d="M0,150 C300,0 600,300 1440,150" stroke="#2563eb" stroke-width="2" fill="none" />
    </svg>
    <!-- Diagonal line (short) -->
    <svg class="diagonal-line" viewBox="0 0 100 100" preserveAspectRatio="none" xmlns="http://www.w3.org/2000/svg">
        <line x1="30" y1="30" x2="70" y2="70" stroke="#93c5fd" stroke-opacity="0.7" stroke-width="2" />
    </svg>
    <div class="login-card">
        @yield('content')
    </div>
</body>
</html>

