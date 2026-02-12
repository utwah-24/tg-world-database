<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $car->car_name }}</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 24px; background: #f7f7f7; color: #222; }
        .card { max-width: 900px; margin: 0 auto; background: #fff; border-radius: 10px; padding: 20px; box-shadow: 0 2px 10px rgba(0,0,0,.08); }
        img { width: 100%; max-height: 520px; object-fit: contain; background: #111; border-radius: 8px; }
        .meta { margin: 14px 0; font-size: 18px; }
        .desc { white-space: pre-wrap; line-height: 1.5; }
        a { color: #0b57d0; text-decoration: none; }
    </style>
</head>
<body>
<div class="card">
    <h1>{{ $car->car_name }}</h1>

    @if($car->car_pic)
        <img src="{{ asset($car->car_pic) }}" alt="{{ $car->car_name }}">
    @else
        <p>No image found for this car.</p>
    @endif

    <p class="meta"><strong>Price:</strong> {{ $car->car_price ?? 'N/A' }}</p>
    <p class="desc">{{ $car->car_description ?? 'No description available.' }}</p>

    <p><a href="/api/cars/{{ $car->car_id }}">View JSON API</a></p>
</div>
</body>
</html>
