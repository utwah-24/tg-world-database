<?php

use App\Models\Car;
use App\Models\Content;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schedule;
use Symfony\Component\Console\Command\Command;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Run daily at midnight to clear Coming Soon status from cars whose arrival date has passed
Schedule::command('cars:clear-coming-soon')->dailyAt('00:00');

Artisan::command('cars:sync-from-folders', function () {
    $basePath = public_path('TGworld');

    if (! File::isDirectory($basePath)) {
        $this->error("Folder not found: {$basePath}");

        return Command::FAILURE;
    }

    $imageExtensions = ['jpg', 'jpeg', 'png', 'webp', 'gif', 'bmp'];
    $imageOrder = ['back', 'front', 'interior', 'side', 'engine'];
    $typeMap = ['SUV' => 'suv', 'TRUCKS' => 'truck', 'TRUCK' => 'truck'];
    $synced = 0;

    $command = $this;

    $syncCar = function (string $folder, string $relativePath, ?string $type) use (
        $imageExtensions, $imageOrder, $command, &$synced
    ): void {
        $carName = basename($folder);

        $descriptionFile = collect(['Description.txt', 'description.txt'])
            ->map(fn (string $f) => $folder.DIRECTORY_SEPARATOR.$f)
            ->first(fn (string $p) => File::exists($p));

        $description = $descriptionFile ? trim((string) File::get($descriptionFile)) : null;
        $price = null;

        if ($description && preg_match('/price\s*:\s*(.+)/i', $description, $matches) === 1) {
            $price = trim($matches[1]);
        }

        $imagePaths = collect(File::files($folder))
            ->filter(fn ($file): bool => in_array(strtolower($file->getExtension()), $imageExtensions, true))
            ->sortBy(function ($file) use ($imageOrder): array {
                $name = strtolower($file->getFilename());
                $priority = collect($imageOrder)->search(fn (string $k): bool => str_contains($name, $k));

                return [$priority === false ? count($imageOrder) : $priority, $name];
            })
            ->values()
            ->map(fn ($file): string => $relativePath.'/'.$file->getFilename())
            ->all();

        Car::updateOrCreate(
            ['car_name' => $carName],
            [
                'car_pic' => $imagePaths ?: null,
                'car_price' => $price,
                'car_description' => $description,
                'type' => $type,
            ],
        );

        $synced++;
        $command->line("Synced: {$carName}");
    };

    foreach (File::directories($basePath) as $topFolder) {
        $topName = basename($topFolder);
        $topUpper = strtoupper($topName);

        if ($topUpper === 'THIRD PARTY') {
            foreach (File::directories($topFolder) as $sub) {
                $subUpper = strtoupper(basename($sub));

                if (isset($typeMap[$subUpper])) {
                    // Third party/SUV/{car} or Third party/TRUCK/{car}
                    foreach (File::directories($sub) as $carFolder) {
                        $rel = 'TGworld/'.$topName.'/'.basename($sub).'/'.basename($carFolder);
                        $syncCar($carFolder, $rel, $typeMap[$subUpper]);
                    }
                } else {
                    // Car sitting directly in Third party (no sub-category)
                    $rel = 'TGworld/'.$topName.'/'.basename($sub);
                    $syncCar($sub, $rel, null);
                }
            }
        } elseif (isset($typeMap[$topUpper])) {
            // SUV/{car} or TRUCKS/{car}
            foreach (File::directories($topFolder) as $carFolder) {
                $rel = 'TGworld/'.$topName.'/'.basename($carFolder);
                $syncCar($carFolder, $rel, $typeMap[$topUpper]);
            }
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
