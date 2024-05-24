import template from './fast-order-line-item-list.html.twig';

const { Mixin, Data: { Criteria } } = Shopware;

export default {
    template,

    inject: ['repositoryFactory', 'acl'],

    mixins: [
        Mixin.getByName('listing'),
    ],

    data() {
        return {
            isLoading: false,
            items: null,
            total: 0,
            repository: null,
            sortBy: 'createdAt',
            sortDirection: 'DESC',
            searchConfigEntity: 'fast_order_line_item',
        };
    },

    metaInfo() {
        return {
            title: this.$createTitle(),
        };
    },

    computed: {
        columns() {
            return this.getColumns();
        },
    },

    created() {
        this.createdComponent();
    },

    methods: {
        createdComponent() {
            this.getList();
        },

        async getList() {
            this.isLoading = true;
            let criteria = new Criteria(this.page, this.limit);
            criteria.addSorting(Criteria.sort(this.sortBy, this.sortDirection));

            this.repository = this.repositoryFactory.create('fast_order_line_item');
            this.repository.search(criteria).then((searchResult) => {
                this.items = searchResult;
                this.total = searchResult.total;

                this.isLoading = false;
            }).catch(() => {
                this.isLoading = false;
            });
        },

        getColumns() {
            return [{
                property: 'productNumber',
                label: 'fast-order-item-list.list.productNumber'
            },
            {
                property: 'quantity',
                label: 'fast-order-item-list.list.quantity',
            },
            {
                property: 'sessionId',
                label: 'fast-order-item-list.list.sessionId',
            },
            {
                property: 'comment',
                label: 'fast-order-item-list.list.comment',
                allowResize: true,
                inlineEdit: 'string',
            },
            {
                property: 'createdAt',
                label: 'fast-order-item-list.list.createdAt',
            }];
        },
    },
};
