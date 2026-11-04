@extends('layouts.admin')

@section('title', 'Edit ' . $domain->hostname)
@section('page-title', 'Edit Domain')

@section('page-subtitle')
    <a href="{{ route('domains.index') }}" style="color:#2563eb;font-weight:600;">Kelola Website</a>
    <span style="color:#cbd5e1;margin:0 6px;">/</span>
    <a href="{{ route('domains.show', $domain) }}" style="color:#2563eb;font-weight:600;">{{ $domain->hostname }}</a>
    <span style="color:#cbd5e1;margin:0 6px;">/</span>
    <span>Edit</span>
@endsection

@section('header-actions')
    <a href="{{ route('domains.show', $domain) }}" class="sg-btn sg-btn-outline">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="m15 18-6-6 6-6"/></svg>
        Kembali ke Detail
    </a>
@endsection

@push('styles')
<style>
    .ed-wrap { max-width: 640px; margin: 0 auto; }
    .ed-input {
        width: 100%; height: 44px; padding: 0 14px; border: 1px solid #dbe4f3; border-radius: 10px; background: #fff;
        font: inherit; font-size: 14px; color: #0f172a; outline: none; transition: border-color .15s, box-shadow .15s;
    }
    .ed-input:focus { border-color: #3b82f6; box-shadow: 0 0 0 3px rgba(59, 130, 246, .2); }
    .ed-input.is-invalid { border-color: #fca5a5; }
    .ed-port { max-width: 180px; }

    .ed-status { display: grid; grid-template-columns: 1fr 1fr; gap: 12px; }
    .ed-option { position: relative; cursor: pointer; }
    .ed-option input { position: absolute; opacity: 0; pointer-events: none; }
    .ed-option-box {
        display: flex; gap: 12px; align-items: flex-start; padding: 14px 16px; border: 1px solid #dbe4f3; border-radius: 12px;
        transition: all .15s; background: #fff; height: 100%;
    }
    .ed-option:hover .ed-option-box { border-color: #bfdbfe; }
    .ed-option input:checked + .ed-option-box { border-color: #3b82f6; background: #eff6ff; box-shadow: 0 0 0 3px rgba(59, 130, 246, .15); }
    .ed-option input:focus-visible + .ed-option-box { outline: 2px solid #3b82f6; outline-offset: 2px; }
    .ed-radio { width: 18px; height: 18px; border-radius: 50%; border: 2px solid #cbd5e1; margin-top: 1px; flex-shrink: 0; position: relative; }
    .ed-option input:checked + .ed-option-box .ed-radio { border-color: #2563eb; }
    .ed-option input:checked + .ed-option-box .ed-radio::after {
        content: ''; position: absolute; inset: 3px; border-radius: 50%; background: #2563eb;
    }
    .ed-option-title { font-weight: 700; font-size: 14px; }
    .ed-option-text { font-size: 12px; color: #64748b; margin-top: 3px; line-height: 1.5; }

    .ed-info {
        display: flex; gap: 12px; padding: 14px 16px; border-radius: 12px; border: 1px dashed #93c5fd; background: #eff6ff;
        font-size: 12.5px; color: #1e40af; line-height: 1.55;
    }
    @media (max-width: 560px) { .ed-status { grid-template-columns: 1fr; } }
</style>
@endpush

@section('content')
    @php
        $edStatus = old('status', $domain->status);
    @endphp

    <div class="ed-wrap">
        <div class="sg-card">
            <form method="POST" action="{{ route('domains.update', $domain) }}" id="edForm">
                @csrf
                @method('PUT')

                <div class="sg-modal-head">
                    <div class="sg-modal-icon">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17 3a2.85 2.83 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5Z"/></svg>
                    </div>
                    <div>
                        <div class="sg-modal-title">Edit Domain Monitoring</div>
                        <div class="sg-modal-sub">Perbarui alamat, port, atau status pemantauan domain ini</div>
                    </div>
                </div>

                <div class="sg-modal-body">
                    <div class="sg-field-group {{ $errors->has('hostname') ? 'sg-has-error' : '' }}">
                        <label class="sg-label" for="edHostname">Nama Domain / FQDN <span class="req">*</span></label>
                        <div class="sg-input-group">
                            <span class="sg-input-prefix mono">https://</span>
                            <input type="text" id="edHostname" name="hostname" value="{{ old('hostname', $domain->hostname) }}" required autocomplete="off">
                        </div>
                        @if ($errors->has('hostname'))
                            <div class="sg-field-error">{{ $errors->first('hostname') }}</div>
                        @else
                            <div class="sg-hint">Masukkan nama domain tanpa <code>http://</code> atau path folder.</div>
                        @endif
                    </div>

                    <div class="sg-field-group">
                        <label class="sg-label" for="edPort">Port TLS <span class="req">*</span></label>
                        <input type="number" id="edPort" name="port" class="ed-input ed-port {{ $errors->has('port') ? 'is-invalid' : '' }}" value="{{ old('port', $domain->port) }}" min="1" max="65535" required>
                        @if ($errors->has('port'))
                            <div class="sg-field-error">{{ $errors->first('port') }}</div>
                        @else
                            <div class="sg-hint">Port standar untuk TLS/HTTPS adalah 443.</div>
                        @endif
                    </div>

                    <div class="sg-field-group">
                        <span class="sg-label">Status Monitoring <span class="req">*</span></span>
                        <div class="ed-status">
                            <label class="ed-option">
                                <input type="radio" name="status" value="active" {{ $edStatus === 'active' ? 'checked' : '' }}>
                                <span class="ed-option-box">
                                    <span class="ed-radio"></span>
                                    <span>
                                        <span class="ed-option-title" style="display:block;">Aktif</span>
                                        <span class="ed-option-text" style="display:block;">Diperiksa otomatis setiap hari pukul 07:00 WIB dan dikirimi notifikasi WhatsApp.</span>
                                    </span>
                                </span>
                            </label>
                            <label class="ed-option">
                                <input type="radio" name="status" value="inactive" {{ $edStatus === 'inactive' ? 'checked' : '' }}>
                                <span class="ed-option-box">
                                    <span class="ed-radio"></span>
                                    <span>
                                        <span class="ed-option-title" style="display:block;">Nonaktif</span>
                                        <span class="ed-option-text" style="display:block;">Pemeriksaan otomatis dan notifikasi dihentikan sementara.</span>
                                    </span>
                                </span>
                            </label>
                        </div>
                        @if ($errors->has('status'))
                            <div class="sg-field-error">{{ $errors->first('status') }}</div>
                        @endif
                    </div>

                    <div class="ed-info">
                        <svg style="margin-top:1px" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><path d="M12 16v-4"/><path d="M12 8h.01"/></svg>
                        <div>Setelah alamat domain diubah, klik <b>Cek SSL Sekarang</b> di halaman detail untuk memperbarui status sertifikatnya.</div>
                    </div>
                </div>

                <div class="sg-modal-foot">
                    <a href="{{ route('domains.show', $domain) }}" class="sg-btn sg-btn-outline">Batal</a>
                    <button type="submit" class="sg-btn sg-btn-primary" id="edSubmit">Simpan Perubahan</button>
                </div>
            </form>
        </div>
    </div>
@endsection

@push('scripts')
<script>
    (function () {
        var host = document.getElementById('edHostname');
        host.addEventListener('blur', function () {
            this.value = this.value.trim().replace(/^https?:\/\//i, '').replace(/\/.*$/, '');
        });
        document.getElementById('edForm').addEventListener('submit', function () {
            var btn = document.getElementById('edSubmit');
            btn.disabled = true;
            btn.style.opacity = '.7';
            btn.textContent = 'Menyimpan...';
        });
    })();
</script>
@endpush