@extends('layouts.admin')

@section('title', 'Pengguna & Role')
@section('page-title', 'Pengguna & Role')
@section('page-subtitle', 'Kelola akun pengguna SSLGuard dan hak akses Administrator')

@section('header-actions')
    <span aria-hidden="true"></span>
@endsection

@push('styles')
<style>
    .us-user { display: flex; align-items: center; gap: 12px; }
    .us-avatar {
        width: 38px; height: 38px; border-radius: 50%; display: flex; align-items: center; justify-content: center; flex-shrink: 0;
        background: #eff6ff; color: #2563eb; font-weight: 700; font-size: 13px; border: 1px solid #dbeafe;
    }
    .us-avatar.admin { background: linear-gradient(135deg, #2563eb, #3b82f6); color: #fff; border: none; }
    .us-name { font-weight: 700; }
    .us-email { font-size: 12px; color: #64748b; margin-top: 2px; }
    .us-you { font-size: 10px; font-weight: 700; color: #2563eb; background: #eff6ff; border: 1px solid #bfdbfe; padding: 1px 6px; border-radius: 999px; margin-left: 6px; vertical-align: middle; }
    .us-domain-link { transition: all .15s; }
    .us-domain-link:hover { background: #dbeafe; border-color: #93c5fd; }
    .us-role-form { display: flex; gap: 8px; align-items: center; justify-content: flex-end; }
    .us-select {
        height: 34px; padding: 0 30px 0 10px; border: 1px solid #dbe4f3; border-radius: 8px; background: #fff;
        font: inherit; font-size: 13px; color: #0f172a; outline: none; cursor: pointer;
    }
    .us-select:focus { border-color: #3b82f6; box-shadow: 0 0 0 3px rgba(59, 130, 246, .2); }
    .us-footer { padding: 14px 20px; border-top: 1px solid #eef2f7; font-size: 12px; color: #64748b; display: flex; justify-content: space-between; flex-wrap: wrap; gap: 10px; }
    .us-pages { display: flex; gap: 6px; }
    .us-page { height: 32px; min-width: 32px; padding: 0 12px; border-radius: 8px; border: 1px solid #e5eaf3; background: #fff; display: inline-flex; align-items: center; justify-content: center; font-size: 12px; font-weight: 600; color: #334155; }
    .us-page.active { background: #2563eb; border-color: #2563eb; color: #fff; }
</style>
@endpush

@section('content')
    <div class="sg-card">
        <div class="sg-panel-head">
            <div>
                <div class="sg-panel-title">Daftar Pengguna</div>
                <div class="sg-panel-sub">Administrator dapat melihat dan mengelola seluruh domain. Owner hanya melihat domain miliknya. Klik jumlah domain untuk melihat daftarnya.</div>
            </div>
        </div>
        <div class="sg-table-wrap">
            <table class="sg-table">
                <thead>
                    <tr>
                        <th>Pengguna</th>
                        <th>Nomor WhatsApp</th>
                        <th>Domain Dimiliki</th>
                        <th>Terdaftar</th>
                        <th>Role</th>
                        <th style="text-align:right;">Ubah Role</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($users as $u)
                        @php
                            $usIsAdmin = $u->isAdmin();
                            $usIsMe = $u->is(auth()->user());
                            $usInitials = collect(explode(' ', trim($u->name)))->filter()->take(2)
                                ->map(fn ($w) => mb_strtoupper(mb_substr($w, 0, 1)))->implode('');
                        @endphp
                        <tr>
                            <td>
                                <div class="us-user">
                                    <div class="us-avatar {{ $usIsAdmin ? 'admin' : '' }}">{{ $usInitials }}</div>
                                    <div>
                                        <div class="us-name">
                                            {{ $u->name }}
                                            @if ($usIsMe)
                                                <span class="us-you">ANDA</span>
                                            @endif
                                        </div>
                                        <div class="us-email">{{ $u->email }}</div>
                                    </div>
                                </div>
                            </td>
                            <td style="font-family:'JetBrains Mono',monospace;font-size:12px;">
                                @if ($u->phone_number)
                                    {{ $u->phone_number }}
                                @else
                                    <span class="sg-pill tone-amber" style="font-family:inherit;">Belum diisi</span>
                                @endif
                            </td>
                            <td>
                                @if (($domainCounts[$u->id] ?? 0) > 0)
                                    <a href="{{ route('domains.index', ['owner' => $u->id]) }}" class="sg-pill mono tone-blue us-domain-link" title="Lihat domain milik {{ $u->name }}">{{ $domainCounts[$u->id] }} domain &rarr;</a>
                                @else
                                    <span class="sg-pill mono tone-gray">0 domain</span>
                                @endif
                            </td>
                            <td style="font-size:13px;color:#64748b;">{{ $u->created_at ? $u->created_at->format('d M Y') : '-' }}</td>
                            <td>
                                <span class="sg-pill {{ $usIsAdmin ? 'tone-blue' : 'tone-gray' }}">{{ $roles[$u->role] ?? ucfirst((string) $u->role) }}</span>
                            </td>
                            <td>
                                @if ($usIsMe)
                                    <div style="text-align:right;font-size:12px;color:#94a3b8;">Tidak dapat diubah</div>
                                @else
                                    <form method="POST" action="{{ route('admin.users.role', $u) }}" class="us-role-form" onsubmit="return confirm('Ubah role {{ addslashes($u->name) }}?');">
                                        @csrf
                                        @method('PATCH')
                                        <select name="role" class="us-select">
                                            @if (! array_key_exists($u->role, $roles))
                                                <option value="" selected disabled>{{ ucfirst((string) $u->role) }} (tidak dikenal)</option>
                                            @endif
                                            @foreach ($roles as $value => $label)
                                                <option value="{{ $value }}" {{ $u->role === $value ? 'selected' : '' }}>{{ $label }}</option>
                                            @endforeach
                                        </select>
                                        <button type="submit" class="sg-btn sg-btn-sm sg-btn-soft">Simpan</button>
                                    </form>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="us-footer">
            <div>Total {{ $users->total() }} pengguna</div>
            @if ($users->hasPages())
                <div class="us-pages">
                    @for ($p = 1; $p <= $users->lastPage(); $p++)
                        <a href="{{ $users->url($p) }}" class="us-page {{ $p === $users->currentPage() ? 'active' : '' }}">{{ $p }}</a>
                    @endfor
                </div>
            @endif
        </div>
    </div>
@endsection