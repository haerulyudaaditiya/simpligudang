<?php

namespace App\Filament\Resources\BorrowRequestResource\Pages;

use App\Filament\Resources\BorrowRequestResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditBorrowRequest extends EditRecord
{
    protected static string $resource = BorrowRequestResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
