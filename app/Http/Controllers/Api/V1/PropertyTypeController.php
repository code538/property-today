<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\PropertyTypeResource;
use App\Models\PropertyType;
use App\Services\ApiResponseService;
use App\Services\PropertyTypeService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class PropertyTypeController extends Controller
{
    public function __construct(
        protected PropertyTypeService $propertyTypeService,
        protected ApiResponseService $apiResponse
    ) {}

    // Public: list active categories
    public function index(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'search' => ['nullable', 'string', 'max:100'],
            'featured' => ['sometimes', 'boolean'],
            'per_page' => ['sometimes', 'integer', 'min:1', 'max:100'],
        ]);

        if ($validator->fails()) {
            return $this->apiResponse->error(
                'Validation failed.',
                $validator->errors(),
                422
            );
        }

        $types = $this->propertyTypeService->getActiveTypes(
            $validator->validated()
        );

        return $this->apiResponse->success(
            'Property types retrieved successfully.',
            [
                'property_types' => PropertyTypeResource::collection(
                    $types->getCollection()
                ),
                'pagination' => [
                    'current_page' => $types->currentPage(),
                    'last_page' => $types->lastPage(),
                    'per_page' => $types->perPage(),
                    'total' => $types->total(),
                ],
            ]
        );
    }

    // Public: show a category
    public function show(int $id)
    {
        $type = $this->propertyTypeService->getActiveType($id);

        return $this->apiResponse->success(
            'Property type retrieved successfully.',
            [
                'property_type' => new PropertyTypeResource($type),
                'children' => PropertyTypeResource::collection(
                    $type->children
                ),
            ]
        );
    }

    // Admin: list all categories
    public function adminIndex(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'search' => ['nullable', 'string', 'max:100'],
            'is_active' => ['sometimes', 'boolean'],
            'per_page' => ['sometimes', 'integer', 'min:1', 'max:100'],
        ]);

        if ($validator->fails()) {
            return $this->apiResponse->error(
                'Validation failed.',
                $validator->errors(),
                422
            );
        }

        $types = $this->propertyTypeService->getAllTypes(
            $validator->validated()
        );

        return $this->apiResponse->success(
            'Property types retrieved successfully.',
            [
                'property_types' => PropertyTypeResource::collection(
                    $types->getCollection()
                ),
                'pagination' => [
                    'current_page' => $types->currentPage(),
                    'last_page' => $types->lastPage(),
                    'per_page' => $types->perPage(),
                    'total' => $types->total(),
                ],
            ]
        );
    }

    // Admin: create a category
    public function store(Request $request)
    {   
        $validator = Validator::make($request->all(), [
            'name' => ['required', 'string', 'max:100'],
            'slug' => ['nullable', 'string', 'max:120', 'alpha_dash', 'unique:property_types,slug'],
            'code' => ['required', 'string', 'max:50', 'alpha_dash:ascii', 'unique:property_types,code'],
            'description' => ['nullable', 'string'],
            'parent_id' => [
                'nullable',
                'integer',
                'exists:property_types,id',
            ],
            'icon' => ['nullable', 'string', 'max:255'],
            'image' => ['nullable', 'string', 'max:255'],
            'image_alt' => ['nullable', 'string', 'max:255'],
            'sort_order' => ['sometimes', 'integer', 'min:0'],
            'supports_sale' => ['sometimes', 'boolean'],
            'supports_rent' => ['sometimes', 'boolean'],
            'supports_lease' => ['sometimes', 'boolean'],
            'supports_bedrooms' => ['sometimes', 'boolean'],
            'supports_bathrooms' => ['sometimes', 'boolean'],
            'supports_area' => ['sometimes', 'boolean'],
            'supports_plot_area' => ['sometimes', 'boolean'],
            'supports_furnishing' => ['sometimes', 'boolean'],
            'supports_parking' => ['sometimes', 'boolean'],
            'form_schema' => ['nullable', 'array'],
            'meta_title' => ['nullable', 'string', 'max:255'],
            'meta_description' => ['nullable', 'string', 'max:500'],
            'is_active' => ['sometimes', 'boolean'],
            'is_featured' => ['sometimes', 'boolean'],
        ]);

        if ($validator->fails()) {
            return $this->apiResponse->error(
                'Validation failed.',
                $validator->errors(),
                422
            );
        }

        $type = $this->propertyTypeService->create(
            $validator->validated()
        );

        return $this->apiResponse->success(
            'Property type created successfully.',
            ['property_type' => new PropertyTypeResource($type)],
            201
        );
    }

    // Admin: update a category
    public function update(Request $request, int $id)
    {
        $type = PropertyType::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'name' => ['sometimes', 'required', 'string', 'max:100'],
            'slug' => [
                'sometimes',
                'required',
                'string',
                'max:120',
                'alpha_dash',
                Rule::unique('property_types', 'slug')->ignore($type->id),
            ],
            'code' => [
                'sometimes',
                'required',
                'string',
                'max:50',
                'alpha_dash:ascii',
                Rule::unique('property_types', 'code')->ignore($type->id),
            ],
            'description' => ['sometimes', 'nullable', 'string'],
            'parent_id' => [
                'sometimes',
                'nullable',
                'integer',
                Rule::exists('property_types', 'id'),
                Rule::notIn([$type->id]),
            ],
            'icon' => ['sometimes', 'nullable', 'string', 'max:255'],
            'image' => ['sometimes', 'nullable', 'string', 'max:255'],
            'image_alt' => ['sometimes', 'nullable', 'string', 'max:255'],
            'sort_order' => ['sometimes', 'integer', 'min:0'],
            'supports_sale' => ['sometimes', 'boolean'],
            'supports_rent' => ['sometimes', 'boolean'],
            'supports_lease' => ['sometimes', 'boolean'],
            'supports_bedrooms' => ['sometimes', 'boolean'],
            'supports_bathrooms' => ['sometimes', 'boolean'],
            'supports_area' => ['sometimes', 'boolean'],
            'supports_plot_area' => ['sometimes', 'boolean'],
            'supports_furnishing' => ['sometimes', 'boolean'],
            'supports_parking' => ['sometimes', 'boolean'],
            'form_schema' => ['sometimes', 'nullable', 'array'],
            'meta_title' => ['sometimes', 'nullable', 'string', 'max:255'],
            'meta_description' => ['sometimes', 'nullable', 'string', 'max:500'],
            'is_active' => ['sometimes', 'boolean'],
            'is_featured' => ['sometimes', 'boolean'],
        ]);

        if ($validator->fails()) {
            return $this->apiResponse->error(
                'Validation failed.',
                $validator->errors(),
                422
            );
        }

        $data = $validator->validated();

        if (isset($data['parent_id'])) {
            $parentId = $data['parent_id'];

            if ($parentId !== null) {
                $parent = PropertyType::findOrFail($parentId);

                if ($parent->parent_id !== null) {
                    return $this->apiResponse->error(
                        'Only one level of child categories is supported.',
                        null,
                        422
                    );
                }

                if ($parent->id === $type->id) {
                    return $this->apiResponse->error(
                        'A category cannot be its own parent.',
                        null,
                        422
                    );
                }
            }
        }

        $updated = $this->propertyTypeService->update(
            $type,
            $data
        );

        return $this->apiResponse->success(
            'Property type updated successfully.',
            ['property_type' => new PropertyTypeResource($updated)]
        );
    }

    // Admin: activate or deactivate a category
    public function changeStatus(Request $request, int $id)
    {
        $validator = Validator::make($request->all(), [
            'is_active' => ['required', 'boolean'],
        ]);

        if ($validator->fails()) {
            return $this->apiResponse->error(
                'Validation failed.',
                $validator->errors(),
                422
            );
        }

        $type = PropertyType::findOrFail($id);

        $updated = $this->propertyTypeService->changeStatus(
            $type,
            $validator->validated()['is_active']
        );

        return $this->apiResponse->success(
            'Property type status updated successfully.',
            ['property_type' => new PropertyTypeResource($updated)]
        );
    }

    // Admin: soft-delete a category
    public function destroy(int $id)
    {
        $type = PropertyType::findOrFail($id);

        try {
            $this->propertyTypeService->delete($type);
        } catch (ValidationException $e) {
            return $this->apiResponse->error(
                'Unable to delete property type.',
                $e->errors(),
                422
            );
        }

        return $this->apiResponse->success(
            'Property type deleted successfully.'
        );
    }
}
