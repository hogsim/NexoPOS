<?php

namespace Modules\CustomProfileTabs\Http\Controllers;

use App\Http\Controllers\DashboardController;

class CustomFieldsController extends DashboardController
{
    /**
     * Display the CRUD list view
     */
    public function index()
    {
        return view('CustomProfileTabs::fields.index', [
            'title' => __('Custom Profile Fields'),
            'description' => __('Manage custom fields for user and customer profiles.'),
        ]);
    }

    /**
     * Display the create form
     */
    public function create()
    {
        return view('CustomProfileTabs::fields.create', [
            'title' => __('Create Custom Field'),
            'description' => __('Define a new custom field for profiles.'),
        ]);
    }

    /**
     * Display the edit form
     */
    public function edit($id)
    {
        return view('CustomProfileTabs::fields.edit', [
            'title' => __('Edit Custom Field'),
            'description' => __('Modify the custom field definition.'),
            'id' => $id,
        ]);
    }
}
