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
use Filament\Tables\Actions\Action;
use Filament\Forms\Components\Select as FormSelect;
use App\Models\User;
use App\Models\Log;
use Filament\Notifications\Notification;
use App\Filament\Resources\ItemResource\RelationManagers\LogsRelationManager;
use Filament\Tables\Actions\ViewAction;
use SimpleSoftwareIO\QrCode\Facades\QrCode;
use Illuminate\Support\HtmlString;
use Filament\Forms\Components\Placeholder;
use pxlrbt\FilamentExcel\Actions\Tables\ExportBulkAction;
use pxlrbt\FilamentExcel\Exports\ExcelExport;
use pxlrbt\FilamentExcel\Columns\Column;
use App\Filament\Resources\ItemResource\RelationManagers\MaintenancesRelationManager;
use Filament\Infolists\Infolist;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Components\Group;
use Filament\Infolists\Components\Section as InfolistSection;

class ItemResource extends Resource
{
    protected static ?string $model = Item::class;

    protected static ?string $navigationIcon = 'heroicon-o-archive-box';
    protected static ?string $navigationGroup = 'Manajemen Inventaris';
    protected static ?int $navigationSort = 3;
    protected static ?string $recordTitleAttribute = 'name';

    public static function getNavigationBadge(): ?string
    {
        $tenant = Filament::getTenant();

        if (!$tenant) return null;

        $lowStockCount = static::getModel()::where('team_id', $tenant->id)
            ->where('quantity', '<=', 5)
            ->count();

        return $lowStockCount > 0 ? (string) $lowStockCount : null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'danger';
    }

    public static function getGloballySearchableAttributes(): array
    {
        return ['name', 'item_code'];
    }

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
                            ->relationship(
                                'category',
                                'name',
                                fn (Builder $query) => $query->where('team_id', Filament::getTenant()->id)
                            )
                            ->searchable()
                            ->preload()
                            ->required(),

                        Select::make('location_id')
                            ->label('Lokasi')
                            ->relationship(
                                'location',
                                'name',
                                fn (Builder $query) => $query->where('team_id', Filament::getTenant()->id)
                            )
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
                    ->sortable()
                    ->color(fn (string $state): string => $state <= 5 ? 'danger' : 'success')
                    ->icon(fn (string $state): ?string => $state <= 5 ? 'heroicon-o-exclamation-triangle' : null)
                    ->weight('bold'),

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
                Action::make('check_out')
                    ->label('Check-out')
                    ->icon('heroicon-o-arrow-up-on-square')
                    ->color('warning')

                    ->visible(fn (Item $record): bool => $record->status === 'in_stock' && auth()->user()->hasTeamRole('admin'))

                    ->form([
                        FormSelect::make('user_id')
                            ->label('Dipinjam oleh')
                            ->options(function () {
                                $team = Filament::getTenant();
                                if (!$team) {
                                    return []; // Jika tidak ada team, kembalikan array kosong
                                }
                                return $team->users->pluck('name', 'id');
                            })
                            ->searchable()
                            ->preload()
                            ->required(),
                        Forms\Components\Textarea::make('notes')
                            ->label('Catatan (Opsional)'),
                    ])
                    ->action(function (Item $record, array $data) {
                        // 1. Ubah status barang
                        $record->status = 'in_use';
                        $record->save();
                        $userPeminjam = User::find($data['user_id']);
                        $namaPeminjam = $userPeminjam ? $userPeminjam->name : 'User Tidak Dikenal';

                        // 2. Buat catatan log (Audit trail profesional)
                        Log::create([
                            'team_id' => Filament::getTenant()->id,
                            'item_id' => $record->id,
                            'user_id' => auth()->id(),
                            'action' => 'check_out',
                            'notes' => "Barang di-check-out ke: {$namaPeminjam}. Catatan: {$data['notes']}",
                        ]);

                        // Tampilkan notifikasi sukses
                        Notification::make()
                            ->title('Check-out Berhasil')
                            ->success()
                            ->send();
                    }),

                Action::make('check_in')
                    ->label('Check-in')
                    ->icon('heroicon-o-arrow-down-on-square')
                    ->color('success')

                    // Hanya tampilkan jika barang TIDAK sedang "in_stock"
                    ->visible(fn (Item $record): bool => $record->status !== 'in_stock')

                    // Minta konfirmasi sebelum menjalankan
                    ->requiresConfirmation()

