<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;

class SwaggerController extends Controller
{
    public function ui()
    {
        return view('swagger');
    }

    public function spec(): JsonResponse
    {
        return response()->json([
            'openapi' => '3.0.3',
            'info' => [
                'title' => 'TG World Cars API',
                'version' => '1.1.0',
                'description' => 'API documentation for cars, orders, sold cars, logos, categories, and authentication.',
            ],
            'servers' => [
                [
                    'url' => url('/'),
                ],
            ],
            'paths' => [
                '/api/auth/register' => [
                    'post' => [
                        'tags' => ['Auth'],
                        'summary' => 'Create a new user account',
                        'requestBody' => [
                            'required' => true,
                            'content' => [
                                'application/json' => [
                                    'schema' => [
                                        '$ref' => '#/components/schemas/RegisterRequest',
                                    ],
                                ],
                            ],
                        ],
                        'responses' => [
                            '201' => [
                                'description' => 'Account created successfully',
                                'content' => [
                                    'application/json' => [
                                        'schema' => [
                                            'type' => 'object',
                                            'properties' => [
                                                'message' => ['type' => 'string', 'example' => 'Account created successfully.'],
                                                'data' => ['$ref' => '#/components/schemas/UserResponse'],
                                            ],
                                        ],
                                    ],
                                ],
                            ],
                            '422' => [
                                'description' => 'Validation error (e.g. username already taken)',
                            ],
                        ],
                    ],
                ],
                '/api/auth/login' => [
                    'post' => [
                        'tags' => ['Auth'],
                        'summary' => 'Login with username and password',
                        'requestBody' => [
                            'required' => true,
                            'content' => [
                                'application/json' => [
                                    'schema' => [
                                        '$ref' => '#/components/schemas/LoginRequest',
                                    ],
                                ],
                            ],
                        ],
                        'responses' => [
                            '200' => [
                                'description' => 'Login successful',
                                'content' => [
                                    'application/json' => [
                                        'schema' => [
                                            'type' => 'object',
                                            'properties' => [
                                                'message' => ['type' => 'string', 'example' => 'Login successful.'],
                                                'data' => ['$ref' => '#/components/schemas/LoginResponse'],
                                            ],
                                        ],
                                    ],
                                ],
                            ],
                            '401' => [
                                'description' => 'Invalid username or password',
                            ],
                            '422' => [
                                'description' => 'Validation error',
                            ],
                        ],
                    ],
                ],
                '/api/cars' => [
                    'get' => [
                        'tags' => ['Cars'],
                        'summary' => 'Get all cars',
                        'parameters' => [
                            [
                                'name' => 'category',
                                'in' => 'query',
                                'required' => false,
                                'description' => 'Filter cars by category slug (suv, trucks, or third-party).',
                                'schema' => [
                                    'type' => 'string',
                                    'enum' => ['suv', 'trucks', 'third-party'],
                                    'example' => 'third-party',
                                ],
                            ],
                        ],
                        'responses' => [
                            '200' => [
                                'description' => 'Cars list',
                                'content' => [
                                    'application/json' => [
                                        'schema' => [
                                            'type' => 'object',
                                            'properties' => [
                                                'data' => [
                                                    'type' => 'array',
                                                    'items' => [
                                                        '$ref' => '#/components/schemas/Car',
                                                    ],
                                                ],
                                            ],
                                        ],
                                    ],
                                ],
                            ],
                            '422' => [
                                'description' => 'Invalid category value',
                            ],
                        ],
                    ],
                ],
                '/api/cars/{carId}' => [
                    'get' => [
                        'tags' => ['Cars'],
                        'summary' => 'Get one car by ID',
                        'parameters' => [
                            [
                                'name' => 'carId',
                                'in' => 'path',
                                'required' => true,
                                'schema' => [
                                    'type' => 'integer',
                                ],
                            ],
                        ],
                        'responses' => [
                            '200' => [
                                'description' => 'Single car',
                                'content' => [
                                    'application/json' => [
                                        'schema' => [
                                            'type' => 'object',
                                            'properties' => [
                                                'data' => [
                                                    '$ref' => '#/components/schemas/Car',
                                                ],
                                            ],
                                        ],
                                    ],
                                ],
                            ],
                            '404' => [
                                'description' => 'Car not found',
                            ],
                        ],
                    ],
                ],
                '/api/logos' => [
                    'get' => [
                        'tags' => ['Logos'],
                        'summary' => 'Get all logos',
                        'responses' => [
                            '200' => [
                                'description' => 'Logos list',
                                'content' => [
                                    'application/json' => [
                                        'schema' => [
                                            'type' => 'object',
                                            'properties' => [
                                                'data' => [
                                                    'type' => 'array',
                                                    'items' => [
                                                        '$ref' => '#/components/schemas/Logo',
                                                    ],
                                                ],
                                            ],
                                        ],
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
                '/api/content' => [
                    'get' => [
                        'tags' => ['Content'],
                        'summary' => 'Get all content videos',
                        'responses' => [
                            '200' => [
                                'description' => 'Content list',
                                'content' => [
                                    'application/json' => [
                                        'schema' => [
                                            'type' => 'object',
                                            'properties' => [
                                                'data' => [
                                                    'type' => 'array',
                                                    'items' => [
                                                        '$ref' => '#/components/schemas/Content',
                                                    ],
                                                ],
                                            ],
                                        ],
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
                '/api/categories' => [
                    'get' => [
                        'tags' => ['Categories'],
                        'summary' => 'Get available car categories',
                        'responses' => [
                            '200' => [
                                'description' => 'Categories list',
                                'content' => [
                                    'application/json' => [
                                        'schema' => [
                                            'type' => 'object',
                                            'properties' => [
                                                'data' => [
                                                    'type' => 'array',
                                                    'items' => [
                                                        '$ref' => '#/components/schemas/Category',
                                                    ],
                                                ],
                                            ],
                                        ],
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
                '/api/companies' => [
                    'get' => [
                        'tags' => ['Car Companies'],
                        'summary' => 'Get all car companies',
                        'description' => 'Returns every company in the directory with its name and logo URL. The list is live — when a new company is added (or its logo updated) via the admin panel, it appears here immediately.',
                        'responses' => [
                            '200' => [
                                'description' => 'Companies list',
                                'content' => [
                                    'application/json' => [
                                        'schema' => [
                                            'type' => 'object',
                                            'properties' => [
                                                'data' => [
                                                    'type' => 'array',
                                                    'items' => [
                                                        '$ref' => '#/components/schemas/CarCompany',
                                                    ],
                                                ],
                                            ],
                                        ],
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
                '/api/orders' => [
                    'post' => [
                        'tags' => ['Orders'],
                        'summary' => 'Submit a new order from the website',
                        'description' => 'Accepts multipart/form-data with the car details and optional PDF files. Automatically sends a WhatsApp notification on success.',
                        'requestBody' => [
                            'required' => true,
                            'content' => [
                                'multipart/form-data' => [
                                    'schema' => [
                                        '$ref' => '#/components/schemas/OrderRequest',
                                    ],
                                ],
                            ],
                        ],
                        'responses' => [
                            '201' => [
                                'description' => 'Order submitted successfully',
                                'content' => [
                                    'application/json' => [
                                        'schema' => [
                                            'type' => 'object',
                                            'properties' => [
                                                'message' => ['type' => 'string', 'example' => 'Order submitted successfully.'],
                                                'data' => ['$ref' => '#/components/schemas/Order'],
                                            ],
                                        ],
                                    ],
                                ],
                            ],
                            '422' => [
                                'description' => 'Validation error',
                            ],
                        ],
                    ],
                    'get' => [
                        'tags' => ['Orders'],
                        'summary' => 'List all orders',
                        'responses' => [
                            '200' => [
                                'description' => 'Orders list',
                                'content' => [
                                    'application/json' => [
                                        'schema' => [
                                            'type' => 'object',
                                            'properties' => [
                                                'data' => [
                                                    'type' => 'array',
                                                    'items' => ['$ref' => '#/components/schemas/Order'],
                                                ],
                                            ],
                                        ],
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
                '/api/orders/{id}' => [
                    'get' => [
                        'tags' => ['Orders'],
                        'summary' => 'Get a single order by ID',
                        'parameters' => [
                            [
                                'name' => 'id',
                                'in' => 'path',
                                'required' => true,
                                'schema' => ['type' => 'integer'],
                                'example' => 1,
                            ],
                        ],
                        'responses' => [
                            '200' => [
                                'description' => 'Order found',
                                'content' => [
                                    'application/json' => [
                                        'schema' => [
                                            'type' => 'object',
                                            'properties' => [
                                                'data' => ['$ref' => '#/components/schemas/Order'],
                                            ],
                                        ],
                                    ],
                                ],
                            ],
                            '404' => ['description' => 'Order not found'],
                        ],
                    ],
                ],
                '/api/third-party' => [
                    'get' => [
                        'tags' => ['Third party'],
                        'summary' => 'Get all third-party cars',
                        'responses' => [
                            '200' => [
                                'description' => 'Third-party cars list',
                                'content' => [
                                    'application/json' => [
                                        'schema' => [
                                            'type' => 'object',
                                            'properties' => [
                                                'data' => [
                                                    'type' => 'array',
                                                    'items' => ['$ref' => '#/components/schemas/Car'],
                                                ],
                                            ],
                                        ],
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
                '/api/test-drives' => [
                    'post' => [
                        'tags' => ['Test Drives'],
                        'summary' => 'Book a test drive',
                        'description' => 'Submitted from the website. Accepts multipart/form-data so the car photo can be uploaded alongside the booking details.',
                        'requestBody' => [
                            'required' => true,
                            'content' => [
                                'multipart/form-data' => [
                                    'schema' => ['$ref' => '#/components/schemas/TestDriveRequest'],
                                ],
                            ],
                        ],
                        'responses' => [
                            '201' => [
                                'description' => 'Test drive booked successfully',
                                'content' => [
                                    'application/json' => [
                                        'schema' => [
                                            'type' => 'object',
                                            'properties' => [
                                                'message' => ['type' => 'string', 'example' => 'Test drive booked successfully.'],
                                                'data'    => ['$ref' => '#/components/schemas/TestDrive'],
                                            ],
                                        ],
                                    ],
                                ],
                            ],
                            '422' => ['description' => 'Validation error'],
                        ],
                    ],
                ],
                '/api/sold-cars' => [
                    'get' => [
                        'tags' => ['Sold Cars'],
                        'summary' => 'List all sold cars',
                        'description' => 'Returns every sale record ordered by sold_at descending. Each record includes a stock snapshot (total_available) and quantity (qty) at the time of the sale.',
                        'responses' => [
                            '200' => [
                                'description' => 'Sold cars list',
                                'content' => [
                                    'application/json' => [
                                        'schema' => [
                                            'type' => 'object',
                                            'properties' => [
                                                'data' => [
                                                    'type' => 'array',
                                                    'items' => ['$ref' => '#/components/schemas/SoldCar'],
                                                ],
                                            ],
                                        ],
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
                '/api/sold-cars/{id}' => [
                    'get' => [
                        'tags' => ['Sold Cars'],
                        'summary' => 'Get a single sold car record by ID',
                        'parameters' => [
                            [
                                'name' => 'id',
                                'in' => 'path',
                                'required' => true,
                                'schema' => ['type' => 'integer'],
                                'example' => 1,
                            ],
                        ],
                        'responses' => [
                            '200' => [
                                'description' => 'Sold car found',
                                'content' => [
                                    'application/json' => [
                                        'schema' => [
                                            'type' => 'object',
                                            'properties' => [
                                                'data' => ['$ref' => '#/components/schemas/SoldCar'],
                                            ],
                                        ],
                                    ],
                                ],
                            ],
                            '404' => ['description' => 'Sold car not found'],
                        ],
                    ],
                ],
            ],
            'components' => [
                'schemas' => [
                    'RegisterRequest' => [
                        'type' => 'object',
                        'required' => ['username', 'phone_number', 'password'],
                        'properties' => [
                            'username' => ['type' => 'string', 'example' => 'john_doe'],
                            'email' => ['type' => 'string', 'format' => 'email', 'nullable' => true, 'example' => 'john@example.com'],
                            'phone_number' => ['type' => 'string', 'example' => '+256700000000'],
                            'password' => ['type' => 'string', 'format' => 'password', 'minLength' => 6, 'example' => 'secret123'],
                        ],
                    ],
                    'LoginRequest' => [
                        'type' => 'object',
                        'required' => ['username', 'password'],
                        'properties' => [
                            'username' => ['type' => 'string', 'example' => 'john_doe'],
                            'password' => ['type' => 'string', 'format' => 'password', 'example' => 'secret123'],
                        ],
                    ],
                    'UserResponse' => [
                        'type' => 'object',
                        'properties' => [
                            'id' => ['type' => 'integer', 'example' => 1],
                            'username' => ['type' => 'string', 'example' => 'john_doe'],
                            'email' => ['type' => 'string', 'format' => 'email', 'nullable' => true, 'example' => 'john@example.com'],
                            'phone_number' => ['type' => 'string', 'example' => '+256700000000'],
                        ],
                    ],
                    'LoginResponse' => [
                        'type' => 'object',
                        'properties' => [
                            'id' => ['type' => 'integer', 'example' => 1],
                            'username' => ['type' => 'string', 'example' => 'john_doe'],
                            'email' => ['type' => 'string', 'format' => 'email', 'nullable' => true, 'example' => 'john@example.com'],
                            'phone_number' => ['type' => 'string', 'example' => '+256700000000'],
                            'token' => ['type' => 'string', 'example' => 'a1b2c3d4e5f6...'],
                        ],
                    ],
                    'Car' => [
                        'type' => 'object',
                        'properties' => [
                            'car_id' => ['type' => 'integer', 'example' => 1],
                            'car_name' => ['type' => 'string', 'example' => '2023 FORD RANGER WILDTRACK'],
                            'year' => ['type' => 'integer', 'nullable' => true, 'example' => 2023],
                            'car_pic' => [
                                'type' => 'array',
                                'nullable' => true,
                                'items' => ['type' => 'string'],
                                'example' => [
                                    'TGworld/SUV/2023 FORD RANGER WILDTRACK/Back.jpeg',
                                    'TGworld/SUV/2023 FORD RANGER WILDTRACK/Front.jpeg',
                                    'TGworld/SUV/2023 FORD RANGER WILDTRACK/Interior.jpeg',
                                    'TGworld/SUV/2023 FORD RANGER WILDTRACK/Side.jpeg',
                                    'TGworld/SUV/2023 FORD RANGER WILDTRACK/Engine.jpeg',
                                ],
                            ],
                            'car_price' => ['type' => 'string', 'nullable' => true, 'example' => '155Million With New Registration'],
                            'car_description' => ['type' => 'string', 'nullable' => true],
                            'notes' => ['type' => 'string', 'nullable' => true, 'description' => 'Additional notes or details about the car (supports full paragraphs)'],
                            'type' => [
                                'type' => 'string',
                                'nullable' => true,
                                'description' => 'Free-text vehicle type (DB column is VARCHAR; e.g. suv, Crossover SUV)',
                                'example' => 'Crossover SUV',
                            ],
                            'condition' => [
                                'type' => 'string',
                                'nullable' => true,
                                'enum' => ['new', 'second_hand', 'third_party'],
                                'example' => 'new',
                            ],
                            'color' => ['type' => 'string', 'nullable' => true, 'example' => 'Pearl White'],
                            'chassis' => ['type' => 'string', 'nullable' => true, 'description' => 'Chassis / VIN (free text)', 'example' => 'JTDKN3DU0E1234567'],
                            'mileage' => ['type' => 'string', 'nullable' => true, 'description' => 'Odometer / mileage (free text, e.g. km)', 'example' => '87000 km'],
                            'company_id' => ['type' => 'integer', 'nullable' => true, 'description' => 'Foreign key to companies table', 'example' => 1],
                            'company' => ['type' => 'string', 'nullable' => true, 'description' => 'Company name (canonical source: companies.name; also duplicated on cars.company_label for SQL convenience)', 'example' => 'Ford'],
                            'company_logo' => ['type' => 'string', 'nullable' => true, 'description' => 'Full URL of the company logo', 'example' => 'https://tgworld.e-saloon.online/TGworld/logos/ford.png'],
                            'company_logo_path' => ['type' => 'string', 'nullable' => true, 'description' => 'Relative path under public/ (e.g. TGworld/logos/file.svg); duplicate of companies.logo', 'example' => 'TGworld/logos/ford.svg'],
                            'brand_id' => ['type' => 'integer', 'nullable' => true, 'description' => 'Foreign key to brands table', 'example' => 1],
                            'brand' => ['type' => 'string', 'nullable' => true, 'description' => 'Brand name (from brands; duplicated on cars.brand_label)', 'example' => 'X series'],
                            'model_id' => ['type' => 'integer', 'nullable' => true, 'description' => 'Foreign key to vehicle_models table', 'example' => 1],
                            'model' => ['type' => 'string', 'nullable' => true, 'description' => 'Vehicle model name (from vehicle_models; duplicated on cars.model_label)', 'example' => 'Prado TXL'],
                            'is_coming_soon' => ['type' => 'string', 'nullable' => true, 'enum' => ['set', null], 'example' => 'set'],
                            'arrival_date' => ['type' => 'string', 'format' => 'date', 'nullable' => true, 'example' => '2026-04-15'],
                            'is_sold' => ['type' => 'string', 'enum' => ['available', 'sold'], 'example' => 'available'],
                            'registration' => ['type' => 'string', 'enum' => ['registered', 'unregistered'], 'example' => 'unregistered'],
                            'registration_number' => ['type' => 'string', 'nullable' => true, 'example' => 'T 123 ABC'],
                            'in_dar' => ['type' => 'boolean', 'description' => 'True = car is in Dar es Salaam. False = see location field.', 'example' => true],
                            'location' => ['type' => 'string', 'nullable' => true, 'description' => 'Custom location when in_dar is false', 'example' => 'Arusha'],
                            'total_available' => ['type' => 'integer', 'description' => 'Number of units currently available for this car', 'example' => 3],
                            'category' => ['type' => 'string', 'nullable' => true, 'example' => 'Third party'],
                            'created_at' => ['type' => 'string', 'format' => 'date-time', 'nullable' => true],
                            'updated_at' => ['type' => 'string', 'format' => 'date-time', 'nullable' => true],
                        ],
                    ],
                    'Category' => [
                        'type' => 'object',
                        'properties' => [
                            'name' => ['type' => 'string', 'example' => 'SUV'],
                            'slug' => ['type' => 'string', 'example' => 'suv'],
                        ],
                    ],
                    'CarCompany' => [
                        'type' => 'object',
                        'properties' => [
                            'company_id'        => ['type' => 'integer', 'example' => 1],
                            'company_label'     => ['type' => 'string', 'example' => 'Toyota'],
                            'logo'              => ['type' => 'string', 'nullable' => true, 'description' => 'Relative storage path of the logo (e.g. TGworld/logos/toyota.svg)', 'example' => 'TGworld/logos/toyota.svg'],
                            'company_logo_path' => ['type' => 'string', 'nullable' => true, 'description' => 'Fully-resolved absolute URL of the company logo', 'example' => 'https://tgworld.e-saloon.online/public/TGworld/logos/toyota.svg'],
                        ],
                    ],
                    'Logo' => [
                        'type' => 'object',
                        'properties' => [
                            'id' => ['type' => 'integer', 'example' => 1],
                            'name' => ['type' => 'string', 'example' => 'logo-dark'],
                            'path' => ['type' => 'string', 'example' => 'logo-dark.jpeg'],
                            'created_at' => ['type' => 'string', 'format' => 'date-time', 'nullable' => true],
                            'updated_at' => ['type' => 'string', 'format' => 'date-time', 'nullable' => true],
                        ],
                    ],
                    'OrderRequest' => [
                        'type' => 'object',
                        'required' => ['car_name'],
                        'properties' => [
                            'car_name'     => ['type' => 'string', 'example' => 'LANDCRUISER ZX'],
                            'email'        => ['type' => 'string', 'format' => 'email', 'nullable' => true, 'example' => 'customer@example.com'],
                            'year'         => ['type' => 'string', 'nullable' => true, 'example' => '2024'],
                            'amount_paid'  => ['type' => 'number', 'format' => 'float', 'nullable' => true, 'example' => 5000000],
                            'amount_due'   => ['type' => 'number', 'format' => 'float', 'nullable' => true, 'example' => 150000000],
                            'total_amount' => ['type' => 'number', 'format' => 'float', 'nullable' => true, 'example' => 155000000],
                            'invoice'      => ['type' => 'string', 'format' => 'binary', 'nullable' => true, 'description' => 'Invoice PDF file (max 20 MB)'],
                            'receipt'      => ['type' => 'string', 'format' => 'binary', 'nullable' => true, 'description' => 'Receipt PDF file (max 20 MB)'],
                        ],
                    ],
                    'Order' => [
                        'type' => 'object',
                        'properties' => [
                            'id'              => ['type' => 'integer', 'example' => 1],
                            'order_date'      => ['type' => 'string', 'format' => 'date', 'example' => '2026-04-23'],
                            'email'           => ['type' => 'string', 'format' => 'email', 'nullable' => true, 'example' => 'customer@example.com'],
                            'car_name'        => ['type' => 'string', 'example' => 'LANDCRUISER ZX'],
                            'car_id'          => ['type' => 'integer', 'nullable' => true, 'description' => 'References cars.car_id', 'example' => 12],
                            'car_pics'        => ['type' => 'array', 'nullable' => true, 'items' => ['type' => 'string'], 'example' => ['TGworld/SUV/LANDCRUISER ZX/Front.jpeg']],
                            'year'            => ['type' => 'string', 'nullable' => true, 'example' => '2024'],
                            'invoice'         => ['type' => 'string', 'format' => 'uri', 'nullable' => true, 'description' => 'Full URL to the invoice PDF'],
                            'receipt'         => ['type' => 'string', 'format' => 'uri', 'nullable' => true, 'description' => 'Full URL to the receipt PDF'],
                            'amount_paid'     => ['type' => 'number', 'format' => 'float', 'nullable' => true, 'example' => 5000000],
                            'amount_due'      => ['type' => 'number', 'format' => 'float', 'nullable' => true, 'example' => 150000000],
                            'total_amount'    => ['type' => 'number', 'format' => 'float', 'nullable' => true, 'example' => 155000000],
                            'total_available' => ['type' => 'integer', 'nullable' => true, 'description' => 'Remaining stock after this order was placed', 'example' => 2],
                            'qty'             => ['type' => 'integer', 'description' => 'Number of units bought in this order', 'example' => 1],
                            'status'          => ['type' => 'boolean', 'description' => 'True = approved, false = pending', 'example' => false],
                            'created_at'      => ['type' => 'string', 'format' => 'date-time', 'nullable' => true],
                            'updated_at'      => ['type' => 'string', 'format' => 'date-time', 'nullable' => true],
                        ],
                    ],
                    'SoldCar' => [
                        'type' => 'object',
                        'properties' => [
                            'id'              => ['type' => 'integer', 'example' => 1],
                            'order_id'        => ['type' => 'integer', 'nullable' => true, 'description' => 'The order that triggered this sale', 'example' => 5],
                            'car_id'          => ['type' => 'integer', 'nullable' => true, 'description' => 'References cars.car_id', 'example' => 12],
                            'car_name'        => ['type' => 'string', 'example' => 'LANDCRUISER ZX'],
                            'car_pics'        => ['type' => 'array', 'nullable' => true, 'items' => ['type' => 'string'], 'example' => ['TGworld/SUV/LANDCRUISER ZX/Front.jpeg']],
                            'sold_at'         => ['type' => 'string', 'format' => 'date-time', 'nullable' => true, 'example' => '2026-05-01T10:00:00+03:00'],
                            'price_sold'      => ['type' => 'string', 'nullable' => true, 'example' => '155 000 Tshs'],
                            'total_available' => ['type' => 'integer', 'nullable' => true, 'description' => 'Remaining stock at the time this sale was recorded', 'example' => 1],
                            'qty'             => ['type' => 'integer', 'description' => 'Number of units in this sale', 'example' => 1],
                            'created_at'      => ['type' => 'string', 'format' => 'date-time', 'nullable' => true],
                            'updated_at'      => ['type' => 'string', 'format' => 'date-time', 'nullable' => true],
                        ],
                    ],
                    'TestDriveRequest' => [
                        'type' => 'object',
                        'required' => ['car_name', 'booked_at'],
                        'properties' => [
                            'car_name'  => ['type' => 'string', 'example' => 'LANDCRUISER ZX'],
                            'car_id'    => ['type' => 'integer', 'nullable' => true, 'description' => 'Optional — auto-resolved from car_name if omitted', 'example' => 12],
                            'year'      => ['type' => 'string', 'nullable' => true, 'example' => '2024'],
                            'photo'     => ['type' => 'string', 'format' => 'binary', 'nullable' => true, 'description' => 'Car photo (JPEG/PNG/WebP, max 10 MB)'],
                            'booked_at' => ['type' => 'string', 'format' => 'date-time', 'example' => '2026-06-15T10:00:00'],
                        ],
                    ],
                    'TestDrive' => [
                        'type' => 'object',
                        'properties' => [
                            'id'         => ['type' => 'integer', 'example' => 1],
                            'car_id'     => ['type' => 'integer', 'nullable' => true, 'example' => 12],
                            'car_name'   => ['type' => 'string', 'example' => 'LANDCRUISER ZX'],
                            'year'       => ['type' => 'string', 'nullable' => true, 'example' => '2024'],
                            'photo'      => ['type' => 'string', 'format' => 'uri', 'nullable' => true, 'description' => 'Full URL to the car photo'],
                            'booked_at'  => ['type' => 'string', 'format' => 'date-time', 'example' => '2026-06-15T10:00:00+03:00'],
                            'created_at' => ['type' => 'string', 'format' => 'date-time', 'nullable' => true],
                            'updated_at' => ['type' => 'string', 'format' => 'date-time', 'nullable' => true],
                        ],
                    ],
                    'Content' => [
                        'type' => 'object',
                        'properties' => [
                            'contentID' => ['type' => 'integer', 'example' => 1],
                            'content_name' => ['type' => 'string', 'example' => 'launch teaser'],
                            'content_video' => ['type' => 'string', 'example' => 'TGworld/content/launch-teaser.mp4'],
                            'duration' => ['type' => 'string', 'nullable' => true, 'example' => '00:01:30'],
                            'created_at' => ['type' => 'string', 'format' => 'date-time', 'nullable' => true],
                            'updated_at' => ['type' => 'string', 'format' => 'date-time', 'nullable' => true],
                        ],
                    ],
                ],
            ],
        ]);
    }
}
