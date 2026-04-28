<?php

namespace App\Filament\Resources\SoldCarResource\Pages;

use App\Filament\Resources\SoldCarResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListSoldCars extends ListRecords
{
    protected static string $resource = SoldCarResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
