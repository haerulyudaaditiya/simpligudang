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
                // --- ACTION 1: APPROVE (Hanya Admin) ---
                Tables\Actions\Action::make('approve')
                    ->label('Setujui')
                    ->icon('heroicon-o-check')
                    ->color('success')
                    // Hanya muncul jika pending & user adalah admin
                    ->visible(fn (BorrowRequest $record) => $record->status === 'pending' && auth()->user()->hasTeamRole('admin'))
                    ->requiresConfirmation()
                    ->action(function (BorrowRequest $record) {
                        // LOGIC TRANSALSI DB (PENTING!)
                        DB::transaction(function () use ($record) {
                            // 1. Update Status Request
                            $record->update([
                                'status' => 'approved',
                                'processed_by' => auth()->id(),
                                'processed_at' => now(),
                            ]);

                            // 2. Kurangi Stok Barang (Real Impact)
                            $item = $record->item;
                            $item->decrement('quantity');

                            // 3. Catat di Log Audit Global (yang kita buat sebelumnya)
                            \App\Models\Log::create([
                                'team_id' => $record->team_id,
                                'item_id' => $item->id,
                                'user_id' => $record->user_id, // User pemohon
                                'action' => 'check_out', // Kita pakai istilah check_out sistem lama
                                'notes' => 'Peminjaman disetujui oleh Admin via Request #' . $record->id,
                            ]);
                        });

                        Notification::make()->title('Permintaan Disetujui')->success()->send();
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