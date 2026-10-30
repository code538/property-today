<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\PropertyResource;
use App\Models\Property;
use App\Models\PropertyType;
use App\Services\ApiResponseService;
use App\Services\PropertyService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class PropertyController extends Controller
{
    public function __construct(
        protected PropertyService $propertyService,
        protected ApiResponseService $apiResponse
    ) {}

    // Public property listing and filters
    public function index(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'search' => ['nullable', 'string', 'max:150'],
            'property_type_id' => [
                'nullable', 'integer',
                'exists:property_types,id',
            ],
            'listing_type' => [
                'nullable',
                Rule::in(['sale', 'rent', 'lease']),
            ],
            'city' => ['nullable', 'string', 'max:100'],
            'state' => ['nullable', 'string', 'max:100'],
            'min_price' => ['nullable', 'numeric', 'min:0'],
            'max_price' => [
                'nullable', 'numeric', 'gte:min_price',
            ],
            'bedrooms' => ['nullable', 'integer', 'min:0'],
            'latitude' => [
                'required_with:longitude',
                'nullable', 'numeric', 'between:-90,90',
            ],
            'longitude' => [
                'required_with:latitude',
                'nullable', 'numeric', 'between:-180,180',
            ],
            'radius_km' => [
                'nullable', 'numeric', 'min:1', 'max:500',
            ],
            'per_page' => [
                'nullable', 'integer', 'min:1', 'max:100',
            ],
        ]);

        if ($validator->fails()) {
            return $this->apiResponse->error(
                'Validation failed.',
                $validator->errors(),
                422
            );
        }

        $properties = $this->propertyService
            ->getPublishedProperties($validator->validated());

        return $this->apiResponse->success(
            'Properties retrieved successfully.',
            [
                'properties' => PropertyResource::collection(
                    $properties->getCollection()
                ),
                'pagination' => [
                    'current_page' => $properties->currentPage(),
                    'last_page' => $properties->lastPage(),
                    'per_page' => $properties->perPage(),
                    'total' => $properties->total(),
                ],
            ]
        );
    }

    // Public: published property details
    public function show(int $id)
    {
        $property = $this->propertyService->getProperty($id);

        return $this->apiResponse->success(
            'Property retrieved successfully.',
            ['property' => new PropertyResource($property)]
        );
    }

    // Authenticated: current user's properties
    public function myProperties(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'per_page' => [
                'nullable', 'integer', 'min:1', 'max:100',
            ],
        ]);

        if ($validator->fails()) {
            return $this->apiResponse->error(
                'Validation failed.',
                $validator->errors(),
                422
            );
        }

        $properties = $this->propertyService->getMyProperties(
            $request->user(),
            (int) $request->input('per_page', 12)
        );

        return $this->apiResponse->success(
            'Your properties retrieved successfully.',
            [
                'properties' => PropertyResource::collection(
                    $properties->getCollection()
                ),
                'pagination' => [
                    'current_page' => $properties->currentPage(),
                    'last_page' => $properties->lastPage(),
                    'per_page' => $properties->perPage(),
                    'total' => $properties->total(),
                ],
            ]
        );
    }

    // Authenticated: create property with images and amenities
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'property_type_id' => [
                'required',
                'integer',
                Rule::exists('property_types', 'id')
                    ->where('is_active', true),
            ],

            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],

            'listing_type' => [
                'required',
                Rule::in(['sale', 'rent', 'lease']),
            ],

            'price' => ['required', 'numeric', 'min:0'],
            'price_negotiable' => ['sometimes', 'boolean'],
            'maintenance_charge' => ['nullable', 'numeric', 'min:0'],
            'security_deposit' => ['nullable', 'numeric', 'min:0'],
            'price_period' => [
                'nullable',
                Rule::in(['daily', 'weekly', 'monthly', 'yearly']),
            ],

            'built_up_area' => ['nullable', 'numeric', 'gt:0'],
            'carpet_area' => ['nullable', 'numeric', 'gt:0'],
            'plot_area' => ['nullable', 'numeric', 'gt:0'],
            'area_unit' => [
                'nullable',
                Rule::in(['sq_ft', 'sq_m', 'sq_yd', 'acre', 'hectare']),
            ],

            'bedrooms' => ['nullable', 'integer', 'min:0'],
            'bathrooms' => ['nullable', 'integer', 'min:0'],
            'balconies' => ['nullable', 'integer', 'min:0'],
            'floor_number' => ['nullable', 'integer', 'min:0'],
            'total_floors' => ['nullable', 'integer', 'min:1'],

            'furnishing_status' => [
                'nullable',
                Rule::in(['furnished', 'semi_furnished', 'unfurnished']),
            ],
            'property_condition' => ['nullable', 'string', 'max:50'],
            'facing_direction' => [
                'nullable',
                Rule::in([
                    'north', 'south', 'east', 'west',
                    'north_east', 'north_west',
                    'south_east', 'south_west',
                ]),
            ],
            'year_built' => [
                'nullable', 'integer', 'min:1800',
                'max:' . (now()->year + 10),
            ],
            'possession_status' => ['nullable', 'string', 'max:50'],

            'total_rooms' => ['nullable', 'integer', 'min:0'],
            'available_rooms' => ['nullable', 'integer', 'min:0'],
            'total_beds' => ['nullable', 'integer', 'min:0'],
            'available_beds' => ['nullable', 'integer', 'min:0'],
            'tenant_preference' => [
                'nullable',
                Rule::in(['any', 'male', 'female', 'family']),
            ],

            'address_line_1' => ['nullable', 'string', 'max:255'],
            'address_line_2' => ['nullable', 'string', 'max:255'],
            'locality' => ['nullable', 'string', 'max:255'],
            'landmark' => ['nullable', 'string', 'max:255'],
            'city' => ['required', 'string', 'max:100'],
            'district' => ['nullable', 'string', 'max:100'],
            'state' => ['required', 'string', 'max:100'],
            'country' => ['nullable', 'string', 'max:100'],
            'postal_code' => ['nullable', 'string', 'max:20'],

            'latitude' => [
                'nullable', 'numeric', 'between:-90,90',
            ],
            'longitude' => [
                'nullable', 'numeric', 'between:-180,180',
            ],

            'contact_name' => ['nullable', 'string', 'max:100'],
            'contact_phone' => ['nullable', 'string', 'max:30'],
            'show_owner_name' => ['sometimes', 'boolean'],

            // One or more image entries.
            'images' => ['required', 'array', 'min:1', 'max:30'],
            'images.*.image_web' => [
                'required', 'image', 'mimes:jpg,jpeg,png,webp',
                'max:8192',
            ],
            'images.*.image_mobile' => [
                'nullable', 'image', 'mimes:jpg,jpeg,png,webp',
                'max:8192',
            ],
            'images.*.image_alt' => [
                'nullable', 'string', 'max:255',
            ],
            'images.*.video' => [
                'nullable', 'file',
                'mimes:mp4,mov,webm',
                'max:51200',
            ],

            'amenity_ids' => ['sometimes', 'array', 'max:100'],
            'amenity_ids.*' => [
                'integer',
                'distinct',
                Rule::exists('amenities', 'id')
                    ->where('is_active', true),
            ],
        ]);

        if ($validator->fails()) {
            return $this->apiResponse->error(
                'Validation failed.',
                $validator->errors(),
                422
            );
        }

        try {
            $property = $this->propertyService->create(
                $request->user(),
                $validator->validated()
            );
        } catch (ValidationException $e) {
            return $this->apiResponse->error(
                'Unable to create property.',
                $e->errors(),
                422
            );
        }

        return $this->apiResponse->success(
            'Property submitted successfully. Awaiting approval.',
            ['property' => new PropertyResource($property)],
            201
        );
    }

    // Authenticated: update own property; admin can update any
    public function update(Request $request, int $id)
    {
        $property = Property::findOrFail($id);

        if (
            $request->user()->role !== 'admin'
            && $property->user_id !== $request->user()->id
        ) {
            return $this->apiResponse->error(
                'You are not authorized to update this property.',
                null,
                403
            );
        }

        $validator = Validator::make($request->all(), [
            'property_type_id' => [
                'sometimes', 'required', 'integer',
                Rule::exists('property_types', 'id')
                    ->where('is_active', true),
            ],
            'title' => ['sometimes', 'required', 'string', 'max:255'],
            'description' => ['sometimes', 'nullable', 'string'],
            'listing_type' => [
                'sometimes', 'required',
                Rule::in(['sale', 'rent', 'lease']),
            ],
            'price' => ['sometimes', 'required', 'numeric', 'min:0'],
            'price_negotiable' => ['sometimes', 'boolean'],
            'maintenance_charge' => ['sometimes', 'nullable', 'numeric', 'min:0'],
            'security_deposit' => ['sometimes', 'nullable', 'numeric', 'min:0'],
            'price_period' => [
                'sometimes', 'nullable',
                Rule::in(['daily', 'weekly', 'monthly', 'yearly']),
            ],

            'built_up_area' => ['sometimes', 'nullable', 'numeric', 'gt:0'],
            'carpet_area' => ['sometimes', 'nullable', 'numeric', 'gt:0'],
            'plot_area' => ['sometimes', 'nullable', 'numeric', 'gt:0'],
            'area_unit' => [
                'sometimes', 'required',
                Rule::in(['sq_ft', 'sq_m', 'sq_yd', 'acre', 'hectare']),
            ],
            'bedrooms' => ['sometimes', 'nullable', 'integer', 'min:0'],
            'bathrooms' => ['sometimes', 'nullable', 'integer', 'min:0'],
            'balconies' => ['sometimes', 'nullable', 'integer', 'min:0'],
            'floor_number' => ['sometimes', 'nullable', 'integer', 'min:0'],
            'total_floors' => ['sometimes', 'nullable', 'integer', 'min:1'],

            'furnishing_status' => [
                'sometimes', 'nullable',
                Rule::in(['furnished', 'semi_furnished', 'unfurnished']),
            ],
            'property_condition' => ['sometimes', 'nullable', 'string', 'max:50'],
            'facing_direction' => [
                'sometimes', 'nullable',
                Rule::in([
                    'north', 'south', 'east', 'west',
                    'north_east', 'north_west',
                    'south_east', 'south_west',
                ]),
            ],
            'year_built' => [
                'sometimes', 'nullable', 'integer', 'min:1800',
                'max:' . (now()->year + 10),
            ],
            'possession_status' => ['sometimes', 'nullable', 'string', 'max:50'],

            'total_rooms' => ['sometimes', 'nullable', 'integer', 'min:0'],
            'available_rooms' => ['sometimes', 'nullable', 'integer', 'min:0'],
            'total_beds' => ['sometimes', 'nullable', 'integer', 'min:0'],
            'available_beds' => ['sometimes', 'nullable', 'integer', 'min:0'],
            'tenant_preference' => [
                'sometimes', 'nullable',
                Rule::in(['any', 'male', 'female', 'family']),
            ],

            'address_line_1' => ['sometimes', 'nullable', 'string', 'max:255'],
            'address_line_2' => ['sometimes', 'nullable', 'string', 'max:255'],
            'locality' => ['sometimes', 'nullable', 'string', 'max:255'],
            'landmark' => ['sometimes', 'nullable', 'string', 'max:255'],
            'city' => ['sometimes', 'required', 'string', 'max:100'],
            'district' => ['sometimes', 'nullable', 'string', 'max:100'],
            'state' => ['sometimes', 'required', 'string', 'max:100'],
            'country' => ['sometimes', 'nullable', 'string', 'max:100'],
            'postal_code' => ['sometimes', 'nullable', 'string', 'max:20'],
            'latitude' => ['sometimes', 'nullable', 'numeric', 'between:-90,90'],
            'longitude' => ['sometimes', 'nullable', 'numeric', 'between:-180,180'],
            'contact_name' => ['sometimes', 'nullable', 'string', 'max:100'],
            'contact_phone' => ['sometimes', 'nullable', 'string', 'max:30'],
            'show_owner_name' => ['sometimes', 'boolean'],

            'images' => ['sometimes', 'array', 'max:30'],
            'images.*.image_web' => [
                'required', 'image', 'mimes:jpg,jpeg,png,webp',
                'max:8192',
            ],
            'images.*.image_mobile' => [
                'nullable', 'image', 'mimes:jpg,jpeg,png,webp',
                'max:8192',
            ],
            'images.*.image_alt' => ['nullable', 'string', 'max:255'],
            'images.*.video' => [
                'nullable', 'file', 'mimes:mp4,mov,webm',
                'max:51200',
            ],

            'amenity_ids' => ['sometimes', 'array', 'max:100'],
            'amenity_ids.*' => [
                'integer',
                'distinct',
                Rule::exists('amenities', 'id')
                    ->where('is_active', true),
            ],
        ]);

        if ($validator->fails()) {
            return $this->apiResponse->error(
                'Validation failed.',
                $validator->errors(),
                422
            );
        }

        $property = $this->propertyService->update(
            $property,
            $validator->validated()
        );

        return $this->apiResponse->success(
            'Property updated successfully.',
            ['property' => new PropertyResource($property)]
        );
    }

    // Authenticated: delete own property; admin can delete any
    public function destroy(Request $request, int $id)
    {
        $property = Property::findOrFail($id);

        if (
            $request->user()->role !== 'admin'
            && $property->user_id !== $request->user()->id
        ) {
            return $this->apiResponse->error(
                'You are not authorized to delete this property.',
                null,
                403
            );
        }

        $this->propertyService->delete($property);

        return $this->apiResponse->success(
            'Property deleted successfully.'
        );
    }
}
