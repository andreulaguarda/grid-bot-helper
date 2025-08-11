<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CoinGeckoController;

Route::get('/', function () {
    $prices = app(CoinGeckoController::class)->getPrices();
    return view('home', ['prices' => $prices]);
});
