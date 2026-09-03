<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Quotation extends Model
{
    public const STATUSES = ['pending', 'reviewing', 'accepted', 'countered', 'rejected', 'withdrawn', 'expired'];

    protected $fillable = [
        'reference', 'customer_id', 'car_id', 'status', 'proposed_price', 'currency',
        'full_name', 'email', 'phone', 'delivery_address', 'delivery_city',
        'delivery_region', 'delivery_postal_code', 'customer_notes', 'vehicle_snapshot',
        'staff_notes', 'counter_price', 'preview_pdf_path', 'submission_fingerprint',
        'reviewed_at', 'expired_at',
    ];

    protected $hidden = ['submission_fingerprint'];

    protected function casts(): array
    {
        return [
            'proposed_price' => 'integer',
            'counter_price' => 'integer',
            'vehicle_snapshot' => 'array',
            'reviewed_at' => 'datetime',
            'expired_at' => 'datetime',
        ];
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Client::class, 'customer_id');
    }

    public function car(): BelongsTo
    {
        return $this->belongsTo(Car::class, 'car_id', 'car_id');
    }

    public function audits(): HasMany
    {
        return $this->hasMany(QuotationAudit::class);
    }
}
