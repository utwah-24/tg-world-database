<?php

namespace App\Filament\Resources\CarResource\Pages;

use App\Filament\Resources\CarResource;
use Filament\Resources\Pages\CreateRecord;

class CreateCar extends CreateRecord
{
    protected static string $resource = CarResource::class;

    /**
     * On create, car_pic_existing is empty — just use the uploaded files.
     */
    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['car_pic'] = array_values($data['car_pic_new'] ?? []);

        unset($data['car_pic_existing'], $data['car_pic_new']);

        // Convert toggle booleans to string values for the database
        $data['is_coming_soon'] = ! empty($data['arrival_date']) ? 'set' : null;
        $data['is_sold']        = ! empty($data['is_sold']) ? 'sold' : 'available';

        return $data;
    }
}
