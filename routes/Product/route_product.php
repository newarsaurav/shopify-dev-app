<?php

use App\Http\Controllers\Product\ProductController;
use Illuminate\Support\Facades\Route;

Route::get('/getAllProducts', [ProductController::class, 'getProducts']);
Route::get('/saveProductLocal', [ProductController::class, 'saveDataDB']);
Route::get('/viewProductFromDB', [ProductController::class, 'viewDataFromDB']);


