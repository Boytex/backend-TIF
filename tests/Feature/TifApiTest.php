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
        User::create([
            'name' => 'Usuario Test',
            'email' => 'test@tif.com',
            'password' => bcrypt('password123'),
            'role' => 'usuario'
        ]);

        $response = $this->postJson('/api/login', [
            'email' => 'test@tif.com',
            'password' => 'password123'
        ]);

        $response->assertStatus(200)
                 ->assertJsonStructure([
                     'message',
                     'access_token',
                     'token_type',
                     'user' => [
                         'id',
                         'name',
                         'email',
                         'role'
                     ]
                 ]);
    }

    public function test_un_usuario_autenticado_puede_ver_las_incidencias()
    {
        $user = User::create([
            'name' => 'Usuario Logueado',
            'email' => 'auth@tif.com',
            'password' => bcrypt('password123'),
            'role' => 'usuario'
        ]);

        // Simulamos la petición con el token o usando actingAs con Sanctum
        $response = $this->actingAs($user, 'sanctum')->getJson('/api/incidents');

        // Esperamos un código 200 
        $response->assertStatus(200);
    }

    public function test_un_usuario_puede_cerrar_sesion_correctamente()
    {
        $user = User::create([
            'name' => 'Usuario Logout',
            'email' => 'logout@tif.com',
            'password' => bcrypt('password123'),
            'role' => 'usuario'
        ]);

        // 1. Creamos un token real de Sanctum
        $token = $user->createToken('auth_token')->plainTextToken;

        // 2. Enviamos la petición enviando el token Bearer en la cabecera
        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
                         ->postJson('/api/logout');

        $response->assertStatus(200)
                 ->assertJson([
                     'message' => 'Sesión cerrada correctamente'
                 ]);
    }

    public function test_un_usuario_autenticado_puede_crear_una_incidencia()
    {
        $user = User::create([
            'name' => 'Usuario Creador',
            'email' => 'creador@tif.com',
            'password' => bcrypt('password123'),
            'role' => 'usuario'
        ]);

        $response = $this->actingAs($user, 'sanctum')->postJson('/api/incidents', [
            'title' => 'Falla en el proyector',
            'description' => 'El proyector del aula 3 no enciende.',
    
        ]);

        // Dependiendo de si tu controlador devuelve 200 o 201 al crear
        $response->assertStatus(200); 
    }

    public function test_falla_al_intentar_crear_incidencia_sin_datos()
    {
        $user = User::create([
            'name' => 'Usuario Validador',
            'email' => 'validador@tif.com',
            'password' => bcrypt('password123'),
            'role' => 'usuario'
        ]);

        $response = $this->actingAs($user, 'sanctum')->postJson('/api/incidents', []);

        $response->assertStatus(422);
    }

    public function test_un_usuario_autenticado_puede_ver_las_categorias()
    {
        $user = User::create([
            'name' => 'Usuario Categorias',
            'email' => 'cat@tif.com',
            'password' => bcrypt('password123'),
            'role' => 'usuario'
        ]);

        $response = $this->actingAs($user, 'sanctum')->getJson('/api/categories');

        $response->assertStatus(200);
    }

    public function test_el_login_falla_con_credenciales_incorrectas()
    {
        User::create([
            'name' => 'Usuario Real',
            'email' => 'real@tif.com',
            'password' => bcrypt('claveCorrecta123'),
            'role' => 'usuario'
        ]);

        $response = $this->postJson('/api/login', [
            'email' => 'real@tif.com',
            'password' => 'claveEquivocada'
        ]);

        // ValidationException en AuthController devuelve 422
        $response->assertStatus(422)
                 ->assertJsonValidationErrors(['email']);
    }

    public function test_un_usuario_autenticado_puede_consultar_su_perfil()
    {
        $user = User::create([
            'name' => 'Elias Perfil',
            'email' => 'perfil@tif.com',
            'password' => bcrypt('password123'),
            'role' => 'tecnico'
        ]);

        $response = $this->actingAs($user, 'sanctum')->getJson('/api/user');

        $response->assertStatus(200)
                 ->assertJson([
                     'user' => [
                         'name' => 'Elias Perfil',
                         'email' => 'perfil@tif.com',
                         'role' => 'tecnico',
                     ]
                 ]);
    }

    public function test_un_usuario_sin_autenticar_no_puede_ver_las_categorias()
    {
        $response = $this->getJson('/api/categories');

        $response->assertStatus(401);
    }
}