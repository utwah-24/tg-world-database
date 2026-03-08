<x-filament-widgets::widget>
    <x-filament::section>
        <x-slot name="heading">
            <div class="flex items-center gap-2">
                <x-heroicon-o-clock class="h-5 w-5 text-gray-400" />
                <span>Recently Added Vehicles</span>
            </div>
        </x-slot>

        @php $cars = $this->getRecentCars(); @endphp

        @if($cars->isEmpty())
            <div class="flex flex-col items-center justify-center py-10 text-center gap-3">
                <x-heroicon-o-truck class="h-10 w-10 text-gray-300 dark:text-gray-600" />
                <p class="text-sm text-gray-400 dark:text-gray-500">No vehicles added yet.</p>
            </div>
        @else
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                @foreach($cars as $car)
                    @php
                        $typeColors = [
                            'suv'         => 'bg-green-100 text-green-700 dark:bg-green-900/40 dark:text-green-400',
                            'truck'       => 'bg-amber-100 text-amber-700 dark:bg-amber-900/40 dark:text-amber-400',
                            'sedan'       => 'bg-blue-100 text-blue-700 dark:bg-blue-900/40 dark:text-blue-400',
                            'van'         => 'bg-red-100 text-red-700 dark:bg-red-900/40 dark:text-red-400',
                            'pickup'      => 'bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-300',
                            'third_party' => 'bg-cyan-100 text-cyan-700 dark:bg-cyan-900/40 dark:text-cyan-400',
                        ];
                        $conditionColors = [
                            'new'         => 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/40 dark:text-emerald-400',
                            'second_hand' => 'bg-yellow-100 text-yellow-700 dark:bg-yellow-900/40 dark:text-yellow-400',
                            'third_party' => 'bg-sky-100 text-sky-700 dark:bg-sky-900/40 dark:text-sky-400',
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
                       class="group flex flex-col gap-2 rounded-xl border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800/50 p-4 hover:border-amber-400 hover:shadow-md transition-all duration-200">

                        <div class="flex items-start justify-between gap-2">
                            <p class="font-semibold text-gray-900 dark:text-white text-sm leading-snug group-hover:text-amber-600 dark:group-hover:text-amber-400 transition-colors">
                                {{ $car->car_name }}
                            </p>
                            @if($car->car_price)
                                <span class="shrink-0 text-xs font-medium text-gray-500 dark:text-gray-400">
                                    {{ $car->car_price }}
                                </span>
                            @endif
                        </div>

                        <div class="flex flex-wrap gap-1.5">
                            @if($car->type)
                                <span class="inline-flex items-center rounded-full px-2 py-0.5 text-xs font-medium {{ $typeColors[$car->type] ?? 'bg-gray-100 text-gray-600' }}">
                                    {{ $typeLabel[$car->type] ?? $car->type }}
                                </span>
                            @endif
                            @if($car->condition)
                                <span class="inline-flex items-center rounded-full px-2 py-0.5 text-xs font-medium {{ $conditionColors[$car->condition] ?? 'bg-gray-100 text-gray-600' }}">
                                    {{ $conditionLabel[$car->condition] ?? $car->condition }}
                                </span>
                            @endif
                        </div>

                        <p class="text-xs text-gray-400 dark:text-gray-500 mt-auto">
                            Added {{ $car->created_at->diffForHumans() }}
                        </p>
                    </a>
                @endforeach
            </div>
        @endif
    </x-filament::section>
</x-filament-widgets::widget>
