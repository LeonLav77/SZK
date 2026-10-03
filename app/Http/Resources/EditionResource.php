<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class EditionResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'about_title' => $this->about_title,
            'about_body' => $this->about_body,
            'about_html' => $this->about_body
                ? Str::markdown($this->about_body, ['html_input' => 'escape', 'allow_unsafe_links' => false])
                : null,
            'pdf_url' => $this->pdf_path ? Storage::disk('public')->url($this->pdf_path) : null,
            'cover_url' => $this->cover_path ? Storage::disk('public')->url($this->cover_path) : null,
            'activated_at' => $this->activated_at,
        ];
    }
}
