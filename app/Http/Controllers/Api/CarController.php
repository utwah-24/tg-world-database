<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Car;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CarController extends Controller
{
    private const AVAILABLE_CATEGORIES = ['SUV', 'TRUCKS'];

    public function index(Request $request): JsonResponse
    {
        $category = $request->query('category');
        $cars = Car::orderBy('car_id')->get();

        if ($category !== null) {
            $normalizedCategory = strtoupper(trim((string) $category));

            if (! in_array($normalizedCategory, self::AVAILABLE_CATEGORIES, true)) {
                return response()->json([
                    'message' => 'Invalid category. Allowed categories are SUV and TRUCKS.',
                    'allowed_categories' => self::AVAILABLE_CATEGORIES,
                ], 422);
            }

            $cars = $cars->filter(function (Car $car) use ($normalizedCategory): bool {
                return $this->resolveCategoryFromPath($car->car_pic) === $normalizedCategory;
            })->values();
        }

        return response()->json([
            'data' => $cars->map(function (Car $car) {
                $car->setAttribute('category', $this->resolveCategoryFromPath($car->car_pic));

                return $car;
            }),
        ]);
    }

    public function categories(): JsonResponse
    {
        $categories = collect(self::AVAILABLE_CATEGORIES)->map(function (string $category) {
            return [
                'name' => $category,
                'slug' => strtolower($category),
            ];
        })->values();

        return response()->json([
            'data' => $categories,
        ]);
    }

    public function show(int $carId): JsonResponse
    {
        $car = Car::where('car_id', $carId)->first();

        if (! $car) {
            return response()->json([
                'message' => 'Car not found.',
            ], 404);
        }

        return response()->json([
            'data' => tap($car, function (Car $resolvedCar): void {
                $resolvedCar->setAttribute('category', $this->resolveCategoryFromPath($resolvedCar->car_pic));
            }),
        ]);
    }

    private function resolveCategoryFromPath(array|string|null $carPic): ?string
    {
        $imagePaths = is_array($carPic) ? $carPic : [$carPic];

        foreach ($imagePaths as $imagePath) {
            if (! is_string($imagePath) || $imagePath === '') {
                continue;
            }

            $parts = explode('/', $imagePath);

            if (count($parts) < 2 || $parts[0] !== 'TGworld') {
                continue;
            }

            $category = strtoupper((string) $parts[1]);

            if (in_array($category, self::AVAILABLE_CATEGORIES, true)) {
                return $category;
            }
        }

        return null;
    }
}
