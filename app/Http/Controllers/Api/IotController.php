<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\OccupancyLog;

class IotController extends Controller
{
    public function cameraData(Request $request)
    {
        $request->validate([
            'camera_zone_id' => 'required|uuid|exists:camera_zones,id',
            'detected_objects_coords' => 'required|array',
        ]);

        OccupancyLog::create([
            'camera_zone_id' => $request->camera_zone_id,
            'detected_objects_coords' => $request->detected_objects_coords,
        ]);

        return response()->json(['processed' => true]);
    }
}
