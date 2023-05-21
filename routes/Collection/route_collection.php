<?php

use App\Http\Controllers\Collection\CollectionController;
use Illuminate\Support\Facades\Route;

// Specific Collection Route
Route::get('/getAllCollections', [CollectionController::class, 'getCollections']);
Route::get('/saveCollectionLocal', [ProductController::class, 'saveCollectionLocal']);

// Specific Collection Route
Route::get('/getSpecificCollection/{type}/{collection_id}', [CollectionController::class, 'getSpecificCollection']);
