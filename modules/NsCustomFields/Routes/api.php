<?php

use Illuminate\Support\Facades\Route;
use Modules\NsCustomFields\Http\Controllers\CustomFieldsController;
use Modules\NsCustomFields\Http\Middleware\CustomFieldsAuth;
use App\Http\Middleware\ClearRequestCacheMiddleware;
use Illuminate\Routing\Middleware\SubstituteBindings;

// All routes support BOTH session and token authentication
Route::middleware(['web', CustomFieldsAuth::class])
    ->withoutMiddleware([\App\Http\Middleware\VerifyCsrfToken::class])
    ->group(function () {
        // Configuration endpoints
        Route::get('/api/custom-fields/config', [CustomFieldsController::class, 'getConfig']);
        Route::post('/api/custom-fields/config', [CustomFieldsController::class, 'saveConfig']);
        
        // Search and customer field routes
        Route::get('/api/customers/search-by-custom-field', [CustomFieldsController::class, 'searchByCustomField']);
        Route::post('/api/customers/search-by-custom-field', [CustomFieldsController::class, 'searchByCustomField']);
        Route::get('/api/customers/{customerId}/custom-fields', [CustomFieldsController::class, 'getCustomerFields']);
        Route::put('/api/customers/{customerId}/custom-fields', [CustomFieldsController::class, 'updateCustomerFields']);
        Route::post('/api/customers/{customerId}/custom-fields', [CustomFieldsController::class, 'updateCustomerFields']);
    });

// API token-based routes (following NexoPOS pattern)
Route::middleware([
        SubstituteBindings::class,
        ClearRequestCacheMiddleware::class,
        'auth:sanctum'
    ])
    ->group(function () {
        // Configuration endpoints
        Route::get('/api/nexopos/custom-fields/config', [CustomFieldsController::class, 'getConfig']);
        
        // Search customers by custom field value
        Route::get('/api/nexopos/customers/search-by-custom-field', [CustomFieldsController::class, 'searchByCustomField']);
        Route::post('/api/nexopos/customers/search-by-custom-field', [CustomFieldsController::class, 'searchByCustomField']);
        
        // Get/update custom fields for specific customer
        Route::get('/api/nexopos/customers/{customerId}/custom-fields', [CustomFieldsController::class, 'getCustomerFields']);
        Route::put('/api/nexopos/customers/{customerId}/custom-fields', [CustomFieldsController::class, 'updateCustomerFields']);
        Route::post('/api/nexopos/customers/{customerId}/custom-fields', [CustomFieldsController::class, 'updateCustomerFields']);
    });
