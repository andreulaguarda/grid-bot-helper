<?php

namespace App\Http\Controllers;

use App\Models\SelectedCryptocurrency;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;

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
                $selectedCoins = collect($this->topCoins)->pluck('id')->toArray();
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
            \Log::error('Error en getPrices: ' . $e->getMessage());
            return ['error' => 'Could not fetch prices', 'message' => $e->getMessage()];
        }
    }

    private $topCoins = [
        ['rank' => 1, 'name' => 'Bitcoin', 'symbol' => 'BTC', 'id' => 'bitcoin', 'image_id' => '1'],
        ['rank' => 2, 'name' => 'Ethereum', 'symbol' => 'ETH', 'id' => 'ethereum', 'image_id' => '279'],
        ['rank' => 3, 'name' => 'XRP', 'symbol' => 'XRP', 'id' => 'ripple', 'image_id' => '44'],
        ['rank' => 4, 'name' => 'BNB', 'symbol' => 'BNB', 'id' => 'binancecoin', 'image_id' => '825'],
        ['rank' => 5, 'name' => 'Solana', 'symbol' => 'SOL', 'id' => 'solana', 'image_id' => '4128'],
        ['rank' => 6, 'name' => 'Dogecoin', 'symbol' => 'DOGE', 'id' => 'dogecoin', 'image_id' => '5'],
        ['rank' => 7, 'name' => 'TRON', 'symbol' => 'TRX', 'id' => 'tron', 'image_id' => '1094'],
        ['rank' => 8, 'name' => 'Cardano', 'symbol' => 'ADA', 'id' => 'cardano', 'image_id' => '2010'],
        ['rank' => 9, 'name' => 'Hyperliquid', 'symbol' => 'HYPE', 'id' => 'hyperliquid', 'image_id' => '31145'],
        ['rank' => 10, 'name' => 'Chainlink', 'symbol' => 'LINK', 'id' => 'chainlink', 'image_id' => '877'],
        ['rank' => 11, 'name' => 'Stellar', 'symbol' => 'XLM', 'id' => 'stellar', 'image_id' => '100'],
        ['rank' => 12, 'name' => 'Sui', 'symbol' => 'SUI', 'id' => 'sui', 'image_id' => '26375'],
        ['rank' => 13, 'name' => 'Bitcoin Cash', 'symbol' => 'BCH', 'id' => 'bitcoin-cash', 'image_id' => '1831'],
        ['rank' => 14, 'name' => 'Hedera', 'symbol' => 'HBAR', 'id' => 'hedera-hashgraph', 'image_id' => '4642'],
        ['rank' => 15, 'name' => 'Avalanche', 'symbol' => 'AVAX', 'id' => 'avalanche-2', 'image_id' => '12559'],
        ['rank' => 16, 'name' => 'UNUS SED LEO', 'symbol' => 'LEO', 'id' => 'leo-token', 'image_id' => '8418'],
        ['rank' => 17, 'name' => 'Shiba Inu', 'symbol' => 'SHIB', 'id' => 'shiba-inu', 'image_id' => '11939'],
        ['rank' => 18, 'name' => 'Toncoin', 'symbol' => 'TON', 'id' => 'the-open-network', 'image_id' => '16547'],
        ['rank' => 19, 'name' => 'Litecoin', 'symbol' => 'LTC', 'id' => 'litecoin', 'image_id' => '2'],
        ['rank' => 20, 'name' => 'Polkadot', 'symbol' => 'DOT', 'id' => 'polkadot', 'image_id' => '12171'],
        ['rank' => 21, 'name' => 'Monero', 'symbol' => 'XMR', 'id' => 'monero', 'image_id' => '69'],
        ['rank' => 22, 'name' => 'Uniswap', 'symbol' => 'UNI', 'id' => 'uniswap', 'image_id' => '12504'],
        ['rank' => 23, 'name' => 'Pepe', 'symbol' => 'PEPE', 'id' => 'pepe', 'image_id' => '29850'],
        ['rank' => 24, 'name' => 'Bitget Token', 'symbol' => 'BGB', 'id' => 'bitget-token', 'image_id' => '11359'],
        ['rank' => 25, 'name' => 'Aave', 'symbol' => 'AAVE', 'id' => 'aave', 'image_id' => '12645'],
        ['rank' => 26, 'name' => 'Bittensor', 'symbol' => 'TAO', 'id' => 'bittensor', 'image_id' => '28452'],
        ['rank' => 27, 'name' => 'Pi', 'symbol' => 'PI', 'id' => 'pi', 'image_id' => '31857'],
        ['rank' => 28, 'name' => 'Aptos', 'symbol' => 'APT', 'id' => 'aptos', 'image_id' => '22861'],
        ['rank' => 29, 'name' => 'NEAR Protocol', 'symbol' => 'NEAR', 'id' => 'near', 'image_id' => '10365'],
        ['rank' => 30, 'name' => 'OKB', 'symbol' => 'OKB', 'id' => 'okb', 'image_id' => '3897']
    ];

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
            \Log::error('Error en getTop100Coins: ' . $e->getMessage());
            return [];
        }
    }

    public function updateSelectedCoins(Request $request)
    {
        try {
            $coins = $request->input('coins', []);
            
            // Desactivar todas las monedas
            SelectedCryptocurrency::query()->update(['is_active' => false]);
            
            foreach ($coins as $coin) {
                SelectedCryptocurrency::updateOrCreate(
                    ['coin_id' => $coin['id']],
                    [
                        'name' => $coin['name'],
                        'symbol' => $coin['symbol'],
                        'is_active' => true
                    ]
                );
            }

            return ['success' => true, 'message' => 'Cryptocurrencies updated successfully'];
        } catch (\Exception $e) {
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
            \Log::error('Error en searchCoins: ' . $e->getMessage());
            return [];
        }
    }
}
