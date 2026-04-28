<?php

namespace App\Filament\Resources\CarResource\Pages;

use App\Filament\Resources\CarResource;
use App\Models\Brand;
use App\Models\Car;
use App\Models\Company;
use App\Models\SoldCar;
use App\Models\VehicleModel;
use Filament\Resources\Pages\CreateRecord;

class CreateCar extends CreateRecord
{
    protected static string $resource = CarResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['car_pic'] = array_values($data['car_pic_new'] ?? []);

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
        $data['is_sold'] = ! empty($data['is_sold']) ? 'sold' : 'available';
        $data['sold_at'] = $data['is_sold'] === 'sold' ? now() : null;
        $data['registration'] = ! empty($data['registration']) ? 'registered' : 'unregistered';

        return $data;
    }

    protected function afterCreate(): void
    {
        $car = $this->record;

        if ($car->is_sold === 'sold') {
            SoldCar::create([
                'car_id'     => $car->car_id,
                'car_name'   => $car->car_name,
                'car_pics'   => $car->car_pic ?? [],
                'sold_at'    => $car->sold_at ?? now(),
                'price_sold' => $car->car_price,
            ]);
        }
    }

    private function resolveCompanyId(?string $name, ?string $logo): ?int
    {
        if (blank($name)) {
            return null;
        }

        $company = Company::whereRaw('LOWER(name) = ?', [strtolower(trim($name))])->first();

        if ($company) {
            if ($logo) {
                $company->update(['logo' => $logo]);
            }

            return $company->id;
        }

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
