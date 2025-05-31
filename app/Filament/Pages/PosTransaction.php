<?php

namespace App\Filament\Pages;

use Carbon\Carbon;
use Filament\Tables;
use App\Models\Product;
use Filament\Pages\Page;
use App\Models\Transaction;
use App\Models\TransactionItem;
use App\Models\TransactionHistory;
use Illuminate\Support\Facades\DB;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ViewColumn;
use Filament\Tables\Contracts\HasTable;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\ImageColumn;
use Illuminate\Database\Eloquent\Builder;
use Filament\Tables\Concerns\InteractsWithTable;

class PosTransaction extends Page implements HasTable
{
    use InteractsWithTable;

    protected static ?string $navigationIcon = 'heroicon-o-shopping-cart';

    protected static string $view = 'filament.pages.point-of-sales';

    protected static ?string $title = 'Point of Sales';

    public $cart = [];
    public $subtotal = 0;
    public $discount = 0;
    public $tax = 0;
    public $total = 0;
    public $customer_name = '';
    public $payment_method = 'cash';
    public $paid_amount = 0;  // Set default ke 0
    public $change = 0;

    public $monthlyTotal = 0;
    public $dailyTotal = 0;
    public $bestSeller = '-';
    public $emptyProducts = 0;

    protected $listeners = [
        'updateCart' => 'updateTotals'
    ];

    public function mount()
    {
        $this->paid_amount = 0; // Pastikan default value
        $this->updateTotals();
        $this->loadSummary();
    }

    public function loadSummary()
    {
        $storeId = auth()->user()->store_id;

        $today = Carbon::today();
        $startOfMonth = Carbon::now()->startOfMonth();

        // Total transaksi bulan ini
        $this->monthlyTotal = Transaction::where('status', 'completed')
            ->where('store_id', $storeId)
            ->whereBetween('created_at', [$startOfMonth, now()])
            ->sum('total');

        // Total transaksi hari ini
        $this->dailyTotal = Transaction::where('status', 'completed')
            ->where('store_id', $storeId)
            ->whereDate('created_at', $today)
            ->sum('total');

        // Produk best seller (dari transaksi detail)
        $bestSeller = TransactionItem::select('product_id')
            ->selectRaw('SUM(quantity) as total_sold')
            ->join('transactions', 'transaction_items.transaction_id', '=', 'transactions.id')
            ->where('transactions.store_id', $storeId) // filter store
            ->groupBy('product_id')
            ->orderByDesc('total_sold')
            ->first();

        $this->bestSeller = $bestSeller ? Product::find($bestSeller->product_id)?->name ?? '-' : '-';

        // Produk yang habis
        $this->emptyProducts = Product::where('store_id', $storeId)
            ->where('qty', '<=', 0)
            ->count();
    }

    public function getTableQuery(): Builder
    {
        return Product::query()
            ->where('store_id', auth()->user()->store_id)
            ->where('qty', '>', 0);
    }

    public function getTableColumns(): array
    {
        return [
            ImageColumn::make('image_path')
                ->label('Gambar')
                ->disk('public')
                ->circular()
                ->height(60)
                ->width(60)
                ->defaultImageUrl('/images/no-image.png')
                ->action(function ($record) {
                    $this->addToCart($record->id);
                })
                ->extraAttributes(['class' => 'cursor-pointer hover:opacity-75 transition-opacity']),

            TextColumn::make('name')
                ->label('Produk')
                ->sortable()
                ->searchable()
                ->wrap()
                ->action(function ($record) {
                    $this->addToCart($record->id);
                })
                ->extraAttributes(['class' => 'cursor-pointer hover:text-primary-600 transition-colors font-medium']),

            TextColumn::make('category.name')
                ->label('Kategori')
                ->sortable()
                ->searchable()
                ->toggleable(),

            TextColumn::make('qty')
                ->label('Stok')
                ->sortable()
                ->badge()
                ->color(fn ($record) => $record->qty < 10 ? 'warning' : ($record->qty <= 0 ? 'danger' : 'success'))
                ->formatStateUsing(fn ($record) => $record->qty <= 0 ? 'Habis' : $record->qty),

            TextColumn::make('price')
                ->label('Harga')
                ->formatStateUsing(fn ($record) => 'Rp. ' . number_format($record->price, 0, ',', '.'))
                ->sortable()
                ->weight('bold')
                ->color('primary'),
        ];
    }

