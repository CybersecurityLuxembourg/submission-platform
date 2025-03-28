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

// Apply general API rate limiting to all API routes
Route::middleware(['throttle:api'])->group(function () {
    
    // Custom token authentication middleware with IP validation
    // Apply specific rate limiting for authenticated API routes
    Route::middleware(['api.token.ip'])->prefix('v1')->name('api.')->group(function () {
        // API Token Management (standard API rate limit)
        Route::apiResource('tokens', ApiTokenController::class)->except(['show']);
        
        // Form Management (standard API rate limit)
        Route::apiResource('forms', FormController::class);
        
        // Form Access Links (standard API rate limit)
        Route::get('/forms/{form}/access-links', [FormAccessController::class, 'index'])
            ->name('forms.access_links.index');
        Route::post('/forms/{form}/access-links', [FormAccessController::class, 'store'])
            ->name('forms.access_links.store');
        Route::get('/forms/{form}/access-links/{accessLink}', [FormAccessController::class, 'show'])
            ->name('forms.access_links.show');
        Route::put('/forms/{form}/access-links/{accessLink}', [FormAccessController::class, 'update'])
            ->name('forms.access_links.update');
        Route::delete('/forms/{form}/access-links/{accessLink}', [FormAccessController::class, 'destroy'])
            ->name('forms.access_links.destroy');
        
        // Submissions with specific submission rate limits
        Route::middleware(['throttle:api-submissions'])->group(function () {
            Route::get('/forms/{form}/submissions', [SubmissionController::class, 'index'])
                ->name('forms.submissions.index');
            Route::post('/forms/{form}/submissions', [SubmissionController::class, 'store'])
                ->name('forms.submissions.store');
            Route::get('/forms/{form}/submissions/{submission}', [SubmissionController::class, 'show'])
                ->name('forms.submissions.show');
            Route::put('/forms/{form}/submissions/{submission}', [SubmissionController::class, 'update'])
                ->name('forms.submissions.update');
            Route::delete('/forms/{form}/submissions/{submission}', [SubmissionController::class, 'destroy'])
                ->name('forms.submissions.destroy');
        });
    });
});
