/**
 * Rollen unter Einstellungen → Benutzer & Rechte → Inhalte → Termine.
 */
Shopware.Service('privileges').addPrivilegeMappingEntry({
    category: 'permissions',
    parent: 'content',
    key: 'nova_termin',
    roles: {
        viewer: {
            privileges: [
                'nova_termin:read',
                'nova_termin_translation:read',
                'nova_termin_sales_channel:read',
                'sales_channel:read',
                'media:read',
                'media_folder:read',
                'media_default_folder:read',
                'language:read',
            ],
            dependencies: [],
        },
        editor: {
            privileges: [
                'nova_termin:update',
                'nova_termin_translation:create',
                'nova_termin_translation:update',
                'nova_termin_sales_channel:create',
                'nova_termin_sales_channel:delete',
                Shopware.Service('privileges').getPrivileges('media.creator'),
            ],
            dependencies: ['nova_termin.viewer'],
        },
        creator: {
            privileges: [
                'nova_termin:create',
            ],
            dependencies: ['nova_termin.viewer', 'nova_termin.editor'],
        },
        deleter: {
            privileges: [
                'nova_termin:delete',
                'nova_termin_translation:delete',
                'nova_termin_sales_channel:delete',
            ],
            dependencies: ['nova_termin.viewer'],
        },
    },
});
