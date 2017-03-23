<?php

use SwagAiSearch\Models\Article\Keyword;

class Shopware_Controllers_Backend_SwagAiSearch extends \Shopware_Controllers_Backend_Application
{
    protected $model = Keyword::class;
    protected $alias = 'keyword';
}