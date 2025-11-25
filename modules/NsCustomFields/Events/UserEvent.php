<?php

namespace Modules\NsCustomFields\Events;

use App\Models\UserAttribute;
use Illuminate\Support\Facades\Auth;

class UserEvent
{
    public static function saveCustomFields( $event )
    {
        $user = $event->user;
        $request = request();
        $customFieldsInput = $request->input( 'custom_fields' );

        if ( ! empty( $customFieldsInput ) ) {
            $attribute = UserAttribute::firstOrNew([ 'user_id' => $user->id ]);
            
            // Get existing data to merge
            $existingData = $attribute->custom_fields ? json_decode( $attribute->custom_fields, true ) : [];
            
            // Get configured fields to only save valid fields
            $fieldsConfig = ns()->option->get( 'ns_custom_fields_config' );
            $fieldsConfig = $fieldsConfig ? json_decode( $fieldsConfig, true ) : [];
            $validFields = collect( $fieldsConfig )->pluck('name')->toArray();

            foreach ( $customFieldsInput as $key => $value ) {
                if ( in_array( $key, $validFields ) ) {
                    $existingData[ $key ] = $value;
                }
            }

            $attribute->custom_fields = json_encode( $existingData );
            $attribute->save();
        }
    }
}
