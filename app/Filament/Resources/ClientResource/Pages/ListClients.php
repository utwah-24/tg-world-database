<?php

namespace App\Filament\Resources\ClientResource\Pages;

use App\Filament\Resources\ClientResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListClients extends ListRecords
{
    protected static string $resource = ClientResource::class;

    protected ?string $heading = 'Users';

    protected ?string $subheading = null;

    public function getBreadcrumbs(): array
    {
        return [
            ClientResource::getUrl() => 'Users',
            'List',
        ];
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->label('New User'),
        ];
    }
}
