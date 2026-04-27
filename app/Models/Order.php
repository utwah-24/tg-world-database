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
        'year',
        'invoice',
        'receipt',
        'status',
    ];

    protected $casts = [
        'order_date' => 'date',
        'status'     => 'boolean',
    ];
}
