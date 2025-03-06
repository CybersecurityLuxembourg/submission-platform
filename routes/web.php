<?php

use App\Http\Controllers\FormAccessController;
use App\Http\Controllers\FormController;
use App\Http\Controllers\FormFieldController;
use App\Http\Controllers\SubmissionController;
use App\Http\Controllers\SubmissionExportController;
use App\Http\Controllers\WorkflowController;
use App\Http\Middleware\FormAccessMiddleware;
use Illuminate\Support\Facades\Route;


Route::get('/', [FormController::class, 'index'])
    ->name('homepage')
    ->middleware(FormAccessMiddleware::class);

Route::get('/forms', [FormController::class, 'publicIndex'])
    ->name('forms.public_index')
    ->middleware(FormAccessMiddleware::class);

Route::get('/forms/access/{token}', [FormAccessController::class, 'accessForm'])
    ->name('form.access')
    ->middleware(FormAccessMiddleware::class);
Route::get('/forms/{form}/submit', [SubmissionController::class, 'show'])->name('submissions.create');
Route::get('/thank-you', [SubmissionController::class, 'thankyou'])->name('submissions.thankyou');


Route::middleware([
    'auth:sanctum',
    config('jetstream.auth_session'),
    'verified',
])->group(function () {
    Route::get('/dashboard', function () {
        return view('dashboard');
    })->name('dashboard');

    Route::get('/dashboard', fn() => view('dashboard'))->name('dashboard');

    // Form Management
    Route::prefix('forms')->name('forms.')->group(function () {
        Route::get('/my-forms', [FormController::class, 'userIndex'])->name('user_index');
        Route::get('/create', [FormController::class, 'create'])->name('create');
        Route::post('/', [FormController::class, 'store'])->name('store');
        Route::get('/{form}/edit', [FormController::class, 'edit'])->name('edit');
        Route::get('/{form}', [FormController::class, 'preview'])->name('show');
        Route::put('/{form}', [FormController::class, 'update'])->name('update');
        Route::delete('/{form}', [FormController::class, 'destroy'])->name('destroy');
        Route::get('/{form}/preview', [FormController::class, 'preview'])->name('preview');

        // Form Access Management
        Route::post('/{form}/assign-users', [FormAccessController::class, 'assignUsers'])->name('assign-users');
        Route::post('/{form}/create-access-link', [FormAccessController::class, 'createAccessLink'])->name('create-access-link');
        Route::delete('/access-links/{accessLink}', [FormAccessController::class, 'deleteAccessLink'])->name('delete-access-link');

        // Form Field Management
        Route::prefix('{form}/fields')->name('fields.')->group(function () {
            Route::post('/', [FormFieldController::class, 'store'])->name('store');
            Route::put('/{field}', [FormFieldController::class, 'update'])->name('update');
            Route::delete('/{field}', [FormFieldController::class, 'destroy'])->name('destroy');
        });

        // Workflow Management
        Route::prefix('{form}/workflows')->name('workflows.')->group(function () {
            Route::get('/manage', [WorkflowController::class, 'manage'])->name('manage');
            Route::get('/{workflow}', [WorkflowController::class, 'show'])->name('show');
            Route::delete('/steps/{step}', [WorkflowController::class, 'destroyStep'])->name('steps.destroy');
            Route::delete('/{workflow}', [WorkflowController::class, 'destroy'])->name('destroy');
        });

        // User Removal from Form
        Route::delete('/{form}/users/{user}', [FormController::class, 'removeUser'])->name('remove-user');
    });
    // Submission Management
    Route::prefix('submissions')->name('submissions.')->group(function () {
        Route::get('/my-submissions', [SubmissionController::class, 'showUserSubmission'])->name('user');
        Route::delete('/{submission}', [SubmissionController::class, 'destroy'])->name('destroy');
        Route::get('/{submission}/download/{filename}', [SubmissionController::class, 'downloadFile'])->name('download');

        // Form Submissions
        Route::prefix('forms/{form}')->group(function () {
            Route::get('/submissions', [SubmissionController::class, 'index'])
                ->middleware(['auth', 'can:viewAny,App\Models\Submission,form'])
                ->name('index');

            Route::get('/submissions/{submission}', [SubmissionController::class, 'showSubmission'])->name('show');
            Route::get('/submissions/edit/{submission}', [SubmissionController::class, 'edit'])->name('edit');

        });
    });
    Route::get('forms/{form}/submissions/{submission}/export/pdf', [SubmissionExportController::class, 'exportSubmissionPdf'])
        ->name('submissions.export.single.pdf');


    Route::post('/forms/{form}/assign-users', [FormAccessController::class, 'assignUsers'])->name('forms.assign-users');
    Route::post('/forms/{form}/create-access-link', [FormAccessController::class, 'createAccessLink'])->name('forms.create-access-link');
    Route::delete('/form-access-links/{accessLink}', [FormAccessController::class, 'deleteAccessLink'])->name('forms.delete-access-link');
    Route::delete('/submissions/{submission}', [SubmissionController::class, 'destroy'])->name('submissions.destroy');
    Route::get('/submissions/{submission}/download/{filename}', [SubmissionController::class, 'downloadFile'])
        ->name('submissions.download');


    /*Route::prefix('forms/{form}/workflows')->name('workflows.')->middleware(['auth'])->group(function () {
        Route::get('/manage', [WorkflowController::class, 'manage'])->name('manage');
        Route::get('/{workflow}', [WorkflowController::class, 'show'])->name('show');
        Route::delete('/steps/{step}', [WorkflowController::class, 'destroyStep'])->name('steps.destroy');
        Route::delete('/{workflow}', [WorkflowController::class, 'destroy'])->name('destroy');
    });*/

});

