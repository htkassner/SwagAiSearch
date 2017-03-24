<?php

namespace SwagAiSearch\Bundle\SearchBundle;

use Enlight_Controller_Request_RequestHttp as Request;
use Shopware\Bundle\SearchBundle\Criteria;
use Shopware\Bundle\SearchBundle\CriteriaRequestHandlerInterface;
use Shopware\Bundle\SearchBundle\SearchTermPreProcessor;
use Shopware\Bundle\StoreFrontBundle\Struct\ShopContextInterface;
use SwagAiSearch\Bundle\SearchBundle\Condition\KeywordAiCondition;

class CriteriaRequestHandler implements CriteriaRequestHandlerInterface
{
    /**
     * @var SearchTermPreProcessor
     */
    private $searchTermPreProcessor;

    public function __construct(SearchTermPreProcessor $searchTermPreProcessor)
    {
        $this->searchTermPreProcessor = $searchTermPreProcessor;
    }

    /**
     * @inheritdoc
     */
    public function handleRequest(
        Request $request,
        Criteria $criteria,
        ShopContextInterface $context
    ) {
        $term = $request->getParam('sAiSearch', null);
        if ($term == null) {
            return;
        }
        $term = $this->searchTermPreProcessor->process($term);
        $criteria->addBaseCondition(new KeywordAiCondition($term));
    }
}