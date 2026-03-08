<?php

namespace App\Filament\Widgets;

use Filament\Widgets\Widget;

class TopSoldWidget extends Widget
{
    protected static string $view = 'filament.widgets.top-sold-widget';

    protected static ?int $sort = 4;

    protected int | string | array $columnSpan = 'full';

    public string $selectedType = 'all';
}
