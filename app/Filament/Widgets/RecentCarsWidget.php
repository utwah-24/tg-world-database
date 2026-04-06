<?php

namespace App\Filament\Widgets;

use App\Models\Car;
use Filament\Widgets\Widget;

class RecentCarsWidget extends Widget
{
    private const SESSION_KEY = 'filament.recent_cars_widget_hidden';

    protected static string $view = 'filament.widgets.recent-cars-widget';

    protected static ?int $sort = 5;

    protected int|string|array $columnSpan = 'full';

    /** Hides the grid on the dashboard only; does not delete vehicles. */
    public bool $recentListHidden = false;

    public function mount(): void
    {
        $this->recentListHidden = (bool) session(self::SESSION_KEY, false);
    }

    public function getRecentCars()
    {
        return Car::latest()->take(12)->get();
    }

    public function clearRecentList(): void
    {
        session([self::SESSION_KEY => true]);
        $this->recentListHidden = true;
    }

    public function showRecentList(): void
    {
        session([self::SESSION_KEY => false]);
        $this->recentListHidden = false;
    }
}
