

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
        me.selModel = me.buildSelectionModel();
        me.plugins = me.buildPlugins();

        me.on('selectionchange', function(selModel, selected) {
            me.down('#deletebutton').setDisabled(selected.length === 0);
        });

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
                header: '{s name=keywords_grid/column_header}Keyword{/s}',
                dataIndex: 'keyword',
                flex: 1,
                editor: {
                    xtype: 'textfield'
                }
            }, {
                xtype: 'actioncolumn',
                width: 30,
                items: [{
                    iconCls: 'sprite-minus-circle-frame',
                    handler: function (view, rowIndex, colIndex, item, opts, record) {
                        me.fireEvent('deleteKeyword', me, [record]);
                    }
                }]
            }]
        };
    },

    buildSelectionModel: function() {
        return {
            selType: 'checkboxmodel',
            allowDeselect: true,
            mode: 'MULTI'
        };
    },

    buildDockedItems: function() {
        var me = this;

        me.topToolbar = Ext.create('Ext.toolbar.Toolbar', {
            dock: 'top',
            ui: 'shopware-ui',
            items: [{
                xtype: 'button',
                text: '{s name=keywords_grid/toolbar_add}Add{/s}',
                iconCls: 'sprite-plus-circle-frame',
                scope: me,
                handler: function() {
                    me.fireEvent('addKeyword', me);
                }
            }, {
                xtype: 'button',
                text: '{s name=keywords_grid/toolbar_delete}Remove selection{/s}',
                iconCls: 'sprite-minus-circle-frame',
                itemId: 'deletebutton',
                disabled: true,
                handler: function() {
                    me.fireEvent('deleteKeyword', me, me.getSelectionModel().getSelection());
                }
            }, '->', {
                xtype:'textfield',
                itemId:'searchfield',
                cls:'searchfield',
                width:170,
                emptyText: '{s name=keywords_grid/toolbar_search}Search...{/s}',
                enableKeyEvents:true,
                checkChangeBuffer:500,
                listeners: {
                    change: function(field, newValue) {
                        me.fireEvent('searchfilterchange', me, field, newValue);
                    }
                }
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
    },

    buildPlugins: function() {
        var me = this;

        me.rowEditor = Ext.create('Ext.grid.plugin.RowEditing', {
            clicksToEdit: 2,
            autoCancel: true,
            listeners: {
                canceledit: function(editor, e) {
                    if (Ext.isEmpty(e.record.get('id'))) {
                        e.store.remove(e.record);
                    }
                },
                edit: function(editor, e) {
                    me.fireEvent('keywordAdded', editor, e);
                }
            }
        });

        return [
            me.rowEditor
        ];
    }
});