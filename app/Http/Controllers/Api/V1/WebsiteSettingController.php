<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\WebsiteSettingResource;
use App\Services\ApiResponseService;
use App\Services\WebsiteSettingService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class WebsiteSettingController extends Controller
{
    public function __construct(
        protected WebsiteSettingService $settingService,
        protected ApiResponseService $apiResponse
    ) {}

    // Public website settings
    public function show()
    {
        $settings = $this->settingService->getSettings();

        return $this->apiResponse->success(
            'Website settings retrieved successfully.',
            new WebsiteSettingResource($settings)
        );
    }

    // Update website settings (admin only)
   
    public function update(Request $request)
    {
        if ($request->user()?->role !== 'admin') {
            return $this->apiResponse->error(
                'You are not authorized to update website settings.',
                null,
                403
            );
        }

        $validator = Validator::make($request->all(), [
            // Company
            'company_name' => ['sometimes', 'required', 'string', 'max:255'],
            'company_email' => ['sometimes', 'nullable', 'email', 'max:255'],
            'company_phone' => ['sometimes', 'nullable', 'string', 'max:30'],
            'company_address' => ['sometimes', 'nullable', 'string', 'max:2000'],

            // Website
            'website_name' => ['sometimes', 'nullable', 'string', 'max:255'],
            'website_url' => ['sometimes', 'nullable', 'url', 'max:255'],
            'tagline' => ['sometimes', 'nullable', 'string', 'max:255'],

            // SEO
            'meta_title' => ['sometimes', 'nullable', 'string', 'max:255'],
            'meta_description' => ['sometimes', 'nullable', 'string', 'max:1000'],
            'meta_keywords' => ['sometimes', 'nullable', 'string', 'max:1000'],

            // Social links
            'facebook_url' => ['sometimes', 'nullable', 'url', 'max:255'],
            'instagram_url' => ['sometimes', 'nullable', 'url', 'max:255'],
            'linkedin_url' => ['sometimes', 'nullable', 'url', 'max:255'],
            'youtube_url' => ['sometimes', 'nullable', 'url', 'max:255'],

            // Desktop and mobile logos
            'logo_web' => [
                'sometimes', 'nullable', 'image',
                'mimes:jpg,jpeg,png,webp', 'max:2048',
            ],
            'logo_mobile' => [
                'sometimes', 'nullable', 'image',
                'mimes:jpg,jpeg,png,webp', 'max:2048',
            ],
            'logo_alt' => ['sometimes', 'nullable', 'string', 'max:255'],

            // Favicon
            'favicon' => [
                'sometimes', 'nullable', 'image',
                'mimes:png,jpg,jpeg,webp', 'max:1024',
            ],

            // Social-sharing images
            'og_image_web' => [
                'sometimes', 'nullable', 'image',
                'mimes:jpg,jpeg,png,webp', 'max:5120',
            ],
            'og_image_mobile' => [
                'sometimes', 'nullable', 'image',
                'mimes:jpg,jpeg,png,webp', 'max:5120',
            ],
            'og_image_alt' => ['sometimes', 'nullable', 'string', 'max:255'],
        ]);

        if ($validator->fails()) {
            return $this->apiResponse->error(
                'Validation failed.',
                $validator->errors(),
                422
            );
        }

        $validated = $validator->validated();

        // Separate uploaded files from regular database fields.
        $fileFields = [
            'logo_web',
            'logo_mobile',
            'favicon',
            'og_image_web',
            'og_image_mobile',
        ];

        $files = [];

        foreach ($fileFields as $field) {
            if ($request->hasFile($field)) {
                $files[$field] = $request->file($field);
            }

            unset($validated[$field]);
        }

        $settings = $this->settingService->updateSettings(
            $validated,
            $files
        );

        return $this->apiResponse->success(
            'Website settings updated successfully.',
            new WebsiteSettingResource($settings)
        );
    }

}
