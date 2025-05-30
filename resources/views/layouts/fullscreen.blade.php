{{-- Untuk fullscreen, gunakan layout khusus atau custom styling --}}
<div class="min-h-screen bg-gray-50 dark:bg-gray-900">
    {{-- Header POS yang sticky --}}
    <div class="sticky top-0 z-50 bg-white dark:bg-gray-800 shadow-sm border-b border-gray-200 dark:border-gray-700">
        <div class="px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between h-16">
                <div class="flex items-center">
                    <h1 class="text-xl font-bold text-gray-900 dark:text-white">
                        <x-heroicon-o-building-storefront class="w-6 h-6 inline mr-2" />
                        Point of Sales
                    </h1>
                </div>
                
                {{-- Header Actions --}}
                <div class="flex items-center space-x-4">
                    <div class="text-sm text-gray-500 dark:text-gray-400">
                        {{ now()->format('d M Y - H:i') }}
                    </div>
                    
                    {{-- Exit Fullscreen Button --}}
                    <button 
                        onclick="window.history.back()" 
                        class="p-2 text-gray-400 hover:text-gray-600 dark:hover:text-gray-200 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700"
                        title="Kembali"
                    >
                        <x-heroicon-o-x-mark class="w-5 h-5" />
                    </button>
                </div>
            </div>
        </div>
    </div>

    {{-- Main Content --}}
    <div class="flex-1 px-4 sm:px-6 lg:px-8 py-6">
        <div class="flex flex-col xl:flex-row gap-6 h-full">
            {{-- Kiri: Daftar Produk --}}
            <div class="xl:w-2/3 w-full">
                <div class="bg-white dark:bg-gray-900 rounded-xl shadow-sm ring-1 ring-gray-950/5 dark:ring-white/10 h-full">
                    <div class="p-6">
                        <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Daftar Produk</h2>
                        {{ $this->table }}
                    </div>
                </div>
            </div>

            {{-- Kanan: Keranjang & Detail Transaksi --}}
            <div class="xl:w-1/3 w-full space-y-6">
                {{-- Customer Info --}}
                <div class="bg-white dark:bg-gray-900 rounded-xl shadow-sm ring-1 ring-gray-950/5 dark:ring-white/10 p-6">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Info Pelanggan</h3>
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Nama Pelanggan</label>
                            <input 
                                type="text" 
                                wire:model="customer_name" 
                                placeholder="Masukkan nama pelanggan (opsional)"
                                class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-white focus:border-primary-500 focus:ring-primary-500"
                            />
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Metode Pembayaran</label>
                            <select 
                                wire:model="payment_method" 
                                class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-white focus:border-primary-500 focus:ring-primary-500"
                            >
                                <option value="cash">Tunai</option>
                                <option value="card">Kartu</option>
                                <option value="transfer">Transfer</option>
                                <option value="e-wallet">E-Wallet</option>
                            </select>
                        </div>
                    </div>
                </div>

                {{-- Cart Items --}}
                <div class="bg-white dark:bg-gray-900 rounded-xl shadow-sm ring-1 ring-gray-950/5 dark:ring-white/10 p-6">
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Keranjang</h3>
                        @if (count($cart))
                            <span class="bg-primary-100 text-primary-800 text-xs font-medium px-2.5 py-0.5 rounded-full dark:bg-primary-900 dark:text-primary-300">
                                {{ count($cart) }} item
                            </span>
                        @endif
                    </div>

                    <div class="space-y-3 max-h-64 overflow-y-auto">
                        @forelse ($cart as $index => $item)
                            <div class="flex items-center justify-between p-3 bg-gray-50 dark:bg-gray-800 rounded-lg">
                                <div class="flex-1">
                                    <h4 class="font-medium text-gray-900 dark:text-white text-sm">{{ $item['name'] }}</h4>
                                    <p class="text-xs text-gray-500 dark:text-gray-400">
                                        Rp. {{ number_format($item['final_price'], 0, ',', '.') }}/item
                                    </p>
                                </div>
                                
                                <div class="flex items-center gap-2">
                                    <div class="flex items-center border border-gray-300 dark:border-gray-600 rounded-lg">
                                        <button 
                                            wire:click="updateCartQuantity({{ $item['id'] }}, {{ $item['qty'] - 1 }})"
                                            class="p-1 text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200"
                                        >
                                            <x-heroicon-o-minus class="w-4 h-4" />
                                        </button>
                                        
                                        <input 
                                            type="number" 
                                            wire:change="updateCartQuantity({{ $item['id'] }}, $event.target.value)"
                                            value="{{ $item['qty'] }}"
                                            min="1"
                                            class="w-12 text-center border-0 text-sm focus:ring-0 dark:bg-gray-800 dark:text-white"
                                        />
                                        
                                        <button 
                                            wire:click="updateCartQuantity({{ $item['id'] }}, {{ $item['qty'] + 1 }})"
                                            class="p-1 text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200"
                                        >
                                            <x-heroicon-o-plus class="w-4 h-4" />
                                        </button>
                                    </div>
                                    
                                    <button 
                                        wire:click="removeFromCart('{{ $item['id'] }}')" 
                                        class="p-1 text-red-500 hover:text-red-700"
                                        title="Hapus item"
                                    >
                                        <x-heroicon-o-trash class="w-4 h-4" />
                                    </button>
                                </div>
                            </div>
                            
                            <div class="text-right text-sm font-medium text-gray-900 dark:text-white">
                                Subtotal: Rp. {{ number_format($item['final_price'] * $item['qty'], 0, ',', '.') }}
                            </div>
                        @empty
                            <div class="text-center py-8">
                                <x-heroicon-o-shopping-cart class="w-12 h-12 mx-auto text-gray-400 mb-2" />
                                <p class="text-gray-500 dark:text-gray-400">Keranjang masih kosong</p>
                            </div>
                        @endforelse
                    </div>

                    @if (count($cart))
                        <div class="mt-4 pt-4 border-t border-gray-200 dark:border-gray-700">
                            <button 
                                wire:click="clearCart" 
                                class="w-full text-red-600 hover:text-red-800 dark:text-red-400 dark:hover:text-red-300 text-sm font-medium"
                            >
                                <x-heroicon-o-trash class="w-4 h-4 inline mr-1" />
                                Kosongkan Keranjang
                            </button>
                        </div>
                    @endif
                </div>

                {{-- Payment & Totals --}}
                @if (count($cart))
                    <div class="bg-white dark:bg-gray-900 rounded-xl shadow-sm ring-1 ring-gray-950/5 dark:ring-white/10 p-6">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Pembayaran</h3>
                        
                        {{-- Discount Input --}}
                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Diskon (Rp)</label>
                            <input 
                                type="number" 
                                wire:model="discount" 
                                min="0"
                                max="{{ $subtotal }}"
                                placeholder="0"
                                class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-white focus:border-primary-500 focus:ring-primary-500"
                            />
                        </div>

                        {{-- Totals --}}
                        <div class="space-y-2 mb-4 p-4 bg-gray-50 dark:bg-gray-800 rounded-lg">
                            <div class="flex justify-between text-sm">
                                <span class="text-gray-600 dark:text-gray-400">Subtotal</span>
                                <span class="font-medium text-gray-900 dark:text-white">Rp. {{ number_format($subtotal, 0, ',', '.') }}</span>
                            </div>
                            
                            @if($discount > 0)
                                <div class="flex justify-between text-sm">
                                    <span class="text-gray-600 dark:text-gray-400">Diskon</span>
                                    <span class="font-medium text-red-600">-Rp. {{ number_format($discount, 0, ',', '.') }}</span>
                                </div>
                            @endif
                            
                            <div class="flex justify-between text-sm">
                                <span class="text-gray-600 dark:text-gray-400">Pajak (10%)</span>
                                <span class="font-medium text-gray-900 dark:text-white">Rp. {{ number_format($tax, 0, ',', '.') }}</span>
                            </div>
                            
                            <div class="flex justify-between text-lg font-bold pt-2 border-t border-gray-200 dark:border-gray-600">
                                <span class="text-gray-900 dark:text-white">Total</span>
                                <span class="text-primary-600 dark:text-primary-400">Rp. {{ number_format($total, 0, ',', '.') }}</span>
                            </div>
                        </div>

                        {{-- Payment Input --}}
                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Jumlah Dibayar</label>
                            <input 
                                type="number" 
                                wire:model.live="paid_amount" 
                                min="0"  
                                placeholder="{{ $total }}"
                                class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-white focus:border-primary-500 focus:ring-primary-500"
                            />
                        </div>

                        {{-- Quick Payment Buttons --}}
                        @if($total > 0)
                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-4">Pembayaran Cepat</label>
                            
                            {{-- Uang Pas Button --}}
                            <div class="mb-4">
                                <button 
                                    wire:click="setExactPayment"
                                    class="py-2 px-2 rounded-lg text-xs font-medium bg-blue-100 text-blue-800 hover:bg-blue-200 transition-colors border border-blue-200"
                                >
                                    <x-heroicon-o-banknotes class="w-4 h-4 inline mr-2" />
                                    Uang Pas - Rp. {{ number_format($total, 0, ',', '.') }}
                                </button>
                            </div>
                            
                            {{-- Dynamic Quick Amount Buttons --}}
                            @php
                                $suggestions = $this->getPaymentSuggestions();
                                $commonAmounts = [10000, 20000, 25000, 50000, 100000, 200000];
                            @endphp
                            
                            <div class="space-y-3">
                                {{-- Suggested amounts based on total --}}
                                @if(count($suggestions) > 0)
                                <div>
                                    <div class="text-xs text-gray-500 dark:text-gray-400 mb-2">💡 Rekomendasi:</div>
                                    <div class="grid grid-cols-3 gap-2">
                                        @foreach(array_slice($suggestions, 0, 6) as $amount)
                                        <button 
                                            wire:click="setQuickPayment({{ $amount }})"
                                            class="py-2 px-2 rounded-lg text-xs font-medium bg-blue-100 text-blue-800 hover:bg-blue-200 transition-colors border border-blue-200"
                                        >
                                            {{ $this->formatCurrency($amount) }}
                                        </button>
                                        @endforeach
                                    </div>
                                </div>
                                @endif
                                
                                {{-- Common denominations --}}
                                <div>
                                    <div class="text-xs text-gray-500 dark:text-gray-400 mb-2">💵 Nominal Umum:</div>
                                    <div class="grid grid-cols-3 gap-2">
                                        @foreach($commonAmounts as $amount)
                                        <button 
                                            wire:click="setQuickPayment({{ $amount }})"
                                            @class([
                                                'py-2 px-2 rounded-lg text-xs font-medium transition-colors',
                                                'bg-gray-100 text-gray-800 hover:bg-gray-200 border border-gray-200' => $amount >= $total,
                                                'bg-gray-50 text-gray-400 cursor-not-allowed border border-gray-100' => $amount < $total,
                                            ])
                                            @if($amount < $total) disabled title="Nominal kurang dari total" @endif
                                        >
                                            {{ $this->formatCurrency($amount) }}
                                        </button>
                                        @endforeach
                                    </div>
                                </div>
                                
                                {{-- Large denominations --}}
                                @if($total > 200000)
                                <div>
                                    <div class="text-xs text-gray-500 dark:text-gray-400 mb-2">💰 Nominal Besar:</div>
                                    <div class="grid grid-cols-3 gap-2">
                                        @foreach([500000, 1000000, 2000000] as $amount)
                                        <button 
                                            wire:click="setQuickPayment({{ $amount }})"
                                            class="py-2 px-2 rounded-lg text-xs font-medium bg-purple-100 text-purple-800 hover:bg-purple-200 transition-colors border border-purple-200"
                                        >
                                            {{ $this->formatCurrency($amount) }}
                                        </button>
                                        @endforeach
                                    </div>
                                </div>
                                @endif
                            </div>
                            
                            {{-- Add/Reset Buttons --}}
                            <div class="mt-4 pt-3 border-t border-gray-200 dark:border-gray-600">
                                <div class="text-xs text-gray-500 dark:text-gray-400 mb-2">⚡ Aksi Cepat:</div>
                                <div class="grid grid-cols-4 gap-2">
                                    <button 
                                        wire:click="addToPayment(10000)"
                                        class="py-1.5 px-2 rounded text-xs font-medium bg-indigo-100 text-indigo-700 hover:bg-indigo-200 transition-colors"
                                    >
                                        +10K
                                    </button>
                                    <button 
                                        wire:click="addToPayment(25000)"
                                        class="py-1.5 px-2 rounded text-xs font-medium bg-indigo-100 text-indigo-700 hover:bg-indigo-200 transition-colors"
                                    >
                                        +25K
                                    </button>
                                    <button 
                                        wire:click="addToPayment(50000)"
                                        class="py-1.5 px-2 rounded text-xs font-medium bg-indigo-100 text-indigo-700 hover:bg-indigo-200 transition-colors"
                                    >
                                        +50K
                                    </button>
                                    <button 
                                        wire:click="resetPayment"
                                        class="py-1.5 px-2 rounded text-xs font-medium bg-red-100 text-red-700 hover:bg-red-200 transition-colors"
                                    >
                                        Reset
                                    </button>
                                </div>
                            </div>
                        </div>
                        @endif

                        {{-- Change --}}
                        @if($paid_amount && $paid_amount >= $total)
                            <div class="mb-4 p-3 bg-green-50 dark:bg-green-900/20 rounded-lg">
                                <div class="flex justify-between">
                                    <span class="text-sm font-medium text-green-800 dark:text-green-200">Kembalian</span>
                                    <span class="text-lg font-bold text-green-800 dark:text-green-200">Rp. {{ number_format($change, 0, ',', '.') }}</span>
                                </div>
                            </div>
                        @endif

                        {{-- Checkout Button --}}
                        <button 
                            wire:click="submit" 
                            @disabled(!$paid_amount || $paid_amount < $total)
                            @class([
                                'w-full font-medium py-3 px-4 rounded-lg transition-colors',
                                'bg-primary-600 hover:bg-primary-700 text-white' => $paid_amount && $paid_amount >= $total,
                                'bg-gray-400 cursor-not-allowed text-gray-200' => !$paid_amount || $paid_amount < $total,
                            ])
                        >
                            <x-heroicon-o-credit-card class="w-5 h-5 inline mr-2" />
                            @if(!$paid_amount || $paid_amount < $total)
                                @if(!$paid_amount)
                                    Masukkan Jumlah Pembayaran
                                @else
                                    Jumlah Pembayaran Kurang (Rp. {{ number_format($total - $paid_amount, 0, ',', '.') }})
                                @endif
                            @else
                                Proses Checkout
                            @endif
                        </button>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>