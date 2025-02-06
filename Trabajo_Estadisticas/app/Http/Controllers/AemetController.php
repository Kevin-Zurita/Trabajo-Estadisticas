<?php
namespace App\Http\Controllers;

use App\Services\AemetService;
use Illuminate\Http\Request;

class AemetController extends Controller
{
    protected $aemetService;

    public function __construct(AemetService $aemetService)
    {
        $this->aemetService = $aemetService;
    }

    public function getWeather(Request $request)
    {
        $location = $request->input('location');
        $weatherData = $this->aemetService->getWeatherData($location);
        return response()->json($weatherData);
    }
}
