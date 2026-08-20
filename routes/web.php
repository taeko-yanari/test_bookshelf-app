<?php

use App\Http\Controllers\BookController;
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

Route::get('/ranking', function () {
    return 'ランキング機能は未実装です';
})->name('ranking.index');
Route::get('/favorites', function () {
    return 'お気に入り機能は未実装です';
})->name('favorites.index');
Route::get('/genres', function () {
    return 'お気に入り機能は未実装です';
})->name('genres.index');
Route::get('/reports', function () {
    return 'お気に入り機能は未実装です';
})->name('reports.index');
Route::get('/reports', function () {
    return 'お気に入り機能は未実装です';
})->name('reports.index');
Route::get('/reading-plans', function () {
    return 'お気に入り機能は未実装です';
})->name('reading-plans.index');
