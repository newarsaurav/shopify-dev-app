<?php

use App\Http\Controllers\Plan\PlanController;
use Illuminate\Support\Facades\Route;

Route::get('/plan', [PlanController::class, 'index']);


Route::get('/plan/purchase/monthly/{amount}', [PlanController::class, 'purchaseMonthlyPlan']);
Route::get('/plan/purchase/annual/{amount}', [PlanController::class, 'purchaseAnnualPlan']);