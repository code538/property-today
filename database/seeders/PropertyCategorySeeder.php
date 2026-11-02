<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\PropertyCategory;

class PropertyCategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            [
                'name' => 'Residential',
                'slug' => 'residential',
                'description' => 'Residential properties for buying and renting.',
                'sort_order' => 1,
                'children' => [
                    ['name' => 'Apartments', 'slug' => 'apartments'],
                    ['name' => 'Villas', 'slug' => 'villas'],
                    ['name' => 'Independent Houses', 'slug' => 'independent-houses'],
                    ['name' => 'Builder Floors', 'slug' => 'builder-floors'],
                ],
            ],
            [
                'name' => 'Commercial',
                'slug' => 'commercial',
                'description' => 'Commercial properties for business purposes.',
                'sort_order' => 2,
                'children' => [
                    ['name' => 'Offices', 'slug' => 'offices'],
                    ['name' => 'Shops', 'slug' => 'shops'],
                    ['name' => 'Warehouses', 'slug' => 'warehouses'],
                    ['name' => 'Showrooms', 'slug' => 'showrooms'],
                    ['name' => 'Commercial Buildings', 'slug' => 'commercial-buildings'],
                ],
            ],
            [
                'name' => 'Plots',
                'slug' => 'plots',
                'description' => 'Residential and commercial plots.',
                'sort_order' => 3,
                'children' => [
                    ['name' => 'Residential Plots', 'slug' => 'residential-plots'],
                    ['name' => 'Commercial Plots', 'slug' => 'commercial-plots'],
                    ['name' => 'Industrial Plots', 'slug' => 'industrial-plots'],
                ],
            ],
            [
                'name' => 'Land & Agriculture',
                'slug' => 'land-agriculture',
                'description' => 'Agricultural and other land properties.',
                'sort_order' => 4,
                'children' => [
                    ['name' => 'Agricultural Land', 'slug' => 'agricultural-land'],
                    ['name' => 'Farm Land', 'slug' => 'farm-land'],
                    ['name' => 'Industrial Land', 'slug' => 'industrial-land'],
                ],
            ],
            [
                'name' => 'PG & Hostels',
                'slug' => 'pg-hostels',
                'description' => 'Paying guest accommodation and hostels.',
                'sort_order' => 5,
                'children' => [
                    ['name' => 'Boys PG', 'slug' => 'boys-pg'],
                    ['name' => 'Girls PG', 'slug' => 'girls-pg'],
                    ['name' => 'Co-living Spaces', 'slug' => 'co-living-spaces'],
                    ['name' => 'Hostels', 'slug' => 'hostels'],
                ],
            ],
        ];

        foreach ($categories as $categoryData) {
            $children = $categoryData['children'];
            unset($categoryData['children']);

            $parent = PropertyCategory::updateOrCreate(
                ['slug' => $categoryData['slug']],
                array_merge($categoryData, [
                    'parent_id' => null,
                    'is_active' => true,
                ])
            );

            foreach ($children as $childData) {
                PropertyCategory::updateOrCreate(
                    ['slug' => $childData['slug']],
                    [
                        'parent_id' => $parent->id,
                        'name' => $childData['name'],
                        'description' => null,
                        'is_active' => true,
                    ]
                );
            }
        }
    }
}
