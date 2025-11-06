<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ItemResource\Pages;
use App\Filament\Resources\ItemResource\RelationManagers;
use App\Models\Item;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\DatePicker;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Facades\Filament;

class ItemResource extends Resource
{
    protected static ?string $model = Item::class;

    protected static ?string $navigationIcon = 'heroicon-o-archive-box';
    protected static ?string $navigationGroup = 'Manajemen Inventaris';
    protected static ?int $navigationSort = 3;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                // --- KOLOM KIRI (Info Utama) ---
                Section::make('Informasi Barang')
                    ->schema([
                        TextInput::make('name')
                            ->label('Nama Barang')
                            ->required()
                            ->maxLength(255),

                        Select::make('category_id')
                            ->label('Kategori')
                            ->relationship('category', 'name')
                            ->searchable()
                            ->preload()
                            ->required(),

                        Select::make('location_id')
                            ->label('Lokasi')
                            ->relationship('location', 'name')
                            ->searchable()
                            ->preload()
                            ->required(),

                        Textarea::make('description')
                            ->label('Deskripsi')
                            ->columnSpan('full'),
                    ])
                    ->columns(2),

                // --- KOLOM KANAN (Metadata & Stok) ---
                Section::make('Metadata & Stok')
                    ->schema([
                        TextInput::make('item_code')
                            ->label('Kode Barang / Serial')
                            ->helperText('Kode unik, SKU, atau Serial Number.')
                            ->unique(Item::class, 'item_code', ignoreRecord: true)
                            ->maxLength(255),

                        Select::make('status')
                            ->label('Status')
                            ->options([
                                'in_stock' => 'In Stock',
                                'in_use' => 'In Use (Dipinjam)',
                                'under_repair' => 'Under Repair',
                                'lost' => 'Lost',
                            ])
                            ->default('in_stock')
                            ->required(),

                        TextInput::make('quantity')
                            ->label('Kuantitas')
                            ->numeric()
                            ->default(1)
                            ->required(),

                        TextInput::make('price')
                            ->label('Harga Beli (Opsional)')
                            ->numeric()
                            ->prefix('Rp'),

                        DatePicker::make('purchase_date')
                            ->label('Tanggal Beli (Opsional)'),

                    ])->columns(1),

            ])
            ->columns(3);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('Nama Barang')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('category.name')
                    ->label('Kategori')
                    ->searchable()
                    ->sortable()
                    ->badge(),

                TextColumn::make('location.name')
                    ->label('Lokasi')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('item_code')
                    ->label('Kode Barang')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'in_stock' => 'success',
                        'in_use' => 'warning',
                        'under_repair' => 'danger',
                        'lost' => 'gray',
                        default => 'info',
                    })
                    ->sortable(),

                TextColumn::make('quantity')
                    ->label('Kuantitas')
                    ->sortable(),

                TextColumn::make('price')
                    ->label('Harga Beli')
                    ->money('IDR')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('category')
                    ->label('Filter Kategori')
                    ->relationship('category', 'name')
                    ->preload(),

                SelectFilter::make('location')
                    ->label('Filter Lokasi')
                    ->relationship('location', 'name')
                    ->preload(),

                SelectFilter::make('status')
                    ->label('Filter Status')
                    ->options([
                        'in_stock' => 'In Stock',
                        'in_use' => 'In Use (Dipinjam)',
                        'under_repair' => 'Under Repair',
                        'lost' => 'Lost',
                    ]),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
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
            'index' => Pages\ListItems::route('/'),
            'create' => Pages\CreateItem::route('/create'),
            'edit' => Pages\EditItem::route('/{record}/edit'),
        ];
    }
}
