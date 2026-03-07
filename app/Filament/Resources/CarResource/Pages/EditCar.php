<?php

namespace App\Filament\Resources\CarResource\Pages;

use App\Filament\Resources\CarResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditCar extends EditRecord
{
    protected static string $resource = CarResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    /**
     * When loading the edit form, split car_pic into:
     *  - car_pic_existing  → shown in the "Current Photos" repeater
     *  - car_pic_new       → empty FileUpload for new uploads
     */
    protected function mutateFormDataBeforeFill(array $data): array
    {
        $data['car_pic_existing'] = collect($data['car_pic'] ?? [])
            ->map(fn ($path) => ['path' => $path])
            ->values()
            ->toArray();

        $data['car_pic_new'] = [];

        return $data;
    }

    /**
     * Before saving, merge remaining existing paths with any new uploads.
     */
    protected function mutateFormDataBeforeSave(array $data): array
    {
        $existing = collect($data['car_pic_existing'] ?? [])
            ->pluck('path')
            ->filter()
            ->values()
            ->toArray();

        $new = array_values($data['car_pic_new'] ?? []);

        $data['car_pic'] = array_merge($existing, $new);

        unset($data['car_pic_existing'], $data['car_pic_new']);

        return $data;
    }
}
