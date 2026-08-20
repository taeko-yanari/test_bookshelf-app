<?php

use App\Http\Controllers\BookController;
use App\Http\Controllers\ReviewController;
use App\Http\Controllers\FavoriteController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::resource('books', BookController::class)->middleware('auth')->except(['index', 'show']);
Route::resource('books', BookController::class)->only(['index', 'show']);
Route::resource('reviews', ReviewController::class)->middleware('auth')->only(['edit','update','destroy']);

Route::middleware(['auth'])->group(function() {
  Route::post('/reviews/{book}', [ReviewController::class, 'store'])->name('reviews.store');
  Route::get('/favorites', [FavoriteController::class, 'index'])->name('favorites.index');
});
