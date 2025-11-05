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

// User profile endpoint
Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

// API v1 Routes
Route::middleware(['throttle:api', 'api.token.ip'])
    ->prefix('v1')
    ->name('api.')
    ->group(function () {
        // API Token Management
        Route::apiResource('tokens', ApiTokenController::class)
            ->except(['show']);

        // Form Management
        Route::apiResource('forms', FormController::class);

        // Form Access Links
        Route::apiResource('form.access-links', FormAccessController::class);

        // Form Submissions (with specific rate limiting)
        Route::middleware(['throttle:api-submissions'])
            ->group(function () {
                Route::apiResource('forms.submissions', SubmissionController::class);
            });
    });
