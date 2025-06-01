<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use App\Models\Attendance;
use Filament\Forms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Notifications\Notification;

class AbsensiForm extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';
    protected static string $view = 'filament.pages.absensi-form';
    protected static ?string $title = 'Absensi Karyawan';

    // public ?array $data = [];

    public $status;
    public $latitude;
    public $longitude;
    public $selfie_path;

    // public function getFormModel(): mixed
    // {
    //     return $this;
    // }

    public function mount(): void
    {
        $this->form->fill([
            'status' => 'masuk',
            'timestamp' => now(),
        ]);
    }

    protected $listeners = ['setFormData'];

    public function setFormData($data): void
    {
        $this->latitude = $data['latitude'] ?? null;
        $this->longitude = $data['longitude'] ?? null;

        $this->form->fill([
            // 'status' => 'masuk',
            // 'timestamp' => now(),
            'latitude' => $this->latitude,
            'longitude' => $this->longitude,
        ]);
    }

    protected function getFormSchema(): array
    {
        return [
            Forms\Components\Grid::make(2)->schema([
                Forms\Components\Select::make('status')
                    ->label('Status Absensi')
                    ->required()
                    ->options([
                        'masuk' => 'Masuk',
                        'pulang' => 'Pulang',
                    ]),

                Forms\Components\FileUpload::make('selfie_path')
                    ->label('Selfie')
                    ->required()
                    ->image()
                    ->directory('selfies')
                    ->imagePreviewHeight('150')
                    ->columnSpan(2),

                Forms\Components\TextInput::make('latitude'),
                Forms\Components\TextInput::make('longitude'),

                Forms\Components\Placeholder::make('timestamp')
                    ->content(now()->format('d M Y H:i')),
            ]),
        ];
    }

    public function submit(): void
    {
        $formData = $this->form->getState();
        // dd($formData);
        Attendance::create([
            'user_id' => auth()->id(),
            'store_id' => auth()->user()->store_id,
            'status' => $formData['status'],
            'timestamp' => now(),
            'latitude' => $formData['latitude'],
            'longitude' => $formData['longitude'],
            'selfie_path' => $formData['selfie_path'],
        ]);

        Notification::make()
            ->title('Absen berhasil disimpan.')
            ->success()
            ->send();

        $this->form->fill(); // Reset form
    }
}
