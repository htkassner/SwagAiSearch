<?php

namespace SwagAiSearch\Components\Clarifai;


use GuzzleHttp\Client;
use Shopware\Components\HttpClient\GuzzleHttpClient;
use SwagAiSearch\Components\Clarifai\Result\PredictionResult;

class ApiClient
{
    const BASE_URL = 'https://api.clarifai.com/v2';

    const GENERAL_MODEL = 'aaa03c23b3724a16a56b629203edc62c';
    const FOOD_MODEL = 'bd367be194cf45149e75f01d59f77ba7';
    const TRAVEL_MODEL = 'eee28c313d69466f836ab83287a54ed9';
    const NSFW_MODEL = 'e9576d86d2004ed1a38ba0cf39ecb4b1';
    const WEDDINGS_MODEL = 'c386b7a870114f4a87477c0824499348';
    const COLOR_MODEL = 'eeed0b6733a644cea07cf4c60f87ebb7';
    const CLUSTER_MODEL = 'cccbe437d6e54e2bb911c6aa292fb072';
    const FACE_DETECT_MODEL = 'a403429f2ddf4b49b307e318f00e528b';
    const BLUR = 'ddd9d34872ab32be9f0e3b2b98a87be2';

    /**
     * @var string
     */
    private $clientId;

    /**
     * @var string
     */
    private $clientSecret;

    /**
     * @var GuzzleHttpClient
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
     * @param $imageData
     * @param $model
     *
     * @return PredictionResult[]
     */
    public function predict($imageData, $model = self::GENERAL_MODEL)
    {
        if (!$this->accessToken) {
            $this->accessToken = $this->requestAccessToken();
        }

        $postData = json_encode([
            'inputs' => [
                [
                    'data' => [
                        'image' => [
                            'base64' => $imageData
                        ]
                    ]
                ]
            ]
        ]);

        $response = $this->doRequest(self::BASE_URL.'/models/'.$model.'/outputs', $postData, $this->accessToken);

        $outputs = $response['outputs'];
        $predictionResults = [];

        foreach ($outputs as $output) {
            $concepts = $output['data']['concepts'];

            foreach ($concepts as $concept) {
                $predictionResult = new PredictionResult($concept['name'], $concept['value']);
                $predictionResults[] = $predictionResult;
            }
        }

        return $predictionResults;

//        $response = $this->httpClient->post(self::BASE_URL.'/models/'.$model.'/outputs', [
//            'headers' => [
//                'Authorization' => 'Bearer ' . $this->accessToken,
//                'Accept' => 'application/json',
//                'Content-Type' => 'application/json',
//            ]
//        ],
//            $postData
//        );
//
//        return json_decode($response->getBody(), true);
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

    private function doRequest($url, $data = [], $token = null) {
        $ch = curl_init();
        $headers = [
            'Accept: application/json',
            'Content-Type: application/json'
        ];

        $headers[] = 'Authorization: Bearer ' . $token;
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, 1);
        // curl_setopt($ch, CURLOPT_USERPWD, "$this->clariApiUser:$this->clariApiSecret");
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        $result = curl_exec($ch);
        $ch_error = curl_error($ch);
        if ($ch_error) {
            echo "cURL Error: $ch_error";
            exit;
        }
        if (json_last_error() != JSON_ERROR_NONE) {
            echo "Can't get a proper json response.";
            exit();
        }
        return json_decode($result, true);
    }
}