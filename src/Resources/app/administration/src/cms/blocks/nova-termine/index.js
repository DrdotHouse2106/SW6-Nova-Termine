import './component';
import './preview';

/**
 * Block „Terminliste“ (Kategorie Text) mit einem Element „nova-termine“.
 */
Shopware.Service('cmsService').registerCmsBlock({
    name: 'nova-termine',
    label: 'nova-termine.cms.blockLabel',
    category: 'text',
    component: 'sw-cms-block-nova-termine',
    previewComponent: 'sw-cms-preview-nova-termine',
    defaultConfig: {
        marginBottom: '20px',
        marginTop: '20px',
        marginLeft: '20px',
        marginRight: '20px',
        sizingMode: 'boxed',
    },
    slots: {
        content: 'nova-termine',
    },
});
