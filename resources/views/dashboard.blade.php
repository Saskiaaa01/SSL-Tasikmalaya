@extends('layouts.admin')

@section('title', 'Dashboard')
@section('page-title', 'Executive Dashboard')
@section('page-subtitle', 'Ringkasan status inspeksi SSL/TLS seluruh domain instansi binaan KOMINFO')

@push('styles')
<style>
    .db-stats { display: grid; grid-template-columns: repeat(4, minmax(0, 1fr)); gap: 18px; margin-bottom: 24px; }
    .db-stat { padding: 22px; display: flex; gap: 16px; align-items: flex-start; }
    .db-stat-icon { width: 46px; height: 46px; border-radius: 12px; display: flex; align-items: center; justify-content: center; border: 1px solid; }
    .db-stat-label { font-size: 13px; font-weight: 600; color: #475569; }
    .db-stat-value { font-size: 30px; font-weight: 800; line-height: 1.2; margin: 4px 0 4px; }
    .db-stat-note { font-size: 12px; color: #94a3b8; }

    .db-grid { display: grid; grid-template-columns: minmax(0, 3fr) minmax(0, 2fr); gap: 22px; margin-bottom: 24px; align-items: start; }

    .db-item {
        display: flex; align-items: center; justify-content: space-between; gap: 12px;
        border: 1px solid #e5eaf3; border-radius: 12px; padding: 14px 18px; margin-bottom: 12px; transition: border-color .15s;
    }
    .db-item:last-child { margin-bottom: 0; }
    .db-item:hover { border-color: #bfdbfe; }
    .db-item-main { display: flex; align-items: flex-start; gap: 10px; min-width: 0; }
    .db-item-host { font-weight: 700; font-size: 14px; word-break: break-all; }
    .db-item-host:hover { color: #2563eb; }
    .db-item-meta { font-size: 12px; color: #94a3b8; margin-top: 3px; }
    .db-item-right { display: flex; align-items: center; gap: 8px; flex-shrink: 0; }

    .db-rule { display: flex; gap: 14px; border: 1px solid #e5eaf3; border-radius: 12px; padding: 14px 16px; margin-bottom: 12px; }
    .db-rule-tag { height: fit-content; padding: 4px 8px; border-radius: 6px; font-size: 12px; border: 1px solid; white-space: nowrap; flex-shrink: 0; }
    .db-rule-title { font-weight: 700; font-size: 14px; }
    .db-rule-text { font-size: 12.5px; color: #64748b; margin-top: 4px; line-height: 1.55; }
    .db-note {
        display: flex; gap: 12px; padding: 14px 16px; border-radius: 12px;
        border: 1px dashed #93c5fd; background: #eff6ff; font-size: 12.5px; color: #1e40af; line-height: 1.55;
    }

    @media (max-width: 1280px) { .db-stats { grid-template-columns: repeat(2, minmax(0, 1fr)); } }
    @media (max-width: 1100px) { .db-grid { grid-template-columns: 1fr; } }
    @media (max-width: 600px) {
        .db-stats { grid-template-columns: 1fr; }
        .db-item { flex-direction: column; align-items: flex-start; }
    }
</style>
@endpush

@section('content')
    @php
        $urgentDomains = $domains->filter(function ($d) {
            return $d->latestSslCheck && in_array($d->latestSslCheck->status, ['expiring', 'expired', 'error']);
        });
        $showUrgentOnly = $urgentDomains->isNotEmpty();
        $listDomains = ($showUrgentOnly ? $urgentDomains : $domains)->take(6);
    @endphp

    {{-- Kartu statistik --}}
    <div class="db-stats">
        <div class="sg-card db-stat">
            <div class="db-stat-icon tone-blue">
                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><path d="M2 12h20"/><path d="M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z"/></svg>
            </div>
            <div>
                <div class="db-stat-label">Total Domain Terpantau</div>
                <div class="db-stat-value">{{ $totalDomains }}</div>
                <div class="db-stat-note">Semua unit kerja &amp; OPD</div>
            </div>
        </div>
        <div class="sg-card db-stat">
            <div class="db-stat-icon tone-green">
                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><path d="m9 12 2 2 4-4"/></svg>
            </div>
            <div>
                <div class="db-stat-label">Sertifikat Valid &amp; Aman</div>
                <div class="db-stat-value ic-green">{{ $activeSslCount }}</div>
                <div class="db-stat-note">Sisa masa aktif &gt; 30 hari</div>
            </div>
        </div>
        <div class="sg-card db-stat">
            <div class="db-stat-icon tone-amber">
                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M10.29 3.86 1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
            </div>
            <div>
                <div class="db-stat-label">Perlu Perhatian (H-30 / 14)</div>
                <div class="db-stat-value ic-amber">{{ $expiringCount }}</div>
                <div class="db-stat-note">Tahap peringatan awal &amp; lanjut</div>
            </div>
        </div>
        <div class="sg-card db-stat">
            <div class="db-stat-icon tone-red">
                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><path d="m15 9-6 6M9 9l6 6"/></svg>
            </div>
            <div>
                <div class="db-stat-label">Kritis &amp; Kedaluwarsa</div>
                <div class="db-stat-value ic-red">{{ $expiredCount + $errorCount }}</div>
                <div class="db-stat-note">H-7, Expired &amp; Gagal Jaringan</div>
            </div>
        </div>
    </div>

    <div class="db-grid">
        {{-- Domain butuh tindakan --}}
        <div class="sg-card">
            <div class="sg-panel-head">
                <div class="sg-panel-title">
                    <span style="width:9px;height:9px;border-radius:50%;background:{{ $showUrgentOnly ? '#ef4444' : '#10b981' }};"></span>
                    {{ $showUrgentOnly ? 'Domain Membutuhkan Tindakan Segera' : 'Domain Terpantau' }}
                </div>
                <a href="{{ route('domains.index') }}" class="sg-link">Lihat Semua &rarr;</a>
            </div>
            <div class="sg-panel-body">
                @forelse ($listDomains as $domain)
                    @php
                        $ssl = $domain->latestSslCheck;
                        $days = $ssl ? $ssl->days_remaining : null;
                        if (! $ssl) {
                            $tone = 'gray'; $label = 'Belum Dicek';
                        } elseif ($ssl->status === 'valid') {
                            $tone = 'green'; $label = ($days !== null ? $days . ' Hari' : 'Valid');
                        } elseif ($ssl->status === 'expiring') {
                            $tone = ($days !== null && $days <= 7) ? 'red' : 'amber';
                            $label = ($days !== null && $days <= 7) ? 'Kritis (' . $days . ' Hari)' : ($days . ' Hari');
                        } elseif ($ssl->status === 'expired') {
                            $tone = 'red';
                            $label = ($days !== null && $days < 0) ? 'Kedaluwarsa (' . abs($days) . 'h lalu)' : 'Kedaluwarsa';
                        } else {
                            $tone = 'red'; $label = 'Koneksi Gagal';
                        }
                    @endphp
                    <div class="db-item">
                        <div class="db-item-main">
                            <svg class="ic-{{ $tone }}" style="margin-top:2px" width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
                            <div>
                                <a href="{{ route('domains.show', $domain) }}" class="db-item-host">{{ $domain->hostname }}</a>
                                <div class="db-item-meta">
                                    Port {{ $domain->port }}
                                    @if ($ssl && $ssl->checked_at)
                                        &bull; Dicek {{ $ssl->checked_at->diffForHumans() }}
                                    @endif
                                </div>
                            </div>
                        </div>
                        <div class="db-item-right">
                            <span class="sg-pill mono tone-{{ $tone }}" @if ($ssl && $ssl->error_message) title="{{ $ssl->error_message }}" @endif>{{ $label }}</span>
                            <form action="{{ route('domains.check', $domain) }}" method="POST">
                                @csrf
                                <button type="submit" class="sg-btn sg-btn-sm sg-btn-soft">Inspeksi</button>
                            </form>
                        </div>
                    </div>
                @empty
                    <div class="sg-empty">
                        <svg class="ic-gray" width="44" height="44" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
                        <div class="sg-empty-title">Belum ada domain terdaftar</div>
                        <div style="font-size:13px;margin-bottom:16px;">Tambahkan domain untuk mulai memantau masa aktif sertifikat SSL.</div>
                        <a href="{{ route('domains.create') }}" class="sg-btn sg-btn-primary">+ Tambah Domain Pertama</a>
                    </div>
                @endforelse
            </div>
        </div>

        {{-- Mekanisme notifikasi --}}
        <div class="sg-card">
            <div class="sg-panel-head">
                <div class="sg-panel-title">
                    <svg class="ic-blue" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="18" height="18" rx="2"/><path d="m9 12 2 2 4-4"/></svg>
                    Mekanisme Ambang Notifikasi WhatsApp
                </div>
            </div>
            <div class="sg-panel-body">
                <div class="db-rule">
                    <span class="db-rule-tag mono tone-blue">H-30</span>
                    <div>
                        <div class="db-rule-title">Peringatan Awal (Early Alert)</div>
                        <div class="db-rule-text">Masa berlaku tersisa 15 - 30 hari. Pesan dikirim 1x ke Admin &amp; Owner OPD untuk mulai menyiapkan anggaran/proses perpanjangan.</div>
                    </div>
                </div>
                <div class="db-rule">
                    <span class="db-rule-tag mono tone-amber">H-14</span>
                    <div>
                        <div class="db-rule-title">Peringatan Lanjutan (Follow-up)</div>
                        <div class="db-rule-text">Masa berlaku tersisa 8 - 14 hari. Mengingatkan pengelola teknis untuk segera melakukan instalasi sertifikat baru.</div>
                    </div>
                </div>
                <div class="db-rule">
                    <span class="db-rule-tag mono tone-red">H-7</span>
                    <div>
                        <div class="db-rule-title">Peringatan Kritis (Urgent Priority)</div>
                        <div class="db-rule-text">Masa berlaku tersisa &le; 7 hari. Peringatan darurat tingkat tinggi untuk mencegah situs diblokir browser.</div>
                    </div>
                </div>
                <div class="db-note">
                    <svg style="margin-top:1px" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
                    <div><b>Fitur Anti-Spam (Deduplikasi):</b> Sistem menjamin hanya tepat 1 pesan per ambang yang dikirim untuk sidik jari sertifikat yang sama.</div>
                </div>
            </div>
        </div>
    </div>

    {{-- Riwayat notifikasi --}}
    <div class="sg-card">
        <div class="sg-panel-head">
            <div>
                <div class="sg-panel-title">Riwayat Notifikasi Peringatan Terakhir</div>
                <div class="sg-panel-sub">Catatan pengiriman pesan alert WhatsApp (H-30, H-14, H-7, EXPIRED).</div>
            </div>
        </div>
        <div class="sg-table-wrap">
            <table class="sg-table">
                <thead>
                    <tr>
                        <th>Domain</th>
                        <th>Jenis Alert</th>
                        <th>Tujuan</th>
                        <th>Status Pengiriman</th>
                        <th>Waktu</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($recentNotifications as $notif)
                        <tr>
                            <td style="font-weight:700;">{{ $notif->domain->hostname ?? '-' }}</td>
                            <td><span class="sg-pill mono {{ $notif->type === 'expired' ? 'tone-red' : 'tone-amber' }}">{{ strtoupper($notif->type) }}</span></td>
                            <td style="color:#64748b;font-size:13px;">{{ $notif->recipient ?? '-' }}</td>
                            <td>
                                <span class="sg-pill {{ $notif->status === 'sent' ? 'tone-green' : 'tone-gray' }}">{{ $notif->status === 'sent' ? '✓ Terkirim' : 'Simulasi/Pending' }}</span>
                            </td>
                            <td style="color:#64748b;font-size:13px;">{{ $notif->sent_at ? $notif->sent_at->diffForHumans() : '-' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="sg-empty" style="font-size:13px;">Belum ada riwayat notifikasi yang terkirim.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection