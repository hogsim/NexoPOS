# CLAUDE.md - NexoPOS Development Guide

This document provides essential information for AI assistants working with the NexoPOS codebase.

## Project Overview

NexoPOS is a free, open-source Point of Sale (POS) system built with:
- **Backend**: Laravel 12 (PHP 8.2+)
- **Frontend**: Vue.js 3, TailwindCSS 4
- **Build Tool**: Vite 7
- **Database**: MySQL/SQLite
- **Icons**: Line Awesome

## Quick Reference

### Common Commands

```bash
# Install dependencies
composer install
npm install

# Development server
npx vite            # Frontend dev with HMR (port 3331)
php artisan serve   # Backend server

# Build for production
npx vite build

# Run tests
php artisan test                    # Run all tests
vendor/bin/phpunit                  # PHPUnit directly
vendor/bin/paratest                 # Parallel tests

# Database
php artisan migrate                 # Run migrations
php artisan ns:translate --symlink  # Setup translations

# Module management
php artisan module:install ModuleName
php artisan module:migrate ModuleName

# Code formatting
vendor/bin/pint                     # Laravel Pint (PSR-12)
```

### Path Aliases (Vite)
- `&` -> `resources/`
- `~` -> `resources/ts/`

## Architecture Overview

### Directory Structure

```
NexoPOS/
├── app/
│   ├── Classes/          # Helper classes (FormInput, CrudForm, AsideMenu)
│   ├── Console/          # Artisan commands
│   ├── Crud/             # CRUD resource definitions
│   ├── Enums/            # Enumerations
│   ├── Events/           # Event classes
│   ├── Fields/           # Form field definitions
│   ├── Forms/            # Form configurations
│   ├── Http/
│   │   ├── Controllers/  # HTTP controllers
│   │   ├── Middleware/   # Request middleware
│   │   └── Requests/     # Form request validation
│   ├── Jobs/             # Queue jobs
│   ├── Listeners/        # Event listeners (auto-discovered)
│   ├── Models/           # Eloquent models
│   ├── Observers/        # Model observers
│   ├── Providers/        # Service providers
│   ├── Services/         # Business logic services
│   ├── Settings/         # Settings page classes
│   ├── Traits/           # Reusable traits
│   └── Widgets/          # Dashboard widgets
├── config/               # Laravel configuration
├── database/
│   ├── migrations/
│   │   ├── core/         # Core table migrations
│   │   ├── create/       # New table migrations
│   │   └── update/       # Table update migrations
│   ├── permissions/      # Permission definitions
│   └── seeders/          # Database seeders
├── lang/                 # Localization files
├── modules/              # Installed modules (PSR-4 autoloaded)
├── resources/
│   ├── css/              # Stylesheets (themes: light.css, dark.css)
│   ├── ts/               # TypeScript/Vue source
│   │   ├── components/   # Vue components (ns-* prefix)
│   │   ├── libraries/    # Utility libraries
│   │   ├── pages/        # Page-specific components
│   │   ├── popups/       # Popup components
│   │   └── widgets/      # Widget components
│   └── views/            # Blade templates
├── routes/
│   ├── api.php           # API routes
│   ├── web.php           # Web routes
│   └── api/, web/        # Route partials
├── tests/
│   ├── Feature/          # Feature tests
│   └── Traits/           # Test traits
└── modules/              # Module directory (auto-discovered)
```

### Key Services

| Service | Location | Purpose |
|---------|----------|---------|
| `CrudService` | `app/Services/CrudService.php` | Base class for CRUD operations |
| `CustomerService` | `app/Services/CustomerService.php` | Customer management |
| `OrderService` | `app/Services/OrderService.php` | Order processing |
| `ProductService` | `app/Services/ProductService.php` | Product management |
| `DateService` | `app/Services/DateService.php` | Date/timezone handling |
| `CurrencyService` | `app/Services/CurrencyService.php` | Currency formatting |
| `WidgetService` | `app/Services/WidgetService.php` | Dashboard widgets |

