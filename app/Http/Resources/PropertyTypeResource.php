<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PropertyTypeResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'slug' => $this->slug,
            'code' => $this->code,
            'description' => $this->description,

            'parent_id' => $this->parent_id,

            'icon' => $this->icon,
            'image' => $this->image
                ? asset('storage/' . ltrim($this->image, '/'))
                : null,
            'image_alt' => $this->image_alt,

            'sort_order' => $this->sort_order,

            'features' => [
                'sale' => $this->supports_sale,
                'rent' => $this->supports_rent,
                'lease' => $this->supports_lease,
                'bedrooms' => $this->supports_bedrooms,
                'bathrooms' => $this->supports_bathrooms,
                'area' => $this->supports_area,
                'plot_area' => $this->supports_plot_area,
                'furnishing' => $this->supports_furnishing,
                'parking' => $this->supports_parking,
            ],

            'form_schema' => $this->form_schema,

            'seo' => [
                'meta_title' => $this->meta_title,
                'meta_description' => $this->meta_description,
            ],

            'is_active' => $this->is_active,
            'is_featured' => $this->is_featured,

            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}
