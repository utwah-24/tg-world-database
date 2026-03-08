<?php

namespace App\Filament\Widgets;

use App\Models\Car;
use App\Models\Content;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class OverviewStatsWidget extends BaseWidget
{
    protected static ?int $sort = 1;

    protected function getStats(): array
    {
        $totalCars     = Car::count();
        $totalVideos   = Content::count();
        $linkedVideos  = Content::whereNotNull('car_id')->count();
        $coveragePct   = $totalCars > 0 ? round(($linkedVideos / $totalCars) * 100) : 0;

        return [
            Stat::make('Total Vehicles', $totalCars)
                ->description('Cars in inventory')
                ->descriptionIcon('heroicon-o-truck')
                ->color('primary'),

            Stat::make('Total Videos', $totalVideos)
                ->description($linkedVideos . ' linked to a car')
                ->descriptionIcon('heroicon-o-film')
                ->color('success'),

            Stat::make('Fleet Coverage', $coveragePct . '%')
                ->description($linkedVideos . ' of ' . $totalCars . ' cars have videos')
                ->descriptionIcon('heroicon-o-chart-bar')
                ->color($coveragePct >= 75 ? 'success' : ($coveragePct >= 40 ? 'warning' : 'danger')),
        ];
    }
}
