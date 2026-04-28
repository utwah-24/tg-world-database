<?php

namespace App\Filament\Resources\SoldCarResource\Pages;

use App\Filament\Resources\SoldCarResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditSoldCar extends EditRecord
{
    protected static string $resource = SoldCarResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
