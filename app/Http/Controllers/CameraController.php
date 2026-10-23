<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class CameraController extends Controller
{
    /**
     * Get DVR Connector Base URL
     */
    private function getDvrConnectorUrl(): string
    {
        return env('DVR_CONNECTOR_URL', 'http://dvr_connector:8000');
    }

    /**
     * Fallback DVR Connector Base URL (for direct host access when Docker alias isn't resolved)
     */
    private function getDvrConnectorFallbackUrl(): string
    {
        return env('DVR_CONNECTOR_FALLBACK_URL', 'http://127.0.0.1:8001');
    }

    /**
     * Render main cameras management view
     */
    public function index()
    {
        return view('mobile.share_camera');
    }

    /**
     * Step 1: Check available channels on DVR via DVR_CONNECTOR microservice
     */
    public function checkChannels(Request $request)
    {
        $request->validate([
            'ip' => 'required|string',
            'port' => 'nullable|integer',
            'user' => 'nullable|string',
            'password' => 'nullable|string',
        ]);

        $ip = trim($request->input('ip'));
        $port = (int) ($request->input('port') ?: 80);
        $user = trim($request->input('user', ''));
        $password = trim($request->input('password', ''));
        $debug = strtolower($ip) === 'debug' || $request->boolean('debug');

        $payload = [
            'ip' => $ip,
            'port' => $port,
            'usuario' => $user,
            'contraseña' => $password,
            'user' => $user,
            'password' => $password,
            'debug' => $debug,
        ];

        // 1. Try primary URL (http://dvr_connector:8000/getChannels)
        $baseUrl = $this->getDvrConnectorUrl();
        $fallbackUrl = $this->getDvrConnectorFallbackUrl();

        $response = null;
        $errorDetail = null;

        try {
            $response = Http::timeout(8)->post("{$baseUrl}/getChannels", $payload);
            if (!$response->successful()) {
                // Try GET method if POST fails
                $response = Http::timeout(8)->get("{$baseUrl}/getChannels", $payload);
            }
        } catch (\Exception $e) {
            Log::warning("DVR primary connector failed ({$baseUrl}): " . $e->getMessage());
        }

        // 2. Try fallback URL if primary failed
        if (!$response || !$response->successful()) {
            try {
                $response = Http::timeout(8)->post("{$fallbackUrl}/getChannels", $payload);
                if (!$response->successful()) {
                    $response = Http::timeout(8)->get("{$fallbackUrl}/getChannels", $payload);
                }
            } catch (\Exception $e) {
                Log::error("DVR fallback connector failed ({$fallbackUrl}): " . $e->getMessage());
                $errorDetail = $e->getMessage();
            }
        }

        if ($response && $response->successful()) {
            $channels = $response->json();
            if (is_array($channels) && count($channels) > 0) {
                return response()->json([
                    'success' => true,
                    'channels' => $channels,
                    'message' => 'Canales recuperados con éxito'
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'El DVR respondió pero no se encontraron canales de video disponibles.'
                ], 422);
            }
        }

        $statusCode = $response ? $response->status() : 504;
        $msg = 'No se pudo establecer conexión con el DVR. Por favor verifique que la IP/Dominio, Puerto, Usuario y Contraseña sean correctos y el DVR esté encendido.';
        if ($debug) {
            $msg .= " (Modo Debug activo, error: " . ($errorDetail ?? 'Connection error') . ")";
        }

        return response()->json([
            'success' => false,
            'message' => $msg
        ], $statusCode >= 400 && $statusCode < 600 ? $statusCode : 504);
    }

    /**
     * Step 2: Fetch camera frame JPEG via DVR_CONNECTOR microservice
     */
    public function getFrame(Request $request)
    {
        $ip = trim($request->input('ip', 'debug'));
        $port = (int) ($request->input('port') ?: 80);
        $user = trim($request->input('user', ''));
        $password = trim($request->input('password', ''));
        $channel = (int) ($request->input('channel') ?: 1);
        $debug = strtolower($ip) === 'debug' || $request->boolean('debug');

        $params = [
            'ip' => $ip,
            'port' => $port,
            'usuario' => $user,
            'contraseña' => $password,
            'user' => $user,
            'password' => $password,
            'canal' => $channel,
            'channel' => $channel,
            'debug' => $debug,
        ];

        $baseUrl = $this->getDvrConnectorUrl();
        $fallbackUrl = $this->getDvrConnectorFallbackUrl();

        $response = null;

        try {
            $response = Http::timeout(10)->get("{$baseUrl}/getFrame", $params);
        } catch (\Exception $e) {
            Log::warning("DVR frame fetch failed on primary ({$baseUrl}): " . $e->getMessage());
        }

        if (!$response || !$response->successful()) {
            try {
                $response = Http::timeout(10)->get("{$fallbackUrl}/getFrame", $params);
            } catch (\Exception $e) {
                Log::error("DVR frame fetch failed on fallback ({$fallbackUrl}): " . $e->getMessage());
            }
        }

        if ($response && $response->successful()) {
            return response($response->body(), 200)
                ->header('Content-Type', 'image/jpeg')
                ->header('Cache-Control', 'no-cache, no-store, must-revalidate');
        }

        // Default SVG fallback image if frame cannot be retrieved
        $svg = '<svg xmlns="http://www.w3.org/2000/svg" width="640" height="480" viewBox="0 0 640 480" fill="#1e293b">
            <rect width="640" height="480" fill="#0f172a"/>
            <text x="50%" y="45%" dominant-baseline="middle" text-anchor="middle" fill="#ef4444" font-family="sans-serif" font-size="20" font-weight="bold">No se pudo cargar la imagen de la cámara</text>
            <text x="50%" y="55%" dominant-baseline="middle" text-anchor="middle" fill="#94a3b8" font-family="sans-serif" font-size="14">Verifique la conexión al canal ' . $channel . ' en IP ' . htmlspecialchars($ip) . '</text>
        </svg>';

        return response($svg, 200)->header('Content-Type', 'image/svg+xml');
    }

    /**
     * Save DVR, Camera and Zones to Database
     */
    public function saveCamera(Request $request)
    {
        $request->validate([
            'ip' => 'required|string',
            'channel' => 'required',
            'zones' => 'nullable|array',
        ]);

        try {
            $user = auth()->user();
            $userId = $user ? $user->id : null;

            // If user is null, find first user or fallback for dev mode
            if (!$userId) {
                $firstUser = \App\Models\User::first();
                $userId = $firstUser ? $firstUser->id : null;
            }

            if (!$userId) {
                return response()->json([
                    'success' => false,
                    'message' => 'No se encontró un usuario válido para asociar el DVR.'
                ], 401);
            }

            // Create or find DVR
            $lat = $request->input('lat') !== null ? (float) $request->input('lat') : -34.603722;
            $lon = $request->input('lon') !== null ? (float) $request->input('lon') : -58.381592;

            $dvr = \App\Models\Dvr::create([
                'user_id' => $userId,
                'lat' => $lat,
                'lon' => $lon,
                'ip' => $request->input('ip'),
                'port' => (int) ($request->input('port') ?: 80),
                'user' => $request->input('user'),
                'password' => $request->input('password'),
                'status' => 1,
            ]);

            // Create Camera
            $camera = \App\Models\Camera::create([
                'dvr_id' => $dvr->id,
                'channel' => (int) $request->input('channel', 1),
                'name' => $request->input('name', 'Cámara Canal ' . $request->input('channel')),
                'status' => 1,
            ]);

            // Create Zones
            $zones = $request->input('zones', []);
            $savedZones = [];

            foreach ($zones as $z) {
                $geometry = $z['geometry'] ?? null;
                $meters = (float) ($z['total_length_meters'] ?? $z['meters'] ?? 2.5);

                $zoneRecord = \App\Models\CameraZone::create([
                    'camera_id' => $camera->id,
                    'geometry' => $geometry,
                    'total_length_meters' => $meters,
                    'status' => 1,
                ]);

                $savedZones[] = $zoneRecord;
            }

            return response()->json([
                'success' => true,
                'message' => 'Cámara y zonas guardadas exitosamente.',
                'dvr_id' => $dvr->id,
                'camera_id' => $camera->id,
                'saved_zones' => count($savedZones),
            ]);

        } catch (\Exception $e) {
            Log::error("Error saving camera: " . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al guardar en la base de datos: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * List all cameras with their DVR and zones
     */
    public function listCameras()
    {
        try {
            $user = auth()->user();
            $userId = $user ? $user->id : null;
            if (!$userId) {
                $firstUser = \App\Models\User::first();
                $userId = $firstUser ? $firstUser->id : null;
            }

            $cameras = \App\Models\Camera::with(['dvr', 'cameraZones'])
                ->when($userId, function($q) use ($userId) {
                    $q->whereHas('dvr', function($dvrQ) use ($userId) {
                        $dvrQ->where('user_id', $userId);
                    });
                })
                ->orderBy('created_at', 'desc')
                ->get();

            return response()->json([
                'success' => true,
                'cameras' => $cameras
            ]);
        } catch (\Exception $e) {
            Log::error("Error listing cameras: " . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener la lista de cámaras: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Delete a camera and its associated zones
     */
    public function deleteCamera($id)
    {
        try {
            $camera = \App\Models\Camera::with('cameraZones')->find($id);
            if (!$camera) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cámara no encontrada.'
                ], 404);
            }

            // Delete zones
            foreach ($camera->cameraZones as $zone) {
                $zone->delete();
            }

            $dvrId = $camera->dvr_id;
            $camera->delete();

            // Clean up empty DVR if no other camera uses it
            if ($dvrId && \App\Models\Camera::where('dvr_id', $dvrId)->count() === 0) {
                \App\Models\Dvr::where('id', $dvrId)->delete();
            }

            return response()->json([
                'success' => true,
                'message' => 'Cámara y sus zonas fueron eliminadas exitosamente.'
            ]);
        } catch (\Exception $e) {
            Log::error("Error deleting camera {$id}: " . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al eliminar la cámara: ' . $e->getMessage()
            ], 500);
        }
    }
}
