<?php

namespace Modules\NsCustomFields\Events;

use App\Models\UserAttribute;

class CustomerEvent
{
    public static function saveCustomFields( $event )
    {
        $customer = $event->customer;
        
        // Handle customer as both array and object
        $customerId = is_array( $customer ) ? $customer['id'] : $customer->id;
        
        \Log::info('CustomFields: Saving custom fields for customer', [
            'customer_id' => $customerId,
            'request_data' => request()->all()
        ]);
        
        $request = request();
        
        // Get configured fields
        $fieldsConfig = ns()->option->get( 'ns_custom_fields_config' );
        $fieldsConfig = $fieldsConfig ? json_decode( $fieldsConfig, true ) : [];
        
        \Log::info('CustomFields: Fields configuration', [
            'fields_config' => $fieldsConfig
        ]);
        
        if ( empty( $fieldsConfig ) ) {
            \Log::info('CustomFields: No fields configured, skipping save');
            return;
        }
        
        $attribute = UserAttribute::firstOrNew([ 'user_id' => $customerId ]);
        
        // Ensure user_id is set
        $attribute->user_id = $customerId;
        
        // Get existing data to merge
        $existingData = $attribute->custom_fields ? json_decode( $attribute->custom_fields, true ) : [];
        
        // Custom fields are nested under 'custom_fields' key in the request
        $customFieldsInput = $request->input('custom_fields', []);
        
        \Log::info('CustomFields: Custom fields input from request', [
            'custom_fields_input' => $customFieldsInput
        ]);
        
        // Get custom field values from request
        $hasData = false;
        foreach ( $fieldsConfig as $config ) {
            $fieldName = $config['name'];
            // Check if the field exists in the nested custom_fields data
            if ( isset( $customFieldsInput[ $fieldName ] ) ) {
                $existingData[ $fieldName ] = $customFieldsInput[ $fieldName ];
                $hasData = true;
                \Log::info('CustomFields: Found field in request', [
                    'field' => $fieldName,
                    'value' => $customFieldsInput[ $fieldName ]
                ]);
            }
        }

        if ( $hasData ) {
            $attribute->custom_fields = json_encode( $existingData );
            $attribute->save();
            \Log::info('CustomFields: Saved custom fields', [
                'attribute_id' => $attribute->id,
                'data' => $existingData
            ]);
        } else {
            \Log::info('CustomFields: No custom field data found in request');
        }
    }
}
