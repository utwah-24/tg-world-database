<?php

namespace App\Filament\Widgets;

use App\Models\Car;
use Filament\Widgets\Widget;

class CarStatsWidget extends Widget
{
    protected static string $view = 'filament.widgets.car-stats-widget';

    protected static ?int $sort = 2;

    protected int | string | array $columnSpan = 'full';

    public function getTypeStats(): array
    {
        $types = ['suv', 'truck', 'sedan', 'van', 'pickup'];

        $counts = collect($types)->mapWithKeys(
            fn ($t) => [$t => Car::where('type', $t)->count()]
        );

        return [
            ['label' => 'SUV',    'key' => 'suv',    'count' => $counts['suv'],    'color' => '#22c55e'],
            ['label' => 'Truck',  'key' => 'truck',  'count' => $counts['truck'],  'color' => '#f59e0b'],
            ['label' => 'Sedan',  'key' => 'sedan',  'count' => $counts['sedan'],  'color' => '#3b82f6'],
            ['label' => 'Van',    'key' => 'van',    'count' => $counts['van'],    'color' => '#ef4444'],
            ['label' => 'Pickup', 'key' => 'pickup', 'count' => $counts['pickup'], 'color' => '#6b7280'],
        ];
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
