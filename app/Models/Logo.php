<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Logo extends Model
{
    protected $fillable = [
        'name',
        'path',
    ];

    /**
     * Returns a fully-encoded absolute URL for the logo image.
     * Used by Filament's ImageColumn via the 'logo_url' attribute name.
     */
    public function getLogoUrlAttribute(): ?string
    {
        if (! $this->path) {
            return null;
        }

        $base     = rtrim(config('app.url'), '/');
        $segments = explode('/', ltrim($this->path, '/'));
        $encoded  = array_map('rawurlencode', $segments);

        return $base . '/' . implode('/', $encoded);
    }
}
