<?php

use App\Http\Controllers\DownloadController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('dashboard', function () {
        return Inertia::render('dashboard');
    })->name('dashboard');
});

Route::get('/d/{documentToken}', [DownloadController::class, 'download'])->name('download.download');
Route::get('/', [DownloadController::class, 'index'])->name('download.index');
Route::get('/testreact', [DownloadController::class, 'testreact'])->name('download.testreact');
Route::get('/testsvelte', [DownloadController::class, 'testsvelte'])->name('download.testsvelte');


require __DIR__.'/settings.php';
require __DIR__.'/auth.php';
