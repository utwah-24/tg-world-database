<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Mail\NewQuotationMail;
use App\Models\Car;
use App\Models\Quotation;
use App\Models\QuotationAudit;
use App\Services\QuotationPdfService;
use Illuminate\Database\QueryException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class QuotationController extends Controller
{
    public function __construct(private QuotationPdfService $pdf) {}

    public function store(Request $request): JsonResponse
    {
        $requestId = (string) Str::uuid();
        if ($error = $this->rejectUnexpected($request)) {
            return $error;
        }

        $input = $request->all();
        data_set($input, 'buyer.phone', $this->phone((string) data_get($input, 'buyer.phone')));
        data_set($input, 'buyer.email', mb_strtolower(trim((string) data_get($input, 'buyer.email'))));
        $validator = Validator::make($input, [
            'carId' => ['required', 'integer', 'min:1'],
            'proposedPrice' => ['required', 'integer', 'min:1', 'max:99999999999999'],
            'currency' => ['required', 'in:TZS'],
            'buyer' => ['required', 'array'],
            'buyer.fullName' => ['required', 'string', 'max:255'],
            'buyer.email' => ['required', 'email:rfc', 'max:255'],
            'buyer.phone' => ['required', 'regex:/^\+255\d{9}$/'],
            'delivery' => ['nullable', 'array'],
            'delivery.address' => ['nullable', 'string', 'max:255'],
            'delivery.city' => ['nullable', 'string', 'max:100'],
            'delivery.region' => ['nullable', 'string', 'max:100'],
            'delivery.postalCode' => ['nullable', 'string', 'max:30'],
            'notes' => ['nullable', 'string', 'max:2000'],
        ], [
            'proposedPrice.integer' => 'Enter a valid proposed price.',
            'buyer.phone.regex' => 'Enter a valid Tanzanian phone number.',
        ]);
        if ($validator->fails()) {
            return $this->error('VALIDATION_FAILED', 'Please correct the highlighted fields.', 422, $validator->errors()->toArray());
        }

        $validated = $validator->validated();
        $car = Car::query()->find($validated['carId']);
        if (! $car) {
            return $this->error('CAR_NOT_FOUND', 'The selected car could not be found.', 404, ['carId' => ['The selected car could not be found.']]);
        }
        if ($car->is_sold || ($car->total_available !== null && (int) $car->total_available < 1)) {
            return $this->error('CAR_NOT_AVAILABLE', 'This vehicle is no longer available.', 409);
        }

        $fingerprint = hash('sha256', implode('|', [
            $request->user()->getKey(), $car->getKey(), $validated['proposedPrice'],
            intdiv(now()->timestamp, 60),
        ]));
        if (Quotation::query()->where('submission_fingerprint', $fingerprint)->exists()) {
            return $this->error('DUPLICATE_QUOTATION', 'This quotation was already submitted.', 409);
        }

        try {
            $quotation = DB::transaction(function () use ($validated, $request, $car, $fingerprint): Quotation {
                $quotation = Quotation::query()->create([
                    'reference' => 'TMP-'.Str::random(20),
                    'customer_id' => $request->user()->getKey(),
                    'car_id' => $car->getKey(),
                    'status' => 'pending',
                    'proposed_price' => $validated['proposedPrice'],
                    'currency' => 'TZS',
                    'full_name' => trim($validated['buyer']['fullName']),
                    'email' => $validated['buyer']['email'],
                    'phone' => $validated['buyer']['phone'],
                    'delivery_address' => data_get($validated, 'delivery.address'),
                    'delivery_city' => data_get($validated, 'delivery.city'),
                    'delivery_region' => data_get($validated, 'delivery.region'),
                    'delivery_postal_code' => data_get($validated, 'delivery.postalCode'),
                    'customer_notes' => $validated['notes'] ?? null,
                    'vehicle_snapshot' => $this->vehicleSnapshot($car),
                    'submission_fingerprint' => $fingerprint,
                ]);
                $quotation->update(['reference' => 'QT-'.now()->format('Ymd').'-'.str_pad((string) $quotation->id, 4, '0', STR_PAD_LEFT)]);
                $path = "quotations/{$quotation->reference}.pdf";
                if (! Storage::disk('local')->put($path, $this->pdf->render($quotation))) {
                    throw new \RuntimeException('Quotation preview could not be stored.');
                }
                $quotation->update(['preview_pdf_path' => $path]);
                QuotationAudit::query()->create([
                    'quotation_id' => $quotation->id,
                    'actor_type' => 'customer',
                    'actor_id' => $request->user()->getKey(),
                    'action' => 'created',
                    'new_values' => ['status' => 'pending', 'proposed_price' => $quotation->proposed_price],
                ]);

                return $quotation;
            });
        } catch (QueryException $exception) {
            if (Quotation::query()->where('submission_fingerprint', $fingerprint)->exists()) {
                return $this->error('DUPLICATE_QUOTATION', 'This quotation was already submitted.', 409);
            }
            Log::error('Quotation creation database failure', [
                'request_id' => $requestId,
                'user_id' => $request->user()->getKey(),
                'car_id' => $car->getKey(),
                'sql_state' => $exception->errorInfo[0] ?? null,
                'driver_code' => $exception->errorInfo[1] ?? null,
                'exception' => $exception::class,
                'database_message' => $exception->getPrevious()?->getMessage(),
            ]);

            return $this->error('QUOTATION_CREATE_FAILED', 'The quotation could not be created.', 500, [], $requestId);
        } catch (\Throwable $exception) {
            Log::error('Quotation creation failure', [
                'request_id' => $requestId,
                'user_id' => $request->user()->getKey(),
                'car_id' => $car->getKey(),
                'exception' => $exception::class,
                'message' => $exception->getMessage(),
            ]);

            return $this->error('QUOTATION_CREATE_FAILED', 'The quotation could not be created.', 500, [], $requestId);
        }

        try {
            Mail::to((string) config('mail.quotation_notifications_to'))->send(new NewQuotationMail($quotation));
            Mail::raw(
                "We received quotation {$quotation->reference}. You can track it in your TG World profile.",
                fn ($message) => $message->to($quotation->email)->subject("Quotation {$quotation->reference} received"),
            );
        } catch (\Throwable $exception) {
            Log::error('Quotation notification email failed', ['quotation_id' => $quotation->id, 'exception' => $exception::class, 'message' => $exception->getMessage()]);
        }

        return response()->json(['quotation' => $this->resource($quotation)], 201);
    }

    public function index(Request $request): JsonResponse
    {
        $page = Quotation::query()->where('customer_id', $request->user()->getKey())->latest()->paginate(15);

        return response()->json([
            'data' => collect($page->items())->map(fn (Quotation $quotation) => $this->resource($quotation)),
            'meta' => ['currentPage' => $page->currentPage(), 'lastPage' => $page->lastPage(), 'perPage' => $page->perPage(), 'total' => $page->total()],
        ]);
    }

    public function show(Request $request, mixed $id): JsonResponse
    {
        $quotation = $this->owned($request, $id);

        return $quotation ? response()->json(['quotation' => $this->resource($quotation)]) : $this->error('QUOTATION_NOT_FOUND', 'The quotation could not be found.', 404);
    }

    public function withdraw(Request $request, mixed $id): JsonResponse
    {
        if (array_keys($request->all())) {
            return $this->error('VALIDATION_FAILED', 'The request body must be empty.', 422, ['request' => ['The request body must be empty.']]);
        }
        $quotation = $this->owned($request, $id);
        if (! $quotation) {
            return $this->error('QUOTATION_NOT_FOUND', 'The quotation could not be found.', 404);
        }
        if (! in_array($quotation->status, ['pending', 'reviewing', 'countered'], true)) {
            return $this->error('QUOTATION_NOT_WITHDRAWABLE', 'This quotation can no longer be withdrawn.', 409);
        }
        $oldStatus = $quotation->status;
        $quotation->update(['status' => 'withdrawn']);
        QuotationAudit::query()->create([
            'quotation_id' => $quotation->id, 'actor_type' => 'customer', 'actor_id' => $request->user()->getKey(),
            'action' => 'withdrawn', 'old_values' => ['status' => $oldStatus], 'new_values' => ['status' => 'withdrawn'],
        ]);

        return response()->json(['quotation' => $this->resource($quotation->fresh())]);
    }

    public function preview(Request $request, mixed $id): mixed
    {
        $quotation = $this->owned($request, $id);
        if (! $quotation || ! $quotation->preview_pdf_path || ! Storage::disk('local')->exists($quotation->preview_pdf_path)) {
            return $this->error('QUOTATION_NOT_FOUND', 'The quotation preview could not be found.', 404);
        }

        return Storage::disk('local')->response($quotation->preview_pdf_path, "{$quotation->reference}.pdf", ['Content-Type' => 'application/pdf', 'Content-Disposition' => 'inline; filename="'.$quotation->reference.'.pdf"']);
    }

    private function owned(Request $request, mixed $id): ?Quotation
    {
        return ctype_digit((string) $id) ? Quotation::query()->where('customer_id', $request->user()->getKey())->find((int) $id) : null;
    }

    private function vehicleSnapshot(Car $car): array
    {
        $images = collect($car->car_pic ?? [])->filter(fn ($path) => is_string($path))->map(fn ($path) => Car::mediaUrl($path))->values()->all();

        return [
            'title' => $car->car_name, 'year' => $car->year, 'listedPrice' => $car->car_price,
            'primaryImageUrl' => $images[0] ?? null, 'images' => $images, 'chassis' => $car->chassis,
            'colour' => $car->color, 'mileage' => $car->mileage, 'fuel' => null, 'transmission' => null,
        ];
    }

    private function resource(Quotation $quotation): array
    {
        return [
            'id' => $quotation->id, 'reference' => $quotation->reference, 'status' => $quotation->status,
            'carId' => $quotation->car_id, 'proposedPrice' => $quotation->proposed_price,
            'counterPrice' => $quotation->counter_price, 'currency' => $quotation->currency,
            'buyer' => ['fullName' => $quotation->full_name, 'email' => $quotation->email, 'phone' => $quotation->phone],
            'delivery' => ['address' => $quotation->delivery_address, 'city' => $quotation->delivery_city, 'region' => $quotation->delivery_region, 'postalCode' => $quotation->delivery_postal_code],
            'notes' => $quotation->customer_notes, 'vehicle' => $quotation->vehicle_snapshot,
            'previewPdfUrl' => "/api/quotations/{$quotation->id}/preview",
            'createdAt' => $quotation->created_at->toISOString(), 'updatedAt' => $quotation->updated_at->toISOString(),
            'reviewedAt' => $quotation->reviewed_at?->toISOString(), 'expiredAt' => $quotation->expired_at?->toISOString(),
        ];
    }

    private function rejectUnexpected(Request $request): ?JsonResponse
    {
        $allowed = ['carId', 'proposedPrice', 'currency', 'buyer', 'delivery', 'notes'];
        $unexpected = array_diff(array_keys($request->all()), $allowed);
        $buyerUnexpected = is_array($request->input('buyer')) ? array_diff(array_keys($request->input('buyer')), ['fullName', 'email', 'phone']) : [];
        $deliveryUnexpected = is_array($request->input('delivery')) ? array_diff(array_keys($request->input('delivery')), ['address', 'city', 'region', 'postalCode']) : [];
        if (! $unexpected && ! $buyerUnexpected && ! $deliveryUnexpected) {
            return null;
        }

        return $this->error('VALIDATION_FAILED', 'Please correct the highlighted fields.', 422, ['request' => ['Unexpected or protected fields were provided.']]);
    }

    private function phone(string $value): string
    {
        $digits = preg_replace('/\D/', '', trim($value)) ?? '';
        if (str_starts_with($digits, '255')) {
            return '+'.$digits;
        }
        if (str_starts_with($digits, '0')) {
            return '+255'.substr($digits, 1);
        }

        return $digits === '' ? '' : '+'.$digits;
    }

    private function error(string $code, string $message, int $status, array $fields = [], ?string $requestId = null): JsonResponse
    {
        $error = ['code' => $code, 'message' => $message, 'fields' => $fields ?: (object) []];
        if ($requestId) {
            $error['requestId'] = $requestId;
        }

        return response()->json(['error' => $error], $status);
    }
}
