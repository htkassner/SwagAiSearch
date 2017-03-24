<?php

namespace SwagAiSearch\Controllers\Frontend;

use Shopware\Bundle\SearchBundle\ProductSearchResult;
use Shopware\Bundle\SearchBundle\SearchTermPreProcessorInterface;
use Shopware\Bundle\StoreFrontBundle\Struct\ShopContextInterface;

class AjaxAiSearch extends \Shopware_Controllers_Frontend_AjaxSearch
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
}