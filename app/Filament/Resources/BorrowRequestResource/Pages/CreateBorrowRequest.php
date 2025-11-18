<?php

namespace App\Filament\Resources\BorrowRequestResource\Pages;

use App\Filament\Resources\BorrowRequestResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateBorrowRequest extends CreateRecord
{
    protected static string $resource = BorrowRequestResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['user_id'] = auth()->id();
        $data['team_id'] = \Filament\Facades\Filament::getTenant()->id;
        $data['status'] = 'pending';
        return $data;
    }
}