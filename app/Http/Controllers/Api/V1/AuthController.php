<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use App\Services\ApiResponseService;
use App\Services\AuthService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
    public function __construct(
        protected AuthService $authService,
        protected ApiResponseService $apiResponse
    ) {}

    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => ['required', 'string', 'max:100'],
            'email' => [
                'required',
                'email',
                'max:255',
                'unique:users,email',
            ],
            'phone' => [
                'nullable',
                'string',
                'max:20',
                'unique:users,phone',
            ],
            'password' => [
                'required',
                'string',
                'min:8',
                'confirmed',
            ],
            'role' => ['required', 'string', 'max:100', 'in:agent,user'],
        ]);

        if ($validator->fails()) {
            return $this->apiResponse->error(
                'Validation failed.',
                $validator->errors(),
                422
            );
        }

        $result = $this->authService->register(
            $validator->validated()
        );

        return $this->apiResponse->success(
            'Registration successful.',
            [
                'user' => new UserResource($result['user']),
                'token' => $result['token'],
                'token_type' => 'Bearer',
            ],
            201
        );
    }

    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
            'device_name' => ['nullable', 'string', 'max:100'],
        ]);
        
        if ($validator->fails()) {
            return $this->apiResponse->error(
                'Validation failed.',
                $validator->errors(),
                422
            );
        }

        $result = $this->authService->login(
            $validator->validated()
        );

        return $this->apiResponse->success(
            'Login successful.',
            [
                'user' => new UserResource($result['user']),
                'token' => $result['token'],
                'token_type' => 'Bearer',
            ]
        );
    }

    public function me(Request $request)
    {
        return $this->apiResponse->success(
            'User retrieved successfully.',
            [
                'user' => new UserResource($request->user()),
            ]
        );
    }

    public function logout(Request $request)
    {
        $this->authService->logout($request->user());

        return $this->apiResponse->success(
            'Logout successful.'
        );
    }
}
