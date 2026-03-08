<?php

namespace App\Filament\Widgets;

use App\Models\Car;
use Filament\Widgets\Widget;

class RecentCarsWidget extends Widget
{
    protected static string $view = 'filament.widgets.recent-cars-widget';

    protected static ?int $sort = 5;

    protected int | string | array $columnSpan = 'full';

    public function getRecentCars()
    {
        return Car::latest()->take(6)->get();
    }
}
