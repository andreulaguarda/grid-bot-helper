<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Grid Bot Helper</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <meta name="csrf-token" content="{{ csrf_token() }}">
</head>

<body class="bg-black text-white min-h-screen">
    <div class="container mx-auto p-4">
        <h1 class="text-4xl font-bold text-center mb-2">Grid Bot Helper</h1>
        <h2 class="text-1xl text-gray-500 text-center mb-6">Cryptocurrency grid bot trading price levels</h2>

        <div class="overflow-x-auto rounded-md overflow-hidden border border-gray-700">
            <table id="crypto-prices-table" class="min-w-full bg-gray-900 text-sm font-sans">
                <thead>
                    <tr>
                        <th class="py-3 px-6 text-left text-gray-500 font-semibold tracking-wider border-b border-gray-700">
                            <div class="flex items-center space-x-2">
                                <span>Cryptocurrency</span>
                                <button id="addCryptoBtn" class="bg-blue-600 hover:bg-blue-700 text-white rounded-md flex items-center justify-center w-8 h-8">
                                    <i class="fas fa-plus"></i>
                                </button>
                            </div>
                        </th>
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
                    @foreach ($prices as $id => $data)
                    <tr data-crypto-id="{{ $id }}" data-current-price="{{ $data['usd'] ?? '0' }}">
                        <td class="py-4 px-6 border-b border-gray-700">
                            <div class="flex items-center space-x-2">
                                <img src="{{ $data['image'] }}" alt="{{ $data['name'] }}" class="w-6 h-6">
                                <span>{{ $data['name'] }} ({{ strtoupper($data['symbol']) }})</span>
                            </div>
                        </td>
                        @php
                        $change24h = $data['usd_24h_change'] ?? null;
                        $changeClass = '';
                        if ($change24h !== null) {
                            if ($change24h > 0) {
                                $changeClass = 'text-green-500';
                            } elseif ($change24h < 0) {
                                $changeClass = 'text-red-500';
                            }
                        }
                        @endphp
                        <td class="py-4 px-6 border-b border-gray-700 {{ $changeClass }}">{{ $change24h !== null ? number_format($change24h, 2) . '%' : 'N/A' }}</td>
                        <td class="py-4 px-6 border-b border-gray-700 text-red-500" data-liquidation-price></td>
                        <td class="py-4 px-6 border-b border-gray-700 text-orange-500" data-low-price></td>
                        <td class="py-4 px-6 border-b border-gray-700">${{ $data['usd'] ?? 'N/A' }}</td>
                        <td class="py-4 px-6 border-b border-gray-700 text-green-500" data-high-price></td>
                    </tr>
                    @endforeach
                    @endif
                </tbody>
            </table>
        </div>
    </div>

    <!-- Modal de criptomonedas -->
    <div id="cryptoModal" class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center">
        <div class="bg-gray-800 p-6 rounded-lg w-full max-w-2xl mx-4">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-xl font-semibold">Add Cryptocurrency</h3>
                <button id="closeModal" class="text-gray-400 hover:text-white">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div id="cryptoList" class="max-h-96 overflow-y-auto">
                <div class="p-4 text-gray-400">Loading cryptocurrencies...</div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const addCryptoBtn = document.getElementById('addCryptoBtn');
            const cryptoModal = document.getElementById('cryptoModal');
            const closeModal = document.getElementById('closeModal');
            const cryptoList = document.getElementById('cryptoList');

            // Función para cargar las criptomonedas principales
            async function loadTopCryptos() {
                try {
                    const response = await fetch('https://api.coingecko.com/api/v3/coins/markets?vs_currency=usd&order=market_cap_desc&per_page=250&page=1&sparkline=false');
                    if (!response.ok) {
                        throw new Error(`Error HTTP: ${response.status}`);
                    }
                    const coins = await response.json();
                    displayCryptoList(coins);
                } catch (error) {
                    console.error('Error loading cryptocurrencies:', error);
                     cryptoList.innerHTML = `<div class="p-4 text-red-500">Error loading cryptocurrencies: ${error.message}</div>`;
                }
            }

            // Función para mostrar la lista de criptomonedas
            function displayCryptoList(coins) {
                cryptoList.innerHTML = coins.map(coin => `
                    <div class="flex items-center justify-between p-3 hover:bg-gray-700 cursor-pointer rounded-md">
                        <div class="flex items-center space-x-3">
                            <img src="${coin.image}" alt="${coin.name}" class="w-8 h-8">
                            <div>
                                <div class="font-medium">${coin.name}</div>
                                <div class="text-sm text-gray-400">${coin.symbol.toUpperCase()}</div>
                            </div>
                        </div>
                        <button class="bg-blue-600 hover:bg-blue-700 text-white w-8 h-8 rounded-md flex items-center justify-center add-crypto" data-coin='${JSON.stringify({id: coin.id, name: coin.name, symbol: coin.symbol, image: coin.image})}'>
                             <i class="fas fa-plus"></i>
                         </button>
                    </div>
                `).join('');

                // Evento para añadir moneda
                cryptoList.querySelectorAll('.add-crypto').forEach(button => {
                    button.addEventListener('click', async (e) => {
                        e.stopPropagation();
                        const coin = JSON.parse(e.target.dataset.coin);
                        try {
                            const response = await fetch('/api/selected-coins', {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json',
                                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                                },
                                body: JSON.stringify({ coins: [coin] })
                            });

                            if (response.ok) {
                                window.location.reload();
                            }
                        } catch (error) {
                            console.error('Error adding cryptocurrency:', error);
                        }
                    });
                });
            }

            // Función para abrir el modal
            addCryptoBtn.addEventListener('click', () => {
                cryptoModal.classList.remove('hidden');
                cryptoModal.classList.add('flex');
                loadTopCryptos();
            });

            // Función para cerrar el modal
            closeModal.addEventListener('click', () => {
                cryptoModal.classList.add('hidden');
                cryptoModal.classList.remove('flex');
                cryptoList.innerHTML = '<div class="p-4 text-gray-400">Cargando criptomonedas...</div>';
            });

            // Cerrar modal al hacer clic fuera
            cryptoModal.addEventListener('click', (e) => {
                if (e.target === cryptoModal) {
                    closeModal.click();
                }
            });




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
                    if (!isNaN(currentPrice)) {
                        const liquidationPrice = currentPrice * (1 + (liquidationPercentage / 100));
                        const lowPrice = currentPrice * (1 + (lowPercentage / 100));
                        const highPrice = currentPrice * (1 + (highPercentage / 100));

                        const liquidationElement = row.querySelector('[data-liquidation-price]');
                        const lowElement = row.querySelector('[data-low-price]');
                        const highElement = row.querySelector('[data-high-price]');

                        if (liquidationElement) liquidationElement.textContent = `$${liquidationPrice.toFixed(2)}`;
                        if (lowElement) lowElement.textContent = `$${lowPrice.toFixed(2)}`;
                        if (highElement) highElement.textContent = `$${highPrice.toFixed(2)}`;
                    }
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