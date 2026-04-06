<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Brand extends Model
{
    protected $fillable = ['name'];

    protected static function booted(): void
    {
        static::saved(function (Brand $brand): void {
            Car::where('brand_id', $brand->id)->update([
                'brand_label' => $brand->name,
            ]);
        });

        static::deleting(function (Brand $brand): void {
            Car::where('brand_id', $brand->id)->update([
                'brand_label' => null,
            ]);
        });
    }

    public function cars(): HasMany
    {
        return $this->hasMany(Car::class, 'brand_id');
    }

    public function vehicleModels(): HasMany
    {
        return $this->hasMany(VehicleModel::class, 'brand_id');
    }
}
