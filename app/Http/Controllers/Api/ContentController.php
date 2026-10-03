<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\CategoryResource;
use App\Http\Resources\EditionResource;
use App\Models\Category;
use App\Models\Edition;
use App\Models\Setting;

class ContentController extends Controller
{
    public function edition()
    {
        $edition = Edition::current();

        abort_unless($edition, 404, 'Trenutno nema aktivnog izdanja.');

        return new EditionResource($edition);
    }

    public function settings()
    {
        $settings = Setting::values();

        return ['data' => [
            'contact_email' => $settings['contact_email'] ?: null,
            'contact_instagram' => $settings['contact_instagram'] ?: null,
            'contact_link' => $settings['contact_link'] ?: null,
            'countdown_seconds' => (int) $settings['countdown_seconds'],
        ]];
    }

    public function categories()
    {
        return CategoryResource::collection(Category::orderBy('sort_order')->orderBy('name')->get());
    }

    /** Printed issues: every non-draft edition that has a PDF, newest first. */
    public function printIssues()
    {
        $editions = Edition::whereNotNull('pdf_path')
            ->where('status', '!=', 'draft')
            ->orderByDesc('activated_at')
            ->get();

        return EditionResource::collection($editions);
    }
}
