<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\PropertyResource;
use App\Models\Property;
use App\Models\PropertyCategory;
use App\Models\PropertyMedia;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use App\Services\PropertyService;

class PropertyController extends Controller
{

    protected PropertyService $propertyService;

    public function __construct(PropertyService $propertyService)
    {
        $this->propertyService = $propertyService;
    }
    /**
     * Public: Get all published properties.
     */
    public function index(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'search' => 'nullable|string|max:255',
            'listing_type' => 'nullable|in:sale,rent,lease',
            'category_id' => 'nullable|integer|exists:property_categories,id',
            'category_slug' => 'nullable|string',
            'city' => 'nullable|string|max:120',
            'locality' => 'nullable|string|max:150',
            'min_price' => 'nullable|numeric|min:0',
            'max_price' => 'nullable|numeric|min:0',
            'bedrooms' => 'nullable|integer|min:0',
            'min_area' => 'nullable|numeric|min:0',
            'max_area' => 'nullable|numeric|min:0',
            'is_featured' => 'nullable|boolean',
            'sort_by' => 'nullable|in:latest,price_low,price_high,popular',
            'per_page' => 'nullable|integer|min:1|max:100',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed.',
                'errors' => $validator->errors(),
            ], 422);
        }

        $query = Property::query()
            ->where('status', 'published')
            ->with([
                'category',
                'media',
                'location',
                'amenities',
                'details',
            ]);

        // Search by title or description.
        if ($request->filled('search')) {
            $search = $request->search;

            $query->where(function ($q) use ($search) {
                $q->where('title', 'LIKE', "%{$search}%")
                    ->orWhere('description', 'LIKE', "%{$search}%");
            });
        }

        // Listing type: sale, rent or lease.
        if ($request->filled('listing_type')) {
            $query->where('listing_type', $request->listing_type);
        }

        // Filter by category ID.
        if ($request->filled('category_id')) {
            $query->where(
                'property_category_id',
                $request->category_id
            );
        }

        // Filter by category slug.
        if ($request->filled('category_slug')) {
            $query->whereHas('category', function ($q) use ($request) {
                $q->where('slug', $request->category_slug);
            });
        }

        // Location filters.
        if ($request->filled('city')) {
            $query->whereHas('location', function ($q) use ($request) {
                $q->where('city', $request->city);
            });
        }

        if ($request->filled('locality')) {
            $query->whereHas('location', function ($q) use ($request) {
                $q->where('locality', 'LIKE', '%' . $request->locality . '%');
            });
        }

        // Price filters.
        if ($request->filled('min_price')) {
            $query->where('price', '>=', $request->min_price);
        }

        if ($request->filled('max_price')) {
            $query->where('price', '<=', $request->max_price);
        }

        // Bedroom filter.
        if ($request->filled('bedrooms')) {
            $query->where('bedrooms', $request->bedrooms);
        }

        // Area filters.
        if ($request->filled('min_area')) {
            $query->where('built_up_area', '>=', $request->min_area);
        }

        if ($request->filled('max_area')) {
            $query->where('built_up_area', '<=', $request->max_area);
        }

        // Featured properties.
        if ($request->filled('is_featured')) {
            $query->where('is_featured', $request->boolean('is_featured'));
        }

        // Sorting.
        switch ($request->input('sort_by', 'latest')) {
            case 'price_low':
                $query->orderBy('price', 'asc');
                break;

            case 'price_high':
                $query->orderBy('price', 'desc');
                break;

            case 'popular':
                $query->orderBy('views_count', 'desc');
                break;

            default:
                $query->latest();
                break;
        }

        $properties = $query->paginate(
            $request->input('per_page', 15)
        );

        return response()->json([
            'success' => true,
            'message' => 'Properties retrieved successfully.',
            'data' => PropertyResource::collection(
                $properties->getCollection()
            ),
            'pagination' => [
                'current_page' => $properties->currentPage(),
                'last_page' => $properties->lastPage(),
                'per_page' => $properties->perPage(),
                'total' => $properties->total(),
            ],
        ], 200);
    }


    /**
     * Public: Get property details by slug.
     */
    public function show(string $slug)
    {
        $property = Property::where('slug', $slug)
            ->where('status', 'published')
            ->with([
                'category',
                'media',
                'location',
                'amenities',
                'details',
                'user',
            ])
            ->first();

        if (!$property) {
            return response()->json([
                'success' => false,
                'message' => 'Property not found.',
                'data' => null,
            ], 404);
        }

        // Increment property views.
        $property->increment('views_count');

        return response()->json([
            'success' => true,
            'message' => 'Property details retrieved successfully.',
            'data' => new PropertyResource($property),
        ], 200);
    }


    /**
     * User / Agent: Create property listing.
     */
  
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'property_category_id' => [
                'required',
                'exists:property_categories,id',
            ],
            'listing_type' => 'required|in:sale,rent,lease',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',

            'price' => 'nullable|numeric|min:0',
            'price_negotiable' => 'nullable|boolean',
            'price_period' => 'nullable|in:daily,weekly,monthly,yearly',
            'maintenance_charge' => 'nullable|numeric|min:0',
            'security_deposit' => 'nullable|numeric|min:0',

            'built_up_area' => 'nullable|numeric|min:0',
            'carpet_area' => 'nullable|numeric|min:0',
            'plot_area' => 'nullable|numeric|min:0',
            'area_unit' => 'nullable|string|max:30',

            'bedrooms' => 'nullable|integer|min:0',
            'bathrooms' => 'nullable|integer|min:0',
            'balconies' => 'nullable|integer|min:0',
            'furnishing_status' => 'nullable|string|max:50',
            'floor_number' => 'nullable|integer|min:0',
            'total_floors' => 'nullable|integer|min:0',
            'property_age' => 'nullable|integer|min:0',
            'possession_status' => 'nullable|string|max:50',
            'available_from' => 'nullable|date',

            'meta_title' => 'nullable|string|max:255',
            'meta_description' => 'nullable|string',

            'location.state' => 'required|string|max:120',
            'location.city' => 'required|string|max:120',
            'location.locality' => 'nullable|string|max:150',
            'location.pincode' => 'nullable|string|max:12',
            'location.address' => 'nullable|string',
            'location.landmark' => 'nullable|string|max:180',
            'location.latitude' => 'nullable|numeric|between:-90,90',
            'location.longitude' => 'nullable|numeric|between:-180,180',

            'amenity_ids' => 'nullable|array',
            'amenity_ids.*' => 'integer|distinct|exists:amenities,id',

            'details' => 'nullable|array',
            'details.plot_length' => 'nullable|numeric|min:0',
            'details.plot_width' => 'nullable|numeric|min:0',
            'details.facing' => 'nullable|string|max:40',
            'details.road_width' => 'nullable|numeric|min:0',
            'details.sharing_type' => 'nullable|string|max:60',
            'details.total_beds' => 'nullable|integer|min:0',
            'details.available_beds' => 'nullable|integer|min:0',
            'details.food_available' => 'nullable|boolean',
            'details.office_type' => 'nullable|string|max:80',
            'details.number_of_cabins' => 'nullable|integer|min:0',
            'details.number_of_washrooms' => 'nullable|integer|min:0',
            'details.attributes' => 'nullable|array',

            'images' => 'nullable|array',
            'images.*.web_image' => 'required|image|mimes:jpg,jpeg,png,webp|max:10240',
            'images.*.mobile_image' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:10240',
            'images.*.image_alt' => 'nullable|string|max:255',

            'videos' => 'nullable|array',
            'videos.*' => 'file|mimes:mp4,mov,avi,webm|max:51200',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed.',
                'errors' => $validator->errors(),
            ], 422);
        }

        $validated = $validator->validated();

        $category = PropertyCategory::where(
            'id',
            $validated['property_category_id']
        )->where('is_active', true)->first();

        if (!$category) {
            return response()->json([
                'success' => false,
                'message' => 'Selected property category is inactive or not found.',
                'data' => null,
            ], 422);
        }

        // Validate active amenities.
        if (!empty($validated['amenity_ids'])) {
            $activeAmenities = \App\Models\Amenity::whereIn(
                'id',
                $validated['amenity_ids']
            )->where('is_active', true)->count();

            if ($activeAmenities !== count($validated['amenity_ids'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'One or more selected amenities are inactive or invalid.',
                    'data' => null,
                ], 422);
            }
        }

        $user = $request->user();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthenticated.',
                'data' => null,
            ], 401);
        }

        try {

            // PropertyService handles the complete database transaction.
            $property = $this->propertyService->createProperty(
                $validated,
                $request,
                $user
            );

            return response()->json([
                'success' => true,
                'message' => 'Property submitted successfully. Waiting for admin approval.',
                'data' => new PropertyResource($property),
            ], 201);

        } catch (\Throwable $e) {

            \Log::error('Property creation failed', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Unable to create property.',
                'error' => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }



    /**
     * User / Agent: Update own property.
     */
    public function update(Request $request, $id)
    {
        $property = Property::where('user_id', $request->user()->id)
            ->find($id);

        if (!$property) {
            return response()->json([
                'success' => false,
                'message' => 'Property not found or you are not authorized.',
                'data' => null,
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'property_category_id' => 'sometimes|required|exists:property_categories,id',
            'listing_type' => 'sometimes|required|in:sale,rent,lease',
            'title' => 'sometimes|required|string|max:255',
            'description' => 'sometimes|nullable|string',
            'price' => 'sometimes|nullable|numeric|min:0',
            'price_negotiable' => 'sometimes|boolean',
            'price_period' => 'sometimes|nullable|in:daily,weekly,monthly,yearly',
            'maintenance_charge' => 'sometimes|nullable|numeric|min:0',
            'security_deposit' => 'sometimes|nullable|numeric|min:0',
            'built_up_area' => 'sometimes|nullable|numeric|min:0',
            'carpet_area' => 'sometimes|nullable|numeric|min:0',
            'plot_area' => 'sometimes|nullable|numeric|min:0',
            'area_unit' => 'sometimes|string|max:30',
            'bedrooms' => 'sometimes|nullable|integer|min:0',
            'bathrooms' => 'sometimes|nullable|integer|min:0',
            'balconies' => 'sometimes|nullable|integer|min:0',
            'furnishing_status' => 'sometimes|nullable|string|max:50',
            'floor_number' => 'sometimes|nullable|integer|min:0',
            'total_floors' => 'sometimes|nullable|integer|min:0',
            'property_age' => 'sometimes|nullable|integer|min:0',
            'possession_status' => 'sometimes|nullable|string|max:50',
            'available_from' => 'sometimes|nullable|date',
            'meta_title' => 'sometimes|nullable|string|max:255',
            'meta_description' => 'sometimes|nullable|string',

            'location' => 'sometimes|array',
            'location.state' => 'required_with:location|string|max:120',
            'location.city' => 'required_with:location|string|max:120',
            'location.locality' => 'nullable|string|max:150',
            'location.pincode' => 'nullable|string|max:12',
            'location.address' => 'nullable|string',
            'location.landmark' => 'nullable|string|max:180',
            'location.latitude' => 'nullable|numeric|between:-90,90',
            'location.longitude' => 'nullable|numeric|between:-180,180',

            'amenity_ids' => 'sometimes|array',
            'amenity_ids.*' => 'integer|distinct|exists:amenities,id',

            'details' => 'sometimes|array',
            'details.plot_length' => 'nullable|numeric|min:0',
            'details.plot_width' => 'nullable|numeric|min:0',
            'details.facing' => 'nullable|string|max:40',
            'details.road_width' => 'nullable|numeric|min:0',
            'details.sharing_type' => 'nullable|string|max:60',
            'details.total_beds' => 'nullable|integer|min:0',
            'details.available_beds' => 'nullable|integer|min:0',
            'details.food_available' => 'nullable|boolean',
            'details.office_type' => 'nullable|string|max:80',
            'details.number_of_cabins' => 'nullable|integer|min:0',
            'details.number_of_washrooms' => 'nullable|integer|min:0',
            'details.attributes' => 'nullable|array',

            'images' => 'sometimes|array',
            'images.*.web_image' => 'required|image|mimes:jpg,jpeg,png,webp|max:10240',
            'images.*.mobile_image' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:10240',
            'images.*.image_alt' => 'nullable|string|max:255',
            'videos' => 'sometimes|array',
            'videos.*' => 'file|mimes:mp4,mov,avi,webm|max:51200',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed.',
                'errors' => $validator->errors(),
            ], 422);
        }

        $validated = $validator->validated();

        if (isset($validated['property_category_id'])) {
            $category = PropertyCategory::where('id', $validated['property_category_id'])
                ->where('is_active', true)
                ->first();

            if (!$category) {
                return response()->json([
                    'success' => false,
                    'message' => 'Selected category is inactive or not found.',
                    'data' => null,
                ], 422);
            }
        }

        if (isset($validated['amenity_ids'])) {
            $activeCount = \App\Models\Amenity::whereIn(
                'id',
                $validated['amenity_ids']
            )->where('is_active', true)->count();

            if ($activeCount !== count($validated['amenity_ids'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'One or more amenities are inactive or invalid.',
                    'data' => null,
                ], 422);
            }
        }

        $property = Property::where('user_id', $request->user()->id)
            ->find($id);

        if (!$property) {
            return response()->json([
                'success' => false,
                'message' => 'Property not found or you are not authorized to update it.',
                'data' => null,
            ], 404);
        }

       try {
            $property = $this->propertyService->updateProperty(
                $property,
                $validated,
                $request
            );

            return response()->json([
                'success' => true,
                'message' => 'Property updated successfully. Waiting for admin approval.',
                'data' => new PropertyResource($property),
            ], 200);

        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Unable to update property.',
                'error' => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }


    /**
     * User / Agent: Delete own property.
     */
    public function destroy(Request $request, $id)
    {
        $property = Property::where('user_id', $request->user()->id)
            ->find($id);

        if (!$property) {
            return response()->json([
                'success' => false,
                'message' => 'Property not found or you are not authorized.',
                'data' => null,
            ], 404);
        }

        // Property model uses SoftDeletes.
        $this->propertyService->deleteProperty($property);

        return response()->json([
            'success' => true,
            'message' => 'Property deleted successfully.',
            'data' => null,
        ], 200);
    }


    /**
     * Generate unique SEO-friendly property slug.
     */
    private function generateSlug(string $title, ?int $ignoreId = null): string
    {
        $baseSlug = Str::slug($title);
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
    private function uploadPropertyMedia(Request $request, Property $property): void
    {
        // Upload images.
        foreach ($request->file('images', []) as $index => $image) {

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
                'image_alt' => $request->input(
                    "images.{$index}.image_alt"
                ),
                'mime_type' => $image['web_image']->getMimeType(),
                'file_size' => $image['web_image']->getSize(),
                'is_primary' => !$property->media()
                    ->where('media_type', 'image')
                    ->exists() && $index === 0,
                'sort_order' => $index,
            ]);
        }

        // Upload videos.
        foreach ($request->file('videos', []) as $index => $video) {

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

    public function myProperties(Request $request)
    {
        $properties = Property::with([
            'category',
            'media',
            'location',
            'amenities',
            'details',
        ])
        ->where('user_id', $request->user()->id)
        ->latest()
        ->paginate(12);

        return response()->json([
            'success' => true,
            'message' => 'Your properties fetched successfully.',
            'data' => PropertyResource::collection($properties),
            'pagination' => [
                'current_page' => $properties->currentPage(),
                'last_page' => $properties->lastPage(),
                'per_page' => $properties->perPage(),
                'total' => $properties->total(),
            ],
        ]);
    }
}
