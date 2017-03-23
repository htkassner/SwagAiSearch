<?php

use Shopware\Models\Article\Article;
use SwagAiSearch\Models\Article\Keyword;

class Shopware_Controllers_Backend_SwagAiSearch extends \Shopware_Controllers_Backend_Application
{
    protected $model = Keyword::class;
    protected $alias = 'keyword';

    public function save($data)
    {
        /**@var $model Keyword */
        if (!empty($data['id'])) {
            $model = $this->getRepository()->find($data['id']);
        } else {
            $model = new $this->model();
            $this->getManager()->persist($model);
        }

        $article = $this->manager->find(Article::class, $data['article']);

        if (!$article) {
            return [
                'success' => false
            ];
        }

        $model->setArticle($article);
        unset($data['article']);
        $model->fromArray($data);


        $violations = $this->getManager()->validate($model);
        $errors = [];
        /** @var $violation Symfony\Component\Validator\ConstraintViolation */
        foreach ($violations as $violation) {
            $errors[] = [
                'message' => $violation->getMessage(),
                'property' => $violation->getPropertyPath()
            ];
        }

        if (!empty($errors)) {
            return ['success' => false, 'violations' => $errors];
        }

        $this->getManager()->flush();

        $detail = $this->getDetail($model->getId());

        return ['success' => true, 'data' => $detail['data']];
    }

    protected function getListQuery()
    {
        $builder = $this->getManager()->createQueryBuilder();
        $builder->select('keyword')
            ->from(Keyword::class, 'keyword')
            ->leftJoin('keyword.article', 'article');

        return $builder;
    }

    public function listAction()
    {
        $offset = $this->Request()->getParam('start', 0);
        $limit = $this->Request()->getParam('limit', 20);
        $sort = $this->Request()->getParam('sort', []);
        $filter = $this->Request()->getParam('filter', []);
        $articleId = $this->Request()->getParam('articleId');

        if (!$articleId) {
            $this->View()->assign([
                'success' => false
            ]);

            return;
        }

        $builder = $this->getListQuery();
        $builder->setFirstResult($offset)
            ->setMaxResults($limit);

        $builder->where('article.id = :articleId')
            ->setParameter('articleId', $articleId);

        $filter = $this->getFilterConditions(
            $filter,
            Keyword::class,
            'keyword',
            $this->filterFields
        );

        $sort = $this->getSortConditions(
            $sort,
            Keyword::class,
            'keyword',
            $this->sortFields
        );

        if (!empty($sort)) {
            $builder->addOrderBy($sort);
        }

        if (!empty($filter)) {
            $builder->addFilter($filter);
        }

        $paginator = $this->getQueryPaginator($builder);
        $data = $paginator->getIterator()->getArrayCopy();
        $count = $paginator->count();


        $this->View()->assign([
            'success' => true,
            'data' => $data,
            'total' => $count
        ]);
    }
}