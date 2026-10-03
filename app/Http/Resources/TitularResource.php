<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class TitularResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'logo_url' => $this->logo_path ? Storage::disk('public')->url($this->logo_path) : null,
            'website' => $this->website,
            'instagram' => $this->instagram,
            'locations' => $this->whenLoaded('locations', fn () => $this->locations->map(fn ($location) => [
                'id' => $location->id,
                'address' => $location->address,
                'note' => $location->note,
                'lat' => $location->lat,
                'lng' => $location->lng,
            ])),
        ];
    }
}
