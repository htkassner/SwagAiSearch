<?php

use Shopware\Bundle\SearchBundle\ProductSearchResult;
use Shopware\Bundle\SearchBundle\SearchTermPreProcessorInterface;
use Shopware\Bundle\StoreFrontBundle\Struct\ShopContextInterface;
use SwagAiSearch\Components\Clarifai\Result\PredictionResult;

class Shopware_Controllers_Frontend_AjaxAiSearch extends Enlight_Controller_Action
{
    public function indexAction()
    {
        $imageData = $this->Request()->get('imageData');

        if (!$imageData) {
            return;
        }

        /**@var ShopContextInterface $context */
        $context = $this->get('shopware_storefront.context_service')->getShopContext();

        $locale = $context->getShop()->getLocale() ? $context->getShop()->getLocale() : 'de_DE';

        $apiClient = $this->container->get('swag_ai_search.clarifai.api_client');
        $predictionMinimum = (float) $this->container->get('config')->get('clarifaiPredictionMinimum');

        try {
            /** @var PredictionResult[] $predictionResults */
            $predictionResults = $apiClient->predict($imageData, $locale);
        } catch (\Exception $e) {
            return;
        }

        $searchString = '';

        foreach ($predictionResults as $predictionResult) {
            if ($predictionResult->getPrediction() >= $predictionMinimum) {
                $searchString .= $predictionResult->getPrediction() . ' ';
            }
        }

        Shopware()->Plugins()->Controller()->Json()->setPadding();

        $this->View()->loadTemplate('frontend/search/ajax.tpl');

        $this->Request()->setParam('sAiSearch', $searchString);

        /** @var SearchTermPreProcessorInterface $processor */
        $processor = $this->get('shopware_search.search_term_pre_processor');
        $term = $processor->process($searchString);

        if (!$term || strlen($term) < Shopware()->Config()->get('MinSearchLenght')) {
            return;
        }

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