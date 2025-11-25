import { defineAsyncComponent } from 'vue';

const NsCustomFieldsManager = defineAsyncComponent(() => import('./components/ns-custom-fields-manager.vue'));

// Register the component globally
if (typeof nsExtraComponents !== 'undefined') {
    nsExtraComponents['ns-custom-fields-manager'] = NsCustomFieldsManager;
}

export default NsCustomFieldsManager;
