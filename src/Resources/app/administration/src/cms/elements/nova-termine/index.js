import './component';
import './config';
import './preview';

/**
 * Erlebniswelten-Element „Terminliste“ (Typ „nova-termine“).
 */
Shopware.Service('cmsService').registerCmsElement({
    name: 'nova-termine',
    label: 'nova-termine.cms.elementLabel',
    component: 'sw-cms-el-nova-termine',
    configComponent: 'sw-cms-el-config-nova-termine',
    previewComponent: 'sw-cms-el-preview-nova-termine',
    defaultConfig: {
        area: { source: 'static', value: 'all' },
        emptyText: { source: 'static', value: '' },
        maxCount: { source: 'static', value: 0 },
        showReport: { source: 'static', value: true },
        reportText: { source: 'static', value: '' },
    },
});
