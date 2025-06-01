<?php

namespace App\Filament\Resources;

use Filament\Forms;
use Filament\Tables;
use App\Models\Supplier;
use Filament\Forms\Form;
use Filament\Tables\Table;
use Filament\Resources\Resource;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Illuminate\Database\Eloquent\Builder;
use App\Filament\Resources\SupplierResource\Pages;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Filament\Resources\SupplierResource\RelationManagers;
use Filament\Tables\Columns\TextColumn;

class SupplierResource extends Resource
{
    protected static ?string $model = Supplier::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?string $navigationGroup = 'Master Data';

    protected static ?string $navigationLabel = 'Supplier';

    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery();

        return auth()->user()->isSuperAdmin()
            ? $query
            : $query->where('store_id', auth()->user()->store_id);
    }

    public static function form(Form $form): Form
    {
        // dd(auth()->user()->store_id);
        return $form
            ->schema([
                Hidden::make('store_id')
                    ->default(fn () => auth()->user()->store_id),
                    
                TextInput::make('supplier_code')
                    ->readOnly()
                    ->default(fn () => 'S' . date('Ym') . str_pad(Supplier::max('id') + 1, 3, '0', STR_PAD_LEFT))
                    ->required(),

                TextInput::make('name')
                    ->minLength(5)
                    ->maxLength(100)
                    ->required(),

                TextInput::make('phone_number')
                    ->minLength(10)
                    ->maxLength(15)
                    ->required()
                    ->numeric(),

                Textarea::make('address')
                    ->required(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('supplier_code')->label('Kode Supplier')->badge(),
                TextColumn::make('name')->label('Nama'),
                TextColumn::make('address')->label('Alamat'),
                TextColumn::make('phone_number')->label('No. Telp'),
                TextColumn::make('created_at')->label('Tgl. dibuat'),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListSuppliers::route('/'),
            'create' => Pages\CreateSupplier::route('/create'),
            'edit' => Pages\EditSupplier::route('/{record}/edit'),
        ];
    }
}
