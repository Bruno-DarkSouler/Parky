<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\ParkingAvailabilityService;

class AndroidAutoController extends Controller
{
    protected $parkingService;

    public function __construct(ParkingAvailabilityService $parkingService)
    {
        $this->parkingService = $parkingService;
    }

    public function nearby(Request $request)
    {
        $request->validate([
            'lat' => 'required|numeric',
            'lon' => 'required|numeric',
            'car_id' => 'required|uuid',
        ]);

        $car = auth('api')->user()->cars()->where('id', $request->car_id)->firstOrFail();
        
        $results = $this->parkingService->getAvailableZones(
            (float) $request->lat,
            (float) $request->lon,
            $car,
            500
        );

        // Minimalist payload (< 2KB)
        $minimalResults = array_map(function($item) {
            return [
                'title' => $item['street_name'],
                'slots' => $item['available_slots_for_this_car'],
                'lat' => $item['coordinates']['lat'],
                'lon' => $item['coordinates']['lon']
            ];
        }, $results);

        return response()->json($minimalResults);
    }
}
