<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DomainController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::middleware(['auth', 'verified'])->group(function () {
    // Admin area – only users with the "Administrator" role can access
    Route::prefix('admin')
        ->name('admin.')
        ->middleware(['role:Administrator'])
        ->group(function () {
            Route::get('dashboard', [\App\Http\Controllers\Admin\AdminDashboardController::class, 'index'])
                ->name('dashboard');
        });
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    Route::resource('domains', DomainController::class);
    Route::post('/domains/{domain}/check', [DomainController::class, 'checkNow'])->name('domains.check');

    Route::post('/ssl/check-all', function () {
        Artisan::call('ssl:check-daily');
        return back()->with('status', 'Pemeriksaan seluruh domain berhasil dijalankan ke antrian sistem.');
    })->name('ssl.check-all');

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
