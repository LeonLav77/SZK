<?php

namespace Tests\Feature;

use App\Models\Coupon;
use App\Models\Edition;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class CouponRulesTest extends TestCase
{
    use RefreshDatabase;

    public function test_expired_coupons_are_hidden(): void
    {
        $edition = Edition::factory()->active()->create();
        $valid = Coupon::factory()->for($edition)->create(['valid_until' => today()]);
        Coupon::factory()->for($edition)->create(['valid_until' => today()->subDay()]);

        $this->getJson('/api/v1/coupons')
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.id', $valid->id);
    }

    public function test_partner_ads_are_listed_but_cannot_be_redeemed(): void
    {
        $partner = Coupon::factory()->for(Edition::factory()->active())->create(['discount' => null]);

        $this->getJson('/api/v1/coupons')->assertJsonPath('data.0.redeemable', false);
        $this->postJson("/api/v1/coupons/{$partner->id}/redeem")->assertUnprocessable();
        $this->assertDatabaseCount('redemptions', 0);
    }

    public function test_demo_seeder_imports_last_years_booklet(): void
    {
        Storage::fake('public');

        $this->seed();

        $edition = Edition::current();
        $this->assertSame('Proljeće/Ljeto 2026.', $edition->name);
        $this->assertSame(27, $edition->coupons()->count());
        $this->assertNotNull($edition->pdf_path);
    }
}
