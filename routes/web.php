<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DomainController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\UserManagementController;
use App\Http\Controllers\WhatsAppLogController;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    Route::resource('domains', DomainController::class);
    Route::post('/domains/{domain}/check', [DomainController::class, 'checkNow'])->name('domains.check');

    Route::post('/ssl/check-all', function () {
        Artisan::call('ssl:check-daily');
        return back()->with('status', 'Pemeriksaan seluruh domain berhasil dijalankan ke antrian sistem.');
    })->name('ssl.check-all');

    // Log & tes WhatsApp
    Route::get('/whatsapp/log', [WhatsAppLogController::class, 'index'])->name('whatsapp.log');
    Route::post('/whatsapp/test', [WhatsAppLogController::class, 'test'])->name('whatsapp.test');

    // Administrasi (pengecekan admin ada di dalam controller)
    Route::get('/admin/users', [UserManagementController::class, 'index'])->name('admin.users');
    Route::patch('/admin/users/{user}/role', [UserManagementController::class, 'updateRole'])->name('admin.users.role');

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';