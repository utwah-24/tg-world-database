<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\SoldCar;
use Illuminate\Http\JsonResponse;

class SoldCarController extends Controller
{
    public function index(): JsonResponse
    {
        $soldCars = SoldCar::orderByDesc('sold_at')->get();

        return response()->json([
            'data' => $soldCars->map(fn (SoldCar $s) => $this->formatSoldCar($s)),
        ]);
    }

    public function show(int $id): JsonResponse
    {
        $soldCar = SoldCar::find($id);

        if (! $soldCar) {
            return response()->json(['message' => 'Sold car not found.'], 404);
        }

        return response()->json([
            'data' => $this->formatSoldCar($soldCar),
        ]);
    }

    private function formatSoldCar(SoldCar $s): array
    {
        return [
            'id'              => $s->id,
            'order_id'        => $s->order_id,
            'car_id'          => $s->car_id,
            'car_name'        => $s->car_name,
            'car_pics'        => $s->car_pics ?? [],
            'sold_at'         => $s->sold_at?->toIso8601String(),
            'price_sold'      => $s->price_sold,
            'total_available' => $s->total_available,
            'qty'             => $s->qty ?? 1,
            'created_at'      => $s->created_at?->toIso8601String(),
            'updated_at'      => $s->updated_at?->toIso8601String(),
        ];
    }
}
