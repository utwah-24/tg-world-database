<?php

use App\Models\Car;
use App\Models\Content;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;
use Symfony\Component\Console\Command\Command;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('cars:sync-from-folders', function () {
    $basePath = public_path('TGworld');

    if (! File::isDirectory($basePath)) {
        $this->error("Folder not found: {$basePath}");

        return Command::FAILURE;
    }

    $topLevelFolders = File::directories($basePath);
    $imageExtensions = ['jpg', 'jpeg', 'png', 'webp', 'gif', 'bmp'];
    $imageOrder = ['back', 'front', 'interior', 'side', 'engine'];
    $allowedCategories = ['SUV', 'TRUCKS', 'THIRD PARTY'];
    $synced = 0;

    foreach ($topLevelFolders as $topLevelFolder) {
        $folderName = basename($topLevelFolder);
        $isCategoryFolder = in_array(strtoupper($folderName), $allowedCategories, true);

        $carFolders = $isCategoryFolder ? File::directories($topLevelFolder) : [$topLevelFolder];

        foreach ($carFolders as $folder) {
            $carName = basename($folder);
            $descriptionFile = collect(['Description.txt', 'description.txt'])
                ->map(fn (string $fileName) => $folder.DIRECTORY_SEPARATOR.$fileName)
                ->first(fn (string $filePath) => File::exists($filePath));

            $description = $descriptionFile ? trim((string) File::get($descriptionFile)) : null;
            $price = null;

            if ($description) {
                if (preg_match('/price\s*:\s*(.+)/i', $description, $matches) === 1) {
                    $price = trim($matches[1]);
                }
            }

            $imageFiles = collect(File::files($folder))
                ->filter(fn ($file): bool => in_array(strtolower($file->getExtension()), $imageExtensions, true))
                ->sortBy(function ($file) use ($imageOrder): array {
                    $fileName = strtolower($file->getFilename());
                    $priority = collect($imageOrder)->search(fn (string $keyword): bool => str_contains($fileName, $keyword));

                    return [$priority === false ? count($imageOrder) : $priority, $fileName];
                })
                ->values();

            $imagePaths = $imageFiles->map(function ($file) use ($isCategoryFolder, $folderName, $carName): string {
                if ($isCategoryFolder) {
                    return 'TGworld/'.str_replace('\\', '/', $folderName).'/'.str_replace('\\', '/', $carName).'/'.$file->getFilename();
                }

                return 'TGworld/'.str_replace('\\', '/', $carName).'/'.$file->getFilename();
            })->all();

            Car::updateOrCreate(
                ['car_name' => $carName],
                [
                    'car_pic' => $imagePaths ?: null,
                    'car_price' => $price,
                    'car_description' => $description,
                ],
            );

            $synced++;
            $this->line("Synced: {$carName}");
        }
    }

    $this->info("Done. Synced {$synced} cars.");

    return Command::SUCCESS;
})->purpose('Sync cars from public/TGworld folders');

Artisan::command('content:sync-from-folder', function () {
    $candidatePaths = [
        public_path('TGworld/content'),
        public_path('TGworld/Content'),
    ];

    $basePath = collect($candidatePaths)->first(fn (string $path): bool => File::isDirectory($path));

    if (! $basePath) {
        $this->error('Folder not found: public/TGworld/content');

        return Command::FAILURE;
    }

    $videoExtensions = ['mp4', 'mov', 'avi', 'mkv', 'webm', 'm4v'];
    $synced = 0;

    $videoFiles = collect(File::files($basePath))
        ->filter(fn ($file): bool => in_array(strtolower($file->getExtension()), $videoExtensions, true))
        ->sortBy(fn ($file): string => strtolower($file->getFilename()))
        ->values();

    foreach ($videoFiles as $file) {
        $filename = $file->getFilename();
        $relativeFolder = str_replace('\\', '/', basename($basePath));
        $videoPath = 'TGworld/'.$relativeFolder.'/'.$filename;
        $name = pathinfo($filename, PATHINFO_FILENAME);
        $duration = null;

        if (preg_match('/(\d{1,2}:\d{2}(?::\d{2})?)/', $filename, $matches) === 1) {
            $duration = $matches[1];
        }

        Content::updateOrCreate(
            ['content_video' => $videoPath],
            [
                'content_name' => $name,
                'duration' => $duration,
            ],
        );

        $synced++;
        $this->line("Synced content: {$filename}");
    }

    $this->info("Done. Synced {$synced} content file(s).");

    return Command::SUCCESS;
})->purpose('Sync content videos from public/TGworld/content folder');
