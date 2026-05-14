<?php

namespace App\Models;

use App\Traits\SyncsToRemote;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use SyncsToRemote;

    protected $fillable = [
        'order_date',
        'email',
        'car_name',
        'car_id',
        'total_available',
        'qty',
        'car_pics',
        'year',
        'invoice',
        'receipt',
        'amount_paid',
        'amount_due',
        'total_amount',
        'status',
    ];

    protected $casts = [
        'order_date' => 'date',
        'status'     => 'boolean',
        'car_pics'   => 'array',
    ];

    public function getCarPicUrlsAttribute(): array
    {
        return collect($this->car_pics ?? [])
            ->map(fn ($path) => Car::mediaUrl($path))
            ->toArray();
    }
}
