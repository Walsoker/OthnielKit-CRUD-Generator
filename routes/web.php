<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});


Route::resource('products', App\Http\Controllers\ProductController::class);
Route::resource('tests', App\Http\Controllers\TestController::class);
Route::resource('articles', App\Http\Controllers\ArticleController::class);


Route::resource('categories', App\Http\Controllers\CategoryController::class);
