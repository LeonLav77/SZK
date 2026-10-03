<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\CouponResource;
use App\Models\Coupon;
use App\Models\Setting;
use Illuminate\Http\Request;

class CouponController extends Controller
{
    public function index(Request $request)
    {
        $coupons = Coupon::visibleTo($request->user('sanctum'))
            ->with(['titular.locations', 'category'])
            ->when($request->integer('category'), fn ($q, $categoryId) => $q->where('category_id', $categoryId))
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get();

        return CouponResource::collection($coupons);
    }

    public function show(Request $request, int $id)
    {
        return new CouponResource($this->findVisible($request, $id));
    }

    /** Logs a use and returns what the countdown screen needs. */
    public function redeem(Request $request, int $id)
    {
        $coupon = $this->findVisible($request, $id);

        abort_unless($coupon->isRedeemable(), 422, 'Ovo je oglas partnera, ne kupon.');

        $redemption = $coupon->redemptions()->create([
            'user_id' => $request->user('sanctum')?->id,
        ]);

        $seconds = (int) Setting::read('countdown_seconds');

        return ['data' => [
            'redeemed_at' => $redemption->created_at,
            'expires_at' => $redemption->created_at->copy()->addSeconds($seconds),
            'seconds' => $seconds,
            'coupon' => new CouponResource($coupon),
        ]];
    }

    /** Login-only coupons 404 for guests, so they stay completely hidden. */
    private function findVisible(Request $request, int $id): Coupon
    {
        return Coupon::visibleTo($request->user('sanctum'))
            ->with(['titular.locations', 'category'])
            ->findOrFail($id);
    }
}
