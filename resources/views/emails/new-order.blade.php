<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>New Order Notification</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            font-family: 'Segoe UI', Arial, sans-serif;
            background-color: #f0f2f5;
            padding: 40px 16px;
            color: #1a1a2e;
        }
        .wrapper {
            max-width: 580px;
            margin: 0 auto;
        }
        .card {
            background: #ffffff;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 4px 24px rgba(0,0,0,0.10);
        }
        .header {
            background: linear-gradient(135deg, #1a1a2e 0%, #16213e 100%);
            padding: 36px 40px;
            text-align: center;
        }
        .header .badge {
            display: inline-block;
            background: rgba(255,255,255,0.12);
            color: #f5a623;
            font-size: 11px;
            font-weight: 700;
            letter-spacing: 2px;
            text-transform: uppercase;
            padding: 6px 14px;
            border-radius: 20px;
            margin-bottom: 16px;
        }
        .header h1 {
            color: #ffffff;
            font-size: 26px;
            font-weight: 700;
            letter-spacing: -0.5px;
        }
        .header p {
            color: rgba(255,255,255,0.55);
            font-size: 13px;
            margin-top: 6px;
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
        .details-card {
            background: #f8f9fc;
            border: 1px solid #e8eaf0;
            border-radius: 10px;
            overflow: hidden;
            margin-bottom: 32px;
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
            width: 110px;
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
        }
        .detail-row .dot {
            width: 8px;
            height: 8px;
            border-radius: 50%;
            background: #f5a623;
            margin-right: 14px;
            flex-shrink: 0;
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
                <div class="badge">New Order</div>
                <h1>TG World</h1>
                <p>A new order has been placed on your platform</p>
            </div>

            <div class="body">
                <p class="intro">
                    Hello,<br><br>
                    A new order has just been placed. Here are the full details:
                </p>

                <div class="details-card">
                    <div class="detail-row">
                        <span class="dot"></span>
                        <span class="detail-label">Order ID</span>
                        <span class="detail-value">#{{ $order->id }}</span>
                    </div>
                    <div class="detail-row">
                        <span class="dot"></span>
                        <span class="detail-label">Date</span>
                        <span class="detail-value">{{ $order->order_date->format('d M Y') }}</span>
                    </div>
                    <div class="detail-row">
                        <span class="dot"></span>
                        <span class="detail-label">Email</span>
                        <span class="detail-value">{{ $order->email ?? '—' }}</span>
                    </div>
                    <div class="detail-row">
                        <span class="dot"></span>
                        <span class="detail-label">Car</span>
                        <span class="detail-value">{{ $order->car_name }}</span>
                    </div>
                    <div class="detail-row">
                        <span class="dot"></span>
                        <span class="detail-label">Year</span>
                        <span class="detail-value">{{ $order->year ?? '—' }}</span>
                    </div>
                </div>

                <div class="cta-wrapper">
                    <a href="https://tgworld.e-saloon.online/admin/orders" class="cta-btn">
                        View in Dashboard →
                    </a>
                </div>
            </div>

            <div class="footer">
                <strong>TG World Database</strong> &mdash; automated order notification<br>
                Do not reply directly to this email.
            </div>

        </div>
    </div>
</body>
</html>
