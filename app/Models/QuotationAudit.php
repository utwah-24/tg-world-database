<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class QuotationAudit extends Model
{
    protected $fillable = ['quotation_id', 'actor_type', 'actor_id', 'action', 'old_values', 'new_values'];

    protected function casts(): array
    {
        return ['old_values' => 'array', 'new_values' => 'array'];
    }

    public function quotation(): BelongsTo
    {
        return $this->belongsTo(Quotation::class);
    }
}
