<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Content extends Model
{
    protected $table = 'content';

    protected $primaryKey = 'contentID';

    protected $fillable = [
        'content_name',
        'content_video',
        'duration',
    ];
}
