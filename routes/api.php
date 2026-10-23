<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;    
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CarController;
use App\Http\Controllers\Api\IotController;
use App\Http\Controllers\Api\ParkingController;
use App\Http\Controllers\Api\AndroidAutoController;
use App\Http\Controllers\Api\CnnProController;

Route::prefix('v1')->group(function () {
    
    // Auth
    Route::prefix('auth')->group(function () {
        Route::post('register', [AuthController::class, 'register']);
        Route::post('login', [AuthController::class, 'login']);
        Route::post('refresh', [AuthController::class, 'refresh']);
        Route::post('logout', [AuthController::class, 'logout'])->middleware('auth:api');
    });

    // Cars
    Route::apiResource('cars', CarController::class);

    // CNN PRO Endpoints (Protegidos por Token)
    Route::prefix('cnn')->middleware('cnn.token')->group(function () {
        Route::get('cameras', [CnnProController::class, 'getCameras']);
        Route::post('availability', [CnnProController::class, 'updateAvailability']);
        Route::put('availability', [CnnProController::class, 'updateAvailability']);
    });

    // IoT Webhook
    Route::post('iot/webhook/camera-data', [IotController::class, 'cameraData'])
        ->middleware('camera.token');

    // Parking Availability
    Route::get('parking/available', [ParkingController::class, 'available']);

    // Android Auto
    Route::get('android-auto/nearby', [AndroidAutoController::class, 'nearby']);
});

// CNN PRO Endpoints (Direct alias routes protegidos por Token)
Route::prefix('cnn')->middleware('cnn.token')->group(function () {
    Route::get('cameras', [CnnProController::class, 'getCameras']);
    Route::post('availability', [CnnProController::class, 'updateAvailability']);
    Route::put('availability', [CnnProController::class, 'updateAvailability']);
});

