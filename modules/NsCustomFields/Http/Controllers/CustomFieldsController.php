<?php

namespace Modules\NsCustomFields\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use App\Models\UserAttribute;

class CustomFieldsController extends Controller
{
    /**
     * Get configuration for custom fields
     */
    public function getConfig()
    {
        $config = ns()->option->get('ns_custom_fields_config');
        
        return [
            'status' => 'success',
            'value' => $config ? json_decode($config, true) : []
        ];
    }

    /**
     * Save custom fields configuration
     */
    public function saveConfig(Request $request)
    {
        ns()->option->set('ns_custom_fields_config', $request->input('ns_custom_fields_config'));
        
        return [
            'status' => 'success',
            'message' => __('The configuration has been saved.')
        ];
    }

    /**
     * Get custom fields for a specific customer
     * 
     * @param int $customerId
     * @return array
     */
    public function getCustomerFields($customerId)
    {
        $attribute = UserAttribute::where('user_id', $customerId)->first();
        
        $customFieldsData = [];
        if ($attribute && $attribute->custom_fields) {
            $customFieldsData = json_decode($attribute->custom_fields, true) ?? [];
        }

        return [
            'status' => 'success',
            'data' => $customFieldsData
        ];
    }

    /**
     * Update custom fields for a specific customer
     * 
     * @param int $customerId
     * @param Request $request
     * @return array
     */
    public function updateCustomerFields($customerId, Request $request)
    {
        $attribute = UserAttribute::firstOrNew(['user_id' => $customerId]);
        $attribute->user_id = $customerId;
        
        // Get existing data to merge
        $existingData = $attribute->custom_fields ? json_decode($attribute->custom_fields, true) : [];
        
        // Merge with new data
        $newData = $request->input('custom_fields', []);
        $mergedData = array_merge($existingData, $newData);
        
        $attribute->custom_fields = json_encode($mergedData);
        $attribute->save();

        return [
            'status' => 'success',
            'message' => __('Custom fields updated successfully.'),
            'data' => $mergedData
        ];
    }

    /**
     * Search for customers by custom field value
     * 
     * @param Request $request
     * @return array
     */
    public function searchByCustomField(Request $request)
    {
        $fieldName = $request->input('field') ?? $request->query('field');
        $fieldValue = $request->input('value') ?? $request->query('value');

        if (!$fieldName || !$fieldValue) {
            return [
                'status' => 'error',
                'message' => __('Both field and value parameters are required.')
            ];
        }

        // Search for matching custom fields
        // Using JSON_EXTRACT to query the JSON column
        $attributes = UserAttribute::whereRaw(
            "JSON_EXTRACT(custom_fields, ?) = ?",
            ['$.' . $fieldName, $fieldValue]
        )->get();

        if ($attributes->isEmpty()) {
            return [
                'status' => 'success',
                'data' => []
            ];
        }

        // Load full customer data for each match
        $customers = [];
        foreach ($attributes as $attribute) {
            $customer = \App\Models\Customer::with([
                'group',
                'billing',
                'shipping',
            ])->find($attribute->user_id);

            if ($customer) {
                // Attach custom fields to customer
                $customFieldsData = $attribute->custom_fields ? json_decode($attribute->custom_fields, true) : [];
                $customer->custom_fields = $customFieldsData;
                
                $customers[] = $customer;
            }
        }

        return [
            'status' => 'success',
            'data' => $customers
        ];
    }
}
