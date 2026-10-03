<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['address', 'note', 'lat', 'lng'])]
class Location extends Model
{
    protected function casts(): array
    {
        return ['lat' => 'float', 'lng' => 'float'];
    }

    public function titular(): BelongsTo
    {
        return $this->belongsTo(Titular::class);
    }
}
