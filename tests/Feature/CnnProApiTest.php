<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Dvr;
use App\Models\Camera;
use App\Models\CameraZone;

class CnnProApiTest extends TestCase
{
    use RefreshDatabase;

    private string $validToken = 'G810IIXfOGA8HMnqRj98lpVxQ33lx9FmTVwd';

    public function test_rejects_unauthorized_access_without_token(): void
    {
        $response = $this->getJson('/api/v1/cnn/cameras');
        $response->assertStatus(401)
            ->assertJson([
                'error' => true,
                'code' => 'UNAUTHORIZED',
            ]);
    }

    public function test_can_get_cameras_paginated_by_10(): void
    {
        $user = User::factory()->create();
        $dvr = Dvr::create([
            'user_id' => $user->id,
            'lat' => -34.603722,
            'lon' => -58.381592,
            'ip' => '192.168.1.50',
            'port' => 8000,
            'status' => 1,
        ]);

        for ($i = 1; $i <= 15; $i++) {
            Camera::create([
                'dvr_id' => $dvr->id,
                'channel' => $i,
                'name' => "Cámara Test $i",
                'status' => 1,
            ]);
        }

        $response = $this->getJson('/api/v1/cnn/cameras', [
            'X-CNN-Token' => $this->validToken,
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
            ])
            ->assertJsonPath('pagination.per_page', 10)
            ->assertJsonPath('pagination.total', 15)
            ->assertJsonPath('pagination.last_page', 2);

        $this->assertCount(10, $response->json('data'));
    }

    public function test_can_update_parking_availability(): void
    {
        $user = User::factory()->create();
        $dvr = Dvr::create([
            'user_id' => $user->id,
            'lat' => -34.603722,
            'lon' => -58.381592,
            'status' => 1,
        ]);

        $camera = Camera::create([
            'dvr_id' => $dvr->id,
            'channel' => 1,
            'name' => 'Cámara Test',
            'status' => 1,
        ]);

        $zone = CameraZone::create([
            'camera_id' => $camera->id,
            'geometry' => null,
            'total_length_meters' => 15.00,
            'status' => 1,
        ]);

        $headers = [
            'X-CNN-Token' => $this->validToken,
        ];

        // Mark full / unavailable
        $responseFull = $this->postJson('/api/v1/cnn/availability', [
            'camera_zone_id' => $zone->id,
            'is_available' => false,
        ], $headers);

        $responseFull->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => [
                    'is_available' => false,
                    'status' => 'full',
                ],
            ]);

        $this->assertDatabaseHas('camera_zones', [
            'id' => $zone->id,
            'status' => 0,
        ]);

        // Mark available
        $responseAvailable = $this->postJson('/api/v1/cnn/availability', [
            'camera_id' => $camera->id,
            'is_available' => true,
        ], $headers);

        $responseAvailable->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => [
                    'is_available' => true,
                    'status' => 'available',
                ],
            ]);

        $this->assertDatabaseHas('camera_zones', [
            'id' => $zone->id,
            'status' => 1,
        ]);
    }
}
