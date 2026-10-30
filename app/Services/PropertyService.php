<?php

namespace App\Services;

use App\Models\Property;
use App\Models\PropertyImage;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class PropertyService
{
    public function getPublishedProperties(
        array $filters = []
    ): LengthAwarePaginator {
        $query = Property::query()
            ->where('status', 'published')
            ->where(function ($q) {
                $q->whereNull('expires_at')
                    ->orWhere('expires_at', '>', now());
            })
            ->with([
                'propertyType',
                'images',
                'amenities',
                'user:id,name',
            ]);

        if (!empty($filters['search'])) {
            $search = $filters['search'];

            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%")
                    ->orWhere('locality', 'like', "%{$search}%")
                    ->orWhere('city', 'like', "%{$search}%");
            });
        }

        foreach ([
            'property_type_id',
            'listing_type',
            'city',
            'state',
        ] as $field) {
            if (!empty($filters[$field])) {
                $query->where($field, $filters[$field]);
            }
        }

        if (isset($filters['min_price'])) {
            $query->where('price', '>=', $filters['min_price']);
        }

        if (isset($filters['max_price'])) {
            $query->where('price', '<=', $filters['max_price']);
        }

        if (isset($filters['bedrooms'])) {
            $query->where(
                'bedrooms',
                '>=',
                $filters['bedrooms']
            );
        }

        // Nearby search using the Haversine formula.
        if (
            isset($filters['latitude'], $filters['longitude'])
        ) {
            $lat = (float) $filters['latitude'];
            $lng = (float) $filters['longitude'];
            $radius = (float) ($filters['radius_km'] ?? 10);

            $distanceSql = '
                (6371 * ACOS(
                    LEAST(1, GREATEST(-1,
                        COS(RADIANS(?))
                        * COS(RADIANS(latitude))
                        * COS(RADIANS(longitude) - RADIANS(?))
                        + SIN(RADIANS(?))
                        * SIN(RADIANS(latitude))
                    ))
                ))
            ';

            $query->whereNotNull('latitude')
                ->whereNotNull('longitude')
                ->whereRaw(
                    $distanceSql . ' <= ?',
                    [$lat, $lng, $lat, $radius]
                )
                ->select('properties.*')
                ->selectRaw(
                    $distanceSql . ' AS distance_km',
                    [$lat, $lng, $lat]
                )
                ->orderBy('distance_km');
        } else {
            $query->orderByDesc('is_featured')
                ->orderByDesc('published_at');
        }

        return $query->paginate(
            min(max((int) ($filters['per_page'] ?? 12), 1), 100)
        );
    }

    public function getProperty(int $id): Property
    {
        return Property::with([
            'propertyType',
            'images',
            'amenities',
            'user:id,name',
        ])
            ->where('status', 'published')
            ->where(function ($q) {
                $q->whereNull('expires_at')
                    ->orWhere('expires_at', '>', now());
            })
            ->findOrFail($id);
    }

    public function getMyProperties(
        User $user,
        int $perPage = 12
    ): LengthAwarePaginator {
        return Property::where('user_id', $user->id)
            ->with(['propertyType', 'images', 'amenities'])
            ->latest()
            ->paginate(min(max($perPage, 1), 100));
    }

    public function create(
        User $user,
        array $data
    ): Property {
        return DB::transaction(function () use ($user, $data) {
            $this->checkListingLimit($user);

            $images = $data['images'] ?? [];
            $amenityIds = $data['amenity_ids'] ?? [];

            unset($data['images'], $data['amenity_ids']);

            $data['user_id'] = $user->id;

            // Never trust client-supplied moderation fields.
            $data['status'] = 'pending';
            $data['is_featured'] = false;
            $data['approved_by'] = null;
            $data['approved_at'] = null;
            $data['published_at'] = null;

            $data['listing_code'] = 'PT-'
                . now()->format('Y')
                . '-'
                . strtoupper(Str::random(8));

            $data['slug'] = Str::slug($data['title'])
                . '-'
                . Str::lower(Str::random(8));

            $property = Property::create($data);

            $this->storeImages($property, $images);

            $property->amenities()->sync($amenityIds);

            return $property->load([
                'propertyType',
                'images',
                'amenities',
                'user:id,name',
            ]);
        });
    }

    public function update(
        Property $property,
        array $data
    ): Property {
        return DB::transaction(function () use ($property, $data) {
            $images = $data['images'] ?? [];
            $amenityIds = $data['amenity_ids'] ?? null;

            unset($data['images'], $data['amenity_ids']);

            // Clients cannot approve or publish their own listings.
            unset(
                $data['user_id'],
                $data['listing_code'],
                $data['status'],
                $data['approved_by'],
                $data['approved_at'],
                $data['published_at'],
                $data['is_featured'],
                $data['featured_until']
            );

            $property->update($data);

            if (!empty($images)) {
                $this->storeImages($property, $images);
            }

            if ($amenityIds !== null) {
                $property->amenities()->sync($amenityIds);
            }

            return $property->fresh()->load([
                'propertyType',
                'images',
                'amenities',
                'user:id,name',
            ]);
        });
    }

    public function delete(Property $property): void
    {
        DB::transaction(function () use ($property) {
            // Soft deletion preserves the listing and its image records.
            $property->delete();
        });
    }

    private function checkListingLimit(User $user): void
    {
        // Admins are not subject to ordinary listing limits.
        if ($user->role === 'admin') {
            return;
        }

        $limits = [
            'user' => 1,
            'agent' => 5,
        ];

        $limit = $limits[$user->role] ?? 0;

        $count = Property::where('user_id', $user->id)
            ->whereNotIn('status', [
                'archived',
                'sold',
                'rented',
            ])
            ->count();

        if ($count >= $limit) {
            throw ValidationException::withMessages([
                'subscription' => [
                    "Your current listing limit is {$limit}. Upgrade your plan to list more properties.",
                ],
            ]);
        }
    }

    private function storeImages(
        Property $property,
        array $images
    ): void {
        foreach ($images as $index => $image) {
            $webPath = Storage::disk('public')->putFile(
                'properties/images',
                $image['image_web']
            );

            $mobilePath = null;
            $videoPath = null;

            if (!empty($image['image_mobile'])) {
                $mobilePath = Storage::disk('public')->putFile(
                    'properties/images/mobile',
                    $image['image_mobile']
                );
            }

            if (!empty($image['video'])) {
                $videoPath = Storage::disk('public')->putFile(
                    'properties/videos',
                    $image['video']
                );
            }

            PropertyImage::create([
                'property_id' => $property->id,
                'image_web_path' => $webPath,
                'image_mobile_path' => $mobilePath,
                'image_alt' => $image['image_alt'] ?? null,
                'video_path' => $videoPath,
                'sort_order' => $index,
                'is_primary' => $index === 0
                    && !$property->images()->exists(),
            ]);
        }
    }
}
