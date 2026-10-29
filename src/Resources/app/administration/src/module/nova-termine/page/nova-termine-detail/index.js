import template from './nova-termine-detail.html.twig';
import { toDay } from '../../util/dates';

const { Component, Mixin } = Shopware;
const { Criteria } = Shopware.Data;

/**
 * Termin anlegen / bearbeiten. Vorschläge aus dem Shop werden hier geprüft und übernommen.
 */
Component.register('nova-termine-detail', {
    template,

    inject: ['repositoryFactory', 'acl'],

    mixins: [Mixin.getByName('notification')],

    props: {
        terminId: {
            type: String,
            required: false,
            default: null,
        },
    },

    data() {
        return {
            termin: null,
            isLoading: true,
            isSaving: false,
            errors: {},
        };
    },

    metaInfo() {
        return { title: this.$createTitle(this.termin?.translated?.title || this.termin?.title || null) };
    },

    computed: {
        repository() {
            return this.repositoryFactory.create('nova_termin');
        },

        isNew() {
            return !this.terminId;
        },

        canEdit() {
            return this.isNew ? this.acl.can('nova_termin.creator') : this.acl.can('nova_termin.editor');
        },

        isSuggestion() {
            return this.termin?.status === 'suggestion';
        },

        participationOptions() {
            return ['booth', 'visitor', 'open'].map((value) => ({
                value,
                label: this.$tc(`nova-termine.participation.${value}`),
            }));
        },

        statusOptions() {
            return ['active', 'inactive', 'suggestion'].map((value) => ({
                value,
                label: this.$tc(`nova-termine.status.${value}`),
            }));
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
            this.errors = {};
            if (this.isNew) {
                this.termin = this.repository.create();
                this.termin.status = 'active';
                this.termin.participation = 'booth';
                this.isLoading = false;
                return;
            }
            const criteria = new Criteria(1, 1);
            criteria.addAssociation('salesChannels');
            try {
                this.termin = await this.repository.get(this.terminId, Shopware.Context.api, criteria);
            } finally {
                this.isLoading = false;
            }
        },

        validate() {
            const t = this.termin;
            const errors = {};
            if (!(t.title || '').trim()) {
                errors.title = this.$tc('nova-termine.detail.errorRequired');
            }
            if (!(t.location || '').trim()) {
                errors.location = this.$tc('nova-termine.detail.errorRequired');
            }
            if (!toDay(t.startDate)) {
                errors.startDate = this.$tc('nova-termine.detail.errorRequired');
            }
            if (t.endDate && toDay(t.startDate) && toDay(t.endDate) < toDay(t.startDate)) {
                errors.endDate = this.$tc('nova-termine.detail.errorEnd');
            }
            if ((t.url || '').trim() !== '' && !/^https?:\/\/[^\s/]+\S*$/i.test(t.url.trim())) {
                errors.url = this.$tc('nova-termine.detail.errorUrl');
            }
            this.errors = errors;
            return Object.keys(errors).length === 0;
        },

        fieldError(field) {
            return this.errors[field] ? { detail: this.errors[field] } : null;
        },

        async save(acceptSuggestion = false) {
            if (!this.validate()) {
                this.createNotificationError({ message: this.$tc('nova-termine.detail.errorForm') });
                return;
            }
            const t = this.termin;
            t.startDate = toDay(t.startDate);
            t.endDate = toDay(t.endDate);
            t.url = (t.url || '').trim() || null;
            if (acceptSuggestion) {
                // Übernehmen: sichtbar schalten, Melder-E-Mail nicht in den öffentlichen Termin übernehmen
                t.status = 'active';
                t.reporterEmail = null;
            }

            this.isSaving = true;
            try {
                await this.repository.save(t);
                this.createNotificationSuccess({ message: this.$tc('nova-termine.detail.saved') });
                if (this.isNew) {
                    this.$router.replace({ name: 'nova.termine.detail', params: { id: t.id } });
                } else {
                    await this.load();
                }
            } catch (e) {
                const detail = e?.response?.data?.errors?.[0]?.detail;
                this.createNotificationError({ message: detail || this.$tc('nova-termine.list.saveError') });
            } finally {
                this.isSaving = false;
            }
        },
    },
});
