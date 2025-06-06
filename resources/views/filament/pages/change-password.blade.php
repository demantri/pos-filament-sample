<x-filament::page>
    <div class="w-full">
        <form wire:submit.prevent="submit">
            <div class="bg-white dark:bg-gray-900 rounded-xl shadow-sm ring-1 ring-gray-950/5 dark:ring-white/10 p-6 space-y-6">
                <div>
                    <h2 class="text-xl font-bold text-gray-900 dark:text-white">Ubah Password</h2>
                    <p class="text-sm text-gray-600 dark:text-gray-300">
                        Silakan masukkan password lama dan password baru Anda.
                    </p>
                </div>

                <div class="grid gap-4">
                    {{ $this->form }}
                </div>

                <div class="flex justify-end pt-4">
                    <x-filament::button type="submit" icon="heroicon-m-check">
                        Update Password
                    </x-filament::button>
                </div>
            </div>
        </form>
    </div>
</x-filament::page>
