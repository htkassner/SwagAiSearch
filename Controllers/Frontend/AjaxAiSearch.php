<?php

use Shopware\Bundle\SearchBundle\ProductSearchResult;
use Shopware\Bundle\SearchBundle\SearchTermPreProcessorInterface;
use Shopware\Bundle\StoreFrontBundle\Struct\ShopContextInterface;

class Shopware_Controllers_Frontend_AjaxAiSearch extends Enlight_Controller_Action
{
    public function indexAction()
    {
        Shopware()->Plugins()->Controller()->Json()->setPadding();

        $this->View()->loadTemplate('frontend/search/ajax.tpl');

        $term = $this->Request()->getParam('sAiSearch');
        /** @var SearchTermPreProcessorInterface $processor */
        $processor = $this->get('shopware_search.search_term_pre_processor');
        $term = $processor->process($term);

        if (!$term || strlen($term) < Shopware()->Config()->get('MinSearchLenght')) {
            return;
        }

        /**@var ShopContextInterface $context */
        $context = $this->get('shopware_storefront.context_service')->getShopContext();

        $criteria = $this->get('shopware_search.store_front_criteria_factory')
            ->createAjaxSearchCriteria($this->Request(), $context);

        /**@var ProductSearchResult $result */
        $result = $this->get('shopware_search.product_search')->search($criteria, $context);

        if ($result->getTotalCount() > 0) {
            $articles = $this->convertProducts($result);
            $this->View()->assign('searchResult', $result);
            $this->View()->assign('sSearchRequest', ['sSearch' => $term]);
            $this->View()->assign('sSearchResults', [
                'sResults' => $articles,
                'sArticlesCount' => $result->getTotalCount()
            ]);
        }
    }

    /**
     * @param ProductSearchResult $result
     * @return array
     */
    private function convertProducts(ProductSearchResult $result)
    {
        $articles = [];
        foreach ($result->getProducts() as $product) {
            $article = $this->get('legacy_struct_converter')->convertListProductStruct($product);

            $article['link'] = $this->Front()->Router()->assemble([
                'controller' => 'detail',
                'sArticle' => $product->getId(),
                'number' => $product->getNumber(),
                'title' => $product->getName()
            ]);
            $article['name'] = $product->getName();
            $articles[] = $article;
        }

        return $articles;
    }
}