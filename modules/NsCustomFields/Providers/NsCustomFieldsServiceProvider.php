<?php


namespace Modules\NsCustomFields\Providers;

use Illuminate\Support\ServiceProvider;
use Modules\NsCustomFields\Events\UserProfileFilter;
use Modules\NsCustomFields\Events\UserEvent;
use Modules\NsCustomFields\Events\CustomerCrudFilter;
use Modules\NsCustomFields\Events\CustomerEvent;
use Modules\NsCustomFields\Events\CustomerInputFilter;
use Modules\NsCustomFields\Events\CustomerApiFilter;
use TorMorten\Eventy\Facades\Events as Hook;
use Illuminate\Support\Facades\Event;
use App\Events\UserAfterUpdatedEvent;
use App\Events\CustomerAfterCreatedEvent;
use App\Events\CustomerAfterUpdatedEvent;
use Modules\NsCustomFields\Settings\CustomFieldsSettings;

class NsCustomFieldsServiceProvider extends ServiceProvider
{
    public function boot()
    {
        // Register views
        $this->loadViewsFrom(__DIR__.'/../Resources/views', 'NsCustomFields');
        
        // Register routes
        $this->loadRoutesFrom(__DIR__.'/../Routes/api.php');
        
        // User Profile - Disabled (only showing in Customers)
        // Hook::addFilter( 'ns-user-profile-form', [ UserProfileFilter::class, 'registerTab' ] );
        // Event::listen( UserAfterUpdatedEvent::class, [ UserEvent::class, 'saveCustomFields' ] );

        // Customer Profile
        Hook::addFilter( 'App\Crud\CustomerCrud@getForm', [ CustomerCrudFilter::class, 'registerTab' ], 10, 2 );
        Hook::addFilter( 'App\Crud\CustomerCrud@filterPostInputs', [ CustomerInputFilter::class, 'filterInputs' ], 10, 2 );
        Hook::addFilter( 'App\Crud\CustomerCrud@filterPutInputs', [ CustomerInputFilter::class, 'filterInputs' ], 10, 2 );
        Event::listen( CustomerAfterCreatedEvent::class, [ CustomerEvent::class, 'saveCustomFields' ] );
        Event::listen( CustomerAfterUpdatedEvent::class, [ CustomerEvent::class, 'saveCustomFields' ] );

        // Register custom component script - removed as we use inline script now
        
        // Customer API - Add custom fields to responses
        Hook::addFilter('ns.api.customers.get', [CustomerApiFilter::class, 'addCustomFieldsToResponse']);
        
        // Settings Page - Add to menu
        Hook::addFilter( 'ns-dashboard-menus', function( $menus ) {
            if ( isset( $menus['settings'] ) ) {
                $menus['settings']['childrens']['custom_fields'] = [
                    'label' => __( 'Custom Fields' ),
                    'identifier' => 'custom-fields',
                    'href' => ns()->url( '/dashboard/settings/ns.custom-fields' )
                ];
            }
            return $menus;
        } );
    }

    public function register()
    {
        //
    }
}
