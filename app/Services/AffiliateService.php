<?php

namespace App\Services;

use App\Exceptions\AffiliateCreateException;
use App\Mail\AffiliateCreated;
use App\Models\Affiliate;
use App\Models\Merchant;
use App\Models\Order;
use App\Models\User;
use Illuminate\Support\Facades\Mail;

class AffiliateService
{
    public function __construct(
        protected ApiService $apiService
    ) {}

    /**
     * Create a new affiliate for the merchant with the given commission rate.
     *
     * @param  Merchant $merchant
     * @param  string $email
     * @param  string $name
     * @param  float $commissionRate
     * @return Affiliate
     */
    public function register(Merchant $merchant, string $email, string $name, float $commissionRate): Affiliate
    {
        $existingAffiliate = Affiliate::where('email', $email)->first();
        if ($existingAffiliate) {
            throw new AffiliateCreateException("this affiliate already exists.");
        }
        $user = new User([
            'name' => $name,
            'email' => $email,
            'type' => User::TYPE_AFFILIATE,
        ]);
        $user->save();
        $affiliate = new Affiliate([
            'user_id' => $user->id,
            'merchant_id' => $merchant->id,
            'commission_rate' => $commissionRate,
        ]);
        $affiliate->save();
        return $affiliate;
    }
}
