<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CoinGeckoController;

Route::get('/coins', [CoinGeckoController::class, 'getTop100Coins']);
Route::get('/selected-coins', [CoinGeckoController::class, 'getSelectedCoins']);
Route::post('/selected-coins', [CoinGeckoController::class, 'updateSelectedCoins']);
Route::get('/search-coins', [CoinGeckoController::class, 'searchCoins']);