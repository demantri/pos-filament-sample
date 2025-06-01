<?php

namespace App\Filament\Resources;

use Filament\Forms;
use Filament\Tables;
use App\Models\Product;
use Filament\Forms\Form;
use Filament\Tables\Table;
use Filament\Resources\Resource;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\FileUpload;
use Illuminate\Database\Eloquent\Builder;
use App\Filament\Resources\ProductResource\Pages;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Filament\Resources\ProductResource\RelationManagers;
use Filament\Forms\Components\Textarea;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;

class ProductResource extends Resource
{
    protected static ?string $model = Product::class;

    protected static ?string $navigationIcon = 'heroicon-o-list-bullet';

    protected static ?string $navigationGroup = 'Master Data';

    protected static ?string $navigationLabel = 'Produk';

    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery();

        return auth()->user()->isSuperAdmin()
            ? $query
            : $query->where('store_id', auth()->user()->store_id);
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Hidden::make('store_id')
                    ->default(fn () => auth()->user()->store_id),

                TextInput::make('name')
                    ->required(),

                Select::make('category_id')
                    // ->relationship('category', 'name')
                    ->relationship(
                        'category',
                        'name',
                        fn ($query) => $query->where('store_id', auth()->user()->store_id)
                    )
                    ->required(),
                
                Textarea::make('description')
                    ->required(),

                FileUpload::make('image_path')
                    ->disk('public')
                    ->directory('product')
                    ->image()
                    ->imagePreviewHeight(200)
                    ->required(),

                TextInput::make('price')
                    ->numeric()
                    ->required(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('Nama'),
                
                TextColumn::make('category.name')
                    ->label('Kategori'),
                
                TextColumn::make('description')
                    ->label('Deskripsi'),
                
                TextColumn::make('price')
                    ->money('IDR')
                    ->label('Harga'),

                ImageColumn::make('image_path')
                    ->label('Gambar')
                    ->disk('public')
                    ->height(60)
                    ->width(60)
                    ->circular(),
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
            'index' => Pages\ListProducts::route('/'),
            'create' => Pages\CreateProduct::route('/create'),
            'edit' => Pages\EditProduct::route('/{record}/edit'),
        ];
    }
}
