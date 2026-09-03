# Frontend authentication implementation

## Backend API

The authentication API is available under:

```text
${NEXT_PUBLIC_API_BASE_URL}/api/auth
```

Recommended environment values:

```env
# Local frontend
NEXT_PUBLIC_API_BASE_URL=http://localhost:8000

# Production frontend
NEXT_PUBLIC_API_BASE_URL=https://tgworld.e-saloon.online
```

Authentication uses the `tgworld_session` cookie. The browser must manage this cookie; do not read it from JavaScript or store credentials in `localStorage` or `sessionStorage`.

Every authentication request must use:

```ts
credentials: "include"
```

## API response types

```ts
export type AuthUser = {
  id: number;
  username: string;
  email: string;
  phone: string;
  role: "customer" | "admin";
  createdAt: string | null;
};

export type AuthResponse = {
  user: AuthUser;
};

export type ApiError = {
  error: {
    code: string;
    message: string;
    fields: Record<string, string[]>;
  };
};
```

## Authentication API client

Create a small client such as `lib/auth-api.ts`:

```ts
const API_BASE_URL =
  process.env.NEXT_PUBLIC_API_BASE_URL ?? "https://tgworld.e-saloon.online";

export class AuthApiError extends Error {
  constructor(
    public code: string,
    message: string,
    public fields: Record<string, string[]> = {},
    public status: number,
  ) {
    super(message);
  }
}

async function authRequest<T>(path: string, init: RequestInit = {}): Promise<T> {
  const response = await fetch(`${API_BASE_URL}/api/auth${path}`, {
    ...init,
    credentials: "include",
    headers: {
      Accept: "application/json",
      ...(init.body ? { "Content-Type": "application/json" } : {}),
      ...init.headers,
    },
    cache: "no-store",
  });

  if (response.status === 204) {
    return undefined as T;
  }

  const body = await response.json().catch(() => null);

  if (!response.ok) {
    throw new AuthApiError(
      body?.error?.code ?? "REQUEST_FAILED",
      body?.error?.message ?? "Something went wrong. Please try again.",
      body?.error?.fields ?? {},
      response.status,
    );
  }

  return body as T;
}

export const authApi = {
  register(input: {
    username: string;
    email: string;
    phone: string;
    password: string;
  }) {
    return authRequest<AuthResponse>("/register", {
      method: "POST",
      body: JSON.stringify(input),
    });
  },

  login(input: { usernameOrEmail: string; password: string }) {
    return authRequest<AuthResponse>("/login", {
      method: "POST",
      body: JSON.stringify(input),
    });
  },

  me() {
    return authRequest<AuthResponse>("/me");
  },

  logout() {
    // This host's LiteSpeed rules reject bodyless POST requests.
    return authRequest<void>("/logout", {
      method: "POST",
      body: JSON.stringify({}),
    });
  },

  forgotPassword(email: string) {
    return authRequest<{ message: string }>("/forgot-password", {
      method: "POST",
      body: JSON.stringify({ email }),
    });
  },

  resetPassword(token: string, password: string) {
    return authRequest<AuthResponse>("/reset-password", {
      method: "POST",
      body: JSON.stringify({ token, password }),
    });
  },
};
```

## Endpoint contract

### Register

```http
POST /api/auth/register
```

```json
{
  "username": "jane",
  "email": "jane@example.com",
  "phone": "+255700000000",
  "password": "a secure passphrase"
}
```

Success returns `201 Created`, the safe user object, and establishes the session cookie.

Validation rules:

- Username: 3–30 characters; letters, numbers, `_`, `.`, and `-`
- Email: required and valid
- Phone: required; send E.164 where possible
- Password: 10–255 characters

### Login

```http
POST /api/auth/login
```

```json
{
  "usernameOrEmail": "jane@example.com",
  "password": "a secure passphrase"
}
```

Success returns `200 OK`, the user, and a fresh session cookie. Invalid existing account and incorrect-password failures both return:

```json
{
  "error": {
    "code": "INVALID_CREDENTIALS",
    "message": "The supplied credentials are invalid.",
    "fields": {}
  }
}
```

### Current user

```http
GET /api/auth/me
```

Returns `200 OK` with the user when authenticated. A missing, expired, revoked, or disabled session returns `401` with code `UNAUTHENTICATED`.

### Logout

```http
POST /api/auth/logout
```

Returns `204 No Content`, revokes the session, and clears the cookie.

### Forgot password

```http
POST /api/auth/forgot-password
```

```json
{
  "email": "jane@example.com"
}
```

Always show the returned generic success message. Do not tell the user whether an account exists.

### Reset password

```http
POST /api/auth/reset-password
```

```json
{
  "token": "token-from-reset-link",
  "password": "a new secure passphrase"
}
```

