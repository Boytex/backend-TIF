<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\User;
use Tests\TestCase;

class TifApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_un_usuario_sin_autenticar_no_puede_ver_las_incidencias()
    {
        $response = $this->getJson('/api/incidents');

        // Laravel Sanctum debe denegar el acceso con un código 401
        $response->assertStatus(401);
    }

    public function test_un_usuario_puede_hacer_login_y_recibir_un_token()
    {
        // Creamos un usuario de prueba en la base de datos temporal
        User::create([
            'name' => 'Usuario Test',
            'email' => 'test@tif.com',
            'password' => bcrypt('password123'),
            'role' => 'usuario'
        ]);

        // Intentamos loguearnos
        $response = $this->postJson('/api/login', [
            'email' => 'test@tif.com',
            'password' => 'password123'
        ]);

        // Verificamos que responda éxito y devuelva el token
        $response->assertStatus(200)
                 ->assertJsonStructure([
                     'message',
                     'token',
                     'user'
                 ]);
    }
}