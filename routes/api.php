<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\PostController;
use App\Http\Controllers\CategoryController;
use App\Models\Category;
use Illuminate\Support\Facades\Route;


Route::post('/login', [AuthController::class, 'login'])->name('login');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');


Route::group(['prefix' => 'posts'], function () {
    Route::get('/', [PostController::class, 'index'])->name('index');
    Route::get('/trending', [PostController::class, 'trending'])->name('trending');
    Route::get('/featured', [PostController::class, 'featured'])->name('featured');
    Route::get('/{id}', [PostController::class, 'show'])->name('show');
    Route::post('/', [PostController::class, 'store'])->name('store');
    Route::get('/detail/{slug}', [PostController::class, 'detail'])->name('detail');
    Route::put('/{id}', [PostController::class, 'update'])->name('update');
    Route::delete('/{id}', [PostController::class, 'destroy'])->name('destroy');
});

Route::group(['prefix' => 'categories'], function () {
    Route::get('/', [CategoryController::class, 'index'])->name('index');
    Route::post('/', [CategoryController::class, 'store'])->name('store');
    Route::put('/{id}', [CategoryController::class, 'update'])->name('update');
    Route::delete('/{id}', [CategoryController::class, 'destroy'])->name('destroy');
});