                    // Logika yang dijalankan saat dikonfirmasi
                    ->action(function (Item $record) {
                        // 1. Ubah status barang
                        $record->status = 'in_stock';
                        $record->save();

                        // 2. Buat catatan log
                        Log::create([
                            'team_id' => Filament::getTenant()->id,
                            'item_id' => $record->id,
                            'user_id' => auth()->id(), // Admin yang melakukan check-in
                            'action' => 'check_in',
                            'notes' => 'Barang telah dikembalikan ke stok.',
                        ]);

                        // 3. Tampilkan notifikasi
                        Notification::make()
                            ->title('Check-in Berhasil')
                            ->body('Barang telah dikembalikan ke stok.')
                            ->success()
                            ->send();
                    }),
                Tables\Actions\ViewAction::make()
                    ->visible(fn (): bool => auth()->user()->hasTeamRole('staff')),

                Action::make('print_qr')
                    ->label('QR Code')
                    ->icon('heroicon-o-qr-code')
                    ->color('info')
                    ->modalHeading(fn (Item $record) => 'QR Code - ' . $record->name)
                    ->modalContent(function (Item $record) {
                        $url = ItemResource::getUrl('view', ['record' => $record]);
                        $qrCode = QrCode::size(200)->color(0,0,0)->generate($url);

                        return new HtmlString('
                            <div class="flex flex-col items-center justify-center p-4">
                                '.$qrCode.'
                                <p class="text-sm text-gray-500 mt-2 font-mono">'.$record->item_code.'</p>
                                <p class="text-xs text-gray-400">Scan untuk detail</p>
                            </div>
                        ');
                    })
                    ->modalSubmitAction(false)
                    ->modalCancelActionLabel('Tutup')
                    ->extraModalFooterActions(fn (Item $record) => [
                        Action::make('open_print_page')
                            ->label('Buka Halaman Cetak')
                            ->icon('heroicon-o-printer')
                            ->url(route('items.print-qr', $record))
                            ->openUrlInNewTab()
                            ->color('primary'),
                    ]),

                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    ExportBulkAction::make()
                        ->label('Export Laporan')
                        ->exports([
                            ExcelExport::make()
                                ->fromTable()
                                ->withFilename(fn ($resource) => $resource::getModelLabel() . '-' . date('Y-m-d'))
                                ->withColumns([
                                    Column::make('name')->heading('Nama Barang'),
                                    Column::make('item_code')->heading('Kode / Serial'),
                                    Column::make('category.name')->heading('Kategori'),
                                    Column::make('location.name')->heading('Lokasi'),
                                    Column::make('status')->heading('Status'),
                                    Column::make('quantity')->heading('Stok'),
                                    Column::make('price')->heading('Harga Beli'),
                                    Column::make('purchase_date')->heading('Tanggal Beli'),
                                ]),
                        ]),
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            LogsRelationManager::class,
            MaintenancesRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListItems::route('/'),
            'create' => Pages\CreateItem::route('/create'),
            'view' => Pages\ViewItem::route('/{record}'),
            'edit' => Pages\EditItem::route('/{record}/edit'),
        ];
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                InfolistSection::make('Ringkasan Aset')
                    ->schema([
                        TextEntry::make('name')->label('Nama Barang'),
                        TextEntry::make('item_code')->label('Kode'),
                        TextEntry::make('status')
                            ->badge()
                            ->color(fn (string $state): string => match ($state) {
                                'in_stock' => 'success',
                                'in_use' => 'warning',
                                'under_repair' => 'danger',
                                'lost' => 'gray',
                                default => 'info',
                            }),
                    ])->columns(3),

                InfolistSection::make('Analisis Biaya (Financials)')
                    ->schema([
                        TextEntry::make('price')
                            ->label('Harga Beli Awal')
                            ->money('IDR'),

                        // --- LOGIC ADVANCE: MENGHITUNG TOTAL MAINTENANCE ---
                        TextEntry::make('maintenance_cost')
                            ->label('Total Biaya Servis')
                            ->money('IDR')
                            ->state(fn (Item $record) => $record->maintenances()->sum('cost')),

                        // --- THE HOLY GRAIL: TCO ---
                        TextEntry::make('tco')
                            ->label('Total Cost of Ownership (TCO)')
                            ->helperText('Total investasi perusahaan untuk aset ini sejauh ini.')
                            ->money('IDR')
                            ->weight('bold')
                            ->size(TextEntry\TextEntrySize::Large)
                            ->color('primary')
                            ->state(fn (Item $record) => $record->price + $record->maintenances()->sum('cost')),
                    ])->columns(3),
            ]);
    }
}
