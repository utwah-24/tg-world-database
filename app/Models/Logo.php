<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Logo extends Model
{
    protected $fillable = [
        'name',
        'path',
    ];

    /** Always include the resolved URL alongside the raw path. */
    protected $appends = ['logo_url'];

    /**
     * Returns a fully-encoded absolute URL for the logo image.
     * Uses MEDIA_BASE_URL so cPanel deployments serving from the project root
     * automatically include the required /public prefix.
     */
    public function getLogoUrlAttribute(): ?string
    {
        if (! $this->path) {
            return null;
        }

        $base = config('app.media_url');
        $segments = array_map('rawurlencode', explode('/', ltrim($this->path, '/')));

        return $base.'/'.implode('/', $segments);
    }
}
