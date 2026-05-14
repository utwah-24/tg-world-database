<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Company;
use Illuminate\Http\JsonResponse;

class CompanyController extends Controller
{
    public function index(): JsonResponse
    {
        $companies = Company::orderBy('name')->get()->map(fn (Company $c) => [
            'company_id'        => $c->id,
            'company_label'     => $c->name,
            'logo'              => $c->logo,
            'company_logo_path' => $c->logo_url,
        ]);

        return response()->json(['data' => $companies]);
    }
}
