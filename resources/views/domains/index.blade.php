<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-bold text-2xl text-gray-800 leading-tight">
                {{ __('Kelola Domain') }}
            </h2>
            <a href="{{ route('domains.create') }}"
               class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-lg font-medium text-xs text-white uppercase tracking-widest hover:bg-indigo-700 active:bg-indigo-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 shadow-sm transition">
                + {{ __('Tambah Domain') }}
            </a>
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

            <div class="bg-white shadow-sm rounded-xl border border-gray-100 overflow-hidden">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">
                            {{ __('Hostname & Port') }}
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">
                            {{ __('Status Monitoring') }}
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">
                            {{ __('Status SSL') }}
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">
                            {{ __('Masa Berlaku') }}
                        </th>
                        <th class="px-6 py-3 text-right text-xs font-semibold text-gray-500 uppercase tracking-wider">
                            {{ __('Aksi') }}
                        </th>
                    </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                    @forelse ($domains as $domain)
                        @php $ssl = $domain->latestSslCheck; @endphp
                        <tr class="hover:bg-gray-50/50 transition">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="font-medium text-gray-900">{{ $domain->hostname }}</div>
                                <div class="text-xs text-gray-400">Port {{ $domain->port }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                @if($domain->status === 'active')
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-green-100 text-green-800">
                                        Aktif
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-gray-100 text-gray-800">
                                        Nonaktif
                                    </span>
                                @endif
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
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800 cursor-help" title="{{ $ssl->error_message ?? 'Pemeriksaan gagal' }}">
                                        ✕ Error
                                    </span>
                                    @if($ssl->error_message)
                                        <div class="text-[10px] text-red-500 mt-0.5 max-w-[160px] truncate" title="{{ $ssl->error_message }}">
                                            {{ Str::limit($ssl->error_message, 25) }}
                                        </div>
                                    @endif
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                                @if ($ssl && $ssl->days_remaining !== null)
                                    <div class="font-semibold {{ $ssl->days_remaining <= 7 ? 'text-red-600' : ($ssl->days_remaining <= 30 ? 'text-amber-600' : 'text-gray-900') }}">
                                        {{ $ssl->days_remaining }} hari tersisa
                                    </div>
                                    <div class="text-xs text-gray-400">Hingga {{ $ssl->valid_to ? $ssl->valid_to->format('d/m/Y') : '-' }}</div>
                                @else
                                    <span class="text-gray-400">-</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium space-x-2">
                                <form action="{{ route('domains.check', $domain) }}" method="POST" class="inline">
                                    @csrf
                                    <button type="submit" class="text-indigo-600 hover:text-indigo-900 text-xs font-semibold px-2.5 py-1 bg-indigo-50 hover:bg-indigo-100 rounded">
                                        {{ __('Cek SSL') }}
                                    </button>
                                </form>

                                <a href="{{ route('domains.edit', $domain) }}"
                                   class="text-amber-600 hover:text-amber-900 text-xs font-semibold px-2.5 py-1 bg-amber-50 hover:bg-amber-100 rounded">
                                    {{ __('Edit') }}
                                </a>

                                <form action="{{ route('domains.destroy', $domain) }}" method="POST"
                                      class="inline"
                                      onsubmit="return confirm('Apakah Anda yakin ingin menghapus domain ini?');">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="text-rose-600 hover:text-rose-900 text-xs font-semibold px-2.5 py-1 bg-rose-50 hover:bg-rose-100 rounded">
                                        {{ __('Hapus') }}
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-6 text-center text-gray-500">
                                {{ __('Belum ada domain terdaftar. Klik "Tambah Domain" di atas untuk menambahkan.') }}
                            </td>
                        </tr>
                    @endforelse
                    </tbody>
                </table>

                @if ($domains->hasPages())
                    <div class="px-6 py-4 border-t border-gray-100">
                        {{ $domains->links() }}
                    </div>
                @endif
            </div>

        </div>
    </div>
</x-app-layout>
