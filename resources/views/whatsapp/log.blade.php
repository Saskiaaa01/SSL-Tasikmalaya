@extends('layouts.admin')

@section('title', 'Log WhatsApp')
@section('page-title', 'Log Notifikasi WhatsApp')
@section('page-subtitle', 'Riwayat pesan peringatan SSL yang dikirim melalui WhatsApp Cloud API')

@section('header-actions')
    <form method="POST" action="{{ route('whatsapp.test') }}">
        @csrf
        <button type="submit" class="sg-btn sg-btn-primary">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m22 2-7 20-4-9-9-4Z"/><path d="M22 2 11 13"/></svg>
            Tes Kirim WA
        </button>
    </form>
@endsection

@push('styles')
<style>
    .wl-stats { display: grid; grid-template-columns: repeat(4, minmax(0, 1fr)); gap: 18px; margin-bottom: 22px; }
    .wl-stat { padding: 18px 20px; }
    .wl-stat-label { font-size: 12px; font-weight: 600; color: #64748b; }
    .wl-stat-value { font-size: 26px; font-weight: 800; margin-top: 4px; }

    .wl-banner {
        display: flex; gap: 12px; align-items: flex-start; padding: 14px 18px; border-radius: 12px; margin-bottom: 22px;
        border: 1px solid #fde68a; background: #fffbeb; color: #92400e; font-size: 13px; line-height: 1.6;
    }
    .wl-banner code { font-family: 'JetBrains Mono', monospace; font-size: 12px; background: #fef3c7; padding: 1px 6px; border-radius: 5px; }

    .wl-toolbar { display: flex; gap: 10px; align-items: center; justify-content: space-between; flex-wrap: wrap; }
    .wl-filters { display: flex; gap: 10px; flex-wrap: wrap; }
    .wl-select {
        height: 38px; padding: 0 32px 0 12px; border: 1px solid #dbe4f3; border-radius: 10px; background: #fff;
        font: inherit; font-size: 13px; color: #0f172a; outline: none; cursor: pointer;
    }
    .wl-select:focus { border-color: #3b82f6; box-shadow: 0 0 0 3px rgba(59, 130, 246, .2); }
    .wl-host { font-weight: 700; }
    .wl-host:hover { color: #2563eb; }

    .wl-footer { display: flex; justify-content: space-between; align-items: center; gap: 12px; flex-wrap: wrap; padding: 14px 20px; border-top: 1px solid #eef2f7; font-size: 12px; color: #64748b; }
    .wl-pages { display: flex; gap: 6px; }
    .wl-page { height: 32px; min-width: 32px; padding: 0 12px; border-radius: 8px; border: 1px solid #e5eaf3; background: #fff; display: inline-flex; align-items: center; justify-content: center; font-size: 12px; font-weight: 600; color: #334155; }
    .wl-page.active { background: #2563eb; border-color: #2563eb; color: #fff; }
    .wl-page.disabled { opacity: .45; pointer-events: none; }

    @media (max-width: 1100px) { .wl-stats { grid-template-columns: repeat(2, minmax(0, 1fr)); } }
</style>
@endpush

@section('content')
    @if (! $waConfigured)
        <div class="wl-banner">
            <svg style="flex-shrink:0;margin-top:2px" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M10.29 3.86 1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
            <div>
                <b>Mode Simulasi.</b> Kredensial WhatsApp Cloud API belum diisi, jadi pesan hanya dicatat dan tidak benar-benar dikirim.
                Isi <code>WHATSAPP_CLOUD_API_TOKEN</code> dan <code>WHATSAPP_PHONE_NUMBER_ID</code> di file <code>.env</code>, lalu jalankan <code>php artisan config:clear</code>.
            </div>
        </div>
    @endif

    <div class="wl-stats">
        <div class="sg-card wl-stat">
            <div class="wl-stat-label">Total Notifikasi</div>
            <div class="wl-stat-value">{{ $stats['total'] }}</div>
        </div>
        <div class="sg-card wl-stat">
            <div class="wl-stat-label">Terkirim</div>
            <div class="wl-stat-value ic-green">{{ $stats['sent'] }}</div>
        </div>
        <div class="sg-card wl-stat">
            <div class="wl-stat-label">Simulasi / Pending / Gagal</div>
            <div class="wl-stat-value ic-amber">{{ $stats['not_sent'] }}</div>
        </div>
        <div class="sg-card wl-stat">
            <div class="wl-stat-label">Hari Ini</div>
            <div class="wl-stat-value ic-blue">{{ $stats['today'] }}</div>
        </div>
    </div>

    <div class="sg-card">
        <div class="sg-panel-head">
            <div class="wl-toolbar" style="width:100%;">
                <div>
                    <div class="sg-panel-title">Riwayat Pengiriman</div>
                    <div class="sg-panel-sub">Diurutkan dari yang terbaru.</div>
                </div>
                <div class="wl-filters">
                    <select id="wlType" class="wl-select">
                        <option value="">Semua Jenis Alert</option>
                        @foreach ($notifications->getCollection()->pluck('type')->filter()->unique()->sort() as $t)
                            <option value="{{ strtolower($t) }}">{{ strtoupper($t) }}</option>
                        @endforeach
                    </select>
                    <select id="wlStatus" class="wl-select">
                        <option value="">Semua Status</option>
                        <option value="sent">Terkirim</option>
                        <option value="other">Simulasi / Pending / Gagal</option>
                    </select>
                </div>
            </div>
        </div>

        <div class="sg-table-wrap">
            <table class="sg-table" id="wlTable">
                <thead>
                    <tr>
                        <th>Waktu</th>
                        <th>Domain</th>
                        <th>Jenis Alert</th>
                        <th>Tujuan</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($notifications as $notif)
                        @php
                            $wlType = strtolower((string) $notif->type);
                            if ($notif->status === 'sent') {
                                $wlTone = 'tone-green'; $wlLabel = '✓ Terkirim';
                            } elseif (in_array($notif->status, ['failed', 'error'], true)) {
                                $wlTone = 'tone-red'; $wlLabel = '✕ Gagal';
                            } else {
                                $wlTone = 'tone-gray'; $wlLabel = 'Simulasi/Pending';
                            }
                        @endphp
                        <tr data-type="{{ $wlType }}" data-status="{{ $notif->status === 'sent' ? 'sent' : 'other' }}">
                            <td style="font-family:'JetBrains Mono',monospace;font-size:12px;">
                                {{ $notif->sent_at ? $notif->sent_at->format('d/m/Y H:i') : '-' }}
                                @if ($notif->sent_at)
                                    <div style="font-family:inherit;color:#94a3b8;font-size:11px;margin-top:2px;">{{ $notif->sent_at->diffForHumans() }}</div>
                                @endif
                            </td>
                            <td>
                                @if ($notif->domain)
                                    <a href="{{ route('domains.show', $notif->domain) }}" class="wl-host">{{ $notif->domain->hostname }}</a>
                                @else
                                    <span style="color:#94a3b8;">(domain dihapus)</span>
                                @endif
                            </td>
                            <td><span class="sg-pill mono {{ (str_contains($wlType, 'expired') || str_contains($wlType, '7')) ? 'tone-red' : 'tone-amber' }}">{{ strtoupper($notif->type) }}</span></td>
                            <td style="font-family:'JetBrains Mono',monospace;font-size:12px;color:#475569;">{{ $notif->recipient ?? '-' }}</td>
                            <td><span class="sg-pill {{ $wlTone }}">{{ $wlLabel }}</span></td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5">
                                <div class="sg-empty">
                                    <svg class="ic-gray" width="44" height="44" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72c.13.96.36 1.9.7 2.81a2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45c.91.34 1.85.57 2.81.7A2 2 0 0 1 22 16.92z"/></svg>
                                    <div class="sg-empty-title">Belum ada notifikasi</div>
                                    <div style="font-size:13px;">Notifikasi akan muncul saat ada sertifikat yang memasuki ambang H-30, H-14, H-7, atau kedaluwarsa.</div>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                    <tr id="wlNoMatch" style="display:none;">
                        <td colspan="5" class="sg-empty" style="font-size:13px;">Tidak ada notifikasi yang cocok dengan filter.</td>
                    </tr>
                </tbody>
            </table>
        </div>

        <div class="wl-footer">
            <div>Menampilkan {{ $notifications->count() }} dari {{ $notifications->total() }} notifikasi</div>
            @if ($notifications->hasPages())
                <div class="wl-pages">
                    <a href="{{ $notifications->previousPageUrl() ?? '#' }}" class="wl-page {{ $notifications->onFirstPage() ? 'disabled' : '' }}">Sebelumnya</a>
                    @for ($p = 1; $p <= $notifications->lastPage(); $p++)
                        <a href="{{ $notifications->url($p) }}" class="wl-page {{ $p === $notifications->currentPage() ? 'active' : '' }}">{{ $p }}</a>
                    @endfor
                    <a href="{{ $notifications->nextPageUrl() ?? '#' }}" class="wl-page {{ $notifications->hasMorePages() ? '' : 'disabled' }}">Berikutnya</a>
                </div>
            @endif
        </div>
    </div>
@endsection

@push('scripts')
<script>
    (function () {
        var type = document.getElementById('wlType');
        var status = document.getElementById('wlStatus');
        var rows = Array.prototype.slice.call(document.querySelectorAll('#wlTable tbody tr[data-type]'));
        var noMatch = document.getElementById('wlNoMatch');
        function apply() {
            var t = type.value, s = status.value, shown = 0;
            rows.forEach(function (r) {
                var ok = (!t || r.dataset.type === t) && (!s || r.dataset.status === s);
                r.style.display = ok ? '' : 'none';
                if (ok) shown++;
            });
            noMatch.style.display = (rows.length && !shown) ? '' : 'none';
        }
        type.addEventListener('change', apply);
        status.addEventListener('change', apply);
    })();
</script>
@endpush