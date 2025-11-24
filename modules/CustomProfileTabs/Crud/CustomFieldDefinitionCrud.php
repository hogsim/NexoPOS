<?php

namespace Modules\CustomProfileTabs\Crud;

use App\Classes\CrudForm;
use App\Classes\CrudTable;
use App\Classes\FormInput;
use App\Services\CrudEntry;
use App\Services\CrudService;
use App\Services\Helper;
use Illuminate\Http\Request;
use Modules\CustomProfileTabs\Models\CustomFieldDefinition;

class CustomFieldDefinitionCrud extends CrudService
{
    /**
     * Define the autoload status
     */
    const AUTOLOAD = true;

    /**
     * Define the identifier
     */
    const IDENTIFIER = 'customprofiletabs.fields';

    /**
     * Define the base table
     */
    protected $table = 'nexopos_custom_field_definitions';

    /**
     * Base route name
     */
    protected $mainRoute = 'customprofiletabs.fields.index';

    /**
     * Define namespace
     */
    protected $namespace = 'customprofiletabs.fields';

    /**
     * Model Used
     */
    protected $model = CustomFieldDefinition::class;

    /**
     * Determine if the options column should display before the crud columns
     */
    protected $prependOptions = true;

    /**
     * Fields which will be filled during post/put
     */
    public $fillable = [];

    /**
     * Define permissions
     */
    protected $permissions = [
        'create' => true,
        'read' => true,
        'update' => true,
        'delete' => true,
    ];

    /**
     * Return the label used for the crud instance
     */
    public function getLabels()
    {
        return CrudTable::labels(
            list_title: __('Custom Profile Fields'),
            list_description: __('Manage custom fields for user and customer profiles.'),
            no_entry: __('No custom fields have been defined yet.'),
            create_new: __('Add New Field'),
            create_title: __('Create Custom Field'),
            create_description: __('Define a new custom field for profiles.'),
            edit_title: __('Edit Custom Field'),
            edit_description: __('Modify the custom field definition.'),
            back_to_list: __('Return to Custom Fields'),
        );
    }

    /**
     * Check whether a feature is enabled
     */
    public function isEnabled($feature): bool
    {
        return false;
    }

    /**
     * Define the form
     */
    public function getForm(?CustomFieldDefinition $entry = null)
    {
        return CrudForm::form(
            main: FormInput::text(
                label: __('Field Name'),
                name: 'name',
                validation: 'required',
                value: $entry->name ?? '',
                description: __('Internal field name (use lowercase, underscores, no spaces). Example: company_name'),
            ),
            tabs: CrudForm::tabs(
                CrudForm::tab(
                    identifier: 'general',
                    label: __('General'),
                    fields: CrudForm::fields(
                        FormInput::text(
                            label: __('Label'),
                            name: 'label',
                            validation: 'required',
                            value: $entry->label ?? '',
                            description: __('Display label for the field. Example: Company Name'),
                        ),
                        FormInput::select(
                            label: __('Field Type'),
                            name: 'type',
                            validation: 'required',
                            value: $entry->type ?? 'text',
                            options: Helper::kvToJsOptions([
                                'text' => __('Text Input'),
                                'textarea' => __('Text Area'),
                                'number' => __('Number'),
                                'email' => __('Email'),
                                'select' => __('Dropdown/Select'),
                                'datetime' => __('Date & Time'),
                                'switch' => __('Toggle Switch (Yes/No)'),
                                'media' => __('Media Upload'),
                            ]),
                            description: __('Select the type of input field.'),
                        ),
                        FormInput::select(
                            label: __('Applies To'),
                            name: 'applies_to',
                            validation: 'required',
                            value: $entry->applies_to ?? 'user',
                            options: Helper::kvToJsOptions([
                                'user' => __('User Profile'),
                                'customer' => __('Customer Profile'),
                            ]),
                            description: __('Choose whether this field appears on user or customer profiles.'),
                        ),
                        FormInput::textarea(
                            label: __('Description'),
                            name: 'description',
                            value: $entry->description ?? '',
                            description: __('Help text displayed below the field (optional).'),
                        ),
                        FormInput::textarea(
                            label: __('Options (JSON)'),
                            name: 'options',
                            value: $entry->options ? json_encode($entry->options, JSON_PRETTY_PRINT) : '',
                            description: __('For select fields, provide options as JSON. Example: {"retail": "Retail", "wholesale": "Wholesale"}'),
                        ),
                        FormInput::text(
                            label: __('Validation Rules'),
                            name: 'validation',
                            value: $entry->validation ?? '',
                            description: __('Laravel validation rules (optional). Example: required|email or min:3|max:100'),
                        ),
                        FormInput::number(
                            label: __('Display Order'),
                            name: 'order',
                            value: $entry->order ?? 0,
                            description: __('Lower numbers appear first. Use 0, 10, 20, etc.'),
                        ),
                        FormInput::switch(
                            label: __('Active'),
                            name: 'active',
                            value: $entry->active ?? true,
                            description: __('Enable or disable this field.'),
                        ),
                    )
                ),
            )
        );
    }

