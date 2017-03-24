<?php

namespace SwagAiSearch\Components\Clarifai\Result;


class PredictionResult
{
    /** @var string  */
    private $prediction;

    /** @var float  */
    private $probability;

    /**
     * @param string $prediction
     * @param float $probability
     */
    public function __construct($prediction, $probability)
    {
        $this->prediction = $prediction;
        $this->probability = $probability;
    }

    /**
     * @return string
     */
    public function getPrediction()
    {
        return $this->prediction;
    }

    /**
     * @param string $prediction
     */
    public function setPrediction($prediction)
    {
        $this->prediction = $prediction;
    }

    /**
     * @return float
     */
    public function getProbability()
    {
        return $this->probability;
    }

    /**
     * @param float $probability
     */
    public function setProbability($probability)
    {
        $this->probability = $probability;
    }
}