<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Edition;
use App\Models\Redemption;
use App\Models\User;

class DashboardController extends Controller
{
    public function __invoke()
    {
        $edition = Edition::current();

        $redemptions = Redemption::whereHas('coupon', fn ($q) => $q->where('edition_id', $edition?->id));

        return view('admin.dashboard', [
            'edition' => $edition,
            'couponCount' => $edition?->coupons()->count() ?? 0,
            'redemptionCount' => (clone $redemptions)->count(),
            'weekCount' => (clone $redemptions)->where('created_at', '>=', now()->subDays(7))->count(),
            'userCount' => User::where('is_admin', false)->count(),
            'topCoupons' => $edition
                ? $edition->coupons()->with('titular')->withCount('redemptions')
                    ->reorder()->orderByDesc('redemptions_count')->limit(5)->get()
                : collect(),
        ]);
    }
}
