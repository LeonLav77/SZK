<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Support\Facades\DB;

#[Fillable(['name', 'about_title', 'about_body', 'pdf_path', 'cover_path'])]
class Edition extends Model
{
    use HasFactory;

    public const STATUS_LABELS = [
        'draft' => 'U pripremi',
        'active' => 'Aktivno',
        'archived' => 'Arhivirano',
    ];

    protected function casts(): array
    {
        return ['activated_at' => 'datetime'];
    }

    public function coupons(): HasMany
    {
        return $this->hasMany(Coupon::class)->orderBy('sort_order')->orderBy('id');
    }

    public function redemptions(): HasManyThrough
    {
        return $this->hasManyThrough(Redemption::class, Coupon::class);
    }

    public static function current(): ?self
    {
        return static::where('status', 'active')->first();
    }

    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    public function statusLabel(): string
    {
        return self::STATUS_LABELS[$this->status] ?? $this->status;
    }

    /** Only one edition is ever active: the previous one gets archived. */
    public function activate(): void
    {
        DB::transaction(function () {
            static::where('status', 'active')->update(['status' => 'archived']);
            $this->forceFill(['status' => 'active', 'activated_at' => now()])->save();
        });
    }

    public function copyCouponsFrom(Edition $source): void
    {
        foreach ($source->coupons()->with('categories')->get() as $coupon) {
            $copy = $coupon->replicate()->fill(['edition_id' => $this->id]);
            $copy->save();
            $copy->categories()->sync($coupon->categories->modelKeys());
        }
    }
}
