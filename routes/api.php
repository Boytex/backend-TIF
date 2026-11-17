<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\IncidentController;
use App\Http\Controllers\CommentController;
use App\Models\Category;

// Ruta pública (Login)
Route::post('/login', [AuthController::class, 'login']);

// Rutas protegidas por Sanctum
Route::middleware('auth:sanctum')->group(function () {
    // Autenticación
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/user', [AuthController::class, 'me']);

    // Categorías
    Route::get('/categories', function () {
        return response()->json(Category::all(), 200);
    });

    // Incidencias
    Route::get('/incidents', [IncidentController::class, 'index']);
    Route::post('/incidents', [IncidentController::class, 'store']);
    Route::get('/incidents/{id}', [IncidentController::class, 'show']);
    Route::put('/incidents/{id}/tomar', [IncidentController::class, 'tomar']);
    Route::put('/incidents/{id}/resolver', [IncidentController::class, 'resolver']);

    // Comentarios
    Route::get('/incidents/{id}/comments', [CommentController::class, 'index']);
    Route::post('/incidents/{id}/comments', [CommentController::class, 'store']);
});