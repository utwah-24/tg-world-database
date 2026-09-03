# Frontend authentication production fix

## Current production status

The production authentication API is available at:

```text
https://tgworld.e-saloon.online/api/auth
```

The production customer schema has been repaired. Registration, login, session lookup, and logout have been verified against the live API.

Use this frontend environment value:

```env
NEXT_PUBLIC_API_BASE_URL=https://tgworld.e-saloon.online
```

All requests must use `credentials: "include"` so the browser sends and receives the `tgworld_session` cookie.

## Registration

```ts
type RegistrationInput = {
  username: string;
  email: string;
  phone: string;
  password: string;
};

export async function register(input: RegistrationInput) {
  return authRequest<AuthResponse>("/register", {
    method: "POST",
    body: JSON.stringify(input),
  });
}
```

Send exactly these fields:

```json
{
  "username": "newuser",
  "email": "newuser@example.com",
  "phone": "+255700000000",
  "password": "a secure password"
}
```

The backend also accepts Tanzanian local phone numbers beginning with `0` and normalizes them to `+255`. Prefer sending E.164 from the frontend.

Successful registration returns `201`:

```json
{
  "user": {
    "id": 1,
    "username": "newuser",
    "email": "newuser@example.com",
    "phone": "+255700000000",
    "role": "customer",
    "createdAt": "2026-09-03T10:27:38.000000Z"
  }
}
```

Registration also establishes the `HttpOnly` session cookie. Do not store a token in browser storage.

## Duplicate fields

An actual duplicate returns `409 ACCOUNT_EXISTS`. Display errors beside only the fields returned by the API:

```json
{
  "error": {
    "code": "ACCOUNT_EXISTS",
    "message": "An account with one or more of these details already exists.",
    "fields": {
      "email": ["This email is already in use."]
    }
  }
}
```

Possible conflict keys are `username`, `email`, and `phone`.

`500 REGISTRATION_FAILED` is not a duplicate. Show its generic message and allow the user to retry.

## Login and session lookup

```ts
export async function login(usernameOrEmail: string, password: string) {
  return authRequest<AuthResponse>("/login", {
    method: "POST",
    body: JSON.stringify({ usernameOrEmail, password }),
  });
}

export async function getCurrentUser() {
  return authRequest<AuthResponse>("/me");
}
```

Call `/me` when the application initializes. Treat `401 UNAUTHENTICATED` as a normal signed-out state.

## Required logout fix

The cPanel LiteSpeed configuration rejects bodyless POST requests before they reach Laravel. Logout must send an empty JSON object:

```ts
export async function logout() {
  return authRequest<void>("/logout", {
    method: "POST",
    body: JSON.stringify({}),
  });
}
```

The shared request helper should be:

```ts
const API_BASE_URL =
  process.env.NEXT_PUBLIC_API_BASE_URL ?? "https://tgworld.e-saloon.online";

async function authRequest<T>(path: string, init: RequestInit = {}): Promise<T> {
  const response = await fetch(`${API_BASE_URL}/api/auth${path}`, {
    ...init,
    credentials: "include",
    cache: "no-store",
    headers: {
      Accept: "application/json",
      ...(init.body ? { "Content-Type": "application/json" } : {}),
      ...init.headers,
    },
  });

  if (response.status === 204) return undefined as T;

  const body = await response.json().catch(() => null);
  if (!response.ok) {
    throw {
      status: response.status,
      code: body?.error?.code ?? "REQUEST_FAILED",
      message: body?.error?.message ?? "Something went wrong. Please try again.",
      fields: body?.error?.fields ?? {},
    };
  }

  return body as T;
}
```

## Verification checklist

- Create a new account and expect `201`.
- Confirm the header immediately shows the returned username.
- Refresh the browser and confirm `/me` restores the same user.
- Log out using the `{}` request body and expect `204`.
- Confirm `/me` returns `401` after logout.
- Confirm a reused username, email, or phone produces a field-specific `409`.
- Never expose, read, or store the `tgworld_session` cookie in JavaScript.
