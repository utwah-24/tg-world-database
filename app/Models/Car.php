<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Car extends Model
{
    protected $primaryKey = 'car_id';

    protected $fillable = [
        'car_name',
        'car_pic',
        'car_price',
        'car_description',
    ];

    protected $casts = [
        'car_pic' => 'array',
    ];
}
