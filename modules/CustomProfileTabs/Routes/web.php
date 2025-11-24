<?php

use Illuminate\Support\Facades\Route;
use Modules\CustomProfileTabs\Http\Controllers\CustomFieldsController;

Route::prefix('dashboard/customprofiletabs')->middleware(['auth'])->group(function () {
    Route::get('/fields', [CustomFieldsController::class, 'index'])
        ->name('customprofiletabs.fields.index');

    Route::get('/fields/create', [CustomFieldsController::class, 'create'])
        ->name('customprofiletabs.fields.create');

    Route::get('/fields/edit/{id}', [CustomFieldsController::class, 'edit'])
        ->name('customprofiletabs.fields.edit');
});
