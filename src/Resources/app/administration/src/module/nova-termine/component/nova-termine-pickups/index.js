import template from './nova-termine-pickups.html.twig';

const { Component } = Shopware;
const { Criteria } = Shopware.Data;

/**
 * Termin → Abholungen: alle Bestellungen, die auf diesem Treffen abgeholt werden (Packliste).
 */
Component.register('nova-termine-pickups', {
    template,

    inject: ['repositoryFactory', 'acl'],

    props: {
        terminId: {
            type: String,
            required: true,
        },
    },

    data() {
        return {
            orders: [],
            isLoading: false,
            failed: false,
        };
    },

    computed: {
        orderRepository() {
            return this.repositoryFactory.create('order');
        },
    },

    watch: {
        terminId() {
            this.load();
        },
    },

    created() {
        this.load();
    },

    methods: {
        async load() {
            this.isLoading = true;
            this.failed = false;
            const criteria = new Criteria(1, 500);
            criteria.addFilter(Criteria.equals('customFields.nova_termine_pickup_id', this.terminId));
            criteria.addAssociation('orderCustomer');
            criteria.addAssociation('stateMachineState');
            criteria.addAssociation('currency');
            criteria.addSorting(Criteria.sort('orderDateTime', 'ASC'));
            try {
                this.orders = Array.from(await this.orderRepository.search(criteria));
            } catch (e) {
                this.failed = true;
                this.orders = [];
            } finally {
                this.isLoading = false;
            }
        },

        customerName(order) {
            const c = order.orderCustomer;
            if (!c) {
                return '–';
            }
            const name = [c.firstName, c.lastName].filter(Boolean).join(' ');
            return c.company ? `${c.company} (${name})` : name;
        },

        orderDate(order) {
            return Shopware.Utils.format.date(order.orderDateTime, { hour: undefined, minute: undefined });
        },

        amount(order) {
            return Shopware.Utils.format.currency(order.amountTotal, order.currency?.isoCode || 'EUR');
        },
    },
});
