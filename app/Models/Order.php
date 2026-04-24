<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    protected $fillable = [
        'order_date',
        'car_name',
        'year',
        'invoice',
        'receipt',
    ];

    protected $casts = [
        'order_date' => 'date',
    ];
}
