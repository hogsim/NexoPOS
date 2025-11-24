<?php

namespace Modules\CustomProfileTabs\Listeners;

use Illuminate\Support\Facades\Auth;
use Modules\CustomProfileTabs\Models\CustomFieldDefinition;
use Modules\CustomProfileTabs\Models\CustomFieldValue;

class SaveUserCustomDataListener
{
    /**
     * Handle the event - save dynamic user custom fields
     */
    public function handle($event)
    {
        $request = request();

        if ($request->has('custom_fields')) {
            // Get all active field definitions for users
            $fieldDefinitions = CustomFieldDefinition::getActiveFields('user');

            foreach ($fieldDefinitions as $fieldDef) {
                $fieldName = $fieldDef->name;
                $fieldValue = $request->input('custom_fields.' . $fieldName);

                // Save or update the field value
                CustomFieldValue::updateOrCreate(
                    [
                        'field_id' => $fieldDef->id,
                        'entity_id' => Auth::id(),
                        'entity_type' => 'user',
                    ],
                    [
                        'value' => $fieldValue ?? '',
                    ]
                );
            }
        }
    }
}
