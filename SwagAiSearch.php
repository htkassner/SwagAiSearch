<?php

namespace SwagAiSearch;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Tools\SchemaTool;
use Enlight_Controller_ActionEventArgs;
use Shopware\Components\Plugin;
use Shopware\Components\Theme\LessDefinition;
use SwagAiSearch\Models\Article\Keyword;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * Shopware-Plugin SwagAiSearch.
 */
class SwagAiSearch extends Plugin
{
    public static function getSubscribedEvents()
    {
        return [
            'Enlight_Controller_Action_PostDispatchSecure_Backend_Article' => 'extendArticleModule',
            'Enlight_Controller_Action_PostDispatchSecure_Frontend' => 'onFrontendPostDispatch',
            'Theme_Compiler_Collect_Plugin_Javascript' => 'onAddJavascriptFiles',
            'Theme_Compiler_Collect_Plugin_Less' => 'onAddLessFiles'
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
     * @param Plugin\Context\ActivateContext $context
     */
    public function activate(Plugin\Context\ActivateContext $context)
    {
        $context->scheduleClearCache(Plugin\Context\InstallContext::CACHE_LIST_DEFAULT);
    }

    /**
     * @param Plugin\Context\InstallContext $context
     */
    public function install(Plugin\Context\InstallContext $context)
    {
        parent::install($context);

        $this->installSchema();
    }

    /**
     * @param Plugin\Context\UninstallContext $context
     */
    public function uninstall(Plugin\Context\UninstallContext $context)
    {
        parent::uninstall($context);

        if ($context->keepUserData()) {
            return;
        }
        $this->uninstallSchema();
    }

    /**
     * @inheritdoc
     */
    private function installSchema()
    {
        $tool = new SchemaTool($this->container->get('models'));
        $tool->updateSchema($this->getModelMetaData(), true);
    }

    /**
     * @inheritdoc
     */
    private function uninstallSchema()
    {
        $tool = new SchemaTool($this->container->get('models'));
        $tool->dropSchema($this->getModelMetaData());
    }

    /**
     * @return array
     */
    private function getModelMetaData()
    {
        return [$this->container->get('models')->getClassMetadata(Keyword::class)];
    }

    /**
    * @param ContainerBuilder $container
    */
    public function build(ContainerBuilder $container)
    {
        $container->setParameter('swag_ai_search.plugin_dir', $this->getPath());
        parent::build($container);
    }

    /**
     * @param Enlight_Controller_ActionEventArgs $args
     */
    public function onFrontendPostDispatch(Enlight_Controller_ActionEventArgs $args)
    {
        $subject = $args->getSubject();
        $view = $subject->View();

        $view->addTemplateDir($this->getPath() . '/Views/');
    }

    /**
     * @return ArrayCollection
     */
    public function onAddLessFiles()
    {
        $lessFiles = [
            $this->getPath() . '/Views/frontend/_public/src/less/all.less'
        ];

        $less = new LessDefinition([], $lessFiles, $this->getPath());

        return new ArrayCollection([$less]);
    }

    /**
     * @return ArrayCollection
     */
    public function onAddJavascriptFiles()
    {
        $jsFiles = [
            $this->getPath() . '/Views/frontend/_public/vendor/js/clarifai.js',
            $this->getPath() . '/Views/frontend/_public/src/js/jquery.image-search.js',
        ];

        return new ArrayCollection($jsFiles);
    }
}
