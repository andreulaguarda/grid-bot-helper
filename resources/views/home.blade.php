<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Grid Bot Helper</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="bg-black text-white min-h-screen">
    <div class="container mx-auto p-4">
        <h1 class="text-4xl font-bold text-center mb-2">Grid Bot Helper</h1>
        <h2 class="text-1xl text-gray-500 text-center mb-6">Cryptocurrency grid bot trading price levels</h2>
        <div class="overflow-x-auto rounded-md overflow-hidden border border-gray-700">
            <table id="crypto-prices-table" class="min-w-full bg-gray-900 text-sm font-sans">
                <thead>
                    <tr>
                        <th class="py-3 px-6 text-left text-gray-500 font-semibold tracking-wider border-b border-gray-700">Cryptocurrency</th>
                        <th class="py-3 px-6 text-left text-gray-500 font-semibold tracking-wider border-b border-gray-700">24h Change (%)</th>
                        <th class="py-3 px-6 text-left text-gray-500 font-semibold tracking-wider border-b border-gray-700">
                            <label for="liquidation-select" class="text-gray-500">Liquidation:</label>
                            <select id="liquidation-select" class="bg-gray-700 text-white rounded-md p-2">
                                @for ($i = -5; $i >= -50; $i -= 5)
                                <option value="{{ $i }}" @if ($i==-40) selected @endif>{{ $i }}%</option>
                                @endfor
                            </select>
                        </th>
                        <th class="py-3 px-6 text-left text-gray-500 font-semibold tracking-wider border-b border-gray-700">
                            <label for="low-select" class="text-gray-500">Low:</label>
                            <select id="low-select" class="bg-gray-700 text-white rounded-md p-2">
                                @for ($i = -5; $i >= -50; $i -= 5)
                                <option value="{{ $i }}" @if ($i==-30) selected @endif>{{ $i }}%</option>
                                @endfor
                            </select>
                        </th>
                        <th class="py-3 px-6 text-left text-gray-500 font-semibold tracking-wider border-b border-gray-700">Current Price</th>
                        <th class="py-3 px-6 text-left text-gray-500 font-semibold tracking-wider border-b border-gray-700">
                            <label for="high-select" class="text-gray-500">High:</label>
                            <select id="high-select" class="bg-gray-700 text-white rounded-md p-2">
                                @for ($i = 5; $i <= 100; $i +=5)
                                    <option value="{{ $i }}" @if ($i==30) selected @endif>{{ $i }}%</option>
                                    @endfor
                            </select>
                        </th>
                    </tr>
                </thead>
                <tbody id="crypto-prices-body">
                    @if (isset($prices['error']))
                    <tr>
                        <td colspan="3" class="py-4 px-6 text-center text-red-500">{{ $prices['message'] }}</td>
                    </tr>
                    @else
                    @php
                    $cryptocurrencies = [
                    'bitcoin' => 'Bitcoin (BTC)',
                    'ethereum' => 'Ethereum (ETH)',
                    'binancecoin' => 'Binance Coin (BNB)',
                    'ripple' => 'Ripple (XRP)',
                    'solana' => 'Solana (SOL)',
                    'cardano' => 'Cardano (ADA)',
                    'avalanche-2' => 'Avalanche (AVAX)',
                    'polkadot' => 'Polkadot (DOT)',
                    'litecoin' => 'Litecoin (LTC)',
                    'sui' => 'Sui (SUI)',
                    'chainlink' => 'Chainlink (LINK)'
                    ];
                    @endphp
                    @foreach ($cryptocurrencies as $id => $name)
                    <tr data-crypto-id="{{ $id }}" data-current-price="{{ $prices[$id]['usd'] ?? '0' }}">
                        <td class="py-4 px-6 border-b border-gray-700">{{ $name }}</td>
                        @php
                        $change24h = $prices[$id]['usd_24h_change'] ?? null;
                        $changeClass = '';
                        if ($change24h !== null) {
                        if ($change24h > 0) {
                        $changeClass = 'text-green-500';
                        } elseif ($change24h < 0) {
                            $changeClass='text-red-500' ;
                            }
                            }
                            @endphp
                            <td class="py-4 px-6 border-b border-gray-700 {{ $changeClass }}">{{ $change24h !== null ? number_format($change24h, 2) . '%' : 'N/A' }}</td>
                            <td class="py-4 px-6 border-b border-gray-700 text-red-500" data-liquidation-price></td>
                            <td class="py-4 px-6 border-b border-gray-700 text-orange-500" data-low-price></td>
                            <td class="py-4 px-6 border-b border-gray-700">${{ $prices[$id]['usd'] ?? 'N/A' }}</td>
                            <td class="py-4 px-6 border-b border-gray-700 text-green-500" data-high-price></td>
                    </tr>
                    @endforeach
                    @endif
                </tbody>
            </table>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const liquidationSelect = document.getElementById('liquidation-select');
            const lowSelect = document.getElementById('low-select');
            const highSelect = document.getElementById('high-select');
            const cryptoPricesTable = document.getElementById('crypto-prices-table');

            function updatePrices() {
                const liquidationPercentage = parseFloat(liquidationSelect.value);
                const lowPercentage = parseFloat(lowSelect.value);
                const highPercentage = parseFloat(highSelect.value);

                cryptoPricesTable.querySelectorAll('tbody tr').forEach(row => {
                    const currentPrice = parseFloat(row.dataset.currentPrice);

                    const liquidationPrice = currentPrice * (1 + (liquidationPercentage / 100));
                    const lowPrice = currentPrice * (1 + (lowPercentage / 100));
                    const highPrice = currentPrice * (1 + (highPercentage / 100));

                    row.querySelector('[data-liquidation-price]').textContent = `$${liquidationPrice.toFixed(2)}`;
                    row.querySelector('[data-low-price]').textContent = `$${lowPrice.toFixed(2)}`;
                    row.querySelector('[data-high-price]').textContent = `$${highPrice.toFixed(2)}`;
                });
            }

            // Initial call to update prices when the page loads
            updatePrices();

            liquidationSelect.addEventListener('change', updatePrices);
            lowSelect.addEventListener('change', updatePrices);
            highSelect.addEventListener('change', updatePrices);
        });
    </script>
</body>
</html>