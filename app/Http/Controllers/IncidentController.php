<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class IncidentController extends Controller
{
    public function index()
    {
        return response()->json([
            'message' => 'Lista de incidencias',
            'data' => []
        ], 200);
    }

    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
        ]);

        // Lógica para guardar la incidencia en la base de datos...
        // ...

        return response()->json([
            'message' => 'Incidencia creada con éxito',
        ], 200);
    }

    public function show($id)
    {
        // Lógica para ver una incidencia
    }

    public function tomar($id)
    {
        // Lógica para tomar incidencia (rol técnico)
    }

    public function resolver($id)
    {
        // Lógica para resolver incidencia
    }
}