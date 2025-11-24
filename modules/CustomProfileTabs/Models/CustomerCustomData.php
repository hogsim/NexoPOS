<?php

namespace Modules\CustomProfileTabs\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Customer;

class CustomerCustomData extends Model
{
    protected $table = 'nexopos_custom_profile_tabs_customer_data';

    protected $fillable = [
        'customer_id',
        'preferred_contact',
        'customer_type',
        'tax_id',
        'notes',
        'referral_source',
    ];

    /**
     * Define relationship to Customer
     */
    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }
}
