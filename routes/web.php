<?php

use App\Http\Controllers\FormAccessController;
use App\Http\Controllers\FormController;
use App\Http\Controllers\FormFieldController;
use App\Http\Controllers\SubmissionController;
use App\Http\Middleware\FormAccessMiddleware;
use Illuminate\Support\Facades\Route;


Route::get('/', function () {
    return view('index');
})->name('homepage');


Route::middleware([
    'auth:sanctum',
    config('jetstream.auth_session'),
    'verified',
])->group(function () {
    Route::get('/dashboard', function () {
        return view('dashboard');
    })->name('dashboard');

// Form Routes
    Route::get('/my-forms', [FormController::class, 'userIndex'])->name('forms.user_index');
    Route::get('/forms/create', [FormController::class, 'create'])->name('forms.create');
    Route::post('/forms', [FormController::class, 'store'])->name('forms.store');
    Route::get('/forms/{form}/edit', [FormController::class, 'edit'])->name('forms.edit');
    Route::put('/forms/{form}', [FormController::class, 'update'])->name('forms.update');
    Route::delete('/forms/{form}', [FormController::class, 'destroy'])->name('forms.destroy');
    Route::get('/forms/{form}/preview', [FormController::class, 'preview'])->name('forms.preview');
    Route::delete('/forms/{form}/users/{user}', [FormController::class, 'removeUser'])
        ->name('forms.remove-user');
    // Form Fields Routes
    Route::post('/forms/{form}/fields', [FormFieldController::class, 'store'])->name('form_fields.store');
    Route::put('/forms/{form}/fields/{field}', [FormFieldController::class, 'update'])->name('form_fields.update');
    Route::delete('/forms/{form}/fields/{field}', [FormFieldController::class, 'destroy'])->name('form_fields.destroy');

    // Submissions Routes
    Route::get('/forms/{form}/submissions', [SubmissionController::class, 'index'])->name('submissions.index');
    Route::get('/forms/{form}/submissions/{submission}', [SubmissionController::class, 'showSubmission'])->name('submissions.show');

// User Submissions Route
    Route::get('/my-submissions', [SubmissionController::class, 'showUserSubmission'])->name('submissions.user');

// Public Form Submission Routes
 #  Route::get('/forms/{form}/submit', [SubmissionController::class, 'show'])->name('submissions.create');
 #  Route::post('/forms/{form}/submit', [SubmissionController::class, 'store'])->name('submissions.store');
 #  Route::get('/thank-you', [SubmissionController::class, 'thankyou'])->name('submissions.thankyou');


    Route::post('/forms/{form}/assign-users', [FormAccessController::class, 'assignUsers'])->name('forms.assign-users');
    Route::post('/forms/{form}/create-access-link', [FormAccessController::class, 'createAccessLink'])->name('forms.create-access-link');
    Route::delete('/form-access-links/{accessLink}', [FormAccessController::class, 'deleteAccessLink'])->name('forms.delete-access-link');

    Route::get('/submissions/{submission}/download/{filename}', [SubmissionController::class, 'downloadFile'])
        ->name('submissions.download');

});

Route::get('/forms', [FormController::class, 'publicIndex'])->name('forms.public_index')->middleware(FormAccessMiddleware::class);
Route::get('/forms/access/{token}', [FormAccessController::class, 'accessForm'])->name('form.access')->middleware(FormAccessMiddleware::class);
Route::get('/forms/{form}/submit', [SubmissionController::class, 'show'])->name('submissions.create')->middleware(FormAccessMiddleware::class);
Route::post('/forms/{form}/submit', [SubmissionController::class, 'store'])->name('submissions.store')->middleware(FormAccessMiddleware::class);
Route::get('/thank-you', [SubmissionController::class, 'thankyou'])->name('submissions.thankyou');
