import template from './sw-cms-el-config-nova-termine.html.twig';

const { Mixin } = Shopware;

/**
 * Einstellungen des Elements: Bereich, Ersatztext, Höchstzahl, „Treffen melden“.
 */
Shopware.Component.register('sw-cms-el-config-nova-termine', {
    template,

    emits: ['element-update'],

    mixins: [Mixin.getByName('cms-element')],

    computed: {
        areaOptions() {
            return [
                { value: 'all', label: this.$tc('nova-termine.cms.areaAll') },
                { value: 'booth', label: this.$tc('nova-termine.cms.areaBooth') },
                { value: 'scene', label: this.$tc('nova-termine.cms.areaScene') },
            ];
        },
    },

    created() {
        this.initElementConfig('nova-termine');
    },

    methods: {
        onChange() {
            this.$emit('element-update', this.element);
        },
    },
});
