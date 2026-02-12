<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Logo;
use Illuminate\Http\JsonResponse;

class LogoController extends Controller
{
    public function index(): JsonResponse
    {
        return response()->json([
            'data' => Logo::orderBy('id')->get(),
        ]);
    }
}
