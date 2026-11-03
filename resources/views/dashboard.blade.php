<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
            <h2 class="font-bold text-2xl text-gray-800 leading-tight">
                {{ __('Dashboard Monitoring SSLGuard') }}
            </h2>
            <form action="{{ route('ssl.check-all') }}" method="POST">
                @csrf
                <button type="submit" class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-lg font-medium text-xs text-white uppercase tracking-widest hover:bg-indigo-700 active:bg-indigo-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150 shadow-sm">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path></svg>
                    {{ __('Pemeriksaan Semua Domain') }}
                </button>
            </form>
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

            <!-- Metric Cards -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">
                <!-- Total Domains -->
                <div class="bg-white overflow-hidden shadow-sm rounded-xl border border-gray-100 p-5">
                    <div class="flex items-center">
                        <div class="flex-shrink-0 bg-blue-500 rounded-lg p-3 text-white">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9"></path></svg>
                        </div>
                        <div class="ml-4">
                            <div class="text-sm font-medium text-gray-500">{{ __('Total Domain') }}</div>
                            <div class="text-2xl font-bold text-gray-900">{{ $totalDomains }}</div>
                        </div>
                    </div>
                </div>

                <!-- SSL Active / Valid -->
                <div class="bg-white overflow-hidden shadow-sm rounded-xl border border-gray-100 p-5">
                    <div class="flex items-center">
                        <div class="flex-shrink-0 bg-emerald-500 rounded-lg p-3 text-white">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path></svg>
                        </div>
                        <div class="ml-4">
                            <div class="text-sm font-medium text-gray-500">{{ __('SSL Aktif / Valid') }}</div>
                            <div class="text-2xl font-bold text-emerald-600">{{ $activeSslCount }}</div>
                        </div>
                    </div>
                </div>

                <!-- Expiring Soon -->
                <div class="bg-white overflow-hidden shadow-sm rounded-xl border border-gray-100 p-5">
                    <div class="flex items-center">
                        <div class="flex-shrink-0 bg-amber-500 rounded-lg p-3 text-white">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        </div>
                        <div class="ml-4">
                            <div class="text-sm font-medium text-gray-500">{{ __('Segera Kadaluarsa (≤30h)') }}</div>
                            <div class="text-2xl font-bold text-amber-600">{{ $expiringCount }}</div>
                        </div>
                    </div>
                </div>

                <!-- Expired / Error -->
                <div class="bg-white overflow-hidden shadow-sm rounded-xl border border-gray-100 p-5">
                    <div class="flex items-center">
                        <div class="flex-shrink-0 bg-rose-500 rounded-lg p-3 text-white">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                        </div>
                        <div class="ml-4">
                            <div class="text-sm font-medium text-gray-500">{{ __('Kadaluarsa / Error') }}</div>
                            <div class="text-2xl font-bold text-rose-600">{{ $expiredCount + $errorCount }}</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Monitored Domains Section -->
            <div class="bg-white shadow-sm rounded-xl border border-gray-100 overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-100 flex justify-between items-center bg-gray-50/50">
                    <div>
                        <h3 class="text-lg font-semibold text-gray-900">{{ __('Daftar Domain Yang Dimonitor') }}</h3>
                        <p class="text-sm text-gray-500">{{ __('Status sertifikat SSL terkini untuk domain terdaftar.') }}</p>
                    </div>
                    <a href="{{ route('domains.create') }}" class="inline-flex items-center px-4 py-2 bg-indigo-600 text-white rounded-lg text-sm font-medium hover:bg-indigo-700 shadow-sm transition">
                        + {{ __('Tambah Domain') }}
                    </a>
                </div>

                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">{{ __('Domain') }}</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">{{ __('Status SSL') }}</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">{{ __('Issuer') }}</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">{{ __('Sisa Waktu') }}</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">{{ __('Pemeriksaan Terakhir') }}</th>
                                <th class="px-6 py-3 text-right text-xs font-semibold text-gray-500 uppercase tracking-wider">{{ __('Aksi') }}</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @forelse ($domains as $domain)
                                @php $ssl = $domain->latestSslCheck; @endphp
                                <tr class="hover:bg-gray-50/50 transition">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="font-medium text-gray-900">{{ $domain->hostname }}</div>
                                        <div class="text-xs text-gray-500">Port: {{ $domain->port }}</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        @if (!$ssl)
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                                Belum Dicek
                                            </span>
                                        @elseif ($ssl->status === 'valid')
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-emerald-100 text-emerald-800">
                                                ✓ Valid
                                            </span>
                                        @elseif ($ssl->status === 'expiring')
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-amber-100 text-amber-800">
                                                ⚠️ Segera Habis
                                            </span>
                                        @elseif ($ssl->status === 'expired')
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-rose-100 text-rose-800">
                                                ✕ Expired
                                            </span>
                                        @else
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800" title="{{ $ssl->error_message }}">
                                                ✕ Error
                                            </span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                                        {{ $ssl->issuer ?? '-' }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm">
                                        @if ($ssl && $ssl->days_remaining !== null)
                                            <span class="font-medium {{ $ssl->days_remaining <= 7 ? 'text-rose-600 font-bold' : ($ssl->days_remaining <= 30 ? 'text-amber-600' : 'text-gray-900') }}">
                                                {{ $ssl->days_remaining }} Hari
                                            </span>
                                            <div class="text-xs text-gray-400">s/d {{ $ssl->valid_to ? $ssl->valid_to->format('d M Y') : '-' }}</div>
                                        @else
                                            <span class="text-gray-400">-</span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-xs text-gray-500">
                                        {{ $ssl && $ssl->checked_at ? $ssl->checked_at->diffForHumans() : '-' }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium space-x-2">
                                        <form action="{{ route('domains.check', $domain) }}" method="POST" class="inline">
                                            @csrf
                                            <button type="submit" class="text-indigo-600 hover:text-indigo-900 text-xs font-semibold px-2 py-1 bg-indigo-50 hover:bg-indigo-100 rounded">
                                                {{ __('Cek Sekarang') }}
                                            </button>
                                        </form>
                                        <a href="{{ route('domains.edit', $domain) }}" class="text-gray-600 hover:text-gray-900 text-xs font-semibold px-2 py-1 bg-gray-50 hover:bg-gray-100 rounded">
                                            {{ __('Edit') }}
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="px-6 py-8 text-center text-gray-500">
                                        <div class="mb-3">
                                            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path></svg>
                                        </div>
                                        <div class="text-base font-medium text-gray-900">{{ __('Belum ada domain terdaftar') }}</div>
                                        <div class="text-sm text-gray-500 mt-1">{{ __('Mulai tambahkan domain Anda untuk memantau masa aktif sertifikat SSL.') }}</div>
                                        <div class="mt-4">
                                            <a href="{{ route('domains.create') }}" class="inline-flex items-center px-4 py-2 bg-indigo-600 text-white rounded-lg text-sm font-medium hover:bg-indigo-700">
                                                + {{ __('Tambah Domain Pertama') }}
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

        </div>
    </div>
</x-app-layout>
