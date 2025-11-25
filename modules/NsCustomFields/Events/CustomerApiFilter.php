<?php

namespace Modules\NsCustomFields\Events;

use App\Models\UserAttribute;

class CustomerApiFilter
{
    /**
     * Add custom fields to customer API response
     * 
     * @param mixed $customer
     * @return mixed
     */
    public static function addCustomFieldsToResponse($customer)
    {
        // Handle both single customer and collection
        if ($customer instanceof \Illuminate\Database\Eloquent\Collection) {
            // Collection of customers
            $customer->each(function ($item) {
                self::attachCustomFields($item);
            });
        } elseif ($customer instanceof \App\Models\Customer) {
            // Single customer
            self::attachCustomFields($customer);
        }

        return $customer;
    }

    /**
     * Attach custom fields to a customer model
     * 
     * @param \App\Models\Customer $customer
     * @return void
     */
    protected static function attachCustomFields($customer)
    {
        $attribute = UserAttribute::where('user_id', $customer->id)->first();
        
        if ($attribute && $attribute->custom_fields) {
            $customFieldsData = json_decode($attribute->custom_fields, true);
            $customer->custom_fields = $customFieldsData ?? [];
        } else {
            $customer->custom_fields = [];
        }
    }
}
