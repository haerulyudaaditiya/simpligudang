<?php

namespace App\Filament\Resources;

use App\Filament\Resources\LogResource\Pages;
use App\Filament\Resources\LogResource\RelationManagers;
use App\Models\Log;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\BadgeColumn;

class LogResource extends Resource
{
    protected static ?string $model = Log::class;
    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-list';
    protected static ?string $navigationGroup = 'Manajemen Inventaris';
    protected static ?int $navigationSort = 4;
    protected static ?string $label = 'Jejak Audit';

    public static function form(Form $form): Form
    {
        return $form->schema([]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('created_at')
                    ->label('Waktu')
                    ->dateTime('d M Y, H:i')
                    ->sortable(),

                TextColumn::make('item.name')
                    ->label('Barang')
                    ->searchable(),

                TextColumn::make('user.name')
                    ->label('Dilakukan oleh')
                    ->searchable(),

                BadgeColumn::make('action')
                    ->label('Aksi')
                    ->colors([
                        'warning' => 'check_out',
                        'success' => 'check_in',
                    ]),

                TextColumn::make('notes')
                    ->label('Catatan')
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
            ])
            ->bulkActions([]);
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
            'index' => Pages\ListLogs::route('/'),
            'view' => Pages\ViewLog::route('/{record}'),
        ];
    }
}