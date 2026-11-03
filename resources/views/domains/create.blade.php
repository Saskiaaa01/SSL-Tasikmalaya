<x-app-layout>
    <x-slot name="header">
        <h2 class="font-bold text-2xl text-gray-800 leading-tight">
            {{ __('Tambah Domain Baru') }}
        </h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white shadow-sm rounded-xl border border-gray-100 p-6">
                <form action="{{ route('domains.store') }}" method="POST" class="space-y-6">
                    @csrf

                    <div>
                        <x-input-label for="hostname" :value="__('Hostname / Domain')" />
                        <x-text-input id="hostname" name="hostname" type="text" class="mt-1 block w-full" :value="old('hostname')" required autofocus placeholder="contoh: tasikmalayakota.go.id" />
                        <p class="text-xs text-gray-500 mt-1">Masukkan nama domain tanpa http:// atau https://</p>
                        <x-input-error class="mt-2" :messages="$errors->get('hostname')" />
                    </div>

                    <div>
                        <x-input-label for="port" :value="__('Port')" />
                        <x-text-input id="port" name="port" type="number" class="mt-1 block w-full" :value="old('port', 443)" min="1" max="65535" required />
                        <p class="text-xs text-gray-500 mt-1">Port standar untuk TLS/HTTPS adalah 443.</p>
                        <x-input-error class="mt-2" :messages="$errors->get('port')" />
                    </div>

                    <div class="flex items-center justify-end space-x-3 pt-4 border-t border-gray-100">
                        <a href="{{ route('domains.index') }}" class="px-4 py-2 border border-gray-300 rounded-lg text-sm font-medium text-gray-700 hover:bg-gray-50 transition">
                            {{ __('Batal') }}
                        </a>
                        <x-primary-button>
                            {{ __('Simpan & Mulai Cek SSL') }}
                        </x-primary-button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
