<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        foreach (['Hardware', 'Software', 'Red', 'Impresoras', 'Otros'] as $name) {
            Category::firstOrCreate(['name' => $name]);
        }

        User::updateOrCreate(
            ['email' => 'tecnico@tif.com'],
            [
                'name' => 'Técnico de Soporte',
                'password' => Hash::make('password123'),
                'role' => 'tecnico',
            ]
        );

        User::updateOrCreate(
            ['email' => 'usuario@tif.com'],
            [
                'name' => 'Usuario de Prueba',
                'password' => Hash::make('password123'),
                'role' => 'usuario',
            ]
        );
    }
}
