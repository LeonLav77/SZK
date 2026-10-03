<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

/** Simple key/value store for global settings (contact info, countdown...). */
#[Fillable(['key', 'value'])]
class Setting extends Model
{
    public const DEFAULTS = [
        'contact_email' => '',
        'contact_instagram' => '',
        'contact_link' => '',
        'countdown_seconds' => '300',
    ];

    protected $primaryKey = 'key';

    protected $keyType = 'string';

    public $incrementing = false;

    public $timestamps = false;

    public static function values(): array
    {
        return array_merge(self::DEFAULTS, static::pluck('value', 'key')->all());
    }

    public static function read(string $key): ?string
    {
        return static::values()[$key] ?? null;
    }

    public static function store(array $values): void
    {
        foreach ($values as $key => $value) {
            static::updateOrCreate(['key' => $key], ['value' => $value]);
        }
    }
}
