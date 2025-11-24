<?php

namespace Modules\CustomProfileTabs\Listeners;

use App\Events\CustomerAfterCreatedEvent;
use App\Events\CustomerAfterUpdatedEvent;
use Modules\CustomProfileTabs\Models\CustomFieldDefinition;
use Modules\CustomProfileTabs\Models\CustomFieldValue;

class SaveCustomerCustomDataListener
{
    /**
     * Handle customer created event
     */
    public function handleCreated(CustomerAfterCreatedEvent $event)
    {
        $this->saveCustomerData($event->customer->id);
    }

    /**
     * Handle customer updated event
     */
    public function handleUpdated(CustomerAfterUpdatedEvent $event)
    {
        $this->saveCustomerData($event->customer->id);
    }

    /**
     * Save customer custom data dynamically
     */
    private function saveCustomerData($customerId)
    {
        $request = request();

        if ($request->has('custom_fields')) {
            // Get all active field definitions for customers
            $fieldDefinitions = CustomFieldDefinition::getActiveFields('customer');

            foreach ($fieldDefinitions as $fieldDef) {
                $fieldName = $fieldDef->name;
                $fieldValue = $request->input('custom_fields.' . $fieldName);

                // Save or update the field value
                CustomFieldValue::updateOrCreate(
                    [
                        'field_id' => $fieldDef->id,
                        'entity_id' => $customerId,
                        'entity_type' => 'customer',
                    ],
                    [
                        'value' => $fieldValue ?? '',
                    ]
                );
            }
        }
    }
}
