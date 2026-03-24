<?php

namespace Database\Seeders;

use App\Models\Car;
use App\Models\Content;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;

class ContentSeeder extends Seeder
{
    public function run(): void
    {
        $contentDir = public_path('TGworld/content');

        if (!is_dir($contentDir)) {
            $this->command->warn("Content folder not found: $contentDir");
            return;
        }

        $videos = collect(File::files($contentDir))
            ->filter(fn($f) => in_array(strtolower($f->getExtension()), ['mp4', 'mov', 'avi', 'webm']));

        foreach ($videos as $video) {
            $filename     = $video->getFilename();
            $contentName  = pathinfo($filename, PATHINFO_FILENAME); // name without extension
            $relativePath = 'TGworld/content/' . $filename;

            // Try to find a matching car by fuzzy name match
            $carId = $this->findMatchingCarId($contentName);

            Content::updateOrCreate(
                ['content_video' => $relativePath],
                [
                    'content_name'  => $contentName,
                    'content_video' => $relativePath,
                    'duration'      => null,
                    'car_id'        => $carId,
                ]
            );

            $matched = $carId ? "→ matched car_id #{$carId}" : "→ no car match";
            $this->command->info("Seeded: {$contentName} {$matched}");
        }

        $this->command->info('All content seeded successfully.');
    }

    private function findMatchingCarId(string $contentName): ?int
    {
        // Normalize: uppercase, remove punctuation for comparison
        $needle = strtoupper(preg_replace('/[^a-zA-Z0-9\s]/', '', $contentName));
        $needleWords = array_filter(explode(' ', $needle));

        $bestScore = 0;
        $bestCarId = null;

        Car::all(['car_id', 'car_name'])->each(function ($car) use ($needleWords, &$bestScore, &$bestCarId) {
            $haystack = strtoupper(preg_replace('/[^a-zA-Z0-9\s]/', '', $car->car_name));
            $haystackWords = array_filter(explode(' ', $haystack));

            // Count how many words from the content name appear in the car name
            $matches = count(array_intersect($needleWords, $haystackWords));

            if ($matches > $bestScore) {
                $bestScore = $matches;
                $bestCarId = $car->car_id;
            }
        });

        // Only link if at least 2 words matched (to avoid false positives)
        return $bestScore >= 2 ? $bestCarId : null;
    }
}
