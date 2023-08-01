<?php

namespace App\Http\Controllers;

use App\Models\Merchant;
use App\Services\MerchantService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class MerchantController extends Controller
{
    public function __construct(
        MerchantService $merchantService
    ) {}

    /**
     * Useful order statistics for the merchant API.
     * 
     * @param Request $request Will include a from and to date
     * @return JsonResponse Should be in the form {count: total number of orders in range, commission_owed: amount of unpaid commissions for orders with an affiliate, revenue: sum order subtotals}
     */
    public function orderStats(Request $request): JsonResponse
    {
        $fromDate = Carbon::parse($request->input('from'));
        $toDate = Carbon::parse($request->input('to'));
        $orderCount = Order::whereBetween('created_at', [$fromDate, $toDate])->count();
        $revenue = Order::whereBetween('created_at', [$fromDate, $toDate])->sum('subtotal_price');
        $commission = Order::where('payout_status', 'paid')->sum('subtotal_price * affiliate.commission_rate');
        return response()->json([
            'count' => $orderCount,
            'commission_owed' => $commission,
            'revenue' => $revenue,
        ]);
    }
}
