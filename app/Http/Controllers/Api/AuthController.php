<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Mail\PasswordResetMail;
use App\Models\AuthSession;
use App\Models\Client;
use App\Models\PasswordResetToken;
use App\Services\AuthSessionService;
use Illuminate\Database\QueryException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
    public function __construct(private AuthSessionService $sessions) {}

    public function register(Request $request): JsonResponse
    {
        if ($error = $this->rejectUnexpected($request, ['username', 'email', 'phone', 'password'])) {
            return $error;
        }

        $input = $request->all();
        $input['normalized_username'] = $this->username((string) ($input['username'] ?? ''));
        $input['normalized_email'] = $this->email((string) ($input['email'] ?? ''));
        $input['normalized_phone'] = $this->phone((string) ($input['phone'] ?? ''));
        $validator = Validator::make($input, [
            'username' => ['required', 'string', 'min:3', 'max:30', 'regex:/^[A-Za-z0-9_.-]+$/'],
            'email' => ['required', 'string', 'email:rfc', 'max:255'],
            'phone' => ['required', 'string', 'max:30'],
            'password' => ['required', 'string', 'min:10', 'max:255'],
            'normalized_username' => ['required'],
            'normalized_email' => ['required'],
            'normalized_phone' => ['required', 'regex:/^\+[1-9]\d{7,14}$/'],
        ], [
            'normalized_phone.regex' => 'Enter a valid international phone number.',
        ]);
        if ($validator->fails()) {
            return $this->validationError($validator->errors()->toArray());
        }

        if ($conflicts = $this->accountConflicts($input)) {
            return $this->accountExists($conflicts);
        }

        try {
            $user = DB::transaction(fn () => Client::create([
                'username' => trim($input['username']), 'normalized_username' => $input['normalized_username'],
                'email' => $input['normalized_email'], 'normalized_email' => $input['normalized_email'],
                'phone_number' => $input['normalized_phone'], 'normalized_phone' => $input['normalized_phone'],
                'password' => $input['password'], 'role' => 'customer',
            ]));
        } catch (QueryException $exception) {
            // A uniqueness race may occur after the preflight query. Confirm an
            // actual normalized match before classifying the error as a conflict.
            if ($conflicts = $this->accountConflicts($input)) {
                return $this->accountExists($conflicts);
            }

            Log::error('Customer registration database failure', [
                'sql_state' => $exception->errorInfo[0] ?? null,
                'driver_code' => $exception->errorInfo[1] ?? null,
                'exception' => $exception::class,
                'database_message' => $exception->getPrevious()?->getMessage(),
                'ip' => $request->ip(),
            ]);

            return $this->error('REGISTRATION_FAILED', 'The account could not be created. Please try again.', 500);
        }
        [$token, $expiresAt] = $this->sessions->create($user, $request);
        Log::info('Customer registered', ['user_id' => $user->id, 'ip' => $request->ip()]);

        return response()->json(['user' => $this->safeUser($user)], 201)->withCookie($this->sessions->cookie($token, $expiresAt));
    }

    public function login(Request $request): JsonResponse
    {
        if ($error = $this->rejectUnexpected($request, ['usernameOrEmail', 'password'])) {
            return $error;
        }
        $validator = Validator::make($request->all(), [
            'usernameOrEmail' => ['required', 'string', 'max:255'], 'password' => ['required', 'string', 'max:255'],
        ]);
        if ($validator->fails()) {
            return $this->validationError($validator->errors()->toArray());
        }

        $identifier = trim((string) $request->input('usernameOrEmail'));
        $isEmail = str_contains($identifier, '@');
        $user = Client::where($isEmail ? 'normalized_email' : 'normalized_username', $isEmail ? $this->email($identifier) : $this->username($identifier))->first();
        if (! $user || $user->disabled_at || ! Hash::check((string) $request->input('password'), $user->password)) {
            Log::warning('Customer login failed', ['ip' => $request->ip()]);

            return $this->error('INVALID_CREDENTIALS', 'The supplied credentials are invalid.', 401);
        }
        if ($old = $this->sessions->resolve($request)) {
            $old->update(['revoked_at' => now()]);
        }
        [$token, $expiresAt] = $this->sessions->create($user, $request);
        Log::info('Customer login succeeded', ['user_id' => $user->id, 'ip' => $request->ip()]);

        return response()->json(['user' => $this->safeUser($user)])->withCookie($this->sessions->cookie($token, $expiresAt));
    }

    public function me(Request $request): JsonResponse
    {
        return response()->json(['user' => $this->safeUser($request->user())]);
    }

    public function logout(Request $request)
    {
        $request->attributes->get('auth_session')->update(['revoked_at' => now()]);
        Log::info('Customer logged out', ['user_id' => $request->user()->id, 'ip' => $request->ip()]);

        return response()->noContent()->withCookie($this->sessions->forgetCookie());
    }

    public function forgotPassword(Request $request): JsonResponse
    {
        if ($error = $this->rejectUnexpected($request, ['email'])) {
            return $error;
        }
        $validator = Validator::make($request->all(), ['email' => ['required', 'string', 'email:rfc', 'max:255']]);
        if ($validator->fails()) {
            return $this->validationError($validator->errors()->toArray());
        }
        $user = Client::where('normalized_email', $this->email((string) $request->input('email')))->first();
        if ($user && ! $user->disabled_at) {
            $token = bin2hex(random_bytes(32));
            PasswordResetToken::where('user_id', $user->id)->whereNull('consumed_at')->update(['consumed_at' => now()]);
            PasswordResetToken::create(['user_id' => $user->id, 'token_hash' => hash('sha256', $token), 'expires_at' => now()->addMinutes((int) config('auth.password_reset_expire', 30))]);
            Mail::to($user->email)->send(new PasswordResetMail($token));
        }

        return response()->json(['message' => 'If an account exists for that email, a password reset link has been sent.']);
    }

    public function resetPassword(Request $request): JsonResponse
    {
        if ($error = $this->rejectUnexpected($request, ['token', 'password'])) {
            return $error;
        }
        $validator = Validator::make($request->all(), ['token' => ['required', 'string', 'size:64'], 'password' => ['required', 'string', 'min:10', 'max:255']]);
        if ($validator->fails()) {
            return $this->validationError($validator->errors()->toArray());
        }
        $user = DB::transaction(function () use ($request) {
            $reset = PasswordResetToken::where('token_hash', hash('sha256', (string) $request->input('token')))->whereNull('consumed_at')->where('expires_at', '>', now())->lockForUpdate()->first();
            if (! $reset || ! ($user = Client::find($reset->user_id)) || $user->disabled_at) {
                return null;
            }
            $reset->update(['consumed_at' => now()]);
            $user->update(['password' => (string) $request->input('password')]);
            AuthSession::where('user_id', $user->id)->whereNull('revoked_at')->update(['revoked_at' => now()]);

            return $user;
        });
        if (! $user) {
            return $this->error('INVALID_RESET_TOKEN', 'The password reset token is invalid or expired.', 422);
        }
        Log::info('Customer password reset', ['user_id' => $user->id, 'ip' => $request->ip()]);
        [$token, $expiresAt] = $this->sessions->create($user, $request);

        return response()->json(['user' => $this->safeUser($user)])->withCookie($this->sessions->cookie($token, $expiresAt));
    }

    private function username(string $value): string
    {
        return mb_strtolower(trim($value));
    }

    private function email(string $value): string
    {
        return mb_strtolower(trim($value));
    }

    private function phone(string $value): string
    {
        $value = preg_replace('/[^\d+]/', '', trim($value)) ?? '';
        if (str_starts_with($value, '00')) {
            $value = '+'.substr($value, 2);
        }
        if (str_starts_with($value, '0')) {
            $value = '+255'.substr($value, 1);
        }
        if ($value !== '' && ! str_starts_with($value, '+')) {
            $value = '+'.$value;
        }

        return $value;
    }

    private function safeUser(Client $user): array
    {
        return ['id' => $user->id, 'username' => $user->username, 'email' => $user->email, 'phone' => $user->phone_number, 'role' => $user->role, 'createdAt' => $user->created_at?->toISOString()];
    }

    private function accountConflicts(array $input): array
    {
        $matches = Client::query()
            ->where(function ($query) use ($input): void {
                $query->where('normalized_username', $input['normalized_username'])
                    ->orWhere('normalized_email', $input['normalized_email'])
                    ->orWhere('normalized_phone', $input['normalized_phone']);
            })
            ->get(['normalized_username', 'normalized_email', 'normalized_phone']);

        $fields = [];
        if ($matches->contains('normalized_username', $input['normalized_username'])) {
            $fields['username'] = ['This username is already in use.'];
        }
        if ($matches->contains('normalized_email', $input['normalized_email'])) {
            $fields['email'] = ['This email is already in use.'];
        }
        if ($matches->contains('normalized_phone', $input['normalized_phone'])) {
            $fields['phone'] = ['This phone number is already in use.'];
        }

        return $fields;
    }

    private function accountExists(array $fields): JsonResponse
    {
        return $this->error('ACCOUNT_EXISTS', 'An account with one or more of these details already exists.', 409, $fields);
    }

    private function rejectUnexpected(Request $request, array $allowed): ?JsonResponse
    {
        $unexpected = array_values(array_diff(array_keys($request->all()), $allowed));

        return $unexpected ? $this->validationError(['request' => ['Unexpected fields: '.implode(', ', $unexpected).'.']]) : null;
    }

    private function validationError(array $fields): JsonResponse
    {
        return $this->error('VALIDATION_FAILED', 'The supplied data is invalid.', 422, $fields);
    }

    private function error(string $code, string $message, int $status, array $fields = []): JsonResponse
    {
        return response()->json(['error' => ['code' => $code, 'message' => $message, 'fields' => $fields ?: (object) []]], $status);
    }
}
