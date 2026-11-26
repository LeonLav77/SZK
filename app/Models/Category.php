<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Fixed list, not editable in the admin. To change it, edit NAMES and run
 * `php artisan migrate` (the fixed-categories migration) or call Category::syncFixed().
 */
#[Fillable(['name', 'sort_order'])]
class Category extends Model
{
    use HasFactory;

    /** In display order. */
    public const NAMES = [
        'Hrana i piće',
        'Sport i rekreacija',
        'Kultura i zabava',
        'Usluge i kupovina',
        'Obrazovanje',
    ];

    /** Makes sure every fixed category exists, in the right order. Existing rows (and their coupons) are kept. */
    public static function syncFixed(): void
    {
        foreach (self::NAMES as $i => $name) {
            static::updateOrCreate(['name' => $name], ['sort_order' => $i * 10]);
        }
    }

    public function coupons(): HasMany
    {
        return $this->hasMany(Coupon::class);
    }
}
