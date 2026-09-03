<?php

namespace App\Models;

use App\Services\PromotionService;
use App\Traits\SyncsToRemote;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Car extends Model
{
    use SyncsToRemote;

    protected $primaryKey = 'car_id';

    protected $fillable = [
        'car_name',
        'year',
        'car_pic',
        'car_price',
        'car_description',
        'notes',
        'test_drive_available',
        'type',
        'brand_id',
        'brand_label',
        'company_id',
        'company_label',
        'company_logo_path',
        'condition',
        'color',
        'chassis',
        'mileage',
        'vehicle_model_id',
        'model_label',
        'is_coming_soon',
        'arrival_date',
        'is_sold',
        'registration',
        'registration_number',
        'total_available',
        'in_dar',
        'location',
        'promo_set',
        'promo_price',
    ];

    protected $casts = [
        'car_pic' => 'array',
        'arrival_date' => 'date',
        'total_available' => 'integer',
        'in_dar' => 'boolean',
        'test_drive_available' => 'boolean',
        'promo_set' => 'boolean',
    ];

    public function content(): HasOne
    {
        return $this->hasOne(Content::class, 'car_id', 'car_id');
    }

    public function favorites(): HasMany
    {
        return $this->hasMany(UserFavorite::class, 'car_id', 'car_id');
    }

    public function promotions(): BelongsToMany
    {
        return $this->belongsToMany(
            Promotion::class,
            'car_promotion',
            'car_id',
            'promoID',
            'car_id',
            'promoID',
        )->withTimestamps();
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class, 'company_id');
    }

    public function brand(): BelongsTo
    {
        return $this->belongsTo(Brand::class, 'brand_id');
    }

    public function vehicleModel(): BelongsTo
    {
        return $this->belongsTo(VehicleModel::class, 'vehicle_model_id');
    }

    /**
     * @return array{brand_label: ?string}
     */
    public static function brandSnapshotForBrandId(?int $brandId): array
    {
        if (! $brandId) {
            return ['brand_label' => null];
        }

        $brand = Brand::find($brandId);

        return ['brand_label' => $brand?->name];
    }

    /**
     * @return array{model_label: ?string}
     */
    public static function vehicleModelSnapshotForModelId(?int $vehicleModelId): array
    {
        if (! $vehicleModelId) {
            return ['model_label' => null];
        }

        $vm = VehicleModel::find($vehicleModelId);

        return ['model_label' => $vm?->name];
    }

    /**
     * @return array{company_label: ?string, company_logo_path: ?string}
     */
    public static function companySnapshotForCompanyId(?int $companyId): array
    {
        if (! $companyId) {
            return ['company_label' => null, 'company_logo_path' => null];
        }

        $company = Company::find($companyId);

        return [
            'company_label' => $company?->name,
            'company_logo_path' => $company?->logo,
        ];
    }

    /**
     * Returns a fully-encoded absolute URL for the company logo, or null if not set.
     */
    public function getCompanyLogoUrlAttribute(): ?string
    {
        $logo = $this->company?->logo ?? $this->company_logo_path;

        return $logo ? self::mediaUrl($logo) : null;
    }

    /**
     * Returns an array of fully-encoded absolute URLs for each car photo.
     * Used by Filament's ImageColumn via the 'car_pic_urls' attribute name.
     */
    public function getCarPicUrlsAttribute(): array
    {
        return collect($this->car_pic ?? [])
            ->map(fn ($path) => self::mediaUrl($path))
            ->toArray();
    }

    /**
     * Builds a fully-encoded absolute media URL from a stored relative path.
     * Uses MEDIA_BASE_URL (config app.media_url) so cPanel deployments that serve
     * from the project root instead of public/ only need to set that one env var.
     */
    public static function mediaUrl(string $path): string
    {
        $base = config('app.media_url');
        $segments = array_map('rawurlencode', explode('/', ltrim($path, '/')));

        return $base.'/'.implode('/', $segments);
    }

    /**
     * Numeric part (integer or decimal) from stored price text for the admin form.
     */
    public static function carPriceDigitsForForm(?string $stored): ?string
    {
        if ($stored === null || trim((string) $stored) === '') {
            return null;
        }

        if (preg_match('/^\s*([\d.]+)\s+Million\b/i', (string) $stored, $m)) {
            return $m[1];
        }

        if (preg_match('/^\s*(\d+(?:\.\d+)?)/', (string) $stored, $m)) {
            return $m[1];
        }

        return null;
    }

    /**
     * Persist "{n} Million Tshs" from form input (digits and optional one decimal point).
     */
    public static function carPriceFromFormInput(mixed $value): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }

        $s = trim(str_replace(',', '.', (string) $value));
        if ($s === '' || ! preg_match('/^\d+(\.\d+)?$/', $s)) {
            return null;
        }

        [$int, $frac] = array_pad(explode('.', $s, 2), 2, null);
        $int = ltrim((string) $int, '0');
        if ($int === '') {
            $int = '0';
        }
        $normalized = $frac !== null && $frac !== '' ? $int.'.'.$frac : $int;

        return $normalized.' Million Tshs';
    }

    /**
     * Compute and return the promo price to persist for a list price + promo IDs.
     * Uses the highest price_reduction among the selected promotions.
     */
    public static function resolveStoredPromoPrice(?string $storedCarPrice, iterable $promoIds): ?string
    {
        $ids = collect($promoIds)->filter()->unique()->values();
        if ($ids->isEmpty()) {
            return null;
        }

        $bestPromoId = Promotion::query()
            ->whereIn('promoID', $ids)
            ->where('status', 'active')
            ->get()
            ->filter(fn (Promotion $promo) => PromotionService::isEffective($promo))
            ->sortByDesc('price_reduction')
            ->value('promoID');

        if (! $bestPromoId) {
            return null;
        }

        return self::discountedPricePreview(
            self::carPriceDigitsForForm($storedCarPrice),
            $bestPromoId,
        );
    }

    public function hasEffectivePromotion(): bool
    {
        return $this->promotions
            ->contains(fn (Promotion $promo) => PromotionService::isEffective($promo));
    }

    public function clearPromotionPricing(): void
    {
        $this->promo_set = false;
        $this->promo_price = null;
        $this->saveQuietly();
    }

    /**
     * Recalculate promo pricing or clear it when no effective promotions remain.
     */
    public function refreshPromotionState(): void
    {
        if (! $this->promo_set) {
            $this->clearPromotionPricing();

            return;
        }

        $effectiveIds = $this->promotions()
            ->get()
            ->filter(fn (Promotion $promo) => PromotionService::isEffective($promo))
            ->pluck('promoID');

        if ($effectiveIds->isEmpty()) {
            $this->clearPromotionPricing();

            return;
        }

        $this->promo_price = self::resolveStoredPromoPrice($this->car_price, $effectiveIds);
        $this->saveQuietly();
    }

    /**
     * Recalculate and persist promo_price from car_price + linked promotions.
     */
    public function refreshPromoPrice(): void
    {
        $this->refreshPromotionState();
    }

    /**
     * Discounted price from form digits + selected promo id(s) (dashboard preview).
     */
    public static function discountedPricePreview(?string $priceDigits, mixed $promoIds): ?string
    {
        if ($priceDigits === null || trim((string) $priceDigits) === '') {
            return null;
        }

        $ids = is_array($promoIds) || $promoIds instanceof \Illuminate\Support\Collection
            ? collect($promoIds)->filter()->values()
            : collect(blank($promoIds) ? [] : [$promoIds]);

        if ($ids->isEmpty()) {
            return null;
        }

        $promo = Promotion::query()
            ->whereIn('promoID', $ids)
            ->orderByDesc('price_reduction')
            ->first();

        if (! $promo) {
            return null;
        }

        $pct = (int) $promo->price_reduction;
        if ($pct <= 0 || $pct > 100) {
            return null;
        }

        $s = trim(str_replace(',', '.', (string) $priceDigits));
        if ($s === '' || ! preg_match('/^\d+(\.\d+)?$/', $s)) {
            return null;
        }

        $reduced = (float) $s * (1 - ($pct / 100));
        $formatted = rtrim(rtrim(number_format($reduced, 2, '.', ''), '0'), '.');

        return self::carPriceFromFormInput($formatted);
    }

    /**
     * Digits-only mileage for the admin form; stored values may be "85 000 km", etc.
     */
    public static function mileageForForm(?string $stored): ?string
    {
        if ($stored === null || trim((string) $stored) === '') {
            return null;
        }

        $digits = preg_replace('/\D/', '', (string) $stored);

        return $digits === '' ? null : $digits;
    }

    /**
     * Persist "{n} km" from numeric-only form input (suffix "km" is added automatically).
     */
    public static function mileageFromFormInput(mixed $value): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }

        $digits = preg_replace('/\D/', '', (string) $value);
        if ($digits === '') {
            return null;
        }

        $digits = ltrim($digits, '0');

        if ($digits === '') {
            $digits = '0';
        }

        return $digits.' km';
    }
}
