<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Incident;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TifApiTest extends TestCase
{
    use RefreshDatabase;

    private Category $category;

    protected function setUp(): void
    {
        parent::setUp();
        $this->category = Category::create(['name' => 'Hardware']);
    }

    private function makeUser(string $role = 'usuario', string $email = 'usuario@test.com'): User
    {
        return User::create([
            'name' => ucfirst($role) . ' Test',
            'email' => $email,
            'password' => 'password123',
            'role' => $role,
        ]);
    }

    private function makeIncident(User $creator, array $attributes = []): Incident
    {
        return Incident::create(array_merge([
            'title' => 'Equipo sin conexión',
            'description' => 'La computadora del aula no tiene conexión.',
            'status' => Incident::STATUS_OPEN,
            'user_id' => $creator->id,
            'category_id' => $this->category->id,
        ], $attributes));
    }

    public function test_un_visitante_no_puede_listar_incidencias(): void
    {
        $this->getJson('/api/incidencias')->assertUnauthorized();
    }

    public function test_login_devuelve_token_y_datos_basicos_del_usuario(): void
    {
        $this->makeUser();

        $this->postJson('/api/login', [
            'email' => 'usuario@test.com',
            'password' => 'password123',
        ])->assertOk()->assertJsonStructure([
            'access_token', 'token_type', 'user' => ['id', 'name', 'email', 'role'],
        ]);
    }

    public function test_usuario_solo_ve_sus_propias_incidencias(): void
    {
        $user = $this->makeUser();
        $other = $this->makeUser('usuario', 'otro@test.com');
        $ownIncident = $this->makeIncident($user);
        $otherIncident = $this->makeIncident($other, ['title' => 'Incidencia ajena']);

        $this->actingAs($user, 'sanctum')
            ->getJson('/api/incidencias')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.id', $ownIncident->id);

        $this->actingAs($user, 'sanctum')
            ->getJson('/api/incidencias/' . $otherIncident->id)
            ->assertNotFound();
    }

    public function test_tecnico_puede_ver_todas_las_incidencias(): void
    {
        $user = $this->makeUser();
        $other = $this->makeUser('usuario', 'otro@test.com');
        $this->makeIncident($user);
        $this->makeIncident($other, ['title' => 'Otro caso']);
        $technician = $this->makeUser('tecnico', 'tecnico@test.com');

        $this->actingAs($technician, 'sanctum')
            ->getJson('/api/incidencias')
            ->assertOk()
            ->assertJsonCount(2, 'data');
    }

    public function test_usuario_puede_crear_una_incidencia_que_nace_abierta(): void
    {
        $user = $this->makeUser();

        $this->actingAs($user, 'sanctum')
            ->postJson('/api/incidencias', [
                'title' => 'Falla del proyector',
                'description' => 'El proyector del aula 3 no enciende.',
                'category_id' => $this->category->id,
            ])
            ->assertCreated()
            ->assertJsonPath('data.status', Incident::STATUS_OPEN)
            ->assertJsonPath('data.user_id', $user->id);

        $this->assertDatabaseHas('incidents', ['title' => 'Falla del proyector']);
    }

    public function test_creacion_valida_campos_obligatorios_y_categoria(): void
    {
        $user = $this->makeUser();

        $this->actingAs($user, 'sanctum')
            ->postJson('/api/incidencias', [])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['title', 'description', 'category_id']);
    }

    public function test_tecnico_puede_tomar_una_incidencia_abierta(): void
    {
        $creator = $this->makeUser();
        $technician = $this->makeUser('tecnico', 'tecnico@test.com');
        $incident = $this->makeIncident($creator);

        $this->actingAs($technician, 'sanctum')
            ->putJson('/api/incidencias/' . $incident->id . '/tomar')
            ->assertOk()
            ->assertJsonPath('data.status', Incident::STATUS_IN_PROGRESS)
            ->assertJsonPath('data.technician_id', $technician->id);
    }

    public function test_usuario_no_puede_tomar_una_incidencia(): void
    {
        $user = $this->makeUser();
        $incident = $this->makeIncident($user);

        $this->actingAs($user, 'sanctum')
            ->putJson('/api/incidencias/' . $incident->id . '/tomar')
            ->assertForbidden();
    }

    public function test_solo_el_tecnico_asignado_puede_resolver_y_debe_indicar_resolucion(): void
    {
        $creator = $this->makeUser();
        $assigned = $this->makeUser('tecnico', 'asignado@test.com');
        $otherTechnician = $this->makeUser('tecnico', 'otro-tecnico@test.com');
        $incident = $this->makeIncident($creator, [
            'status' => Incident::STATUS_IN_PROGRESS,
            'technician_id' => $assigned->id,
        ]);

        $this->actingAs($otherTechnician, 'sanctum')
            ->putJson('/api/incidencias/' . $incident->id . '/resolver', [
                'resolution' => 'Se realizó una reparación de prueba.',
            ])->assertForbidden();

        $this->actingAs($assigned, 'sanctum')
            ->putJson('/api/incidencias/' . $incident->id . '/resolver', [])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['resolution']);

        $this->actingAs($assigned, 'sanctum')
            ->putJson('/api/incidencias/' . $incident->id . '/resolver', [
                'resolution' => 'Se reinstaló el controlador y se probó el equipo.',
            ])
            ->assertOk()
            ->assertJsonPath('data.status', Incident::STATUS_RESOLVED);
    }

    public function test_usuario_y_tecnico_pueden_comentar_y_se_listan_en_orden(): void
    {
        $creator = $this->makeUser();
        $technician = $this->makeUser('tecnico', 'tecnico@test.com');
        $incident = $this->makeIncident($creator);

        $this->actingAs($creator, 'sanctum')
            ->postJson('/api/incidencias/' . $incident->id . '/comentarios', [
                'comment' => 'El problema comenzó hoy.',
            ])->assertCreated();

        $this->actingAs($technician, 'sanctum')
            ->postJson('/api/incidencias/' . $incident->id . '/comentarios', [
                'comment' => 'Vamos a revisar el equipo.',
            ])->assertCreated();

        $this->actingAs($creator, 'sanctum')
            ->getJson('/api/incidencias/' . $incident->id . '/comentarios')
            ->assertOk()
            ->assertJsonCount(2, 'data')
            ->assertJsonPath('data.0.comment', 'El problema comenzó hoy.')
            ->assertJsonPath('data.1.comment', 'Vamos a revisar el equipo.');
    }

    public function test_categorias_precargadas_se_pueden_consultar(): void
    {
        $user = $this->makeUser();

        $this->actingAs($user, 'sanctum')
            ->getJson('/api/categories')
            ->assertOk()
            ->assertJsonPath('data.0.name', 'Hardware');
    }

    public function logout(Request $request): JsonResponse
    {
       // Revocar el token actual.
       $request->user()->currentAccessToken()?->delete();

       // Limpiar el usuario almacenado en el guard de Sanctum.
       Auth::guard('sanctum')->forgetUser();

       return response()->json([
           'success' => true,
           'message' => 'Sesión cerrada correctamente.',
       ]);
    }
}
