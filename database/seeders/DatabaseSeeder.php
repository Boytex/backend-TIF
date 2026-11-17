<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Category;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // 1. Crear las categorías obligatorias para las incidencias
        $categories = [
            'Hardware',
            'Software',
            'Red',
            'Impresoras',
            'Otros'
        ];

        foreach ($categories as $categoryName) {
            Category::create(['name' => $categoryName]);
        }

        // 2. Crear usuario con rol "tecnico" para pruebas
        User::create([
            'name' => 'Técnico Soporte',
            'email' => 'tecnico@tif.com',
            'password' => Hash::make('password123'),
            'role' => 'tecnico',
        ]);

        // 3. Crear usuario con rol "usuario" común para pruebas
        User::create([
            'name' => 'Usuario Común',
            'email' => 'usuario@tif.com',
            'password' => Hash::make('password123'),
            'role' => 'usuario',
        ]);
    }
}