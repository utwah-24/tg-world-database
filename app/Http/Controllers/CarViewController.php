<?php

namespace App\Http\Controllers;

use App\Models\Car;
use Illuminate\View\View;

class CarViewController extends Controller
{
    public function show(int $carId): View
    {
        $car = Car::where('car_id', $carId)->firstOrFail();

        return view('car-show', [
            'car' => $car,
        ]);
    }
}
