<?php

use Illuminate\Support\Facades\Route;
use Modules\NsCustomFields\Http\Controllers\CustomFieldsController;
use Modules\NsCustomFields\Http\Middleware\CustomFieldsAuth;

// All custom fields API routes support both session and token authentication
Route::middleware([CustomFieldsAuth::class])
    ->withoutMiddleware([\App\Http\Middleware\VerifyCsrfToken::class])
    ->group(function () {
        // Configuration endpoints
        Route::get('/api/custom-fields/config', [CustomFieldsController::class, 'getConfig']);
        Route::post('/api/custom-fields/config', [CustomFieldsController::class, 'saveConfig']);
        
        // Search customers by custom field value
        Route::get('/api/customers/search-by-custom-field', [CustomFieldsController::class, 'searchByCustomField']);
        Route::post('/api/customers/search-by-custom-field', [CustomFieldsController::class, 'searchByCustomField']);
        
        // Get/update custom fields for specific customer
        Route::get('/api/customers/{customerId}/custom-fields', [CustomFieldsController::class, 'getCustomerFields']);
        Route::put('/api/customers/{customerId}/custom-fields', [CustomFieldsController::class, 'updateCustomerFields']);
        Route::post('/api/customers/{customerId}/custom-fields', [CustomFieldsController::class, 'updateCustomerFields']);
    });

