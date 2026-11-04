@extends('layouts.admin')

@section('title', $domain->hostname)
@section('page-title', 'Detail Sertifikat & Monitoring')

@section('page-subtitle')
    <a href="{{ route('domains.index') }}" style="color:#2563eb;font-weight:600;">Kelola Website</a>
    <span style="color:#cbd5e1;margin:0 6px;">/</span>
    <span>{{ $domain->hostname }}</span>
@endsection

@section('header-actions')
    <a href="{{ route('domains.index') }}" class="sg-btn sg-btn-outline">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="m15 18-6-6 6-6"/></svg>
        Kembali
    </a>
    <a href="{{ route('domains.edit', $domain) }}" class="sg-btn sg-btn-outline">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17 3a2.85 2.83 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5Z"/></svg>
        Edit
    </a>
    <form action="{{ route('domains.check', $domain) }}" method="POST">
        @csrf
        <button type="submit" class="sg-btn sg-btn-primary">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 12a9 9 0 1 1-3-6.7L21 8"/><path d="M21 3v5h-5"/></svg>
            Cek SSL Sekarang
        </button>
    </form>
@endsection

@push('styles')
<style>
    .ds-hero { display: flex; align-items: center; gap: 18px; padding: 24px; border-bottom: 1px solid #eef2f7; flex-wrap: wrap; }
    .ds-hero-icon { width: 58px; height: 58px; border-radius: 16px; display: flex; align-items: center; justify-content: center; border: 1px solid; }
    .ds-host { font-size: 22px; font-weight: 800; word-break: break-all; }
    .ds-host-meta { display: flex; align-items: center; gap: 8px; margin-top: 8px; flex-wrap: wrap; font-size: 13px; color: #64748b; }
    .ds-hero-status { margin-left: auto; }
    .ds-hero-status .sg-pill { font-size: 13px; padding: 6px 14px; }

    .ds-tiles { display: grid; grid-template-columns: repeat(4, minmax(0, 1fr)); }
    .ds-tile { padding: 20px 24px; border-right: 1px solid #eef2f7; }
    .ds-tile:last-child { border-right: none; }
    .ds-tile-label { font-size: 11px; font-weight: 700; letter-spacing: .08em; text-transform: uppercase; color: #94a3b8; }
    .ds-tile-value { font-size: 18px; font-weight: 800; margin-top: 8px; word-break: break-word; }
    .ds-tile-note { font-size: 12px; color: #64748b; margin-top: 4px; }

    .ds-error {
        display: flex; gap: 12px; margin: 0 24px 24px; padding: 14px 16px; border-radius: 12px;
        border: 1px solid #fecaca; background: #fef2f2; color: #7f1d1d; font-size: 13px; line-height: 1.55;
    }
    .ds-error b { color: #b91c1c; display: block; margin-bottom: 2px; }
    .ds-error code { font-family: 'JetBrains Mono', monospace; font-size: 12px; word-break: break-all; }

    .ds-section { margin-top: 22px; }
    .ds-mono-sm { font-family: 'JetBrains Mono', monospace; font-size: 12px; }
    .ds-err-cell { font-size: 12px; color: #b91c1c; max-width: 420px; }

    @media (max-width: 1100px) {
        .ds-tiles { grid-template-columns: repeat(2, minmax(0, 1fr)); }
        .ds-tile:nth-child(2) { border-right: none; }
        .ds-tile:nth-child(-n+2) { border-bottom: 1px solid #eef2f7; }
    }
    @media (max-width: 600px) {
        .ds-tiles { grid-template-columns: 1fr; }
        .ds-tile { border-right: none; border-bottom: 1px solid #eef2f7; }
        .ds-hero-status { margin-left: 0; }
    }
</style>
@endpush

@section('content')
    @php
        $ssl = $domain->latestSslCheck;
        $days = $ssl ? $ssl->days_remaining : null;

        if (! $ssl) {
            $tone = 'gray'; $statusLabel = 'Belum Dicek';
        } elseif ($ssl->status === 'valid') {
            $tone = 'green'; $statusLabel = '● Valid Aman';
        } elseif ($ssl->status === 'expiring') {
            if ($days !== null && $days <= 7) {
                $tone = 'red'; $statusLabel = '⛔ Kritis H-7';
            } elseif ($days !== null && $days <= 14) {
                $tone = 'amber'; $statusLabel = '⚠ Peringatan H-14';
            } else {
                $tone = 'amber'; $statusLabel = '⚠ Peringatan H-30';
            }
        } elseif ($ssl->status === 'expired') {
            $tone = 'red'; $statusLabel = '✕ Kedaluwarsa';
        } else {
            $tone = 'red'; $statusLabel = '✕ Gagal / Error';
        }

        if ($days === null) {
            $daysText = '-'; $daysTone = '';
        } elseif ($days < 0) {
            $daysText = 'Lewat ' . abs($days) . ' hari'; $daysTone = 'ic-red';
        } else {
            $daysText = $days . ' hari';
            $daysTone = $days <= 7 ? 'ic-red' : ($days <= 30 ? 'ic-amber' : 'ic-green');
        }

        $historyStatus = [
            'valid' => ['Valid', 'tone-green'],
            'expiring' => ['Segera Habis', 'tone-amber'],
            'expired' => ['Expired', 'tone-red'],
            'error' => ['Error', 'tone-red'],
        ];
    @endphp

    {{-- ===== Ringkasan status ===== --}}
    <div class="sg-card">
        <div class="ds-hero">
            <div class="ds-hero-icon tone-{{ $tone }}">
                <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/>@if ($tone === 'green')<path d="m9 12 2 2 4-4"/>@endif</svg>
            </div>
            <div style="min-width:0;">
                <div class="ds-host">{{ $domain->hostname }}</div>
                <div class="ds-host-meta">
                    <span class="sg-pill mono tone-gray">:{{ $domain->port }}</span>
                    @if ($domain->status === 'active')
                        <span class="sg-pill tone-blue">Monitoring Aktif</span>
                    @else
                        <span class="sg-pill tone-gray">Monitoring Nonaktif</span>
                    @endif
                    <span>Ditambahkan {{ $domain->created_at ? $domain->created_at->format('d M Y') : '-' }}</span>
                </div>
            </div>
            <div class="ds-hero-status">
                <span class="sg-pill tone-{{ $tone }}">{{ $statusLabel }}</span>
            </div>
        </div>

        <div class="ds-tiles">
            <div class="ds-tile">
                <div class="ds-tile-label">Sisa Masa Berlaku</div>
                <div class="ds-tile-value {{ $daysTone }}">{{ $daysText }}</div>
            </div>
            <div class="ds-tile">
                <div class="ds-tile-label">Berlaku Hingga</div>
                <div class="ds-tile-value">{{ $ssl && $ssl->valid_to ? $ssl->valid_to->format('d M Y') : '-' }}</div>
                @if ($ssl && $ssl->valid_to)
                    <div class="ds-tile-note">Pukul {{ $ssl->valid_to->format('H:i') }}</div>
                @endif
            </div>
            <div class="ds-tile">
                <div class="ds-tile-label">Penerbit (CA)</div>
                <div class="ds-tile-value" style="font-size:15px;">{{ $ssl->issuer ?? '-' }}</div>
            </div>
            <div class="ds-tile">
                <div class="ds-tile-label">Terakhir Diperiksa</div>
                <div class="ds-tile-value" style="font-size:15px;">{{ $ssl && $ssl->checked_at ? $ssl->checked_at->format('d M Y, H:i') : '-' }}</div>
                @if ($ssl && $ssl->checked_at)
                    <div class="ds-tile-note">{{ $ssl->checked_at->diffForHumans() }}</div>
                @endif
            </div>
        </div>

        @if ($ssl && $ssl->error_message)
            <div class="ds-error">
                <svg style="color:#dc2626;margin-top:1px" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><path d="M12 8v4"/><path d="M12 16h.01"/></svg>
                <div>
                    <b>Rincian Error Pengecekan</b>
                    <code>{{ $ssl->error_message }}</code>
                </div>
            </div>
        @endif
    </div>

    {{-- ===== Riwayat pemeriksaan ===== --}}
    <div class="sg-card ds-section">
        <div class="sg-panel-head">
            <div>
                <div class="sg-panel-title">Riwayat Pemeriksaan SSL</div>
                <div class="sg-panel-sub">10 pemeriksaan terakhir untuk domain ini.</div>
            </div>
        </div>
        <div class="sg-table-wrap">
            <table class="sg-table">
                <thead>
                    <tr>
                        <th>Waktu Pengecekan</th>
                        <th>Status</th>
                        <th>Sisa Hari</th>
                        <th>Penerbit (CA)</th>
                        <th>Keterangan / Error</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($domain->sslChecks as $check)
                        @php
                            $hs = $historyStatus[$check->status] ?? [ucfirst((string) $check->status), 'tone-gray'];
                        @endphp
                        <tr>
                            <td class="ds-mono-sm">{{ $check->checked_at ? $check->checked_at->format('d/m/Y H:i') : '-' }}</td>
                            <td><span class="sg-pill {{ $hs[1] }}">{{ $hs[0] }}</span></td>
                            <td class="ds-mono-sm">{{ $check->days_remaining !== null ? $check->days_remaining . ' hari' : '-' }}</td>
                            <td style="font-size:13px;color:#475569;">{{ $check->issuer ?? '-' }}</td>
                            <td>
                                @if ($check->error_message)
                                    <div class="ds-err-cell">{{ $check->error_message }}</div>
                                @else
                                    <span style="color:#94a3b8;">-</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="sg-empty" style="font-size:13px;">Belum ada riwayat pemeriksaan. Klik "Cek SSL Sekarang" untuk memulai.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- ===== Riwayat notifikasi ===== --}}
    <div class="sg-card ds-section">
        <div class="sg-panel-head">
            <div>
                <div class="sg-panel-title">Riwayat Notifikasi Domain Ini</div>
                <div class="sg-panel-sub">Pesan peringatan WhatsApp yang pernah dikirim untuk domain ini.</div>
            </div>
        </div>
        <div class="sg-table-wrap">
            <table class="sg-table">
                <thead>
                    <tr>
                        <th>Waktu</th>
                        <th>Jenis Alert</th>
                        <th>Tujuan</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($domain->notifications as $notif)
                        <tr>
                            <td class="ds-mono-sm">{{ $notif->sent_at ? $notif->sent_at->format('d/m/Y H:i') : '-' }}</td>
                            <td><span class="sg-pill mono {{ $notif->type === 'expired' ? 'tone-red' : 'tone-amber' }}">{{ strtoupper($notif->type) }}</span></td>
                            <td style="font-size:13px;color:#475569;">{{ $notif->recipient ?? '-' }}</td>
                            <td><span class="sg-pill {{ $notif->status === 'sent' ? 'tone-green' : 'tone-gray' }}">{{ $notif->status === 'sent' ? '✓ Terkirim' : 'Simulasi/Pending' }}</span></td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="sg-empty" style="font-size:13px;">Belum ada notifikasi yang dikirimkan untuk domain ini.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection