//{namespace name="swagaisearch/translations"}

Ext.define('Shopware.apps.Article.swagAiSearch.model.Keyword', {
    extend: 'Shopware.data.Model',

    fields: [
        { name: 'id', type: 'int', useNull: true },
        { name: 'keyword', type: 'string' },
        { name: 'article', type: 'int', persist: false }
    ],

    proxy: {
        type: 'ajax',
        api: {
            create: '{url action="save" controller="SwagAiSearch"}',
            update: '{url action="save" controller="SwagAiSearch"}',
            delete: '{url action="delete" controller="SwagAiSearch"}'
        },

        reader:{
            type:'json',
            root:'data',
            totalProperty:'total'
        }
    }
});