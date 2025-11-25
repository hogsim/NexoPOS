<?php


namespace Modules\NsCustomFields\Events;

class CustomerInputFilter
{
    public static function filterInputs( $inputs )
    {
        // Get configured custom fields
        $fieldsConfig = ns()->option->get( 'ns_custom_fields_config' );
        $fieldsConfig = $fieldsConfig ? json_decode( $fieldsConfig, true ) : [];
        $customFieldNames = collect( $fieldsConfig )->pluck('name')->toArray();

        // Remove custom fields from inputs so they don't get saved to the main table
        foreach ( $customFieldNames as $fieldName ) {
            unset( $inputs[ $fieldName ] );
        }

        return $inputs;
    }
}
