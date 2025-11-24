<?php

namespace Modules\CustomProfileTabs\Providers;

use App\Classes\Hook;
use App\Services\Helper;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Event;
use Modules\CustomProfileTabs\Models\UserCustomData;
use Modules\CustomProfileTabs\Models\CustomerCustomData;
use Modules\CustomProfileTabs\Listeners\SaveUserCustomDataListener;
use Modules\CustomProfileTabs\Listeners\SaveCustomerCustomDataListener;
use App\Events\CustomerAfterCreatedEvent;
use App\Events\CustomerAfterUpdatedEvent;

class CustomProfileTabsServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap services.
     */
    public function boot()
    {
        // Add custom tab to user profile
        Hook::addFilter('ns-user-profile-form', [$this, 'addUserProfileTab']);

        // Hook to save user profile custom data
        Hook::addAction('ns.after-user-profile-saved', [SaveUserCustomDataListener::class, 'handle']);

        // Add custom tab to customer CRUD form
        Hook::addFilter('ns.crud.form', [$this, 'addCustomerProfileTab'], 10, 2);

        // Listen to customer events to save custom data
        Event::listen(CustomerAfterCreatedEvent::class, [SaveCustomerCustomDataListener::class, 'handleCreated']);
        Event::listen(CustomerAfterUpdatedEvent::class, [SaveCustomerCustomDataListener::class, 'handleUpdated']);
    }

    /**
     * Add custom tab to user profile form
     *
     * @param array $tabs
     * @return array
     */
    public function addUserProfileTab($tabs)
    {
        // Get existing custom data for the user
        $customData = UserCustomData::where('user_id', Auth::id())->first();

        $tabs['custom'] = [
            'label' => __('Custom Info'),
            'fields' => [
                [
                    'label' => __('Company Name'),
                    'name' => 'company_name',
                    'value' => $customData->company_name ?? '',
                    'type' => 'text',
                    'description' => __('Enter your company name.'),
                ],
                [
                    'label' => __('Job Title'),
                    'name' => 'job_title',
                    'value' => $customData->job_title ?? '',
                    'type' => 'text',
                    'description' => __('Enter your job title.'),
                ],
                [
                    'label' => __('Department'),
                    'name' => 'department',
                    'value' => $customData->department ?? '',
                    'type' => 'select',
                    'options' => Helper::kvToJsOptions([
                        '' => __('Select Department'),
                        'sales' => __('Sales'),
                        'management' => __('Management'),
                        'support' => __('Support'),
                        'technical' => __('Technical'),
                        'other' => __('Other'),
                    ]),
                    'description' => __('Select your department.'),
                ],
                [
                    'label' => __('Bio'),
                    'name' => 'bio',
                    'value' => $customData->bio ?? '',
                    'type' => 'textarea',
                    'description' => __('Tell us about yourself.'),
                ],
                [
                    'label' => __('LinkedIn Profile'),
                    'name' => 'linkedin_url',
                    'value' => $customData->linkedin_url ?? '',
                    'type' => 'text',
                    'description' => __('Enter your LinkedIn profile URL.'),
                ],
            ],
        ];

        return $tabs;
    }

    /**
     * Add custom tab to customer CRUD form
     *
     * @param array $form
     * @param string $namespace
     * @return array
     */
    public function addCustomerProfileTab($form, $namespace)
    {
        // Only apply to customer CRUD
        if ($namespace !== 'ns.customers') {
            return $form;
        }

        // Get the customer ID if editing (from URL or entry)
        $customerId = request()->route('id');
        $customData = null;

        if ($customerId) {
            $customData = CustomerCustomData::where('customer_id', $customerId)->first();
        }

        // Add custom tab to the tabs array
        $form['tabs'][] = [
            'label' => __('Custom Data'),
            'identifier' => 'custom_data',
            'fields' => [
                [
                    'label' => __('Preferred Contact Method'),
                    'name' => 'preferred_contact',
                    'value' => $customData->preferred_contact ?? '',
                    'type' => 'select',
                    'options' => Helper::kvToJsOptions([
                        '' => __('Select Method'),
                        'email' => __('Email'),
                        'phone' => __('Phone'),
                        'sms' => __('SMS'),
                    ]),
                    'description' => __('How should we contact this customer?'),
                ],
                [
                    'label' => __('Customer Type'),
                    'name' => 'customer_type',
                    'value' => $customData->customer_type ?? '',
                    'type' => 'select',
                    'options' => Helper::kvToJsOptions([
                        '' => __('Select Type'),
                        'retail' => __('Retail'),
                        'wholesale' => __('Wholesale'),
                        'vip' => __('VIP'),
                    ]),
                    'description' => __('Specify the customer type.'),
                ],
                [
                    'label' => __('Tax ID / VAT Number'),
                    'name' => 'tax_id',
                    'value' => $customData->tax_id ?? '',
                    'type' => 'text',
                    'description' => __('Enter customer tax ID or VAT number.'),
                ],
                [
                    'label' => __('Notes'),
                    'name' => 'notes',
                    'value' => $customData->notes ?? '',
                    'type' => 'textarea',
                    'description' => __('Add any special notes about this customer.'),
                ],
                [
                    'label' => __('Referral Source'),
                    'name' => 'referral_source',
                    'value' => $customData->referral_source ?? '',
                    'type' => 'text',
                    'description' => __('How did this customer find us?'),
                ],
            ],
        ];

        return $form;
    }
}
