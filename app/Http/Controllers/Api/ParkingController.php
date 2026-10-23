<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\ParkingAvailabilityService;
use App\Models\Car;

class ParkingController extends Controller
{
    protected $parkingService;

    public function __construct(ParkingAvailabilityService $parkingService)
    {
        $this->parkingService = $parkingService;
    }

    public function available(Request $request)
    {
        $request->validate([
            'lat' => 'required|numeric',
            'lon' => 'nullable|numeric',
            'lng' => 'nullable|numeric',
            'car_id' => 'nullable|string',
            'radius_meters' => 'sometimes|numeric|min:50|max:100000'
        ]);

        $lon = $request->input('lon', $request->input('lng', 0));
        $radius = $request->input('radius_meters', 50000);
        
        $user = auth('api')->user() ?? auth('web')->user() ?? auth()->user();
        $car = null;

        if ($request->car_id) {
            $car = Car::find($request->car_id);
            if (!$car && $user) {
                $car = $user->cars()->find($request->car_id);
            }
        }
        
        if (!$car && $user) {
            $car = $user->cars()->where('status', 1)->first();
        }

        if (!$car) {
            $car = Car::where('status', 1)->first();
        }

        $results = $this->parkingService->getAvailableZones(
            (float) $request->lat,
            (float) $lon,
            $car,
            (int) $radius
        );

        return response()->json([
            'search_center' => [
                'lat' => (float) $request->lat,
                'lon' => (float) $lon
            ],
            'results' => $results
        ]);
    }
}
