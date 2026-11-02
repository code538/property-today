<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\PropertyCategoryMenuResource;
use App\Models\PropertyCategoryMenu;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\PropertyCategory;

class PropertyCategoryMenuController extends Controller
{
    /**
     * Public: Get all active property category menus.
     */
    public function index()
    {
        $menuTypes = ['buy', 'rent', 'land'];

        $menus = PropertyCategoryMenu::query()
            ->where('is_active', true)
            ->whereHas('category', function ($query) {
                $query->where('is_active', true);
            })
            ->with([
                'category' => function ($query) {
                    $query->where('is_active', true)
                        ->with([
                            'children' => function ($childQuery) {
                                $childQuery->where('is_active', true)
                                    ->orderBy('sort_order');
                            }
                        ]);
                }
            ])
            ->orderBy('sort_order')
            ->get()
            ->groupBy('menu_type');

        $data = [];

        foreach ($menuTypes as $type) {
            $data[$type] = PropertyCategoryMenuResource::collection(
                $menus->get($type, collect())
            );
        }

        return response()->json([
            'success' => true,
            'message' => 'Property category menus retrieved successfully.',
            'data' => $data,
        ], 200);
    }

    
    /**
     * Admin: Get all property category menus.
     */
    public function adminIndex(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'search' => 'nullable|string|max:150',
            'menu_type' => 'nullable|in:buy,rent,land',
            'status' => 'nullable|in:active,inactive',
            'per_page' => 'nullable|integer|min:1|max:100',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed.',
                'errors' => $validator->errors(),
            ], 422);
        }

        $query = PropertyCategoryMenu::query()
            ->with([
                'category' => function ($query) {
                    $query->with('parent');
                }
            ]);

        // Search by category name or menu label.
        if ($request->filled('search')) {
            $search = $request->search;

            $query->where(function ($q) use ($search) {
                $q->where('menu_label', 'LIKE', "%{$search}%")
                    ->orWhereHas('category', function ($categoryQuery) use ($search) {
                        $categoryQuery->where('name', 'LIKE', "%{$search}%")
                            ->orWhere('slug', 'LIKE', "%{$search}%");
                    });
            });
        }

        // Filter by menu type.
        if ($request->filled('menu_type')) {
            $query->where('menu_type', $request->menu_type);
        }

        // Filter by active/inactive status.
        if ($request->filled('status')) {
            $query->where(
                'is_active',
                $request->status === 'active'
            );
        }

        $menus = $query
            ->orderBy('menu_type')
            ->orderBy('sort_order')
            ->orderByDesc('id')
            ->paginate($request->input('per_page', 15));

        return response()->json([
            'success' => true,
            'message' => 'Property category menus retrieved successfully.',
            'data' => PropertyCategoryMenuResource::collection(
                $menus->getCollection()
            ),
            'pagination' => [
                'current_page' => $menus->currentPage(),
                'last_page' => $menus->lastPage(),
                'per_page' => $menus->perPage(),
                'total' => $menus->total(),
                'from' => $menus->firstItem(),
                'to' => $menus->lastItem(),
            ],
        ], 200);
    }

    
    /**
     * Admin: Create property category menu.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'property_category_id' => [
                'required',
                'integer',
                'exists:property_categories,id',
            ],
            'menu_type' => [
                'required',
                'in:buy,rent,land',
            ],
            'menu_label' => [
                'nullable',
                'string',
                'max:150',
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

        // Check whether the category is active.
        $category = PropertyCategory::where(
            'id',
            $validated['property_category_id']
        )
            ->where('is_active', true)
            ->first();

        if (!$category) {
            return response()->json([
                'success' => false,
                'message' => 'Selected property category is inactive or not found.',
                'data' => null,
            ], 422);
        }

        // Prevent duplicate category assignment to the same menu.
        $exists = PropertyCategoryMenu::where(
            'property_category_id',
            $validated['property_category_id']
        )
            ->where('menu_type', $validated['menu_type'])
            ->exists();

        if ($exists) {
            return response()->json([
                'success' => false,
                'message' => 'This category is already assigned to this menu.',
                'data' => null,
            ], 422);
        }

        $menu = PropertyCategoryMenu::create([
            'property_category_id' => $validated['property_category_id'],
            'menu_type' => $validated['menu_type'],
            'menu_label' => $validated['menu_label'] ?? null,
            'sort_order' => $validated['sort_order'] ?? 0,
            'is_active' => $validated['is_active'] ?? true,
        ]);

        $menu->load('category');

        return response()->json([
            'success' => true,
            'message' => 'Property category menu created successfully.',
            'data' => new PropertyCategoryMenuResource($menu),
        ], 201);
    }

    
    /**
     * Admin: Get category menu details for editing.
     */
    public function edit($id)
    {
        $menu = PropertyCategoryMenu::with([
            'category.parent'
        ])->find($id);

        if (!$menu) {
            return response()->json([
                'success' => false,
                'message' => 'Property category menu not found.',
                'data' => null,
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'Property category menu retrieved successfully.',
            'data' => new PropertyCategoryMenuResource($menu),
        ], 200);
    }


    
    /**
     * Admin: Update property category menu.
     */
    public function update(Request $request, $id)
    {
        $menu = PropertyCategoryMenu::find($id);

        if (!$menu) {
            return response()->json([
                'success' => false,
                'message' => 'Property category menu not found.',
                'data' => null,
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'property_category_id' => [
                'sometimes',
                'required',
                'integer',
                'exists:property_categories,id',
            ],
            'menu_type' => [
                'sometimes',
                'required',
                'in:buy,rent,land',
            ],
            'menu_label' => [
                'sometimes',
                'nullable',
                'string',
                'max:150',
            ],
            'sort_order' => [
                'sometimes',
                'integer',
                'min:0',
            ],
            'is_active' => [
                'sometimes',
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

        $categoryId = $validated['property_category_id']
            ?? $menu->property_category_id;

        $menuType = $validated['menu_type']
            ?? $menu->menu_type;

        // Check selected category is active.
        $category = PropertyCategory::where('id', $categoryId)
            ->where('is_active', true)
            ->first();

        if (!$category) {
            return response()->json([
                'success' => false,
                'message' => 'Selected category is inactive or not found.',
                'data' => null,
            ], 422);
        }

        // Prevent duplicate category assignment in the same menu.
        $duplicate = PropertyCategoryMenu::where(
            'property_category_id',
            $categoryId
        )
            ->where('menu_type', $menuType)
            ->where('id', '!=', $menu->id)
            ->exists();

        if ($duplicate) {
            return response()->json([
                'success' => false,
                'message' => 'This category is already assigned to this menu.',
                'data' => null,
            ], 422);
        }

        $menu->update($validated);

        $menu->load('category.parent');

        return response()->json([
            'success' => true,
            'message' => 'Property category menu updated successfully.',
            'data' => new PropertyCategoryMenuResource($menu),
        ], 200);
    }

    
    /**
     * Admin: Delete property category menu.
     */
    public function destroy($id)
    {
        $menu = PropertyCategoryMenu::find($id);

        if (!$menu) {
            return response()->json([
                'success' => false,
                'message' => 'Property category menu not found.',
                'data' => null,
            ], 404);
        }

        $menu->delete();

        return response()->json([
            'success' => true,
            'message' => 'Property category menu deleted successfully.',
            'data' => null,
        ], 200);
    }


    
    /**
     * Admin: Change property category menu status.
     */
    public function changeStatus(Request $request, $id)
    {
        $menu = PropertyCategoryMenu::find($id);

        if (!$menu) {
            return response()->json([
                'success' => false,
                'message' => 'Property category menu not found.',
                'data' => null,
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'is_active' => [
                'required',
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

        $menu->update([
            'is_active' => $request->boolean('is_active'),
        ]);

        $menu->load('category');

        return response()->json([
            'success' => true,
            'message' => $menu->is_active
                ? 'Property category menu activated successfully.'
                : 'Property category menu deactivated successfully.',
            'data' => new PropertyCategoryMenuResource($menu),
        ], 200);
    }





}
