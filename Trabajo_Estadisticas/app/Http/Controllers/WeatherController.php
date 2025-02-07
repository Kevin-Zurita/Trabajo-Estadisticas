<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Http;

class WeatherController extends Controller
{
    public function getAllStations()
    {
        $client = new Client();
        $apiKey = env('AEMET_API_KEY'); // Obtiene la clave de API desde .env
        $url = 'https://opendata.aemet.es/opendata/api/valores/climatologicos/inventarioestaciones/todasestaciones';

        try {
            // Primera petición: obtener la URL de los datos
            $response = $client->get($url, [
                'query' => ['api_key' => $apiKey]
            ]);

            $data = json_decode($response->getBody(), true);
            
            if (!isset($data['datos'])) {
                return response()->json(['error' => 'No se encontró la URL de datos'], 500);
            }

            // Segunda petición: descargar los datos de la URL proporcionada
            $dataUrl = $data['datos'];

            try {
                $dataResponse = $client->get($dataUrl, [
                    'headers' => [
                        'User-Agent' => 'Mozilla/5.0', // Evita bloqueos
                        'Accept' => 'application/json' // Pide JSON en la respuesta
                    ],
                    'allow_redirects' => true // Permite seguir redirecciones
                ]);

                $body = $dataResponse->getBody()->getContents();
                
                if (empty($body)) {
                    return response()->json(['error' => 'Respuesta vacía'], 500);
                }

                // Intentar decodificar JSON y forzar la decodificación UTF8
                $stations = json_decode($body, true, 512, JSON_INVALID_UTF8_IGNORE);
                
                if (json_last_error() !== JSON_ERROR_NONE) {
                    return response()->json([
                        'error' => 'Error decodificando JSON',
                        'raw_response' => $body
                    ], 500);
                }

                return response()->json($stations);
            } catch (\Exception $e) {
                return response()->json(['error' => $e->getMessage()], 500);
            }
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Obtener datos climáticos de una estación específica
     */
    public function getWeatherData($idema)
    {
        $apiKey = env('AEMET_API_KEY');
        $apiUrl = "https://opendata.aemet.es/opendata/api/observacion/convencional/datos/estacion/$idema";

        try {
            $response = Http::get($apiUrl, ['api_key' => $apiKey]);

            if ($response->failed()) {
                return response()->json(['error' => 'Error al conectar con AEMET'], 500);
            }

            $dataUrl = $response->json()['datos'] ?? null;

            if (!$dataUrl) {
                return response()->json(['error' => 'No se encontró la URL de datos'], 500);
            }

            // Obtener los datos de la URL proporcionada
            $weatherData = Http::get($dataUrl)->json();

            if (empty($weatherData)) {
                return response()->json(['error' => 'No hay datos para esta estación'], 404);
            }

            // Extraer la información relevante
            $weatherInfo = [
                'idema'          => $weatherData[0]['idema'] ?? null,
                'lat'            => $weatherData[0]['lat'] ?? null,
                'lon'            => $weatherData[0]['lon'] ?? null,
                'vel_viento'     => $weatherData[0]['vv'] ?? null,
                'temperatura'    => $weatherData[0]['ta'] ?? null,
                'humedad'        => $weatherData[0]['hr'] ?? null,
                'precipitacion'  => $weatherData[0]['prec'] ?? null,
                'fecha'          => $weatherData[0]['fint'] ?? null,
            ];

            return response()->json($weatherInfo);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}
