<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Models\EstacionInv;
use Carbon\Carbon;

class RecolectaInventario extends Controller
{
/**
 * Obtener todas las estaciones desde la API y guardarlas en la BD.
 */
public function getAllStations()
    {
        $client = new Client();
        $apiKey = env('AEMET_API_KEY');
        $url = 'https://opendata.aemet.es/opendata/api/valores/climatologicos/inventarioestaciones/todasestaciones';

        try {
            // Obtener la URL de los datos
            $response = $client->get($url, ['query' => ['api_key' => $apiKey]]);
            $data = json_decode($response->getBody(), true);

            if (!isset($data['datos'])) {
                return response()->json(['error' => 'No se encontró la URL de datos'], 500);
            }

            // Descargar los datos desde la URL obtenida
            $dataUrl = $data['datos'];
            $dataResponse = $client->get($dataUrl, [
                'headers' => ['User-Agent' => 'Mozilla/5.0', 'Accept' => 'application/json']
            ]);

            $stations = json_decode($dataResponse->getBody()->getContents(), true, 512, JSON_INVALID_UTF8_IGNORE);

            if (!is_array($stations)) {
                return response()->json(['error' => 'Formato incorrecto en la API'], 500);
            }

            // Seleccionamos 10 estaciones con provincias únicas
            $uniqueProvinces = [];
            $inserted = 0;

            foreach ($stations as $station) {
                $province = strtolower($station['provincia']);  // Convertimos la provincia a minúsculas para evitar duplicados

                // Si la provincia no está en el array, la agregamos
                if (!in_array($province, $uniqueProvinces)) {
                    // Agregamos la provincia al array de provincias únicas
                    $uniqueProvinces[] = $province;

                    if (isset($station['indicativo'])) {
                        EstacionInv::create([
                            'idema' => $station['indicativo'],
                            'nombre' => $station['nombre'] ?? null,
                            'latitud' => isset($station['latitud']) ? preg_replace('/[^\d.]/', '', $station['latitud']) : null,
                            'longitud' => isset($station['longitud']) ? preg_replace('/[^\d.]/', '', $station['longitud']) : null,
                            'altitud' => isset($station['altitud']) ? intval($station['altitud']) : null,
                            'provincia' => $station['provincia'] ?? null,
                            'fecha' => now(),
                        ]);

                        $inserted++;
                    }
                }

                // Detenemos cuando hemos insertado 10 estaciones
                if ($inserted >= 10) {
                    break;
                }
            }

            // Depuración: Provincias encontradas
            Log::info("Provincias encontradas: " . implode(', ', $uniqueProvinces));

            return response()->json(['message' => "Datos guardados correctamente ($inserted registros)"]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

/**
 * Recolectar datos climáticos de las estaciones y guardarlos en la tabla 'datos'.
 */
public function recolectaDatos()
{
    $client = new Client();
    $apiKey = env('AEMET_API_KEY');
    
    // Obtener todas las estaciones desde la base de datos
    $estaciones = EstacionInv::get(['id', 'idema']);

    if ($estaciones->isEmpty()) {
        return response()->json(['message' => 'No hay estaciones en la base de datos.'], 404);
    }

    $inserted = 0;
    $dataToInsert = [];

    // Obtener todas las combinaciones (idema, fecha) existentes en la base de datos
    $fechasExistentes = DB::table('datos')
        ->select('idema', 'fecha')
        ->get()
        ->map(function ($registro) {
            return "{$registro->idema}_{$registro->fecha}";
        })->toArray();

    // Recorremos todas las estaciones
    foreach ($estaciones as $estacion) {
        // Generamos la URL de la API para cada estación
        $url = "https://opendata.aemet.es/opendata/api/observacion/convencional/datos/estacion/{$estacion['idema']}";

        try {
            // Obtener la URL de los datos
            $response = $client->get($url, ['query' => ['api_key' => $apiKey]]);
            $data = json_decode($response->getBody(), true);

            if (!isset($data['datos'])) {
                Log::warning("No se encontró la URL de datos para la estación {$estacion->idema}");
                continue;
            }

            // Descargar los datos desde la URL obtenida
            $dataUrl = $data['datos'];
            $dataResponse = $client->get($dataUrl, [
                'headers' => ['User-Agent' => 'Mozilla/5.0', 'Accept' => 'application/json']
            ]);

            $clima = json_decode($dataResponse->getBody()->getContents(), true, 512, JSON_INVALID_UTF8_IGNORE);

            if (!is_array($clima)) {
                Log::warning("Formato incorrecto para la estación {$estacion->idema}");
                continue;
            }

            // Insertamos solo los registros nuevos (idema, fecha no deben repetirse)
            foreach ($clima as $registro) {
                $fechaRegistro = Carbon::parse($registro['fint'] ?? now())->toDateTimeString();
                $claveRegistro = "{$estacion->idema}_{$fechaRegistro}";

                if (in_array($claveRegistro, $fechasExistentes)) {
                    continue; // Si ya existe, lo ignoramos
                }

                // Agregamos el nuevo dato a la lista de inserción
                $dataToInsert[] = [
                    'id_estacion' => $estacion->id,
                    'idema' => $estacion->idema,
                    'fecha' => $fechaRegistro,
                    'vv' => $registro['vv'] ?? null,
                    'ta' => $registro['ta'] ?? null,
                    'hr' => $registro['hr'] ?? null,
                    'prec' => $registro['prec'] ?? null,
                ];
                $inserted++;

                // Guardamos la clave para futuras verificaciones en esta ejecución
                $fechasExistentes[] = $claveRegistro;
            }
            
        } catch (\Exception $e) {
            Log::error("Error en estación {$estacion->idema}: " . $e->getMessage());
            continue;
        }
    }

    // Si hay datos nuevos, los insertamos
    if (!empty($dataToInsert)) {
        DB::table('datos')->insert($dataToInsert);
    }

    return response()->json(['message' => "Datos recolectados correctamente ($inserted registros insertados)."]);
}

public function procesaStat()
{
    // Obtenemos los datos agrupados por id_estacion, idema y fecha, y calculamos los promedios
    $datosEstacion = DB::table('datos')
        ->select(
            'id_estacion',
            'idema',
            DB::raw('AVG(vv) as avg_vv'),
            DB::raw('AVG(ta) as avg_ta'),
            DB::raw('AVG(hr) as avg_hr'),
            DB::raw('AVG(prec) as avg_prec'),
            DB::raw('DATE_FORMAT(fecha, "%Y-%m-%d %H:%i") as fecha') // Formateamos la fecha para agruparla
        )
        ->whereNotNull('vv')  // Nos aseguramos de excluir los valores nulos
        ->whereNotNull('ta')  // Nos aseguramos de excluir los valores nulos
        ->whereNotNull('hr')  // Nos aseguramos de excluir los valores nulos
        ->whereNotNull('prec') // Nos aseguramos de excluir los valores nulos
        ->groupBy('id_estacion', 'idema', DB::raw('DATE_FORMAT(fecha, "%Y-%m-%d %H:%i")'))  // Agrupar por id_estacion, idema y fecha
        ->get();

    // Comprobamos si tenemos resultados antes de proceder
    if ($datosEstacion->isEmpty()) {
        return response()->json(['message' => 'No hay datos para procesar.']);
    }

    // Retornamos los datos con los promedios calculados
    return $datosEstacion;
}

public function almacenarStats($datosEstacion)
{
    foreach ($datosEstacion as $registro) {
        DB::table('stats')->insert([
            'id_estacion' => $registro->id_estacion,
            'idema' => $registro->idema,
            'fecha' => $registro->fecha,
            'vv' => $registro->avg_vv,
            'ta' => $registro->avg_ta,
            'hr' => $registro->avg_hr,
            'prec' => $registro->avg_prec
        ]);

        // Mostrar los promedios para verificar
        Log::info("Estación: {$registro->id_estacion} | Fecha: {$registro->fecha}");
        Log::info("Promedio VV: {$registro->avg_vv}");
        Log::info("Promedio TA: {$registro->avg_ta}");
        Log::info("Promedio HR: {$registro->avg_hr}");
        Log::info("Promedio PREC: {$registro->avg_prec}");
    }

    return response()->json(['message' => 'Estadísticas procesadas e insertadas correctamente en la tabla "stats".']);
}

public function procesaYalmacenaStat()
{
    // Paso 1: Calcular estadísticas
    $datosEstacion = $this->procesaStat();

    // Paso 2: Almacenar las estadísticas calculadas
    $this->almacenarStats($datosEstacion);

    return response()->json(['message' => 'Estadísticas procesadas e insertadas correctamente']);
}

}
