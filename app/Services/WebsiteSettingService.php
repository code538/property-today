<?php

namespace App\Services;

use App\Models\WebsiteSetting;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Throwable;

class WebsiteSettingService
{
    public function getSettings(): WebsiteSetting
    {
        return WebsiteSetting::firstOrCreate(
            ['id' => 1],
            ['company_name' => 'PropertyHub']
        );
    }

    public function updateSettings(
        array $data,
        array $files = []
    ): WebsiteSetting {
        $settings = $this->getSettings();

        $disk = Storage::disk('public');

        $imageMap = [
            'logo_web' => 'logo_web_path',
            'logo_mobile' => 'logo_mobile_path',
            'favicon' => 'favicon_path',
            'og_image_web' => 'og_image_web_path',
            'og_image_mobile' => 'og_image_mobile_path',
        ];

        $newPaths = [];
        $oldPaths = [];

        try {
            // Store uploaded files and prepare their database paths.
            foreach ($imageMap as $input => $column) {
                if (! isset($files[$input])) {
                    continue;
                }

                /** @var UploadedFile $file */
                $file = $files[$input];

                $newPath = $file->store(
                    'website-settings',
                    'public'
                );

                if (! $newPath) {
                    throw new \RuntimeException(
                        'Unable to store uploaded image.'
                    );
                }

                $newPaths[] = $newPath;

                $oldPath = $settings->{$column};

                if ($oldPath) {
                    $oldPaths[] = $oldPath;
                }

                $data[$column] = $newPath;
            }

            DB::transaction(function () use ($settings, $data) {
                $settings->update($data);
            });
        } catch (Throwable $exception) {
            // Remove newly uploaded files if the update fails.
            foreach ($newPaths as $path) {
                $disk->delete($path);
            }

            throw $exception;
        }

        // Delete replaced images only after the database update succeeds.
        foreach (array_unique($oldPaths) as $oldPath) {
            if (! in_array($oldPath, $newPaths, true)) {
                $disk->delete($oldPath);
            }
        }

        return $settings->refresh();
    }
}
