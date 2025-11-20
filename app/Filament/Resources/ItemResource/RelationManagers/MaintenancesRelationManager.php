<?php

namespace App\Filament\Resources\ItemResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Facades\Filament;
use Illuminate\Database\Eloquent\Builder;

class MaintenancesRelationManager extends RelationManager
{
    protected static string $relationship = 'maintenances';
    protected static ?string $title = 'Riwayat Servis & Biaya'; // Judul Keren

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('service_provider')
                    ->label('Tempat Servis / Vendor')
                    ->required()
                    ->maxLength(255),

                Forms\Components\Select::make('status')
                    ->options([
                        'scheduled' => 'Terjadwal',
                        'in_progress' => 'Sedang Dikerjakan',
                        'completed' => 'Selesai',
                    ])
                    ->required()
                    ->default('in_progress'),

                Forms\Components\Textarea::make('issue_description')
                    ->label('Keluhan / Masalah')
                    ->required()
                    ->columnSpanFull(),

                Forms\Components\DatePicker::make('start_date')
                    ->label('Tanggal Masuk')
                    ->default(now())
                    ->required(),

                Forms\Components\DatePicker::make('completion_date')
                    ->label('Tanggal Selesai'),

                Forms\Components\TextInput::make('cost')
                    ->label('Biaya Servis')
                    ->numeric()
                    ->prefix('Rp')
                    ->required(),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('start_date')->date()->label('Tgl Masuk'),
                Tables\Columns\TextColumn::make('service_provider')->label('Vendor'),
                Tables\Columns\TextColumn::make('issue_description')->limit(30)->label('Masalah'),

                Tables\Columns\TextColumn::make('cost')
                    ->money('IDR')
                    ->label('Biaya')
                    ->sortable()
                    ->summarize([
                        // FITUR ADVANCE: Total Biaya per Item
                        Tables\Columns\Summarizers\Sum::make()
                            ->label('Total Biaya Servis')
                            ->money('IDR'),
                    ]),

                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'scheduled' => 'gray',
                        'in_progress' => 'warning',
                        'completed' => 'success',
                    }),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->mutateFormDataUsing(function (array $data): array {
                        $data['team_id'] = Filament::getTenant()->id;
                        $data['user_id'] = auth()->id();
                        return $data;
                    })
                    // LOGIC BISNIS: Saat servis masuk, ubah status barang otomatis
                    ->after(function ($livewire) {
                        $item = $livewire->getOwnerRecord();
                        $item->update(['status' => 'under_repair']);
                    }),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ]);
    }
}
