@extends('layout.dashboard')

@section('layout.dashboard.body')
<div class="flex-auto flex flex-col">
    @include( Hook::filter( 'ns-dashboard-header-file', '../common/dashboard-header' ) )
    <div class="px-4 flex flex-col" id="dashboard-content">
        <div class="flex-auto flex flex-col">
            <div class="page-inner-header mb-4">
                <h3 class="text-3xl font-bold">{{ __('Custom Fields Manager') }}</h3>
                <p class="text-secondary">{{ __('Manage custom fields for Customer profiles.') }}</p>
            </div>
        
        <div class="ns-box">
            <div class="border-b border-box-edge p-4">
                <div class="flex justify-between items-center">
                    <h3 class="font-semibold text-lg">{{ __('Custom Fields') }}</h3>
                    <button onclick="customFieldsManager.addField()" type="button" class="ns-button info">
                        <i class="las la-plus"></i>
                        {{ __('Add Field') }}
                    </button>
                </div>
            </div>

            <div class="p-4">
                <div id="fields-container">
                    <div id="empty-state" class="border-2 border-dashed rounded p-8 text-center text-secondary" style="display: none;">
                        <i class="las la-inbox text-6xl"></i>
                        <p class="mt-2">{{ __('No custom fields defined yet. Click "Add Field" to create one.') }}</p>
                    </div>
                    <div id="fields-list" class="space-y-4"></div>
                </div>

                <div class="mt-6 flex justify-end">
                    <button onclick="customFieldsManager.saveFields()" type="button" class="ns-button info">
                        <i class="las la-save"></i>
                        {{ __('Save Settings') }}
                    </button>
                </div>
            </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('layout.dashboard.footer')
