<?php

namespace App\Models;

use App\Traits\SyncsToRemote;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class VehicleModel extends Model
{
    use SyncsToRemote;
    protected $table = 'vehicle_models';

    protected $fillable = ['brand_id', 'name'];

    protected static function booted(): void
    {
        static::saved(function (VehicleModel $vehicleModel): void {
            Car::where('vehicle_model_id', $vehicleModel->id)->update([
                'model_label' => $vehicleModel->name,
            ]);
        });

        static::deleting(function (VehicleModel $vehicleModel): void {
            Car::where('vehicle_model_id', $vehicleModel->id)->update([
                'model_label' => null,
            ]);
        });
    }

    public function brand(): BelongsTo
    {
        return $this->belongsTo(Brand::class, 'brand_id');
    }

    public function cars(): HasMany
    {
        return $this->hasMany(Car::class, 'vehicle_model_id');
    }
}
