<?php

namespace App\Filament\Resources\BorrowRequestResource\Pages;

use App\Filament\Resources\BorrowRequestResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListBorrowRequests extends ListRecords
{
    protected static string $resource = BorrowRequestResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
