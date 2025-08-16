<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class CoinGeckoController extends Controller
{
    public function getPrices()
    {
        try {
            $response = Http::get('https://api.coingecko.com/api/v3/simple/price', [
                'ids' => 'bitcoin,ethereum,ripple,binancecoin,solana,dogecoin,cardano,sui,chainlink,avalanche-2,litecoin,polkadot,pepe',
                'vs_currencies' => 'usd',
                'include_24hr_change' => 'true',
            ]);

            return $response->json();
        } catch (\Exception $e) {
            // Log the error for debugging purposes
            // Log::error('CoinGecko API Error: ' . $e->getMessage());
            return ['error' => 'Could not fetch prices', 'message' => $e->getMessage()];
        }
    }
}
