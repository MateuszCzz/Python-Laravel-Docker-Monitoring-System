<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\VideoController;
use Illuminate\Support\Facades\Route;


Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    Route::get('/videos', [VideoController::class, 'listVideos'])->name('videos.listVideos');
    Route::get('/videos/{video}/download', [VideoController::class, 'download'])->name('videos.download');
    Route::delete('/videos/remove/{id}', [VideoController::class, 'remove'])->name('videos.remove');
    Route::delete('/videos/remove-all', [VideoController::class, 'removeAll'])->name('videos.removeAll');
});


require __DIR__.'/auth.php';