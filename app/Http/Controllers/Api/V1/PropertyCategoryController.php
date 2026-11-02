<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\PropertyCategoryResource;
use App\Models\PropertyCategory;
use App\Models\PropertyCategoryMenu;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class PropertyCategoryController extends Controller
{
    /**
     * Get all active property categories.
     */
    public function index(Request $request)
    {
        $request->validate([
            'menu_type' => 'nullable|in:buy,rent,land',
        ]);

        $query = PropertyCategory::query()
            ->whereNull('parent_id')
            ->where('is_active', true)
            ->with([
                'children' => function ($query) {
                    $query->where('is_active', true)
                        ->orderBy('sort_order');
                }
            ])
            ->orderBy('sort_order');

        // Filter categories according to menu type.
        if ($request->filled('menu_type')) {

            $categoryIds = PropertyCategoryMenu::where(
                'menu_type',
                $request->menu_type
            )
                ->where('is_active', true)
                ->pluck('property_category_id');

            $query->whereIn('id', $categoryIds);
        }

        $categories = $query->get();

        return response()->json([
            'success' => true,
            'message' => 'Property categories retrieved successfully.',
            'data' => PropertyCategoryResource::collection($categories),
        ], 200);
    }

    
    /**
     * Get a single property category by slug.
     */
    public function show(string $slug)
    {
        $category = PropertyCategory::query()
            ->where('slug', $slug)
            ->where('is_active', true)
            ->with([
                'parent' => function ($query) {
                    $query->where('is_active', true);
                },
                'children' => function ($query) {
                    $query->where('is_active', true)
                        ->orderBy('sort_order');
                },
            ])
            ->first();

        if (!$category) {
            return response()->json([
                'success' => false,
                'message' => 'Property category not found.',
                'data' => null,
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'Property category retrieved successfully.',
            'data' => new PropertyCategoryResource($category),
        ], 200);
    }
    
    /**
     * Admin:  property category 
     */
    public function adminIndex(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'search' => 'nullable|string|max:150',
            'status' => 'nullable|in:active,inactive',
            'parent_id' => 'nullable|integer|exists:property_categories,id',
            'per_page' => 'nullable|integer|min:1|max:100',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed.',
                'errors' => $validator->errors(),
            ], 422);
        }

        $query = PropertyCategory::query()
            ->with([
                'parent:id,name,slug',
            ])
            ->withCount([
                'children',
                'properties',
            ]);

        // Search by category name or slug.
        if ($request->filled('search')) {
            $search = $request->search;

            $query->where(function ($q) use ($search) {
                $q->where('name', 'LIKE', "%{$search}%")
                    ->orWhere('slug', 'LIKE', "%{$search}%");
            });
        }

        // Filter by active/inactive status.
        if ($request->filled('status')) {
            $query->where(
                'is_active',
                $request->status === 'active'
            );
        }

        // Filter by parent category.
        if ($request->filled('parent_id')) {
            $query->where('parent_id', $request->parent_id);
        }

        $categories = $query
            ->orderBy('sort_order')
            ->orderBy('id', 'desc')
            ->paginate($request->input('per_page', 15));

        return response()->json([
            'success' => true,
            'message' => 'Admin property categories retrieved successfully.',
            'data' => PropertyCategoryResource::collection(
                $categories->getCollection()
            ),
            'pagination' => [
                'current_page' => $categories->currentPage(),
                'last_page' => $categories->lastPage(),
                'per_page' => $categories->perPage(),
                'total' => $categories->total(),
                'from' => $categories->firstItem(),
                'to' => $categories->lastItem(),
            ],
        ], 200);
    }



    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'parent_id' => [
                'nullable',
                'integer',
                'exists:property_categories,id',
            ],
            'name' => [
                'required',
                'string',
                'max:150',
            ],
            'slug' => [
                'nullable',
                'string',
                'max:180',
                'alpha_dash',
                'unique:property_categories,slug',
            ],
            'description' => [
                'nullable',
                'string',
            ],
            'icon' => [
                'nullable',
                'string',
                'max:255',
            ],
            'image' => [
                'nullable',
                'string',
                'max:255',
            ],
            'meta_title' => [
                'nullable',
                'string',
                'max:255',
            ],
            'meta_description' => [
                'nullable',
                'string',
            ],
            'sort_order' => [
                'nullable',
                'integer',
                'min:0',
            ],
            'is_active' => [
                'nullable',
                'boolean',
            ],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed.',
                'errors' => $validator->errors(),
            ], 422);
        }

        $validated = $validator->validated();

        // Validate that the selected parent category is active.
        if (!empty($validated['parent_id'])) {
            $parent = PropertyCategory::where('id', $validated['parent_id'])
                ->where('is_active', true)
                ->first();

            if (!$parent) {
                return response()->json([
                    'success' => false,
                    'message' => 'Selected parent category is inactive or does not exist.',
                    'data' => null,
                ], 422);
            }
        }

        // Generate slug automatically if not provided.
        $slug = !empty($validated['slug'])
            ? Str::slug($validated['slug'])
            : Str::slug($validated['name']);

        // Ensure the generated slug is unique.
        if (PropertyCategory::where('slug', $slug)->exists()) {
            return response()->json([
                'success' => false,
                'message' => 'This category slug already exists. Please provide a unique slug.',
                'data' => null,
            ], 422);
        }

        $category = PropertyCategory::create([
            'parent_id' => $validated['parent_id'] ?? null,
            'name' => $validated['name'],
            'slug' => $slug,
            'description' => $validated['description'] ?? null,
            'icon' => $validated['icon'] ?? null,
            'image' => $validated['image'] ?? null,
            'meta_title' => $validated['meta_title'] ?? $validated['name'],
            'meta_description' => $validated['meta_description'] ?? null,
            'sort_order' => $validated['sort_order'] ?? 0,
            'is_active' => $validated['is_active'] ?? true,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Property category created successfully.',
            'data' => new PropertyCategoryResource($category),
        ], 201);
    }

  
    public function edit($id)
    {
        $category = PropertyCategory::with('parent')
            ->find($id);

        if (!$category) {
            return response()->json([
                'success' => false,
                'message' => 'Property category not found.',
                'data' => null,
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'Property category details retrieved successfully.',
            'data' => new PropertyCategoryResource($category),
        ], 200);
    }


    public function update(Request $request, $id)
    {
        $category = PropertyCategory::find($id);

        if (!$category) {
            return response()->json([
                'success' => false,
                'message' => 'Property category not found.',
                'data' => null,
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'parent_id' => [
                'sometimes',
                'nullable',
                'integer',
                'exists:property_categories,id',
            ],
            'name' => [
                'sometimes',
                'required',
                'string',
                'max:150',
            ],
            'slug' => [
                'sometimes',
                'nullable',
                'string',
                'max:180',
                'alpha_dash',
            ],
            'description' => ['sometimes', 'nullable', 'string'],
            'icon' => ['sometimes', 'nullable', 'string', 'max:255'],
            'image' => ['sometimes', 'nullable', 'string', 'max:255'],
            'meta_title' => ['sometimes', 'nullable', 'string', 'max:255'],
            'meta_description' => ['sometimes', 'nullable', 'string'],
            'sort_order' => ['sometimes', 'integer', 'min:0'],
            'is_active' => ['sometimes', 'boolean'],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed.',
                'errors' => $validator->errors(),
            ], 422);
        }

        $validated = $validator->validated();

        // Prevent a category from becoming its own parent.
        if (array_key_exists('parent_id', $validated)) {
            if ((int) $validated['parent_id'] === (int) $category->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'A category cannot be its own parent.',
                    'data' => null,
                ], 422);
            }

            if ($validated['parent_id'] !== null) {
                $parent = PropertyCategory::where('id', $validated['parent_id'])
                    ->where('is_active', true)
                    ->first();

                if (!$parent) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Selected parent category is inactive or does not exist.',
                        'data' => null,
                    ], 422);
                }

                // Prevent moving a category beneath one of its descendants.
                $descendantIds = $this->getDescendantIds($category);

                if (in_array((int) $validated['parent_id'], $descendantIds, true)) {
                    return response()->json([
                        'success' => false,
                        'message' => 'A category cannot be moved under one of its own subcategories.',
                        'data' => null,
                    ], 422);
                }
            }
        }

        // Generate or normalize slug when supplied or when name changes.
        if (array_key_exists('slug', $validated) || array_key_exists('name', $validated)) {
            $slugInput = $validated['slug'] ?? $validated['name'] ?? $category->name;
            $slug = Str::slug($slugInput);

            $slugExists = PropertyCategory::where('slug', $slug)
                ->where('id', '!=', $category->id)
                ->exists();

            if ($slugExists) {
                return response()->json([
                    'success' => false,
                    'message' => 'This category slug already exists.',
                    'data' => null,
                ], 422);
            }

            $validated['slug'] = $slug;
        }

        $category->update($validated);

        $category->load(['parent', 'children']);

        return response()->json([
            'success' => true,
            'message' => 'Property category updated successfully.',
            'data' => new PropertyCategoryResource($category),
        ], 200);
    }

    /**
     * Get all descendant category IDs.
     */
    private function getDescendantIds(PropertyCategory $category): array
    {
        $ids = [];

        $category->loadMissing('children');

        foreach ($category->children as $child) {
            $ids[] = (int) $child->id;

            $ids = array_merge(
                $ids,
                $this->getDescendantIds($child)
            );
        }

        return $ids;
    }


    
   
    public function destroy($id)
    {
        $category = PropertyCategory::withCount([
            'children',
            'properties'
        ])->find($id);

        if (!$category) {
            return response()->json([
                'success' => false,
                'message' => 'Property category not found.',
                'data' => null,
            ], 404);
        }

        if ($category->children_count > 0) {
            return response()->json([
                'success' => false,
                'message' => 'This category cannot be deleted because it has subcategories.',
                'data' => null,
            ], 422);
        }

        if ($category->properties_count > 0) {
            return response()->json([
                'success' => false,
                'message' => 'This category cannot be deleted because properties are assigned to it.',
                'data' => null,
            ], 422);
        }

        $category->delete();

        return response()->json([
            'success' => true,
            'message' => 'Property category deleted successfully.',
            'data' => null,
        ], 200);
    }




}
