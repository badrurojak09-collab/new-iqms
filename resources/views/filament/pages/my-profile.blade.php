<x-filament-panels::page>
    <div class="space-y-6">
        {{-- Profile Header Card --}}
        <div class="p-6 bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 rounded-xl shadow-sm">
            <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
                <div class="flex items-center gap-4">
                    <div class="w-16 h-16 rounded-full bg-blue-600/10 text-blue-600 dark:text-blue-400 font-bold text-2xl flex items-center justify-center border-2 border-blue-600/20">
                        {{ strtoupper(substr(auth()->user()->name, 0, 2)) }}
                    </div>
                    <div>
                        <h2 class="text-xl font-bold text-gray-900 dark:text-white">
                            {{ auth()->user()->profile?->formatted_full_name ?: auth()->user()->name }}
                        </h2>
                        <div class="flex flex-wrap items-center gap-2 mt-1 text-xs text-gray-500 dark:text-gray-400">
                            <span>📧 {{ auth()->user()->email }}</span>
                            <span>•</span>
                            <span>🏛️ {{ auth()->user()->perguruanTinggi?->nama_pt ?: 'Tingkat Yayasan' }}</span>
                            @if (auth()->user()->profile?->nidn)
                                <span>•</span>
                                <span>🆔 NIDN: {{ auth()->user()->profile->nidn }}</span>
                            @endif
                        </div>
                    </div>
                </div>

                <div class="flex flex-wrap gap-2">
                    @foreach (auth()->user()->roles as $role)
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-blue-100 dark:bg-blue-900/40 text-blue-800 dark:text-blue-300">
                            🛡️ {{ $role->name }}
                        </span>
                    @endforeach
                </div>
            </div>
        </div>

        {{-- Form Container --}}
        <form wire:submit="save" class="space-y-6">
            {{ $this->form }}

            <div class="flex items-center justify-end gap-3 pt-4 border-t border-gray-200 dark:border-gray-800">
                <x-filament::button type="submit" size="lg" icon="heroicon-o-check">
                    Simpan Perubahan Profil
                </x-filament::button>
            </div>
        </form>
    </div>
</x-filament-panels::page>
