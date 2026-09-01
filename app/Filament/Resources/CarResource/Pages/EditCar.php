<?php

namespace App\Filament\Resources\CarResource\Pages;

use App\Filament\Resources\CarResource;
use App\Models\Brand;
use App\Models\Car;
use App\Models\Company;
use App\Models\SoldCar;
use App\Models\VehicleModel;
use Filament\Actions;
use Filament\Actions\Action;
use Filament\Resources\Pages\EditRecord;

class EditCar extends EditRecord
{
    protected static string $resource = CarResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function getCancelFormAction(): Action
    {
        return parent::getCancelFormAction()
            ->url($this->getResource()::getUrl('index'));
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        $data['car_pic_existing'] = collect($data['car_pic'] ?? [])
            ->map(fn ($path) => ['path' => $path])
            ->values()
            ->toArray();

        $data['car_pic_new'] = [];

        // Populate the company name from the relationship
        $company = $data['company_id'] ? Company::find($data['company_id']) : null;

        $data['company_name'] = $company?->name;
        // company_logo intentionally left empty — FileUpload always starts blank.
        // The existing logo is shown via the read-only preview Placeholder above it.

        $data['brand_name'] = $data['brand_id']
            ? Brand::find($data['brand_id'])?->name
            : null;

        $data['model_name'] = $data['vehicle_model_id']
            ? VehicleModel::find($data['vehicle_model_id'])?->name
            : null;

        $data['car_price'] = Car::carPriceDigitsForForm($data['car_price'] ?? null);
        $data['mileage'] = Car::mileageForForm($data['mileage'] ?? null);

        // Convert string DB values back to booleans so the Toggle components render correctly
        $data['is_coming_soon'] = $data['is_coming_soon'] === 'set';
        $data['is_sold'] = $data['is_sold'] === 'sold';
        $data['registration'] = $data['registration'] === 'registered';
        $data['promo_set'] = (bool) ($data['promo_set'] ?? false);

        return $data;
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $existing = collect($data['car_pic_existing'] ?? [])
            ->pluck('path')
            ->filter()
            ->values()
            ->toArray();

        $new = array_values($data['car_pic_new'] ?? []);

        $data['car_pic'] = array_merge($existing, $new);

        unset($data['car_pic_existing'], $data['car_pic_new']);

        $data['car_price'] = Car::carPriceFromFormInput($data['car_price'] ?? null);
        $data['mileage'] = Car::mileageFromFormInput($data['mileage'] ?? null);

        // Resolve or create the company from the typed name
        $data['company_id'] = $this->resolveCompanyId(
            $data['company_name'] ?? null,
            $data['company_logo'] ?? null,
        );

        unset($data['company_name'], $data['company_logo']);

        $data = array_merge($data, Car::companySnapshotForCompanyId($data['company_id'] ?? null));

        $data['brand_id'] = $this->resolveBrandId($data['brand_name'] ?? null);
        unset($data['brand_name']);
        $data = array_merge($data, Car::brandSnapshotForBrandId($data['brand_id'] ?? null));

        $data['vehicle_model_id'] = $this->resolveVehicleModelId(
            $data['model_name'] ?? null,
            $data['brand_id'] ?? null,
        );
        unset($data['model_name']);
        $data = array_merge($data, Car::vehicleModelSnapshotForModelId($data['vehicle_model_id'] ?? null));

        // Convert toggle booleans to string values for the database
        $data['is_coming_soon'] = ! empty($data['arrival_date']) ? 'set' : null;

        $wasSold = $this->record->is_sold === 'sold';
        $willBeSold = ! empty($data['is_sold']);
        $data['is_sold'] = $willBeSold ? 'sold' : 'available';

        if ($willBeSold && ! $wasSold) {
            $data['sold_at'] = now();
            // Record the sale in sold_cars table
            SoldCar::create([
                'car_id'     => $this->record->car_id,
                'car_name'   => $this->record->car_name,
                'car_pics'   => $this->record->car_pic ?? [],
                'sold_at'    => now(),
                'price_sold' => $this->record->car_price,
            ]);
        } elseif (! $willBeSold) {
            $data['sold_at'] = null;
        }

        $isRegistered = ! empty($data['registration']);
        $data['registration'] = $isRegistered ? 'registered' : 'unregistered';
        $data['registration_number'] = $isRegistered
            ? ($data['registration_number'] ?? null)
            : null;
        $data['promo_set'] = ! empty($data['promo_set']);

        // Relationship sync is handled by Filament Select::relationship().
        // promo_price is recalculated in afterSave().
        unset($data['promotions']);

        return $data;
    }

    protected function afterSave(): void
    {
        if (! $this->record->promo_set) {
            $this->record->promotions()->sync([]);
            $this->record->promo_price = null;
            $this->record->saveQuietly();

            return;
        }

        $this->record->refreshPromoPrice();
    }

    private function resolveCompanyId(?string $name, ?string $logo): ?int
    {
        if (blank($name)) {
            return null;
        }

        $company = Company::whereRaw('LOWER(name) = ?', [strtolower(trim($name))])->first();

        if ($company) {
            // Update logo only if a new one was uploaded
            if ($logo) {
                $company->update(['logo' => $logo]);
            }

            return $company->id;
        }

        // Brand-new company — create it with the optional logo
        return Company::create([
            'name' => trim($name),
            'logo' => $logo ?: null,
        ])->id;
    }

    private function resolveBrandId(?string $name): ?int
    {
        if (blank($name)) {
            return null;
        }

        $trimmed = trim($name);
        $existing = Brand::whereRaw('LOWER(name) = ?', [strtolower($trimmed)])->first();

        if ($existing) {
            return $existing->id;
        }

        return Brand::create(['name' => $trimmed])->id;
    }

    private function resolveVehicleModelId(?string $name, ?int $brandId): ?int
    {
        if (blank($name) || ! $brandId) {
            return null;
        }

        $trimmed = trim($name);
        $existing = VehicleModel::query()
            ->where('brand_id', $brandId)
            ->whereRaw('LOWER(name) = ?', [strtolower($trimmed)])
            ->first();

        if ($existing) {
            return $existing->id;
        }

        return VehicleModel::create([
            'brand_id' => $brandId,
            'name' => $trimmed,
        ])->id;
    }
}
