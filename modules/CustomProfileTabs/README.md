# Custom Profile Tabs Module

A powerful NexoPOS module that allows you to **dynamically add custom fields to user and customer profiles through a web-based admin interface** - no code required!

## Overview

This module provides a complete field management system where you can:
- **Create custom fields via GUI** - No coding needed!
- **Choose from 8 field types** - text, textarea, number, email, select, datetime, switch, media
- **Add fields to user or customer profiles** - Select where each field appears
- **Set validation rules** - Require fields, validate emails, set min/max lengths
- **Reorder fields** - Control the display order
- **Enable/disable fields** - Turn fields on/off without deleting them
- **Define dropdown options** - Create select fields with custom options

## Features

### 🎛️ Web-Based Field Management Interface
- **CRUD Interface**: Create, read, update, and delete custom fields through a user-friendly web interface
- **No Coding Required**: All field management is done through the admin panel
- **Real-Time Updates**: Changes apply immediately to user and customer profiles

### 📋 Supported Field Types
1. **Text Input** - Single-line text field
2. **Text Area** - Multi-line text for longer content
3. **Number** - Numeric input with validation
4. **Email** - Email input with format validation
5. **Dropdown/Select** - Custom dropdown options (defined via JSON)
6. **Date & Time** - Date and time picker
7. **Toggle Switch** - Yes/No boolean field
8. **Media Upload** - Image/file uploader

### ⚙️ Field Configuration Options
- **Field Name**: Internal identifier (e.g., `company_name`)
- **Label**: Display name shown to users (e.g., "Company Name")
- **Description**: Help text displayed below the field
- **Validation Rules**: Laravel validation (e.g., `required|email`, `min:3|max:100`)
- **Display Order**: Control the sequence of fields
- **Active Status**: Enable or disable without deletion

### 🔧 Profile Integration
- **User Profiles**: Add custom fields to logged-in user profiles
- **Customer Profiles**: Add custom fields to customer management forms
- **Dynamic Loading**: Fields are loaded from database at runtime
- **Automatic Saving**: Field values are saved automatically when profiles are updated

## Installation

### 1. Place the Module
The module is already in your `/modules/CustomProfileTabs/` directory.

### 2. Run Migrations
Create the required database tables:

```bash
php artisan migrate
```

This creates two tables:
- `nexopos_custom_field_definitions` - Stores field configurations
- `nexopos_custom_field_values` - Stores field data

### 3. Clear Cache
```bash
php artisan config:clear
php artisan cache:clear
```

### 4. Access the Admin Interface
Navigate to:
```
/dashboard/customprofiletabs/fields
```

## Usage Guide

### Creating a Custom Field

1. **Access the field management page**:
   - Go to `/dashboard/customprofiletabs/fields`
   - Click "Add New Field"

2. **Configure the field**:
   - **Field Name**: Enter an internal name (lowercase, underscores only)
     - Example: `company_name`, `tax_id`, `preferred_language`
   - **Label**: Enter the display name users will see
     - Example: "Company Name", "Tax ID", "Preferred Language"
   - **Field Type**: Select from 8 available types
   - **Applies To**: Choose "User Profile" or "Customer Profile"
   - **Description** (optional): Add help text
   - **Options** (for select fields): Define dropdown options in JSON format
   - **Validation** (optional): Add Laravel validation rules
   - **Display Order**: Set the position (0, 10, 20, etc.)
   - **Active**: Toggle to enable/disable

3. **Save the field**: Click "Submit"

4. **View the result**: The field now appears on the selected profile type!

### Example: Creating a Dropdown Field

To create a "Customer Type" dropdown for customer profiles:

**Field Configuration:**
- Field Name: `customer_type`
- Label: `Customer Type`
- Field Type: `Dropdown/Select`
- Applies To: `Customer Profile`
- Description: `Select the customer category`
- Options (JSON):
  ```json
  {
    "retail": "Retail Customer",
    "wholesale": "Wholesale Customer",
    "vip": "VIP Customer"
  }
  ```
- Validation: `required`
- Display Order: `10`
- Active: `Yes`

### Example: Creating a Text Field with Validation

To create a "LinkedIn Profile" field for user profiles:

**Field Configuration:**
- Field Name: `linkedin_url`
- Label: `LinkedIn Profile URL`
- Field Type: `Text Input`
- Applies To: `User Profile`
- Description: `Enter your LinkedIn profile URL`
- Validation: `url` (ensures valid URL format)
- Display Order: `20`
- Active: `Yes`

## Module Structure

