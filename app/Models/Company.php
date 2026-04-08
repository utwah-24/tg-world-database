<?php

namespace App\Models;

use App\Models\Car;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Company extends Model
{
    protected $fillable = ['name', 'logo'];

    /** Always include the resolved logo URL alongside the raw path. */
    protected $appends = ['logo_url'];

    /**
     * Returns a fully-encoded absolute URL for the company logo.
     * Uses MEDIA_BASE_URL so cPanel deployments include the /public prefix.
     */
    public function getLogoUrlAttribute(): ?string
    {
        return $this->logo ? Car::mediaUrl($this->logo) : null;
    }

    protected static function booted(): void
    {
        static::saved(function (Company $company): void {
            Car::where('company_id', $company->id)->update([
                'company_label' => $company->name,
                'company_logo_path' => $company->logo,
            ]);
        });

        static::deleting(function (Company $company): void {
            Car::where('company_id', $company->id)->update([
                'company_label' => null,
                'company_logo_path' => null,
            ]);
        });
    }

    public function cars(): HasMany
    {
        return $this->hasMany(Car::class, 'company_id');
    }
}
