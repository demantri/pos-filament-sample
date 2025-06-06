<?php

namespace App\Filament\Pages;

use Filament\Forms\Form;
use Filament\Pages\Page;
use Filament\Forms\Components\Card;
use Illuminate\Support\Facades\Hash;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Forms\Components\Actions\Action;
use Filament\Forms\Concerns\InteractsWithForms;

class ChangePassword extends Page implements HasForms
{
    use InteractsWithForms;

    // testing update

    protected static ?string $navigationIcon = 'heroicon-o-lock-closed';
    protected static string $view = 'filament.pages.change-password';
    // protected static ?string $title = 'Ubah Password';
    protected static ?string $navigationGroup = 'Pengaturan Akun';

    public ?array $data = [];

    public function mount(): void
    {
        $this->form->fill();
    }

    public function form(Form $form): Form
    {
        return $form->schema([
            TextInput::make('current_password')
                ->password()
                ->required()
                ->rules(['current_password'])
                ->label('Password Lama'),

            TextInput::make('new_password')
                ->password()
                ->minLength(8)
                ->required()
                ->same('new_password_confirmation')
                ->label('Password Baru'),

            TextInput::make('new_password_confirmation')
                ->password()
                ->required()
                ->label('Konfirmasi Password Baru'),
        ])
        ->statePath('data');
        // ->actions($this->getFormActions()); // ✅ Ini yang perlu ditambahkan
    }

    public function submit()
    {
        $user = auth()->user();

        if (!Hash::check($this->data['current_password'], $user->password)) {
            Notification::make()
                ->title('Password saat ini tidak sesuai, silahkan coba lagi.')
                ->danger()
                ->send();
            return;
        }

        $user->update([
            'password' => bcrypt($this->data['new_password']),
        ]);

        $this->form->fill(); // Reset input

        Notification::make()
            ->title('Password berhasil diperbarui.')
            ->success()
            ->send();
    }

    protected function getFormActions(): array
    {
        return [
            Action::make('submit')
                ->label('Update Password')
                ->submit('submit')
                ->color('primary')
                ->icon('heroicon-m-check'),
        ];
    }
}
