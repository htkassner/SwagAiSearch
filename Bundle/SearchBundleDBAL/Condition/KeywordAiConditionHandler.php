<?php

namespace SwagAiSearch\Bundle\SearchBundleDBAL\Condition;

use Doctrine\DBAL\Connection;
use Shopware\Bundle\SearchBundle\ConditionInterface;
use Shopware\Bundle\SearchBundleDBAL\ConditionHandlerInterface;
use Shopware\Bundle\SearchBundleDBAL\QueryBuilder;
use Shopware\Bundle\StoreFrontBundle\Struct\ShopContextInterface;
use SwagAiSearch\Bundle\SearchBundle\Condition\KeywordAiCondition;

class KeywordAiConditionHandler implements ConditionHandlerInterface
{
    /**
     * @inheritdoc
     */
    public function supportsCondition(ConditionInterface $condition)
    {
        return ($condition instanceof KeywordAiCondition);
    }

    /**
     * @inheritdoc
     */
    public function generateCondition(
        ConditionInterface $condition,
        QueryBuilder $query,
        ShopContextInterface $context
    ) {
        /** @var KeywordAiCondition $condition */
        $query->leftJoin(
            'product',
            's_article_keywords',
            'keyword',
            'keyword.articleID = product.id AND keyword.keyword IN (:keyword)'
        )->setParameter('keyword', explode(' ', $condition->getKeyword()), Connection::PARAM_STR_ARRAY);
    }
}
