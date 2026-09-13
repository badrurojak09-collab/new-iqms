<x-filament-panels::page>
    <div class="space-y-6">


        {{-- Form Container --}}
        <form wire:submit="save" class="space-y-6">
            {{ $this->form }}
            <div>
                <br />
            </div>
            <div class="flex items-center justify-end gap-3 pt-4 border-t border-gray-200 dark:border-gray-800">
                <x-filament::button type="submit" size="lg" icon="heroicon-o-check">
                    Simpan Perubahan Profil
                </x-filament::button>
            </div>
        </form>
    </div>
</x-filament-panels::page>