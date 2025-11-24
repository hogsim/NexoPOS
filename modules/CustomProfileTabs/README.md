# Custom Profile Tabs Module

A comprehensive NexoPOS module that demonstrates how to add custom tabs and fields to both user and customer profiles.

## Overview

This module extends NexoPOS by adding:
- **Custom tabs to user profiles** with additional fields for company information, job title, department, bio, and LinkedIn profile
- **Custom tabs to customer profiles** with fields for preferred contact method, customer type, tax ID, notes, and referral source
- Proper data persistence using database migrations
- Event-driven architecture for saving custom data

## Features

### User Profile Extension
- Company Name field
- Job Title field
- Department dropdown (Sales, Management, Support, Technical, Other)
- Bio textarea
- LinkedIn Profile URL field

### Customer Profile Extension
- Preferred Contact Method dropdown (Email, Phone, SMS)
- Customer Type dropdown (Retail, Wholesale, VIP)
- Tax ID / VAT Number field
- Notes textarea
- Referral Source field

## Installation

1. Copy the `CustomProfileTabs` folder to your NexoPOS `modules` directory:
   ```
   /modules/CustomProfileTabs/
   ```

2. The module will be automatically discovered by NexoPOS through the `config.xml` file.

3. Run the module migrations to create the required database tables:
   ```bash
   php artisan module:migrate CustomProfileTabs
   ```

   Or if your NexoPOS installation uses a different command:
   ```bash
   php artisan migrate
   ```

4. Clear the cache:
   ```bash
   php artisan config:clear
   php artisan cache:clear
   ```

## Module Structure

```
CustomProfileTabs/
├── config.xml                           # Module configuration
├── CustomProfileTabsModule.php          # Main module class
├── Migrations/                          # Database migrations
│   ├── CreateUserCustomDataTable.php    # User custom data table
│   └── CreateCustomerCustomDataTable.php # Customer custom data table
├── Models/                              # Eloquent models
│   ├── UserCustomData.php               # User custom data model
│   └── CustomerCustomData.php           # Customer custom data model
├── Providers/                           # Service providers
│   └── CustomProfileTabsServiceProvider.php # Main service provider
├── Listeners/                           # Event listeners
│   ├── SaveUserCustomDataListener.php   # Handles user data saving
│   └── SaveCustomerCustomDataListener.php # Handles customer data saving
└── README.md                            # This file
```

## How It Works

### User Profile Tabs

1. **Hook Registration**: The module uses the `ns-user-profile-form` filter hook to add a custom tab to the user profile form.

2. **Data Display**: When a user views their profile, the module retrieves existing custom data from the database and displays it in the custom tab.

3. **Data Saving**: When the user saves their profile, the module listens to the `ns.after-user-profile-saved` action hook and saves the custom field data to the `nexopos_custom_profile_tabs_user_data` table.

### Customer Profile Tabs

1. **Hook Registration**: The module uses the `ns.crud.form` filter hook to add a custom tab specifically to the customer CRUD form.

2. **Data Display**: When editing a customer, the module retrieves and displays existing custom data from the `nexopos_custom_profile_tabs_customer_data` table.

3. **Data Saving**: The module listens to Laravel events `CustomerAfterCreatedEvent` and `CustomerAfterUpdatedEvent` to save custom field data whenever a customer is created or updated.

## Code Examples

### Adding a Custom Tab to User Profiles

```php
// In your ServiceProvider's boot method
Hook::addFilter('ns-user-profile-form', [$this, 'addUserProfileTab']);

public function addUserProfileTab($tabs)
{
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
            // More fields...
        ],
    ];

    return $tabs;
}
```

### Adding a Custom Tab to Customer Profiles

```php
// In your ServiceProvider's boot method
Hook::addFilter('ns.crud.form', [$this, 'addCustomerProfileTab'], 10, 2);

public function addCustomerProfileTab($form, $namespace)
{
    if ($namespace !== 'ns.customers') {
        return $form;
    }

    $customerId = request()->route('id');
    $customData = CustomerCustomData::where('customer_id', $customerId)->first();

    $form['tabs'][] = [
        'label' => __('Custom Data'),
        'identifier' => 'custom_data',
        'fields' => [
            [
                'label' => __('Customer Type'),
                'name' => 'customer_type',
                'value' => $customData->customer_type ?? '',
                'type' => 'select',
                'options' => Helper::kvToJsOptions([
                    'retail' => __('Retail'),
                    'wholesale' => __('Wholesale'),
                    'vip' => __('VIP'),
                ]),
            ],
            // More fields...
        ],
    ];

    return $form;
}
```

### Saving Custom Data