```
CustomProfileTabs/
├── config.xml                                      # Module metadata
├── CustomProfileTabsModule.php                     # Main module class
├── Crud/
│   └── CustomFieldDefinitionCrud.php               # CRUD for field management
├── Http/
│   └── Controllers/
│       └── CustomFieldsController.php              # Web controllers
├── Listeners/
│   ├── SaveUserCustomDataListener.php              # Handles user data saving
│   └── SaveCustomerCustomDataListener.php          # Handles customer data saving
├── Migrations/
│   ├── CreateCustomFieldDefinitionsTable.php       # Field definitions table
│   └── CreateCustomFieldValuesTable.php            # Field values table
├── Models/
│   ├── CustomFieldDefinition.php                   # Field definition model
│   └── CustomFieldValue.php                        # Field value model
├── Providers/
│   └── CustomProfileTabsServiceProvider.php        # Service provider
├── Resources/
│   └── Views/
│       └── fields/
│           ├── index.blade.php                     # List view
│           ├── create.blade.php                    # Create form
│           └── edit.blade.php                      # Edit form
├── Routes/
│   └── web.php                                     # Web routes
└── README.md                                       # This file
```

## Database Schema

### nexopos_custom_field_definitions

Stores the field configuration.

| Column | Type | Description |
|--------|------|-------------|
| id | bigint | Primary key |
| name | varchar | Internal field name |
| label | varchar | Display label |
| type | enum | Field type (text, textarea, number, email, select, datetime, switch, media) |
| applies_to | enum | Where field appears (user, customer) |
| description | text | Help text (optional) |
| options | json | Dropdown options for select fields |
| validation | varchar | Laravel validation rules |
| order | integer | Display order |
| active | boolean | Enable/disable field |
| created_at | timestamp | Created timestamp |
| updated_at | timestamp | Updated timestamp |

### nexopos_custom_field_values

Stores the actual field data for each user/customer.

| Column | Type | Description |
|--------|------|-------------|
| id | bigint | Primary key |
| field_id | bigint | Foreign key to custom_field_definitions |
| entity_id | bigint | User ID or Customer ID |
| entity_type | enum | Type (user, customer) |
| value | text | The field value |
| created_at | timestamp | Created timestamp |
| updated_at | timestamp | Updated timestamp |

## How It Works

### Architecture Overview

1. **Field Definition**: Admin defines fields via web interface
2. **Dynamic Loading**: Service provider loads active fields from database
3. **Profile Integration**: Fields are injected into user/customer profile forms
4. **Data Persistence**: Listeners save field values when profiles are updated
5. **Retrieval**: Values are loaded and displayed when profiles are viewed

### Technical Flow

#### For User Profiles:
1. User visits their profile page
2. `addUserProfileTab()` method queries active user fields
3. Existing values are loaded from `custom_field_values` table
4. Fields are rendered in "Custom Fields" tab
5. On save, `SaveUserCustomDataListener` saves all field values

#### For Customer Profiles:
1. Admin views customer create/edit page
2. `addCustomerProfileTab()` method queries active customer fields
3. Existing values are loaded (if editing)
4. Fields are rendered in "Custom Fields" tab
5. On save, `SaveCustomerCustomDataListener` saves all field values

### Code Hooks Used

- **`ns-user-profile-form`**: Filter to add tabs to user profile
- **`ns.crud.form`**: Filter to modify CRUD forms (for customers)
- **`ns.after-user-profile-saved`**: Action after user profile is saved
- **`CustomerAfterCreatedEvent`**: Event after customer creation
- **`CustomerAfterUpdatedEvent`**: Event after customer update

## Admin Interface Routes

| Route | Purpose |
|-------|---------|
| `/dashboard/customprofiletabs/fields` | List all custom fields |
| `/dashboard/customprofiletabs/fields/create` | Create new field |
| `/dashboard/customprofiletabs/fields/edit/{id}` | Edit existing field |
| `/api/crud/customprofiletabs.fields` | CRUD API endpoint |

## Validation Rules Examples

You can use any Laravel validation rule in the "Validation Rules" field:

- `required` - Field must be filled
- `email` - Must be valid email format
- `url` - Must be valid URL
- `min:3` - Minimum 3 characters
- `max:100` - Maximum 100 characters
- `numeric` - Must be a number
- `required|email` - Required AND must be email
- `min:3|max:50` - Between 3 and 50 characters
- `in:option1,option2` - Must be one of specified values

## JSON Options Format for Select Fields

For dropdown fields, provide options in JSON format:

```json
{
  "value1": "Display Label 1",
  "value2": "Display Label 2",
  "value3": "Display Label 3"
}
```

**Examples:**

Customer Types:
```json
{
  "retail": "Retail",
  "wholesale": "Wholesale",
  "vip": "VIP",
  "corporate": "Corporate"
}
```

