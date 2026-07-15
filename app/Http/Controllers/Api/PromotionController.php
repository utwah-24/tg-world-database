<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Promotion;
use App\Services\PromotionService;
use Illuminate\Http\JsonResponse;

class PromotionController extends Controller
{
    public function index(): JsonResponse
    {
        PromotionService::syncStatuses();

        return response()->json([
            'data' => Promotion::with('cars')->orderBy('promoID')->get()->map(fn (Promotion $promo) => [
                'promoID' => $promo->promoID,
                'promo_name' => $promo->promo_name,
                'price_reduction' => $promo->price_reduction,
                'price_reduction_label' => $promo->price_reduction_label,
                'promo_pics' => $promo->promo_pics,
                'promo_pic_urls' => $promo->promo_pic_urls,
                'start_date' => $promo->start_date?->toDateString(),
                'end_date' => $promo->end_date?->toDateString(),
                'status' => $promo->status,
                'is_active' => $promo->isCurrentlyActive(),
                'car_ids' => $promo->cars->pluck('car_id')->values(),
                'created_at' => $promo->created_at,
                'updated_at' => $promo->updated_at,
            ]),
        ]);
    }
}
