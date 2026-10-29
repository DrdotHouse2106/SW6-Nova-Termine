import template from './nova-termine-list.html.twig';
import './nova-termine-list.scss';
import { today, formatRange, isUpcoming, plusOneYear } from '../../util/dates';

const { Component, Mixin } = Shopware;
const { Criteria } = Shopware.Data;

/** Höchstzahl je Abfrage – ein Terminkalender hat realistisch einige Dutzend bis wenige Hundert Einträge */
const LIMIT = 500;

/**
 * Terminliste: kommende zuerst (aufsteigend), vergangene danach ausgegraut, Vorschläge eigener Reiter.
 */
Component.register('nova-termine-list', {
    template,

    inject: ['repositoryFactory', 'acl'],

    mixins: [Mixin.getByName('notification')],

    data() {
        return {
            isLoading: true,
            filter: 'all',
            search: '',
            upcoming: [],
            past: [],
            suggestions: [],
            suggestionCount: 0,
            deleteItem: null,
            searchTimer: null,
        };
    },

    metaInfo() {
        return { title: this.$createTitle() };
    },

    computed: {
        repository() {
            return this.repositoryFactory.create('nova_termin');
        },

        filters() {
            return [
                { id: 'all', label: this.$tc('nova-termine.list.filterAll') },
                { id: 'upcoming', label: this.$tc('nova-termine.list.filterUpcoming') },
                { id: 'past', label: this.$tc('nova-termine.list.filterPast') },
                { id: 'suggestions', label: this.$tc('nova-termine.list.filterSuggestions') },
            ];
        },

        items() {
            if (this.filter === 'suggestions') {
                return this.suggestions;
            }
            if (this.filter === 'upcoming') {
                return this.upcoming;
            }
            if (this.filter === 'past') {
                return this.past;
            }
            return [...this.upcoming, ...this.past];
        },
    },

    created() {
        const filter = this.$route.query?.filter;
        if (['all', 'upcoming', 'past', 'suggestions'].includes(filter)) {
            this.filter = filter;
        }
        this.load();
    },

    methods: {
        baseCriteria() {
            const criteria = new Criteria(1, LIMIT);
            criteria.addAssociation('salesChannels');
            if (this.search.trim() !== '') {
                criteria.setTerm(this.search.trim());
            }
            return criteria;
        },

        upcomingFilter(day) {
            return Criteria.multi('OR', [
                Criteria.range('endDate', { gte: day }),
                Criteria.multi('AND', [
                    Criteria.equals('endDate', null),
                    Criteria.range('startDate', { gte: day }),
                ]),
            ]);
        },

        async load() {
            this.isLoading = true;
            const day = today();

            const upcoming = this.baseCriteria();
            upcoming.addFilter(Criteria.not('AND', [Criteria.equals('status', 'suggestion')]));
            upcoming.addFilter(this.upcomingFilter(day));
            upcoming.addSorting(Criteria.sort('startDate', 'ASC'));

            const past = this.baseCriteria();
            past.addFilter(Criteria.not('AND', [Criteria.equals('status', 'suggestion')]));
            past.addFilter(Criteria.not('AND', [this.upcomingFilter(day)]));
            past.addSorting(Criteria.sort('startDate', 'DESC'));

            const suggestions = this.baseCriteria();
            suggestions.addFilter(Criteria.equals('status', 'suggestion'));
            suggestions.addSorting(Criteria.sort('createdAt', 'DESC'));

            try {
                const [u, p, s] = await Promise.all([
                    this.repository.search(upcoming),
                    this.repository.search(past),
                    this.repository.search(suggestions),
                ]);
                this.upcoming = Array.from(u);
                this.past = Array.from(p);
                this.suggestions = Array.from(s);
                this.suggestionCount = s.total ?? this.suggestions.length;
            } catch (e) {
                this.createNotificationError({ message: this.$tc('nova-termine.list.loadError') });
            } finally {
                this.isLoading = false;
            }
        },

        onSearch() {
            clearTimeout(this.searchTimer);
            this.searchTimer = setTimeout(() => this.load(), 300);
        },

        setFilter(id) {
            this.filter = id;
            this.$router.replace({ query: { ...this.$route.query, filter: id } });
        },

        dateText(item) {
            return formatRange(item.startDate, item.endDate);
        },

        isPast(item) {
            return item.status !== 'suggestion' && !isUpcoming(item.startDate, item.endDate);
        },

        titleOf(item) {
            return item.translated?.title || item.title || '–';
        },

        participationLabel(item) {
            return this.$tc(`nova-termine.participation.${item.participation}`);
        },

        statusLabel(item) {
            return this.$tc(`nova-termine.status.${item.status}`);
        },

        channelsText(item) {
            if (!item.salesChannels || item.salesChannels.length === 0) {
                return this.$tc('nova-termine.list.allChannels');
            }
            return Array.from(item.salesChannels).map((c) => c.translated?.name || c.name).join(', ');
        },

        openDetail(item) {
            this.$router.push({ name: 'nova.termine.detail', params: { id: item.id } });
        },

        async accept(item) {
            try {
                item.status = 'active';
                item.reporterEmail = null;
                await this.repository.save(item);
                this.createNotificationSuccess({ message: this.$tc('nova-termine.list.accepted', 0, { title: this.titleOf(item) }) });
            } catch (e) {
                this.createNotificationError({ message: this.$tc('nova-termine.list.saveError') });
            }
            this.load();
        },

        async duplicate(item) {
            this.isLoading = true;
            try {
                const clone = await this.repository.clone(item.id, {
                    cloneChildren: false,
                    overwrites: {
                        status: 'inactive',
                        startDate: plusOneYear(item.startDate),
                        endDate: item.endDate ? plusOneYear(item.endDate) : null,
                        reporterEmail: null,
                    },
                });
                this.createNotificationInfo({ message: this.$tc('nova-termine.list.duplicated') });
                this.$router.push({ name: 'nova.termine.detail', params: { id: clone.id } });
            } catch (e) {
                this.createNotificationError({ message: this.$tc('nova-termine.list.saveError') });
                this.isLoading = false;
            }
        },

        async confirmDelete() {
            const item = this.deleteItem;
            this.deleteItem = null;
            if (!item) {
                return;
            }
            try {
                await this.repository.delete(item.id);
            } catch (e) {
                this.createNotificationError({ message: this.$tc('nova-termine.list.deleteError') });
            }
            this.load();
        },
    },
});
