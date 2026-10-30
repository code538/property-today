<?php

namespace App\Services;

use App\Models\Amenity;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class AmenityService
{
    public function getActiveAmenities(
        ?string $search = null,
        int $perPage = 30
    ): LengthAwarePaginator {
        $query = Amenity::query()
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('name');

        if ($search !== null && $search !== '') {
            $query->where('name', 'like', "%{$search}%");
        }

        return $query->paginate(
            min(max($perPage, 1), 100)
        );
    }
}
