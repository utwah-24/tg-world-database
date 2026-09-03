<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Car;
use App\Models\UserFavorite;
use Illuminate\Database\QueryException;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;

class FavoriteController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $favorites = UserFavorite::query()
            ->where('user_id', $request->user()->getKey())
            ->latest('created_at')
            ->latest('id')
            ->get(['car_id', 'created_at'])
            ->map(fn (UserFavorite $favorite) => [
                'carId' => $favorite->car_id,
                'createdAt' => $favorite->created_at->toISOString(),
            ]);

        return response()->json(['data' => $favorites]);
    }

    public function store(Request $request): JsonResponse
    {
        if (array_diff(array_keys($request->all()), ['carId'])) {
            return $this->validationError(['request' => ['Unexpected fields were provided.']]);
        }

        $carId = $this->parseCarId($request->input('carId'));
        if ($carId === null) {
            return $this->validationError(['carId' => ['A positive car ID is required.']]);
        }

        if (! Car::query()->whereKey($carId)->exists()) {
            return $this->error('CAR_NOT_FOUND', 'The selected car does not exist.', 404, [
                'carId' => ['The selected car does not exist.'],
            ]);
        }

        $attributes = ['user_id' => $request->user()->getKey(), 'car_id' => $carId];
        $existing = UserFavorite::query()->where($attributes)->first();
        if ($existing) {
            return response()->json(['favorite' => $this->resource($existing)]);
        }

        try {
            $favorite = UserFavorite::query()->create($attributes);

            return response()->json(['favorite' => $this->resource($favorite)], 201);
        } catch (UniqueConstraintViolationException $exception) {
            // Another request may have inserted the same favorite between our read and insert.
            $favorite = UserFavorite::query()->where($attributes)->first();
            if ($favorite) {
                return response()->json(['favorite' => $this->resource($favorite)]);
            }

            $this->logFailure('save', $exception, $request, $carId);
        } catch (QueryException $exception) {
            $this->logFailure('save', $exception, $request, $carId);
        }

        return $this->error('FAVORITE_SAVE_FAILED', 'The favorite could not be saved.', 500);
    }

    public function destroy(Request $request, mixed $carId): Response|JsonResponse
    {
        if (array_keys($request->all())) {
            return $this->validationError(['request' => ['The request body must be empty.']]);
        }

        $parsedCarId = $this->parseCarId($carId);
        if ($parsedCarId === null) {
            return $this->validationError(['carId' => ['A positive car ID is required.']]);
        }

        if (! Car::query()->whereKey($parsedCarId)->exists()) {
            return $this->error('CAR_NOT_FOUND', 'The selected car does not exist.', 404, [
                'carId' => ['The selected car does not exist.'],
            ]);
        }

        try {
            UserFavorite::query()
                ->where('user_id', $request->user()->getKey())
                ->where('car_id', $parsedCarId)
                ->delete();

            return response()->noContent();
        } catch (QueryException $exception) {
            $this->logFailure('delete', $exception, $request, $parsedCarId);

            return $this->error('FAVORITE_DELETE_FAILED', 'The favorite could not be removed.', 500);
        }
    }

    private function parseCarId(mixed $value): ?int
    {
        if (is_int($value)) {
            return $value > 0 ? $value : null;
        }

        if (! is_string($value) || ! preg_match('/^[1-9][0-9]*$/', $value)) {
            return null;
        }

        $integer = filter_var($value, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);

        return $integer === false ? null : $integer;
    }

    private function resource(UserFavorite $favorite): array
    {
        return [
            'carId' => $favorite->car_id,
            'createdAt' => $favorite->created_at->toISOString(),
        ];
    }

    private function validationError(array $fields): JsonResponse
    {
        return $this->error('VALIDATION_FAILED', 'The provided data is invalid.', 422, $fields);
    }

    private function error(string $code, string $message, int $status, array $fields = []): JsonResponse
    {
        return response()->json(['error' => [
            'code' => $code,
            'message' => $message,
            'fields' => $fields ?: (object) [],
        ]], $status);
    }

    private function logFailure(string $operation, QueryException $exception, Request $request, int $carId): void
    {
        Log::error("Favorite {$operation} failed.", [
            'user_id' => $request->user()?->getKey(),
            'car_id' => $carId,
            'exception' => $exception,
        ]);
    }
}
