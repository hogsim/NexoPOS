<?php

use Illuminate\Support\Facades\Route;
use Modules\NsCustomFields\Http\Controllers\CustomFieldsController;

Route::middleware(['web', 'auth'])->group(function () {
    Route::get('/api/custom-fields/config', [CustomFieldsController::class, 'getConfig']);
    Route::post('/api/custom-fields/config', [CustomFieldsController::class, 'saveConfig']);
});
