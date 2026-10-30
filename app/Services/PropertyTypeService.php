<?php

namespace App\Services;

use App\Models\PropertyType;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class PropertyTypeService
{
    public function getActiveTypes(array $filters = []): LengthAwarePaginator
    {
        $query = PropertyType::query()
            ->where('is_active', true)
            ->whereNull('parent_id')
            ->orderBy('sort_order')
            ->orderBy('name');

        if (!empty($filters['search'])) {
            $search = $filters['search'];

            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
            });
        }

        if (isset($filters['featured'])) {
            $query->where('is_featured', $filters['featured']);
        }

        return $query->paginate(
            min((int) ($filters['per_page'] ?? 10), 100)
        );
    }

    public function getActiveType(int $id): PropertyType
    {
        return PropertyType::query()
            ->where('is_active', true)
            ->with(['children' => function ($query) {
                $query->where('is_active', true);
            }])
            ->findOrFail($id);
    }

    public function getAllTypes(array $filters = []): LengthAwarePaginator
    {
        $query = PropertyType::query()
            ->with('parent:id,name')
            ->withCount('properties')
            ->orderBy('sort_order')
            ->orderBy('name');

        if (isset($filters['is_active'])) {
            $query->where('is_active', $filters['is_active']);
        }

        if (!empty($filters['search'])) {
            $search = $filters['search'];

            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('code', 'like', "%{$search}%")
                    ->orWhere('slug', 'like', "%{$search}%");
            });
        }

        return $query->paginate(
            min((int) ($filters['per_page'] ?? 15), 100)
        );
    }

    public function create(array $data): PropertyType
    {
        return DB::transaction(function () use ($data) {
            $data['slug'] = $data['slug']
                ?? Str::slug($data['name']);

            return PropertyType::create($data);
        });
    }

    public function update(
        PropertyType $propertyType,
        array $data
    ): PropertyType {
        return DB::transaction(function () use ($propertyType, $data) {
            if (isset($data['name']) && !isset($data['slug'])) {
                $data['slug'] = Str::slug($data['name']);
            }

            $propertyType->update($data);

            return $propertyType->fresh();
        });
    }

    public function delete(PropertyType $propertyType): void
    {
        if ($propertyType->properties()->exists()) {
            throw ValidationException::withMessages([
                'property_type' => [
                    'This category has properties. Reassign those properties before deleting it.',
                ],
            ]);
        }

        if ($propertyType->children()->exists()) {
            throw ValidationException::withMessages([
                'property_type' => [
                    'This category has child categories. Remove or reassign them before deleting it.',
                ],
            ]);
        }

        $propertyType->delete();
    }

    public function changeStatus(
        PropertyType $propertyType,
        bool $isActive
    ): PropertyType {
        $propertyType->update(['is_active' => $isActive]);

        return $propertyType->fresh();
    }
}
