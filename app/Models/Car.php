<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Car extends Model
{
    protected $primaryKey = 'car_id';

    protected $fillable = [
        'car_name',
        'year',
        'car_pic',
        'car_price',
        'car_description',
        'type',
        'company',
        'condition',
        'brand',
        'is_coming_soon',
        'arrival_date',
        'is_sold',
    ];

    protected $casts = [
        'car_pic'      => 'array',
        'arrival_date' => 'date',
    ];

    public function content(): HasOne
    {
        return $this->hasOne(Content::class, 'car_id', 'car_id');
    }

    /**
     * Returns an array of fully-encoded absolute URLs for each car photo.
     * Used by Filament's ImageColumn via the 'car_pic_urls' attribute name.
     */
    public function getCarPicUrlsAttribute(): array
    {
        return collect($this->car_pic ?? [])
            ->map(function ($path) {
                $segments = explode('/', ltrim($path, '/'));
                $encoded  = array_map('rawurlencode', $segments);
                // Use the actual request host so this works regardless of APP_URL config
                try {
                    $base = request()->getSchemeAndHttpHost();
                } catch (\Throwable $e) {
                    $base = rtrim(config('app.url'), '/');
                }
                return $base . '/public/' . implode('/', $encoded);
            })
            ->toArray();
    }
}
