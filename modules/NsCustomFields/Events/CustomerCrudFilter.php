<?php

namespace Modules\NsCustomFields\Events;

use App\Classes\CrudForm;
use App\Classes\FormInput;
use App\Models\UserAttribute;

class CustomerCrudFilter
{
    public static function registerTab( $form )
    {
        $attribute = null;
        $customFieldsData = [];

        // Try to get customer ID from URL segments
        $segments = request()->segments();
        $customerId = null;
        
        // URL pattern 1: /dashboard/customers/edit/{id}
        if ( in_array( 'customers', $segments ) && in_array( 'edit', $segments ) ) {
            $editIndex = array_search( 'edit', $segments );
            $customerId = $segments[ $editIndex + 1 ] ?? null;
        }
        
        // URL pattern 2: /api/crud/customers/form-config/{id}
        if ( ! $customerId && in_array( 'form-config', $segments ) ) {
            $formConfigIndex = array_search( 'form-config', $segments );
            $customerId = $segments[ $formConfigIndex + 1 ] ?? null;
        }
        
        if ( $customerId ) {
            $attribute = UserAttribute::where( 'user_id', $customerId )->first();
            $customFieldsData = $attribute && $attribute->custom_fields ? json_decode( $attribute->custom_fields, true ) : [];
        }

        $fieldsConfig = ns()->option->get( 'ns_custom_fields_config' );
        $fieldsConfig = $fieldsConfig ? json_decode( $fieldsConfig, true ) : [];

        $fields = [];

        foreach ( $fieldsConfig as $config ) {
            $field = [
                'name' => $config['name'],
                'label' => $config['label'],
                'value' => $customFieldsData[ $config['name'] ] ?? '',
                'description' => $config['description'] ?? '',
            ];

            switch ( $config['type'] ) {
                case 'text':
                    $fields[] = FormInput::text( ...$field );
                    break;
                case 'textarea':
                    $fields[] = FormInput::textarea( ...$field );
                    break;
                case 'number':
                    $fields[] = FormInput::number( ...$field );
                    break;
                case 'email':
                    $fields[] = FormInput::email( ...$field );
                    break;
                case 'date':
                    $fields[] = FormInput::date( ...$field );
                    break;
            }
        }

        if ( ! empty( $fields ) ) {
            $form['tabs']['custom_fields'] = CrudForm::tab(
                identifier: 'custom_fields',
                label: __( 'Custom Fields' ),
                fields: $fields
            );
        }

        return $form;
    }
}
