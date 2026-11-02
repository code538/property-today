<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PropertyCategoryResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'parent_id' => $this->parent_id,
            'name' => $this->name,
            'slug' => $this->slug,
            'description' => $this->description,
            'icon' => $this->icon,
            'image' => $this->image,
            'meta_title' => $this->meta_title,
            'meta_description' => $this->meta_description,
            'sort_order' => $this->sort_order,
            'is_active' => (bool) $this->is_active,

            'parent' => new PropertyCategoryResource(
                $this->whenLoaded('parent')
            ),

            'children' => PropertyCategoryResource::collection(
                $this->whenLoaded('children')
            ),
        ];
    }
}
