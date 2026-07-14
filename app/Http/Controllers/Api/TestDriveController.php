<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Car;
use App\Models\TestDrive;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class TestDriveController extends Controller
{
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'car_id'        => ['nullable', 'integer'],
            'car_name'      => ['required', 'string', 'max:255'],
            'year'          => ['nullable', 'string', 'max:4'],
            'photo'         => ['nullable', 'file', 'mimes:jpeg,jpg,png,webp', 'max:10240'],
            'booked_at'     => ['required', 'date'],
            'customer_name' => ['required', 'string', 'max:255'],
            'phone'         => ['required', 'string', 'max:20'],
            'email'         => ['required', 'email', 'max:255'],
        ]);

        $photoPath = null;

        if ($request->hasFile('photo')) {
            $photoPath = $request->file('photo')->store('test-drives/photos', 'public');
        }

        // Auto-resolve car_id from car_name when not supplied
        if (empty($validated['car_id'])) {
            $car = Car::where('car_name', $validated['car_name'])->first();
            $validated['car_id'] = $car?->car_id;
        }

        $testDrive = TestDrive::create([
            'car_id'        => $validated['car_id'] ?? null,
            'car_name'      => $validated['car_name'],
            'year'          => $validated['year'] ?? null,
            'photo'         => $photoPath,
            'booked_at'     => $validated['booked_at'],
            'customer_name' => $validated['customer_name'],
            'phone'         => $validated['phone'],
            'email'         => $validated['email'],
        ]);

        return response()->json([
            'message' => 'Test drive booked successfully.',
            'data'    => $this->format($testDrive),
        ], 201);
    }

    private function format(TestDrive $testDrive): array
    {
        return [
            'id'            => $testDrive->id,
            'car_id'        => $testDrive->car_id,
            'car_name'      => $testDrive->car_name,
            'year'          => $testDrive->year,
            'photo'         => $testDrive->photo
                ? Storage::disk('public')->url($testDrive->photo)
                : null,
            'booked_at'     => $testDrive->booked_at?->toIso8601String(),
            'customer_name' => $testDrive->customer_name,
            'phone'         => $testDrive->phone,
            'email'         => $testDrive->email,
            'created_at'    => $testDrive->created_at,
            'updated_at'    => $testDrive->updated_at,
        ];
    }
}
