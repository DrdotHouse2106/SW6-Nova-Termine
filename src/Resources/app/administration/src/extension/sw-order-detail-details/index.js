import template from './sw-order-detail-details.html.twig';

/**
 * Bestellung → Details: Karte „Abholung auf Treffen/Messe“, wenn die Bestellung ein Abhol-Treffen hat.
 */
Shopware.Component.override('sw-order-detail-details', {
    template,

    computed: {
        novaTerminePickup() {
            const fields = this.order?.customFields || {};
            if (!fields.nova_termine_pickup_text && !fields.nova_termine_pickup_title) {
                return null;
            }
            return {
                id: fields.nova_termine_pickup_id || null,
                title: fields.nova_termine_pickup_title || '',
                location: fields.nova_termine_pickup_location || '',
                date: fields.nova_termine_pickup_date || '',
                text: fields.nova_termine_pickup_text || '',
            };
        },
    },
});
