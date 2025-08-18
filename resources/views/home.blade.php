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

        <!-- Loading Overlay -->
        <div id="loadingOverlay" class="fixed inset-0 bg-black bg-opacity-75 hidden items-center justify-center z-50">
            <div class="bg-gray-800 p-8 rounded-lg text-center">
                <div class="animate-spin rounded-full h-16 w-16 border-b-2 border-blue-500 mx-auto mb-4"></div>
                <p class="text-white text-lg font-medium">Updating data...</p>
                <p class="text-gray-400 text-sm mt-2">Please wait while cryptocurrencies are loading</p>
            </div>
        </div>

        <div class="overflow-x-auto rounded-md overflow-hidden border border-gray-700">
            <table id="crypto-prices-table" class="min-w-full bg-gray-900 text-sm font-sans">
                <thead>
                    <tr>
                        <th class="py-3 px-6 text-left text-gray-500 font-semibold tracking-wider border-b border-gray-700">
                            <div class="flex items-center space-x-2">
                                <span>Cryptocurrency</span>
                                <button id="addCryptoBtn" class="bg-gray-700 hover:bg-blue-600 text-white rounded-md flex items-center justify-center w-8 h-8 cursor-pointer">
                                    <i class="fas fa-gear"></i>
                                </button>
                            </div>
                        </th>
                        <th class="py-3 px-6 text-left text-gray-500 font-semibold tracking-wider border-b border-gray-700">24h Change (%)</th>
                        <th class="py-3 px-6 text-left text-gray-500 font-semibold tracking-wider border-b border-gray-700">
                            <label for="liquidation-select" class="text-gray-500">Liquidation:</label>
                            <select id="liquidation-select" class="bg-gray-700 hover:bg-gray-600 text-white rounded-md p-2 cursor-pointer">
                                @for ($i = -5; $i >= -50; $i -= 5)
                                <option value="{{ $i }}" @if ($i==-40) selected @endif>{{ $i }}%</option>
                                @endfor
                            </select>
                        </th>
                        <th class="py-3 px-6 text-left text-gray-500 font-semibold tracking-wider border-b border-gray-700">
                            <label for="low-select" class="text-gray-500">Low:</label>
                            <select id="low-select" class="bg-gray-700 hover:bg-gray-600 text-white rounded-md p-2 cursor-pointer">
                                @for ($i = -5; $i >= -50; $i -= 5)
                                <option value="{{ $i }}" @if ($i==-30) selected @endif>{{ $i }}%</option>
                                @endfor
                            </select>
                        </th>
                        <th class="py-3 px-6 text-left text-gray-500 font-semibold tracking-wider border-b border-gray-700">Current Price</th>
                        <th class="py-3 px-6 text-left text-gray-500 font-semibold tracking-wider border-b border-gray-700">
                            <label for="high-select" class="text-gray-500">High:</label>
                            <select id="high-select" class="bg-gray-700 hover:bg-gray-600 text-white rounded-md p-2 cursor-pointer">
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

    <!-- Cryptocurrency Modal -->
    <div id="cryptoModal" class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center">
        <div class="bg-gray-800 p-6 rounded-lg w-full max-w-2xl mx-4">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-xl font-semibold">Manage Cryptocurrencies</h3>
                <button id="closeModal" class="text-gray-400 hover:text-white cursor-pointer">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div id="cryptoList" class="max-h-96 overflow-y-auto mb-4">
                <div class="p-4 text-gray-400">Loading cryptocurrencies...</div>
            </div>
            <div class="flex justify-between">
                <button id="resetToDefaults" class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-md cursor-pointer">
                DEACTIVATE ALL
            </button>
                <button id="saveChanges" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-md cursor-pointer">
                    OK
                </button>
            </div>
        </div>
    </div>

    <script>
        // Mostrar overlay de carga al inicio
        window.addEventListener('beforeunload', () => {
            const loadingOverlay = document.getElementById('loadingOverlay');
            if (loadingOverlay) {
                loadingOverlay.classList.remove('hidden');
                loadingOverlay.classList.add('flex');
            }
        });
        
        // Ocultar overlay cuando la página esté completamente cargada
        window.addEventListener('load', () => {
            const loadingOverlay = document.getElementById('loadingOverlay');
            if (loadingOverlay) {
                setTimeout(() => {
                    loadingOverlay.classList.add('hidden');
                    loadingOverlay.classList.remove('flex');
                }, 500); // Pequeño delay para suavizar la transición
            }
        });
        
        document.addEventListener('DOMContentLoaded', function() {
            const addCryptoBtn = document.getElementById('addCryptoBtn');
            const cryptoModal = document.getElementById('cryptoModal');
            const closeModal = document.getElementById('closeModal');
            const cryptoList = document.getElementById('cryptoList');

            // Function to handle cryptocurrency toggle using event delegation
            function setupCryptoToggleHandler() {
                const cryptoList = document.getElementById('cryptoList');
                if (!cryptoList) {
                    console.warn('cryptoList container not found');
                    return;
                }

                // Avoid duplicate listeners
                if (cryptoList._toggleHandlerAttached) return;
                cryptoList._toggleHandlerAttached = true;

                cryptoList.addEventListener('change', async (e) => {
                    const input = e.target;
                    if (!(input instanceof HTMLInputElement) || input.type !== 'checkbox') return;

                    const label = input.closest('label.toggle-crypto');
                    if (!label) return;

                    try {
                        const coinDataStr = label.dataset.coin;
                        const coin = coinDataStr ? JSON.parse(coinDataStr) : null;
                        if (!coin || !coin.id) return;

                        const isNowSelected = input.checked;
                        console.log('Toggle change:', { coin: coin.name, isNowSelected });

                        const response = await fetch('/api/selected-coins', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'Accept': 'application/json'
                            },
                            body: JSON.stringify({ 
                                coins: [{
                                    id: coin.id,
                                    name: coin.name,
                                    symbol: coin.symbol
                                }]
                            })
                        });

                        if (response.ok) {
                            const result = await response.json();
                            console.log('API response:', result);
                            label.dataset.selected = isNowSelected.toString();
                        } else {
                            console.error('API error:', response.status, await response.text());
                            // Revertir el checkbox si hay error
                            input.checked = !isNowSelected;
                        }
                    } catch (error) {
                        console.error('Error toggling cryptocurrency:', error);
                        // Revertir el checkbox si hay error
                        input.checked = !input.checked;
                    }
                });
            }

            // Función para crear fetch con timeout
            function fetchWithTimeout(url, options = {}, timeout = 10000) {
                return Promise.race([
                    fetch(url, options),
                    new Promise((_, reject) => 
                        setTimeout(() => reject(new Error('Request timeout')), timeout)
                    )
                ]);
            }

            // Function to load main cryptocurrencies
            async function loadTopCryptos() {
                // Validate that cryptoList exists
                if (!cryptoList) {
                    console.error('cryptoList element not found');
                    return;
                }

                // Mostrar estado de carga
                cryptoList.innerHTML = '<div class="p-4 text-gray-400 flex items-center justify-center"><div class="animate-spin rounded-full h-6 w-6 border-b-2 border-blue-500 mr-2"></div>Loading cryptocurrencies...</div>';

                try {
                    // Load selected coins first with timeout
                    const selectedResponse = await fetchWithTimeout('/api/selected-coins', {}, 8000);
                    if (!selectedResponse.ok) {
                        throw new Error(`Error fetching selected coins: ${selectedResponse.status}`);
                    }
                    const selectedData = await selectedResponse.json();
                    
                    // Validate response structure
                    if (!selectedData || typeof selectedData !== 'object') {
                        throw new Error('Invalid response format for selected coins');
                    }
                    
                    const selectedCoins = selectedData.data || [];
                    console.log('Pre-loaded selected coins:', selectedCoins);

                    // Load all available coins with timeout
                    const response = await fetchWithTimeout('/api/coins', {}, 8000);
                    if (!response.ok) {
                        throw new Error(`Error HTTP: ${response.status} - ${response.statusText}`);
                    }
                    
                    const coins = await response.json();
                    
                    // Validate that coins is an array
                    if (!Array.isArray(coins)) {
                        throw new Error('Invalid response format: coins is not an array');
                    }
                    
                    console.log('Loaded all available coins:', coins.length);

                    // Validate that selectedCoins is an array
                    if (!Array.isArray(selectedCoins)) {
                        console.warn('selectedCoins is not an array, using empty array');
                        selectedCoins = [];
                    }

                    // Mark selected coins
                    const selectedIds = selectedCoins.map(coin => {
                        if (coin && coin.coin_id) {
                            return coin.coin_id;
                        }
                        console.warn('Invalid selected coin data:', coin);
                        return null;
                    }).filter(id => id !== null);
                    
                    console.log('Selected coin IDs:', selectedIds);

                    displayCryptoList(coins, selectedIds);
                } catch (error) {
                    console.error('Error loading cryptocurrencies:', error);
                    
                    let errorMessage = 'Error loading cryptocurrencies';
                    if (error.message === 'Request timeout') {
                        errorMessage = 'Request timeout - please check your connection and try again';
                    } else if (error.message.includes('fetch')) {
                        errorMessage = 'Network error - please check your connection';
                    } else {
                        errorMessage = `Error: ${error.message}`;
                    }
                    
                    if (cryptoList) {
                        cryptoList.innerHTML = `
                            <div class="p-4 text-red-500 text-center">
                                <div class="mb-2">${errorMessage}</div>
                                <button onclick="loadTopCryptos()" class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700 transition-colors">
                                    Retry
                                </button>
                            </div>
                        `;
                    }
                }
            }

            // Function to get selected coins
            async function getSelectedCoins() {
                try {
                    const response = await fetchWithTimeout('/api/selected-coins', {}, 8000);
                    if (!response.ok) {
                        throw new Error(`Error fetching selected coins: ${response.status} - ${response.statusText}`);
                    }
                    const data = await response.json();
                    
                    // Validate response structure
                    if (!data || typeof data !== 'object') {
                        console.warn('Invalid response format for selected coins, using empty array');
                        return [];
                    }
                    
                    console.log('Selected coins:', data); // Debug
                    
                    // Validate that data.data is an array
                    const selectedCoins = data.data || [];
                    if (!Array.isArray(selectedCoins)) {
                        console.warn('Selected coins data is not an array, using empty array');
                        return [];
                    }
                    
                    return selectedCoins;
                } catch (error) {
                    console.error('Error getting selected coins:', error);
                    if (error.message === 'Request timeout') {
                        console.warn('Timeout getting selected coins, using empty array');
                    }
                    return [];
                }
            }

            // Function to display cryptocurrency list
            async function displayCryptoList(coins, selectedIds) {
                try {
                    // Validate that cryptoList exists
                    if (!cryptoList) {
                        console.error('cryptoList element not found');
                        return;
                    }

                    // Validate that coins is a valid array
                    if (!Array.isArray(coins)) {
                        console.error('coins is not a valid array:', coins);
                        cryptoList.innerHTML = '<div class="p-4 text-red-500">Error: Invalid coins data</div>';
                        return;
                    }

                    // Validate that selectedIds is a valid array
                    if (!Array.isArray(selectedIds)) {
                        console.warn('selectedIds is not a valid array, using empty array');
                        selectedIds = [];
                    }

                    console.log('All coins:', coins); // Debug
                     console.log('Selected IDs:', selectedIds); // Debug

                    if (coins.length === 0) {
                        cryptoList.innerHTML = `
                            <div class="p-4 text-center">
                                <div class="text-gray-400 mb-4">No cryptocurrencies available</div>
                                <button onclick="retryLoadCryptos()" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-md cursor-pointer transition-colors">
                                    <i class="fas fa-redo mr-2"></i>Try Again
                                </button>
                            </div>
                        `;
                        return;
                    }

                    cryptoList.innerHTML = coins.map(coin => {
                        // Validate that each coin has the necessary properties
                        if (!coin || !coin.id || !coin.name || !coin.symbol) {
                            console.warn('Invalid coin data:', coin);
                            return '';
                        }

                        const isSelected = selectedIds.includes(coin.id);
                        console.log(`Processing coin ${coin.id}, selected: ${isSelected}`); // Additional debug
                        
                        // Escape special characters to avoid JSON errors
                        const coinData = {
                            id: coin.id,
                            name: coin.name,
                            symbol: coin.symbol,
                            image: coin.image || ''
                        };
                        
                        return `
                            <div class="flex items-center justify-between p-3 hover:bg-gray-700 rounded-md">
                                <div class="flex items-center space-x-3">
                                    <img src="${coin.image || ''}" alt="${coin.name}" class="w-8 h-8" onerror="this.style.display='none'">
                                    <div>
                                        <div class="font-medium">${coin.name}</div>
                                        <div class="text-sm text-gray-400">${coin.symbol.toUpperCase()}</div>
                                    </div>
                                </div>
                                <label class="relative inline-flex items-center cursor-pointer toggle-crypto"
                                    data-coin='${JSON.stringify(coinData)}'
                                    data-selected='${isSelected}'>
                                    <input type="checkbox" class="sr-only peer" ${isSelected ? 'checked' : ''}>
                                    <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 dark:peer-focus:ring-blue-800 rounded-full peer dark:bg-gray-700 peer-checked:after:translate-x-full rtl:peer-checked:after:-translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:start-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all dark:border-gray-600 peer-checked:bg-blue-600"></div>
                                </label>
                            </div>
                        `;
                    }).filter(html => html !== '').join('');
                } catch (error) {
                    console.error('Error displaying crypto list:', error);
                    if (cryptoList) {
                        cryptoList.innerHTML = '<div class="p-4 text-red-500">Error loading cryptocurrency list</div>';
                    }
                }
            }



            // Función para abrir el modal
            addCryptoBtn.addEventListener('click', () => {
                cryptoModal.classList.remove('hidden');
                cryptoModal.classList.add('flex');
                loadTopCryptos();
                setupCryptoToggleHandler(); // Set up event handler
            });

            // Función para cerrar el modal
            closeModal.addEventListener('click', () => {
                cryptoModal.classList.add('hidden');
                cryptoModal.classList.remove('flex');
                cryptoList.innerHTML = '<div class="p-4 text-gray-400">Loading cryptocurrencies...</div>';
            });

            // Function to close modal and reload
            document.getElementById('saveChanges').addEventListener('click', async () => {
                try {
                    const selectedCoins = await getSelectedCoins();
                    if (selectedCoins.length === 0) {
                        // If no coins are selected, activate BTC and ETH by default
                        await fetch('/api/selected-coins', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'Accept': 'application/json'
                            },
                            body: JSON.stringify({
                                coins: [{
                                    id: 'bitcoin',
                                    name: 'Bitcoin',
                                    symbol: 'BTC'
                                }, {
                                    id: 'ethereum',
                                    name: 'Ethereum',
                                    symbol: 'ETH'
                                }]
                            })
                        });
                    }
                } catch (error) {
                    console.error('Error saving default coins:', error);
                } finally {
                    // Cerrar modal y mostrar overlay de carga
                    cryptoModal.classList.add('hidden');
                    const loadingOverlay = document.getElementById('loadingOverlay');
                    loadingOverlay.classList.remove('hidden');
                    loadingOverlay.classList.add('flex');
                    
                    // Small delay so the overlay is visible before reload
                    setTimeout(() => {
                        window.location.reload();
                    }, 100);
                }
            });

            // Function to deactivate all cryptocurrencies
            document.getElementById('resetToDefaults').addEventListener('click', async () => {
                try {
                    // Get all currently selected coins
                    const selectedResponse = await fetch('/api/selected-coins');
                    if (!selectedResponse.ok) throw new Error('Error fetching selected coins');
                    const selectedData = await selectedResponse.json();
                    const currentSelected = selectedData.data || [];
                    
                    // Deactivate all selected coins
                    for (const coin of currentSelected) {
                        await fetch('/api/selected-coins', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'Accept': 'application/json'
                            },
                            body: JSON.stringify({ 
                                coins: [{
                                    id: coin.coin_id,
                                    name: coin.name,
                                    symbol: coin.symbol
                                }]
                            })
                        });
                    }
                    
                    // Reload cryptocurrency list in modal
                    loadTopCryptos();
                    
                    console.log('Reset completed: All cryptocurrencies have been deactivated');
                    
                } catch (error) {
                    console.error('Error during reset:', error);
                    alert('Error resetting cryptocurrencies. Please try again.');
                }
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

            // Evento para eliminar moneda
            document.querySelectorAll('.remove-crypto').forEach(button => {
                button.addEventListener('click', async (e) => {
                    e.stopPropagation();
                    const coin = JSON.parse(button.dataset.coin);
                    try {
                        const response = await fetch(`/api/selected-coins/${coin.id}`, {
                            method: 'DELETE',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                            }
                        });

                        if (response.ok) {
                            // Cambiar el botón a rojo y mostrar animación
                            button.classList.add('animate-pulse');
                            
                            // Reload page after a brief delay
                            setTimeout(() => {
                                window.location.reload();
                            }, 500);
                        }
                    } catch (error) {
                        console.error('Error removing cryptocurrency:', error);
                    }
                });
            });

            // Function to retry loading cryptocurrencies
                window.retryLoadCryptos = function() {
                    const cryptoList = document.getElementById('cryptoList');
                    if (cryptoList) {
                        cryptoList.innerHTML = '<div class="p-4 text-gray-400 flex items-center justify-center"><div class="animate-spin rounded-full h-6 w-6 border-b-2 border-blue-500 mr-2"></div>Retrying...</div>';
                        
                        // Retry loading after a short delay
                        setTimeout(() => {
                            loadTopCryptos();
                        }, 1000);
                    }
                };

                // Auto-retry mechanism for failed loads
                let retryCount = 0;
                const maxRetries = 3;
                const originalLoadTopCryptos = loadTopCryptos;
                
                loadTopCryptos = async function() {
                    try {
                        await originalLoadTopCryptos();
                        retryCount = 0; // Reset retry count on success
                    } catch (error) {
                        console.error('Error loading cryptocurrencies, attempt:', retryCount + 1);
                        
                        if (retryCount < maxRetries) {
                            retryCount++;
                            const cryptoList = document.getElementById('cryptoList');
                            if (cryptoList) {
                                cryptoList.innerHTML = `
                                    <div class="p-4 text-center">
                                        <div class="text-yellow-400 mb-2">Connection failed. Retrying automatically...</div>
                                        <div class="text-gray-400 text-sm mb-4">Attempt ${retryCount} of ${maxRetries}</div>
                                        <div class="flex items-center justify-center">
                                            <div class="animate-spin rounded-full h-6 w-6 border-b-2 border-yellow-500"></div>
                                        </div>
                                    </div>
                                `;
                            }
                            
                            // Auto-retry after delay (increasing delay with each retry)
                            setTimeout(() => {
                                loadTopCryptos();
                            }, 2000 * retryCount);
                        } else {
                            // Max retries reached, show manual retry option
                            const cryptoList = document.getElementById('cryptoList');
                            if (cryptoList) {
                                cryptoList.innerHTML = `
                                    <div class="p-4 text-center">
                                        <div class="text-red-400 mb-2">Failed to load cryptocurrencies</div>
                                        <div class="text-gray-400 text-sm mb-4">Please check your connection and try again</div>
                                        <button onclick="retryCount = 0; retryLoadCryptos()" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-md cursor-pointer transition-colors">
                                            <i class="fas fa-redo mr-2"></i>Try Again
                                        </button>
                                    </div>
                                `;
                            }
                        }
                    }
                };
            });
        </script>
    </body>
</html>