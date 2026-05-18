<?php

namespace App\Filament\Resources\ComingSoonCarResource\Pages;

use App\Filament\Resources\CarResource\Pages\CreateCar;
use App\Filament\Resources\ComingSoonCarResource;

class CreateComingSoonCar extends CreateCar
{
    protected static string $resource = ComingSoonCarResource::class;

    public function mount(): void
    {
        parent::mount();

        $this->form->fill([
            'is_coming_soon' => true,
        ]);
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data = parent::mutateFormDataBeforeCreate($data);

        if (! empty($data['is_coming_soon']) || ! empty($data['arrival_date'])) {
            $data['is_coming_soon'] = 'set';
        }

        return $data;
    }
}
