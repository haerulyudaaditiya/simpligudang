<?php

namespace App\Filament\Resources;

use App\Filament\Resources\BorrowRequestResource\Pages;
use App\Filament\Resources\BorrowRequestResource\RelationManagers;
use App\Models\BorrowRequest;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Facades\Filament;
use Illuminate\Support\Facades\DB;
use Filament\Notifications\Notification;

class BorrowRequestResource extends Resource
{
    protected static ?string $model = BorrowRequest::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Formulir Peminjaman')
                    ->schema([
                        Forms\Components\Select::make('item_id')
                            ->label('Pilih Barang')
                            ->relationship(
                                'item',
                                'name',
                                fn ($query) => $query->where('team_id', Filament::getTenant()->id)
                                                    ->where('quantity', '>', 0)
                            )
                            ->searchable()
                            ->preload()
                            ->required(),

                        Forms\Components\DatePicker::make('borrow_date')
                            ->label('Tanggal Pinjam')
                            ->default(now())
                            ->required(),

                        Forms\Components\DatePicker::make('return_date')
                            ->label('Rencana Kembali')
                            ->required()
                            ->after('borrow_date'),

                        Forms\Components\Textarea::make('reason')
                            ->label('Keperluan / Alasan')
                            ->required()
                            ->columnSpanFull(),
                    ])->columns(2)
                    ->disabled(fn ($record) => $record && $record->status !== 'pending'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('user.name')
                    ->label('Pemohon')
                    ->sortable(),
                Tables\Columns\TextColumn::make('item.name')
                    ->label('Barang')
                    ->searchable(),
                Tables\Columns\TextColumn::make('borrow_date')
                    ->date()
                    ->label('Tgl Pinjam'),

                // Badge Status Keren
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'pending' => 'warning',
                        'approved' => 'success',
                        'rejected' => 'danger',
                        'returned' => 'info',
                    }),
            ])
            ->defaultSort('created_at', 'desc')

            ->actions([
                Tables\Actions\Action::make('approve')
                    ->label('Setujui')
                    ->icon('heroicon-o-check')
                    ->color('success')
                    ->visible(fn (BorrowRequest $record) => $record->status === 'pending' && auth()->user()->hasTeamRole('admin'))
                    ->requiresConfirmation()
                    ->action(function (BorrowRequest $record) {
                        // 1. Jalankan Logika Bisnis (Update status & stok)
                        DB::transaction(function () use ($record) {
                            $record->update([
                                'status' => 'approved',
                                'processed_by' => auth()->id(),
                                'processed_at' => now(),
                            ]);

                            $item = $record->item;
                            $item->decrement('quantity');

                            \App\Models\Log::create([
                                'team_id' => $record->team_id,
                                'item_id' => $item->id,
                                'user_id' => $record->user_id,
                                'action' => 'check_out',
                                'notes' => 'Peminjaman disetujui oleh Admin via Request #' . $record->id,
                            ]);
                        });

                        // 2. Notifikasi Sukses untuk ADMIN (Toast Hijau di pojok)
                        Notification::make()
                            ->title('Permintaan Disetujui')
                            ->success()
                            ->send();

                        // Kita kirim ke $record->user (User yang membuat request)
                        Notification::make()
                            ->title('Permintaan Anda Disetujui')
                            ->body("Barang '{$record->item->name}' siap diambil. Silakan cek gudang.")
                            ->success() // Warna hijau
                            ->sendToDatabase($record->user);
                    }),

                // --- ACTION 2: REJECT (Hanya Admin) ---
                Tables\Actions\Action::make('reject')
                    ->label('Tolak')
                    ->icon('heroicon-o-x-mark')
                    ->color('danger')
                    ->visible(fn (BorrowRequest $record) => $record->status === 'pending' && auth()->user()->hasTeamRole('admin'))
                    ->form([
                        Forms\Components\Textarea::make('rejection_reason')
                            ->label('Alasan Penolakan')
                            ->required(),
                    ])
                    ->action(function (BorrowRequest $record, array $data) {
                        $record->update([
                            'status' => 'rejected',
                            'processed_by' => auth()->id(),
                            'processed_at' => now(),
                            'reason' => $record->reason . " | Ditolak: " . $data['rejection_reason'],
                        ]);
                        Notification::make()->title('Permintaan Ditolak')->danger()->send();
                    }),

                Tables\Actions\Action::make('download_bast')
                    ->label('Download BAST')
                    ->icon('heroicon-o-document-text')
                    ->color('primary')
                    ->visible(fn (BorrowRequest $record) => in_array($record->status, ['approved', 'returned']))
                    ->url(fn (BorrowRequest $record) => route('borrow-requests.bast', $record))
                    ->openUrlInNewTab(),
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
            'index' => Pages\ListBorrowRequests::route('/'),
            'create' => Pages\CreateBorrowRequest::route('/create'),
            'edit' => Pages\EditBorrowRequest::route('/{record}/edit'),
        ];
    }
}
