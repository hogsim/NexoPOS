<?php

namespace Modules\CustomProfileTabs\Listeners;

use App\Events\CustomerAfterCreatedEvent;
use App\Events\CustomerAfterUpdatedEvent;
use Modules\CustomProfileTabs\Models\CustomerCustomData;

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
     * Save customer custom data
     */
    private function saveCustomerData($customerId)
    {
        $request = request();

        if ($request->has('custom_data')) {
            $customData = CustomerCustomData::firstOrNew([
                'customer_id' => $customerId,
            ]);

            $customData->preferred_contact = $request->input('custom_data.preferred_contact');
            $customData->customer_type = $request->input('custom_data.customer_type');
            $customData->tax_id = $request->input('custom_data.tax_id');
            $customData->notes = $request->input('custom_data.notes');
            $customData->referral_source = $request->input('custom_data.referral_source');

            $customData->save();
        }
    }
}
