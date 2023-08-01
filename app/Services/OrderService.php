<?php

namespace App\Services;

use App\Models\Affiliate;
use App\Models\Merchant;
use App\Models\Order;
use App\Models\User;

class OrderService
{
    public function __construct(
        protected AffiliateService $affiliateService
    ) {}

    /**
     * Process an order and log any commissions.
     * This should create a new affiliate if the customer_email is not already associated with one.
     * This method should also ignore duplicates based on order_id.
     *
     * @param  array{order_id: string, subtotal_price: float, merchant_domain: string, discount_code: string, customer_email: string, customer_name: string} $data
     * @return void
     */
    public function processOrder(array $data)
    {
        $existingOrder = Order::where('order_id', $data['order_id'])->first();
        if ($existingOrder) {
            return; 
        }

        $affiliate = $this->findOrCreateAffiliate($data['customer_email'], $data['customer_name']);

        $merchant = Merchant::where('domain', $data['merchant_domain'])->first();
        $order = new Order([
            'order_id' => $data['order_id'],
            'subtotal_price' => $data['subtotal_price'],
            'discount_code' => $data['discount_code'],
            'affiliate_id' => $affiliate->id,
            'merchant_id' => $merchant->id,
        ]);
        $order->save();
    }
    protected function findOrCreateAffiliate(string $email, string $name): Affiliate
    {
        $existingAffiliate = Affiliate::where('email', $email)->first();
        if ($existingAffiliate) {
            return $existingAffiliate;
        }
        $commissionRate = 0.1; 
        $merchant = null;

        return $this->affiliateService->register($merchant, $email, $name, $commissionRate);
    }

}
