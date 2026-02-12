<?php

use App\Models\Car;
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
    $allowedCategories = ['SUV', 'TRUCKS'];
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
