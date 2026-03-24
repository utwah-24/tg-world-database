<?php

namespace Database\Seeders;

use App\Models\Car;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;

class CarSeeder extends Seeder
{
    public function run(): void
    {
        $base = public_path('TGworld');

        $categories = [
            'SUV'             => 'suv',
            'TRUCKS'          => 'truck',
            'Third party/SUV' => 'suv',   // type=suv; condition=third_party distinguishes these
        ];

        foreach ($categories as $folder => $type) {
            $dir = $base . DIRECTORY_SEPARATOR . $folder;

            if (!is_dir($dir)) {
                $this->command->warn("Folder not found: $dir — skipping.");
                continue;
            }

            $carFolders = array_filter(scandir($dir), function ($name) use ($dir) {
                return $name !== '.' && $name !== '..' && is_dir($dir . DIRECTORY_SEPARATOR . $name);
            });

            foreach ($carFolders as $carName) {
                $carPath = $dir . DIRECTORY_SEPARATOR . $carName;

                // Collect image paths (relative to public/)
                $images = [];
                foreach (File::files($carPath) as $file) {
                    $ext = strtolower($file->getExtension());
                    if (in_array($ext, ['jpg', 'jpeg', 'png', 'webp', 'gif'])) {
                        // Store relative to public/ so they resolve as URLs
                        $relativePath = 'TGworld/' . $folder . '/' . $carName . '/' . $file->getFilename();
                        $images[] = $relativePath;
                    }
                }

                // Read description file (case-insensitive search)
                $description = null;
                foreach (File::files($carPath) as $file) {
                    if (strtolower($file->getFilename()) === 'description.txt') {
                        $description = trim(File::get($file->getPathname()));
                        break;
                    }
                }

                // Extract price from description (e.g. "Price : 155Million")
                $price = null;
                if ($description) {
                    if (preg_match('/Price\s*:\s*(.+)/i', $description, $matches)) {
                        $price = trim($matches[1]);
                    }
                }

                // Map type to condition: third_party cars use 'third_party', others use 'second_hand'
                // unless description mentions "Brand New"
                $condition = 'second_hand';
                if ($type === 'third_party') {
                    $condition = 'third_party';
                } elseif ($description && stripos($description, 'brand new') !== false) {
                    $condition = 'new';
                }

                Car::updateOrCreate(
                    ['car_name' => $carName],
                    [
                        'car_pic'         => $images,
                        'car_price'       => $price,
                        'car_description' => $description,
                        'type'            => $type,
                        'condition'       => $condition,
                    ]
                );

                $this->command->info("Seeded: [{$type}] {$carName}");
            }
        }

        $this->command->info('All cars seeded successfully.');
    }
}
