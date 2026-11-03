<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
            <div>
                <div class="flex items-center space-x-2 text-sm text-gray-500 mb-1">
                    <a href="{{ route('domains.index') }}" class="hover:text-indigo-600">Domain</a>
                    <span>/</span>
                    <span class="text-gray-900 font-medium">{{ $domain->hostname }}</span>
                </div>
                <h2 class="font-bold text-2xl text-gray-800 leading-tight">
                    {{ __('Detail Sertifikat & Monitoring') }}
                </h2>
            </div>
            <div class="flex items-center space-x-3">
                <form action="{{ route('domains.check', $domain) }}" method="POST">
                    @csrf
                    <button type="submit" class="inline-flex items-center px-4 py-2 bg-indigo-600 text-white rounded-lg text-sm font-medium hover:bg-indigo-700 shadow-sm transition">
                        <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path></svg>
                        {{ __('Cek SSL Sekarang') }}
                    </button>
                </form>
                <a href="{{ route('domains.edit', $domain) }}" class="inline-flex items-center px-4 py-2 bg-gray-100 text-gray-700 rounded-lg text-sm font-medium hover:bg-gray-200 transition">
                    {{ __('Edit') }}
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            @if (session('status'))
                <div class="p-4 bg-emerald-50 border-l-4 border-emerald-500 text-emerald-800 rounded-md shadow-sm flex items-center">
                    <svg class="w-5 h-5 mr-3 text-emerald-500" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path></svg>
                    <span>{{ session('status') }}</span>
                </div>
            @endif

            @php $ssl = $domain->latestSslCheck; @endphp

            <!-- Status Card -->
            <div class="bg-white shadow-sm rounded-xl border border-gray-100 p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">{{ __('Status Sertifikat Terkini') }}</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                    <div>
                        <div class="text-xs text-gray-500 font-medium uppercase tracking-wider">{{ __('Status SSL') }}</div>
                        <div class="mt-1">
                            @if (!$ssl)
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-gray-100 text-gray-700">Belum Pernah Dicek</span>
                            @elseif ($ssl->status === 'valid')
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-emerald-100 text-emerald-800">✓ Valid / Aman</span>
                            @elseif ($ssl->status === 'expiring')
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-amber-100 text-amber-800">⚠️ Segera Habis</span>
                            @elseif ($ssl->status === 'expired')
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-rose-100 text-rose-800">✕ Expired / Kadaluarsa</span>
                            @else
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-red-100 text-red-800">✕ Gagal / Error</span>
                            @endif
                        </div>
                    </div>

                    <div>
                        <div class="text-xs text-gray-500 font-medium uppercase tracking-wider">{{ __('Sisa Masa Berlaku') }}</div>
                        <div class="mt-1 text-lg font-bold {{ $ssl && $ssl->days_remaining <= 7 ? 'text-red-600' : ($ssl && $ssl->days_remaining <= 30 ? 'text-amber-600' : 'text-gray-900') }}">
                            {{ $ssl && $ssl->days_remaining !== null ? $ssl->days_remaining . ' Hari' : '-' }}
                        </div>
                    </div>

                    <div>
                        <div class="text-xs text-gray-500 font-medium uppercase tracking-wider">{{ __('Masa Berlaku (Valid To)') }}</div>
                        <div class="mt-1 text-sm font-semibold text-gray-800">
                            {{ $ssl && $ssl->valid_to ? $ssl->valid_to->format('d F Y, H:i') : '-' }}
                        </div>
                    </div>

                    <div>
                        <div class="text-xs text-gray-500 font-medium uppercase tracking-wider">{{ __('Penerbit (Issuer)') }}</div>
                        <div class="mt-1 text-sm text-gray-800 truncate" title="{{ $ssl->issuer ?? '-' }}">
                            {{ $ssl->issuer ?? '-' }}
                        </div>
                    </div>
                </div>

                @if ($ssl && $ssl->error_message)
                    <div class="mt-6 p-4 bg-rose-50 border border-rose-200 rounded-lg text-rose-800 text-sm">
                        <div class="font-semibold mb-1">Rincian Error Pengecekan:</div>
                        <div>{{ $ssl->error_message }}</div>
                    </div>
                @endif

                @if ($ssl && $ssl->fingerprint_sha256)
                    <div class="mt-6 pt-6 border-t border-gray-100">
                        <div class="text-xs text-gray-500 font-medium uppercase tracking-wider">{{ __('SHA-256 Fingerprint') }}</div>
                        <div class="mt-1 font-mono text-xs bg-gray-50 p-2.5 rounded border border-gray-200 text-gray-800 break-all select-all">
                            {{ $ssl->fingerprint_sha256 }}
                        </div>
                    </div>
                @endif
            </div>

            <!-- SSL Check History -->
            <div class="bg-white shadow-sm rounded-xl border border-gray-100 overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-100 bg-gray-50/50">
                    <h3 class="text-base font-semibold text-gray-900">{{ __('Riwayat Pemeriksaan SSL') }}</h3>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 text-sm">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase">{{ __('Waktu Pengecekan') }}</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase">{{ __('Status') }}</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase">{{ __('Sisa Hari') }}</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase">{{ __('Issuer') }}</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase">{{ __('Keterangan / Error') }}</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            @forelse ($domain->sslChecks as $check)
                                <tr>
                                    <td class="px-6 py-3 text-gray-600">{{ $check->checked_at ? $check->checked_at->format('d/m/Y H:i') : '-' }}</td>
                                    <td class="px-6 py-3">
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium {{ $check->status === 'valid' ? 'bg-green-100 text-green-800' : ($check->status === 'expiring' ? 'bg-amber-100 text-amber-800' : 'bg-red-100 text-red-800') }}">
                                            {{ ucfirst($check->status) }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-3 font-medium">{{ $check->days_remaining !== null ? $check->days_remaining . ' hari' : '-' }}</td>
                                    <td class="px-6 py-3 text-gray-600">{{ $check->issuer ?? '-' }}</td>
                                    <td class="px-6 py-3 text-gray-500 text-xs">{{ $check->error_message ?? '-' }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="px-6 py-4 text-center text-gray-400 text-xs">Belum ada riwayat pemeriksaan SSL.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Notification History for this Domain -->
            <div class="bg-white shadow-sm rounded-xl border border-gray-100 overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-100 bg-gray-50/50">
                    <h3 class="text-base font-semibold text-gray-900">{{ __('Riwayat Notifikasi Domain Ini') }}</h3>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 text-sm">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase">{{ __('Waktu') }}</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase">{{ __('Jenis Alert') }}</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase">{{ __('Tujuan') }}</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase">{{ __('Status') }}</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            @forelse ($domain->notifications as $notif)
                                <tr>
                                    <td class="px-6 py-3 text-gray-600">{{ $notif->sent_at ? $notif->sent_at->format('d/m/Y H:i') : '-' }}</td>
                                    <td class="px-6 py-3 uppercase font-semibold text-xs text-amber-700">{{ $notif->type }}</td>
                                    <td class="px-6 py-3 text-gray-600">{{ $notif->recipient ?? '-' }}</td>
                                    <td class="px-6 py-3 text-xs">{{ $notif->status === 'sent' ? '✓ Terkirim' : 'Simulasi/Pending' }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="px-6 py-4 text-center text-gray-400 text-xs">Belum ada notifikasi yang dikirimkan untuk domain ini.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

        </div>
    </div>
</x-app-layout>

