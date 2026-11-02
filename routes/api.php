<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\WebsiteSettingController;
use App\Http\Controllers\Api\V1\PropertyCategoryController;
use App\Http\Controllers\Api\V1\PropertyCategoryMenuController;
use App\Http\Controllers\Api\V1\PropertyController;

Route::prefix('v1')->group(function () {

    

    // Public routes
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login', [AuthController::class, 'login']);
     
    Route::get('/website-settings',[WebsiteSettingController::class, 'show']);


      // Public property category routes
    Route::get('/property-categories', [
        PropertyCategoryController::class,
        'index'
    ]);
    
    Route::get('/property-categories/{slug}', [
        PropertyCategoryController::class,
        'show'
    ]);

    Route::get('/property-category-menus', [
        PropertyCategoryMenuController::class,
        'index'
    ]);

    // Public property APIs
    Route::get('/properties', [PropertyController::class, 'index']);

    Route::get('/properties/{slug}', [PropertyController::class, 'show']);









    // Protected routes
    Route::middleware('auth:sanctum')->group(function () {
        Route::get('/me', [AuthController::class, 'me']);
        Route::post('/logout', [AuthController::class, 'logout']);



        // Admin endpoint
        Route::post(
            '/admin/website-settings',
            [WebsiteSettingController::class, 'update']
        )->middleware('auth:sanctum');

        // User property listing routes
        Route::get('/my-properties', [PropertyController::class, 'myProperties']);
        Route::post('/properties', [PropertyController::class, 'store']);
        Route::put('/properties/{id}', [PropertyController::class, 'update']);
        Route::delete('/properties/{id}', [PropertyController::class, 'destroy']);

    });

    // Route::prefix('admin')
    // ->middleware(['auth:sanctum', 'role:admin'])
    // ->group(function () {

    //     Route::get('property-types',[PropertyTypeController::class, 'adminIndex']);
    //     Route::post('property-types',[PropertyTypeController::class, 'store']);
    //     Route::put('property-types/{id}',[PropertyTypeController::class, 'update']);
    //     Route::patch('property-types/{id}/status',[PropertyTypeController::class, 'changeStatus']);
    //     Route::delete('property-types/{id}',[PropertyTypeController::class, 'destroy']);

        
    //     Route::put('properties/{id}', [PropertyController::class, 'update']);
    //     Route::delete('properties/{id}', [PropertyController::class, 'destroy']);
    // });


    
    // Admin property category routes
   
    Route::middleware(['auth:sanctum', 'role:admin'])->prefix('admin')->group(function () {
        Route::get('/property-categories', [
            PropertyCategoryController::class,
            'adminIndex'
        ]);

        Route::post('/property-categories', [
            PropertyCategoryController::class,
            'store'
        ]);

        Route::get('/property-categories/{id}/edit', [
            PropertyCategoryController::class,
            'edit'
        ]);

        Route::put('/property-categories/{id}', [
            PropertyCategoryController::class,
            'update'
        ]);

        Route::delete('/property-categories/{id}', [
            PropertyCategoryController::class,
            'destroy'
        ]);

        // Admin property category menu routes
        
        Route::get('/property-category-menus', [PropertyCategoryMenuController::class, 'adminIndex']);
        Route::post('/property-category-menus', [PropertyCategoryMenuController::class, 'store']);
        Route::get('/property-category-menus/{id}/edit', [PropertyCategoryMenuController::class, 'edit']);
        Route::put('/property-category-menus/{id}', [PropertyCategoryMenuController::class, 'update']);
        Route::delete('/property-category-menus/{id}', [PropertyCategoryMenuController::class, 'destroy']);      
        Route::patch('/property-category-menus/{id}/status', [
            PropertyCategoryMenuController::class,
            'changeStatus'
        ]);




    });



    

});










