<?php

namespace SwagAiSearch\Bundle\SearchBundle\Condition;

use Assert\Assertion;
use Shopware\Bundle\SearchBundle\ConditionInterface;

class KeywordAiCondition implements ConditionInterface
{
    /**
     * @var string
     */
    private $keyword;

    /**
     * @param string $keyword
     */
    public function __construct($keyword)
    {
        Assertion::string($keyword);
        $this->keyword = $keyword;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'keyword';
    }

    /**
     * @return string
     */
    public function getKeyword()
    {
        return $this->keyword;
    }
}