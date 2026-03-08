<?php

namespace App\Filament\Resources\ContentResource\Pages;

use App\Filament\Resources\ContentResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditContent extends EditRecord
{
    protected static string $resource = ContentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        // Only re-extract if a new video was uploaded (path changed)
        $current = $this->record->content_video ?? null;

        if (isset($data['content_video']) && $data['content_video'] !== $current) {
            $data['duration'] = CreateContent::extractDuration($data['content_video']);
        }

        return $data;
    }
}
