<?php

namespace App\Models;

use App\Traits\SyncsToRemote;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\DB;

class Company extends Model
{
    use SyncsToRemote;

    protected $fillable = ['name', 'logo'];

    /** Always include the resolved logo URL alongside the raw path. */
    protected $appends = ['logo_url'];

    /**
     * Returns a fully-encoded absolute URL for the company logo.
     * Uses MEDIA_BASE_URL so cPanel deployments include the /public prefix.
     */
    public function getLogoUrlAttribute(): ?string
    {
        $path = $this->resolvedLogoPath();

        return $path ? Car::mediaUrl($path) : null;
    }

    /**
     * Prefer companies.logo; fall back to a car snapshot when the company row was never backfilled.
     */
    public function resolvedLogoPath(): ?string
    {
        if (filled($this->logo)) {
            return self::normalizeStoredPath($this->logo);
        }

        $fromCar = $this->cars()->whereNotNull('company_logo_path')->value('company_logo_path');

        return $fromCar ? self::normalizeStoredPath($fromCar) : null;
    }

    /**
     * Normalize a stored logo value or absolute URL to a relative public path.
     */
    public static function normalizeStoredPath(?string $value): ?string
    {
        if (! filled($value)) {
            return null;
        }

        $value = trim($value);

        if (! str_starts_with($value, 'http://') && ! str_starts_with($value, 'https://')) {
            return ltrim($value, '/');
        }

        $path = parse_url($value, PHP_URL_PATH) ?: '';
        $path = ltrim($path, '/');

        if (str_starts_with($path, 'public/')) {
            $path = substr($path, 7);
        }

        return $path !== '' ? $path : null;
    }

    /**
     * Copy logo paths from linked cars when companies.logo is still empty.
     */
    public static function backfillLogosFromCars(): int
    {
        $rows = DB::table('cars')
            ->whereNotNull('company_logo_path')
            ->whereNotNull('company_id')
            ->selectRaw('company_id, MAX(company_logo_path) as logo_path')
            ->groupBy('company_id')
            ->get();

        $updated = 0;

        foreach ($rows as $row) {
            $path = self::normalizeStoredPath($row->logo_path);

            if (! $path) {
                continue;
            }

            $affected = self::query()
                ->where('id', $row->company_id)
                ->where(function ($query) {
                    $query->whereNull('logo')->orWhere('logo', '');
                })
                ->update(['logo' => $path]);

            $updated += $affected;
        }

        return $updated;
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
