<?php

namespace App\Filament\Pages;

use App\Models\Product;
use Filament\Pages\Page;
use App\Exports\ProductsExport;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Models\TransactionHistory;
use Filament\Tables\Actions\Action;
use Maatwebsite\Excel\Facades\Excel;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Contracts\HasTable;
use Illuminate\Database\Eloquent\Builder;
use Filament\Tables\Concerns\InteractsWithTable;

class LaporanStok extends Page implements HasTable
{
    use InteractsWithTable;
    
    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static string $view = 'filament.pages.laporan-stok';

    protected static ?string $navigationGroup = 'Laporan';

    protected static ?string $title = 'Laporan Stok';

    public function getTableHeaderActions(): array 
    {
        return [
            Action::make('Export excel')
                ->icon('heroicon-o-arrow-down-tray')
                ->action(function () {
                    return Excel::download(new ProductsExport, 'produk.xlsx');
                }),

            Action::make('Export Pdf')
                ->icon('heroicon-o-document')
                ->action(function () {
                    $data = Product::all();
                    $pdf = Pdf::loadView('exports.products-pdf', ['products' => $data]);
                    return response()->streamDownload(fn () => print($pdf->stream()), 'produk.pdf');
                }),
        ];
    }

    public function getTableQuery(): Builder
    {
        $store_id = auth()->user()->store_id;

        return TransactionHistory::query()
            ->selectRaw("transaction_histories.*, transaction_items.quantity")
            ->join('transaction_items', function ($join) {
                $join->on('transaction_items.transaction_id', '=', 'transaction_histories.transaction_id')
                    ->on('transaction_items.product_id', '=', 'transaction_histories.product_id');
            })
            ->where('transaction_histories.store_id', $store_id)
            ->orderByDesc('transaction_histories.id');
    }

    public function getTableColumns(): array
    {
        return [
            TextColumn::make('product.name')
                ->label('Produk'),

            TextColumn::make('type')
                ->badge()
                ->color(fn ($record) => $record->type == 'keluar' ? 'warning' : 'success')
                ->label('Tipe'),

            TextColumn::make('quantity')
                ->badge()
                ->color('success')
                // ->color(fn ($record) => $record->qty < 10 ? 'warning' : ($record->qty <= 0 ? 'danger' : 'success'))
                ->label('Qty'),
            
                TextColumn::make('first_stok')
                ->badge()
                ->color('success')
                // ->color(fn ($record) => $record->qty < 10 ? 'warning' : ($record->qty <= 0 ? 'danger' : 'success'))
                ->label('Stok awal'),
            
            TextColumn::make('last_stok')
                ->badge()
                ->color('info')
                // ->color(fn ($record) => $record->qty < 10 ? 'warning' : ($record->qty <= 0 ? 'danger' : 'success'))
                ->label('Stok akhir'),
            
            TextColumn::make('created_at')
                ->sortable()
                ->label('Tgl. dibuat'),
        ];
    }
}
