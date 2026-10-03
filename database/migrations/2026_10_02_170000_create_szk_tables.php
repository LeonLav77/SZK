<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('editions', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('status')->default('draft'); // draft | active | archived
            $table->string('about_title')->nullable();
            $table->text('about_body')->nullable();
            $table->string('pdf_path')->nullable();
            $table->string('cover_path')->nullable();
            $table->timestamp('activated_at')->nullable();
            $table->timestamps();
        });

        Schema::create('categories', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::create('titulars', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('logo_path')->nullable();
            $table->string('website')->nullable();
            $table->string('instagram')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        Schema::create('locations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('titular_id')->constrained()->cascadeOnDelete();
            $table->string('address');
            $table->string('note')->nullable(); // opening hours, phone...
            $table->decimal('lat', 10, 7)->nullable();
            $table->decimal('lng', 10, 7)->nullable();
            $table->timestamps();
        });

        Schema::create('coupons', function (Blueprint $table) {
            $table->id();
            $table->foreignId('edition_id')->constrained()->cascadeOnDelete();
            $table->foreignId('titular_id')->constrained()->cascadeOnDelete();
            $table->foreignId('category_id')->nullable()->constrained()->nullOnDelete();
            $table->string('title');
            $table->string('discount')->nullable(); // empty = partner ad, no "Iskoristi kupon"
            $table->text('description')->nullable();
            $table->text('conditions')->nullable();
            $table->string('code')->nullable(); // promo code for online use
            $table->string('link')->nullable();
            $table->date('valid_until')->nullable(); // empty = valid while the edition is active
            $table->string('visual_path')->nullable();
            $table->boolean('requires_login')->default(false);
            $table->boolean('is_published')->default(true);
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::create('redemptions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('coupon_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->timestamp('created_at')->useCurrent()->index();
        });

        Schema::create('settings', function (Blueprint $table) {
            $table->string('key')->primary();
            $table->text('value')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('settings');
        Schema::dropIfExists('redemptions');
        Schema::dropIfExists('coupons');
        Schema::dropIfExists('locations');
        Schema::dropIfExists('titulars');
        Schema::dropIfExists('categories');
        Schema::dropIfExists('editions');
    }
};
