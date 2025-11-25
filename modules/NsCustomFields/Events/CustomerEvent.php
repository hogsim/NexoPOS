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
        
        $request = request();
        
        // Get configured fields
        $fieldsConfig = ns()->option->get( 'ns_custom_fields_config' );
        $fieldsConfig = $fieldsConfig ? json_decode( $fieldsConfig, true ) : [];
        
        if ( empty( $fieldsConfig ) ) {
            return;
        }
        
        $attribute = UserAttribute::firstOrNew([ 'user_id' => $customerId ]);
        
        // Ensure user_id is set
        $attribute->user_id = $customerId;
        
        // Get existing data to merge
        $existingData = $attribute->custom_fields ? json_decode( $attribute->custom_fields, true ) : [];
        
        // Get custom field values from request
        $hasData = false;
        foreach ( $fieldsConfig as $config ) {
            $fieldName = $config['name'];
            if ( $request->has( $fieldName ) ) {
                $existingData[ $fieldName ] = $request->input( $fieldName );
                $hasData = true;
            }
        }

        if ( $hasData ) {
            $attribute->custom_fields = json_encode( $existingData );
            $attribute->save();
        }
    }
}
