<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PropertyResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'listing_code' => $this->listing_code,
            'slug' => $this->slug,

            'title' => $this->title,
            'description' => $this->description,

            'property_type' => $this->whenLoaded(
                'propertyType',
                fn () => [
                    'id' => $this->propertyType->id,
                    'name' => $this->propertyType->name,
                    'slug' => $this->propertyType->slug,
                ]
            ),

            'listing_type' => $this->listing_type,
            'price' => $this->price,
            'price_negotiable' => $this->price_negotiable,
            'maintenance_charge' => $this->maintenance_charge,
            'security_deposit' => $this->security_deposit,
            'price_period' => $this->price_period,

            'area' => [
                'built_up' => $this->built_up_area,
                'carpet' => $this->carpet_area,
                'plot' => $this->plot_area,
                'unit' => $this->area_unit,
            ],

            'rooms' => [
                'bedrooms' => $this->bedrooms,
                'bathrooms' => $this->bathrooms,
                'balconies' => $this->balconies,
                'floor_number' => $this->floor_number,
                'total_floors' => $this->total_floors,
                'total_rooms' => $this->total_rooms,
                'available_rooms' => $this->available_rooms,
                'total_beds' => $this->total_beds,
                'available_beds' => $this->available_beds,
            ],

            'details' => [
                'furnishing_status' => $this->furnishing_status,
                'property_condition' => $this->property_condition,
                'facing_direction' => $this->facing_direction,
                'year_built' => $this->year_built,
                'possession_status' => $this->possession_status,
                'tenant_preference' => $this->tenant_preference,
            ],

            'location' => [
                'address_line_1' => $this->address_line_1,
                'address_line_2' => $this->address_line_2,
                'locality' => $this->locality,
                'landmark' => $this->landmark,
                'city' => $this->city,
                'district' => $this->district,
                'state' => $this->state,
                'country' => $this->country,
                'postal_code' => $this->postal_code,
                'latitude' => $this->latitude,
                'longitude' => $this->longitude,
            ],

            // Do not expose contact numbers in public responses.
            'owner' => $this->whenLoaded(
                'user',
                fn () => [
                    'id' => $this->user->id,
                    'name' => $this->show_owner_name
                        ? $this->user->name
                        : null,
                ]
            ),

            'images' => PropertyImageResource::collection(
                $this->whenLoaded('images')
            ),

            'amenities' => AmenityResource::collection(
                $this->whenLoaded('amenities')
            ),

            'status' => $this->status,
            'is_featured' => $this->is_featured,
            'published_at' => $this->published_at?->toISOString(),
            'created_at' => $this->created_at?->toISOString(),
        ];
    }
}
