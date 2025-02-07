<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class AemetService
{
    protected $apiKey;
    protected $baseUrl;

    public function __construct()
    {
        $this->apiKey = env('AEMET_API_KEY');
        $this->baseUrl = "https://opendata.aemet.es/opendata/api";
    }

    public function getWeatherStations()
    {
        if (!$this->apiKey) {
            return ['error' => 'API Key no configurada en el archivo .env'];
        }

        $url = "{$this->baseUrl}/observacion/convencional/todas/?api_key={$this->apiKey}";

        // 1️⃣ Primera petición para obtener la URL de los datos
        $response = Http::get($url);

        // Verificamos si la respuesta es exitosa y registramos el log de la respuesta
        if ($response->successful()) {
            Log::info('Respuesta de AEMET (primera):', ['data' => $response->json()]);
        } else {
            Log::error('Error al obtener la URL de AEMET:', ['response' => $response->body()]);
        }

        if (!$response->successful()) {
            return [
                'error' => 'No se pudo obtener la URL de datos',
                'details' => $response->body()
            ];
        }

        $data = $response->json();

        if (!isset($data['datos'])) {
            return ['error' => 'La API de AEMET no devolvió una URL válida'];
        }

        // 2️⃣ Segunda petición para obtener los datos reales
        $stationsResponse = Http::get($data['datos']);

        // Verificamos si la respuesta es exitosa y registramos el log de la respuesta
        if ($stationsResponse->successful()) {
            Log::info('Respuesta de AEMET (segunda):', ['data' => $stationsResponse->json()]);
        } else {
            Log::error('Error al obtener las estaciones de AEMET:', ['response' => $stationsResponse->body()]);
        }

        if (!$stationsResponse->successful()) {
            return [
                'error' => 'No se pudo obtener la lista de estaciones',
                'details' => $stationsResponse->body()
            ];
        }

        $stations = $stationsResponse->json();

        // 3️⃣ Filtrar los campos requeridos
        return collect($stations)->map(function ($station) {
            return [
                'idema' => $station['idema'] ?? null,
                'latitud' => isset($station['lat']) ? floatval($station['lat']) : null,
                'longitud' => isset($station['lon']) ? floatval($station['lon']) : null,
                'nombre' => $station['ubi'] ?? null,
                'provincia' => $station['provincia'] ?? null,
                'cp' => $station['indsinop'] ?? null,
                'altitud' => $station['alt'] ?? null,
            ];
        })->toArray();
    }
}