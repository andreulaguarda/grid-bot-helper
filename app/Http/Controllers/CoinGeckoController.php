<?php

namespace App\Http\Controllers;

use App\Models\SelectedCryptocurrency;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class CoinGeckoController extends Controller
{
    public function getPrices()
    {
        try {
            // Intentar obtener datos del caché
            $cacheKey = 'coingecko_prices';
            if (Cache::has($cacheKey)) {
                return Cache::get($cacheKey);
            }

            $selectedCoins = SelectedCryptocurrency::where('is_active', true)
                ->pluck('coin_id')
                ->toArray();

            if (empty($selectedCoins)) {
                return [];
            }

            $coinData = [];
            foreach (array_chunk($selectedCoins, 30) as $chunk) {
                // Esperar 1 segundo entre solicitudes para evitar límites de tasa
                sleep(1);

                $response = Http::get('https://api.coingecko.com/api/v3/coins/markets', [
                    'ids' => implode(',', $chunk),
                    'vs_currency' => 'usd',
                    'order' => 'market_cap_desc',
                    'per_page' => 30,
                    'page' => 1,
                    'sparkline' => false,
                    'price_change_percentage' => '24h'
                ]);

                if (!$response->successful()) {
                    // Si el error es 429 (Too Many Requests), esperar y reintentar
                    if ($response->status() === 429) {
                        sleep(5); // Esperar 5 segundos
                        $response = Http::get('https://api.coingecko.com/api/v3/coins/markets', [
                            'ids' => implode(',', $chunk),
                            'vs_currency' => 'usd',
                            'order' => 'market_cap_desc',
                            'per_page' => 30,
                            'page' => 1,
                            'sparkline' => false,
                            'price_change_percentage' => '24h'
                        ]);
                    }
                    
                    if (!$response->successful()) {
                        throw new \Exception('Error al obtener datos de CoinGecko: ' . $response->status());
                    }
                }

                $chunkData = $response->json();
                if (!is_array($chunkData)) {
                    throw new \Exception('La respuesta no es un array válido');
                }

                $coinData = array_merge($coinData, $chunkData);
            }

            $result = [];
            foreach ($coinData as $coin) {
                $result[$coin['id']] = [
                    'usd' => $coin['current_price'],
                    'usd_24h_change' => $coin['price_change_percentage_24h'],
                    'name' => $coin['name'],
                    'symbol' => $coin['symbol'],
                    'image' => $coin['image']
                ];
            }

            // Guardar en caché por 5 minutos
            Cache::put($cacheKey, $result, now()->addMinutes(5));

            return $result;
        } catch (\Exception $e) {
            Log::error('Error en getPrices: ' . $e->getMessage());
            return ['error' => 'Could not fetch prices', 'message' => $e->getMessage()];
        }
    }



    public function getTop100Coins()
    {
        try {
            $response = Http::get('https://api.coingecko.com/api/v3/coins/markets', [
                'vs_currency' => 'usd',
                'order' => 'market_cap_desc',
                'per_page' => 100,
                'page' => 1,
                'sparkline' => false
            ]);

            if (!$response->successful()) {
                throw new \Exception('Error al obtener datos de CoinGecko: ' . $response->status());
            }

            $coinData = $response->json();

            if (!is_array($coinData)) {
                throw new \Exception('La respuesta de CoinGecko no es un array válido');
            }

            if (empty($coinData)) {
                return [];
            }

            return array_map(function($coin) {
                return [
                    'id' => $coin['id'],
                    'name' => $coin['name'],
                    'symbol' => $coin['symbol'],
                    'rank' => $coin['market_cap_rank'],
                    'image' => $coin['image']
                ];
            }, $coinData);
        } catch (\Exception $e) {
            Log::error('Error en getTop100Coins: ' . $e->getMessage());
            return [];
        }
    }

    public function updateSelectedCoins(Request $request)
    {
        try {
            $coins = $request->input('coins', []);
            
            if (empty($coins)) {
                return ['error' => 'No coins provided', 'message' => 'Please provide at least one coin'];
            }

            foreach ($coins as $coin) {
                $selectedCoin = SelectedCryptocurrency::where('coin_id', $coin['id'])->first();
                
                if ($selectedCoin) {
                    // Si la moneda existe, invertir su estado
                    $selectedCoin->update([
                        'is_active' => !$selectedCoin->is_active
                    ]);
                } else {
                    // Si la moneda no existe, crearla como activa
                    SelectedCryptocurrency::create([
                        'coin_id' => $coin['id'],
                        'name' => $coin['name'],
                        'symbol' => $coin['symbol'],
                        'is_active' => true
                    ]);
                }
            }

            // Limpiar el caché para que los cambios se reflejen inmediatamente
            Cache::forget('coingecko_prices');

            return ['success' => true, 'message' => 'Cryptocurrencies updated successfully'];
        } catch (\Exception $e) {
            Log::error('Error en updateSelectedCoins: ' . $e->getMessage());
            return ['error' => 'Could not update selected coins', 'message' => $e->getMessage()];
        }
    }

    public function getSelectedCoins()
    {
        try {
            $coins = SelectedCryptocurrency::where('is_active', true)->get();
            return ['success' => true, 'data' => $coins];
        } catch (\Exception $e) {
            return ['error' => 'Could not fetch selected coins', 'message' => $e->getMessage()];
        }
    }

    public function searchCoins(Request $request)
    {
        try {
            $query = strtolower($request->input('query', ''));
            if (strlen($query) < 2) {
                return [];
            }

            // Intentar obtener datos del caché
            $cacheKey = 'coingecko_top_100';
            $coins = Cache::remember($cacheKey, now()->addMinutes(5), function () {
                $response = Http::get('https://api.coingecko.com/api/v3/coins/markets', [
                    'vs_currency' => 'usd',
                    'order' => 'market_cap_desc',
                    'per_page' => 100,
                    'page' => 1,
                    'sparkline' => false
                ]);

                if (!$response->successful()) {
                    throw new \Exception('Error al obtener datos de CoinGecko: ' . $response->status());
                }

                return $response->json();
            });

            if (!is_array($coins)) {
                throw new \Exception('La respuesta no es un array válido');
            }

            // Filtrar las monedas según el término de búsqueda
            $filteredCoins = array_filter($coins, function($coin) use ($query) {
                return str_contains(strtolower($coin['name']), $query) ||
                       str_contains(strtolower($coin['symbol']), $query);
            });

            return array_map(function($coin) {
                return [
                    'id' => $coin['id'],
                    'name' => $coin['name'],
                    'symbol' => $coin['symbol'],
                    'image' => $coin['image']
                ];
            }, array_values($filteredCoins));

        } catch (\Exception $e) {
            Log::error('Error en searchCoins: ' . $e->getMessage());
            return [];
        }
    }
}
