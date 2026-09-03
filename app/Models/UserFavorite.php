<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserFavorite extends Model
{
    protected $fillable = ['user_id', 'car_id'];

    protected function casts(): array
    {
        return [
            'user_id' => 'integer',
            'car_id' => 'integer',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(Client::class, 'user_id');
    }

    public function car(): BelongsTo
    {
        return $this->belongsTo(Car::class, 'car_id', 'car_id');
    }
}
