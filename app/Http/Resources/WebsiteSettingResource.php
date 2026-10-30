<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class WebsiteSettingResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        // change this code
        return [
            'id' => $this->id,

            'company' => [
                'name' => $this->company_name,
                'email' => $this->company_email,
                'phone' => $this->company_phone,
                'address' => $this->company_address,
            ],
            'website' => [
                'name' => $this->website_name,
                'url' => $this->website_url,
                'tagline' => $this->tagline,
            ],
            'branding' => [
                'logo_web' => $this->logo_web_path
                    ? '/storage/app/public/' . $this->logo_web_path : null,
                'logo_mobile' => $this->logo_mobile_path
                    ? '/storage/app/public/' . $this->logo_mobile_path : null,
                'logo_alt' => $this->logo_alt,
                'favicon' => $this->favicon_path
                    ? '/storage/app/public/' . $this->favicon_path : null,
            ],

            'seo' => [
                'meta_title' => $this->meta_title,
                'meta_description' => $this->meta_description,
                'meta_keywords' => $this->meta_keywords,
            ],

            'social' => [
                'facebook' => $this->facebook_url,
                'instagram' => $this->instagram_url,
                'linkedin' => $this->linkedin_url,
                'youtube' => $this->youtube_url,
            ],

            'social_sharing' => [
                'image_web' => $this->og_image_web_path
                    ? '/storage/app/public/' . $this->og_image_web_path : null,
                'image_mobile' => $this->og_image_mobile_path
                    ? '/storage/app/public/' . $this->og_image_mobile_path : null,
                'image_alt' => $this->og_image_alt,
            ],
        ];
    }
}
