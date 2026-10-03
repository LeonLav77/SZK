<?php

namespace Tests\Feature;

use App\Models\Coupon;
use App\Models\Edition;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_guests_only_see_public_coupons_of_the_active_edition(): void
    {
        $active = Edition::factory()->active()->create();
        $public = Coupon::factory()->for($active)->create();
        Coupon::factory()->for($active)->loginOnly()->create();
        Coupon::factory()->for($active)->create(['is_published' => false]);
        Coupon::factory()->for(Edition::factory()->archived())->create();

        $this->getJson('/api/v1/coupons')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.id', $public->id);
    }

    public function test_logged_in_users_also_see_login_only_coupons(): void
    {
        $active = Edition::factory()->active()->create();
        Coupon::factory()->for($active)->create();
        Coupon::factory()->for($active)->loginOnly()->create();

        $this->withToken(User::factory()->create()->createToken('test')->plainTextToken)
            ->getJson('/api/v1/coupons')
            ->assertOk()
            ->assertJsonCount(2, 'data');
    }

    public function test_login_only_coupon_is_hidden_from_guests(): void
    {
        $coupon = Coupon::factory()->for(Edition::factory()->active())->loginOnly()->create();

        $this->getJson("/api/v1/coupons/{$coupon->id}")->assertNotFound();
        $this->postJson("/api/v1/coupons/{$coupon->id}/redeem")->assertNotFound();
    }

    public function test_redeeming_logs_a_use(): void
    {
        $coupon = Coupon::factory()->for(Edition::factory()->active())->create();
        $user = User::factory()->create();

        $this->withToken($user->createToken('test')->plainTextToken)
            ->postJson("/api/v1/coupons/{$coupon->id}/redeem")
            ->assertOk()
            ->assertJsonPath('data.seconds', 300)
            ->assertJsonStructure(['data' => ['redeemed_at', 'expires_at', 'seconds', 'coupon']]);

        $this->assertDatabaseHas('redemptions', ['coupon_id' => $coupon->id, 'user_id' => $user->id]);
    }

    public function test_register_login_and_me(): void
    {
        $this->postJson('/api/v1/register', [
            'name' => 'Ana', 'email' => 'ana@example.com', 'password' => 'secret123',
        ])->assertCreated()->assertJsonStructure(['data' => ['token', 'user']]);

        $token = $this->postJson('/api/v1/login', ['email' => 'ana@example.com', 'password' => 'secret123'])
            ->assertOk()
            ->json('data.token');

        $this->withToken($token)->getJson('/api/v1/me')->assertJsonPath('data.email', 'ana@example.com');

        $this->postJson('/api/v1/login', ['email' => 'ana@example.com', 'password' => 'wrong'])
            ->assertUnprocessable();
    }

    public function test_edition_returns_active_edition_or_404(): void
    {
        $this->getJson('/api/v1/edition')->assertNotFound();

        $edition = Edition::factory()->active()->create();

        $this->getJson('/api/v1/edition')->assertOk()->assertJsonPath('data.name', $edition->name);
    }
}
