<?php

namespace Database\Seeders;

use App\Models\Amenity;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class AmenitySeeder extends Seeder
{
    public function run(): void
    {
        $amenities = [
            [
                'name' => 'Lift',
                'group' => 'Basic Facilities',
                'icon' => 'building',
            ],
            [
                'name' => 'Car Parking',
                'group' => 'Parking',
                'icon' => 'car',
            ],
            [
                'name' => '24x7 Security',
                'group' => 'Security',
                'icon' => 'shield-check',
            ],
            [
                'name' => 'CCTV',
                'group' => 'Security',
                'icon' => 'camera',
            ],
            [
                'name' => 'Power Backup',
                'group' => 'Basic Facilities',
                'icon' => 'zap',
            ],
            [
                'name' => 'Swimming Pool',
                'group' => 'Recreation',
                'icon' => 'waves',
            ],
            [
                'name' => 'Gym',
                'group' => 'Recreation',
                'icon' => 'dumbbell',
            ],
            [
                'name' => 'Garden',
                'group' => 'Outdoor',
                'icon' => 'trees',
            ],
            [
                'name' => 'Club House',
                'group' => 'Recreation',
                'icon' => 'users',
            ],
            [
                'name' => 'Children Play Area',
                'group' => 'Recreation',
                'icon' => 'baby',
            ],
            [
                'name' => 'Water Supply',
                'group' => 'Basic Facilities',
                'icon' => 'droplets',
            ],
            [
                'name' => 'Fire Safety',
                'group' => 'Security',
                'icon' => 'flame',
            ],
            [
                'name' => 'Visitor Parking',
                'group' => 'Parking',
                'icon' => 'parking-circle',
            ],
            [
                'name' => 'Wi-Fi',
                'group' => 'Basic Facilities',
                'icon' => 'wifi',
            ],
            [
                'name' => 'Air Conditioning',
                'group' => 'Comfort',
                'icon' => 'snowflake',
            ],
            [
                'name' => 'Attached Bathroom',
                'group' => 'Comfort',
                'icon' => 'bath',
            ],
            [
                'name' => 'Food Available',
                'group' => 'Food',
                'icon' => 'utensils',
            ],
            [
                'name' => 'Laundry',
                'group' => 'Basic Facilities',
                'icon' => 'washing-machine',
            ],
        ];

        foreach ($amenities as $index => $amenity) {

            Amenity::updateOrCreate(
                [
                    'slug' => Str::slug($amenity['name']),
                ],
                [
                    'name' => $amenity['name'],
                    'amenity_group' => $amenity['group'],
                    'icon' => $amenity['icon'],
                    'is_active' => true,
                    'sort_order' => $index + 1,
                ]
            );
        }
    }
}
