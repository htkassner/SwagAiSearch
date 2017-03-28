<?php

namespace SwagAiSearch\Components\Clarifai;

use GuzzleHttp\Client;
use SwagAiSearch\Components\Clarifai\Result\PredictionResult;
use WebDriver\Exception;

class ApiClient
{
    const BASE_URL = 'https://api.clarifai.com/v2';
    const IMAGE_DATA_TYPE_BASE64 = 'base64';
    const IMAGE_DATA_TYPE_URL = 'url';

    const GENERAL_MODEL = 'aaa03c23b3724a16a56b629203edc62c';
    const FOOD_MODEL = 'bd367be194cf45149e75f01d59f77ba7';
    const TRAVEL_MODEL = 'eee28c313d69466f836ab83287a54ed9';
    const NSFW_MODEL = 'e9576d86d2004ed1a38ba0cf39ecb4b1';
    const WEDDINGS_MODEL = 'c386b7a870114f4a87477c0824499348';
    const COLOR_MODEL = 'eeed0b6733a644cea07cf4c60f87ebb7';
    const CLUSTER_MODEL = 'cccbe437d6e54e2bb911c6aa292fb072';
    const FACE_DETECT_MODEL = 'a403429f2ddf4b49b307e318f00e528b';
    const BLUR = 'ddd9d34872ab32be9f0e3b2b98a87be2';

    /** @var array */
    private $languages = [
        'de_DE' => 'de',
        'en_GB' => 'en',
        'es_ES' => 'es',
        'fr_FR' => 'fr',
        'nl_NL' => 'nl',
    ];

    /**
     * @var string
     */
    private $clientId;

    /**
     * @var string
     */
    private $clientSecret;

    /**
     * @var Client
     */
    private $httpClient;

    /**
     * @var string
     */
    private $accessToken;

    /**
     * @var \Shopware_Components_Config
     */
    public function __construct(\Shopware_Components_Config $config)
    {
        $this->clientId     = $config->clarifaiClientId;
        $this->clientSecret = $config->clarifaiClientSecret;
        $this->httpClient   = new Client();
    }

    /**
     * @param array $imageData
     * @param $locale
     * @param $model
     *
     * @return PredictionResult[]
     */
    public function predict(array $imageData, $locale = 'de_DE', $model = self::GENERAL_MODEL)
    {
        if (!$this->clientId || !$this->clientSecret) {
            throw new \Exception('No clarifai.com credentials configured! Please configure your plugin');
        }

        if (!$this->accessToken) {
            $this->accessToken = $this->requestAccessToken();
        }

        $inputs = [];
        foreach ($imageData as $image) {
            $type = self::IMAGE_DATA_TYPE_BASE64;
            if (filter_var($image, FILTER_VALIDATE_URL)) {
                $type = self::IMAGE_DATA_TYPE_URL;
            }
            $inputs[] = [
                'data' => [
                    'image' => [
                        $type => $image
                    ]
                ]
            ];
        }

        $postData = json_encode([
            'inputs' => $inputs,
            'model' => [
                'output_info' => [
                    'output_config' => [
                        'language' => $this->languages[$locale]
                    ]
                ]
            ]
        ]);

        $response = $this->httpClient->post(self::BASE_URL.'/models/'.$model.'/outputs',
            [
                'headers' => [
                    'Authorization' => 'Bearer ' . $this->accessToken,
                    'Accept' => 'application/json',
                    'Content-Type' => 'application/json',
                ],
                'body' => $postData
            ]
        );

        $result = json_decode($response->getBody(), true);
        $outputs = $result['outputs'];

        $predictionResults = [];

        foreach ($outputs as $output) {
            $concepts = $output['data']['concepts'];

            foreach ($concepts as $concept) {
                $predictionResult = new PredictionResult($concept['name'], $concept['value']);
                $predictionResults[] = $predictionResult;
            }
        }

        return $predictionResults;
    }

    /**
     * Retrieve a new access token.
     *
     * @return array
     */
    private function requestAccessToken()
    {
        $response = $this->httpClient->post(self::BASE_URL . '/token', [
            'auth' => [$this->clientId, $this->clientSecret]
        ]);

        $result = json_decode($response->getBody(), true);

        return $result['access_token'];
    }
}
