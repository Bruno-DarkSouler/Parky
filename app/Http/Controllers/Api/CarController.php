<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Car;
use App\Models\User;

class CarController extends Controller
{
    public function index()
    {
        $user = auth('web')->user() ?? auth('api')->user() ?? request()->user();
        if (!$user) {
            $user = User::first();
        }

        $cars = $user ? $user->cars()->where('status', 1)->get() : Car::where('status', 1)->get();
        return response()->json($cars);
    }

    public function store(Request $request)
    {
        $user = auth('web')->user() ?? auth('api')->user() ?? request()->user();
        if (!$user) {
            $user = User::first() ?? User::create([
                'name' => 'Usuario Test',
                'email' => 'guest@parkingfinder.com',
                'password' => bcrypt('password'),
                'status' => 1,
            ]);
        }

        $request->validate([
            'plate' => 'required|string',
            'brand' => 'nullable|string',
            'model' => 'nullable|string',
            'year' => 'nullable|integer',
            'color' => 'nullable|string',
            'width_meters' => 'nullable|numeric|min:0.5',
            'length_meters' => 'nullable|numeric|min:1.0',
        ]);

        $length = $request->length_meters;
        if (!$length) {
            $searchStr = strtolower(($request->brand ?? '') . ' ' . ($request->model ?? ''));
            if (str_contains($searchStr, 'fitito') || str_contains($searchStr, '600')) {
                $length = 3.30;
            } else if (str_contains($searchStr, 'cybertruck')) {
                $length = 5.80;
            } else {
                $length = 4.50;
            }
        }

        // If plate exists for this user, update it or generate a unique suffix
        $plate = $request->plate;
        $existingCar = Car::where('plate', $plate)->first();
        if ($existingCar && $existingCar->user_id !== $user->id) {
            $plate = $plate . '-' . rand(100, 999);
        }

        $car = Car::updateOrCreate(
            ['user_id' => $user->id, 'plate' => $plate],
            [
                'brand' => $request->brand,
                'model' => $request->model,
                'year' => $request->year,
                'color' => $request->color,
                'width_meters' => $request->width_meters ?? 1.80,
                'length_meters' => $length,
                'status' => 1,
            ]
        );

        return response()->json($car, 201);
    }

    public function update(Request $request, $id)
    {
        $user = auth('web')->user() ?? auth('api')->user() ?? request()->user() ?? User::first();
        $car = Car::findOrFail($id);

        $request->validate([
            'plate' => 'sometimes|string',
            'brand' => 'nullable|string',
            'model' => 'nullable|string',
            'year' => 'nullable|integer',
            'color' => 'nullable|string',
            'width_meters' => 'sometimes|numeric|min:0.5',
            'length_meters' => 'sometimes|numeric|min:1.0',
        ]);

        $car->update($request->only(['plate', 'brand', 'model', 'year', 'color', 'width_meters', 'length_meters']));

        return response()->json($car);
    }

    public function destroy($id)
    {
        $car = Car::findOrFail($id);
        $car->update(['status' => 0]);
        
        return response()->json(['deleted' => true]);
    }
}
