<?php

use App\Http\Controllers\BulkVisitController;
use App\Http\Controllers\ClientController;
use App\Http\Controllers\ClientExportController;
use App\Http\Controllers\ClientGridController;
use App\Http\Controllers\ClientVisitController;
use App\Http\Controllers\FormFieldController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\StationController;
use App\Http\Controllers\UserManagementController;
use Illuminate\Support\Facades\Route;

Route::redirect('/', '/login');

Route::middleware(['auth'])->group(function () {
    Route::get('/dashboard', function () {
        return redirect()->route('clients.index');
    })->name('dashboard');

    Route::get('/clients', [ClientController::class, 'index'])->name('clients.index');
    Route::get('/clients/create', [ClientController::class, 'create'])->name('clients.create');
    Route::post('/clients', [ClientController::class, 'store'])->name('clients.store');

    Route::middleware('role:super_admin,admin')->group(function () {
        Route::get('/clients/export', ClientExportController::class)->name('clients.export');
        Route::get('/grid', [ClientGridController::class, 'index'])->name('clients.grid');
        Route::patch('/grid/{client}/fields/{field}', [ClientGridController::class, 'saveCell'])
            ->name('clients.grid.save');
    });

    Route::delete('/clients/{client}', [ClientController::class, 'destroy'])->name('clients.destroy');
    Route::get('/clients/{client}', [ClientVisitController::class, 'show'])->name('clients.show');
    Route::post('/clients/{client}/visits', [ClientVisitController::class, 'store'])->name('clients.visits.store');
    Route::get('/clients/{client}/encode', [ClientController::class, 'encodeRedirect'])->name('clients.encode');
    Route::get('/clients/{client}/visits/{visit}/encode', [ClientVisitController::class, 'encode'])
        ->name('clients.visits.encode');
    Route::patch('/clients/{client}/visits/{visit}/fields/{field}', [ClientVisitController::class, 'saveField'])
        ->name('clients.visits.fields.save');

    Route::middleware('role:super_admin')->group(function () {
        Route::post('/clients/bulk-visits', [BulkVisitController::class, 'store'])
            ->name('clients.visits.bulk-store');
        Route::resource('stations', StationController::class)->except(['show']);
        Route::resource('form-fields', FormFieldController::class)->except(['show']);
        Route::resource('users', UserManagementController::class)->except(['show']);
    });

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