Contact Preferences:
```json
{
  "email": "Email",
  "phone": "Phone",
  "sms": "SMS",
  "mail": "Postal Mail"
}
```

Languages:
```json
{
  "en": "English",
  "fr": "French",
  "es": "Spanish",
  "de": "German"
}
```

## Troubleshooting

### Fields Not Showing in Profiles

1. **Check field is active**: Verify the "Active" checkbox is enabled
2. **Check applies_to**: Ensure field is assigned to correct profile type
3. **Clear cache**: Run `php artisan cache:clear`
4. **Check database**: Verify field exists in `nexopos_custom_field_definitions`

### Values Not Saving

1. **Check Laravel logs**: `storage/logs/laravel.log`
2. **Verify migrations**: Ensure tables exist in database
3. **Check field name**: Must match exactly (case-sensitive)
4. **Test validation**: Check if validation rules are too strict

### Dropdown Not Working

1. **Validate JSON**: Ensure options are valid JSON format
2. **Check quotes**: Use double quotes, not single quotes
3. **Format check**: Must be `{"key": "value"}` format
4. **Test online**: Use JSONLint.com to validate JSON

### Migration Errors

```bash
# Rollback and retry
php artisan migrate:rollback
php artisan migrate

# Or reset all migrations (CAUTION: deletes data)
php artisan migrate:fresh
```

## Advanced Usage

### Adding Fields Programmatically

While the GUI is recommended, you can also create fields via code:

```php
use Modules\CustomProfileTabs\Models\CustomFieldDefinition;

CustomFieldDefinition::create([
    'name' => 'company_name',
    'label' => 'Company Name',
    'type' => 'text',
    'applies_to' => 'user',
    'description' => 'Enter your company name',
    'validation' => 'required|min:3',
    'order' => 10,
    'active' => true,
]);
```

### Querying Field Values

```php
use Modules\CustomProfileTabs\Models\CustomFieldValue;

// Get all values for a user
$values = CustomFieldValue::where('entity_id', $userId)
    ->where('entity_type', 'user')
    ->with('field')
    ->get();

// Get specific field value
$value = CustomFieldValue::where('entity_id', $userId)
    ->where('entity_type', 'user')
    ->whereHas('field', function($query) {
        $query->where('name', 'company_name');
    })
    ->first()
    ->value;
```

## Best Practices

### Field Naming
- ✅ Use lowercase with underscores: `company_name`, `tax_id`
- ❌ Don't use spaces: `company name`
- ❌ Don't use special characters: `company-name`, `company.name`
- ❌ Don't start with numbers: `1st_name`

### Display Order
- Use increments of 10: `0, 10, 20, 30...`
- This allows inserting fields between existing ones later
- Lower numbers appear first

### Validation
- Only add validation when necessary
- Test validation rules before deploying
- Consider user experience (not too restrictive)

### Field Organization
- Group related fields with similar order numbers
- Use descriptive labels
- Provide clear descriptions

## Examples & Use Cases

### Use Case 1: B2B Customer Information

Create these fields for customer profiles:
- `company_name` (text, required)
- `tax_id` (text, required)
- `business_type` (select: retail/wholesale/distributor)
- `credit_limit` (number)
- `account_manager` (text)

### Use Case 2: User Preferences

Create these fields for user profiles:
- `notification_preference` (select: email/sms/both)
- `language` (select: en/fr/es/de)
- `timezone` (select with timezone options)
- `department` (select with department list)

### Use Case 3: Marketing Data

Create these fields for customer profiles:
- `referral_source` (text)
- `marketing_consent` (switch yes/no)
- `preferred_contact_time` (text)
- `interests` (textarea)

## Security Considerations

- All field values are sanitized before storage
- Validation rules are enforced
- Only authenticated users can access the admin interface
- SQL injection protected through Eloquent ORM
- XSS protection through Laravel's  blade templating

## Support & Resources

- **NexoPOS Documentation**: https://my.nexopos.com/en/documentation
- **Laravel Validation**: https://laravel.com/docs/validation
- **JSON Validator**: https://jsonlint.com

## Version History

### 2.0.0 (Current - Dynamic Fields)
- ✨ Web-based field management GUI
- ✨ Dynamic field loading from database
- ✨ 8 field types supported
- ✨ Validation rules support
- ✨ Dropdown options via JSON
- ✨ Field ordering and activation
- 🗄️ New database structure
- 📚 Comprehensive documentation

### 1.0.0 (Initial - Hardcoded Fields)
- Basic custom tabs with hardcoded fields
- User and customer profile extensions
- Static field definitions

## License

This module is provided as an educational resource. Feel free to modify and use it in your NexoPOS projects.

---

**Need Help?** Check the troubleshooting section or review the NexoPOS documentation for module development guidelines.
