<?php

namespace Tests\Feature;

use App\Mail\PasswordResetMail;
use App\Models\AuthSession;
use App\Models\Client;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    private array $registration = [
        'username' => 'Jane.Doe',
        'email' => 'Jane@Example.com',
        'phone' => '0700 000 000',
        'password' => 'a secure passphrase',
    ];

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
            $table->timestamp('email_verified_at')->nullable();
            $table->timestamp('phone_verified_at')->nullable();
            $table->timestamp('disabled_at')->nullable();
            $table->rememberToken();
            $table->timestamps();
        });
        Schema::create('auth_sessions', function (Blueprint $table) {
            $table->id();
            $table->string('token_hash')->unique();
            $table->foreignId('user_id');
            $table->timestamp('expires_at');
            $table->timestamp('last_used_at')->nullable();
            $table->timestamp('revoked_at')->nullable();
            $table->string('ip_address')->nullable();
            $table->string('user_agent')->nullable();
            $table->timestamps();
        });
        Schema::create('customer_password_reset_tokens', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id');
            $table->string('token_hash')->unique();
            $table->timestamp('expires_at');
            $table->timestamp('consumed_at')->nullable();
            $table->timestamps();
        });
    }

    public function test_registration_normalizes_user_hashes_password_and_sets_http_only_cookie(): void
    {
        $response = $this->postJson('/api/auth/register', $this->registration);
        $response->assertCreated()->assertJsonPath('user.email', 'jane@example.com')->assertJsonMissing(['password' => true]);
        $user = Client::firstOrFail();
        $this->assertSame('jane.doe', $user->normalized_username);
        $this->assertSame('Jane.Doe', $user->name);
        $this->assertSame('+255700000000', $user->phone_number);
        $this->assertTrue(Hash::check($this->registration['password'], $user->password));
        $this->assertNotSame($this->registration['password'], $user->password);
        $cookie = collect($response->headers->getCookies())->first(fn ($cookie) => $cookie->getName() === 'tgworld_session');
        $this->assertNotNull($cookie);
        $this->assertTrue($cookie->isHttpOnly());
        $this->assertDatabaseMissing('auth_sessions', ['token_hash' => $cookie->getValue()]);
    }

    public function test_login_me_and_logout_use_a_revocable_cookie_session(): void
    {
        $this->postJson('/api/auth/register', $this->registration);
        $response = $this->postJson('/api/auth/login', ['usernameOrEmail' => 'JANE@example.com', 'password' => $this->registration['password']])->assertOk();
        $token = collect($response->headers->getCookies())->first(fn ($cookie) => $cookie->getName() === 'tgworld_session')->getValue();
        $this->call('GET', '/api/auth/me', cookies: ['tgworld_session' => $token], server: ['HTTP_ACCEPT' => 'application/json'])->assertOk()->assertJsonPath('user.username', 'Jane.Doe');
        $this->call('POST', '/api/auth/logout', cookies: ['tgworld_session' => $token], server: ['HTTP_ACCEPT' => 'application/json'])->assertNoContent();
        $this->call('GET', '/api/auth/me', cookies: ['tgworld_session' => $token], server: ['HTTP_ACCEPT' => 'application/json'])->assertUnauthorized()->assertJsonPath('error.code', 'UNAUTHENTICATED');
    }

    public function test_two_genuinely_unique_users_can_register(): void
    {
        $this->postJson('/api/auth/register', $this->registration)->assertCreated();
        $this->postJson('/api/auth/register', [
            'username' => 'john.smith',
            'email' => 'john@example.com',
            'phone' => '+255711222333',
            'password' => 'another secure passphrase',
        ])->assertCreated()->assertJsonPath('user.email', 'john@example.com');

        $this->assertDatabaseCount('users', 2);
    }

    public function test_invalid_credentials_are_generic_and_duplicates_are_field_specific(): void
    {
        $this->postJson('/api/auth/register', $this->registration)->assertCreated();
        $this->postJson('/api/auth/login', ['usernameOrEmail' => 'missing', 'password' => 'wrong password'])->assertUnauthorized()->assertJsonPath('error.code', 'INVALID_CREDENTIALS');
        $this->postJson('/api/auth/login', ['usernameOrEmail' => 'Jane.Doe', 'password' => 'wrong password'])->assertUnauthorized()->assertJsonPath('error.code', 'INVALID_CREDENTIALS');
        $this->postJson('/api/auth/register', array_merge($this->registration, [
            'username' => 'jane.doe',
            'email' => 'other@example.com',
            'phone' => '+255711222333',
        ]))
            ->assertConflict()
            ->assertJsonPath('error.code', 'ACCOUNT_EXISTS')
            ->assertJsonPath('error.fields.username.0', 'This username is already in use.')
            ->assertJsonMissingPath('error.fields.email')
            ->assertJsonMissingPath('error.fields.phone');

        $this->postJson('/api/auth/register', array_merge($this->registration, [
            'username' => 'other-user',
            'phone' => '+255711222333',
        ]))
            ->assertConflict()
            ->assertJsonPath('error.fields.email.0', 'This email is already in use.');

        $this->postJson('/api/auth/register', array_merge($this->registration, [
            'username' => 'another-user',
            'email' => 'another@example.com',
        ]))
            ->assertConflict()
            ->assertJsonPath('error.fields.phone.0', 'This phone number is already in use.');
    }

    public function test_expired_and_revoked_sessions_are_rejected(): void
    {
        $response = $this->postJson('/api/auth/register', $this->registration);
        $token = collect($response->headers->getCookies())->first(fn ($cookie) => $cookie->getName() === 'tgworld_session')->getValue();
        AuthSession::firstOrFail()->update(['expires_at' => now()->subMinute()]);
        $this->withUnencryptedCookie('tgworld_session', $token)->getJson('/api/auth/me')->assertUnauthorized();
        AuthSession::firstOrFail()->update(['expires_at' => now()->addDay(), 'revoked_at' => now()]);
        $this->withUnencryptedCookie('tgworld_session', $token)->getJson('/api/auth/me')->assertUnauthorized();
    }

    public function test_password_recovery_is_generic_single_use_and_revokes_sessions(): void
    {
        Mail::fake();
        $register = $this->postJson('/api/auth/register', $this->registration);
        $oldToken = collect($register->headers->getCookies())->first(fn ($cookie) => $cookie->getName() === 'tgworld_session')->getValue();
        $this->postJson('/api/auth/forgot-password', ['email' => 'unknown@example.com'])->assertOk();
        $this->postJson('/api/auth/forgot-password', ['email' => 'JANE@example.com'])->assertOk();
        Mail::assertSent(PasswordResetMail::class);
        $mail = Mail::sent(PasswordResetMail::class)->first();
        $this->assertDatabaseMissing('customer_password_reset_tokens', ['token_hash' => $mail->token]);
        $this->postJson('/api/auth/reset-password', ['token' => $mail->token, 'password' => 'my new secure passphrase'])->assertOk();
        $this->postJson('/api/auth/reset-password', ['token' => $mail->token, 'password' => 'another secure passphrase'])->assertUnprocessable()->assertJsonPath('error.code', 'INVALID_RESET_TOKEN');
        $this->withUnencryptedCookie('tgworld_session', $oldToken)->getJson('/api/auth/me')->assertUnauthorized();
    }

    public function test_unexpected_fields_and_untrusted_origins_are_rejected(): void
    {
        $this->postJson('/api/auth/register', $this->registration + ['role' => 'admin'])->assertUnprocessable();
        $this->withHeader('Origin', 'https://evil.example')->postJson('/api/auth/login', ['usernameOrEmail' => 'jane', 'password' => 'password'])->assertForbidden()->assertJsonPath('error.code', 'INVALID_ORIGIN');
    }

    public function test_login_is_rate_limited(): void
    {
        for ($i = 0; $i < 5; $i++) {
            $this->postJson('/api/auth/login', ['usernameOrEmail' => 'limited@example.com', 'password' => 'wrong password']);
        }
        $this->postJson('/api/auth/login', ['usernameOrEmail' => 'limited@example.com', 'password' => 'wrong password'])->assertTooManyRequests();
    }
}
