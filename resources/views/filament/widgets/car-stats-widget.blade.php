<x-filament-widgets::widget>
    {{-- x-data wraps the ENTIRE card so both the header buttons and content panels share the same scope --}}
    <div
        x-data="{ tab: 'type' }"
        class="fi-section rounded-xl bg-white shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10"
    >
        {{-- Card header --}}
        <header class="fi-section-header flex flex-col gap-3 px-6 py-4">
            <div class="flex flex-wrap items-center justify-between gap-3 w-full">

                {{-- Title --}}
                <div class="flex items-center gap-2">
                    <svg class="h-5 w-5 text-gray-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 6a7.5 7.5 0 1 0 7.5 7.5h-7.5V6Z" />
                        <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 10.5H21A7.5 7.5 0 0 0 13.5 3v7.5Z" />
                    </svg>
                    <h3 class="text-base font-semibold leading-6 text-gray-950 dark:text-white"
                        x-text="tab === 'type' ? 'Vehicles by Type' : 'Vehicles by Condition'">
                    </h3>
                </div>

                {{-- Tab switcher --}}
                <div class="flex rounded-lg overflow-hidden border border-gray-200 dark:border-gray-700 text-sm font-medium">
                    <button
                        type="button"
                        @click="tab = 'type'"
                        :style="tab === 'type' ? 'background:#f59e0b;color:#fff;' : ''"
                        class="px-4 py-1.5 transition-colors duration-150 bg-white dark:bg-gray-800 text-gray-600 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700"
                    >
                        By Type
                    </button>
                    <button
                        type="button"
                        @click="tab = 'condition'"
                        :style="tab === 'condition' ? 'background:#f59e0b;color:#fff;' : ''"
                        class="px-4 py-1.5 transition-colors duration-150 border-l border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 text-gray-600 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700"
                    >
                        By Condition
                    </button>
                </div>

            </div>
        </header>

        {{-- Card body --}}
        <div class="border-t border-gray-200 dark:border-white/10">
            <div class="p-6">

                {{-- By Type --}}
                <div x-show="tab === 'type'" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100">
                    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-4">
                        @foreach($this->getTypeStats() as $stat)
                            <div class="flex flex-col items-center justify-center gap-2 rounded-xl p-5 ring-1 ring-black/5 dark:ring-white/10 bg-gray-50 dark:bg-gray-800">
                                <div class="h-2.5 w-10 rounded-full" style="background-color: {{ $stat['color'] }}"></div>
                                <span class="text-3xl font-bold text-gray-900 dark:text-white">{{ $stat['count'] }}</span>
                                <span class="text-xs font-medium text-gray-500 dark:text-gray-400 text-center">{{ $stat['label'] }}</span>
                            </div>
                        @endforeach
                    </div>
                </div>

                {{-- By Condition --}}
                <div x-show="tab === 'condition'" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" style="display:none">
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                        @foreach($this->getConditionStats() as $stat)
                            <div class="flex flex-col items-center justify-center gap-3 rounded-xl p-7 ring-1 ring-black/5 dark:ring-white/10 bg-gray-50 dark:bg-gray-800">
                                <div class="h-2.5 w-16 rounded-full" style="background-color: {{ $stat['color'] }}"></div>
                                <span class="text-4xl font-bold text-gray-900 dark:text-white">{{ $stat['count'] }}</span>
                                <span class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ $stat['label'] }}</span>
                            </div>
                        @endforeach
                    </div>
                </div>

            </div>
        </div>
    </div>
</x-filament-widgets::widget>
