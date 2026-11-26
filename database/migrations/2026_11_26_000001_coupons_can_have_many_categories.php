<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/** A coupon can sit in several categories: coupons.category_id becomes a category_coupon pivot. */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('category_coupon', function (Blueprint $table) {
            $table->foreignId('category_id')->constrained()->cascadeOnDelete();
            $table->foreignId('coupon_id')->constrained()->cascadeOnDelete();
            $table->primary(['category_id', 'coupon_id']);
        });

        DB::table('category_coupon')->insertUsing(
            ['category_id', 'coupon_id'],
            DB::table('coupons')->whereNotNull('category_id')->select('category_id', 'id'),
        );

        Schema::table('coupons', function (Blueprint $table) {
            $table->dropConstrainedForeignId('category_id');
        });
    }

    public function down(): void
    {
        Schema::table('coupons', function (Blueprint $table) {
            $table->foreignId('category_id')->nullable()->after('titular_id')->constrained()->nullOnDelete();
        });

        // Back to one category per coupon: keep the lowest one.
        DB::table('category_coupon')->orderBy('category_id')->get()->each(fn ($row) => DB::table('coupons')
            ->where('id', $row->coupon_id)->whereNull('category_id')->update(['category_id' => $row->category_id]));

        Schema::dropIfExists('category_coupon');
    }
};