@parent
<script>
const customFieldsManager = {
    fields: [],
    
    init() {
        this.loadFields();
    },
    
    async loadFields() {
        try {
            const response = await fetch('/api/custom-fields/config', {
                headers: {
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content
                }
            });
            
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            
            const text = await response.text();
            
            let data;
            try {
                data = JSON.parse(text);
            } catch (e) {
                console.error('JSON parse error:', e);
                throw new Error('Invalid JSON response from server');
            }
            
            let config = data.value;
            
            if (typeof config === 'string') {
                try {
                    config = JSON.parse(config);
                } catch (e) {
                    config = [];
                }
            }
            
            this.fields = Array.isArray(config) ? config : [];
            this.render();
        } catch (e) {
            console.error('Failed to load custom fields config:', e);
            const container = document.getElementById('fields-container');
            if (container) {
                container.innerHTML = `<div class="p-4 bg-red-100 text-red-700 rounded">Error loading configuration: ${e.message}</div>`;
            }
            this.fields = [];
        }
    },
    
    render() {
        const container = document.getElementById('fields-list');
        const emptyState = document.getElementById('empty-state');
        
        if (!container || !emptyState) {
            console.error('Required DOM elements not found');
            return;
        }
        
        if (this.fields.length === 0) {
            container.innerHTML = '';
            emptyState.style.display = 'block';
            return;
        }
        
        emptyState.style.display = 'none';
        container.innerHTML = this.fields.map((field, index) => this.renderField(field, index)).join('');
    },
    
    renderField(field, index) {
        return `
            <div class="border rounded p-4 bg-white shadow-sm">
                <div class="flex justify-between items-start mb-4">
                    <h4 class="font-semibold">${field.label || 'New Field'}</h4>
                    <div class="flex gap-2">
                        <button onclick="customFieldsManager.moveUp(${index})" ${index === 0 ? 'disabled' : ''} type="button"
                                class="ns-button default text-sm ${index === 0 ? 'opacity-50 cursor-not-allowed' : ''}">
                            <i class="las la-arrow-up"></i>
                        </button>
                        <button onclick="customFieldsManager.moveDown(${index})" ${index === this.fields.length - 1 ? 'disabled' : ''} type="button"
                                class="ns-button default text-sm ${index === this.fields.length - 1 ? 'opacity-50 cursor-not-allowed' : ''}">
                            <i class="las la-arrow-down"></i>
                        </button>
                        <button onclick="customFieldsManager.removeField(${index})" type="button" class="ns-button error text-sm">
                            <i class="las la-trash"></i>
                        </button>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium mb-1">{{ __('Field Label') }}</label>
                        <input value="${field.label || ''}" type="text" 
                               class="border rounded px-3 py-2 w-full" 
                               placeholder="{{ __('e.g., Loyalty Number') }}"
                               onchange="customFieldsManager.updateField(${index}, 'label', this.value)" />
                    </div>

                    <div>
                        <label class="block text-sm font-medium mb-1">{{ __('Field Name (Identifier)') }}</label>
                        <input value="${field.name || ''}" type="text" 
                               class="border rounded px-3 py-2 w-full" 
                               placeholder="{{ __('e.g., loyalty_number') }}"
                               oninput="customFieldsManager.updateFieldName(${index}, this.value)" />
                        <p class="text-xs text-secondary mt-1">{{ __('Use lowercase letters, numbers, and underscores only') }}</p>
                    </div>

                    <div>
                        <label class="block text-sm font-medium mb-1">{{ __('Field Type') }}</label>
                        <select class="border rounded px-3 py-2 w-full" onchange="customFieldsManager.updateField(${index}, 'type', this.value)">
                            <option value="text" ${field.type === 'text' ? 'selected' : ''}>{{ __('Text') }}</option>
                            <option value="textarea" ${field.type === 'textarea' ? 'selected' : ''}>{{ __('Textarea') }}</option>
                            <option value="number" ${field.type === 'number' ? 'selected' : ''}>{{ __('Number') }}</option>
                            <option value="email" ${field.type === 'email' ? 'selected' : ''}>{{ __('Email') }}</option>
                            <option value="date" ${field.type === 'date' ? 'selected' : ''}>{{ __('Date') }}</option>
                        </select>
                    </div>

                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium mb-1">{{ __('Description (Optional)') }}</label>
                        <textarea class="border rounded px-3 py-2 w-full" 
                                  rows="2"
                                  placeholder="{{ __('Helper text for this field') }}"
                                  onchange="customFieldsManager.updateField(${index}, 'description', this.value)">${field.description || ''}</textarea>
                    </div>
                </div>
            </div>
        `;
    },
    
    addField() {
        this.fields.push({
            label: '',
            name: '',
            type: 'text',
            description: ''
        });
        this.render();
    },
    
    removeField(index) {
        if (confirm('{{ __('Are you sure you want to remove this field?') }}')) {
            this.fields.splice(index, 1);
            this.render();
        }
    },
    
    moveUp(index) {
        if (index > 0) {
            const field = this.fields.splice(index, 1)[0];
            this.fields.splice(index - 1, 0, field);
            this.render();
        }
    },
    
    moveDown(index) {
        if (index < this.fields.length - 1) {
            const field = this.fields.splice(index, 1)[0];
            this.fields.splice(index + 1, 0, field);
            this.render();
        }
    },
    
    updateField(index, key, value) {
        this.fields[index][key] = value;
    },
    
    updateFieldName(index, value) {
        // Sanitize field name
        this.fields[index].name = value.toLowerCase().replace(/[^a-z0-9_]/g, '_');
        this.render();
    },
    
    async saveFields() {
        try {
            const response = await fetch('/api/custom-fields/config', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content
                },
                body: JSON.stringify({
                    ns_custom_fields_config: JSON.stringify(this.fields)
                })
            });
            
            const result = await response.json();
            
            if (response.ok) {
                nsSnackBar.success(result.message || '{{ __('Settings saved successfully') }}');
            } else {
                throw new Error(result.message || '{{ __('Failed to save settings') }}');
            }
        } catch (e) {
            console.error('Failed to save custom fields:', e);
            nsSnackBar.error(e.message || '{{ __('Failed to save settings') }}');
        }
    }
};

document.addEventListener('DOMContentLoaded', function() {
    customFieldsManager.init();
});
</script>
@endsection