    /**
     * Filter POST input fields
     */
    public function filterPostInputs($inputs)
    {
        // Parse JSON options if provided
        if (isset($inputs['options']) && !empty($inputs['options'])) {
            $inputs['options'] = json_decode($inputs['options'], true);
        } else {
            $inputs['options'] = null;
        }

        // Ensure active is boolean
        $inputs['active'] = isset($inputs['active']) && $inputs['active'] ? true : false;

        return $inputs;
    }

    /**
     * Filter PUT input fields
     */
    public function filterPutInputs($inputs, CustomFieldDefinition $entry)
    {
        return $this->filterPostInputs($inputs);
    }

    /**
     * Define columns
     */
    public function getColumns(): array
    {
        return CrudTable::columns(
            CrudTable::column(
                label: __('Label'),
                identifier: 'label'
            ),
            CrudTable::column(
                label: __('Field Name'),
                identifier: 'name'
            ),
            CrudTable::column(
                label: __('Type'),
                identifier: 'type'
            ),
            CrudTable::column(
                label: __('Applies To'),
                identifier: 'applies_to'
            ),
            CrudTable::column(
                label: __('Order'),
                identifier: 'order'
            ),
            CrudTable::column(
                label: __('Active'),
                identifier: 'active'
            ),
        );
    }

    /**
     * Define actions
     */
    public function setActions(CrudEntry $entry): CrudEntry
    {
        $entry->action(
            identifier: 'edit',
            label: __('Edit'),
            type: 'GOTO',
            url: ns()->url('dashboard/customprofiletabs/fields/edit/' . $entry->id),
        );

        $entry->action(
            identifier: 'delete',
            label: __('Delete'),
            type: 'DELETE',
            url: ns()->url('/api/crud/customprofiletabs.fields/' . $entry->id),
            confirm: [
                'message' => __('Would you like to delete this field?'),
                'title' => __('Delete Custom Field'),
            ],
        );

        return $entry;
    }

    /**
     * Bulk Delete Action
     */
    public function bulkAction(Request $request)
    {
        if ($request->input('action') == 'delete_selected') {
            $status = [
                'success' => 0,
                'error' => 0,
            ];

            foreach ($request->input('entries') as $id) {
                $entity = $this->model::find($id);
                if ($entity instanceof CustomFieldDefinition) {
                    $entity->delete();
                    $status['success']++;
                } else {
                    $status['error']++;
                }
            }

            return $status;
        }

        return false;
    }

    /**
     * Get Links
     */
    public function getLinks(): array
    {
        return CrudTable::links(
            list: ns()->url('dashboard/customprofiletabs/fields'),
            create: ns()->url('dashboard/customprofiletabs/fields/create'),
            edit: ns()->url('dashboard/customprofiletabs/fields/edit/{id}'),
            post: ns()->url('/api/crud/customprofiletabs.fields'),
            put: ns()->url('/api/crud/customprofiletabs.fields/{id}'),
        );
    }

    /**
     * Get Bulk actions
     */
    public function getBulkActions(): array
    {
        return [
            [
                'label' => __('Delete Selected Fields'),
                'identifier' => 'delete_selected',
                'url' => ns()->route('ns.api.crud-bulk-actions', [
                    'namespace' => $this->namespace,
                ]),
            ],
        ];
    }

    /**
     * Get exports
     */
    public function getExports()
    {
        return [];
    }
}
