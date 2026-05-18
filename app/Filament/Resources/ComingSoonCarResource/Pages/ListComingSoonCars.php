<?php

namespace App\Filament\Resources\ComingSoonCarResource\Pages;

use App\Filament\Resources\ComingSoonCarResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListComingSoonCars extends ListRecords
{
    protected static string $resource = ComingSoonCarResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
