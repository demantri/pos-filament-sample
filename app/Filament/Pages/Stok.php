<?php

namespace App\Filament\Pages;

use App\Models\Product;
use Filament\Pages\Page;
use Filament\Tables\Contracts\HasTable;
use Illuminate\Database\Eloquent\Builder;
use Filament\Tables\Concerns\InteractsWithTable;

class Stok extends Page implements HasTable
{
    use InteractsWithTable;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static string $view = 'filament.pages.stok';

    protected static ?string $title = 'Stok Masuk/Keluar';

    protected static ?string $navigationGroup = 'Atur Stok';

    public function getTableQuery(): Builder
    {
        return Product::query();
    }

    public function getTableColumns(): array
    {
        return [];
    }
}
