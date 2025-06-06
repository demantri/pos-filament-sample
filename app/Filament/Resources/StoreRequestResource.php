<?php

namespace App\Filament\Resources;

use Filament\Forms;
use Filament\Tables;
use App\Models\Store;
use Filament\Forms\Form;
use Filament\Tables\Table;
use App\Models\StoreRequest;
use Filament\Resources\Resource;
use Filament\Tables\Actions\Action;
use Illuminate\Foundation\Auth\User;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Tables\Actions\ViewAction;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Filament\Resources\StoreRequestResource\Pages;
use App\Filament\Resources\StoreRequestResource\RelationManagers;

class StoreRequestResource extends Resource
{
    protected static ?string $model = StoreRequest::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?string $navigationLabel = 'Approval Toko Baru';

    protected static ?string $navigationGroup = 'Kelola Toko';

    public static function canAccess(): bool
    {
        return auth()->user()?->isSuperAdmin();
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('store_name')->disabled(),
                TextInput::make('owner_name')->disabled(),
                TextInput::make('email')->disabled(),
                TextInput::make('phone')->disabled(),
                Textarea::make('address')->disabled(),
                Select::make('status')
                    ->options([
                        'pending' => 'Pending',
                        'approved' => 'Approved',
                        'rejected' => 'Rejected',
                    ]),

            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('store_name'),
                Tables\Columns\TextColumn::make('owner_name'),
                Tables\Columns\TextColumn::make('email'),
                Tables\Columns\TextColumn::make('status')->badge(),
            ])
            ->filters([
                //
            ])
            ->actions([
                ViewAction::make(),
                Action::make('approve')
                    ->label('Approve')
                    ->color('success')
                    ->visible(fn ($record) => $record->status === 'pending')
                    ->action(function (StoreRequest $record) {
                        // Buat Store dan User
                        $store = Store::create([
                            'name' => $record->store_name,
                            'address' => $record->address,
                            'phone_number' => $record->phone,
                        ]);

                        User::create([
                            'name' => $record->owner_name,
                            'email' => $record->email,
                            'password' => bcrypt('password'), // default password
                            'store_id' => $store->id,
                            'role' => 'store_user',
                        ]);

                        $record->update(['status' => 'approved']);

                        Notification::make()
                            ->title('Pengajuan berhasil disetujui.')
                            ->success()
                            ->send();
                    }),
                // Tables\Actions\DeleteAction::make(),
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
            'index' => Pages\ListStoreRequests::route('/'),
            'create' => Pages\CreateStoreRequest::route('/create'),
            'edit' => Pages\EditStoreRequest::route('/{record}/edit'),
        ];
    }
}
