<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\AmenityResource;
use App\Services\AmenityService;
use App\Services\ApiResponseService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class AmenityController extends Controller
{
    public function __construct(
        protected AmenityService $amenityService,
        protected ApiResponseService $apiResponse
    ) {}

    public function index(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'search' => ['nullable', 'string', 'max:100'],
            'per_page' => ['sometimes', 'integer', 'min:1', 'max:100'],
        ]);

        if ($validator->fails()) {
            return $this->apiResponse->error(
                'Validation failed.',
                $validator->errors(),
                422
            );
        }

        $data = $validator->validated();

        $amenities = $this->amenityService->getActiveAmenities(
            $data['search'] ?? null,
            (int) ($data['per_page'] ?? 30)
        );

        return $this->apiResponse->success(
            'Amenities retrieved successfully.',
            [
                'amenities' => AmenityResource::collection(
                    $amenities->getCollection()
                ),
                'pagination' => [
                    'current_page' => $amenities->currentPage(),
                    'last_page' => $amenities->lastPage(),
                    'per_page' => $amenities->perPage(),
                    'total' => $amenities->total(),
                ],
            ]
        );
    }
}
