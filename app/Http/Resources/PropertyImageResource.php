<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class PropertyImageResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'image_web_url' => $this->image_web_path
                ? Storage::disk('public')->url($this->image_web_path)
                : null,
            'image_mobile_url' => $this->image_mobile_path
                ? Storage::disk('public')->url($this->image_mobile_path)
                : null,
            'image_alt' => $this->image_alt,
            'video_url' => $this->video_path
                ? Storage::disk('public')->url($this->video_path)
                : null,
            'sort_order' => $this->sort_order,
            'is_primary' => (bool) $this->is_primary,
        ];
    }
}
