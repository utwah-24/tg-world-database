# Frontend quotation implementation

Quotation requests are authenticated with the existing HTTP-only `tgworld_session` cookie. The frontend must never send a customer/user ID, account role, quotation status, staff notes, or vehicle details.

## Create and open the PDF preview

Send the form to the same-origin Next.js gateway:

```ts
type CreateQuotationResponse = {
  quotation: {
    id: number;
    reference: string;
    status: string;
    carId: number;
    proposedPrice: number;
    counterPrice: number | null;
    currency: "TZS";
    previewPdfUrl: string;
    createdAt: string;
  };
};

const response = await fetch("/api/quotations", {
  method: "POST",
  credentials: "include",
  headers: { "Content-Type": "application/json", Accept: "application/json" },
  body: JSON.stringify({
    carId: Number(carId),
    proposedPrice: Number(proposedPrice),
    currency: "TZS",
    buyer: { fullName, email, phone },
    delivery: { address, city, region, postalCode },
    notes,
  }),
});

const { quotation } = (await response.json()) as CreateQuotationResponse;
window.open(quotation.previewPdfUrl, "_blank", "noopener,noreferrer");
```

The POST returns JSON with the saved quotation and protected PDF URL. Fetching/opening `previewPdfUrl` returns `application/pdf`; it is only available to the quotation owner. Ensure the Next.js catch-all proxy forwards `GET`, `POST`, cookies, JSON bodies, and PDF response bodies for `/api/quotations/*`.

## Customer profile endpoints

| Purpose | Request |
| --- | --- |
| Create | `POST /api/quotations` |
| Paginated list | `GET /api/quotations?page=1` |
| Details | `GET /api/quotations/{id}` |
| PDF preview | `GET /api/quotations/{id}/preview` |
| Withdraw | `POST /api/quotations/{id}/withdraw` with `{}` |

Every request uses `credentials: "include"`. List responses have `{ data, meta }`. Detail and mutation responses have `{ quotation }`.

## UI behavior

- Add a Quotations/Quotes tab beside Purchases and Favorites.
- Show reference, vehicle snapshot, offer, counter-offer, status, submission date, and a PDF button.
- Load live inventory separately when current price/availability is needed; the quotation vehicle object is an immutable submission snapshot.
- Allow withdrawal only for `pending`, `reviewing`, or `countered` requests.
- Disable the submit button while pending. A rapid duplicate returns `409 DUPLICATE_QUOTATION`.
- On `401 UNAUTHENTICATED`, clear account state and redirect to sign-in.
- Render field messages from `error.fields` for `422 VALIDATION_FAILED`.
- Show a specific unavailable message for `409 CAR_NOT_AVAILABLE`.
- For a `500`, display the returned `error.requestId` so support can locate the server log safely.

## PDF handling through a Next.js proxy

Do not parse the preview response as JSON. Stream the upstream body and preserve at least these headers:

```text
Content-Type: application/pdf
Content-Disposition: inline; filename="QT-....pdf"
```

The PDF is a quotation-request preview, not an invoice or final binding offer.
