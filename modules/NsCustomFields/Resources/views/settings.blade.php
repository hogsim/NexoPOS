@extends('layout.dashboard')

@section('layout.dashboard.body')
<div class="flex-auto flex flex-col">
    <div class="px-4 flex-auto flex flex-col" id="report-section">
        <div class="page-inner-header mb-4">
            <h3 class="text-3xl font-bold">{{ __('Custom Fields Manager') }}</h3>
            <p class="text-secondary">{{ __('Manage custom fields for Customer profiles.') }}</p>
        </div>
        <div id="ns-custom-fields-settings"></div>
    </div>
</div>
@endsection

@section('layout.dashboard.footer')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const { createApp } = Vue;
    const { createPinia } = Pinia;
    
    // Import the custom fields manager component
    const NsCustomFieldsManager = {
        template: `
            <div class="ns-custom-fields-manager">
                <div class="mb-4">
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="font-semibold text-lg">Custom Fields</h3>
                        <button @click="addField" type="button" class="rounded shadow px-3 py-2 bg-info-primary text-white hover:bg-info-secondary">
                            <i class="las la-plus"></i>
                            Add Field
                        </button>
                    </div>

                    <div v-if="fields.length === 0" class="border-2 border-dashed border-gray-300 rounded p-8 text-center text-gray-500">
                        <i class="las la-inbox text-6xl"></i>
                        <p class="mt-2">No custom fields defined yet. Click "Add Field" to create one.</p>
                    </div>

                    <div v-else class="space-y-4">
                        <div v-for="(field, index) in fields" :key="index" 
                             class="border rounded p-4 bg-white shadow-sm">
                            <div class="flex justify-between items-start mb-4">
                                <h4 class="font-semibold">@{{ field.label || 'New Field' }}</h4>
                                <div class="flex gap-2">
                                    <button @click="moveUp(index)" :disabled="index === 0" type="button"
                                            class="rounded shadow px-2 py-1 text-sm" :class="index === 0 ? 'bg-gray-200 text-gray-400 cursor-not-allowed' : 'bg-gray-100 hover:bg-gray-200'">
                                        <i class="las la-arrow-up"></i>
                                    </button>
                                    <button @click="moveDown(index)" :disabled="index === fields.length - 1" type="button"
                                            class="rounded shadow px-2 py-1 text-sm" :class="index === fields.length - 1 ? 'bg-gray-200 text-gray-400 cursor-not-allowed' : 'bg-gray-100 hover:bg-gray-200'">
                                        <i class="las la-arrow-down"></i>
                                    </button>
                                    <button @click="removeField(index)" type="button" class="rounded shadow px-2 py-1 bg-error-primary text-white hover:bg-error-secondary text-sm">
                                        <i class="las la-trash"></i>
                                    </button>
                                </div>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-medium mb-1">Field Label</label>
                                    <input v-model="field.label" type="text" 
                                           class="border rounded px-3 py-2 w-full" 
                                           placeholder="e.g., Loyalty Number" />
                                </div>

                                <div>
                                    <label class="block text-sm font-medium mb-1">Field Name (Identifier)</label>
                                    <input v-model="field.name" type="text" 
                                           class="border rounded px-3 py-2 w-full" 
                                           placeholder="e.g., loyalty_number"
                                           @input="sanitizeFieldName(field)" />
                                    <p class="text-xs text-gray-500 mt-1">Use lowercase letters, numbers, and underscores only</p>
                                </div>

                                <div>
                                    <label class="block text-sm font-medium mb-1">Field Type</label>
                                    <select v-model="field.type" class="border rounded px-3 py-2 w-full">
                                        <option value="text">Text</option>
                                        <option value="textarea">Textarea</option>
                                        <option value="number">Number</option>
                                        <option value="email">Email</option>
                                        <option value="date">Date</option>
                                    </select>
                                </div>

                                <div class="md:col-span-2">
                                    <label class="block text-sm font-medium mb-1">Description (Optional)</label>
                                    <textarea v-model="field.description" 
                                              class="border rounded px-3 py-2 w-full" 
                                              rows="2"
                                              placeholder="Helper text for this field"></textarea>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="mt-6 flex justify-end">
                    <button @click="saveFields" type="button" class="rounded shadow px-4 py-2 bg-info-primary text-white hover:bg-info-secondary">
                        <i class="las la-save"></i>
                        Save Settings
                    </button>
                </div>
            </div>
        `,
        data() {
            return {
                fields: [],
                isSaving: false
            };
        },
        mounted() {
            this.loadFields();
        },
        methods: {
            async loadFields() {
                try {
                    const response = await fetch('/api/custom-fields/config');
                    const data = await response.json();
                    const parsed = JSON.parse(data.value || '[]');
                    this.fields = Array.isArray(parsed) ? parsed : [];
                } catch (e) {
                    console.error('Failed to load custom fields config:', e);
                    this.fields = [];
                }
            },
            addField() {
                this.fields.push({
                    label: '',
                    name: '',
                    type: 'text',
                    description: ''
                });
            },
            removeField(index) {
                if (confirm('Are you sure you want to remove this field?')) {
                    this.fields.splice(index, 1);
                }
            },
            moveUp(index) {
                if (index > 0) {
                    const field = this.fields.splice(index, 1)[0];
                    this.fields.splice(index - 1, 0, field);
                }
            },
            moveDown(index) {
                if (index < this.fields.length - 1) {
                    const field = this.fields.splice(index, 1)[0];
                    this.fields.splice(index + 1, 0, field);
                }
            },
            sanitizeFieldName(field) {
                field.name = field.name.toLowerCase().replace(/[^a-z0-9_]/g, '_');
            },
            async saveFields() {
                this.isSaving = true;
                try {
                    const response = await fetch('/api/custom-fields/config', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                        },
                        body: JSON.stringify({
                            ns_custom_fields_config: JSON.stringify(this.fields)
                        })
                    });
                    
                    const result = await response.json();
                    
                    if (response.ok) {
                        nsSnackBar.success(result.message || 'Settings saved successfully');
                    } else {
                        throw new Error(result.message || 'Failed to save settings');
                    }
                } catch (e) {
                    console.error('Failed to save custom fields:', e);
                    nsSnackBar.error(e.message || 'Failed to save settings');
                } finally {
                    this.isSaving = false;
                }
            }
        }
    };

    const app = createApp(NsCustomFieldsManager);
    app.use(createPinia());
    app.mount('#ns-custom-fields-settings');
});
</script>
@endsection
