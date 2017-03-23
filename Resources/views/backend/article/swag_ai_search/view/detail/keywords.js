

Ext.define('Shopware.apps.Article.swagAiSearch.view.detail.Keywords', {
    extend: 'Ext.grid.Panel',
    alias: 'widget.swagaisearch-keywords-grid',

    config: {
        articleId: null
    },

    initComponent: function() {
        var me = this;

        me.store = me.buildStore();
        me.columns = me.buildColumns();
        me.dockedItems = me.buildDockedItems();

        me.callParent(arguments);
    },

    buildStore: function() {
        var me = this,
            store;

        store = Ext.create('Shopware.apps.Article.swagAiSearch.store.Keywords');

        store.getProxy().setExtraParam('articleId', me.getArticleId());

        return store;
    },

    buildColumns: function() {
        var me = this;

        return {
            defaults: {
                menuDisabled: true,
                draggable: false,
                sortable: false
            },
            items: [{
                header: '{s name=keywords_grid/column_header}{/s}',
                dataIndex: 'keyword',
                flex: 1
            }, {
                xtype: 'actioncolumn',
                width: 30,
                items: [{
                    iconCls: 'sprite-minus-circle-frame',
                    handler: function (view, rowIndex, colIndex, item, opts, record) {
                        me.fireEvent('deleteKeyword', view, [record]);
                    }
                }]
            }]
        };
    },

    buildDockedItems: function() {
        var me = this;

        me.topToolbar = Ext.create('Ext.toolbar.Toolbar', {
            dock: 'top',
            ui: 'shopware-ui',
            items: [{
                xtype: 'button',
                text: '{s name=keywords_grid/toolbar_add}{/s}',
                iconCls: 'sprite-plus-circle-frame',
                handler: function() {

                }
            }, '->', {
                xtype:'textfield',
                itemId:'searchfield',
                cls:'searchfield',
                width:170,
                emptyText: '{s name=keywords_grid/toolbar_search}{/s}',
                enableKeyEvents:true,
                checkChangeBuffer:500
            }]
        });

        me.pagingToolbar = Ext.create('Ext.toolbar.Paging', {
            dock: 'bottom',
            displayInfo: true,
            store: me.getStore()
        });

        return [
            me.topToolbar,
            me.pagingToolbar
        ];
    }
});