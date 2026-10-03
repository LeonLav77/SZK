<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'edition_id', 'titular_id', 'category_id', 'title', 'discount', 'description', 'conditions',
    'code', 'link', 'valid_until', 'visual_path', 'requires_login', 'is_published', 'sort_order',
])]
class Coupon extends Model
{
    use HasFactory;

    protected function casts(): array
    {
        return [
            'requires_login' => 'boolean',
            'is_published' => 'boolean',
            'valid_until' => 'date',
        ];
    }

    /** Partner ads have no discount, so there is nothing to redeem. */
    public function isRedeemable(): bool
    {
        return filled($this->discount);
    }

    public function isExpired(): bool
    {
        return $this->valid_until?->endOfDay()->isPast() ?? false;
    }

    public function edition(): BelongsTo
    {
        return $this->belongsTo(Edition::class);
    }

    public function titular(): BelongsTo
    {
        return $this->belongsTo(Titular::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function redemptions(): HasMany
    {
        return $this->hasMany(Redemption::class);
    }

    /** Published, unexpired coupons of the active edition; guests don't see login-only ones. */
    #[Scope]
    protected function visibleTo(Builder $query, ?User $user): void
    {
        $query->where('is_published', true)
            ->whereHas('edition', fn ($q) => $q->where('status', 'active'))
            ->where(fn ($q) => $q->whereNull('valid_until')->orWhereDate('valid_until', '>=', today()))
            ->when(! $user, fn ($q) => $q->where('requires_login', false));
    }
}
