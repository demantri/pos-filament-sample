<x-filament-panels::page>
    <div class="flex flex-col xl:flex-row gap-6">
        <div class="xl:w-2/3 w-full">
            <div class="bg-white dark:bg-gray-900 rounded-xl shadow-sm ring-1 ring-gray-950/5 dark:ring-white/10">
                <div class="p-6">
                    <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Daftar Penjualan</h2>
                    {{ $this->table }}
                </div>
            </div>
        </div>
    </div>
</x-filament-panels::page>
