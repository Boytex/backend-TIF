<?php

use Illuminate\Support\Facades\Route;

// Laravel funciona únicamente como API; la interfaz vive en el repositorio frontend.
Route::get('/', function () {
    return response()->json([
        'application' => 'Sistema de Gestión de Incidencias Técnicas',
        'status' => 'online',
        'api' => url('/api'),
        'message' => 'La interfaz se ejecuta desde el proyecto tif-incidencias-frontend.',
    ]);
});