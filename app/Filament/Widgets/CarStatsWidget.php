<?php

namespace App\Filament\Widgets;

use App\Models\Car;
use Filament\Widgets\Widget;

class CarStatsWidget extends Widget
{
    protected static string $view = 'filament.widgets.car-stats-widget';

    protected static ?int $sort = 2;

    protected int | string | array $columnSpan = 'full';

    protected static ?string $pollingInterval = '30s';

    public function getTypeStats(): array
    {
        $colorMap = [
            'suv'           => '#22c55e',
            'truck'         => '#f59e0b',
            'sedan'         => '#3b82f6',
            'van'           => '#ef4444',
            'pickup'        => '#6b7280',
            'bus'           => '#8b5cf6',
            'convertible'   => '#ec4899',
            'coupe'         => '#06b6d4',
            'crossover suv' => '#14b8a6',
            'hatchback'     => '#f97316',
            'minivan'       => '#a855f7',
            'station wagon' => '#0ea5e9',
        ];

        return Car::selectRaw('LOWER(type) as type_key, COUNT(*) as total')
            ->whereNotNull('type')
            ->where('type', '!=', '')
            ->groupBy('type_key')
            ->orderByDesc('total')
            ->get()
            ->map(fn ($row) => [
                'label' => ucwords($row->type_key === 'suv' ? 'SUV' : $row->type_key),
                'key'   => $row->type_key,
                'count' => $row->total,
                'color' => $colorMap[$row->type_key] ?? '#94a3b8',
            ])
            ->toArray();
    }

    public function getConditionStats(): array
    {
        $conditions = ['new', 'second_hand', 'third_party'];

        $counts = collect($conditions)->mapWithKeys(
            fn ($c) => [$c => Car::where('condition', $c)->count()]
        );

        return [
            ['label' => 'New',          'key' => 'new',         'count' => $counts['new'],         'color' => '#10b981'],
            ['label' => 'Second Hand',  'key' => 'second_hand', 'count' => $counts['second_hand'], 'color' => '#eab308'],
            ['label' => 'Third Party',  'key' => 'third_party', 'count' => $counts['third_party'], 'color' => '#0ea5e9'],
        ];
    }
}
