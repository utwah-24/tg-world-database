<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>New Test Drive Booking</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            font-family: 'Segoe UI', Arial, sans-serif;
            background-color: #f0f2f5;
            padding: 40px 16px;
            color: #1a1a2e;
        }
        .wrapper {
            max-width: 620px;
            margin: 0 auto;
        }
        .card {
            background: #ffffff;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 4px 24px rgba(0,0,0,0.10);
        }
        .header {
            background: #000000;
            padding: 32px 40px 28px;
            text-align: center;
        }
        .header img {
            max-width: 220px;
            height: auto;
            display: block;
            margin: 0 auto 18px;
        }
        .header .badge {
            display: inline-block;
            background: rgba(245, 166, 35, 0.15);
            color: #f5a623;
            font-size: 11px;
            font-weight: 700;
            letter-spacing: 2px;
            text-transform: uppercase;
            padding: 6px 14px;
            border-radius: 20px;
            margin-bottom: 12px;
        }
        .header h1 {
            color: #ffffff;
            font-size: 22px;
            font-weight: 700;
            letter-spacing: -0.3px;
        }
        .header p {
            color: rgba(255,255,255,0.55);
            font-size: 13px;
            margin-top: 8px;
        }
        .body {
            padding: 36px 40px;
        }
        .intro {
            font-size: 15px;
            color: #444;
            line-height: 1.7;
            margin-bottom: 28px;
        }
        .section-title {
            font-size: 12px;
            font-weight: 700;
            color: #f5a623;
            text-transform: uppercase;
            letter-spacing: 1.2px;
            margin-bottom: 12px;
        }
        .details-card {
            background: #f8f9fc;
            border: 1px solid #e8eaf0;
            border-radius: 10px;
            overflow: hidden;
            margin-bottom: 28px;
        }
        .details-card .detail-row {
            display: flex;
            align-items: center;
            padding: 14px 20px;
            border-bottom: 1px solid #e8eaf0;
        }
        .details-card .detail-row:last-child {
            border-bottom: none;
        }
        .details-card .detail-label {
            width: 130px;
            font-size: 12px;
            font-weight: 700;
            color: #888;
            text-transform: uppercase;
            letter-spacing: 0.8px;
            flex-shrink: 0;
        }
        .details-card .detail-value {
            font-size: 14px;
            font-weight: 600;
            color: #1a1a2e;
            word-break: break-word;
        }
        .details-card .detail-value a {
            color: #e8951a;
            text-decoration: none;
        }
        .detail-row .dot {
            width: 8px;
            height: 8px;
            border-radius: 50%;
            background: #f5a623;
            margin-right: 14px;
            flex-shrink: 0;
        }
        .photo-block {
            text-align: center;
            margin-bottom: 28px;
        }
        .photo-block img {
            max-width: 100%;
            max-height: 260px;
            border-radius: 10px;
            border: 1px solid #e8eaf0;
        }
        .cta-wrapper {
            text-align: center;
            margin-bottom: 8px;
        }
        .cta-btn {
            display: inline-block;
            background: linear-gradient(135deg, #f5a623 0%, #e8951a 100%);
            color: #1a1a2e;
            font-size: 15px;
            font-weight: 700;
            text-decoration: none;
            padding: 14px 36px;
            border-radius: 8px;
            letter-spacing: 0.3px;
        }
        .footer {
            background: #f8f9fc;
            border-top: 1px solid #e8eaf0;
            text-align: center;
            padding: 20px 40px;
            font-size: 12px;
            color: #aaa;
        }
        .footer strong {
            color: #888;
        }
    </style>
</head>
<body>
    <div class="wrapper">
        <div class="card">

            <div class="header">
                <img src="{{ $logoUrl }}" alt="TG World International">
                <div class="badge">New Test Drive</div>
                <h1>Test Drive Booking Received</h1>
                <p>A customer has requested a test drive on your platform</p>
            </div>

            <div class="body">
                <p class="intro">
                    Hello,<br><br>
                    A new test drive booking has just been submitted. Please review the details below and follow up with the customer.
                </p>

                <p class="section-title">Customer Details</p>
                <div class="details-card">
                    <div class="detail-row">
                        <span class="dot"></span>
                        <span class="detail-label">Name</span>
                        <span class="detail-value">{{ $testDrive->customer_name }}</span>
                    </div>
                    <div class="detail-row">
                        <span class="dot"></span>
                        <span class="detail-label">Phone</span>
                        <span class="detail-value">
                            <a href="tel:{{ $testDrive->phone }}">{{ $testDrive->phone }}</a>
                        </span>
                    </div>
                    <div class="detail-row">
                        <span class="dot"></span>
                        <span class="detail-label">Email</span>
                        <span class="detail-value">
                            <a href="mailto:{{ $testDrive->email }}">{{ $testDrive->email }}</a>
                        </span>
                    </div>
                </div>

                <p class="section-title">Booking Details</p>
                <div class="details-card">
                    <div class="detail-row">
                        <span class="dot"></span>
                        <span class="detail-label">Booking ID</span>
                        <span class="detail-value">#{{ $testDrive->id }}</span>
                    </div>
                    <div class="detail-row">
                        <span class="dot"></span>
                        <span class="detail-label">Car</span>
                        <span class="detail-value">{{ $testDrive->car_name }}</span>
                    </div>
                    <div class="detail-row">
                        <span class="dot"></span>
                        <span class="detail-label">Car ID</span>
                        <span class="detail-value">{{ $testDrive->car_id ?? '—' }}</span>
                    </div>
                    <div class="detail-row">
                        <span class="dot"></span>
                        <span class="detail-label">Year</span>
                        <span class="detail-value">{{ $testDrive->year ?? '—' }}</span>
                    </div>
                    <div class="detail-row">
                        <span class="dot"></span>
                        <span class="detail-label">Booked At</span>
                        <span class="detail-value">{{ $testDrive->booked_at?->format('d M Y, H:i') ?? '—' }}</span>
                    </div>
                    <div class="detail-row">
                        <span class="dot"></span>
                        <span class="detail-label">Submitted</span>
                        <span class="detail-value">{{ $testDrive->created_at?->format('d M Y, H:i') ?? '—' }}</span>
                    </div>
                </div>

                @if ($photoUrl)
                    <p class="section-title">Car Photo</p>
                    <div class="photo-block">
                        <img src="{{ $photoUrl }}" alt="{{ $testDrive->car_name }}">
                    </div>
                @endif

                <div class="cta-wrapper">
                    <a href="{{ $dashboardUrl }}" class="cta-btn">
                        View in Dashboard →
                    </a>
                </div>
            </div>

            <div class="footer">
                <strong>TG World International</strong> &mdash; automated test drive notification<br>
                Reply to this email to contact the customer directly.
            </div>

        </div>
    </div>
</body>
</html>
