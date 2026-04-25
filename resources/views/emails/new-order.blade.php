<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>New Order Notification</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            margin: 0;
            padding: 30px 0;
            color: #333;
        }
        .wrapper {
            max-width: 560px;
            margin: 0 auto;
            background: #ffffff;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
        }
        .header {
            background-color: #1a1a2e;
            padding: 28px 32px;
            text-align: center;
        }
        .header h1 {
            color: #ffffff;
            margin: 0;
            font-size: 20px;
            letter-spacing: 0.5px;
        }
        .body {
            padding: 32px;
        }
        .body p {
            font-size: 15px;
            line-height: 1.6;
            margin-top: 0;
        }
        .details {
            background: #f8f8f8;
            border-left: 4px solid #1a1a2e;
            border-radius: 4px;
            padding: 16px 20px;
            margin: 24px 0;
        }
        .details table {
            width: 100%;
            border-collapse: collapse;
        }
        .details td {
            padding: 6px 0;
            font-size: 14px;
        }
        .details td:first-child {
            color: #777;
            width: 100px;
            font-weight: 600;
        }
        .footer {
            text-align: center;
            padding: 20px 32px;
            font-size: 12px;
            color: #aaa;
            border-top: 1px solid #eee;
        }
    </style>
</head>
<body>
    <div class="wrapper">
        <div class="header">
            <h1>TG World — New Order</h1>
        </div>

        <div class="body">
            <p>Hello,</p>
            <p>You have gotten a new order. Here are the details:</p>

            <div class="details">
                <table>
                    <tr>
                        <td>Order ID</td>
                        <td>#{{ $order->id }}</td>
                    </tr>
                    <tr>
                        <td>Date</td>
                        <td>{{ $order->order_date->format('d M Y') }}</td>
                    </tr>
                    <tr>
                        <td>Car</td>
                        <td>{{ $order->car_name }}</td>
                    </tr>
                    <tr>
                        <td>Year</td>
                        <td>{{ $order->year ?? '—' }}</td>
                    </tr>
                </table>
            </div>

            <p>Log in to your dashboard to view the full order.</p>
        </div>

        <div class="footer">
            TG World Database &mdash; automated notification
        </div>
    </div>
</body>
</html>
