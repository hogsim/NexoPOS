<?php

namespace Modules\CustomProfileTabs\Providers;

use App\Classes\Hook;
use App\Services\Helper;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Event;
use Modules\CustomProfileTabs\Models\CustomFieldDefinition;
use Modules\CustomProfileTabs\Models\CustomFieldValue;
use Modules\CustomProfileTabs\Listeners\SaveUserCustomDataListener;
use Modules\CustomProfileTabs\Listeners\SaveCustomerCustomDataListener;
use App\Events\CustomerAfterCreatedEvent;
use App\Events\CustomerAfterUpdatedEvent;

class CustomProfileTabsServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap services.
     */
    public function boot()
    {
        // Load views
        $this->loadViewsFrom(__DIR__ . '/../Resources/Views', 'CustomProfileTabs');

        // Add custom tab to user profile
        Hook::addFilter('ns-user-profile-form', [$this, 'addUserProfileTab']);

        // Hook to save user profile custom data
        Hook::addAction('ns.after-user-profile-saved', [SaveUserCustomDataListener::class, 'handle']);

        // Add custom tab to customer CRUD form
        Hook::addFilter('ns.crud.form', [$this, 'addCustomerProfileTab'], 10, 2);

        // Listen to customer events to save custom data
        Event::listen(CustomerAfterCreatedEvent::class, [SaveCustomerCustomDataListener::class, 'handleCreated']);
        Event::listen(CustomerAfterUpdatedEvent::class, [SaveCustomerCustomDataListener::class, 'handleUpdated']);
    }

    /**
     * Add custom tab to user profile form (dynamically loaded from database)
     *
     * @param array $tabs
     * @return array
     */
    public function addUserProfileTab($tabs)
    {
        // Get active field definitions for user profiles
        $fieldDefinitions = CustomFieldDefinition::getActiveFields('user');

        if ($fieldDefinitions->isEmpty()) {
            return $tabs;
        }

        // Get existing values for the current user
        $existingValues = CustomFieldValue::where('entity_id', Auth::id())
            ->where('entity_type', 'user')
            ->get()
            ->keyBy('field_id');

        // Build fields array
        $fields = [];
        foreach ($fieldDefinitions as $fieldDef) {
            $value = $existingValues->get($fieldDef->id)->value ?? '';

            $field = [
                'label' => $fieldDef->label,
                'name' => $fieldDef->name,
                'value' => $value,
                'type' => $fieldDef->type,
                'description' => $fieldDef->description ?? '',
            ];

            // Add validation if specified
            if ($fieldDef->validation) {
                $field['validation'] = $fieldDef->validation;
            }

            // Add options for select fields
            if ($fieldDef->type === 'select' && $fieldDef->options) {
                $field['options'] = Helper::kvToJsOptions($fieldDef->options);
            }

            $fields[] = $field;
        }

        // Add the custom tab
        $tabs['custom_fields'] = [
            'label' => __('Custom Fields'),
            'fields' => $fields,
        ];

        return $tabs;
    }

    /**
     * Add custom tab to customer CRUD form (dynamically loaded from database)
     *
     * @param array $form
     * @param string $namespace
     * @return array
     */
    public function addCustomerProfileTab($form, $namespace)
    {
        // Only apply to customer CRUD
        if ($namespace !== 'ns.customers') {
            return $form;
        }

        // Get active field definitions for customer profiles
        $fieldDefinitions = CustomFieldDefinition::getActiveFields('customer');

        if ($fieldDefinitions->isEmpty()) {
            return $form;
        }

        // Get the customer ID if editing
        $customerId = request()->route('id');
        $existingValues = collect();

        if ($customerId) {
            $existingValues = CustomFieldValue::where('entity_id', $customerId)
                ->where('entity_type', 'customer')
                ->get()
                ->keyBy('field_id');
        }

        // Build fields array
        $fields = [];
        foreach ($fieldDefinitions as $fieldDef) {
            $value = $existingValues->get($fieldDef->id)->value ?? '';

            $field = [
                'label' => $fieldDef->label,
                'name' => $fieldDef->name,
                'value' => $value,
                'type' => $fieldDef->type,
                'description' => $fieldDef->description ?? '',
            ];

            // Add validation if specified
            if ($fieldDef->validation) {
                $field['validation'] = $fieldDef->validation;
            }

            // Add options for select fields
            if ($fieldDef->type === 'select' && $fieldDef->options) {
                $field['options'] = Helper::kvToJsOptions($fieldDef->options);
            }

            $fields[] = $field;
        }

        // Add custom tab to the tabs array
        $form['tabs'][] = [
            'label' => __('Custom Fields'),
            'identifier' => 'custom_fields',
            'fields' => $fields,
        ];

        return $form;
    }
}
