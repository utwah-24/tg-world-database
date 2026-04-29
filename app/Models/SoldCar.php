<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SoldCar extends Model
{
    protected $fillable = [
        'order_id',
        'car_id',
        'car_name',
        'car_pics',
        'sold_at',
        'price_sold',
    ];

    protected $casts = [
        'sold_at'  => 'datetime',
        'car_pics' => 'array',
    ];

    public function car()
    {
        return $this->belongsTo(Car::class, 'car_id', 'car_id');
    }

    public function getCarPicUrlsAttribute(): array
    {
        return collect($this->car_pics ?? [])
            ->map(fn ($path) => Car::mediaUrl($path))
            ->toArray();
    }
}
