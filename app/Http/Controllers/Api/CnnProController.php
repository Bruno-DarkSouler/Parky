<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Camera;
use App\Models\CameraZone;
use App\Models\OccupancyLog;
use App\Services\ParkingAvailabilityService;
use Illuminate\Support\Facades\Log;

class CnnProController extends Controller
{
    /**
     * Obtener cámaras paginadas de a 10 para CNN PRO.
     * Endpoint: GET /api/v1/cnn/cameras o GET /api/cnn/cameras
     */
    public function getCameras(Request $request)
    {
        $perPage = (int) $request->input('per_page', 10);
        if ($perPage <= 0) {
            $perPage = 10;
        }

        $cameras = Camera::with(['dvr', 'cameraZones.occupancyLogs' => function ($query) {
            $query->latest('created_at')->limit(1);
        }])
        ->orderBy('created_at', 'desc')
        ->paginate($perPage);

        return response()->json([
            'success' => true,
            'message' => 'Cámaras obtenidas exitosamente.',
            'data' => $cameras->items(),
            'pagination' => [
                'current_page' => $cameras->currentPage(),
                'last_page' => $cameras->lastPage(),
                'per_page' => $cameras->perPage(),
                'total' => $cameras->total(),
                'has_more_pages' => $cameras->hasMorePages(),
                'next_page_url' => $cameras->nextPageUrl(),
                'prev_page_url' => $cameras->previousPageUrl(),
            ]
        ], 200);
    }

    /**
     * Actualizar disponibilidad del estacionamiento para CNN PRO.
     * Endpoint: POST /api/v1/cnn/availability o POST /api/cnn/availability
     */
    public function updateAvailability(Request $request)
    {
        $request->validate([
            'camera_id' => 'nullable|uuid|exists:cameras,id',
            'camera_zone_id' => 'nullable|uuid|exists:camera_zones,id',
            'zone_id' => 'nullable|string',
            'is_available' => 'nullable|boolean',
            'available' => 'nullable|boolean',
            'is_free' => 'nullable|boolean',
            'status' => 'nullable',
            'detected_objects_coords' => 'nullable|array',
        ]);

        $zoneId = $request->input('camera_zone_id') ?: $request->input('zone_id');
        $cameraId = $request->input('camera_id');

        $cameraZone = null;

        if ($zoneId) {
            $cameraZone = CameraZone::find($zoneId);
        }

        if (!$cameraZone && $cameraId) {
            $camera = Camera::find($cameraId);
            if ($camera) {
                $cameraZone = $camera->cameraZones()->first();
                if (!$cameraZone) {
                    $cameraZone = CameraZone::create([
                        'camera_id' => $camera->id,
                        'geometry' => null,
                        'total_length_meters' => 20.00,
                        'status' => 1,
                    ]);
                }
            }
        }

        if (!$cameraZone) {
            return response()->json([
                'success' => false,
                'message' => 'No se encontró la zona o cámara especificada. Proporcione camera_id o camera_zone_id válido.'
            ], 422);
        }

        // Determinar disponibilidad
        $isAvailable = null;
        if ($request->has('is_available')) {
            $isAvailable = $request->boolean('is_available');
        } elseif ($request->has('available')) {
            $isAvailable = $request->boolean('available');
        } elseif ($request->has('is_free')) {
            $isAvailable = $request->boolean('is_free');
        } elseif ($request->has('status')) {
            $statusVal = strtolower((string)$request->input('status'));
            if (in_array($statusVal, ['1', 'true', 'available', 'disponible', 'free'])) {
                $isAvailable = true;
            } elseif (in_array($statusVal, ['0', 'false', 'full', 'occupied', 'ocupado', 'no_disponible'])) {
                $isAvailable = false;
            }
        }

        $detectedCoords = $request->input('detected_objects_coords');

        if ($detectedCoords === null && $isAvailable !== null) {
            if ($isAvailable) {
                $detectedCoords = [];
            } else {
                $totalLen = (float) ($cameraZone->total_length_meters ?: 20.0);
                $detectedCoords = [
                    ['start' => 0.0, 'end' => $totalLen]
                ];
            }
        }

        if ($detectedCoords === null) {
            $detectedCoords = [];
        }

        if ($isAvailable === null) {
            $parkingService = app(ParkingAvailabilityService::class);
            $analysis = $parkingService->calculateAvailableSlotsDetails(
                (float) ($cameraZone->total_length_meters ?: 20.0),
                $detectedCoords,
                3.5 // Margen mínimo para estacionar (longitud auto + maniobra)
            );
            $isAvailable = $analysis['available_slots'] > 0 || count($detectedCoords) === 0;
        }

        // Actualizar estado de la zona (1: disponible/activo, 0: no disponible/lleno)
        $cameraZone->status = $isAvailable ? 1 : 0;
        $cameraZone->save();

        if ($cameraZone->camera) {
            $cameraZone->camera->status = $isAvailable ? 1 : 0;
            $cameraZone->camera->save();
        }

        // Guardar log de ocupación
        $occupancyLog = OccupancyLog::create([
            'camera_zone_id' => $cameraZone->id,
            'detected_objects_coords' => $detectedCoords,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Estado de disponibilidad actualizado correctamente para CNN PRO.',
            'data' => [
                'camera_id' => $cameraZone->camera_id,
                'camera_zone_id' => $cameraZone->id,
                'is_available' => $isAvailable,
                'status' => $isAvailable ? 'available' : 'full',
                'zone_status' => $cameraZone->status,
                'total_length_meters' => (float)$cameraZone->total_length_meters,
                'detected_objects_coords' => $detectedCoords,
                'updated_at' => now()->toIso8601String(),
            ]
        ], 200);
    }
}
