<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\CommentController;
use App\Http\Controllers\IncidentController;
use App\Models\Category;
use Illuminate\Support\Facades\Route;

Route::post('/login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/user', [AuthController::class, 'me']);
    Route::get('/categories', fn () => response()->json([
        'data' => Category::query()->orderBy('id')->get(['id', 'name']),
    ]));

    // Endpoints en español según el enunciado del TIF.
    Route::get('/incidencias', [IncidentController::class, 'index']);
    Route::post('/incidencias', [IncidentController::class, 'store']);
    Route::get('/incidencias/{id}', [IncidentController::class, 'show']);
    Route::put('/incidencias/{id}/tomar', [IncidentController::class, 'tomar']);
    Route::put('/incidencias/{id}/resolver', [IncidentController::class, 'resolver']);
    Route::get('/incidencias/{id}/comentarios', [CommentController::class, 'index']);
    Route::post('/incidencias/{id}/comentarios', [CommentController::class, 'store']);

    // Alias en inglés para mantener compatibilidad con el backend original.
    Route::get('/incidents', [IncidentController::class, 'index']);
    Route::post('/incidents', [IncidentController::class, 'store']);
    Route::get('/incidents/{id}', [IncidentController::class, 'show']);
    Route::put('/incidents/{id}/tomar', [IncidentController::class, 'tomar']);
    Route::put('/incidents/{id}/resolver', [IncidentController::class, 'resolver']);
    Route::get('/incidents/{id}/comments', [CommentController::class, 'index']);
    Route::post('/incidents/{id}/comments', [CommentController::class, 'store']);
});
