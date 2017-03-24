//{namespace name="swagaisearch/translations"}

Ext.define('Shopware.apps.Article.SwagAiSearch.controller.Keywords', {
    extend: 'Enlight.app.Controller',

    init: function() {
        var me = this;

        me.control({
            'swagaisearch-keywords-grid': {
                'addKeyword': me.onAddKeyword,
                'keywordAdded': me.onKeywordAdded,
                'deleteKeyword': me.onDeleteKeyword,
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

    onKeywordAdded: function(editor, e) {
        var me = this;

        Ext.Ajax.request({
            url: '{url action="create" controller="SwagAiSearch"}',
            jsonData: e.record.getData(),
            success: function() {
                e.store.load();
            },
            scope: me
        });
    },

    onDeleteKeyword: function(grid, records) {
        var me = this,
            store = grid.getStore(),
            deletionCount = 0;

        for (var i = 0, count = records.length; i < count; i++) {
            var record = records[i];

            Ext.Ajax.request({
                url: '{url action="delete" controller="SwagAiSearch"}',
                jsonData: {
                    id: record.get('id')
                },
                success: function() {
                    deletionCount++;
                    if (deletionCount === records.length) {
                        store.load();
                    }
                },
                scope: me
            });
        }
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