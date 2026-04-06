<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Content extends Model
{
    protected $table = 'content';

    protected $primaryKey = 'contentID';

    protected $fillable = [
        'content_name',
        'content_video',
        'duration',
        'car_id',
    ];

    /** Always include the resolved URL alongside the raw path. */
    protected $appends = ['video_url'];

    public function car(): BelongsTo
    {
        return $this->belongsTo(Car::class, 'car_id', 'car_id');
    }

    /**
     * Returns a fully-encoded absolute URL for the content video.
     * Uses MEDIA_BASE_URL so cPanel deployments serving from the project root
     * automatically include the required /public prefix.
     */
    public function getVideoUrlAttribute(): ?string
    {
        if (! $this->content_video) {
            return null;
        }

        $base = config('app.media_url');
        $segments = array_map('rawurlencode', explode('/', ltrim($this->content_video, '/')));

        return $base.'/'.implode('/', $segments);
    }
}
