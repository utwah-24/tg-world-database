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
                'description' => 'API documentation for cars, logos, and category filtering.',
            ],
            'servers' => [
                [
                    'url' => url('/'),
                ],
            ],
            'paths' => [
                '/api/cars' => [
                    'get' => [
                        'tags' => ['Cars'],
                        'summary' => 'Get all cars',
                        'parameters' => [
                            [
                                'name' => 'category',
                                'in' => 'query',
                                'required' => false,
                                'description' => 'Filter cars by category (SUV or TRUCKS).',
                                'schema' => [
                                    'type' => 'string',
                                    'enum' => ['SUV', 'TRUCKS'],
                                    'example' => 'SUV',
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
            ],
            'components' => [
                'schemas' => [
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
                            'category' => ['type' => 'string', 'nullable' => true, 'example' => 'SUV'],
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
                ],
            ],
        ]);
    }
}
