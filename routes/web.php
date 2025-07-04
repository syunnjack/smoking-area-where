<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\SpotController;
use App\Http\Controllers\ReviewController;




/*Route::get('/', function () {
    return view('welcome');
});*/

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

Route::get('/', [SpotController::class, 'index']);
Route::get('/create', [SpotController::class, 'create']);
Route::post('/store', [SpotController::class, 'store']);

Route::get('/spots', [SpotController::class, 'index']);

Route::get('/spots/{spot}', [SpotController::class, 'show']);

Route::view('/thanks', 'spots.thanks')->name('spots.thanks');

Route::post('/spots/{spot}/reviews', [ReviewController::class, 'store'])->name('reviews.store');

Route::get('/spots/{spot}/edit', [SpotController::class, 'edit'])->name('spots.edit');

Route::patch('/spots/{spot}', [SpotController::class, 'update'])->name('spots.update');



Route::post('/spots/{spot}/reviews', [ReviewController::class, 'store'])
    ->name('spots.reviews.store');

Route::get('/spots', [SpotController::class, 'index'])->name('spots.index');

Route::get('/spots/{spot}', [SpotController::class, 'show'])->name('spots.show');


require __DIR__.'/auth.php';
