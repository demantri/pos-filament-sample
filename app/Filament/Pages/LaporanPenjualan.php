<?php

namespace App\Filament\Pages;

use App\Models\Product;
use Filament\Pages\Page;
use App\Models\Transaction;
use App\Exports\ProductsExport;
use Barryvdh\DomPDF\Facade\Pdf;
use Filament\Tables\Actions\Action;
use Filament\Tables\Filters\Filter;
use Maatwebsite\Excel\Facades\Excel;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Contracts\HasTable;
use Filament\Forms\Components\DatePicker;
use Illuminate\Database\Eloquent\Collection;
use Filament\Tables\Concerns\InteractsWithTable;
use Illuminate\Contracts\Database\Eloquent\Builder;

class LaporanPenjualan extends Page implements HasTable
{
    use InteractsWithTable;
    
    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static string $view = 'filament.pages.laporan-penjualan';

    protected static ?string $navigationGroup = 'Laporan';

    protected static ?string $title = 'Penjualan Harian';

    public function getTableFilters(): array
    {
        return [
            Filter::make('created_at')
                ->form([
                    DatePicker::make('tanggal')
                        ->label('Pilih Tanggal'),
                ])
                ->query(function (Builder $query, array $data): Builder {
                    return $query
                        ->when($data['tanggal'], fn ($q) =>
                            $q->whereDate('created_at', $data['tanggal'])
                        );
                })
                ->label('Filter Harian'),
        ];
    }

    public function getTableQuery(): Builder
    {
        return Transaction::query()
            ->where('store_id', auth()->user()->store_id);
    }

    public function getTableColumns(): array
    {
        return [
            TextColumn::make('transaction_number')
                ->sortable()
                ->searchable()
                ->weight('bold')
                ->label('Transaksi ID'),

            TextColumn::make('created_at')
                ->sortable()
                ->searchable()
                ->label('Tgl. Transaksi'),

            TextColumn::make('customer_name')
                ->label('Pembeli'),

            TextColumn::make('subtotal')
                ->formatStateUsing(fn ($record) => 'Rp. ' . number_format($record->subtotal, 0, ',', '.'))
                ->label('Subtotal'),

            TextColumn::make('discount')
                ->formatStateUsing(fn ($record) => 'Rp. ' . number_format($record->discount, 0, ',', '.'))
                ->label('Diskon'),

            TextColumn::make('tax')
                ->formatStateUsing(fn ($record) => 'Rp. ' . number_format($record->tax, 0, ',', '.'))
                ->label('Tax'),

            TextColumn::make('total')
                ->formatStateUsing(fn ($record) => 'Rp. ' . number_format($record->total, 0, ',', '.'))
                ->label('Total'),

            TextColumn::make('payment_method')
                // ->formatStateUsing(fn ($record) => 'Rp. ' . number_format($record->payment_method, 0, ',', '.'))
                ->searchable()
                ->label('Metode Pembayaran'),

            TextColumn::make('paid_amount')
                ->formatStateUsing(fn ($record) => 'Rp. ' . number_format($record->paid_amount, 0, ',', '.'))
                ->label('Nominal Bayar'),

            TextColumn::make('change')
                ->formatStateUsing(fn ($record) => 'Rp. ' . number_format($record->change, 0, ',', '.'))
                ->label('Kembali'),

            TextColumn::make('status')
                ->label('Status')
                ->badge()
                ->searchable()
                ->color(fn ($record) => $record->status == 'completed' ? 'success' : 'warning'),
        ];
    }
}
