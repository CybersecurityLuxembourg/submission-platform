<?php

use App\Http\Controllers\Api\ApiTokenController;
use App\Http\Controllers\Api\FormAccessController;
use App\Http\Controllers\Api\FormController;
use App\Http\Controllers\Api\SubmissionController;
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

// API routes - protected by sanctum auth and custom IP validation
Route::middleware(['auth:sanctum', 'api.token.ip'])->prefix('v1')->group(function () {
    // API Token Management
    Route::apiResource('tokens', ApiTokenController::class)->except(['show']);
    
    // Form Management
    Route::apiResource('forms', FormController::class);
    
    // Form Access Links
    Route::get('/forms/{form}/access-links', [FormAccessController::class, 'index']);
    Route::post('/forms/{form}/access-links', [FormAccessController::class, 'store']);
    Route::get('/forms/{form}/access-links/{accessLink}', [FormAccessController::class, 'show']);
    Route::put('/forms/{form}/access-links/{accessLink}', [FormAccessController::class, 'update']);
    Route::delete('/forms/{form}/access-links/{accessLink}', [FormAccessController::class, 'destroy']);
    
    // Submissions
    Route::get('/forms/{form}/submissions', [SubmissionController::class, 'index']);
    Route::post('/forms/{form}/submissions', [SubmissionController::class, 'store']);
    Route::get('/forms/{form}/submissions/{submission}', [SubmissionController::class, 'show']);
    Route::put('/forms/{form}/submissions/{submission}', [SubmissionController::class, 'update']);
    Route::delete('/forms/{form}/submissions/{submission}', [SubmissionController::class, 'destroy']);
});
