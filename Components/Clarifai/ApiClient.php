<?php

namespace SwagAiSearch\Components\Clarifai;


class ApiClient
{
    const CLARIFAI_API_URL = 'https://api.clarifai.com/v2/models/aaa03c23b3724a16a56b629203edc62c/outputs';

    public function predict($imageData, $model)
    {

    }

    private function curlConnector()
    {
        $curlHandle = curl_init();

        curl_setopt($curlHandle, CURLOPT_URL, self::CLARIFAI_API_URL);
        curl_setopt($curlHandle,CURLOPT_RETURNTRANSFER,true);
        curl_setopt($curlHandle, CURLOPT_POSTFIELDS, '');

        $response = curl_exec($curlHandle);
    }
}