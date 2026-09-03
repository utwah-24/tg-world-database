<?php

namespace Tests\Feature;

use App\Mail\NewQuotationMail;
use App\Models\AuthSession;
use App\Models\Client;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class QuotationsTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        Mail::fake();
        config(['mail.quotation_notifications_to' => 'dashboard@example.com']);
        Storage::fake('local');

        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name')->nullable();
            $table->string('username');
            $table->string('normalized_username')->unique();
            $table->string('email');
            $table->string('normalized_email')->unique();
            $table->string('password');
            $table->string('phone_number');
            $table->string('normalized_phone')->unique();
            $table->string('role')->default('customer');
            $table->timestamp('disabled_at')->nullable();
            $table->timestamps();
        });
        Schema::create('auth_sessions', function (Blueprint $table) {
            $table->id();
            $table->string('token_hash')->unique();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->timestamp('expires_at');
            $table->timestamp('last_used_at')->nullable();
            $table->timestamp('revoked_at')->nullable();
            $table->timestamps();
        });
        Schema::create('cars', function (Blueprint $table) {
            $table->id('car_id');
            $table->string('car_name');
            $table->string('year')->nullable();
            $table->json('car_pic')->nullable();
            $table->string('car_price')->nullable();
            $table->string('chassis')->nullable();
            $table->string('color')->nullable();
            $table->string('mileage')->nullable();
            $table->boolean('is_sold')->default(false);
            $table->unsignedInteger('total_available')->nullable();
            $table->timestamps();
        });
        Schema::create('quotations', function (Blueprint $table) {
            $table->id();
            $table->string('reference')->unique();
            $table->foreignId('customer_id')->constrained('users')->cascadeOnDelete();
            $table->unsignedBigInteger('car_id');
            $table->string('status')->default('pending');
            $table->unsignedBigInteger('proposed_price');
            $table->char('currency', 3);
            $table->string('full_name');
            $table->string('email');
            $table->string('phone');
            $table->string('delivery_address')->nullable();
            $table->string('delivery_city')->nullable();
            $table->string('delivery_region')->nullable();
            $table->string('delivery_postal_code')->nullable();
            $table->text('customer_notes')->nullable();
            $table->json('vehicle_snapshot');
            $table->text('staff_notes')->nullable();
            $table->unsignedBigInteger('counter_price')->nullable();
            $table->string('preview_pdf_path')->nullable();
            $table->char('submission_fingerprint', 64)->unique();
            $table->timestamp('reviewed_at')->nullable();
            $table->timestamp('expired_at')->nullable();
            $table->timestamps();
            $table->foreign('car_id')->references('car_id')->on('cars')->cascadeOnDelete();
        });
        Schema::create('quotation_audits', function (Blueprint $table) {
            $table->id();
            $table->foreignId('quotation_id')->constrained('quotations')->cascadeOnDelete();
            $table->string('actor_type');
            $table->unsignedBigInteger('actor_id')->nullable();
            $table->string('action');
            $table->json('old_values')->nullable();
            $table->json('new_values')->nullable();
            $table->timestamps();
        });
    }

    public function test_quotation_endpoints_require_authentication(): void
    {
        $this->postJson('/api/quotations', [])->assertUnauthorized()->assertJsonPath('error.code', 'UNAUTHENTICATED');
        $this->getJson('/api/quotations')->assertUnauthorized();
        $this->getJson('/api/quotations/1')->assertUnauthorized();
        $this->postJson('/api/quotations/1/withdraw', [])->assertUnauthorized();
    }

    public function test_customer_creates_quotation_with_canonical_snapshot_and_pdf(): void
    {
        [$user, $token] = $this->customer('buyer');
        $carId = $this->car();

        $response = $this->jsonRequest('POST', '/api/quotations', $token, $this->payload($carId))
            ->assertCreated()
            ->assertJsonPath('quotation.status', 'pending')
            ->assertJsonPath('quotation.carId', $carId)
            ->assertJsonPath('quotation.vehicle.title', 'Toyota Land Cruiser')
            ->assertJsonPath('quotation.vehicle.chassis', 'CHASSIS-CANONICAL');

        $id = $response->json('quotation.id');
        $reference = $response->json('quotation.reference');
        $this->assertMatchesRegularExpression('/^QT-\d{8}-\d{4,}$/', $reference);
        $this->assertDatabaseHas('quotations', ['id' => $id, 'customer_id' => $user->id, 'car_id' => $carId, 'phone' => '+255700000000']);
        $this->assertDatabaseHas('quotation_audits', ['quotation_id' => $id, 'action' => 'created']);
        Storage::disk('local')->assertExists("quotations/{$reference}.pdf");
        Mail::assertSent(NewQuotationMail::class, function (NewQuotationMail $mail) use ($id): bool {
            $html = $mail->render();

            return $mail->hasTo('dashboard@example.com')
                && $mail->quotation->id === $id
                && str_contains($html, 'TG World International')
                && str_contains($html, 'Toyota Land Cruiser')
                && str_contains($html, '350,000,000')
                && ! str_contains($html, 'TGworld/SUV/car.jpg');
        });

        $pdf = $this->jsonRequest('GET', "/api/quotations/{$id}/preview", $token)->assertOk()->assertHeader('content-type', 'application/pdf');
        $this->assertStringStartsWith('%PDF-1.4', $pdf->streamedContent());
    }

    public function test_invalid_missing_unavailable_and_protected_fields_are_rejected(): void
    {
        [, $token] = $this->customer('validation');
        $carId = $this->car();

        $this->jsonRequest('POST', '/api/quotations', $token, [])->assertUnprocessable()->assertJsonStructure(['error' => ['fields' => ['carId', 'proposedPrice']]]);
        $this->jsonRequest('POST', '/api/quotations', $token, $this->payload($carId, ['proposedPrice' => 1.5]))
            ->assertUnprocessable()->assertJsonStructure(['error' => ['fields' => ['proposedPrice']]]);
        $this->jsonRequest('POST', '/api/quotations', $token, $this->payload(999))->assertNotFound()->assertJsonPath('error.code', 'CAR_NOT_FOUND');
        $this->jsonRequest('POST', '/api/quotations', $token, $this->payload($carId, ['userId' => 999]))->assertUnprocessable();

        DB::table('cars')->where('car_id', $carId)->update(['is_sold' => true]);
        $this->jsonRequest('POST', '/api/quotations', $token, $this->payload($carId))->assertConflict()->assertJsonPath('error.code', 'CAR_NOT_AVAILABLE');
    }

    public function test_rapid_duplicate_submission_is_rejected(): void
    {
        [, $token] = $this->customer('duplicate');
        $payload = $this->payload($this->car());

        $this->jsonRequest('POST', '/api/quotations', $token, $payload)->assertCreated();
        $this->jsonRequest('POST', '/api/quotations', $token, $payload)->assertConflict()->assertJsonPath('error.code', 'DUPLICATE_QUOTATION');
        $this->assertDatabaseCount('quotations', 1);
    }

    public function test_customer_can_only_list_read_preview_and_withdraw_own_quotation(): void
    {
        [, $ownerToken] = $this->customer('owner');
        [, $otherToken] = $this->customer('other');
        $created = $this->jsonRequest('POST', '/api/quotations', $ownerToken, $this->payload($this->car()))->assertCreated();
        $id = $created->json('quotation.id');

        $this->jsonRequest('GET', '/api/quotations', $ownerToken)->assertOk()->assertJsonCount(1, 'data');
        $this->jsonRequest('GET', '/api/quotations', $otherToken)->assertOk()->assertJsonCount(0, 'data');
        $this->jsonRequest('GET', "/api/quotations/{$id}", $otherToken)->assertNotFound();
        $this->jsonRequest('GET', "/api/quotations/{$id}/preview", $otherToken)->assertNotFound();
        $this->jsonRequest('POST', "/api/quotations/{$id}/withdraw", $otherToken, [])->assertNotFound();
        $this->jsonRequest('POST', "/api/quotations/{$id}/withdraw", $ownerToken, [])->assertOk()->assertJsonPath('quotation.status', 'withdrawn');
        $this->assertDatabaseHas('quotation_audits', ['quotation_id' => $id, 'action' => 'withdrawn']);
    }

    private function payload(int $carId, array $overrides = []): array
    {
        return array_replace_recursive([
            'carId' => $carId,
            'proposedPrice' => 350000000,
            'currency' => 'TZS',
            'buyer' => ['fullName' => 'JOHN DOE', 'email' => 'JOHN@EXAMPLE.COM', 'phone' => '0700 000 000'],
            'delivery' => ['address' => '123 Main Street', 'city' => 'Dar es Salaam', 'region' => 'Kinondoni', 'postalCode' => '14111'],
            'notes' => 'Please confirm whether financing is available.',
        ], $overrides);
    }

    private function car(): int
    {
        return DB::table('cars')->insertGetId([
            'car_name' => 'Toyota Land Cruiser', 'year' => '2024', 'car_pic' => json_encode(['TGworld/SUV/car.jpg']),
            'car_price' => '400 Million', 'chassis' => 'CHASSIS-CANONICAL', 'color' => 'Black', 'mileage' => '1000 km',
            'is_sold' => false, 'total_available' => 1, 'created_at' => now(), 'updated_at' => now(),
        ], 'car_id');
    }

    private function customer(string $suffix): array
    {
        $phone = '+2557'.str_pad((string) Client::count(), 8, '0', STR_PAD_LEFT);
        $user = Client::query()->create([
            'name' => "User {$suffix}", 'username' => "user-{$suffix}", 'normalized_username' => "user-{$suffix}",
            'email' => "{$suffix}@example.com", 'normalized_email' => "{$suffix}@example.com",
            'password' => 'a secure password', 'phone_number' => $phone, 'normalized_phone' => $phone,
        ]);
        $token = bin2hex(random_bytes(32));
        AuthSession::query()->create(['token_hash' => hash('sha256', $token), 'user_id' => $user->id, 'expires_at' => now()->addHour(), 'last_used_at' => now()]);

        return [$user, $token];
    }

    private function jsonRequest(string $method, string $uri, string $token, array $payload = [])
    {
        return $this->call($method, $uri, $payload, ['tgworld_session' => $token], [], ['HTTP_ACCEPT' => 'application/json', 'CONTENT_TYPE' => 'application/json'], json_encode($payload, JSON_THROW_ON_ERROR));
    }
}
