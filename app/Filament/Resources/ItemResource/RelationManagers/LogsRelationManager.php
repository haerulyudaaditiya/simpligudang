<?php

namespace App\Filament\Resources\ItemResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\BadgeColumn;

class LogsRelationManager extends RelationManager
{
    protected static string $relationship = 'logs';
    protected static ?string $title = 'Riwayat Aktivitas Barang';

    // Kita tidak perlu form, ini read-only
    public function form(Form $form): Form
    {
        return $form->schema([]);
    }

    // Konfigurasi tabel riwayat
    public function table(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                TextColumn::make('created_at')
                    ->label('Waktu')
                    ->dateTime('d M Y, H:i')
                    ->sortable(),

                TextColumn::make('user.name')
                    ->label('Dilakukan oleh'),

                BadgeColumn::make('action') 
                    ->label('Aksi')
                    ->colors([
                        'warning' => 'check_out',
                        'success' => 'check_in',
                    ]),

                TextColumn::make('notes')
                    ->label('Catatan'),
            ])
            ->filters([
                //
            ])
            ->headerActions([])
            ->actions([])
            ->bulkActions([]);
    }
}
