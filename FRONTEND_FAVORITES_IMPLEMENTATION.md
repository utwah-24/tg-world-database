# Frontend favorites implementation

The backend is the source of truth for an authenticated customer's favorites. Do not store favorites only in `localStorage`, and never send a user ID from the browser.

## API contract

All requests must include the existing session cookie:

```ts
const response = await fetch(`${API_BASE_URL}/api/favorites`, {
  credentials: "include",
  headers: { Accept: "application/json" },
});
```

Endpoints:

| Action | Request | Success |
| --- | --- | --- |
| Load favorites | `GET /api/favorites` | `200 { data: [{ carId, createdAt }] }` |
| Add favorite | `POST /api/favorites` with `{ "carId": 289 }` | `201 { favorite: { carId, createdAt } }` when created; `200` if already saved |
| Remove favorite | `DELETE /api/favorites/289` with an empty JSON body `{}` | `204` |
| Removal fallback | `POST /api/favorites/289/remove` with `{}` | `204` |

Use the POST fallback if the production host rejects `DELETE`. Both removal routes are idempotent.

## Recommended client flow

1. After `/api/auth/me` confirms a session, load `GET /api/favorites` once and create a `Set<number>` from `data[].carId`.
2. Render the heart as selected when that set contains the car's canonical numeric `car_id`.
3. Optimistically update the set when clicked, disable repeated clicks while the mutation is pending, and roll back if the request fails.
4. Clear the favorites set on logout. On the next login, load it again from the API.
5. Treat `401 UNAUTHENTICATED` as an expired session. Preserve the intended page and send the customer to sign in.

Example mutation helpers:

```ts
export async function addFavorite(carId: number) {
  return apiFetch("/api/favorites", {
    method: "POST",
    credentials: "include",
    headers: { "Content-Type": "application/json", Accept: "application/json" },
    body: JSON.stringify({ carId }),
  });
}

export async function removeFavorite(carId: number) {
  return apiFetch(`/api/favorites/${carId}/remove`, {
    method: "POST",
    credentials: "include",
    headers: { "Content-Type": "application/json", Accept: "application/json" },
    body: JSON.stringify({}),
  });
}
```

## Error handling

Errors use `{ error: { code, message, fields } }`.

- `401 UNAUTHENTICATED`: clear session/favorites state and prompt for sign-in.
- `403 INVALID_ORIGIN`: frontend/API origin configuration does not match.
- `404 CAR_NOT_FOUND`: remove the stale car from local UI state.
- `422 VALIDATION_FAILED`: show `error.fields.carId` when present.
- `429 RATE_LIMITED`: temporarily disable the action and show a retry message.
- `500 FAVORITE_SAVE_FAILED` or `FAVORITE_DELETE_FAILED`: restore the previous optimistic state and show a generic retry message.

Do not send `user_id`, `userId`, email, or username. Ownership always comes from the authenticated HTTP-only session cookie.
