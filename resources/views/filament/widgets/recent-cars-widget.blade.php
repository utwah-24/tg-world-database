<x-filament-widgets::widget>
    {{-- Re-fetch latest cars on an interval so new vehicles appear without a full page reload. --}}
    <div wire:poll.5s.visible class="w-full">
    @php $cars = $recentListHidden ? collect() : $this->getRecentCars(); @endphp
    <x-filament::section>
        <x-slot name="heading">
            <div class="flex items-center gap-2">
                <x-heroicon-o-clock class="h-5 w-5 text-gray-400" />
                <span>Recently Added Vehicles</span>
            </div>
        </x-slot>

        <x-slot name="headerEnd">
            <div class="flex flex-wrap items-center justify-end gap-2 shrink-0">
                @if($recentListHidden)
                    <x-filament::button wire:click="showRecentList" size="sm" color="gray" outlined>
                        Show recent
                    </x-filament::button>
                @else
                    <x-filament::button
                        tag="a"
                        :href="\App\Filament\Resources\CarResource::getUrl('index')"
                        size="sm"
                        color="gray"
                    >
                        View all
                    </x-filament::button>
                    @if($cars->isNotEmpty())
                        <x-filament::button wire:click="clearRecentList" size="sm" color="gray" outlined>
                            Clear all
                        </x-filament::button>
                    @endif
                @endif
            </div>
        </x-slot>

        @if(! $recentListHidden && $cars->isEmpty())
            <div class="flex flex-col items-center justify-center py-10 text-center gap-3">
                <x-heroicon-o-truck class="h-10 w-10 text-gray-300 dark:text-gray-600" />
                <p class="text-sm text-gray-400 dark:text-gray-500">No vehicles added yet.</p>
            </div>
        @elseif(! $recentListHidden)
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                @foreach($cars as $car)
                    @php
                        // Use only utilities shipped in Filament's precompiled theme.css (see public/css/filament/filament/app.css).
                        // Arbitrary colors and opacity modifiers (e.g. text-gray-900, dark:bg-gray-800/50) are often missing, which
                        // made card text inherit the panel's light-on-dark color on near-white cards.
                        $typeColors = [
                            'suv'         => 'bg-gray-100 text-gray-700 dark:bg-gray-600 dark:text-gray-200',
                            'truck'       => 'bg-gray-100 text-gray-700 dark:bg-gray-600 dark:text-gray-200',
                            'sedan'       => 'bg-gray-100 text-gray-700 dark:bg-gray-600 dark:text-gray-200',
                            'van'         => 'bg-gray-100 text-gray-700 dark:bg-gray-600 dark:text-gray-200',
                            'pickup'      => 'bg-gray-100 text-gray-700 dark:bg-gray-600 dark:text-gray-200',
                            'third_party' => 'bg-gray-100 text-gray-700 dark:bg-gray-600 dark:text-gray-200',
                        ];
                        $conditionColors = [
                            'new'         => 'bg-primary-50 text-primary-600 dark:bg-primary-500 dark:text-white',
                            'second_hand' => 'bg-gray-100 text-gray-700 dark:bg-gray-600 dark:text-gray-200',
                            'third_party' => 'bg-gray-100 text-gray-700 dark:bg-gray-600 dark:text-gray-200',
                        ];
                        $typeLabel = [
                            'suv' => 'SUV', 'truck' => 'Truck', 'sedan' => 'Sedan',
                            'van' => 'Van', 'pickup' => 'Pickup', 'third_party' => 'Third Party',
                        ];
                        $conditionLabel = [
                            'new' => 'New', 'second_hand' => 'Second Hand', 'third_party' => 'Third Party',
                        ];
                    @endphp

                    <a href="{{ route('filament.admin.resources.cars.edit', $car->car_id) }}"
                       class="group flex flex-col gap-2 rounded-xl border border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-800 p-4 shadow-sm ring-1 ring-gray-950/5 dark:ring-white/10 hover:shadow-lg transition-all duration-200">

                        <div class="flex items-start justify-between gap-2">
                            <p class="font-semibold text-gray-950 dark:text-white text-sm leading-snug group-hover:text-gray-700 dark:group-hover:text-gray-200 transition-colors">
                                {{ $car->car_name }}
                            </p>
                            @if($car->car_price)
                                <span class="shrink-0 text-xs font-medium text-gray-600 dark:text-gray-400">
                                    {{ $car->car_price }}
                                </span>
                            @endif
                        </div>

                        <div class="flex flex-wrap gap-1.5">
                            @if($car->type)
                                <span class="inline-flex items-center rounded-full px-2 py-0.5 text-xs font-medium {{ $typeColors[$car->type] ?? 'bg-gray-100 text-gray-700 dark:bg-gray-600 dark:text-gray-200' }}">
                                    {{ $typeLabel[$car->type] ?? $car->type }}
                                </span>
                            @endif
                            @if($car->condition)
                                <span class="inline-flex items-center rounded-full px-2 py-0.5 text-xs font-medium {{ $conditionColors[$car->condition] ?? 'bg-gray-100 text-gray-700 dark:bg-gray-600 dark:text-gray-200' }}">
                                    {{ $conditionLabel[$car->condition] ?? $car->condition }}
                                </span>
                            @endif
                        </div>

                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-auto">
                            Added {{ $car->created_at->diffForHumans() }}
                        </p>
                    </a>
                @endforeach
            </div>
        @endif
    </x-filament::section>
    </div>
</x-filament-widgets::widget>
