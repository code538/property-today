<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PropertyCategoryMenuResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'menu_type' => $this->menu_type,
            'menu_label' => $this->menu_label,
            'sort_order' => $this->sort_order,
            'is_active' => (bool) $this->is_active,

            'category' => new PropertyCategoryResource(
                $this->whenLoaded('category')
            ),
        ];
    }
}
