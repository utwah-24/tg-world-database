<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>New quotation request</title>
</head>
<body style="margin:0;padding:0;background:#f3f4f6;color:#17191f;font-family:Arial,'Helvetica Neue',sans-serif;">
<table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="background:#f3f4f6;">
    <tr>
        <td align="center" style="padding:32px 12px;">
            <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="max-width:640px;background:#ffffff;border-radius:16px;overflow:hidden;box-shadow:0 10px 30px rgba(23,25,31,.10);">
                <tr>
                    <td align="center" style="padding:30px 28px 26px;background:#08090b;border-bottom:4px solid #ff6b00;">
                        <img src="{{ $logoUrl }}" width="180" alt="TG World International" style="display:block;width:180px;max-width:70%;height:auto;margin:0 auto 20px;">
                        <span style="display:inline-block;padding:7px 14px;border-radius:999px;background:#2a1a0e;color:#ff8a2a;font-size:11px;font-weight:700;letter-spacing:1.6px;text-transform:uppercase;">New quotation request</span>
                        <h1 style="margin:15px 0 7px;color:#ffffff;font-size:25px;line-height:1.25;">A customer has submitted an offer</h1>
                        <p style="margin:0;color:#aeb3bd;font-size:14px;line-height:1.5;">Review the request and follow up from the TG World dashboard.</p>
                    </td>
                </tr>
                <tr>
                    <td style="padding:30px 32px;">
                        <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="margin-bottom:24px;background:#fff8f2;border:1px solid #ffe0c7;border-radius:12px;">
                            <tr>
                                <td style="padding:18px 20px;">
                                    <p style="margin:0 0 5px;color:#a54b08;font-size:11px;font-weight:700;letter-spacing:1.2px;text-transform:uppercase;">Reference</p>
                                    <p style="margin:0;color:#17191f;font-size:22px;font-weight:800;">{{ $quotation->reference }}</p>
                                </td>
                                <td align="right" style="padding:18px 20px;">
                                    <span style="display:inline-block;padding:7px 12px;border-radius:999px;background:#ff6b00;color:#ffffff;font-size:12px;font-weight:700;text-transform:capitalize;">{{ $quotation->status }}</span>
                                    <p style="margin:8px 0 0;color:#777d88;font-size:12px;">{{ $quotation->created_at?->format('d M Y, H:i') }}</p>
                                </td>
                            </tr>
                        </table>

                        <p style="margin:0 0 10px;color:#ff6b00;font-size:12px;font-weight:800;letter-spacing:1.1px;text-transform:uppercase;">Customer details</p>
                        <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="margin-bottom:24px;border:1px solid #e6e8ec;border-radius:12px;">
                            <tr><td style="padding:12px 16px;color:#777d88;font-size:12px;border-bottom:1px solid #eceef1;width:120px;">Name</td><td style="padding:12px 16px;font-size:14px;font-weight:700;border-bottom:1px solid #eceef1;">{{ $quotation->full_name }}</td></tr>
                            <tr><td style="padding:12px 16px;color:#777d88;font-size:12px;border-bottom:1px solid #eceef1;">Phone</td><td style="padding:12px 16px;font-size:14px;font-weight:700;border-bottom:1px solid #eceef1;"><a href="tel:{{ $quotation->phone }}" style="color:#17191f;text-decoration:none;">{{ $quotation->phone }}</a></td></tr>
                            <tr><td style="padding:12px 16px;color:#777d88;font-size:12px;">Email</td><td style="padding:12px 16px;font-size:14px;font-weight:700;"><a href="mailto:{{ $quotation->email }}" style="color:#d95600;text-decoration:none;">{{ $quotation->email }}</a></td></tr>
                        </table>

                        @php($vehicle = $quotation->vehicle_snapshot ?? [])
                        <p style="margin:0 0 10px;color:#ff6b00;font-size:12px;font-weight:800;letter-spacing:1.1px;text-transform:uppercase;">Car details</p>
                        <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="margin-bottom:24px;border:1px solid #e6e8ec;border-radius:12px;">
                            <tr><td style="padding:12px 16px;color:#777d88;font-size:12px;border-bottom:1px solid #eceef1;width:120px;">Vehicle</td><td style="padding:12px 16px;font-size:14px;font-weight:700;border-bottom:1px solid #eceef1;">{{ $vehicle['title'] ?? 'Vehicle #'.$quotation->car_id }}</td></tr>
                            <tr><td style="padding:12px 16px;color:#777d88;font-size:12px;border-bottom:1px solid #eceef1;">Year</td><td style="padding:12px 16px;font-size:14px;font-weight:700;border-bottom:1px solid #eceef1;">{{ $vehicle['year'] ?? '—' }}</td></tr>
                            <tr><td style="padding:12px 16px;color:#777d88;font-size:12px;border-bottom:1px solid #eceef1;">Chassis</td><td style="padding:12px 16px;font-size:14px;font-weight:700;border-bottom:1px solid #eceef1;">{{ $vehicle['chassis'] ?? '—' }}</td></tr>
                            <tr><td style="padding:12px 16px;color:#777d88;font-size:12px;border-bottom:1px solid #eceef1;">Colour</td><td style="padding:12px 16px;font-size:14px;font-weight:700;border-bottom:1px solid #eceef1;">{{ $vehicle['colour'] ?? '—' }}</td></tr>
                            <tr><td style="padding:12px 16px;color:#777d88;font-size:12px;border-bottom:1px solid #eceef1;">Mileage</td><td style="padding:12px 16px;font-size:14px;font-weight:700;border-bottom:1px solid #eceef1;">{{ $vehicle['mileage'] ?? '—' }}</td></tr>
                            <tr><td style="padding:12px 16px;color:#777d88;font-size:12px;">Listed price</td><td style="padding:12px 16px;font-size:14px;font-weight:700;">{{ $vehicle['listedPrice'] ?? '—' }}</td></tr>
                        </table>

                        <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="margin-bottom:24px;background:#101216;border-radius:12px;">
                            <tr>
                                <td style="padding:20px;">
                                    <p style="margin:0 0 6px;color:#aeb3bd;font-size:11px;font-weight:700;letter-spacing:1.2px;text-transform:uppercase;">Customer's proposed price</p>
                                    <p style="margin:0;color:#ffffff;font-size:26px;font-weight:800;">{{ $quotation->currency }} {{ number_format($quotation->proposed_price) }}</p>
                                </td>
                            </tr>
                        </table>

                        @if ($quotation->delivery_address || $quotation->delivery_city || $quotation->delivery_region)
                            <p style="margin:0 0 7px;color:#777d88;font-size:12px;font-weight:700;text-transform:uppercase;">Delivery</p>
                            <p style="margin:0 0 22px;color:#30333a;font-size:14px;line-height:1.6;">{{ implode(', ', array_filter([$quotation->delivery_address, $quotation->delivery_city, $quotation->delivery_region, $quotation->delivery_postal_code])) }}</p>
                        @endif

                        @if ($quotation->customer_notes)
                            <p style="margin:0 0 7px;color:#777d88;font-size:12px;font-weight:700;text-transform:uppercase;">Customer notes</p>
                            <p style="margin:0 0 24px;padding:14px 16px;background:#f6f7f9;border-left:4px solid #ff6b00;border-radius:6px;color:#30333a;font-size:14px;line-height:1.65;">{{ $quotation->customer_notes }}</p>
                        @endif

                        <div style="text-align:center;padding-top:4px;">
                            <a href="{{ $dashboardUrl }}" style="display:inline-block;padding:14px 28px;border-radius:9px;background:#ff6b00;color:#ffffff;font-size:14px;font-weight:800;text-decoration:none;">Open Quotes Dashboard &rarr;</a>
                        </div>
                    </td>
                </tr>
                <tr>
                    <td align="center" style="padding:19px 24px;background:#f8f9fa;border-top:1px solid #e8eaed;color:#8a909a;font-size:11px;line-height:1.6;">
                        <strong style="color:#555a63;">TG World International</strong><br>
                        Automated dashboard notification. Reply to this email to contact the customer.
                    </td>
                </tr>
            </table>
        </td>
    </tr>
</table>
</body>
</html>
