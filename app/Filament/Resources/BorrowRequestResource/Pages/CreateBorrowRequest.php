<?php

namespace App\Filament\Resources\BorrowRequestResource\Pages;

use App\Filament\Resources\BorrowRequestResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use Filament\Facades\Filament;
use Filament\Notifications\Notification;
use App\Models\User;

class CreateBorrowRequest extends CreateRecord
{
    protected static string $resource = BorrowRequestResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['user_id'] = auth()->id();
        $data['team_id'] = Filament::getTenant()->id;
        $data['status'] = 'pending';
        return $data;
    }

    protected function afterCreate(): void
    {
        $record = $this->record;

        // CARA MANUAL / QUERY BUILDER (ANTI GAGAL)
        // Kita cari user_id dari tabel team_user secara langsung
        $adminIds = \Illuminate\Support\Facades\DB::table('team_user')
            ->where('team_id', $record->team_id)
            ->where('role', 'admin') // Pastikan ini sesuai tulisan di DB Anda (huruf besar/kecil)
            ->pluck('user_id');

        // Ambil object User berdasarkan ID tadi
        $admins = User::whereIn('id', $adminIds)->get();

        foreach ($admins as $admin) {
            Notification::make()
                ->title('Permintaan Peminjaman Baru')
                ->body("{$record->user->name} ingin meminjam {$record->item->name}.")
                ->warning()
                ->actions([
                    \Filament\Notifications\Actions\Action::make('view')
                        ->label('Tinjau')
                        ->url(BorrowRequestResource::getUrl('index')),
                ])
                ->sendToDatabase($admin);
        }
    }
}
