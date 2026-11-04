<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'SSLGuard') | SSLGuard</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=JetBrains+Mono:wght@500;600&display=swap" rel="stylesheet">
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: 'Plus Jakarta Sans', sans-serif; background: #f4f7fc; color: #0f172a; font-size: 14px; }
        a { color: inherit; text-decoration: none; }
        button { font: inherit; cursor: pointer; }
        svg { flex-shrink: 0; }
        .mono { font-family: 'JetBrains Mono', monospace; }

        /* ===== Sidebar ===== */
        .sg-sidebar {
            position: fixed; top: 0; left: 0; bottom: 0; width: 260px; z-index: 40;
            background: #fff; border-right: 1px solid #e5eaf3;
            display: flex; flex-direction: column; transition: transform .25s;
        }
        .sg-brand { display: flex; align-items: center; gap: 12px; padding: 22px 20px; border-bottom: 1px solid #eef2f7; }
        .sg-brand-logo {
            width: 42px; height: 42px; border-radius: 12px; display: flex; align-items: center; justify-content: center;
            background: linear-gradient(135deg, #2563eb, #3b82f6); box-shadow: 0 6px 16px rgba(37, 99, 235, .3);
        }
        .sg-brand-name { font-size: 19px; font-weight: 800; line-height: 1.1; }
        .sg-brand-sub { font-size: 10px; font-weight: 700; letter-spacing: .12em; color: #2563eb; margin-top: 3px; }
        .sg-nav { flex: 1; overflow-y: auto; padding: 18px 12px; }
        .sg-nav-label { font-size: 11px; font-weight: 700; letter-spacing: .1em; color: #94a3b8; padding: 0 10px; margin: 6px 0 10px; }
        .sg-nav-label + .sg-nav-link { margin-top: 0; }
        .sg-nav-group { margin-bottom: 18px; }
        .sg-nav-link {
            display: flex; align-items: center; gap: 12px; padding: 10px 12px; margin-bottom: 4px;
            border-radius: 10px; border: 1px solid transparent; color: #475569; font-weight: 500; transition: background .15s;
        }
        .sg-nav-link:hover { background: #f8fafc; }
        .sg-nav-link.active { background: #eff6ff; border-color: #bfdbfe; color: #2563eb; font-weight: 700; }
        .sg-nav-badge {
            margin-left: auto; min-width: 22px; padding: 1px 7px; border-radius: 999px; text-align: center;
            font-size: 11px; font-weight: 700; background: #e2e8f0; color: #475569;
        }
        .sg-nav-link.active .sg-nav-badge { background: #dbeafe; color: #2563eb; }
        .sg-sidebar-bottom { padding: 14px; border-top: 1px solid #eef2f7; display: grid; gap: 10px; }
        .sg-mini-card { border: 1px solid #e5eaf3; border-radius: 12px; padding: 12px 14px; background: #f8fafc; }
        .sg-mini-title { display: flex; align-items: center; gap: 8px; font-weight: 700; font-size: 13px; }
        .sg-dot { width: 8px; height: 8px; border-radius: 50%; background: #10b981; box-shadow: 0 0 0 3px rgba(16, 185, 129, .2); }
        .sg-mini-text { font-size: 12px; color: #64748b; margin-top: 6px; line-height: 1.5; }
        .sg-mini-text b { color: #334155; }
        .sg-user { display: flex; align-items: center; gap: 10px; }
        .sg-avatar {
            width: 38px; height: 38px; border-radius: 50%; display: flex; align-items: center; justify-content: center;
            background: linear-gradient(135deg, #2563eb, #3b82f6); color: #fff; font-weight: 700; font-size: 13px;
        }
        .sg-user-info { flex: 1; min-width: 0; }
        .sg-user-name { font-weight: 700; font-size: 13px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
        .sg-user-role { font-size: 11px; color: #64748b; }
        .sg-logout {
            width: 34px; height: 34px; border-radius: 9px; border: 1px solid #fecaca; background: #fef2f2; color: #dc2626;
            display: flex; align-items: center; justify-content: center;
        }
        .sg-logout:hover { background: #fee2e2; }

        /* ===== Main ===== */
        .sg-main { margin-left: 260px; min-height: 100vh; min-width: 0; }
        .sg-header {
            background: #fff; border-bottom: 1px solid #e5eaf3; padding: 22px 32px;
            display: flex; align-items: center; justify-content: space-between; gap: 20px; flex-wrap: wrap;
            position: sticky; top: 0; z-index: 30;
        }
        .sg-header-left { display: flex; align-items: center; gap: 14px; min-width: 0; }
        .sg-page-title { font-size: 24px; font-weight: 800; letter-spacing: -.01em; }
        .sg-page-sub { color: #64748b; font-size: 13px; margin-top: 4px; }
        .sg-header-actions { display: flex; align-items: center; gap: 10px; flex-wrap: wrap; }
        .sg-clock {
            display: inline-flex; align-items: center; gap: 8px; height: 40px; padding: 0 16px;
            border: 1px solid #e5eaf3; border-radius: 999px; color: #475569; font-size: 13px; background: #fff;
        }
        .sg-hamburger { display: none; width: 40px; height: 40px; border-radius: 10px; border: 1px solid #e5eaf3; background: #fff; align-items: center; justify-content: center; }
        .sg-content { padding: 28px 32px 40px; }

        /* ===== Buttons ===== */
        .sg-btn {
            display: inline-flex; align-items: center; justify-content: center; gap: 8px; height: 40px; padding: 0 16px;
            border-radius: 10px; font-weight: 700; font-size: 13px; border: 1px solid transparent; transition: all .15s; white-space: nowrap;
        }
        .sg-btn-primary { background: linear-gradient(90deg, #2563eb, #3b82f6); color: #fff; box-shadow: 0 6px 16px rgba(37, 99, 235, .25); }
        .sg-btn-primary:hover { background: #1d4ed8; }
        .sg-btn-outline { background: #fff; border-color: #dbe4f3; color: #0f172a; }
        .sg-btn-outline:hover { background: #f8fafc; border-color: #bfdbfe; }
        .sg-btn-sm { height: 30px; padding: 0 12px; font-size: 12px; border-radius: 8px; }
        .sg-btn-soft { background: #eff6ff; color: #2563eb; border-color: #dbeafe; }
        .sg-btn-soft:hover { background: #dbeafe; }

        /* ===== Cards & panels ===== */
        .sg-card { background: #fff; border: 1px solid #e5eaf3; border-radius: 16px; box-shadow: 0 4px 20px rgba(15, 23, 42, .04); }
        .sg-panel-head { display: flex; align-items: center; justify-content: space-between; gap: 12px; padding: 18px 22px; border-bottom: 1px solid #eef2f7; }
        .sg-panel-title { display: flex; align-items: center; gap: 10px; font-size: 16px; font-weight: 800; }
        .sg-panel-sub { font-size: 12px; color: #64748b; margin-top: 3px; }
        .sg-panel-body { padding: 18px 22px; }
        .sg-link { color: #2563eb; font-weight: 700; font-size: 13px; }
        .sg-link:hover { text-decoration: underline; }

        /* ===== Badges ===== */
        .sg-pill {
            display: inline-flex; align-items: center; gap: 5px; padding: 4px 11px; border-radius: 999px;
            font-size: 12px; font-weight: 700; border: 1px solid; white-space: nowrap;
        }
        .sg-pill.mono { font-weight: 600; }
        .tone-green { color: #059669; background: #ecfdf5; border-color: #a7f3d0; }
        .tone-amber { color: #d97706; background: #fffbeb; border-color: #fde68a; }
        .tone-red { color: #dc2626; background: #fef2f2; border-color: #fecaca; }
        .tone-gray { color: #475569; background: #f1f5f9; border-color: #e2e8f0; }
        .tone-blue { color: #2563eb; background: #eff6ff; border-color: #bfdbfe; }
        .ic-green { color: #059669; } .ic-amber { color: #d97706; } .ic-red { color: #dc2626; } .ic-gray { color: #94a3b8; } .ic-blue { color: #2563eb; }

        /* ===== Table ===== */
        .sg-table-wrap { overflow-x: auto; }
        .sg-table { width: 100%; border-collapse: collapse; }
        .sg-table th {
            text-align: left; font-size: 11px; font-weight: 700; letter-spacing: .08em; text-transform: uppercase;
            color: #64748b; background: #f8fafc; padding: 13px 20px; border-bottom: 1px solid #eef2f7; white-space: nowrap;
        }
        .sg-table td { padding: 15px 20px; border-bottom: 1px solid #eef2f7; vertical-align: middle; }
        .sg-table tbody tr:hover { background: #f8fafc; }
        .sg-table tbody tr:last-child td { border-bottom: none; }
        .sg-empty { text-align: center; padding: 40px 20px; color: #64748b; }
        .sg-empty-title { font-weight: 700; color: #0f172a; margin: 10px 0 4px; font-size: 15px; }

        /* ===== Toast ===== */
        .sg-toast {
            position: fixed; top: 20px; right: 20px; z-index: 100; display: flex; align-items: center; gap: 10px;
            padding: 12px 18px; border-radius: 12px; font-weight: 600; font-size: 13px; border: 1px solid;
            box-shadow: 0 12px 30px rgba(15, 23, 42, .12); transition: opacity .4s, transform .4s;
        }
        .sg-toast.hide { opacity: 0; transform: translateY(-10px); pointer-events: none; }

        .sg-overlay { display: none; position: fixed; inset: 0; background: rgba(15, 23, 42, .35); z-index: 35; }

        /* ===== Modal Tambah Domain ===== */
        .sg-modal-backdrop {
            position: fixed; inset: 0; z-index: 90; display: none; align-items: center; justify-content: center; padding: 20px;
            background: rgba(15, 23, 42, .35); backdrop-filter: blur(6px); -webkit-backdrop-filter: blur(6px);
        }
        .sg-modal-backdrop.open { display: flex; animation: sgFade .2s ease; }
        .sg-modal {
            width: 100%; max-width: 540px; max-height: calc(100vh - 40px); overflow-y: auto;
            background: #fff; border: 1px solid #e5eaf3; border-radius: 18px;
            box-shadow: 0 25px 60px rgba(15, 23, 42, .18); animation: sgPop .22s ease;
        }
        @keyframes sgFade { from { opacity: 0; } to { opacity: 1; } }
        @keyframes sgPop { from { opacity: 0; transform: translateY(12px) scale(.98); } to { opacity: 1; transform: none; } }
        .sg-modal-head { display: flex; align-items: flex-start; gap: 14px; padding: 22px 24px; border-bottom: 1px solid #eef2f7; }
        .sg-modal-icon { width: 42px; height: 42px; border-radius: 11px; background: #eff6ff; border: 1px solid #dbeafe; color: #2563eb; display: flex; align-items: center; justify-content: center; }
        .sg-modal-title { font-size: 17px; font-weight: 800; }
        .sg-modal-sub { font-size: 12.5px; color: #64748b; margin-top: 3px; }
        .sg-modal-close { margin-left: auto; width: 32px; height: 32px; border-radius: 8px; border: none; background: transparent; color: #94a3b8; display: flex; align-items: center; justify-content: center; }
        .sg-modal-close:hover { background: #f1f5f9; color: #0f172a; }
        .sg-modal-body { padding: 22px 24px; }
        .sg-modal-foot { display: flex; justify-content: flex-end; gap: 10px; padding: 16px 24px; border-top: 1px solid #eef2f7; }
        .sg-label { display: block; font-size: 13px; font-weight: 700; color: #334155; margin-bottom: 8px; }
        .sg-label .req { color: #dc2626; }
        .sg-field-group { margin-bottom: 18px; }
        .sg-input-group { display: flex; border: 1px solid #dbe4f3; border-radius: 10px; overflow: hidden; transition: border-color .15s, box-shadow .15s; background: #fff; }
        .sg-input-group:focus-within { border-color: #3b82f6; box-shadow: 0 0 0 3px rgba(59, 130, 246, .2); }
        .sg-input-prefix { display: flex; align-items: center; padding: 0 14px; background: #f8fafc; border-right: 1px solid #e5eaf3; color: #64748b; font-size: 13px; }
        .sg-input-group input { flex: 1; min-width: 0; border: none; outline: none; height: 44px; padding: 0 14px; font: inherit; font-size: 14px; color: #0f172a; background: transparent; }
        .sg-text-input { width: 180px; height: 44px; padding: 0 14px; border: 1px solid #dbe4f3; border-radius: 10px; font: inherit; font-size: 14px; color: #0f172a; outline: none; transition: border-color .15s, box-shadow .15s; }
        .sg-text-input:focus { border-color: #3b82f6; box-shadow: 0 0 0 3px rgba(59, 130, 246, .2); }
        .sg-input-group input::placeholder, .sg-text-input::placeholder { color: #94a3b8; }
        .sg-hint { font-size: 12px; color: #64748b; margin-top: 6px; }
        .sg-hint code { color: #2563eb; font-family: 'JetBrains Mono', monospace; font-size: 11.5px; }
        .sg-field-error { font-size: 12px; color: #dc2626; margin-top: 6px; }
        .sg-has-error .sg-input-group, .sg-has-error .sg-text-input { border-color: #fca5a5; }
        .sg-alert {
            display: flex; gap: 12px; padding: 14px 16px; border-radius: 12px; border: 1px solid #fecaca; background: #fef2f2;
            font-size: 12.5px; color: #7f1d1d; line-height: 1.55;
        }
        .sg-alert b { color: #b91c1c; }
        .sg-alert code { font-family: 'JetBrains Mono', monospace; font-size: 11.5px; color: #991b1b; }

        @media (max-width: 1024px) {
            .sg-sidebar { transform: translateX(-100%); }
            .sg-sidebar.open { transform: translateX(0); box-shadow: 0 0 40px rgba(15, 23, 42, .2); }
            .sg-overlay.open { display: block; }
            .sg-main { margin-left: 0; }
            .sg-hamburger { display: inline-flex; }
            .sg-header { padding: 16px 18px; position: static; }
            .sg-content { padding: 20px 18px 32px; }
        }
    </style>
    @stack('styles')
</head>
<body>
@php
    $sgUser = auth()->user();
    $sgIsAdmin = $sgUser && method_exists($sgUser, 'isAdmin') && $sgUser->isAdmin();
    $sgDomainCount = $sgUser
        ? ($sgIsAdmin ? \App\Models\Domain::count() : \App\Models\Domain::where('user_id', $sgUser->id)->count())
        : 0;
    $sgInitials = $sgUser
        ? collect(explode(' ', trim($sgUser->name)))->filter()->take(2)->map(fn ($w) => mb_strtoupper(mb_substr($w, 0, 1)))->implode('')
        : '?';
@endphp

<div class="sg-overlay" id="sgOverlay"></div>

<aside class="sg-sidebar" id="sgSidebar">
    <div class="sg-brand">
        <div class="sg-brand-logo">
            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/><path d="m9 12 2 2 4-4"/></svg>
        </div>
        <div>
            <div class="sg-brand-name">SSLGuard</div>
            <div class="sg-brand-sub">KOMINFO MONITORING</div>
        </div>
    </div>

    <nav class="sg-nav">
        <div class="sg-nav-group">
            <div class="sg-nav-label">MENU UTAMA</div>
            <a href="{{ route('dashboard') }}" class="sg-nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="7" height="7" rx="1"/><rect x="14" y="3" width="7" height="7" rx="1"/><rect x="14" y="14" width="7" height="7" rx="1"/><rect x="3" y="14" width="7" height="7" rx="1"/></svg>
                Dashboard
            </a>
            <a href="{{ route('domains.index') }}" class="sg-nav-link {{ request()->routeIs('domains.*') ? 'active' : '' }}">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><path d="M2 12h20"/><path d="M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z"/></svg>
                Kelola Website
                <span class="sg-nav-badge">{{ $sgDomainCount }}</span>
            </a>
            @if (Route::has('whatsapp.log'))
                <a href="{{ route('whatsapp.log') }}" class="sg-nav-link {{ request()->routeIs('whatsapp.*') ? 'active' : '' }}">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72c.13.96.36 1.9.7 2.81a2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45c.91.34 1.85.57 2.81.7A2 2 0 0 1 22 16.92z"/></svg>
                    Log WhatsApp
                </a>
            @endif
        </div>

        @if (Route::has('profile.edit'))
            <div class="sg-nav-group">
                <div class="sg-nav-label">AKUN</div>
                <a href="{{ route('profile.edit') }}" class="sg-nav-link {{ request()->routeIs('profile.*') ? 'active' : '' }}">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M19 21v-2a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                    Profil Saya
                </a>
            </div>
        @endif

        @if (Route::has('admin.users'))
            <div class="sg-nav-group">
                <div class="sg-nav-label">ADMINISTRASI</div>
                <a href="{{ route('admin.users') }}" class="sg-nav-link {{ request()->routeIs('admin.*') ? 'active' : '' }}">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                    Pengguna &amp; Role
                </a>
            </div>
        @endif
    </nav>

    <div class="sg-sidebar-bottom">
        <div class="sg-mini-card">
            <div class="sg-mini-title"><span class="sg-dot"></span> WhatsApp Cloud API</div>
            <div class="sg-mini-text">Meta Platform: <b>Aktif</b><br>Scheduler: <b>07:00 WIB (Daily)</b></div>
        </div>
        <div class="sg-mini-card sg-user">
            <div class="sg-avatar">{{ $sgInitials }}</div>
            <div class="sg-user-info">
                @if (Route::has('profile.edit'))
                    <a href="{{ route('profile.edit') }}" class="sg-user-name" title="Profil">{{ $sgUser->name ?? '-' }}</a>
                @else
                    <div class="sg-user-name">{{ $sgUser->name ?? '-' }}</div>
                @endif
                <div class="sg-user-role">{{ $sgIsAdmin ? 'Administrator' : 'Pengguna' }}</div>
            </div>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="sg-logout" title="Keluar">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg>
                </button>
            </form>
        </div>
    </div>
</aside>

<div class="sg-main">
    <header class="sg-header">
        <div class="sg-header-left">
            <button type="button" class="sg-hamburger" id="sgHamburger" aria-label="Buka menu">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M4 6h16M4 12h16M4 18h16"/></svg>
            </button>
            <div>
                <h1 class="sg-page-title">@yield('page-title', 'Dashboard')</h1>
                <p class="sg-page-sub">@yield('page-subtitle')</p>
            </div>
        </div>
        <div class="sg-header-actions">
            <span class="sg-clock">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                <span id="sgClock">-</span>
            </span>
            @hasSection('header-actions')
                @yield('header-actions')
            @else
                <form action="{{ route('ssl.check-all') }}" method="POST">
                    @csrf
                    <button type="submit" class="sg-btn sg-btn-outline">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 12a9 9 0 1 1-3-6.7L21 8"/><path d="M21 3v5h-5"/></svg>
                        Scan Semua Domain
                    </button>
                </form>
                <a href="{{ route('domains.create') }}" class="sg-btn sg-btn-primary" data-open-domain-modal>
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><path d="M12 5v14M5 12h14"/></svg>
                    Tambah Domain
                </a>
            @endif
        </div>
    </header>

    <main class="sg-content">
        @yield('content')
    </main>
</div>

{{-- ===== Modal Tambah Domain ===== --}}
@php
    $sgFromCreate = old('_form') === 'domain-create';
    $sgDomainFormError = $sgFromCreate && ($errors->has('hostname') || $errors->has('port'));
@endphp
<div class="sg-modal-backdrop {{ $sgDomainFormError ? 'open' : '' }}" id="sgDomainModal" aria-hidden="true">
    <div class="sg-modal" role="dialog" aria-modal="true" aria-labelledby="sgDomainModalTitle">
        <form method="POST" action="{{ route('domains.store') }}">
            @csrf
            <input type="hidden" name="_form" value="domain-create">
            <div class="sg-modal-head">
                <div class="sg-modal-icon">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><path d="M2 12h20"/><path d="M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z"/></svg>
                </div>
                <div>
                    <div class="sg-modal-title" id="sgDomainModalTitle">Tambah Domain Monitoring</div>
                    <div class="sg-modal-sub">Daftarkan website untuk inspeksi SSL/TLS otomatis harian</div>
                </div>
                <button type="button" class="sg-modal-close" data-close-domain-modal aria-label="Tutup">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round"><path d="M18 6 6 18M6 6l12 12"/></svg>
                </button>
            </div>

            <div class="sg-modal-body">
                <div class="sg-field-group {{ $sgDomainFormError && $errors->has('hostname') ? 'sg-has-error' : '' }}">
                    <label class="sg-label" for="sgHostname">Nama Domain / FQDN <span class="req">*</span></label>
                    <div class="sg-input-group">
                        <span class="sg-input-prefix mono">https://</span>
                        <input type="text" id="sgHostname" name="hostname" value="{{ $sgFromCreate ? old('hostname') : '' }}" placeholder="layanan.tasikmalayakota.go.id" required autocomplete="off">
                    </div>
                    @if ($sgDomainFormError && $errors->has('hostname'))
                        <div class="sg-field-error">{{ $errors->first('hostname') }}</div>
                    @else
                        <div class="sg-hint">Masukkan nama domain tanpa <code>http://</code> atau path folder.</div>
                    @endif
                </div>

                <div class="sg-field-group {{ $sgDomainFormError && $errors->has('port') ? 'sg-has-error' : '' }}">
                    <label class="sg-label" for="sgPort">Port TLS <span class="req">*</span></label>
                    <input type="number" id="sgPort" name="port" class="sg-text-input" value="{{ $sgFromCreate ? old('port', 443) : 443 }}" min="1" max="65535" required>
                    @if ($sgDomainFormError && $errors->has('port'))
                        <div class="sg-field-error">{{ $errors->first('port') }}</div>
                    @else
                        <div class="sg-hint">Port standar untuk TLS/HTTPS adalah 443.</div>
                    @endif
                </div>

                <div class="sg-alert">
                    <svg style="color:#dc2626;margin-top:1px" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/><path d="M12 8v4"/><path d="M12 16h.01"/></svg>
                    <div><b>Proteksi SSRF Aktif:</b> Sistem secara otomatis memblokir hostname <code>localhost</code>, IP loopback <code>127.0.0.1</code>, dan IP privat lokal intranet.</div>
                </div>
            </div>

            <div class="sg-modal-foot">
                <button type="button" class="sg-btn sg-btn-outline" data-close-domain-modal>Batal</button>
                <button type="submit" class="sg-btn sg-btn-primary">Simpan &amp; Periksa SSL Sekarang</button>
            </div>
        </form>
    </div>
</div>

@php
    $sgStatusMap = [
        'profile-updated' => 'Profil berhasil disimpan.',
        'password-updated' => 'Password berhasil diperbarui.',
        'verification-link-sent' => 'Link verifikasi baru telah dikirim ke email Anda.',
    ];
    $sgStatus = session('status');
    $sgStatusText = $sgStatus ? ($sgStatusMap[$sgStatus] ?? $sgStatus) : null;
@endphp
@if ($sgStatusText)
    <div class="sg-toast tone-green" id="sgToast">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><path d="m9 12 2 2 4-4"/></svg>
        {{ $sgStatusText }}
    </div>
@endif
@if (session('error'))
    <div class="sg-toast tone-red" id="sgToast">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><path d="m15 9-6 6M9 9l6 6"/></svg>
        {{ session('error') }}
    </div>
@endif

<script>
    (function () {
        // Jam real-time (WIB)
        var clock = document.getElementById('sgClock');
        function tick() {
            var now = new Date();
            var tz = 'Asia/Jakarta';
            var d = now.toLocaleDateString('id-ID', { weekday: 'short', day: 'numeric', month: 'short', year: 'numeric', timeZone: tz });
            var t = now.toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit', second: '2-digit', hour12: false, timeZone: tz });
            clock.textContent = d + ', ' + t + ' WIB';
        }
        tick();
        setInterval(tick, 1000);

        // Sidebar mobile
        var sidebar = document.getElementById('sgSidebar');
        var overlay = document.getElementById('sgOverlay');
        document.getElementById('sgHamburger').addEventListener('click', function () {
            sidebar.classList.add('open');
            overlay.classList.add('open');
        });
        overlay.addEventListener('click', function () {
            sidebar.classList.remove('open');
            overlay.classList.remove('open');
        });

        // Modal Tambah Domain
        var modal = document.getElementById('sgDomainModal');
        var createUrl = @json(route('domains.create'));
        function openModal() {
            modal.classList.add('open');
            modal.setAttribute('aria-hidden', 'false');
            document.body.style.overflow = 'hidden';
            setTimeout(function () { document.getElementById('sgHostname').focus(); }, 50);
        }
        function closeModal() {
            modal.classList.remove('open');
            modal.setAttribute('aria-hidden', 'true');
            document.body.style.overflow = '';
        }
        // Semua tombol/link "Tambah Domain" di halaman ini membuka popup
        document.addEventListener('click', function (e) {
            var link = e.target.closest('a');
            if (link && (link.hasAttribute('data-open-domain-modal') || link.href === createUrl)) {
                e.preventDefault();
                openModal();
                return;
            }
            if (e.target.closest('[data-close-domain-modal]') || e.target === modal) {
                closeModal();
            }
        });
        document.addEventListener('keydown', function (e) {
            if (e.key === 'Escape' && modal.classList.contains('open')) closeModal();
        });
        if (modal.classList.contains('open')) {
            document.body.style.overflow = 'hidden';
        }
        // Buang http:// atau https:// dan path kalau user tetap menempelkannya
        document.getElementById('sgHostname').addEventListener('blur', function () {
            this.value = this.value.trim().replace(/^https?:\/\//i, '').replace(/\/.*$/, '');
        });

        // Cegah klik ganda saat menyimpan domain
        modal.querySelector('form').addEventListener('submit', function () {
            var btn = this.querySelector('button[type="submit"]');
            btn.disabled = true;
            btn.style.opacity = '.7';
            btn.textContent = 'Menyimpan...';
        });

        // Toast otomatis hilang
        var toast = document.getElementById('sgToast');
        if (toast) {
            setTimeout(function () { toast.classList.add('hide'); }, 4000);
        }
    })();
</script>
@stack('scripts')
</body>
</html>