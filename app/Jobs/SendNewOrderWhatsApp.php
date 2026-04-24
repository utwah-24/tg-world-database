<?php

namespace App\Jobs;

use App\Models\Order;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Twilio\Rest\Client;

class SendNewOrderWhatsApp implements ShouldQueue
{
    use Queueable;

    public int $tries = 3;

    public int $backoff = 60;

    public function __construct(private readonly Order $order) {}

    public function handle(): void
    {
        $sid   = config('services.twilio.sid');
        $token = config('services.twilio.token');
        $from  = config('services.twilio.whatsapp_from');
        $to    = config('services.twilio.whatsapp_to');

        if (! $sid || ! $token || ! $from || ! $to) {
            Log::warning('WhatsApp notification skipped: Twilio credentials not configured.');

            return;
        }

        $order = $this->order;

        $invoiceUrl = $order->invoice ? Storage::disk('public')->url($order->invoice) : 'Not provided';
        $receiptUrl = $order->receipt ? Storage::disk('public')->url($order->receipt) : 'Not provided';
        $carLabel   = trim(($order->year ? $order->year.' ' : '').$order->car_name);

        $message = implode("\n", [
            '🛒 *You have got a new order!*',
            '',
            "📦 *Order ID:* #{$order->id}",
            "🚗 *Car:* {$carLabel}",
            '📅 *Order Date:* '.$order->order_date->format('d M Y'),
            "📄 *Invoice:* {$invoiceUrl}",
            "🧾 *Receipt:* {$receiptUrl}",
            '🕐 *Received at:* '.$order->created_at->format('d M Y, H:i'),
        ]);

        $client = new Client($sid, $token);

        $client->messages->create(
            "whatsapp:{$to}",
            [
                'from' => "whatsapp:{$from}",
                'body' => $message,
            ]
        );
    }
}
