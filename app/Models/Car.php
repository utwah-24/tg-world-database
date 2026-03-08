<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Car extends Model
{
    protected $primaryKey = 'car_id';

    protected $fillable = [
        'car_name',
        'car_pic',
        'car_price',
        'car_description',
        'type',
        'condition',
    ];

    protected $casts = [
        'car_pic' => 'array',
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
                $base     = rtrim(config('app.url'), '/');
                $segments = explode('/', ltrim($path, '/'));
                $encoded  = array_map('rawurlencode', $segments);
                return $base . '/' . implode('/', $encoded);
            })
            ->toArray();
    }
}
