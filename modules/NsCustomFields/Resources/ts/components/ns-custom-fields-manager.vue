<template>
    <div class="ns-custom-fields-manager">
        <div class="mb-4">
            <div class="flex justify-between items-center mb-4">
                <h3 class="font-semibold text-lg">{{ __('Custom Fields') }}</h3>
                <button @click="addField" class="ns-button info">
                    <i class="las la-plus"></i>
                    {{ __('Add Field') }}
                </button>
            </div>

            <div v-if="fields.length === 0" class="border-2 border-dashed border-input-edge rounded p-8 text-center text-secondary">
                <i class="las la-inbox text-6xl"></i>
                <p class="mt-2">{{ __('No custom fields defined yet. Click "Add Field" to create one.') }}</p>
            </div>

            <div v-else class="space-y-4">
                <div v-for="(field, index) in fields" :key="index" 
                     class="border border-input-edge rounded p-4 bg-input-background">
                    <div class="flex justify-between items-start mb-4">
                        <h4 class="font-semibold">{{ field.label || __('New Field') }}</h4>
                        <div class="flex gap-2">
                            <button @click="moveUp(index)" :disabled="index === 0" 
                                    class="ns-button default text-sm" :class="{ 'opacity-50 cursor-not-allowed': index === 0 }">
                                <i class="las la-arrow-up"></i>
                            </button>
                            <button @click="moveDown(index)" :disabled="index === fields.length - 1" 
                                    class="ns-button default text-sm" :class="{ 'opacity-50 cursor-not-allowed': index === fields.length - 1 }">
                                <i class="las la-arrow-down"></i>
                            </button>
                            <button @click="removeField(index)" class="ns-button error text-sm">
                                <i class="las la-trash"></i>
                            </button>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium mb-1">{{ __('Field Label') }}</label>
                            <input v-model="field.label" type="text" 
                                   class="ns-input w-full" 
                                   :placeholder="__('e.g., Loyalty Number')" />
                        </div>

                        <div>
                            <label class="block text-sm font-medium mb-1">{{ __('Field Name (Identifier)') }}</label>
                            <input v-model="field.name" type="text" 
                                   class="ns-input w-full" 
                                   :placeholder="__('e.g., loyalty_number')"
                                   @input="sanitizeFieldName(field)" />
                            <p class="text-xs text-secondary mt-1">{{ __('Use lowercase letters, numbers, and underscores only') }}</p>
                        </div>

                        <div>
                            <label class="block text-sm font-medium mb-1">{{ __('Field Type') }}</label>
                            <select v-model="field.type" class="ns-input w-full">
                                <option value="text">{{ __('Text') }}</option>
                                <option value="textarea">{{ __('Textarea') }}</option>
                                <option value="number">{{ __('Number') }}</option>
                                <option value="email">{{ __('Email') }}</option>
                                <option value="date">{{ __('Date') }}</option>
                            </select>
                        </div>

                        <div class="md:col-span-2">
                            <label class="block text-sm font-medium mb-1">{{ __('Description (Optional)') }}</label>
                            <textarea v-model="field.description" 
                                      class="ns-input w-full" 
                                      rows="2"
                                      :placeholder="__('Helper text for this field')"></textarea>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Hidden input to store JSON for form submission -->
        <input type="hidden" :name="fieldName" :value="jsonValue" />
    </div>
</template>

<script>
import { __ } from '~/libraries/lang';

export default {
    name: 'ns-custom-fields-manager',
    props: {
        fieldName: {
            type: String,
            default: 'ns_custom_fields_config'
        },
        value: {
            type: String,
            default: '[]'
        }
    },
    data() {
        return {
            fields: []
        };
    },
    computed: {
        jsonValue() {
            return JSON.stringify(this.fields);
        }
    },
    mounted() {
        this.loadFields();
    },
    methods: {
        __,
        loadFields() {
            try {
                const parsed = JSON.parse(this.value || '[]');
                this.fields = Array.isArray(parsed) ? parsed : [];
            } catch (e) {
                console.error('Failed to parse custom fields config:', e);
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
            if (confirm(this.__('Are you sure you want to remove this field?'))) {
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
            // Convert to lowercase and replace invalid characters with underscores
            field.name = field.name.toLowerCase().replace(/[^a-z0-9_]/g, '_');
        }
    }
};
</script>

<style scoped>
.ns-custom-fields-manager {
    /* Component-specific styles if needed */
}
</style>
