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

    public function getWeatherStations()
    {
        $stations = $this->aemetService->getWeatherStations();
        return response()->json($stations);
    }
}