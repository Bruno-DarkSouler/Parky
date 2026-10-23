<?php

use App\Http\Controllers\AuthController;

// Redirect auth
Route::get('/auth/redirect', [AuthController::class, 'auth_redirect'])->name('auth.redirect');
Route::get('/callback', [AuthController::class, 'callback'])->name('auth.redirect');

// Welcome
Route::get('/', function (\Illuminate\Http\Request $request) {
    if (Auth::check()) {
        if (!$request->hasCookie('location_enabled')) {
            return redirect()->route('location');
        }
        return view('index');
    }
    return view('welcome');
})->name('welcome');

// Cameras view and DVR Connector proxy routes
use App\Http\Controllers\CameraController;

Route::get('/cameras', function () {
    return redirect()->route('app.share_camera');
})->name('cameras.index');
Route::post('/api/cameras/check-channels', [CameraController::class, 'checkChannels'])->name('cameras.check-channels');
Route::get('/api/cameras/get-frame', [CameraController::class, 'getFrame'])->name('cameras.get-frame');
Route::post('/api/cameras/save', [CameraController::class, 'saveCamera'])->name('cameras.save');
Route::get('/api/cameras/list', [CameraController::class, 'listCameras'])->name('cameras.list');
Route::delete('/api/cameras/{id}', [CameraController::class, 'deleteCamera'])->name('cameras.delete');


// Login
Route::post('/login', [AuthController::class, 'login'])->name('api.login');;
Route::get('/login', function () {
    if (Auth::check()) {
        return redirect()->route('location');
    }
    return view('login');
})->name('login');

// Register
Route::post('/register', [AuthController::class, 'register'])->name('api.register');
Route::get('/register', function () {
    if (Auth::check()) {
        return redirect()->route('location');
    }
    return view('register');
})->name('register');

// Auth routes
Route::middleware('auth')->group(function () {

    // Logout
    Route::get('/logout', [AuthController::class, 'logout'])->name('logout');

    // Location
    Route::get('/location', function () {
        return view('location');
    })->name('location');

    // Add Vehicle
    Route::get('/add-vehicle', function () {
        return view('add_vehicle');
    })->name('vehicle.add');

    Route::post('/add-vehicle', [AuthController::class, 'storeVehicle'])->name('vehicle.store');

    // Skip Vehicle
    Route::get('/skip-vehicle', function () {
        return redirect()->route('welcome')->withCookie(cookie('skip_vehicle_prompt', true, 525600)); // 1 year in minutes
    })->name('vehicle.skip');

    Route::get('/map', function () {
        return view('index');
    })->name('map');

    // App Mobile Views
    Route::prefix('app')->name('app.')->group(function () {
        Route::view('/menu', 'mobile.menu')->name('menu');
        Route::view('/profile', 'mobile.profile')->name('profile');
        Route::view('/vehicles', 'mobile.vehicles')->name('vehicles');
        Route::view('/history', 'mobile.history')->name('history');
        Route::view('/settings', 'mobile.settings')->name('settings');
        Route::get('/share-camera', [CameraController::class, 'index'])->name('share_camera');
    });

});
