

Ext.define('Shopware.apps.Article.swagAiSearch.model.Keyword', {
    extend: 'Shopware.data.Model',

    fields: [
        { name: 'id', type: 'int', useNull: true },
        { name: 'keyword', type: 'string' },
        { name: 'article', persist: false }
    ],

    proxy: {
        type: 'ajax',
        api: {
            create: '{url action="save"}',
            update: '{url action="save"}',
            delete: '{url action="delete"}'
        },

        reader:{
            type:'json',
            root:'data',
            totalProperty:'total'
        }
    }
});