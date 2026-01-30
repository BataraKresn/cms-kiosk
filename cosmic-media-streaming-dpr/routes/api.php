<?php

use App\Http\Controllers\API\ChartController;
use App\Http\Controllers\API\CustomLayoutController;
use App\Http\Controllers\API\DeviceController;
use App\Http\Controllers\API\LayoutController;
use App\Http\Controllers\API\MediaHLSController;
use App\Http\Controllers\API\MediaHTMLController;
use App\Http\Controllers\API\MediaImageController;
use App\Http\Controllers\API\MediaLiveURLController;
use App\Http\Controllers\API\MediaVideoController;
use App\Http\Controllers\Api\DeviceRegistrationController;
use App\Http\Controllers\DeviceSchedulerController;
use App\Http\Controllers\DisplayController;
use App\Http\Controllers\ImageController;
use App\Http\Controllers\MediaController;
use App\Http\Controllers\VideoController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

// Health check endpoint for Docker
Route::get('/health', function () {
    return response()->json([
        'status' => 'healthy',
        'timestamp' => now()->toIso8601String(),
    ]);
});

Route::post('/new_connection_device', [DeviceController::class, 'new_connection']);

Route::post('/save_change/{id}', [CustomLayoutController::class, 'save_change'])->name('api.custom-layout.save_change');
Route::get('/load_data/{id}', [CustomLayoutController::class, 'load_data'])->name('api.custom-layout.load_data');

// MEDIA IMAGE
Route::post('/media_image/create', [MediaImageController::class, 'create']);
Route::post('/media_image/edit/{id}', [MediaImageController::class, 'edit']);
Route::get('/media_image/detail/{id}', [MediaImageController::class, 'detail']);
Route::delete('/media_image/delete/{id}', [MediaImageController::class, 'delete']);

// MEDIA VIDEO
Route::post('/media_video/create', [MediaVideoController::class, 'create']);
Route::post('/media_video/edit/{id}', [MediaVideoController::class, 'edit']);
Route::get('/media_video/detail/{id}', [MediaVideoController::class, 'detail']);
Route::delete('/media_video/delete/{id}', [MediaVideoController::class, 'delete']);

// MEDIA HTML
Route::post('/media_html/create', [MediaHTMLController::class, 'create']);
Route::post('/media_html/edit/{id}', [MediaHTMLController::class, 'edit']);
Route::get('/media_html/detail/{id}', [MediaHTMLController::class, 'detail']);
Route::delete('/media_html/delete/{id}', [MediaHTMLController::class, 'delete']);

// MEDIA HLS
Route::post('/media_hls/create', [MediaHLSController::class, 'create']);
Route::post('/media_hls/edit/{id}', [MediaHLSController::class, 'edit']);
Route::get('/media_hls/detail/{id}', [MediaHLSController::class, 'detail']);
Route::delete('/media_hls/delete/{id}', [MediaHLSController::class, 'delete']);

// MEDIA LIVE URL
Route::post('/media_live_url/create', [MediaLiveURLController::class, 'create']);
Route::post('/media_live_url/edit/{id}', [MediaLiveURLController::class, 'edit']);
Route::get('/media_live_url/detail/{id}', [MediaLiveURLController::class, 'detail']);
Route::delete('/media_live_url/delete/{id}', [MediaLiveURLController::class, 'delete']);

// INSERT SPOTS
Route::post('/spots/create/{id} ', [LayoutController::class, 'create']);
Route::delete('/spots/delete/{id} ', [LayoutController::class, 'delete']);

// API CHART 
Route::get('/chart', [ChartController::class, 'device_status']);

Route::post('/storeCreateMediaVideo', [MediaController::class, 'storeCreateMediaVideo']);

Route::get('/video/{filename}', [VideoController::class, 'streamVideo']);
Route::get('/image/{filename}', [ImageController::class, 'serveImage']);

Route::post('storeCreateMediaVideo', [MediaController::class, 'storeCreateMediaVideo'])->name("storeCreateMediaVideos");
Route::post('storeEditMediaVideo', [MediaController::class, 'storeEditMediaVideo'])->name("storeEditMediaVideos");
Route::get('checkVideoName', [MediaController::class, 'checkVideoName'])->name("checkVideoName");

Route::post('refreshDisplaysByVideo', [DisplayController::class, 'refreshDisplaysByVideo'])->name("refreshDisplaysByVideos");
Route::post('refreshDisplaysByLiveUrl', [DisplayController::class, 'refreshDisplaysByLiveUrl'])->name("refreshDisplaysByLiveUrl");


// Optimized ping endpoint for connection testing (minimal server impact)
Route::get('/ping', function () {
    return response('', 204)->header('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0');
});

// Add a network speed test endpoint
Route::get('/network-test', function () {
    // Generate a small random payload (10KB) to test network speed
    $data = str_repeat('X', 10 * 1024);
    return response($data)
        ->header('Content-Type', 'text/plain')
        ->header('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0');
});

// ============================================================================
// Device Auto-Registration API Routes
// ============================================================================
// These endpoints allow Android devices to register themselves automatically
// when the APK is installed, eliminating manual device creation in CMS

// Register new device (called once on APK first launch)
Route::post('/devices/register', [DeviceRegistrationController::class, 'register']);

// Device heartbeat to maintain online status (called every 30 seconds)
Route::post('/devices/heartbeat', [DeviceRegistrationController::class, 'heartbeat']);

// Unregister device (on APK uninstall or reset)
Route::delete('/devices/unregister', [DeviceRegistrationController::class, 'unregister']);

// Get list of available displays (for APK display selection)
Route::get('/displays', [DeviceRegistrationController::class, 'getDisplays']);