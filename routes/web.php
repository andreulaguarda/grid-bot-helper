<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CoinGeckoController;

Route::get('/', function () {
    $prices = app(CoinGeckoController::class)->getPrices();
    $selectedCoins = app(CoinGeckoController::class)->getSelectedCoins();
    return view('home', [
        'prices' => $prices,
        'selectedCoins' => $selectedCoins['data'] ?? []
    ]);
});

Route::prefix('api')->group(function () {
    Route::get('/coins', [CoinGeckoController::class, 'getTop100Coins']);
    Route::get('/selected-coins', [CoinGeckoController::class, 'getSelectedCoins']);
    Route::post('/selected-coins', [CoinGeckoController::class, 'updateSelectedCoins']);
    Route::get('/search-coins', [CoinGeckoController::class, 'searchCoins']);
});
