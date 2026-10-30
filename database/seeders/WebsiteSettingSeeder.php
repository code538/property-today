<?php

namespace Database\Seeders;

use App\Models\WebsiteSetting;
use Illuminate\Database\Seeder;

class WebsiteSettingSeeder extends Seeder
{
    public function run(): void
    {
        WebsiteSetting::firstOrCreate(
            ['id' => 1],
            [
                'company_name' => 'PropertyHub',
                'website_name' => 'PropertyHub',
                'website_url' => 'http://localhost:5173',
                'tagline' => 'Find your perfect property',
                'meta_title' => 'Buy, Sell & Rent Properties',
                'meta_description' =>
                    'Find land, houses, flats and rental properties.',
            ]
        );
    }
}
