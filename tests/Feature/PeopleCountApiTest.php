<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\PeopleCount;

class PeopleCountApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_register_people_count(): void
    {
        $response = $this->postJson('/api/v1/people', [
            'cantidad' => 25,
        ]);

        $response->assertStatus(201)
            ->assertJson([
                'mensaje' => 'Registro creado correctamente',
                'cantidad' => 25,
            ]);

        $this->assertDatabaseHas('people_counts', [
            'cantidad' => 25,
        ]);
    }

    public function test_rejects_invalid_people_count(): void
    {
        $response = $this->postJson('/api/v1/people', [
            'cantidad' => -5,
        ]);

        $response->assertStatus(422)
            ->assertJson([
                'error' => true,
            ]);

        $responseEmpty = $this->postJson('/api/v1/people', []);
        $responseEmpty->assertStatus(422);
    }

    public function test_can_get_people_count_and_sum(): void
    {
        PeopleCount::create(['cantidad' => 10, 'fecha_hora' => now()->subMinutes(10)]);
        PeopleCount::create(['cantidad' => 20, 'fecha_hora' => now()->subMinutes(5)]);
        PeopleCount::create(['cantidad' => 30, 'fecha_hora' => now()]);

        $response = $this->getJson('/api/v1/people');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'suma' => 60,
                'total' => 60,
                'total_mediciones' => 3,
            ]);

        $this->assertCount(3, $response->json('registros'));
        $this->assertEquals(30, $response->json('ultimo_registro.cantidad'));
    }

    public function test_alias_routes_work(): void
    {
        $this->postJson('/api/personas', ['cantidad' => 12])
            ->assertStatus(201)
            ->assertJson(['mensaje' => 'Registro creado correctamente', 'cantidad' => 12]);

        $this->getJson('/api/personas')
            ->assertStatus(200)
            ->assertJson(['suma' => 12]);

        $this->getJson('/api/personas/suma')
            ->assertStatus(200)
            ->assertJson(['suma' => 12]);
    }
}

