<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Car;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

class CarController extends Controller
{
    private const CATEGORY_DEFINITIONS = [
        ['key' => 'SUV', 'name' => 'SUV', 'slug' => 'suv'],
        ['key' => 'TRUCKS', 'name' => 'TRUCKS', 'slug' => 'trucks'],
        ['key' => 'THIRD PARTY', 'name' => 'Third party', 'slug' => 'third-party'],
    ];

    public function index(Request $request): JsonResponse
    {
        $category = $request->query('category');
        $cars = Car::with(['company', 'brand', 'vehicleModel'])->orderBy('car_id')->get();

        if ($category !== null) {
            $normalizedCategory = $this->normalizeCategory((string) $category);

            if ($normalizedCategory === null) {
                return response()->json([
                    'message' => 'Invalid category. Allowed categories are suv, trucks, and third-party.',
                    'allowed_categories' => $this->categorySlugs(),
                ], 422);
            }

            $cars = $this->filterCarsByCategory($cars, $normalizedCategory);
        }

        return response()->json([
            'data' => $cars->map(fn (Car $car) => $this->formatCar($car)),
        ]);
    }

    public function categories(): JsonResponse
    {
        $categories = collect(self::CATEGORY_DEFINITIONS)->map(function (array $category) {
            return [
                'name' => $category['name'],
                'slug' => $category['slug'],
            ];
        })->values();

        return response()->json([
            'data' => $categories,
        ]);
    }

    public function thirdParty(): JsonResponse
    {
        $cars = Car::with(['company', 'brand', 'vehicleModel'])->orderBy('car_id')->get();
        $thirdPartyCars = $this->filterCarsByCategory($cars, 'THIRD PARTY');

        return response()->json([
            'data' => $thirdPartyCars->map(fn (Car $car) => $this->formatCar($car)),
        ]);
    }

    public function show(int $carId): JsonResponse
    {
        $car = Car::with(['company', 'brand', 'vehicleModel'])->where('car_id', $carId)->first();

        if (! $car) {
            return response()->json([
                'message' => 'Car not found.',
            ], 404);
        }

        return response()->json([
            'data' => $this->formatCar($car),
        ]);
    }

    private function formatCar(Car $car): array
    {
        $categoryKey = $this->resolveCategoryFromPath($car->car_pic);

        return [
            'car_id' => $car->car_id,
            'car_name' => $car->car_name,
            'year' => $car->year,
            'car_pic' => $car->car_pic,
            'car_price' => $car->car_price,
            'car_description' => $car->car_description,
            'type' => $car->type,
            'condition' => $car->condition,
            'company_id' => $car->company_id,
            'company' => $car->company?->name ?? $car->company_label,
            'company_logo' => $car->company_logo_url,
            'company_logo_path' => $car->company_logo_path ?? $car->company?->logo,
            'brand_id' => $car->brand_id,
            'brand' => $car->brand?->name ?? $car->brand_label,
            'model_id' => $car->vehicle_model_id,
            'model' => $car->vehicleModel?->name ?? $car->model_label,
            'is_coming_soon' => $car->is_coming_soon,
            'arrival_date' => $car->arrival_date?->toDateString(),
            'is_sold' => $car->is_sold,
            'registration' => $car->registration,
            'category' => $this->categoryNameFromKey($categoryKey),
            'created_at' => $car->created_at,
            'updated_at' => $car->updated_at,
        ];
    }

    private function filterCarsByCategory(Collection $cars, string $categoryKey): Collection
    {
        return $cars->filter(function (Car $car) use ($categoryKey): bool {
            return $this->resolveCategoryFromPath($car->car_pic) === $categoryKey;
        })->values();
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

            if (in_array($category, $this->categoryKeys(), true)) {
                return $category;
            }
        }

        return null;
    }

    private function normalizeCategory(string $category): ?string
    {
        $normalized = strtolower(trim($category));

        foreach (self::CATEGORY_DEFINITIONS as $definition) {
            $key = strtolower($definition['key']);
            $name = strtolower($definition['name']);
            $slug = strtolower($definition['slug']);

            if ($normalized === $key || $normalized === $name || $normalized === $slug) {
                return $definition['key'];
            }
        }

        return null;
    }

    private function categoryNameFromKey(?string $categoryKey): ?string
    {
        if ($categoryKey === null) {
            return null;
        }

        foreach (self::CATEGORY_DEFINITIONS as $definition) {
            if ($definition['key'] === $categoryKey) {
                return $definition['name'];
            }
        }

        return null;
    }

    private function categoryKeys(): array
    {
        return array_column(self::CATEGORY_DEFINITIONS, 'key');
    }

    private function categorySlugs(): array
    {
        return array_column(self::CATEGORY_DEFINITIONS, 'slug');
    }
}
