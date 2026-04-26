<?php

namespace App\Services;

use App\Models\Brand;
use App\Models\Car;
use App\Models\Company;
use App\Models\Content;
use App\Models\Logo;
use App\Models\Order;
use App\Models\VehicleModel;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Http\Client\Pool;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class LiveSyncService
{
    private string $base;

    public function __construct()
    {
        $this->base = rtrim(config('services.sync.live_url', ''), '/');
    }

    /**
     * Fetch all live API endpoints concurrently then upsert into local DB.
     * Returns true on success, false if the live URL is not configured or
     * a network error occurred.
     */
    public function pull(): bool
    {
        if (! $this->base) {
            return false;
        }

        SyncService::$isSyncing = true;

        try {
            // ── Fire all HTTP requests in parallel ───────────────────────────
            $responses = Http::pool(fn (Pool $pool) => [
                $pool->as('companies')->timeout(15)->get("{$this->base}/api/companies"),
                $pool->as('cars')->timeout(15)->get("{$this->base}/api/cars"),
                $pool->as('content')->timeout(15)->get("{$this->base}/api/content"),
                $pool->as('logos')->timeout(15)->get("{$this->base}/api/logos"),
                $pool->as('orders')->timeout(15)->get("{$this->base}/api/orders"),
            ]);

            $companies = $this->items($responses['companies']);
            $cars      = $this->items($responses['cars']);
            $content   = $this->items($responses['content']);
            $logos     = $this->items($responses['logos']);
            $orders    = $this->items($responses['orders']);

            // ── Persist in FK-safe order ─────────────────────────────────────
            $this->saveCompanies($companies);
            $this->saveCars($cars);       // also saves brands + vehicle models
            $this->saveContent($content);
            $this->saveLogos($logos);
            $this->saveOrders($orders);

            return true;
        } catch (\Throwable $e) {
            Log::warning('LiveSync pull failed: '.$e->getMessage());

            return false;
        } finally {
            SyncService::$isSyncing = false;
        }
    }

    // ── Companies ─────────────────────────────────────────────────────────────

    private function saveCompanies(array $items): void
    {
        $liveIds = collect($items)->pluck('company_id')->filter()->all();

        foreach ($items as $item) {
            Company::updateOrCreate(
                ['id' => $item['company_id']],
                ['name' => $item['company_label']]
            );
        }

        // Remove companies that were deleted on the live site
        if (! empty($liveIds)) {
            Company::whereNotIn('id', $liveIds)->delete();
        }
    }

    // ── Cars (brands + vehicle models are embedded in the cars response) ──────

    private function saveCars(array $items): void
    {
        // 1. Brands
        $brands = collect($items)->whereNotNull('brand_id')->unique('brand_id');
        foreach ($brands as $item) {
            Brand::updateOrCreate(
                ['id' => $item['brand_id']],
                ['name' => $item['brand'] ?? '']
            );
        }

        // 2. Vehicle models
        $models = collect($items)->whereNotNull('model_id')->unique('model_id');
        foreach ($models as $item) {
            VehicleModel::updateOrCreate(
                ['id' => $item['model_id']],
                ['name' => $item['model'] ?? '', 'brand_id' => $item['brand_id'] ?? null]
            );
        }

        // 3. Delete local cars that no longer exist on the live site
        $liveCarIds = collect($items)->pluck('car_id')->filter()->all();
        if (! empty($liveCarIds)) {
            Car::whereNotIn('car_id', $liveCarIds)->delete();
        }

        // 4. Upsert remaining cars — car_id is the PK but not in $fillable,
        //    so we set it directly on the model instance to preserve the live ID.
        foreach ($items as $item) {
            $data = [
                'car_name'          => $item['car_name']          ?? null,
                'year'              => $item['year']              ?? null,
                'car_pic'           => $item['car_pic']           ?? null,
                'car_price'         => $item['car_price']         ?? null,
                'car_description'   => $item['car_description']   ?? null,
                'type'              => $item['type']              ?? null,
                'condition'         => $item['condition']         ?? null,
                'color'             => $item['color']             ?? null,
                'chassis'           => $item['chassis']           ?? null,
                'mileage'           => $item['mileage']           ?? null,
                'company_id'        => $item['company_id']        ?? null,
                'company_label'     => $item['company']           ?? null,
                'company_logo_path' => $item['company_logo_path'] ?? null,
                'brand_id'          => $item['brand_id']          ?? null,
                'brand_label'       => $item['brand']             ?? null,
                'vehicle_model_id'  => $item['model_id']          ?? null,
                'model_label'       => $item['model']             ?? null,
                'is_coming_soon'    => $item['is_coming_soon']    ?? false,
                'arrival_date'      => $item['arrival_date']      ?? null,
                'is_sold'           => $item['is_sold']           ?? false,
                'registration'      => $item['registration']      ?? null,
            ];

            $car = Car::find($item['car_id']) ?? new Car();
            $car->car_id = $item['car_id'];

            try {
                $car->fill($data)->save();
            } catch (UniqueConstraintViolationException) {
                Car::where('car_name', $item['car_name'])
                    ->where('year', $item['year'])
                    ->update($data);
            }

            // Restore original live timestamps so newest-first sort is correct
            DB::table('cars')->where('car_id', $item['car_id'])->update([
                'created_at' => Carbon::parse($item['created_at'] ?? now())->toDateTimeString(),
                'updated_at' => Carbon::parse($item['updated_at'] ?? now())->toDateTimeString(),
            ]);
        }
    }

    // ── Content ───────────────────────────────────────────────────────────────

    private function saveContent(array $items): void
    {
        $liveIds = collect($items)->pluck('contentID')->filter()->all();

        foreach ($items as $item) {
            Content::updateOrCreate(
                ['contentID' => $item['contentID']],
                [
                    'content_name'  => $item['content_name']  ?? null,
                    'content_video' => $item['content_video'] ?? null,
                    'duration'      => $item['duration']      ?? null,
                    'car_id'        => $item['car_id']        ?? null,
                ]
            );
        }

        if (! empty($liveIds)) {
            Content::whereNotIn('contentID', $liveIds)->delete();
        }
    }

    // ── Logos ─────────────────────────────────────────────────────────────────

    private function saveLogos(array $items): void
    {
        $liveIds = collect($items)->pluck('id')->filter()->all();

        foreach ($items as $item) {
            Logo::updateOrCreate(
                ['id' => $item['id']],
                ['name' => $item['name'] ?? null, 'path' => $item['path'] ?? null]
            );
        }

        if (! empty($liveIds)) {
            Logo::whereNotIn('id', $liveIds)->delete();
        }
    }

    // ── Orders ────────────────────────────────────────────────────────────────

    private function saveOrders(array $items): void
    {
        $liveIds = collect($items)->pluck('id')->filter()->all();

        $now = now()->toDateTimeString();

        foreach ($items as $item) {
            DB::table('orders')->upsert(
                [
                    'id'         => $item['id'],
                    'car_name'   => $item['car_name']   ?? null,
                    'year'       => $item['year']        ?? null,
                    'order_date' => $item['order_date']  ?? null,
                    'invoice'    => $item['invoice']     ?? null,
                    'receipt'    => $item['receipt']     ?? null,
                    'created_at' => Carbon::parse($item['created_at'] ?? $now)->toDateTimeString(),
                    'updated_at' => Carbon::parse($item['updated_at'] ?? $now)->toDateTimeString(),
                ],
                ['id'],
                ['car_name', 'year', 'order_date', 'invoice', 'receipt', 'updated_at'],
            );
        }

        if (! empty($liveIds)) {
            DB::table('orders')->whereNotIn('id', $liveIds)->delete();
        }
    }

    // ── Helper ────────────────────────────────────────────────────────────────

    private function items(\Illuminate\Http\Client\Response $response): array
    {
        return $response->successful() ? ($response->json('data') ?? []) : [];
    }
}
