<?php

namespace Database\Seeders;

use App\Models\PropertyType;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class PropertyTypeSeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            [
                'name' => 'Residential',
                'code' => 'RESIDENTIAL',
                'description' => 'Residential properties for living.',
                'icon' => 'house',
                'sort_order' => 1,
                'supports_sale' => true,
                'supports_rent' => true,
                'supports_lease' => true,
                'supports_bedrooms' => true,
                'supports_bathrooms' => true,
                'supports_area' => true,
                'supports_plot_area' => false,
                'supports_furnishing' => true,
                'supports_parking' => true,
            ],
            [
                'name' => 'Apartment',
                'code' => 'APARTMENT',
                'description' => 'Flats and apartments.',
                'icon' => 'building-2',
                'sort_order' => 2,
                'supports_bedrooms' => true,
                'supports_bathrooms' => true,
                'supports_furnishing' => true,
                'supports_parking' => true,
            ],
            [
                'name' => 'Villa',
                'code' => 'VILLA',
                'description' => 'Independent villas and houses.',
                'icon' => 'house',
                'sort_order' => 3,
                'supports_bedrooms' => true,
                'supports_bathrooms' => true,
                'supports_furnishing' => true,
                'supports_parking' => true,
            ],
            [
                'name' => 'Plot',
                'code' => 'PLOT',
                'description' => 'Residential and other land plots.',
                'icon' => 'map',
                'sort_order' => 4,
                'supports_bedrooms' => false,
                'supports_bathrooms' => false,
                'supports_plot_area' => true,
                'supports_furnishing' => false,
                'supports_parking' => false,
            ],
            [
                'name' => 'Commercial',
                'code' => 'COMMERCIAL',
                'description' => 'Shops, offices and commercial spaces.',
                'icon' => 'store',
                'sort_order' => 5,
                'supports_sale' => true,
                'supports_rent' => true,
                'supports_lease' => true,
                'supports_area' => true,
            ],
            [
                'name' => 'Hostel',
                'code' => 'HOSTEL',
                'description' => 'Hostel buildings and accommodation.',
                'icon' => 'bed',
                'sort_order' => 6,
                'supports_sale' => false,
                'supports_rent' => true,
                'supports_lease' => false,
                'supports_bedrooms' => false,
                'supports_bathrooms' => true,
                'supports_furnishing' => true,
            ],
            [
                'name' => 'PG',
                'code' => 'PG',
                'description' => 'Paying guest accommodation.',
                'icon' => 'bed',
                'sort_order' => 7,
                'supports_sale' => false,
                'supports_rent' => true,
                'supports_lease' => false,
                'supports_bedrooms' => true,
                'supports_bathrooms' => true,
                'supports_furnishing' => true,
            ],
        ];

        foreach ($categories as $category) {
            $name = $category['name'];
            $code = $category['code'];

            PropertyType::withTrashed()->updateOrCreate(
                ['code' => $code],
                array_merge([
                    'name' => $name,
                    'slug' => Str::slug($name),
                    'description' => null,
                    'parent_id' => null,
                    'icon' => null,
                    'image' => null,
                    'image_alt' => null,
                    'sort_order' => 0,
                    'supports_sale' => true,
                    'supports_rent' => true,
                    'supports_lease' => false,
                    'supports_bedrooms' => false,
                    'supports_bathrooms' => false,
                    'supports_area' => true,
                    'supports_plot_area' => false,
                    'supports_furnishing' => false,
                    'supports_parking' => false,
                    'form_schema' => null,
                    'meta_title' => null,
                    'meta_description' => null,
                    'is_active' => true,
                    'is_featured' => false,
                    'deleted_at' => null,
                ], $category)
            );
        }
    }
}
