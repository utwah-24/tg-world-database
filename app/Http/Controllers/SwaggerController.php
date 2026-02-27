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
                'version' => '1.0.0',
                'description' => 'API documentation for cars, logos, category filtering, and user authentication.',
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
                                                    'items' => [
                                                        '$ref' => '#/components/schemas/Car',
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
            ],
            'components' => [
                'schemas' => [
                    'RegisterRequest' => [
                        'type' => 'object',
                        'required' => ['username', 'phone_number', 'password'],
                        'properties' => [
                            'username'     => ['type' => 'string', 'example' => 'john_doe'],
                            'email'        => ['type' => 'string', 'format' => 'email', 'nullable' => true, 'example' => 'john@example.com'],
                            'phone_number' => ['type' => 'string', 'example' => '+256700000000'],
                            'password'     => ['type' => 'string', 'format' => 'password', 'minLength' => 6, 'example' => 'secret123'],
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
                            'id'           => ['type' => 'integer', 'example' => 1],
                            'username'     => ['type' => 'string', 'example' => 'john_doe'],
                            'email'        => ['type' => 'string', 'format' => 'email', 'nullable' => true, 'example' => 'john@example.com'],
                            'phone_number' => ['type' => 'string', 'example' => '+256700000000'],
                        ],
                    ],
                    'LoginResponse' => [
                        'type' => 'object',
                        'properties' => [
                            'id'           => ['type' => 'integer', 'example' => 1],
                            'username'     => ['type' => 'string', 'example' => 'john_doe'],
                            'email'        => ['type' => 'string', 'format' => 'email', 'nullable' => true, 'example' => 'john@example.com'],
                            'phone_number' => ['type' => 'string', 'example' => '+256700000000'],
                            'token'        => ['type' => 'string', 'example' => 'a1b2c3d4e5f6...'],
                        ],
                    ],
                    'Car' => [
                        'type' => 'object',
                        'properties' => [
                            'car_id' => ['type' => 'integer', 'example' => 1],
                            'car_name' => ['type' => 'string', 'example' => '2023 FORD RANGER WILDTRACK'],
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
                            'type' => ['type' => 'string', 'nullable' => true, 'enum' => ['suv', 'truck'], 'example' => 'suv'],
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
