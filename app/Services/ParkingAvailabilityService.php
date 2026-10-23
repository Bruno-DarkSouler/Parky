<?php

namespace App\Services;

use App\Models\Car;
use App\Models\Dvr;
use App\Models\OccupancyLog;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;

class ParkingAvailabilityService
{
    /**
     * Resolve real street name or approximate location for a camera/dvr.
     */
    private function resolveStreetName($camera, $dvr): string
    {
        $rawName = $camera->name ?? '';
        $cleanLocation = trim(preg_replace('/\s*\(Zona.*?\)/i', '', $rawName));

        if (!empty($cleanLocation) && !preg_match('/^Cámara\s+Canal/i', $cleanLocation) && !preg_match('/^Cámara\s+\d+/i', $cleanLocation) && strtolower($cleanLocation) !== 'calle') {
            return $cleanLocation;
        }

        if ($dvr && $dvr->lat && $dvr->lon) {
            $latKey = round((float) $dvr->lat, 4);
            $lonKey = round((float) $dvr->lon, 4);
            $cacheKey = "street_name_{$latKey}_{$lonKey}";

            return Cache::remember($cacheKey, 86400, function () use ($dvr) {
                try {
                    $response = Http::withHeaders([
                        'User-Agent' => 'ParkingFinder/1.0'
                    ])->timeout(3)->get('https://nominatim.openstreetmap.org/reverse', [
                        'format' => 'json',
                        'lat' => $dvr->lat,
                        'lon' => $dvr->lon
                    ]);

                    if ($response->successful()) {
                        $data = $response->json();
                        $address = $data['address'] ?? [];
                        $road = $address['road'] ?? $address['pedestrian'] ?? $address['suburb'] ?? null;
                        $houseNumber = $address['house_number'] ?? '';

                        if ($road) {
                            return trim("{$road} {$houseNumber}");
                        }
                    }
                } catch (\Throwable $e) {
                    // Fallback
                }

                return 'Calle cercana';
            });
        }

        return !empty($cleanLocation) ? $cleanLocation : 'Calle cercana';
    }

    /**
     * Get available parking zones for a specific car near a location.
     *
     * @param float $lat
     * @param float $lon
     * @param Car|null $car
     * @param int $radius Radius in meters (default 50000)
     * @return array
     */
    public function getAvailableZones(float $lat, float $lon, ?Car $car = null, int $radius = 50000): array
    {
        $carLength = $car ? (float)$car->length_meters : 4.50;
        $carName = $car ? (($car->brand ? $car->brand . ' ' : '') . ($car->model ?? 'Vehículo')) : 'Vehículo estándar';
        $marginMeters = 1.0; // 0.5m front + 0.5m back
        $requiredSpace = $carLength + $marginMeters;

        $nearbyDvrs = [];
        try {
            $sql = "
                SELECT *, (6371000 * acos(cos(radians(?)) * cos(radians(lat)) * cos(radians(lon) - radians(?)) + sin(radians(?)) * sin(radians(lat)))) AS distance_meters
                FROM dvrs
                WHERE status = 1
            ";
            
            $dvrsData = DB::select("SELECT * FROM ($sql) AS dvr_distances WHERE distance_meters <= ? ORDER BY distance_meters ASC", [$lat, $lon, $lat, $radius]);
            $nearbyDvrs = Dvr::hydrate($dvrsData);
        } catch (\Throwable $e) {
            $nearbyDvrs = Dvr::where('status', 1)->get();
        }

        if ($nearbyDvrs->isEmpty()) {
            $nearbyDvrs = Dvr::where('status', 1)->get();
        }

        $results = [];

        foreach ($nearbyDvrs as $dvr) {
            foreach ($dvr->cameras()->where('status', 1)->get() as $camera) {
                foreach ($camera->cameraZones()->where('status', 1)->get() as $zone) {
                    
                    $lastLog = OccupancyLog::where('camera_zone_id', $zone->id)
                        ->latest('created_at')
                        ->first();
                        
                    $detectedCoords = ($lastLog && is_array($lastLog->detected_objects_coords))
                        ? $lastLog->detected_objects_coords
                        : [];

                    $analysis = $this->calculateAvailableSlotsDetails(
                        (float)$zone->total_length_meters,
                        $detectedCoords,
                        $requiredSpace
                    );

                    $cleanLocation = $this->resolveStreetName($camera, $dvr);

                    $lastVerifiedAt = 'Sin verificaciones aún';
                    if ($lastLog && $lastLog->created_at) {
                        try {
                            $lastVerifiedAt = $lastLog->created_at->locale('es')->diffForHumans();
                        } catch (\Throwable $e) {
                            $lastVerifiedAt = 'Sin verificaciones aún';
                        }
                    }

                    $results[] = [
                        'zone_id' => (string) $zone->id,
                        'name' => $camera->name ?? 'Zona de Estacionamiento',
                        'street_name' => $cleanLocation,
                        'location_approximate' => $cleanLocation,
                        'last_verified_at' => $lastVerifiedAt,
                        'distance_meters' => round($dvr->distance_meters ?? 100),
                        'available_slots' => $analysis['available_slots'],
                        'available_slots_for_this_car' => $analysis['available_slots'],
                        'status' => $analysis['available_slots'] > 0 ? 'available' : 'full',
                        'geometry_info' => [
                            'total_length' => (float) $zone->total_length_meters,
                            'occupied_objects' => $detectedCoords,
                            'free_gaps' => $analysis['free_gaps']
                        ],
                        'car_info' => [
                            'id' => $car ? $car->id : null,
                            'model' => $carName,
                            'length_meters' => $carLength,
                            'required_space' => $requiredSpace
                        ],
                        'coordinates' => [
                            'lat' => (float) $dvr->lat,
                            'lon' => (float) $dvr->lon
                        ],
                        'lat' => (float) $dvr->lat,
                        'lng' => (float) $dvr->lon
                    ];
                }
            }
        }

        return $results;
    }

    public function calculateAvailableSlotsDetails(float $totalLength, array $detectedObjects, float $requiredSpace): array
    {
        usort($detectedObjects, function($a, $b) {
            return ((float)($a['start'] ?? 0)) <=> ((float)($b['start'] ?? 0));
        });

        $availableSlots = 0;
        $currentPos = 0.0;
        $freeGaps = [];

        foreach ($detectedObjects as $object) {
            $start = (float)($object['start'] ?? 0);
            $end = (float)($object['end'] ?? 0);
            
            $gap = $start - $currentPos;
            
            if ($gap > 0.1) {
                $slotsInGap = $gap >= $requiredSpace ? (int) floor($gap / $requiredSpace) : 0;
                $availableSlots += $slotsInGap;
                $freeGaps[] = [
                    'start' => round($currentPos, 2),
                    'end' => round($start, 2),
                    'length' => round($gap, 2),
                    'fits' => $gap >= $requiredSpace,
                    'slots' => $slotsInGap
                ];
            }
            
            $currentPos = max($currentPos, $end);
        }

        $finalGap = $totalLength - $currentPos;
        if ($finalGap > 0.1) {
            $slotsInGap = $finalGap >= $requiredSpace ? (int) floor($finalGap / $requiredSpace) : 0;
            $availableSlots += $slotsInGap;
            $freeGaps[] = [
                'start' => round($currentPos, 2),
                'end' => round($totalLength, 2),
                'length' => round($finalGap, 2),
                'fits' => $finalGap >= $requiredSpace,
                'slots' => $slotsInGap
            ];
        }

        return [
            'available_slots' => $availableSlots,
            'free_gaps' => $freeGaps
        ];
    }
}
