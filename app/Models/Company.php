<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Company extends Model
{
    protected $fillable = ['name', 'logo'];

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
