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
            'data' => $soldCars->map(fn (SoldCar $s) => [
                'id'         => $s->id,
                'car_id'     => $s->car_id,
                'car_name'   => $s->car_name,
                'car_pics'   => $s->car_pics ?? [],
                'sold_at'    => $s->sold_at?->toIso8601String(),
                'price_sold' => $s->price_sold,
                'created_at' => $s->created_at?->toIso8601String(),
            ]),
        ]);
    }
}
