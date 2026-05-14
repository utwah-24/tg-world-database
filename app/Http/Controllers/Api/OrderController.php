<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Car;
use App\Models\Order;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class OrderController extends Controller
{
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'car_name'     => ['required', 'string', 'max:255'],
            'email'        => ['nullable', 'email', 'max:255'],
            'year'         => ['nullable', 'string', 'max:4'],
            'invoice'      => ['nullable', 'file', 'mimes:pdf', 'max:20480'],
            'receipt'      => ['nullable', 'file', 'mimes:pdf', 'max:20480'],
            'amount_paid'  => ['nullable', 'numeric', 'min:0'],
            'amount_due'   => ['nullable', 'numeric', 'min:0'],
            'total_amount' => ['nullable', 'numeric', 'min:0'],
        ]);

        $invoicePath = null;
        $receiptPath = null;

        if ($request->hasFile('invoice')) {
            $invoicePath = $request->file('invoice')->store('orders/invoices', 'public');
        }

        if ($request->hasFile('receipt')) {
            $receiptPath = $request->file('receipt')->store('orders/receipts', 'public');
        }

        $car = Car::where('car_name', $validated['car_name'])->first();
        $carPics = $car?->car_pic ?? [];

        $order = Order::create([
            'car_name'     => $validated['car_name'],
            'car_id'       => $car?->car_id,
            'car_pics'     => $carPics,
            'email'        => $validated['email'] ?? null,
            'year'         => $validated['year'] ?? null,
            'order_date'   => now()->toDateString(),
            'invoice'      => $invoicePath,
            'receipt'      => $receiptPath,
            'amount_paid'  => $validated['amount_paid'] ?? null,
            'amount_due'   => $validated['amount_due'] ?? null,
            'total_amount' => $validated['total_amount'] ?? null,
        ]);

        return response()->json([
            'message' => 'Order submitted successfully.',
            'data'    => $this->formatOrder($order),
        ], 201);
    }

    public function index(): JsonResponse
    {
        $orders = Order::orderBy('created_at', 'desc')->get();

        return response()->json([
            'data' => $orders->map(fn (Order $order) => $this->formatOrder($order)),
        ]);
    }

    public function show(int $id): JsonResponse
    {
        $order = Order::find($id);

        if (! $order) {
            return response()->json(['message' => 'Order not found.'], 404);
        }

        return response()->json([
            'data' => $this->formatOrder($order),
        ]);
    }

    private function formatOrder(Order $order): array
    {
        return [
            'id'              => $order->id,
            'order_date'      => $order->order_date?->toDateString(),
            'email'           => $order->email,
            'car_name'        => $order->car_name,
            'car_id'          => $order->car_id,
            'total_available' => $order->total_available,
            'qty'             => $order->qty ?? 1,
            'car_pics'        => $order->car_pics ?? [],
            'year'            => $order->year,
            'invoice'         => $order->invoice ? Storage::disk('public')->url($order->invoice) : null,
            'receipt'         => $order->receipt ? Storage::disk('public')->url($order->receipt) : null,
            'amount_paid'     => $order->amount_paid,
            'amount_due'      => $order->amount_due,
            'total_amount'    => $order->total_amount,
            'status'          => (bool) $order->status,
            'created_at'      => $order->created_at,
            'updated_at'      => $order->updated_at,
        ];
    }
}
