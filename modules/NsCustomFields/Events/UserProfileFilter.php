<?php

namespace Modules\NsCustomFields\Events;

use App\Classes\CrudForm;
use App\Classes\FormInput;
use App\Models\UserAttribute;
use Illuminate\Support\Facades\Auth;

class UserProfileFilter
{
    public static function registerTab( $tabs )
    {
        $user = Auth::user();
        $attribute = UserAttribute::where( 'user_id', $user->id )->first();
        $customFieldsData = $attribute ? json_decode( $attribute->custom_fields, true ) : [];
        
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
            $tabs['custom_fields'] = CrudForm::tab(
                identifier: 'custom_fields',
                label: __( 'Custom Fields' ),
                fields: $fields
            );
        }

        return $tabs;
    }
}