### Core Vue Components

| Component | Purpose |
|-----------|---------|
| `ns-crud` | Data table with sorting, filtering, bulk actions |
| `ns-crud-form` | Form for creating/editing records |
| `ns-field` | Dynamic form field renderer |
| `ns-tabs` / `ns-tabs-item` | Tabbed interface |
| `ns-button` | Styled button |
| `ns-input` | Text input field |
| `ns-select` | Dropdown select |
| `ns-datepicker` | Date picker |
| `ns-numpad` | Numeric keypad |
| `ns-spinner` | Loading indicator |

## Development Conventions

### PHP Conventions

1. **Namespacing**: Follow PSR-4 autoloading
   - Core: `App\{Component}\{Class}`
   - Modules: `Modules\{ModuleName}\{Component}\{Class}`

2. **Service Classes**: Business logic in `app/Services/`

3. **CRUD Classes**: Extend `App\Services\CrudService` with:
   ```php
   const AUTOLOAD = true;
   const IDENTIFIER = 'ns.resource-name';
   protected $table = 'nexopos_tablename';
   protected $model = Model::class;
   protected $namespace = 'ns.resource-name';
   ```

4. **Form Inputs**: Use `App\Classes\FormInput` helper:
   ```php
   FormInput::text(label: __('Name'), name: 'name', validation: 'required')
   FormInput::select(label: __('Status'), name: 'status', options: [...])
   FormInput::searchSelect(label: __('Customer'), name: 'customer_id')
   ```

5. **Permissions**: Follow `{action}.{resource}` pattern:
   - `create.products`, `read.products`, `update.products`, `delete.products`

### Frontend Conventions

1. **Vue Components**: Use Options API or Composition API
2. **HTTP Client**: Use `nsHttpClient` (RxJS Observable-based):
   ```javascript
   nsHttpClient.get('/api/products').subscribe({
       next: (data) => { /* handle success */ },
       error: (error) => { /* handle error */ }
   });
   ```
3. **Notifications**: Use `nsSnackBar.success()`, `nsSnackBar.error()`
4. **Translations**: Use `__('Translation Key')` in both PHP and JS

### Blade Layout Patterns

1. **Standard Dashboard Page**:
   ```blade
   @extends('layout.dashboard')
   @section('layout.dashboard.body')
   <div>
       @include(Hook::filter('ns-dashboard-header-file', '../common/dashboard-header'))
       <div id="dashboard-content" class="px-4">
           @include('common.dashboard.title')
           <!-- Content -->
       </div>
   </div>
   @endsection
   ```

2. **Shorthand with Title**:
   ```blade
   @extends('layout.dashboard')
   @section('layout.dashboard.with-title')
       <!-- Content with header and title automatically included -->
   @endsection
   ```

### Database Conventions

- **Table prefix**: `nexopos_` (or `ns_` in tests)
- **Model timestamps**: Use Laravel conventions
- **Soft deletes**: Use when data should be recoverable

## Module Development

### Module Structure

```
modules/ModuleName/
├── config.xml              # Required: Module metadata
├── ModuleNameModule.php    # Required: Entry point
├── Crud/                   # CRUD classes
├── Http/Controllers/       # Controllers
├── Migrations/             # Database migrations
├── Models/                 # Eloquent models
├── Providers/              # Service providers (auto-discovered)
├── Resources/Views/        # Blade templates
├── Routes/                 # web.php, api.php (auto-discovered)
└── vite.config.js          # Optional: Module-specific Vite config
```

### config.xml Structure

```xml
<?xml version="1.0" encoding="UTF-8"?>
<module>
    <namespace>ModuleName</namespace>
    <version>1.0.0</version>
    <author>Author Name</author>
    <name>Human Readable Name</name>
    <description>Module description</description>
</module>
```

### Extending Dashboard Menus

