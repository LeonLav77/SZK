<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class CouponResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'discount' => $this->discount,
            'description' => $this->description,
            'conditions' => $this->conditions,
            'code' => $this->code,
            'redeemable' => $this->isRedeemable(),
            'visual_url' => $this->visual_path ? Storage::disk('public')->url($this->visual_path) : null,
            'requires_login' => $this->requires_login,
            'categories' => CategoryResource::collection($this->whenLoaded('categories')),
            'titular' => new TitularResource($this->whenLoaded('titular')),
        ];
    }
}
