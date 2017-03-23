

//{block name="backend/article/view/detail/window"}
//{$smarty.block.parent}
//{namespace name="swagaisearch/translations"}
Ext.define('Shopware.apps.Article.swagAiSearch.view.detail.Window', {
    override: 'Shopware.apps.Article.view.detail.Window',

    initComponent: function() {
        var me = this;

        me.callParent(arguments);

        me.on('storesLoaded', function() {
            me.mainTab.insert(2, me.createKeywordTab());
        }, me);
    },

    createKeywordTab: function() {
        var me = this,
            articleId = me.article ? me.article.get('id') : null;

        me.keywordGrid = Ext.create('Shopware.apps.Article.swagAiSearch.view.detail.Keywords', {
            articleId: articleId
        });

        me.keywordTab = Ext.create('Ext.panel.Panel', {
            title: '{s name=keywords_tab/title}{/s}',
            layout: 'fit',
            disabled: Ext.isEmpty(articleId),
            items: [me.keywordGrid],
            listeners: {
                activate: {
                    single: true,
                    fn: function() {
                        me.keywordGrid.getStore().load();
                    }
                }
            }
        });

        return me.keywordTab;
    }
});
//{/block}