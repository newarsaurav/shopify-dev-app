<?php

use App\Http\Controllers\Webhook\WebhookController;
use Illuminate\Support\Facades\Route;

Route::POST('/shopify/uninstall', [WebhookController::class, 'appUninstall']);


Route::POST('/shopify/removeShopData', [WebhookController::class, 'removeShopData']);

Route::get('/shopify/uninstall', [WebhookController::class, 'appUninstall']);

Route::get('/shopify/uninstallApp', [WebhookController::class, 'uninstallApp']);
