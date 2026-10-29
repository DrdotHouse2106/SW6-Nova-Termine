import template from './sw-cms-el-nova-termine.html.twig';
import './sw-cms-el-nova-termine.scss';

const { Mixin } = Shopware;

/**
 * Vorschau im Erlebniswelten-Editor (Beispieldaten – die echten Termine erscheinen im Shop).
 */
Shopware.Component.register('sw-cms-el-nova-termine', {
    template,

    mixins: [Mixin.getByName('cms-element')],

    computed: {
        area() {
            return this.element?.config?.area?.value || 'all';
        },

        showReport() {
            return this.element?.config?.showReport?.value !== false;
        },

        reportText() {
            return this.element?.config?.reportText?.value || this.$tc('nova-termine.cms.defaultReportText');
        },
    },

    created() {
        this.initElementConfig('nova-termine');
    },
});