Success signs the user in with a new session. Invalid, expired, or previously used tokens return code `INVALID_RESET_TOKEN`.

## Sign-in page work

Update `/signin` so that:

1. The login form submits `usernameOrEmail` and `password` to `authApi.login`.
2. The registration form submits exactly `username`, `email`, `phone`, and `password` to `authApi.register`.
3. Submit buttons are disabled while their request is pending.
4. Field messages from `error.fields` are displayed beside the corresponding inputs.
5. `INVALID_CREDENTIALS` appears as a form-level message.
6. `RATE_LIMITED` asks the user to wait before trying again.
7. Network failures display a retryable generic message.
8. Password inputs are cleared after failed authentication.
9. Successful login or registration updates shared account state and redirects safely.

Do not send `role`, `phone_number`, or any other extra field. The backend rejects unexpected fields.

## Safe redirects

Only allow same-origin relative paths. Never pass an arbitrary URL directly to `router.push`.

```ts
export function safeRedirect(value: string | null, fallback = "/") {
  if (!value || !value.startsWith("/") || value.startsWith("//")) {
    return fallback;
  }

  return value;
}
```

After authentication:

```ts
router.replace(safeRedirect(searchParams.get("next"), "/"));
router.refresh();
```

## Shared authentication state

Create an auth provider or equivalent store that:

- Calls `authApi.me()` when the application initializes
- Stores `user`, `isLoading`, and authentication status
- Treats a `401` response as signed out
- Revalidates after login, registration, password reset, and logout
- Exposes `logout()` to the desktop and mobile headers

Suggested state shape:

```ts
type AuthState = {
  user: AuthUser | null;
  isLoading: boolean;
  isAuthenticated: boolean;
};
```

While `/me` is loading, render a stable placeholder rather than briefly showing signed-out controls.

## Header behavior

- Signed out: show the existing sign-in link.
- Signed in: show the username/account link and a logout action.
- Apply the same behavior to desktop and mobile navigation.
- On logout, clear client-side user state and navigate away from private pages.

## Password recovery pages

Create:

```text
/forgot-password
/reset-password?token=...
```

The forgot-password page contains an email field and always displays the generic success message after a successful request.

The reset-password page reads the token from the query string, asks for a password and confirmation, verifies that both password fields match in the browser, then sends only `token` and `password` to the API.

Do not log, persist, or send the reset token to analytics.

## Protecting private pages

Client-side route guards improve navigation but are not authorization. Protected data APIs must also reject unauthenticated requests.

Because the API cookie belongs to the API domain, a Next.js server hosted on a different domain may not receive it. For that deployment shape:

- Use client-side `/me` checks for account UI.
- Keep sensitive authorization enforcement in the Laravel API.
- Do not assume Next.js middleware can inspect the API-domain cookie.

If frontend and API are later placed under the same parent domain, server-side page checks can forward the incoming cookie to `/api/auth/me`.

## Error handling reference

| Code | Status | Frontend behavior |
|---|---:|---|
| `VALIDATION_FAILED` | 422 | Show field and form errors |
| `ACCOUNT_EXISTS` | 409 | Explain that the supplied details are already registered |
| `INVALID_CREDENTIALS` | 401 | Show a generic login error |
| `UNAUTHENTICATED` | 401 | Clear account state and show signed-out UI |
| `INVALID_RESET_TOKEN` | 422 | Ask the user to request a new reset link |
| `INVALID_ORIGIN` | 403 | Show a generic request error and verify environment configuration |
| `RATE_LIMITED` | 429 | Disable immediate retry and ask the user to wait |
| `REQUEST_TOO_LARGE` | 413 | Reject the submission and show a generic form error |

## Production requirements

The backend production environment must contain:

```env
FRONTEND_URL=https://tgworld.netlify.app
SESSION_COOKIE=tgworld_session
SESSION_SECURE_COOKIE=true
SESSION_SAME_SITE=none
AUTH_SESSION_LIFETIME=10080
AUTH_PASSWORD_RESET_EXPIRE=30
```

The frontend origin must exactly match `FRONTEND_URL`, including the scheme and without an unexpected subdomain. Both sites must use HTTPS.

## Acceptance checklist

- Registration creates an account and immediately shows signed-in UI.
- Login works with either username or email.
- Refreshing the browser preserves the authenticated state through `/me`.
- JavaScript cannot read `tgworld_session`.
- Logout clears the UI state and `/me` subsequently returns `401`.
- Duplicate username, email, and phone errors render correctly.
- Invalid credentials do not reveal whether an account exists.
- Forgot-password always shows the same success response.
- A reset link works once, signs the user in, and cannot be reused.
- Desktop and mobile headers reflect the same account state.
- Private API data remains inaccessible when signed out.
- Local and production origins both work with credentialed requests.
