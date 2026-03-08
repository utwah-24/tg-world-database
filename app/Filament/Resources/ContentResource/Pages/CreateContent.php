<?php

namespace App\Filament\Resources\ContentResource\Pages;

use App\Filament\Resources\ContentResource;
use Filament\Resources\Pages\CreateRecord;

class CreateContent extends CreateRecord
{
    protected static string $resource = ContentResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['duration'] = self::extractDuration($data['content_video'] ?? null);

        return $data;
    }

    public static function extractDuration(?string $relativePath): ?string
    {
        if (! $relativePath) {
            return null;
        }

        $fullPath = config('filesystems.disks.public_root.root')
            . DIRECTORY_SEPARATOR
            . ltrim(str_replace('/', DIRECTORY_SEPARATOR, $relativePath), DIRECTORY_SEPARATOR);

        if (! file_exists($fullPath)) {
            return null;
        }

        require_once base_path('vendor/james-heinrich/getid3/getid3/getid3.php');

        $info = (new \getID3())->analyze($fullPath);

        if (! isset($info['playtime_seconds'])) {
            return null;
        }

        $total   = (int) round($info['playtime_seconds']);
        $hours   = intdiv($total, 3600);
        $minutes = intdiv($total % 3600, 60);
        $seconds = $total % 60;

        return $hours > 0
            ? sprintf('%d:%02d:%02d', $hours, $minutes, $seconds)
            : sprintf('%d:%02d', $minutes, $seconds);
    }
}
