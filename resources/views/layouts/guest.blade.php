<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>Login | SSLGuard</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=inter:400,500,600&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
        <style>
            /* Glassmorphism card */
            .glass-card {
                background: rgba(255,255,255,0.6);
                backdrop-filter: blur(18px);
                -webkit-backdrop-filter: blur(18px);
                border: 1px solid rgba(255,255,255,0.8);
                box-shadow: 0 20px 50px rgba(37,99,235,0.15);
            }
            /* Remove default input outline */
            .custom-input:focus {
                outline: none;
                border-color: #3b82f6;
                box-shadow: 0 0 0 3px rgba(59,130,246,0.25);
            }
            /* Chrome autofill */
            input:-webkit-autofill,
            input:-webkit-autofill:hover,
            input:-webkit-autofill:focus {
                -webkit-box-shadow: inset 0 0 0px 1000px rgba(255,255,255,0.75) !important;
                -webkit-text-fill-color: #0f172a !important;
            }
        </style>
    </head>
    <body class="font-sans antialiased">
        <div class="relative min-h-screen flex items-center justify-center bg-gradient-to-br from-[#f8fbff] to-[#eef4ff] overflow-hidden">
            <!-- Decorative circles -->
            <div class="absolute top-[-250px] right-[-250px] w-[530px] h-[530px] rounded-full" style="background: radial-gradient(circle at 30% 30%, #93c5fd, #60a5fa); filter: blur(80px);"></div>
            <div class="absolute bottom-[-300px] left-[-300px] w-[630px] h-[630px] rounded-full" style="background: radial-gradient(circle at 70% 70%, #bfdbfe, #3b82f6); filter: blur(80px);"></div>
            <!-- Curved line (SVG) -->
            <svg class="absolute" width="100%" height="100%" viewBox="0 0 1440 800" preserveAspectRatio="none" style="pointer-events:none;">
                <path d="M0,200 C400,100 800,300 1440,200" stroke="#2563eb" stroke-width="2" fill="none" filter="url(#blur)"/>
                <defs>
                    <filter id="blur"><feGaussianBlur stdDeviation="2"/></filter>
                </defs>
            </svg>
            <!-- Diagonal line -->
            <div class="absolute inset-0">
                <svg class="w-full h-full" style="pointer-events:none;">
                    <line x1="0" y1="0" x2="100%" y2="100%" stroke="#93c5fd" stroke-opacity="0.7" stroke-width="2" />
                </svg>
            </div>

            <div class="glass-card rounded-2xl p-8 w-[380px] sm:w-[90%]">
                {{ $slot }}
            </div>
        </div>
    </body>
</html>
