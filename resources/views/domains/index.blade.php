@extends('layouts.admin')

@section('title', 'Kelola Website')
@section('page-title', 'Kelola & Monitoring Website')
@section('page-subtitle', 'Daftar seluruh domain, status port TLS, dan inspeksi langsung on-demand')

@push('styles')
<style>
    .dm-toolbar { display: flex; justify-content: space-between; align-items: center; gap: 12px; flex-wrap: wrap; margin-bottom: 18px; }
    .dm-search { position: relative; width: 340px; max-width: 100%; }
    .dm-search svg { position: absolute; left: 14px; top: 50%; transform: translateY(-50%); color: #94a3b8; }
    .dm-input, .dm-select {
        height: 42px; border: 1px solid #dbe4f3; border-radius: 10px; background: #fff;
        font: inherit; font-size: 13px; color: #0f172a; outline: none; transition: border-color .15s, box-shadow .15s;
    }
    .dm-input { width: 100%; padding: 0 14px 0 40px; }
    .dm-select { padding: 0 34px 0 14px; cursor: pointer; }
    .dm-input:focus, .dm-select:focus { border-color: #3b82f6; box-shadow: 0 0 0 3px rgba(59, 130, 246, .2); }
    .dm-input::placeholder { color: #94a3b8; }
    .dm-filters { display: flex; gap: 10px; flex-wrap: wrap; }

    .dm-host { display: flex; align-items: flex-start; gap: 10px; }
    .dm-host a { font-weight: 700; white-space: nowrap; }
    .dm-host a:hover { color: #2563eb; }
    .dm-port { display: inline-block; margin-top: 4px; padding: 1px 7px; border-radius: 6px; background: #f1f5f9; color: #64748b; font-size: 11px; }
    .dm-muted { color: #64748b; font-size: 13px; }
    .dm-err { font-size: 11px; color: #dc2626; margin-top: 4px; max-width: 200px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }

    .dm-owner { font-size: 13px; font-weight: 600; color: #334155; }
    .dm-owner:hover { color: #2563eb; text-decoration: underline; }
    .dm-chip {
        display: inline-flex; align-items: center; gap: 8px; margin-bottom: 16px; padding: 8px 14px; border-radius: 999px;
        background: #eff6ff; border: 1px solid #bfdbfe; color: #1e40af; font-size: 13px;
    }
    .dm-chip a { color: #2563eb; font-weight: 700; margin-left: 6px; }
    .dm-chip a:hover { text-decoration: underline; }
    .dm-actions { display: flex; gap: 6px; justify-content: flex-end; }
    .dm-icon-btn {
        width: 34px; height: 34px; border-radius: 8px; border: 1px solid #e5eaf3; background: #fff;
        display: inline-flex; align-items: center; justify-content: center; color: #475569; transition: all .15s;
    }
    .dm-icon-btn:hover { background: #f8fafc; border-color: #bfdbfe; color: #2563eb; }
    .dm-icon-btn.danger:hover { background: #fef2f2; border-color: #fecaca; color: #dc2626; }

    .dm-footer { display: flex; justify-content: space-between; align-items: center; gap: 12px; flex-wrap: wrap; padding: 14px 20px; border-top: 1px solid #eef2f7; }
    .dm-pages { display: flex; gap: 6px; align-items: center; }
    .dm-page { height: 32px; min-width: 32px; padding: 0 12px; border-radius: 8px; border: 1px solid #e5eaf3; background: #fff; display: inline-flex; align-items: center; justify-content: center; font-size: 12px; font-weight: 600; color: #334155; }
    .dm-page:hover { border-color: #bfdbfe; }
    .dm-page.active { background: #2563eb; border-color: #2563eb; color: #fff; }
    .dm-page.disabled { opacity: .45; pointer-events: none; }
</style>
@endpush

@section('content')
    <div class="dm-toolbar">
        <div class="dm-search">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/></svg>
            <input type="text" id="dmSearch" class="dm-input" placeholder="Cari nama domain (contoh: dinas.go.id)...">
        </div>
        <div class="dm-filters">
            @if ($owners->isNotEmpty())
                <select id="dmOwner" class="dm-select" title="Filter berdasarkan pemilik">
                    <option value="">Semua Pemilik</option>
                    @foreach ($owners as $ownerId => $ownerName)
                        <option value="{{ $ownerId }}" {{ $ownerFilter === $ownerId ? 'selected' : '' }}>{{ $ownerName }}</option>
                    @endforeach
                </select>
            @endif
            <select id="dmStatus" class="dm-select">
                <option value="">Semua Status</option>
                <option value="valid">Valid Aman</option>
                <option value="expiring">Peringatan</option>
                <option value="expired">Kedaluwarsa</option>
                <option value="error">Jaringan Gagal</option>
                <option value="unchecked">Belum Dicek</option>
            </select>
            <button type="button" id="dmExport" class="sg-btn sg-btn-outline">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
                Export CSV
            </button>
        </div>
    </div>

    @if ($ownerFilter)
        <div class="dm-chip">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M19 21v-2a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
            Menampilkan domain milik <b>{{ $owners[$ownerFilter] }}</b>
            <a href="{{ route('domains.index') }}">Tampilkan semua &times;</a>
        </div>
    @endif

    <div class="sg-card">
        <div class="sg-table-wrap">
            <table class="sg-table" id="dmTable">
                <thead>
                    <tr>
                        <th>Domain &amp; Port</th>
                        @if ($owners->isNotEmpty())
                            <th>Pemilik</th>
                        @endif
                        <th>Monitoring</th>
                        <th>Sisa Masa Berlaku</th>
                        <th>Status Sertifikat</th>
                        <th>Penerbit (CA)</th>
                        <th>Terakhir Diperiksa</th>
                        <th style="text-align:right;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($domains as $domain)
                        @php
                            $ssl = $domain->latestSslCheck;
                            $days = $ssl ? $ssl->days_remaining : null;

                            // Status sertifikat
                            if (! $ssl) {
                                $statusKey = 'unchecked'; $tone = 'gray'; $statusLabel = 'Belum Dicek';
                            } elseif ($ssl->status === 'valid') {
                                $statusKey = 'valid'; $tone = 'green'; $statusLabel = '● Valid Aman';
                            } elseif ($ssl->status === 'expiring') {
                                $statusKey = 'expiring';
                                if ($days !== null && $days <= 7) {
                                    $tone = 'red'; $statusLabel = '⛔ Kritis H-7';
                                } elseif ($days !== null && $days <= 14) {
                                    $tone = 'amber'; $statusLabel = '⚠ Peringatan H-14';
                                } else {
                                    $tone = 'amber'; $statusLabel = '⚠ Peringatan H-30';
                                }
                            } elseif ($ssl->status === 'expired') {
                                $statusKey = 'expired'; $tone = 'red'; $statusLabel = '✕ KEDALUWARSA';
                            } else {
                                $statusKey = 'error'; $tone = 'gray'; $statusLabel = '? Jaringan Gagal';
                            }

                            // Sisa masa berlaku
                            if ($days === null) {
                                $daysLabel = null; $daysTone = 'gray';
                            } elseif ($days < 0) {
                                $daysLabel = 'Lewat ' . abs($days) . ' Hari'; $daysTone = 'red';
                            } else {
                                $daysLabel = $days . ' Hari';
                                $daysTone = $days <= 7 ? 'red' : ($days <= 30 ? 'amber' : 'green');
                            }
                        @endphp
                        <tr data-host="{{ strtolower($domain->hostname) }}" data-status="{{ $statusKey }}">
                            <td>
                                <div class="dm-host">
                                    <svg class="ic-{{ $tone }}" style="margin-top:2px" width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
                                    <div>
                                        <a href="{{ route('domains.show', $domain) }}" data-col="host">{{ $domain->hostname }}</a><br>
                                        <span class="dm-port mono">:{{ $domain->port }}</span>
                                    </div>
                                </div>
                            </td>
                            @if ($owners->isNotEmpty())
                                <td>
                                    <a href="{{ route('domains.index', ['owner' => $domain->user_id]) }}" class="dm-owner" data-col="owner" title="Lihat semua domain milik pengguna ini">{{ $owners[$domain->user_id] ?? '-' }}</a>
                                </td>
                            @endif
                            <td>
                                @if ($domain->status === 'active')
                                    <span class="sg-pill tone-blue" data-col="monitoring">Aktif</span>
                                @else
                                    <span class="sg-pill tone-gray" data-col="monitoring">Nonaktif</span>
                                @endif
                            </td>
                            <td>
                                @if ($daysLabel)
                                    <span class="sg-pill mono tone-{{ $daysTone }}" data-col="days">{{ $daysLabel }}</span>
                                    @if ($ssl->valid_to)
                                        <div class="dm-muted" style="font-size:11px;margin-top:4px;">s/d {{ $ssl->valid_to->format('d M Y') }}</div>
                                    @endif
                                @else
                                    <span class="dm-muted" data-col="days">-</span>
                                @endif
                            </td>
                            <td>
                                <span class="sg-pill tone-{{ $tone }}" data-col="status">{{ $statusLabel }}</span>
                                @if ($ssl && $ssl->error_message)
                                    <div class="dm-err" title="{{ $ssl->error_message }}">{{ \Illuminate\Support\Str::limit($ssl->error_message, 30) }}</div>
                                @endif
                            </td>
                            <td class="dm-muted" data-col="issuer">{{ $ssl->issuer ?? 'Tidak Diketahui' }}</td>
                            <td class="dm-muted" data-col="checked">{{ $ssl && $ssl->checked_at ? $ssl->checked_at->diffForHumans() : '-' }}</td>
                            <td>
                                <div class="dm-actions">
                                    <form action="{{ route('domains.check', $domain) }}" method="POST">
                                        @csrf
                                        <button type="submit" class="dm-icon-btn" title="Cek SSL sekarang">
                                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 12a9 9 0 1 1-3-6.7L21 8"/><path d="M21 3v5h-5"/></svg>
                                        </button>
                                    </form>
                                    <a href="{{ route('domains.show', $domain) }}" class="dm-icon-btn" title="Lihat detail">
                                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7S2 12 2 12z"/><circle cx="12" cy="12" r="3"/></svg>
                                    </a>
                                    <a href="{{ route('domains.edit', $domain) }}" class="dm-icon-btn" title="Edit domain">
                                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17 3a2.85 2.83 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5Z"/></svg>
                                    </a>
                                    <form action="{{ route('domains.destroy', $domain) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus domain ini?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="dm-icon-btn danger" title="Hapus domain">
                                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 6h18"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6"/><path d="M8 6V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/></svg>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8">
                                <div class="sg-empty">
                                    <svg class="ic-gray" width="44" height="44" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><path d="M2 12h20"/><path d="M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z"/></svg>
                                    <div class="sg-empty-title">Belum ada domain yang dimonitor</div>
                                    <div style="font-size:13px;margin-bottom:16px;">Klik tombol di bawah untuk menambahkan domain pertama.</div>
                                    <a href="{{ route('domains.create') }}" class="sg-btn sg-btn-primary">+ Tambah Domain</a>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                    <tr id="dmNoMatch" style="display:none;">
                        <td colspan="8" class="sg-empty" style="font-size:13px;">Tidak ada domain yang cocok dengan pencarian/filter.</td>
                    </tr>
                </tbody>
            </table>
        </div>

        <div class="dm-footer">
            @php
                $dmFullPager = $domains instanceof \Illuminate\Contracts\Pagination\LengthAwarePaginator;
            @endphp
            <div class="dm-muted" style="font-size:12px;">
                Menampilkan {{ $domains->count() }} dari {{ $dmFullPager ? $domains->total() : $domains->count() }} domain
            </div>
            @if ($dmFullPager && $domains->hasPages())
                <div class="dm-pages">
                    <a href="{{ $domains->previousPageUrl() ?? '#' }}" class="dm-page {{ $domains->onFirstPage() ? 'disabled' : '' }}">Sebelumnya</a>
                    @for ($p = 1; $p <= $domains->lastPage(); $p++)
                        <a href="{{ $domains->url($p) }}" class="dm-page {{ $p === $domains->currentPage() ? 'active' : '' }}">{{ $p }}</a>
                    @endfor
                    <a href="{{ $domains->nextPageUrl() ?? '#' }}" class="dm-page {{ $domains->hasMorePages() ? '' : 'disabled' }}">Berikutnya</a>
                </div>
            @endif
        </div>
    </div>
@endsection

@push('scripts')
<script>
    (function () {
        var search = document.getElementById('dmSearch');
        var status = document.getElementById('dmStatus');
        var rows = Array.prototype.slice.call(document.querySelectorAll('#dmTable tbody tr[data-host]'));
        var noMatch = document.getElementById('dmNoMatch');

        function applyFilter() {
            var q = search.value.trim().toLowerCase();
            var s = status.value;
            var shown = 0;
            rows.forEach(function (row) {
                var ok = (!q || row.dataset.host.indexOf(q) !== -1) && (!s || row.dataset.status === s);
                row.style.display = ok ? '' : 'none';
                if (ok) shown++;
            });
            noMatch.style.display = (rows.length && shown === 0) ? '' : 'none';
        }
        search.addEventListener('input', applyFilter);
        status.addEventListener('change', applyFilter);

        // Filter pemilik diproses di server (memuat ulang halaman)
        var owner = document.getElementById('dmOwner');
        if (owner) {
            owner.addEventListener('change', function () {
                var url = new URL(window.location.href);
                url.searchParams.delete('page');
                if (owner.value) {
                    url.searchParams.set('owner', owner.value);
                } else {
                    url.searchParams.delete('owner');
                }
                window.location.href = url.toString();
            });
        }

        document.getElementById('dmExport').addEventListener('click', function () {
            var hasOwner = !!document.querySelector('[data-col="owner"]');
            var header = ['Domain'].concat(hasOwner ? ['Pemilik'] : [], ['Monitoring', 'Sisa Masa Berlaku', 'Status Sertifikat', 'Penerbit (CA)', 'Terakhir Diperiksa']);
            var cols = ['host'].concat(hasOwner ? ['owner'] : [], ['monitoring', 'days', 'status', 'issuer', 'checked']);
            var lines = [header.join(',')];
            rows.forEach(function (row) {
                if (row.style.display === 'none') return;
                var vals = cols.map(function (c) {
                    var el = row.querySelector('[data-col="' + c + '"]');
                    var v = el ? el.textContent.trim().replace(/\s+/g, ' ') : '';
                    return '"' + v.replace(/"/g, '""') + '"';
                });
                lines.push(vals.join(','));
            });
            var blob = new Blob(['﻿' + lines.join('\n')], { type: 'text/csv;charset=utf-8;' });
            var a = document.createElement('a');
            a.href = URL.createObjectURL(blob);
            a.download = 'sslguard-domain-' + new Date().toISOString().slice(0, 10) + '.csv';
            a.click();
        });
    })();
</script>
@endpush