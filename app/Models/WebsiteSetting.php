<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WebsiteSetting extends Model
{
    protected $fillable = [
        'company_name',
        'company_email',
        'company_phone',
        'company_address',
        'website_name',
        'website_url',
        'tagline',
        'logo_web_path',
        'logo_mobile_path',
        'logo_alt',
        'favicon_path',
        'meta_title',
        'meta_description',
        'meta_keywords',
        'og_image_web_path',
        'og_image_mobile_path',
        'og_image_alt',
        'facebook_url',
        'instagram_url',
        'linkedin_url',
        'youtube_url',
    ];
}
