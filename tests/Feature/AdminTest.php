<?php

namespace Tests\Feature;

use App\Models\Coupon;
use App\Models\Edition;
use App\Models\Titular;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        $user = User::factory()->create();
        $user->forceFill(['is_admin' => true])->save();

        return $user;
    }

    public function test_guests_are_sent_to_login_and_students_are_blocked(): void
    {
        $this->get('/admin')->assertRedirect('/admin/login');
        $this->actingAs(User::factory()->create())->get('/admin')->assertForbidden();
    }

    public function test_every_admin_page_renders(): void
    {
        $edition = Edition::factory()->active()->create();
        $coupon = Coupon::factory()->for($edition)->create();
        $this->actingAs($admin = $this->admin());

        foreach ([
            '/admin', '/admin/editions', '/admin/editions/create', "/admin/editions/{$edition->id}",
            "/admin/editions/{$edition->id}/edit", "/admin/editions/{$edition->id}/coupons/create",
            "/admin/coupons/{$coupon->id}/edit", '/admin/titulars', '/admin/titulars/create',
            "/admin/titulars/{$coupon->titular_id}/edit", '/admin/categories', '/admin/users',
            "/admin/users/{$admin->id}/edit", '/admin/settings', "/admin/editions/{$edition->id}/export",
        ] as $url) {
            $this->get($url)->assertOk();
        }
    }

    public function test_activating_an_edition_archives_the_previous_one(): void
    {
        $old = Edition::factory()->active()->create();
        $new = Edition::factory()->create();

        $this->actingAs($this->admin())->post("/admin/editions/{$new->id}/activate")->assertRedirect();

        $this->assertSame('archived', $old->fresh()->status);
        $this->assertSame('active', $new->fresh()->status);
    }

    public function test_new_edition_can_copy_coupons(): void
    {
        $old = Edition::factory()->active()->create();
        Coupon::factory()->for($old)->count(3)->create();

        $this->actingAs($this->admin())
            ->post('/admin/editions', ['name' => 'Ljeto 2027', 'copy_from' => $old->id])
            ->assertRedirect();

        $this->assertSame(3, Edition::where('name', 'Ljeto 2027')->first()->coupons()->count());
    }

    public function test_admin_can_add_a_coupon_and_location(): void
    {
        $edition = Edition::factory()->create();
        $titular = Titular::factory()->create();
        $this->actingAs($this->admin());

        $this->post("/admin/editions/{$edition->id}/coupons", [
            'titular_id' => $titular->id,
            'title' => 'Pizza',
            'discount' => '2 za 1',
            'is_published' => '1',
            'requires_login' => '0',
        ])->assertRedirect("/admin/editions/{$edition->id}");

        $this->post("/admin/titulars/{$titular->id}/locations", [
            'address' => 'Ilica 1, Zagreb', 'lat' => '45.8131', 'lng' => '15.9775',
        ])->assertRedirect();

        $this->assertDatabaseHas('coupons', ['title' => 'Pizza', 'discount' => '2 za 1', 'requires_login' => false]);
        $this->assertDatabaseHas('locations', ['titular_id' => $titular->id, 'address' => 'Ilica 1, Zagreb']);
    }

    public function test_active_edition_cannot_be_deleted(): void
    {
        $edition = Edition::factory()->active()->create();

        $this->actingAs($this->admin())->delete("/admin/editions/{$edition->id}");

        $this->assertModelExists($edition);
    }
}
