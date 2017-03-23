<?php

namespace SwagAiSearch;

use Doctrine\ORM\Tools\SchemaTool;
use Shopware\Components\Model\ModelManager;
use Shopware\Components\Plugin;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * Shopware-Plugin SwagAiSearch.
 */
class SwagAiSearch extends Plugin
{
    public static function getSubscribedEvents()
    {
        return [
            'Enlight_Controller_Action_PostDispatchSecure_Backend_Article' => 'extendArticleModule'
        ];
    }

    /**
     * @param \Enlight_Controller_ActionEventArgs $args
     */
    public function extendArticleModule(\Enlight_Controller_ActionEventArgs $args)
    {
        /** @var \Enlight_Controller_Request_Request $request */
        $request = $args->getSubject()->Request();

        /** @var \Enlight_View_Default $view */
        $view = $args->getSubject()->View();

        // register templates
        $view->addTemplateDir($this->getPath() . '/Resources/views');

        if ($request->getActionName() === 'load') {
            $view->extendsTemplate('backend/article/swag_ai_search/view/detail/window.js');
        }

        if ($request->getActionName() === 'index') {
            $view->extendsTemplate('backend/article/swag_ai_search/app.js');
        }
    }

    /**
     * @inheritdoc
     */
    public function install(Plugin\Context\InstallContext $context)
    {
        $this->updateSchema();
    }

    private function updateSchema()
    {
        /** @var ModelManager $modelManager */
        $modelManager = $this->container->get('models');
        $tool = new SchemaTool($modelManager);
        $classes = $this->getModelMetaData();

        try {
            $tool->dropSchema($classes);
        } catch (\Exception $e) {
        }

        $tool->createSchema($classes);
    }

    /**
     * @return array
     */
    private function getModelMetaData()
    {
        return [$this->container->get('models')->getClassMetadata(Models\Article\Keyword::class)];
    }

    /**
    * @param ContainerBuilder $container
    */
    public function build(ContainerBuilder $container)
    {
        $container->setParameter('swag_ai_search.plugin_dir', $this->getPath());
        parent::build($container);
    }
}
