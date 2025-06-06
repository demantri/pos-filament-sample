<?php

namespace App\Filament\Pages;

use App\Models\Product;
use App\Models\TransactionHistory;
use Filament\Pages\Page;
use Illuminate\Database\Eloquent\Builder;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Notifications\Notification;
use GuzzleHttp\Psr7\Request;

class Stok extends Page implements HasTable
{
    use InteractsWithTable;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static string $view = 'filament.pages.stok';

    protected static ?string $title = 'Manage Stok';

    protected static ?string $navigationGroup = 'Transaksi';

    // Form properties
    public $product_id;
    public $tipe;
    public $jumlah;

    // Query for product table
    public function getTableQuery(): Builder
    {
        $store_id = auth()->user()->store_id;
        return Product::query()
            ->where('store_id', $store_id);
    }

    public function getTableColumns(): array
    {
        return [
            TextColumn::make('name')->label('Nama Produk'),
            TextColumn::make('qty')->label('Stok'),
        ];
    }

    // Submit form
    public function submit()
    {
        $this->validate([
            'product_id' => 'required|exists:products,id',
            'tipe' => 'required|in:masuk,keluar',
            'jumlah' => 'required|numeric|min:1',
        ]);

        $product = Product::find($this->product_id);
        $firstStok = is_null($product->qty) == 0;

        if ($this->tipe === 'masuk') {
            $lastStok = $firstStok + $this->jumlah;
        } else {
            $lastStok = max(0, $firstStok - $this->jumlah);
        }

        // Simpan ke TransactionHistory
        TransactionHistory::create([
            'store_id' => auth()->user()->store_id,
            'transaction_id' => null,
            'product_id' => $product->id,
            'first_stok' => $firstStok,
            'last_stok' => $lastStok,
            'type' => $this->tipe,
            'source' => 'manual_input', // Ini penanda input manual
        ]);

        // Update stok produk
        $product->qty = $lastStok;
        $product->save();

        Notification::make()
            ->title('Stok berhasil diperbarui.')
            ->success()
            ->send();

        // Reset form
        $this->reset(['product_id', 'tipe', 'jumlah']);
    }
}