    public function addToCart($id)
    {
        $product = Product::findOrFail($id);
        
        if ($product->qty <= 0) {
            Notification::make()
                ->title('Stok Habis')
                ->body('Produk ' . $product->name . ' sudah habis.')
                ->danger()
                ->duration(5000)
                ->send();
            return;
        }

        $existing = collect($this->cart)->firstWhere('id', $id);
        $currentQtyInCart = $existing ? $existing['qty'] : 0;

        if ($currentQtyInCart >= $product->qty) {
            Notification::make()
                ->title('Stok Tidak Cukup')
                ->body('Stok produk ' . $product->name . ' hanya tersisa ' . $product->qty . ' item.')
                ->warning()
                ->duration(5000)
                ->send();
            return;
        }

        if ($existing) {
            foreach ($this->cart as &$item) {
                if ($item['id'] == $id) {
                    $item['qty'] += 1;
                    break;
                }
            }
        } else {
            $this->cart[] = [
                'id' => $product->id,
                'name' => $product->name,
                'qty' => 1,
                'price' => $product->price,
                'discount' => $product->discount ?? 0,
                'final_price' => $product->price - ($product->discount ?? 0),
                'image' => $product->image_path,
            ];
        }

        $this->updateTotals();
        
        Notification::make()
            ->title('Berhasil Ditambahkan')
            ->body($product->name . ' ditambahkan ke keranjang.')
            ->success()
            ->duration(5000)
            ->send();
        
        $this->dispatch('refreshTable');
    }

    public function updateCartQuantity($id, $qty)
    {
        $product = Product::findOrFail($id);
        
        if ($qty <= 0) {
            $this->removeFromCart($id);
            return;
        }

        if ($qty > $product->qty) {
            Notification::make()
                ->title('Stok Tidak Cukup')
                ->body('Stok produk hanya tersisa ' . $product->qty . ' item.')
                ->warning()
                ->send();
            return;
        }

        foreach ($this->cart as &$item) {
            if ($item['id'] == $id) {
                $item['qty'] = $qty;
                break;
            }
        }

        $this->updateTotals();
    }

    public function removeFromCart($id)
    {
        $this->cart = array_filter($this->cart, fn ($item) => $item['id'] != $id);
        $this->cart = array_values($this->cart);
        $this->updateTotals();
        
        Notification::make()
            ->title('Item Dihapus')
            ->body('Item berhasil dihapus dari cart.')
            ->success()
            ->send();
    }

    public function clearCart()
    {
        $this->cart = [];
        $this->customer_name = '';
        $this->paid_amount = 0; // Reset ke 0, bukan string kosong
        $this->updateTotals();
        
        Notification::make()
            ->title('Cart Dikosongkan')
            ->body('Semua item berhasil dihapus dari cart.')
            ->success()
            ->send();
    }

    public function updateTotals()
    {
        $this->subtotal = collect($this->cart)->sum(fn ($item) => $item['final_price'] * $item['qty']);
        
        // Pastikan discount adalah numeric
        $discount = is_numeric($this->discount) ? (float) $this->discount : 0;
        
        // Hitung tax berdasarkan persentase (10%)
        $this->tax = ($this->subtotal - $discount) * 0.1;
        
        $this->total = $this->subtotal + $this->tax - $discount;
        
        // Pastikan paid_amount adalah numeric untuk perhitungan change
        $paidAmount = is_numeric($this->paid_amount) ? (float) $this->paid_amount : 0;
        $this->change = max(0, $paidAmount - $this->total);
    }

    // Method untuk handle input kosong pada paid_amount
    public function updatedPaidAmount($value)
    {
        // Handle empty string atau null
        if ($value === '' || $value === null) {
            $this->paid_amount = 0;
        } else {
            // Pastikan value adalah numeric dan tidak negatif
            $this->paid_amount = max(0, (float) $value);
        }
        
        $this->updateTotals();
    }

    // Method untuk handle input kosong pada discount
    public function updatedDiscount($value)
    {
        // Handle empty string atau null
        if ($value === '' || $value === null) {
            $this->discount = 0;
        } else {
            // Pastikan value adalah numeric dan tidak negatif, tidak melebihi subtotal
            $this->discount = max(0, min((float) $value, $this->subtotal));
        }
        
        $this->updateTotals();
    }

    public function submit()
    {
        // dd(auth()->user()->id);
        // Validasi
        if (empty($this->cart)) {
            Notification::make()
                ->title('Error')
                ->body('Cart masih kosong.')
                ->danger()
                ->send();
            return;
        }

        // Pastikan paid_amount adalah numeric untuk validasi
        $paidAmount = is_numeric($this->paid_amount) ? (float) $this->paid_amount : 0;
        
        if ($paidAmount < $this->total) {
            Notification::make()
                ->title('Pembayaran Gagal')
                ->body('Jumlah pembayaran kurang dari total.')
                ->danger()
                ->send();
            return;
        }
        // dd($this->total, $this->customer_name, $this->payment_method, $this->change, $this->cart);
        try {
            DB::transaction(function () use ($paidAmount) {
                $transaction = Transaction::create([
                    'store_id' => auth()->user()->store_id,
                    'user_id' => auth()->user()->id,
                    'transaction_number' => 'TRX-' . date('Ymd') . str_pad(Transaction::whereDate('created_at', today())->count() + 1, 4, '0', STR_PAD_LEFT),
                    'customer_name' => $this->customer_name ?: 'Walk-in Customer',
                    'subtotal' => $this->subtotal,
                    'discount' => is_numeric($this->discount) ? (float) $this->discount : 0,
                    'tax' => $this->tax,
                    'total' => $this->total,
                    'payment_method' => $this->payment_method,
                    'paid_amount' => $paidAmount,
                    'change' => $this->change,
                    'status' => 'completed',
                ]);

                foreach ($this->cart as $item) {
                    TransactionItem::create([
                        'transaction_id' => $transaction->id,
                        'product_id' => $item['id'],
                        'quantity' => $item['qty'],
                        'price' => $item['price'],
                        'discount' => $item['discount'],
                        'subtotal' => $item['final_price'] * $item['qty'],
                    ]);

                    $product = Product::find($item['id']);
                    $product->decrement('qty', $item['qty']);

                    TransactionHistory::create([
                        'store_id' => auth()->user()->store_id,
                        'transaction_id' => $transaction->id,
                        'product_id' => $item['id'],
                        'first_stok' => $product->qty + $item['qty'],
                        'last_stok' => $product->qty,
                        'type' => 'keluar',
                    ]);
                }
            });

            $this->clearCart();

            $this->loadSummary();
            
            Notification::make()
                ->title('Transaksi Berhasil')
                ->body('Transaksi berhasil disimpan.')
                ->success()
                ->send();

        } catch (\Exception $e) {
            Notification::make()
                ->title('Error')
                ->body('Terjadi kesalahan: ' . $e->getMessage())
                ->danger()
                ->send();
        }
    }

