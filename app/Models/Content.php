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

    /**
     * Returns the publicly accessible URL for the content video.
     */
    public function getVideoUrlAttribute(): ?string
    {
        if (! $this->content_video) {
            return null;
        }

        return '/' . implode('/', array_map('rawurlencode', explode('/', ltrim($this->content_video, '/'))));
    }
}
