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
