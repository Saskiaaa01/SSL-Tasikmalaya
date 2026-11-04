@extends('layouts.admin')

@section('title', 'Profil Saya')
@section('page-title', 'Profil Saya')
@section('page-subtitle', 'Kelola informasi akun, nomor WhatsApp notifikasi, dan keamanan password')

@push('styles')
<style>
    .pf-grid { display: grid; grid-template-columns: minmax(0, 1fr) minmax(0, 1fr); gap: 22px; align-items: start; margin-bottom: 22px; }
    .pf-hero { display: flex; align-items: center; gap: 16px; padding: 22px; border-bottom: 1px solid #eef2f7; }
    .pf-avatar {
        width: 60px; height: 60px; border-radius: 50%; display: flex; align-items: center; justify-content: center;
        background: linear-gradient(135deg, #2563eb, #3b82f6); color: #fff; font-weight: 800; font-size: 20px;
        box-shadow: 0 8px 20px rgba(37, 99, 235, .25);
    }
    .pf-name { font-size: 18px; font-weight: 800; }
    .pf-email { font-size: 13px; color: #64748b; margin-top: 2px; }
    .pf-badges { display: flex; gap: 6px; margin-top: 8px; flex-wrap: wrap; }

    .pf-form { padding: 22px; }
    .pf-field { margin-bottom: 18px; }
    .pf-input {
        width: 100%; height: 44px; padding: 0 14px; border: 1px solid #dbe4f3; border-radius: 10px; background: #fff;
        font: inherit; font-size: 14px; color: #0f172a; outline: none; transition: border-color .15s, box-shadow .15s;
    }
    .pf-input:focus { border-color: #3b82f6; box-shadow: 0 0 0 3px rgba(59, 130, 246, .2); }
    .pf-input::placeholder { color: #94a3b8; }
    .pf-input.is-invalid { border-color: #fca5a5; }
    .pf-pass { position: relative; }
    .pf-pass .pf-input { padding-right: 46px; }
    .pf-eye { position: absolute; right: 12px; top: 50%; transform: translateY(-50%); border: none; background: none; color: #94a3b8; display: flex; }
    .pf-eye:hover { color: #2563eb; }
    .pf-actions { display: flex; justify-content: flex-end; padding-top: 6px; }
    .pf-verify { margin-top: 10px; padding: 10px 12px; border-radius: 10px; background: #fffbeb; border: 1px solid #fde68a; font-size: 12.5px; color: #92400e; }
    .pf-verify button { border: none; background: none; color: #2563eb; font-weight: 700; text-decoration: underline; padding: 0; }

    .pf-danger { border-color: #fecaca; }
    .pf-danger .sg-panel-head { border-bottom-color: #fee2e2; }
    .pf-danger-body { display: flex; justify-content: space-between; align-items: center; gap: 20px; padding: 20px 22px; flex-wrap: wrap; }
    .pf-danger-text { font-size: 13px; color: #64748b; max-width: 640px; line-height: 1.6; }
    .sg-btn-danger { background: #dc2626; color: #fff; }
    .sg-btn-danger:hover { background: #b91c1c; }

    @media (max-width: 1100px) { .pf-grid { grid-template-columns: 1fr; } }
</style>
@endpush

@section('content')
    @php
        $pfInitials = collect(explode(' ', trim($user->name)))->filter()->take(2)
            ->map(fn ($w) => mb_strtoupper(mb_substr($w, 0, 1)))->implode('');
        $pfIsAdmin = method_exists($user, 'isAdmin') && $user->isAdmin();
        $pfNeedsVerify = $user instanceof \Illuminate\Contracts\Auth\MustVerifyEmail && ! $user->hasVerifiedEmail();
        $pfDeleteError = $errors->userDeletion->isNotEmpty();
    @endphp

    <div class="pf-grid">
        {{-- ===== Informasi Profil ===== --}}
        <div class="sg-card">
            <div class="pf-hero">
                <div class="pf-avatar">{{ $pfInitials }}</div>
                <div>
                    <div class="pf-name">{{ $user->name }}</div>
                    <div class="pf-email">{{ $user->email }}</div>
                    <div class="pf-badges">
                        <span class="sg-pill tone-blue">{{ $pfIsAdmin ? 'Administrator' : 'Pengguna' }}</span>
                        @if ($user->phone_number)
                            <span class="sg-pill tone-green">✓ WhatsApp terdaftar</span>
                        @else
                            <span class="sg-pill tone-amber">⚠ WhatsApp belum diisi</span>
                        @endif
                    </div>
                </div>
            </div>

            @if ($pfNeedsVerify && Route::has('verification.send'))
                <form id="send-verification" method="POST" action="{{ route('verification.send') }}">
                    @csrf
                </form>
            @endif

            <form method="POST" action="{{ route('profile.update') }}" class="pf-form">
                @csrf
                @method('PATCH')

                <div class="sg-panel-title" style="font-size:15px;margin-bottom:4px;">Informasi Profil</div>
                <div class="sg-panel-sub" style="margin-bottom:18px;">Perbarui nama, email, dan nomor WhatsApp untuk notifikasi SSL.</div>

                <div class="pf-field">
                    <label class="sg-label" for="name">Nama <span class="req">*</span></label>
                    <input id="name" name="name" type="text" class="pf-input {{ $errors->has('name') ? 'is-invalid' : '' }}" value="{{ old('name', $user->name) }}" required autocomplete="name">
                    @if ($errors->has('name'))
                        <div class="sg-field-error">{{ $errors->first('name') }}</div>
                    @endif
                </div>

                <div class="pf-field">
                    <label class="sg-label" for="email">Email <span class="req">*</span></label>
                    <input id="email" name="email" type="email" class="pf-input {{ $errors->has('email') ? 'is-invalid' : '' }}" value="{{ old('email', $user->email) }}" required autocomplete="username">
                    @if ($errors->has('email'))
                        <div class="sg-field-error">{{ $errors->first('email') }}</div>
                    @endif
                    @if ($pfNeedsVerify && Route::has('verification.send'))
                        <div class="pf-verify">
                            Email Anda belum diverifikasi.
                            <button type="submit" form="send-verification">Kirim ulang link verifikasi</button>
                        </div>
                    @endif
                </div>

                <div class="pf-field">
                    <label class="sg-label" for="phone_number">Nomor WhatsApp (untuk notifikasi SSL)</label>
                    <div class="sg-input-group {{ $errors->has('phone_number') ? 'sg-has-error' : '' }}">
                        <span class="sg-input-prefix">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#059669" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72c.13.96.36 1.9.7 2.81a2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45c.91.34 1.85.57 2.81.7A2 2 0 0 1 22 16.92z"/></svg>
                        </span>
                        <input id="phone_number" name="phone_number" type="text" inputmode="tel" value="{{ old('phone_number', $user->phone_number) }}" placeholder="Contoh: 08123456789 atau 628123456789">
                    </div>
                    @if ($errors->has('phone_number'))
                        <div class="sg-field-error">{{ $errors->first('phone_number') }}</div>
                    @else
                        <div class="sg-hint">Peringatan sertifikat SSL (H-30, H-14, H-7) akan dikirim ke nomor ini.</div>
                    @endif
                </div>

                <div class="pf-actions">
                    <button type="submit" class="sg-btn sg-btn-primary">Simpan Profil</button>
                </div>
            </form>
        </div>

        {{-- ===== Ubah Password ===== --}}
        <div class="sg-card">
            <form method="POST" action="{{ route('password.update') }}" class="pf-form">
                @csrf
                @method('PUT')

                <div class="sg-panel-title" style="font-size:15px;margin-bottom:4px;">
                    <svg class="ic-blue" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
                    Ubah Password
                </div>
                <div class="sg-panel-sub" style="margin-bottom:18px;">Gunakan password yang panjang dan acak agar akun tetap aman.</div>

                <div class="pf-field">
                    <label class="sg-label" for="current_password">Password Saat Ini</label>
                    <div class="pf-pass">
                        <input id="current_password" name="current_password" type="password" class="pf-input {{ $errors->updatePassword->has('current_password') ? 'is-invalid' : '' }}" autocomplete="current-password">
                        <button type="button" class="pf-eye" data-toggle-pass="current_password" aria-label="Tampilkan password">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7S2 12 2 12z"/><circle cx="12" cy="12" r="3"/></svg>
                        </button>
                    </div>
                    @if ($errors->updatePassword->has('current_password'))
                        <div class="sg-field-error">{{ $errors->updatePassword->first('current_password') }}</div>
                    @endif
                </div>

                <div class="pf-field">
                    <label class="sg-label" for="update_password">Password Baru</label>
                    <div class="pf-pass">
                        <input id="update_password" name="password" type="password" class="pf-input {{ $errors->updatePassword->has('password') ? 'is-invalid' : '' }}" autocomplete="new-password">
                        <button type="button" class="pf-eye" data-toggle-pass="update_password" aria-label="Tampilkan password">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7S2 12 2 12z"/><circle cx="12" cy="12" r="3"/></svg>
                        </button>
                    </div>
                    @if ($errors->updatePassword->has('password'))
                        <div class="sg-field-error">{{ $errors->updatePassword->first('password') }}</div>
                    @endif
                </div>

                <div class="pf-field">
                    <label class="sg-label" for="update_password_confirmation">Konfirmasi Password Baru</label>
                    <div class="pf-pass">
                        <input id="update_password_confirmation" name="password_confirmation" type="password" class="pf-input {{ $errors->updatePassword->has('password_confirmation') ? 'is-invalid' : '' }}" autocomplete="new-password">
                        <button type="button" class="pf-eye" data-toggle-pass="update_password_confirmation" aria-label="Tampilkan password">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7S2 12 2 12z"/><circle cx="12" cy="12" r="3"/></svg>
                        </button>
                    </div>
                    @if ($errors->updatePassword->has('password_confirmation'))
                        <div class="sg-field-error">{{ $errors->updatePassword->first('password_confirmation') }}</div>
                    @endif
                </div>

                <div class="pf-actions">
                    <button type="submit" class="sg-btn sg-btn-primary">Perbarui Password</button>
                </div>
            </form>
        </div>
    </div>

    {{-- ===== Zona Berbahaya ===== --}}
    <div class="sg-card pf-danger">
        <div class="sg-panel-head">
            <div class="sg-panel-title" style="color:#b91c1c;">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M10.29 3.86 1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
                Zona Berbahaya
            </div>
        </div>
        <div class="pf-danger-body">
            <div class="pf-danger-text">
                <b style="color:#0f172a;">Hapus Akun.</b> Setelah akun dihapus, semua data milik akun ini, termasuk domain yang dimonitor dan riwayatnya, akan terhapus permanen. Unduh dulu data yang ingin Anda simpan.
            </div>
            <button type="button" class="sg-btn sg-btn-danger" id="pfOpenDelete">Hapus Akun</button>
        </div>
    </div>

    {{-- ===== Modal konfirmasi hapus akun ===== --}}
    <div class="sg-modal-backdrop {{ $pfDeleteError ? 'open' : '' }}" id="pfDeleteModal" aria-hidden="true">
        <div class="sg-modal" role="dialog" aria-modal="true" style="max-width:480px;">
            <form method="POST" action="{{ route('profile.destroy') }}">
                @csrf
                @method('DELETE')
                <div class="sg-modal-head">
                    <div class="sg-modal-icon" style="background:#fef2f2;border-color:#fecaca;color:#dc2626;">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 6h18"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6"/><path d="M8 6V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/></svg>
                    </div>
                    <div>
                        <div class="sg-modal-title">Yakin ingin menghapus akun?</div>
                        <div class="sg-modal-sub">Tindakan ini tidak bisa dibatalkan.</div>
                    </div>
                    <button type="button" class="sg-modal-close" data-close-delete aria-label="Tutup">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round"><path d="M18 6 6 18M6 6l12 12"/></svg>
                    </button>
                </div>
                <div class="sg-modal-body">
                    <label class="sg-label" for="delete_password">Masukkan password Anda untuk konfirmasi</label>
                    <input id="delete_password" name="password" type="password" class="pf-input {{ $pfDeleteError ? 'is-invalid' : '' }}" placeholder="Password" autocomplete="current-password">
                    @if ($errors->userDeletion->has('password'))
                        <div class="sg-field-error">{{ $errors->userDeletion->first('password') }}</div>
                    @endif
                </div>
                <div class="sg-modal-foot">
                    <button type="button" class="sg-btn sg-btn-outline" data-close-delete>Batal</button>
                    <button type="submit" class="sg-btn sg-btn-danger">Ya, Hapus Akun</button>
                </div>
            </form>
        </div>
    </div>
@endsection

@push('scripts')
<script>
    (function () {
        // Tampilkan / sembunyikan password
        document.querySelectorAll('[data-toggle-pass]').forEach(function (btn) {
            btn.addEventListener('click', function () {
                var input = document.getElementById(btn.getAttribute('data-toggle-pass'));
                input.type = input.type === 'password' ? 'text' : 'password';
            });
        });

        // Modal hapus akun
        var modal = document.getElementById('pfDeleteModal');
        function openDelete() {
            modal.classList.add('open');
            document.body.style.overflow = 'hidden';
            setTimeout(function () { document.getElementById('delete_password').focus(); }, 50);
        }
        function closeDelete() {
            modal.classList.remove('open');
            document.body.style.overflow = '';
        }
        document.getElementById('pfOpenDelete').addEventListener('click', openDelete);
        modal.addEventListener('click', function (e) {
            if (e.target === modal || e.target.closest('[data-close-delete]')) closeDelete();
        });
        document.addEventListener('keydown', function (e) {
            if (e.key === 'Escape' && modal.classList.contains('open')) closeDelete();
        });
        if (modal.classList.contains('open')) document.body.style.overflow = 'hidden';
    })();
</script>
@endpush