```php
use App\Classes\AsideMenu;

Hook::addFilter('ns.dashboard.menus', function($menus) {
    return array_merge($menus, AsideMenu::wrapper(
        AsideMenu::menu(
            label: __m('Module Menu', 'ModuleName'),
            identifier: 'module-menu',
            icon: 'la-puzzle-piece',
            permissions: ['module.access'],
            childrens: AsideMenu::childrens(
                AsideMenu::subMenu(
                    label: __m('Dashboard', 'ModuleName'),
                    identifier: 'module-dashboard',
                    href: ns()->url('/dashboard/module')
                )
            )
        )
    ));
});
```

## Testing

### Test Configuration

- **Config File**: `phpunit.xml`
- **Database**: SQLite (`tests/database.sqlite`)
- **Environment**: `APP_ENV=TESTING`

### Test Structure

Tests are organized in sequential and parallel suites:
- `Sequential-1`: Database setup, hard reset
- `Parallel-1`: Independent tests (auth, CRUD, options)
- `Sequential-2`: Dependent entity creation (taxes, customers, products)
- `Parallel-2`: Order tests

### Running Specific Tests

```bash
# Single test file
php artisan test --filter=CreateOrderTest

# Test suite
vendor/bin/phpunit --testsuite=Parallel-1

# With parallel execution
vendor/bin/paratest
```

## API Conventions

### REST Endpoints

- **CRUD API**: `/api/crud/{namespace}`
- **Settings API**: `/api/settings/{identifier}`
- **Bulk Actions**: `/api/crud/{namespace}/bulk-actions`

### Response Format

```json
{
    "status": "success|error",
    "message": "Operation result message",
    "data": { /* payload */ }
}
```

## Common Patterns

### Creating a CRUD Resource

1. Create model in `app/Models/`
2. Create CRUD class in `app/Crud/` extending `CrudService`
3. Define `getTable()`, `getForm()`, `getActions()` methods
4. Create controller methods using `CrudClass::table()` and `CrudClass::form()`
5. Register routes

### Adding Permissions

Create file in `database/permissions/`:
```php
<?php
use App\Models\Permission;

if (defined('NEXO_CREATE_PERMISSIONS')) {
    foreach (['create', 'read', 'update', 'delete'] as $crud) {
        $permission = Permission::firstOrNew(['namespace' => $crud . '.resource']);
        $permission->name = ucwords($crud) . ' Resource';
        $permission->namespace = $crud . '.resource';
        $permission->save();
    }
}
```

### Creating Widgets

1. Create widget class extending `App\Services\Widgets`
2. Define `$vueComponent`, `$permission`, `$name`, `$description`
3. Register in service provider using `WidgetService::registerWidgets()`
4. Create Vue component in `resources/ts/widgets/`

## Important Notes

### Security
- Always validate input using Laravel Form Requests
- Use `ns.permission` middleware for route protection
- Check permissions with `Auth::user()->allowedTo('permission.name')`

### Performance
- Use database indexes on frequently queried columns
- Implement pagination for large datasets
- Cache expensive queries where appropriate

### Localization
- Use `__('key')` for core translations
- Use `__m('key', 'ModuleName')` for module translations
- Translation files in `lang/` directory

## Additional Documentation

Detailed documentation is available in `.github/instructions/`:
- `nexopos.instructions.md` - Main architecture overview
- `nexopos-crud.instructions.md` - CRUD system guide
- `nexopos-modules.instructions.md` - Module development
- `nexopos-permissions.instructions.md` - Permission system
- `nexopos-forminput.instructions.md` - Form input helpers
- `nexopos-httpclient.instructions.md` - Frontend HTTP client
- `nexopos-blade-layouts.instructions.md` - Layout templates
- `nexopos-tabs.instructions.md` - Tab components
- `nexopos-asidemenu.instructions.md` - Navigation menus
- `nexopos-widgets.instructions.md` - Widget development

## External Resources

- **Documentation**: https://my.nexopos.com/en/documentation
- **REST API Docs**: https://docs.api.nexopos.com
- **Demo**: https://cloud.nexopos.com
- **Support**: https://github.com/blair2004/NexoPOS/issues