```php
// Listen to events
Event::listen(CustomerAfterCreatedEvent::class, [SaveCustomerCustomDataListener::class, 'handleCreated']);
Event::listen(CustomerAfterUpdatedEvent::class, [SaveCustomerCustomDataListener::class, 'handleUpdated']);

// In your listener
public function handleCreated(CustomerAfterCreatedEvent $event)
{
    $request = request();

    if ($request->has('custom_data')) {
        CustomerCustomData::create([
            'customer_id' => $event->customer->id,
            'customer_type' => $request->input('custom_data.customer_type'),
            // More fields...
        ]);
    }
}
```

## Available Field Types

NexoPOS supports various field types for form inputs:

- `text` - Single-line text input
- `textarea` - Multi-line text input
- `select` - Dropdown selection
- `number` - Numeric input
- `email` - Email input with validation
- `datetime` - Date and time picker
- `media` - Media uploader
- `searchSelect` - Searchable dropdown with remote data
- `switch` - Toggle switch (boolean)
- `checkbox` - Checkbox input

## Database Tables

### nexopos_custom_profile_tabs_user_data

Stores custom data for user profiles.

| Column | Type | Description |
|--------|------|-------------|
| id | bigint | Primary key |
| user_id | bigint | Foreign key to nexopos_users |
| company_name | varchar | Company name |
| job_title | varchar | Job title |
| department | varchar | Department |
| bio | text | Biography |
| linkedin_url | varchar | LinkedIn profile URL |
| created_at | timestamp | Created timestamp |
| updated_at | timestamp | Updated timestamp |

### nexopos_custom_profile_tabs_customer_data

Stores custom data for customer profiles.

| Column | Type | Description |
|--------|------|-------------|
| id | bigint | Primary key |
| customer_id | bigint | Foreign key to nexopos_users |
| preferred_contact | varchar | Preferred contact method |
| customer_type | varchar | Customer type |
| tax_id | varchar | Tax ID / VAT number |
| notes | text | Customer notes |
| referral_source | varchar | How customer found us |
| created_at | timestamp | Created timestamp |
| updated_at | timestamp | Updated timestamp |

## Hooks and Events Used

### Filters (Hooks)
- `ns-user-profile-form` - Modify user profile form tabs
- `ns.crud.form` - Modify CRUD form structure (used for customers)

### Actions (Hooks)
- `ns.after-user-profile-saved` - Triggered after user profile is saved

### Laravel Events
- `CustomerAfterCreatedEvent` - Triggered after customer is created
- `CustomerAfterUpdatedEvent` - Triggered after customer is updated

## Customization

### Adding More Fields

To add more fields to the user profile:

1. Add the column to the migration in `Migrations/CreateUserCustomDataTable.php`
2. Add the field to the `$fillable` array in `Models/UserCustomData.php`
3. Add the field definition in the `addUserProfileTab()` method
4. Update the save logic in `Listeners/SaveUserCustomDataListener.php`

### Adding More Fields to Customer Profile

1. Add the column to the migration in `Migrations/CreateCustomerCustomDataTable.php`
2. Add the field to the `$fillable` array in `Models/CustomerCustomData.php`
3. Add the field definition in the `addCustomerProfileTab()` method
4. Update the save logic in `Listeners/SaveCustomerCustomDataListener.php`

## Troubleshooting

### Tab Not Showing

1. Clear the cache: `php artisan config:clear && php artisan cache:clear`
2. Verify the module is in the `modules` folder
3. Check that migrations have been run
4. Ensure the service provider is loaded (check logs)

### Data Not Saving

1. Check Laravel logs: `storage/logs/laravel.log`
2. Verify database tables exist
3. Ensure event listeners are registered
4. Check request data is being sent correctly

### Migration Errors

If migrations fail:
```bash
# Rollback and retry
php artisan migrate:rollback
php artisan migrate
```

## Best Practices

1. **Always validate input data** before saving to prevent security issues
2. **Use translations** with `__()` function for all user-facing text
3. **Follow NexoPOS naming conventions** for database tables (use `nexopos_` prefix)
4. **Use foreign key constraints** to maintain data integrity
5. **Index foreign keys** for better query performance
6. **Handle null values** gracefully when displaying existing data

## Development Tips

- Use `dd($variable)` or `dump($variable)` for debugging
- Check NexoPOS core files for similar implementations
- Test both create and edit scenarios
- Verify data is saved correctly in the database
- Test with different user roles and permissions

## Resources

- [NexoPOS Documentation](https://my.nexopos.com/en/documentation)
- [Module Development Guide](https://my.nexopos.com/en/documentation/module-development)
- [Adding Custom Tabs](https://my.nexopos.com/en/documentation/module-development/user-profile-add-custom-tab)

## License

This module is provided as an example for educational purposes. Feel free to modify and use it in your projects.

## Support

For questions or issues:
1. Check the NexoPOS documentation
2. Review the code comments
3. Join the NexoPOS community forums
4. Open an issue on the NexoPOS repository

## Version History

### 1.0.0 (Initial Release)
- Custom tabs for user profiles
- Custom tabs for customer profiles
- Database migrations
- Event-driven data persistence
- Comprehensive documentation
