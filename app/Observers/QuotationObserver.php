<?php

namespace App\Observers;

use App\Models\Quotation;
use App\Models\QuotationAudit;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class QuotationObserver
{
    public function updating(Quotation $quotation): void
    {
        if ($quotation->isDirty('status') && in_array($quotation->status, ['reviewing', 'accepted', 'countered', 'rejected'], true)) {
            $quotation->reviewed_at = now();
        }
    }

    public function updated(Quotation $quotation): void
    {
        if (! Auth::check()) {
            return;
        }

        $tracked = ['status', 'staff_notes', 'counter_price', 'reviewed_at', 'expired_at'];
        $changes = array_intersect_key($quotation->getChanges(), array_flip($tracked));
        if (! $changes) {
            return;
        }

        QuotationAudit::query()->create([
            'quotation_id' => $quotation->id,
            'actor_type' => 'staff',
            'actor_id' => Auth::id(),
            'action' => 'staff_updated',
            'old_values' => collect(array_keys($changes))->mapWithKeys(fn ($key) => [$key => $quotation->getOriginal($key)])->all(),
            'new_values' => $changes,
        ]);

        if (array_key_exists('status', $changes) || array_key_exists('counter_price', $changes)) {
            try {
                $counter = $quotation->counter_price ? ' Counter offer: TZS '.number_format($quotation->counter_price).'.' : '';
                Mail::raw("Your quotation {$quotation->reference} is now {$quotation->status}.{$counter}", fn ($message) => $message->to($quotation->email)->subject("Quotation {$quotation->reference} update"));
            } catch (\Throwable $exception) {
                Log::error('Quotation status email failed', ['quotation_id' => $quotation->id, 'exception' => $exception::class, 'message' => $exception->getMessage()]);
            }
        }
    }
}
