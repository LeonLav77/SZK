<?php

use App\Models\Category;
use Illuminate\Database\Migrations\Migration;

/** Categories are a fixed list in code (Category::NAMES); this puts them in the database. */
return new class extends Migration
{
    public function up(): void
    {
        Category::syncFixed();
    }

    public function down(): void
    {
        // Nothing to undo: the rows may already be used by coupons.
    }
};
