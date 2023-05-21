<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\LoginController;
use App\Models\Shop;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});


Route::get('/', [LoginController::class, 'index']);

Route::POST('/addShop', [LoginController::class, 'addShop']);

Route::get('/login', [LoginController::class, 'login']);


Route::get('/auth/callback', [LoginController::class, 'generateToken']);

Route::get('/shop/list', function(){
    return Shop::all();
});

