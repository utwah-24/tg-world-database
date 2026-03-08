<x-filament-widgets::widget>
    <x-filament::section>
        <x-slot name="heading">
            <div class="flex items-center justify-between w-full gap-4">
                <div class="flex items-center gap-2">
                    <x-heroicon-o-trophy class="h-5 w-5 text-amber-500" />
                    <span>Top Sold Cars</span>
                </div>

                <select
                    wire:model.live="selectedType"
                    class="text-sm rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 px-3 py-1.5 text-gray-900 dark:text-white shadow-sm focus:border-amber-500 focus:ring-2 focus:ring-amber-500 focus:outline-none"
                >
                    <option value="all">All Types</option>
                    <option value="suv">SUV</option>
                    <option value="truck">Truck</option>
                    <option value="sedan">Sedan</option>
                    <option value="van">Van</option>
                    <option value="pickup">Pickup</option>
                    <option value="third_party">Third Party</option>
                </select>
            </div>
        </x-slot>

        <div class="flex flex-col items-center justify-center py-14 text-center gap-4">
            <div class="rounded-full bg-amber-100 dark:bg-amber-900/30 p-5">
                <x-heroicon-o-chart-bar class="h-10 w-10 text-amber-500" />
            </div>
            <div>
                <p class="text-base font-semibold text-gray-700 dark:text-gray-300">
                    Sales data coming soon
                </p>
                <p class="mt-1 text-sm text-gray-400 dark:text-gray-500">
                    This chart will display top-selling
                    {{ $selectedType === 'all' ? 'vehicles across all types' : strtoupper(str_replace('_', ' ', $selectedType)) . ' vehicles' }}
                    once sales are recorded.
                </p>
            </div>
        </div>
    </x-filament::section>
</x-filament-widgets::widget>
