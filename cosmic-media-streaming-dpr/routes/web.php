<?php

use App\Http\Controllers\CustomLayoutController;
use App\Http\Controllers\DisplayController;
use App\Http\Controllers\ImageController;
use App\Http\Controllers\LayoutPreviewController;
use App\Http\Controllers\MediaController;
use App\Http\Controllers\PdfController;
use App\Http\Controllers\RemoteDevicesController;
use App\Http\Controllers\TestController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::redirect('/', '/back-office');
Route::get('/layout/{id}', [LayoutPreviewController::class, 'show']);
Route::get('/schedule/{id}', [LayoutPreviewController::class, 'showSchedule'])->name('schedule-preview')->middleware('auth');
Route::get('/display/{token}', [DisplayController::class, 'show'])->name('display-token');

Route::get('/editor/{id}', [CustomLayoutController::class, 'index'])->name('editor');
Route::get('/custom-template/{id}', [CustomLayoutController::class, 'gridStack'])->name('custom-template');
Route::get('/preview/{id}', [CustomLayoutController::class, 'preview'])->name('preview');

Route::get('view-pdf', [PdfController::class, 'viewPDF']);
Route::get('generate-pdf', [PdfController::class, 'generatePDF']);
Route::get('jsPDF', [PdfController::class, 'jsPDF']);

Route::get('back-office/media-videos/createMediaVideo', [MediaController::class, 'createMediaVideo']);
// Route::post('storeCreateMediaVideo', [MediaController::class, 'storeCreateMediaVideo'])->name("storeCreateMediaVideos");
Route::get('back-office/media-videos/editMediaVideo/{id}', [MediaController::class, 'editMediaVideo']);
// Route::post('storeEditMediaVideo', [MediaController::class, 'storeEditMediaVideo'])->name("storeEditMediaVideos");
Route::get('downloadVideo/{id}', [MediaController::class, 'downloadVideo']);
Route::get('/image/{filename}', [ImageController::class, 'serveImage']);
Route::get('test', [TestController::class, 'index']);
Route::post('upload', [TestController::class, 'store'])->name('upload.store');
Route::post('upload', [MediaController::class, 'store'])->name('upload.store');
Route::post('refreshDisplaysByHtml', [DisplayController::class, 'refreshDisplaysByHtml'])->name("refreshDisplaysByHtml");
Route::get('/api/video/{path}', [MediaController::class, 'serveVideo'])->where('path', '.*');