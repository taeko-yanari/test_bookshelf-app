<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\BookController;
use App\Http\Controllers\ReviewController;
use App\Http\Controllers\FavoriteController;
use App\Http\Controllers\ReviewLikeController;
use App\Http\Controllers\RankingController;
use App\Http\Controllers\GenreController;

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

Route::get('/', [BookController::class, 'index'])->name('home');

Route::resource('books', BookController::class)->middleware('auth')->except(['index', 'show']);
Route::resource('books', BookController::class)->only(['index', 'show']);
Route::resource('genres', GenreController::class)->middleware('auth');
Route::resource('reviews', ReviewController::class)->middleware('auth')->only(['edit','update','destroy']);
Route::get('/ranking', [RankingController::class, 'index'])->name('ranking.index');

Route::middleware(['auth'])->group(function() {
  Route::post('/reviews/{book}', [ReviewController::class, 'store'])->name('reviews.store');
  Route::get('/favorites', [FavoriteController::class, 'index'])->name('favorites.index');
  Route::post('/favorites/{book}', [FavoriteController::class, 'toggle'])->name('favorites.toggle');
  Route::post('/reviews/{review}/like', [ReviewLikeController::class, 'toggle'])->name('reviews.like');
});