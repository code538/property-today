<?php

namespace App\Services;

use App\Models\Property;
use App\Models\PropertyMedia;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Throwable;

class PropertyService
{
    /**
     * Create property with related records.
     */
    public function createProperty(array $data, Request $request, $user): Property
    {
        DB::beginTransaction();

        try {
            $propertyData = collect($data)->except([
                'location',
                'details',
                'amenity_ids',
                'images',
                'videos',
            ])->toArray();

            $propertyData['user_id'] = $user->id;
            $propertyData['slug'] = $this->generateSlug($data['title']);
            $propertyData['status'] = 'pending';
            $propertyData['price_negotiable'] = $data['price_negotiable'] ?? false;
            $propertyData['area_unit'] = $data['area_unit'] ?? 'sqft';
            $propertyData['meta_title'] = $data['meta_title'] ?? $data['title'];

            $property = Property::create($propertyData);

            // Save location.
            if (!empty($data['location'])) {
                $property->location()->create($data['location']);
            }

            // Save additional details.
            if (!empty($data['details'])) {
                $property->details()->create($data['details']);
            }

            // Save amenities.
            if (!empty($data['amenity_ids'])) {
                $property->amenities()->sync($data['amenity_ids']);
            }

            // Upload images and videos.
            $this->uploadMedia($request, $property);

            DB::commit();

            return $property->load([
                'category',
                'media',
                'location',
                'amenities',
                'details',
                'user',
            ]);

        } catch (Throwable $e) {
            DB::rollBack();

            // Remove uploaded files if database operation fails.
            if (isset($property)) {
                $this->deleteMediaFiles($property);
            }

            throw $e;
        }
    }

    /**
     * Update property with related records.
     */
    public function updateProperty(
        Property $property,
        array $data,
        Request $request
    ): Property {
        DB::beginTransaction();

        try {
            $propertyData = collect($data)->except([
                'location',
                'details',
                'amenity_ids',
                'images',
                'videos',
            ])->toArray();

            if (isset($data['title'])) {
                $propertyData['slug'] = $this->generateSlug(
                    $data['title'],
                    $property->id
                );
            }

            if (!empty($propertyData)) {
                $propertyData['status'] = 'pending';
                $propertyData['published_at'] = null;

                $property->update($propertyData);
            }

            // Update location.
            if (isset($data['location'])) {
                $property->location()->updateOrCreate(
                    ['property_id' => $property->id],
                    $data['location']
                );
            }

            // Update additional details.
            if (isset($data['details'])) {
                $property->details()->updateOrCreate(
                    ['property_id' => $property->id],
                    $data['details']
                );
            }

            // Update amenities.
            if (isset($data['amenity_ids'])) {
                $property->amenities()->sync($data['amenity_ids']);
            }

            // Upload new media.
            $this->uploadMedia($request, $property);

            DB::commit();

            return $property->fresh()->load([
                'category',
                'media',
                'location',
                'amenities',
                'details',
                'user',
            ]);

        } catch (Throwable $e) {
            DB::rollBack();

            throw $e;
        }
    }

    /**
     * Delete property and its media files.
     */
    public function deleteProperty(Property $property): bool
    {
        DB::beginTransaction();

        try {
            $this->deleteMediaFiles($property);

            // Soft delete property.
            $property->delete();

            DB::commit();

            return true;

        } catch (Throwable $e) {
            DB::rollBack();

            throw $e;
        }
    }

    /**
     * Generate unique property slug.
     */
    public function generateSlug(string $title, ?int $ignoreId = null): string
    {
        $baseSlug = Str::slug($title);

        if ($baseSlug === '') {
            $baseSlug = 'property';
        }

        $slug = $baseSlug;
        $counter = 1;

        while (
            Property::where('slug', $slug)
                ->when($ignoreId, function ($query) use ($ignoreId) {
                    $query->where('id', '!=', $ignoreId);
                })
                ->exists()
        ) {
            $slug = $baseSlug . '-' . $counter;
            $counter++;
        }

        return $slug;
    }

    /**
     * Upload property images and videos.
     */
    public function uploadMedia(Request $request, Property $property): void
    {
        $images = $request->file('images', []);

        foreach ($images as $index => $image) {

            $webPath = Storage::disk('public')->putFile(
                "properties/{$property->id}/web",
                $image['web_image']
            );

            $mobilePath = null;

            if (!empty($image['mobile_image'])) {
                $mobilePath = Storage::disk('public')->putFile(
                    "properties/{$property->id}/mobile",
                    $image['mobile_image']
                );
            }

            PropertyMedia::create([
                'property_id' => $property->id,
                'media_type' => 'image',
                'file_path' => $webPath,
                'mobile_file_path' => $mobilePath,
                'image_alt' => $image['image_alt'] ?? null,
                'mime_type' => $image['web_image']->getMimeType(),
                'file_size' => $image['web_image']->getSize(),
                'is_primary' => !$property->media()
                    ->where('media_type', 'image')
                    ->exists() && $index === 0,
                'sort_order' => $index,
            ]);
        }

        // Upload videos.
        $videos = $request->file('videos', []);

        foreach ($videos as $index => $video) {

            $videoPath = Storage::disk('public')->putFile(
                "properties/{$property->id}/videos",
                $video
            );

            PropertyMedia::create([
                'property_id' => $property->id,
                'media_type' => 'video',
                'file_path' => $videoPath,
                'mobile_file_path' => null,
                'image_alt' => null,
                'mime_type' => $video->getMimeType(),
                'file_size' => $video->getSize(),
                'is_primary' => false,
                'sort_order' => $index,
            ]);
        }
    }

    /**
     * Delete all property media files from storage.
     */
    public function deleteMediaFiles(Property $property): void
    {
        $mediaItems = $property->media()->get();

        foreach ($mediaItems as $media) {

            if ($media->file_path) {
                Storage::disk('public')->delete($media->file_path);
            }

            if ($media->mobile_file_path) {
                Storage::disk('public')->delete($media->mobile_file_path);
            }

            $media->delete();
        }
    }
}
