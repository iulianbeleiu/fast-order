Shopware.Component.register('fast-order-line-item-list', () => import('./page/fast-order-line-item-list/index'));

const { Module } = Shopware;

Module.register('fast-order-line-items', {
    type: 'plugin',
    name: 'fast-order-line-items',
    title: 'fast-order-item-list.general.mainMenuItemGeneral',
    description: 'fast-order-item-list.general.descriptionTextModule',

    routes: {
        index: {
            component: 'fast-order-line-item-list',
            path: 'index'
        }
    },

    navigation: [{
        label: 'fast-order-item-list.general.mainMenuItemGeneral',
        color: '#ff3d58',
        path: 'fast.order.line.items.index',
        parent: 'sw-order',
        position: 100
    }]
});
