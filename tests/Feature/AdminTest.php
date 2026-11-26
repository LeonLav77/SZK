<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Coupon;
use App\Models\Edition;
use App\Models\Titular;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
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
            '/admin/editions', '/admin/editions/create', "/admin/editions/{$edition->id}",
            "/admin/editions/{$edition->id}/edit", "/admin/editions/{$edition->id}/coupons/create",
            "/admin/coupons/{$coupon->id}/edit", '/admin/titulars', '/admin/titulars/create',
            "/admin/titulars/{$coupon->titular_id}/edit", '/admin/users',
            "/admin/users/{$admin->id}/edit", '/admin/settings',
        ] as $url) {
            $this->get($url)->assertOk();
        }

        $this->get('/admin')->assertRedirect('/admin/editions');
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

    public function test_coupon_can_be_in_several_categories(): void
    {
        Storage::fake('public');
        $edition = Edition::factory()->active()->create();
        [$food, $sport, $culture] = Category::orderBy('sort_order')->take(3)->get();
        $this->actingAs($this->admin());

        $this->post("/admin/editions/{$edition->id}/coupons", [
            'titular_id' => Titular::factory()->create()->id,
            'title' => 'Bowling i pizza',
            'categories' => [$food->id, $sport->id],
            'visual' => UploadedFile::fake()->image('oglas.jpg'),
        ])->assertSessionHasNoErrors();
        $coupon = Coupon::firstWhere('title', 'Bowling i pizza');
        $this->assertEqualsCanonicalizing([$food->id, $sport->id], $coupon->categories->modelKeys());

        // Listed under each of its categories, not under others.
        $this->getJson("/api/v1/coupons?category={$food->id}")->assertJsonCount(1, 'data');
        $this->getJson("/api/v1/coupons?category={$sport->id}")->assertJsonCount(1, 'data');
        $this->getJson("/api/v1/coupons?category={$culture->id}")->assertJsonCount(0, 'data');

        // A copied edition keeps the categories.
        $this->post('/admin/editions', ['name' => 'Jesen 2027', 'copy_from' => $edition->id]);
        $copy = Edition::firstWhere('name', 'Jesen 2027')->coupons()->first();
        $this->assertCount(2, $copy->categories);
    }

    public function test_coupon_needs_a_visual(): void
    {
        $edition = Edition::factory()->create();

        $this->actingAs($this->admin())
            ->post("/admin/editions/{$edition->id}/coupons", ['titular_id' => Titular::factory()->create()->id, 'title' => 'Pizza'])
            ->assertSessionHasErrors('visual');
    }

    public function test_admin_can_add_a_coupon_and_location(): void
    {
        Storage::fake('public');
        $edition = Edition::factory()->create();
        $titular = Titular::factory()->create();
        $this->actingAs($this->admin());

        $this->post("/admin/editions/{$edition->id}/coupons", [
            'titular_id' => $titular->id,
            'title' => 'Pizza',
            'discount' => '2 za 1',
            'visual' => UploadedFile::fake()->image('oglas.jpg', 1050, 1500),
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

    public function test_logo_can_be_uploaded_replaced_and_removed(): void
    {
        Storage::fake('public');
        $this->actingAs($this->admin());

        $this->post('/admin/titulars', ['name' => 'Napoli', 'logo' => UploadedFile::fake()->image('logo.png')])
            ->assertSessionHasNoErrors();
        $titular = Titular::firstWhere('name', 'Napoli');
        Storage::disk('public')->assertExists($titular->logo_path);

        // Saving without a new file keeps the current logo.
        $this->put("/admin/titulars/{$titular->id}", ['name' => 'Napoli 2'])->assertSessionHasNoErrors();
        $this->assertNotNull($titular->fresh()->logo_path);

        $this->put("/admin/titulars/{$titular->id}", ['name' => 'Napoli', 'remove_logo' => '1']);
        $this->assertNull($titular->fresh()->logo_path);
    }

    public function test_too_big_upload_is_a_form_error_not_a_crash(): void
    {
        Storage::fake('public');

        $this->actingAs($this->admin())
            ->post('/admin/titulars', ['name' => 'Napoli', 'logo' => UploadedFile::fake()->image('logo.png')->size(25000)])
            ->assertSessionHasErrors('logo');
    }
}