    public function getTableRecordsPerPageSelectOptions(): array
    {
        return [10, 25, 50, 100];
    }

    /**
     * Set pembayaran dengan nominal exact (uang pas)
     */
    public function setExactPayment()
    {
        $this->paid_amount = $this->total;
        $this->updateTotals();
        
        Notification::make()
            ->title('Uang Pas')
            ->body('Pembayaran diset sesuai total: Rp. ' . number_format($this->total, 0, ',', '.'))
            ->success()
            ->duration(2000)
            ->send();
    }

    /**
     * Set pembayaran dengan nominal cepat
     */
    public function setQuickPayment($amount)
    {
        if ($amount < $this->total) {
            Notification::make()
                ->title('Nominal Kurang')
                ->body('Nominal Rp. ' . number_format($amount, 0, ',', '.') . ' kurang dari total pembayaran.')
                ->warning()
                ->duration(5000)
                ->send();
            return;
        }
        
        $this->paid_amount = $amount;
        $this->updateTotals();
        
        $change = $amount - $this->total;
        
        Notification::make()
            ->title('Pembayaran Diset')
            ->body('Dibayar: Rp. ' . number_format($amount, 0, ',', '.') . 
                ($change > 0 ? ' | Kembalian: Rp. ' . number_format($change, 0, ',', '.') : ''))
            ->success()
            ->duration(5000)
            ->send();
    }

    /**
     * Tambah nominal ke pembayaran saat ini
     */
    public function addToPayment($amount)
    {
        $currentPayment = is_numeric($this->paid_amount) ? (float) $this->paid_amount : 0;
        $this->paid_amount = $currentPayment + $amount;
        $this->updateTotals();
        
        Notification::make()
            ->title('Nominal Ditambah')
            ->body('Ditambah Rp. ' . number_format($amount, 0, ',', '.') . 
                ' | Total: Rp. ' . number_format($this->paid_amount, 0, ',', '.'))
            ->info()
            ->duration(2000)
            ->send();
    }

    /**
     * Reset pembayaran ke 0
     */
    public function resetPayment()
    {
        $this->paid_amount = 0;
        $this->updateTotals();
        
        Notification::make()
            ->title('Pembayaran Direset')
            ->body('Jumlah pembayaran dikembalikan ke 0.')
            ->info()
            ->duration(2000)
            ->send();
    }

    /**
     * Method untuk mendapatkan suggestion nominal berdasarkan total
     */
    public function getPaymentSuggestions()
    {
        $total = $this->total;
        $suggestions = [];
        
        // Nominal dasar
        $amounts = [10000, 20000, 25000, 50000, 75000, 100000, 150000, 200000, 250000, 500000, 1000000];
        
        // Filter yang >= total
        $validAmounts = array_filter($amounts, fn($amount) => $amount >= $total);
        
        // Tambahkan beberapa nominal di atas total
        $roundedUp = ceil($total / 10000) * 10000; // Bulatkan ke 10rb terdekat
        if (!in_array($roundedUp, $validAmounts)) {
            $validAmounts[] = $roundedUp;
        }
        
        // Bulatkan ke 25rb terdekat
        $roundedUp25 = ceil($total / 25000) * 25000;
        if (!in_array($roundedUp25, $validAmounts)) {
            $validAmounts[] = $roundedUp25;
        }
        
        sort($validAmounts);
        return array_slice($validAmounts, 0, 6); // Ambil 6 suggestion teratas
    }

    /**
     * Method untuk cek apakah nominal cukup
     */
    public function isAmountSufficient($amount)
    {
        return $amount >= $this->total;
    }

    /**
     * Method untuk format currency display
     */
    public function formatCurrency($amount)
    {
        if ($amount >= 1000000) {
            return number_format($amount / 1000000, 1) . 'jt';
        } elseif ($amount >= 1000) {
            return number_format($amount / 1000, 0) . 'k';
        }
        return number_format($amount, 0);
    }
}