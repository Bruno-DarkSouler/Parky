<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Car;
use App\Models\Dvr;
use App\Models\Camera;
use App\Models\CameraZone;
use App\Models\OccupancyLog;
use Illuminate\Support\Str;

class ParkingDataSeeder extends Seeder
{
    public function run(): void
    {
        $users = User::all();
        if ($users->isEmpty()) {
            $user = User::create([
                'id' => (string) Str::uuid(),
                'name' => 'Usuario Test',
                'email' => 'test@parkingfinder.com',
                'password' => bcrypt('password'),
                'status' => 1,
            ]);
            $users = collect([$user]);
        }

        $adminUser = $users->first();

        // Seed test cars for all users so everyone can test Fitito vs Cybertruck
        // Seed test cars for all users so everyone can test Fitito vs Cybertruck
        foreach ($users as $idx => $user) {
            $suffix = $idx > 0 ? ('-' . ($idx + 1)) : '';
            
            Car::updateOrCreate(
                ['user_id' => $user->id, 'plate' => 'FIT-600' . $suffix],
                [
                    'brand' => 'Fiat',
                    'model' => '600 (Fitito)',
                    'year' => 1975,
                    'color' => 'Amarillo',
                    'width_meters' => 1.40,
                    'length_meters' => 3.30,
                    'status' => 1,
                ]
            );

            Car::updateOrCreate(
                ['user_id' => $user->id, 'plate' => 'CYB-999' . $suffix],
                [
                    'brand' => 'Tesla',
                    'model' => 'Cybertruck',
                    'year' => 2024,
                    'color' => 'Plateado',
                    'width_meters' => 2.00,
                    'length_meters' => 5.80,
                    'status' => 1,
                ]
            );

            Car::updateOrCreate(
                ['user_id' => $user->id, 'plate' => 'ONX-123' . $suffix],
                [
                    'brand' => 'Chevrolet',
                    'model' => 'Onix Sedan',
                    'year' => 2022,
                    'color' => 'Blanco',
                    'width_meters' => 1.73,
                    'length_meters' => 4.47,
                    'status' => 1,
                ]
            );
        }

        // Clean previous DVRs/Cameras/Zones for fresh seeding
        Dvr::query()->delete();

        // 1. DVR 1: Calle Ader 1500 (20 meters zone)
        $dvr1 = Dvr::create([
            'user_id' => $adminUser->id,
            'lat' => -34.56800000,
            'lon' => -58.45600000,
            'ip' => '192.168.1.100',
            'port' => 8000,
            'user' => 'admin',
            'password' => bcrypt('admin'),
            'status' => 1,
        ]);

        $cam1 = Camera::create([
            'dvr_id' => $dvr1->id,
            'channel' => 1,
            'name' => 'Calle Ader 1500 (Zona 20m)',
            'status' => 1,
        ]);

        $zone1 = CameraZone::create([
            'camera_id' => $cam1->id,
            'geometry' => [['x' => 10, 'y' => 10], ['x' => 500, 'y' => 10]],
            'total_length_meters' => 20.00,
            'homography_matrix' => null,
            'status' => 1,
        ]);

        // Occupancy log: 2 cars in the middle (0 to 4.5m and 10 to 14.5m)
        OccupancyLog::create([
            'camera_zone_id' => $zone1->id,
            'detected_objects_coords' => [
                ['start' => 0.0, 'end' => 4.5],
                ['start' => 10.0, 'end' => 14.5],
            ],
        ]);

        // 2. DVR 2: Av. Corrientes 2400 (30 meters zone)
        $dvr2 = Dvr::create([
            'user_id' => $adminUser->id,
            'lat' => -34.60370000,
            'lon' => -58.38160000,
            'ip' => '192.168.1.101',
            'port' => 8000,
            'user' => 'admin',
            'password' => bcrypt('admin'),
            'status' => 1,
        ]);

        $cam2 = Camera::create([
            'dvr_id' => $dvr2->id,
            'channel' => 1,
            'name' => 'Av. Corrientes 2400 (Zona 30m)',
            'status' => 1,
        ]);

        $zone2 = CameraZone::create([
            'camera_id' => $cam2->id,
            'geometry' => [['x' => 10, 'y' => 10], ['x' => 500, 'y' => 10]],
            'total_length_meters' => 30.00,
            'homography_matrix' => null,
            'status' => 1,
        ]);

        OccupancyLog::create([
            'camera_zone_id' => $zone2->id,
            'detected_objects_coords' => [
                ['start' => 0.0, 'end' => 5.0],
                ['start' => 12.0, 'end' => 17.0],
            ],
        ]);

        // 3. DVR 3: Av. San Martín 1200 (15 meters zone, congested)
        $dvr3 = Dvr::create([
            'user_id' => $adminUser->id,
            'lat' => -34.59500000,
            'lon' => -58.39000000,
            'ip' => '192.168.1.102',
            'port' => 8000,
            'user' => 'admin',
            'password' => bcrypt('admin'),
            'status' => 1,
        ]);

        $cam3 = Camera::create([
            'dvr_id' => $dvr3->id,
            'channel' => 1,
            'name' => 'Av. San Martín 1200 (Zona 15m)',
            'status' => 1,
        ]);

        $zone3 = CameraZone::create([
            'camera_id' => $cam3->id,
            'geometry' => [['x' => 10, 'y' => 10], ['x' => 500, 'y' => 10]],
            'total_length_meters' => 15.00,
            'homography_matrix' => null,
            'status' => 1,
        ]);

        OccupancyLog::create([
            'camera_zone_id' => $zone3->id,
            'detected_objects_coords' => [
                ['start' => 0.0, 'end' => 4.5],
                ['start' => 5.5, 'end' => 10.0],
                ['start' => 11.0, 'end' => 15.0],
            ],
        ]);

        // 4. DVR 4: Cerrito 3960 (Zona 25m - Frente Escuela Técnica N°1 Eduardo Ader)
        $dvr4 = Dvr::create([
            'user_id' => $adminUser->id,
            'lat' => -34.52920000,
            'lon' => -58.54980000,
            'ip' => '192.168.1.103',
            'port' => 8000,
            'user' => 'admin',
            'password' => bcrypt('admin'),
            'status' => 1,
        ]);

        $cam4 = Camera::create([
            'dvr_id' => $dvr4->id,
            'channel' => 1,
            'name' => 'Cerrito 3960 (Zona 25m - Escuela Técnica N°1)',
            'status' => 1,
        ]);

        $zone4 = CameraZone::create([
            'camera_id' => $cam4->id,
            'geometry' => [['x' => 10, 'y' => 10], ['x' => 500, 'y' => 10]],
            'total_length_meters' => 25.00,
            'homography_matrix' => null,
            'status' => 1,
        ]);

        OccupancyLog::create([
            'camera_zone_id' => $zone4->id,
            'detected_objects_coords' => [
                ['start' => 0.0, 'end' => 4.5],
                ['start' => 12.0, 'end' => 16.5],
            ],
        ]);

        // 5. DVR 5: Cerrito 4010 (Zona 20m - Esquina Paraná, Villa Adelina)
        $dvr5 = Dvr::create([
            'user_id' => $adminUser->id,
            'lat' => -34.52880000,
            'lon' => -58.55050000,
            'ip' => '192.168.1.104',
            'port' => 8000,
            'user' => 'admin',
            'password' => bcrypt('admin'),
            'status' => 1,
        ]);

        $cam5 = Camera::create([
            'dvr_id' => $dvr5->id,
            'channel' => 1,
            'name' => 'Cerrito 4010 (Zona 20m - Esquina Paraná)',
            'status' => 1,
        ]);

        $zone5 = CameraZone::create([
            'camera_id' => $cam5->id,
            'geometry' => [['x' => 10, 'y' => 10], ['x' => 500, 'y' => 10]],
            'total_length_meters' => 20.00,
            'homography_matrix' => null,
            'status' => 1,
        ]);

        OccupancyLog::create([
            'camera_zone_id' => $zone5->id,
            'detected_objects_coords' => [
                ['start' => 2.0, 'end' => 6.5],
            ],
        ]);
    }
}
