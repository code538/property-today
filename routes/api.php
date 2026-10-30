<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\WebsiteSettingController;
use App\Http\Controllers\Api\V1\PropertyTypeController;
use App\Http\Controllers\Api\V1\PropertyController;
use App\Http\Controllers\Api\V1\AmenityController;

Route::prefix('v1')->group(function () {

    

    // Public routes
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login', [AuthController::class, 'login']);
     
    Route::get('/website-settings',[WebsiteSettingController::class, 'show']);

    Route::get('property-types',[PropertyTypeController::class, 'index']);
    Route::get('property-types/{id}',[PropertyTypeController::class, 'show']);

    Route::get('properties', [PropertyController::class, 'index']);
    Route::get('properties/{id}', [PropertyController::class, 'show']);

    Route::get('amenities', [AmenityController::class, 'index']);








    // Protected routes
    Route::middleware('auth:sanctum')->group(function () {
        Route::get('/me', [AuthController::class, 'me']);
        Route::post('/logout', [AuthController::class, 'logout']);



        // Admin endpoint
        Route::put(
            '/admin/website-settings',
            [WebsiteSettingController::class, 'update']
        )->middleware('auth:sanctum');

        // Admin property management
        Route::get('my/properties', [PropertyController::class, 'myProperties']);

        Route::post('properties', [PropertyController::class, 'store']);

    });

    Route::prefix('admin')
    ->middleware(['auth:sanctum', 'role:admin'])
    ->group(function () {

        Route::get('property-types',[PropertyTypeController::class, 'adminIndex']);
        Route::post('property-types',[PropertyTypeController::class, 'store']);
        Route::put('property-types/{id}',[PropertyTypeController::class, 'update']);
        Route::patch('property-types/{id}/status',[PropertyTypeController::class, 'changeStatus']);
        Route::delete('property-types/{id}',[PropertyTypeController::class, 'destroy']);

        
        Route::put('properties/{id}', [PropertyController::class, 'update']);
        Route::delete('properties/{id}', [PropertyController::class, 'destroy']);
    });

    

});










