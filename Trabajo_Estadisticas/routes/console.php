<?php
use App\Http\Controllers\RecolectaInventario;
use Illuminate\Support\Facades\Schedule;

Schedule::call(function () {
    (new RecolectaInventario())->recolectaDatos();
})->everySixHours();

Schedule::call(function () {
    (new RecolectaInventario())->procesaYalmacenaStat();
})->weekly();