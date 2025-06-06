<?php

namespace App\Filament\Resources\StoreRequestResource\Pages;

use App\Filament\Resources\StoreRequestResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListStoreRequests extends ListRecords
{
    protected static string $resource = StoreRequestResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
