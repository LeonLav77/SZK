<?php

namespace App\Support;

use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Throwable;

/**
 * One place for admin file uploads (logos, coupon visuals, edition PDF and cover).
 *
 * Laravel's $file->store() reads the temp file through realpath(), which fails when PHP's
 * upload_tmp_dir can't be listed (e.g. C:\Windows\Temp under Herd) and ends in a 500.
 * move() uses PHP's own move_uploaded_file() instead, so it works there too, and any
 * failure turns into a normal form error rather than a crash.
 */
class Uploads
{
    /**
     * The path to save for $field: a newly uploaded file, null when "remove_{$field}"
     * is ticked, otherwise the current path unchanged.
     *
     * Old files are not deleted: coupons copied into a new edition share the same file.
     */
    public static function sync(Request $request, string $field, string $dir, ?string $current): ?string
    {
        if ($request->hasFile($field)) {
            return self::store($request->file($field), $field, $dir);
        }

        return $request->boolean("remove_{$field}") ? null : $current;
    }

    public static function store(UploadedFile $file, string $field, string $dir): string
    {
        $name = $file->hashName();

        try {
            $file->move(Storage::disk('public')->path($dir), $name);
        } catch (Throwable $e) {
            report($e);

            throw ValidationException::withMessages([
                $field => 'Datoteku nije bilo moguće spremiti. Pokušaj ponovno ili odaberi drugu datoteku.',
            ]);
        }

        return "{$dir}/{$name}";
    }
}
