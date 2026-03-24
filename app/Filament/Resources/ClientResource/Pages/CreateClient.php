<?php

namespace App\Filament\Resources\ClientResource\Pages;

use App\Filament\Resources\ClientResource;
use Filament\Resources\Pages\CreateRecord;

class CreateClient extends CreateRecord
{
    protected static string $resource = ClientResource::class;

    protected ?string $heading = 'New User';

    public function getBreadcrumbs(): array
    {
        return [
            ClientResource::getUrl() => 'Users',
            'Create',
        ];
    }
}
