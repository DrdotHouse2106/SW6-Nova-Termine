import './acl';
import './page/nova-termine-list';
import './page/nova-termine-detail';

/**
 * Inhalte → Termine: Liste, Anlegen/Bearbeiten, Vorschläge aus dem Shop übernehmen.
 */
Shopware.Module.register('nova-termine', {
    type: 'plugin',
    name: 'nova-termine',
    title: 'nova-termine.general.title',
    description: 'nova-termine.general.description',
    color: '#ff68b4',
    icon: 'regular-calendar',
    entity: 'nova_termin',

    routes: {
        index: {
            component: 'nova-termine-list',
            path: 'index',
            meta: { privilege: 'nova_termin.viewer' },
        },
        detail: {
            component: 'nova-termine-detail',
            path: 'detail/:id',
            props: { default: (route) => ({ terminId: route.params.id }) },
            meta: { parentPath: 'nova.termine.index', privilege: 'nova_termin.viewer' },
        },
        create: {
            component: 'nova-termine-detail',
            path: 'create',
            meta: { parentPath: 'nova.termine.index', privilege: 'nova_termin.creator' },
        },
    },

    navigation: [{
        id: 'nova-termine',
        label: 'nova-termine.general.title',
        color: '#ff68b4',
        path: 'nova.termine.index',
        icon: 'regular-calendar',
        parent: 'sw-content',
        position: 60,
        privilege: 'nova_termin.viewer',
    }],
});
