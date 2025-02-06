<?php
namespace App\Services;

use GuzzleHttp\Client;

class AemetService
{
    protected $client;
    protected $apiKey;

    public function __construct()
    {
        $this->client = new Client();
        $this->apiKey = env('AEMET_API_KEY');  // Obtener la clave de API desde .env
        }


    public function getWeatherData($location)
    {
        $url = "https://api.aemet.es/v1/forecast?api_key={$this->apiKey}&location={$location}";
        $response = $this->client->get($url);
        return json_decode($response->getBody()->getContents(), true);
    }
}
