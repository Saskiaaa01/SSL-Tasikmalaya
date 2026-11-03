<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>SSLGuard - Monitoring Sertifikat SSL/TLS Kota Tasikmalaya</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600,700" rel="stylesheet" />

        <!-- Scripts & Styles -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="bg-gray-50 text-gray-900 antialiased min-h-screen flex flex-col justify-between selection:bg-indigo-500 selection:text-white">
        <!-- Navigation Header -->
        <header class="w-full bg-white border-b border-gray-100 shadow-xs sticky top-0 z-50">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-16 flex items-center justify-between">
                <div class="flex items-center space-x-3">
                    <div class="h-10 w-10 bg-indigo-600 rounded-xl flex items-center justify-center text-white shadow-sm shadow-indigo-200">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path></svg>
                    </div>
                    <div>
                        <span class="font-bold text-xl tracking-tight text-gray-900">SSL<span class="text-indigo-600">Guard</span></span>
                        <span class="hidden sm:inline-block ml-2 px-2 py-0.5 text-[10px] font-semibold bg-indigo-50 text-indigo-700 rounded-full border border-indigo-100">Diskominfo Kota Tasikmalaya</span>
                    </div>
                </div>

                <div class="flex items-center space-x-3">
                    @auth
                        <a href="{{ url('/dashboard') }}" class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-lg font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 shadow-sm transition">
                            Buka Dashboard &rarr;
                        </a>
                    @else
                        <a href="{{ route('login') }}" class="px-4 py-2 text-sm font-semibold text-gray-700 hover:text-indigo-600 transition">
                            Masuk
                        </a>
                        @if (Route::has('register'))
                            <a href="{{ route('register') }}" class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-lg font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 shadow-sm transition">
                                Daftar Akun
                            </a>
                        @endif
                    @endauth
                </div>
            </div>
        </header>

        <!-- Hero Section -->
        <main class="flex-grow flex items-center py-12 lg:py-20">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 w-full">
                <div class="grid grid-cols-1 lg:grid-cols-12 gap-12 items-center">
                    
                    <!-- Left Column (Copywriting & CTA) -->
                    <div class="lg:col-span-7 space-y-6 text-center lg:text-left">
                        <div class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-indigo-50 text-indigo-700 border border-indigo-200">
                            <span class="w-2 h-2 rounded-full bg-emerald-500 mr-2 animate-pulse"></span>
                            Sistem Monitoring Aktif 24/7
                        </div>

                        <h1 class="text-4xl sm:text-5xl lg:text-6xl font-extrabold text-gray-900 tracking-tight leading-tight">
                            Monitoring Masa Berlaku <br class="hidden sm:inline">
                            <span class="text-transparent bg-clip-text bg-gradient-to-r from-indigo-600 to-blue-600">Sertifikat SSL/TLS</span>
                        </h1>

                        <p class="text-lg text-gray-600 max-w-2xl mx-auto lg:mx-0 leading-relaxed">
                            Pantau kesehatan dan masa aktif seluruh domain layanan publik Kota Tasikmalaya. Dapatkan peringatan dini otomatis melalui WhatsApp sebelum sertifikat kadaluarsa.
                        </p>

                        <!-- CTA Buttons -->
                        <div class="flex flex-col sm:flex-row items-center justify-center lg:justify-start gap-4 pt-2">
                            @auth
                                <a href="{{ url('/dashboard') }}" class="w-full sm:w-auto inline-flex justify-center items-center px-6 py-3.5 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl font-bold text-base shadow-lg shadow-indigo-200 transition">
                                    Menuju Dashboard SSL &rarr;
                                </a>
                            @else
                                <a href="{{ route('login') }}" class="w-full sm:w-auto inline-flex justify-center items-center px-6 py-3.5 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl font-bold text-base shadow-lg shadow-indigo-200 transition">
                                    Masuk ke Sistem
                                </a>
                                @if (Route::has('register'))
                                    <a href="{{ route('register') }}" class="w-full sm:w-auto inline-flex justify-center items-center px-6 py-3.5 bg-white hover:bg-gray-50 text-gray-800 border border-gray-300 rounded-xl font-semibold text-base transition">
                                        Daftar Pengelola Domain
                                    </a>
                                @endif
                            @endauth
                        </div>

                        <!-- Highlights List -->
                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 pt-6 border-t border-gray-100 text-left">
                            <div class="flex items-start space-x-3">
                                <div class="p-2 bg-emerald-100 text-emerald-700 rounded-lg">✓</div>
                                <div>
                                    <div class="font-semibold text-sm text-gray-900">Peringatan Dini</div>
                                    <div class="text-xs text-gray-500">Alert H-30, H-14, H-7, & Expired</div>
                                </div>
                            </div>
                            <div class="flex items-start space-x-3">
                                <div class="p-2 bg-indigo-100 text-indigo-700 rounded-lg">🛡️</div>
                                <div>
                                    <div class="font-semibold text-sm text-gray-900">Proteksi SSRF</div>
                                    <div class="text-xs text-gray-500">Validasi IP publik ketat & aman</div>
                                </div>
                            </div>
                            <div class="flex items-start space-x-3">
                                <div class="p-2 bg-blue-100 text-blue-700 rounded-lg">📱</div>
                                <div>
                                    <div class="font-semibold text-sm text-gray-900">WhatsApp Alert</div>
                                    <div class="text-xs text-gray-500">Notifikasi langsung ke admin</div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Right Column (Card Preview) -->
                    <div class="lg:col-span-5">
                        <div class="bg-white rounded-2xl shadow-xl border border-gray-100 p-6 space-y-4">
                            <div class="flex justify-between items-center pb-4 border-b border-gray-100">
                                <div class="flex items-center space-x-2">
                                    <div class="w-3 h-3 rounded-full bg-rose-400"></div>
                                    <div class="w-3 h-3 rounded-full bg-amber-400"></div>
                                    <div class="w-3 h-3 rounded-full bg-emerald-400"></div>
                                    <span class="text-xs font-mono text-gray-400 ml-2">sslguard-monitor</span>
                                </div>
                                <span class="px-2 py-0.5 rounded text-[10px] font-semibold bg-emerald-100 text-emerald-800">Online</span>
                            </div>

                            <!-- Mock Sample Items -->
                            <div class="space-y-3">
                                <div class="p-3 bg-gray-50 rounded-xl flex items-center justify-between border border-gray-100">
                                    <div>
                                        <div class="text-sm font-bold text-gray-900">tasikmalayakota.go.id</div>
                                        <div class="text-xs text-gray-500">Let's Encrypt Authority • Port 443</div>
                                    </div>
                                    <span class="px-2.5 py-1 rounded-full text-xs font-semibold bg-emerald-100 text-emerald-800">✓ 84 Hari</span>
                                </div>

                                <div class="p-3 bg-gray-50 rounded-xl flex items-center justify-between border border-gray-100">
                                    <div>
                                        <div class="text-sm font-bold text-gray-900">diskominfo.tasikmalayakota.go.id</div>
                                        <div class="text-xs text-gray-500">DigiCert Global Root • Port 443</div>
                                    </div>
                                    <span class="px-2.5 py-1 rounded-full text-xs font-semibold bg-emerald-100 text-emerald-800">✓ 42 Hari</span>
                                </div>

                                <div class="p-3 bg-amber-50/60 rounded-xl flex items-center justify-between border border-amber-100">
                                    <div>
                                        <div class="text-sm font-bold text-gray-900">layanan.tasikmalayakota.go.id</div>
                                        <div class="text-xs text-amber-700">Peringatan: Perlu perpanjangan segera</div>
                                    </div>
                                    <span class="px-2.5 py-1 rounded-full text-xs font-semibold bg-amber-100 text-amber-800">⚠️ 6 Hari</span>
                                </div>
                            </div>

                            <div class="pt-4 border-t border-gray-100 text-center">
                                <p class="text-xs text-gray-500">Pemeriksaan otomatis harian pukul 07.00 WIB</p>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </main>

        <!-- Footer -->
        <footer class="bg-white border-t border-gray-100 py-6 text-center text-xs text-gray-500">
            <div class="max-w-7xl mx-auto px-4">
                &copy; {{ date('Y') }} SSLGuard. Dinas Komunikasi dan Informatika Kota Tasikmalaya.
            </div>
        </footer>
    </body>
</html>
