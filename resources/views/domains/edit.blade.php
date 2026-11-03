<x-app-layout>
    <x-slot name="header">
        <h2 class="font-bold text-2xl text-gray-800 leading-tight">
            {{ __('Edit Domain') }}
        </h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white shadow-sm rounded-xl border border-gray-100 p-6">
                <form action="{{ route('domains.update', $domain) }}" method="POST" class="space-y-6">
                    @csrf
                    @method('PUT')

                    <div>
                        <x-input-label for="hostname" :value="__('Hostname / Domain')" />
                        <x-text-input id="hostname" name="hostname" type="text" class="mt-1 block w-full" :value="old('hostname', $domain->hostname)" required autofocus />
                        <x-input-error class="mt-2" :messages="$errors->get('hostname')" />
                    </div>

                    <div>
                        <x-input-label for="port" :value="__('Port')" />
                        <x-text-input id="port" name="port" type="number" class="mt-1 block w-full" :value="old('port', $domain->port)" min="1" max="65535" required />
                        <x-input-error class="mt-2" :messages="$errors->get('port')" />
                    </div>

                    <div>
                        <x-input-label for="status" :value="__('Status Monitoring')" />
                        <select id="status" name="status" class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
                            <option value="active" {{ old('status', $domain->status) === 'active' ? 'selected' : '' }}>Aktif</option>
                            <option value="inactive" {{ old('status', $domain->status) === 'inactive' ? 'selected' : '' }}>Nonaktif</option>
                        </select>
                        <x-input-error class="mt-2" :messages="$errors->get('status')" />
                    </div>

                    <div class="flex items-center justify-end space-x-3 pt-4 border-t border-gray-100">
                        <a href="{{ route('domains.index') }}" class="px-4 py-2 border border-gray-300 rounded-lg text-sm font-medium text-gray-700 hover:bg-gray-50 transition">
                            {{ __('Batal') }}
                        </a>
                        <x-primary-button>
                            {{ __('Simpan Perubahan') }}
                        </x-primary-button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
