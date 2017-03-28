//{namespace name="swagaisearch/translations"}

Ext.define('Shopware.apps.Article.SwagAiSearch.controller.Keywords', {
    extend: 'Enlight.app.Controller',

    init: function() {
        var me = this;

        me.control({
            'swagaisearch-keywords-grid': {
                'addKeyword': me.onAddKeyword,
                'saveKeyword': me.onSaveKeyword,
                'deleteKeyword': me.onDeleteKeyword,
                'learnKeyword': me.onLearnKeyword,
                'searchfilterchange': me.onSearchFilterChange
            }
        });

        me.callParent(arguments);
    },

    onAddKeyword: function(grid) {
        var store = grid.getStore();

        if (!grid.rowEditor.editing) {
            store.insert(0, Ext.create('Shopware.apps.Article.swagAiSearch.model.Keyword', {
                article: grid.getArticleId()
            }));
            grid.rowEditor.startEdit(0, 0);
        }
    },

    onSaveKeyword: function(editor, e) {
        var me = this,
            data = e.record.getData(),
            url = '{url action="create" controller="SwagAiSearch"}';

        data['article'] = e.grid.getArticleId();

        me.doRequest(url, data, function() {
            e.store.load();
        }, me);
    },

    onDeleteKeyword: function(grid, records) {
        var me = this,
            store = grid.getStore(),
            url = '{url action="delete" controller="SwagAiSearch"}',
            deletionCount = 0;

        for (var i = 0, count = records.length; i < count; i++) {
            var record = records[i];

            me.doRequest(url, { id: record.get('id') }, function() {
                deletionCount++;
                if (deletionCount === records.length) {
                    store.load();
                }
            }, me);
        }
    },

    onLearnKeyword: function(grid, articleId) {
        var me = this;

        Ext.Ajax.request({
            url: '{url controller="SwagAiSearch" action="learn"}',
            jsonData: {
                articleId: articleId
            },
            callback: function(options, success, response) {
                var result = Ext.JSON.decode(response.responseText);

                if (result.success) {
                    grid.getStore().load();

                    return;
                }
                Shopware.Notification.createGrowlMessage(
                    '{s name=keywords/no_credentials_title}Clarifai api error{/s}',
                    result['message']
                );
            }
        });
    },

    doRequest: function(url, data, callback, scope) {
        Ext.Ajax.request({
            url: url,
            jsonData: data,
            success: function() {
                Ext.callback(callback, scope);
            },
            scope: scope
        });
    },

    onSearchFilterChange: function(grid, field, newValue) {
        var store = grid.getStore(),
            searchString = Ext.String.trim(newValue);

        store.currentPage = 1;

        if ( searchString.length === 0 ) {
            store.filters.removeAtKey('search');
            store.load();
        } else {
            //Loads the store with a special filter
            store.filter([
                { id: 'search', property: 'keyword', value: '%' + searchString + '%', expression: 'LIKE' }
            ]);
        }
    }
});