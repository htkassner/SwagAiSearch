<?php

use Shopware\Models\Article\Article;
use Shopware\Models\Article\Image;
use SwagAiSearch\Components\Clarifai\ApiClient;
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

    public function learnAction()
    {
        $articleId = $this->Request()->getParam('articleId');

        if (!$articleId) {
            $this->View()->assign(['success' => false]);

            return;
        }

        $modelManager = $this->container->get('models');
        $article = $modelManager->find(Article::class, $articleId);
        $images = $modelManager->getRepository(Image::class)->findBy(['articleId' => $articleId]);
        $productImages = [];
        $mediaService = $this->container->get('shopware_media.media_service');
        $predictionMinimum = (float) $this->container->get('config')->get('clarifaiPredictionMinimum');

        /** @var Image $image */
        foreach ($images as $image) {
            $imagePath = $mediaService->getUrl($image->getMedia()->getPath());
            $imageData = base64_encode(file_get_contents($imagePath));

            $productImages[] = $imageData;
        }

        try {
            /** @var ApiClient $apiClient */
            $apiClient = $this->container->get('swag_ai_search.clarifai.api_client');

            $predictionResults = $apiClient->predict($productImages);

            $usedKeywords = [];
            foreach ($predictionResults as $predictionResult) {
                $prediction = $predictionResult->getPrediction();
                if (!in_array($prediction, $usedKeywords) && $predictionResult->getPrediction() >= $predictionMinimum) {
                    $keyword = new Keyword();
                    $keyword->setArticle($article);
                    $keyword->setKeyword($prediction);
                    $modelManager->persist($keyword);
                    $usedKeywords[] = $prediction;
                }
            }

            $modelManager->flush();
        } catch (\Exception $e) {
            $this->View()->assign([
                'success' => false,
                'message' => $e->getMessage()
            ]);

            return;
        }

        $this->View()->assign(['success' => true]);
    }

    protected function getListQuery()
    {
        $builder = $this->getManager()->createQueryBuilder();
        $builder->select('keyword')
            ->from(Keyword::class, 'keyword')
            ->leftJoin('keyword.article', 'article');

        return $builder;
    }
}
