<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class PropertyResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            // Basic property information
            'id' => $this->id,
            'title' => $this->title,
            'slug' => $this->slug,
            'description' => $this->description,

            // Listing information
            'listing_type' => $this->listing_type,
            'price' => $this->price,
            'price_negotiable' => (bool) $this->price_negotiable,
            'price_period' => $this->price_period,
            'maintenance_charge' => $this->maintenance_charge,
            'security_deposit' => $this->security_deposit,

            // Property area
            'built_up_area' => $this->built_up_area,
            'carpet_area' => $this->carpet_area,
            'plot_area' => $this->plot_area,
            'area_unit' => $this->area_unit,

            // Property specifications
            'bedrooms' => $this->bedrooms,
            'bathrooms' => $this->bathrooms,
            'balconies' => $this->balconies,
            'furnishing_status' => $this->furnishing_status,
            'floor_number' => $this->floor_number,
            'total_floors' => $this->total_floors,
            'property_age' => $this->property_age,
            'possession_status' => $this->possession_status,
            'available_from' => $this->available_from?->format('Y-m-d'),

            // Category
            'category' => new PropertyCategoryResource(
                $this->whenLoaded('category')
            ),

            // Owner information
            'owner' => $this->whenLoaded('user', function () {
                return [
                    'id' => $this->user->id,
                    'name' => $this->user->name,
                    'user_type' => $this->user->user_type ?? null,
                ];
            }),

            // Property media
           
            'media' => $this->whenLoaded('media', function () {
                return $this->media->map(function ($item) {

                    return [
                        'id' => $item->id,
                        'media_type' => $item->media_type,

                        // Relative storage path
                        'file_path' => '/storage/app/public/'.$item->file_path,

                        // Public web URL
                        'web_url' => $item->file_path
                            ? Storage::disk('public')->url($item->file_path)
                            : null,

                        'mobile_file_path' => '/storage/app/public/'.$item->mobile_file_path,

                        'mobile_url' => $item->mobile_file_path
                            ? Storage::disk('public')->url($item->mobile_file_path)
                            : null,

                        'video_url' => $item->media_type === 'video' && $item->file_path
                            ? Storage::disk('public')->url($item->file_path)
                            : null,

                        'image_alt' => $item->image_alt,
                        'mime_type' => $item->mime_type,
                        'file_size' => $item->file_size,
                        'is_primary' => (bool) $item->is_primary,
                        'sort_order' => $item->sort_order,
                    ];
                });
            }),


            // Property location
            'location' => $this->whenLoaded('location', function () {
                return [
                    'country' => $this->location->country,
                    'state' => $this->location->state,
                    'city' => $this->location->city,
                    'locality' => $this->location->locality,
                    'pincode' => $this->location->pincode,
                    'address' => $this->location->address,
                    'landmark' => $this->location->landmark,
                    'latitude' => $this->location->latitude,
                    'longitude' => $this->location->longitude,
                ];
            }),

            // Amenities
            'amenities' => $this->whenLoaded('amenities', function () {
                return $this->amenities->map(function ($amenity) {
                    return [
                        'id' => $amenity->id,
                        'name' => $amenity->name,
                        'slug' => $amenity->slug,
                        'amenity_group' => $amenity->amenity_group,
                        'icon' => $amenity->icon,
                    ];
                });
            }),

            // Additional category-specific details
            'details' => $this->whenLoaded('details', function () {
                return [
                    'plot_length' => $this->details->plot_length,
                    'plot_width' => $this->details->plot_width,
                    'facing' => $this->details->facing,
                    'road_width' => $this->details->road_width,
                    'sharing_type' => $this->details->sharing_type,
                    'total_beds' => $this->details->total_beds,
                    'available_beds' => $this->details->available_beds,
                    'food_available' => $this->details->food_available,
                    'office_type' => $this->details->office_type,
                    'number_of_cabins' => $this->details->number_of_cabins,
                    'number_of_washrooms' => $this->details->number_of_washrooms,
                    'attributes' => $this->details->attributes,
                ];
            }),

            // SEO
            'seo' => [
                'meta_title' => $this->meta_title,
                'meta_description' => $this->meta_description,
            ],

            // Additional information
            'is_featured' => (bool) $this->is_featured,
            'views_count' => $this->views_count,
            'published_at' => $this->published_at?->toDateTimeString(),
            'created_at' => $this->created_at?->toDateTimeString(),
        ];
    }
}
