<?php

use App\Http\Controllers\FormController;
use App\Http\Controllers\FormFieldController;
use App\Http\Controllers\SubmissionController;
use Illuminate\Support\Facades\Route;


Route::get('/', function () {
    return view('index');
});

Route::middleware([
    'auth:sanctum',
    config('jetstream.auth_session'),
    'verified',
])->group(function () {
    Route::get('/dashboard', function () {
        return view('dashboard');
    })->name('dashboard');


// Form Routes

    Route::get('/forms', [FormController::class, 'index'])->name('forms.index');
    Route::get('/forms/create', [FormController::class, 'create'])->name('forms.create');
    Route::post('/forms', [FormController::class, 'store'])->name('forms.store');
    Route::get('/forms/{form}/edit', [FormController::class, 'edit'])->name('forms.edit');
    Route::put('/forms/{form}', [FormController::class, 'update'])->name('forms.update');
    Route::delete('/forms/{form}', [FormController::class, 'destroy'])->name('forms.destroy');
    Route::get('/forms/{form}/preview', [FormController::class, 'preview'])->name('forms.preview');

    // Form Fields Routes
    Route::post('/forms/{form}/fields', [FormFieldController::class, 'store'])->name('form_fields.store');
    Route::put('/forms/{form}/fields/{field}', [FormFieldController::class, 'update'])->name('form_fields.update');
    Route::delete('/forms/{form}/fields/{field}', [FormFieldController::class, 'destroy'])->name('form_fields.destroy');

    // Submissions Routes
    Route::get('/forms/{form}/submissions', [SubmissionController::class, 'index'])->name('submissions.index');


// Public Form Submission Routes
    Route::get('/forms/{form}/submit', [SubmissionController::class, 'show'])->name('submissions.create');
    Route::post('/forms/{form}/submit', [SubmissionController::class, 'store'])->name('submissions.store');
    Route::get('/thank-you', [SubmissionController::class, 'thankyou'])->name('submissions.thankyou');

});
