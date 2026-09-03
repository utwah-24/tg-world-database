<?php

namespace Tests\Feature;

use App\Models\AuthSession;
use App\Models\Client;
use Illuminate\Database\QueryException;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class FavoritesTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

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
            $table->string('ip_address')->nullable();
            $table->string('user_agent')->nullable();
            $table->timestamps();
        });
        Schema::create('cars', function (Blueprint $table) {
            $table->id('car_id');
            $table->string('car_name');
            $table->timestamps();
        });
        Schema::create('user_favorites', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->unsignedBigInteger('car_id');
            $table->timestamps();
            $table->foreign('car_id')->references('car_id')->on('cars')->cascadeOnDelete();
            $table->unique(['user_id', 'car_id']);
        });
    }

    public function test_list_add_and_remove_require_authentication(): void
    {
        $this->getJson('/api/favorites')->assertUnauthorized()->assertJsonPath('error.code', 'UNAUTHENTICATED');
        $this->postJson('/api/favorites', ['carId' => 1])->assertUnauthorized();
        $this->deleteJson('/api/favorites/1', [])->assertUnauthorized();
        $this->postJson('/api/favorites/1/remove', [])->assertUnauthorized();
    }

    public function test_existing_car_can_be_added_and_listed_newest_first(): void
    {
        $token = $this->customerToken('first');
        $firstCar = $this->car('First car');
        $secondCar = $this->car('Second car');

        $this->jsonRequest('POST', '/api/favorites', $token, ['carId' => (string) $firstCar])
            ->assertCreated()
            ->assertJsonPath('favorite.carId', $firstCar);
        $this->travel(1)->seconds();
        $this->jsonRequest('POST', '/api/favorites', $token, ['carId' => $secondCar])->assertCreated();

        $this->jsonRequest('GET', '/api/favorites', $token)
            ->assertOk()
            ->assertJsonPath('data.0.carId', $secondCar)
            ->assertJsonPath('data.1.carId', $firstCar);
    }

    public function test_duplicate_adds_are_idempotent_and_unique_constraint_guards_races(): void
    {
        $token = $this->customerToken('duplicate');
        $carId = $this->car('Duplicate car');

        $this->jsonRequest('POST', '/api/favorites', $token, ['carId' => $carId])->assertCreated();
        $this->jsonRequest('POST', '/api/favorites', $token, ['carId' => $carId])->assertOk();
        $this->assertDatabaseCount('user_favorites', 1);

        $favorite = DB::table('user_favorites')->first();
        $this->expectException(QueryException::class);
        DB::table('user_favorites')->insert([
            'user_id' => $favorite->user_id,
            'car_id' => $favorite->car_id,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function test_malformed_missing_and_unexpected_input_is_rejected(): void
    {
        $token = $this->customerToken('invalid');

        foreach ([null, '', 0, -1, '1.5', 'abc', []] as $invalid) {
            $this->jsonRequest('POST', '/api/favorites', $token, ['carId' => $invalid])
                ->assertUnprocessable()
                ->assertJsonPath('error.code', 'VALIDATION_FAILED')
                ->assertJsonStructure(['error' => ['fields' => ['carId']]]);
        }

        $this->jsonRequest('POST', '/api/favorites', $token, [])->assertUnprocessable();
        $this->jsonRequest('POST', '/api/favorites', $token, ['carId' => 1, 'user_id' => 999])
            ->assertUnprocessable()
            ->assertJsonStructure(['error' => ['fields' => ['request']]]);
        $this->jsonRequest('DELETE', '/api/favorites/not-an-id', $token)->assertUnprocessable();
    }

    public function test_nonexistent_car_returns_field_specific_not_found(): void
    {
        $token = $this->customerToken('missing');

        $this->jsonRequest('POST', '/api/favorites', $token, ['carId' => 999])
            ->assertNotFound()
            ->assertJsonPath('error.code', 'CAR_NOT_FOUND')
            ->assertJsonStructure(['error' => ['fields' => ['carId']]]);
    }

    public function test_favorites_are_isolated_by_user_and_removal_is_idempotent(): void
    {
        $firstToken = $this->customerToken('owner');
        $secondToken = $this->customerToken('other');
        $carId = $this->car('Private favorite');

        $this->jsonRequest('POST', '/api/favorites', $firstToken, ['carId' => $carId])->assertCreated();
        $this->jsonRequest('GET', '/api/favorites', $secondToken)->assertJsonCount(0, 'data');
        $this->jsonRequest('DELETE', "/api/favorites/{$carId}", $secondToken)->assertNoContent();
        $this->assertDatabaseCount('user_favorites', 1);
        $this->jsonRequest('POST', "/api/favorites/{$carId}/remove", $firstToken, [])->assertNoContent();
        $this->jsonRequest('DELETE', "/api/favorites/{$carId}", $firstToken)->assertNoContent();
        $this->assertDatabaseCount('user_favorites', 0);
    }

    public function test_deleting_user_or_car_cascades_favorites(): void
    {
        $firstToken = $this->customerToken('cascade-user');
        $firstUserId = AuthSession::where('token_hash', hash('sha256', $firstToken))->value('user_id');
        $firstCar = $this->car('Cascade user car');
        $this->jsonRequest('POST', '/api/favorites', $firstToken, ['carId' => $firstCar])->assertCreated();
        Client::findOrFail($firstUserId)->delete();
        $this->assertDatabaseCount('user_favorites', 0);

        $secondToken = $this->customerToken('cascade-car');
        $secondCar = $this->car('Cascade deleted car');
        $this->jsonRequest('POST', '/api/favorites', $secondToken, ['carId' => $secondCar])->assertCreated();
        DB::table('cars')->where('car_id', $secondCar)->delete();
        $this->assertDatabaseCount('user_favorites', 0);
    }

    private function customerToken(string $suffix): string
    {
        $user = Client::query()->create([
            'name' => "User {$suffix}",
            'username' => "user-{$suffix}",
            'normalized_username' => "user-{$suffix}",
            'email' => "{$suffix}@example.com",
            'normalized_email' => "{$suffix}@example.com",
            'password' => 'a secure password',
            'phone_number' => '+2557'.str_pad((string) Client::count(), 8, '0', STR_PAD_LEFT),
            'normalized_phone' => '+2557'.str_pad((string) Client::count(), 8, '0', STR_PAD_LEFT),
        ]);
        $token = bin2hex(random_bytes(32));
        AuthSession::query()->create([
            'token_hash' => hash('sha256', $token),
            'user_id' => $user->getKey(),
            'expires_at' => now()->addHour(),
            'last_used_at' => now(),
        ]);

        return $token;
    }

    private function car(string $name): int
    {
        return DB::table('cars')->insertGetId([
            'car_name' => $name,
            'created_at' => now(),
            'updated_at' => now(),
        ], 'car_id');
    }

    private function jsonRequest(string $method, string $uri, string $token, array $payload = [])
    {
        return $this->call(
            $method,
            $uri,
            $payload,
            ['tgworld_session' => $token],
            [],
            ['HTTP_ACCEPT' => 'application/json', 'CONTENT_TYPE' => 'application/json'],
            json_encode($payload, JSON_THROW_ON_ERROR),
        );
    }
}
