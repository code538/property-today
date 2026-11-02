<?php

namespace Database\Seeders;

use App\Models\PropertyCategory;
use App\Models\PropertyCategoryMenu;
use Illuminate\Database\Seeder;

class PropertyCategoryMenuSeeder extends Seeder
{
    public function run(): void
    {
        $menus = [
            'buy' => [
                'residential' => 'Residential',
                'apartments' => 'Apartments',
                'villas' => 'Villas',
                'plots' => 'Plots',
                'commercial' => 'Commercial',
            ],

            'rent' => [
                'residential' => 'Residential',
                'commercial' => 'Commercial',
                'pg-hostels' => 'PG / Hostels',
            ],

            'land' => [
                'agricultural-land' => 'Agricultural',
                'residential-plots' => 'Residential Plots',
                'commercial-land' => 'Commercial Land',
            ],
        ];

        foreach ($menus as $menuType => $categories) {

            $sortOrder = 1;

            foreach ($categories as $slug => $label) {

                $category = PropertyCategory::where(
                    'slug',
                    $slug
                )->first();

                if (!$category) {
                    continue;
                }

                PropertyCategoryMenu::updateOrCreate(
                    [
                        'menu_type' => $menuType,
                        'property_category_id' => $category->id,
                    ],
                    [
                        'menu_label' => $label,
                        'sort_order' => $sortOrder++,
                        'is_active' => true,
                    ]
                );
            }
        }
    }
}
