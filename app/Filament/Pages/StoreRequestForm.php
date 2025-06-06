<?php

namespace App\Filament\Pages;

use App\Models\StoreRequest;
use Filament\Forms;
use Filament\Pages\Page;
use Filament\Forms\Form;
use Filament\Notifications\Notification;

class StoreRequestForm extends Page
{
    protected static ?string $title = 'Pengajuan Toko Baru';

    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static string $view = 'filament.pages.store-request-form';

    protected static ?string $navigationGroup = 'Kelola Toko';

    // protected static ?string $navigationLabel = 'Toko Baru';


    public ?array $data = [];

    public static function canAccess(): bool
    {
        return auth()->user()?->isSuperAdmin();
    }

    public function mount(): void
    {
        $this->form->fill([]);
    }

    public function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('store_name')->label('Nama Toko')->required(),
            Forms\Components\TextInput::make('owner_name')->label('Nama Pemilik')->required(),
            Forms\Components\TextInput::make('email')->label('Email')->email()->required(),
            Forms\Components\TextInput::make('phone')->label('No. Telepon')->required(),
            Forms\Components\Textarea::make('address')->label('Alamat')->required(),
        ])->statePath('data');
    }

    public function submit()
    {
        StoreRequest::create($this->data);

        Notification::make()
            ->title('Pengajuan berhasil dikirim!')
            ->success()
            ->send();

        $this->form->fill();
    }
}