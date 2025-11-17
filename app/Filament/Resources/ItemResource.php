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
use SimpleSoftwareIO\QrCode\Facades\QrCode; // Library QR
use Illuminate\Support\HtmlString; // Untuk render HTML
use Filament\Forms\Components\Placeholder; // Komponen penampung

class ItemResource extends Resource
{
    protected static ?string $model = Item::class;

    protected static ?string $navigationIcon = 'heroicon-o-archive-box';
    protected static ?string $navigationGroup = 'Manajemen Inventaris';
    protected static ?int $navigationSort = 3;
    protected static ?string $recordTitleAttribute = 'name';

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

                Section::make('Label & Identifikasi Fisik')
                ->schema([
                    Placeholder::make('qr_code')
                        ->label('QR Code Barang')
                        ->content(function (Item $record = null) {
                            // Jika record belum dibuat (halaman Create), jangan tampilkan
                            if (! $record) {
                                return '-';
                            }

                            // Generate QR Code yang mengarah ke URL View item ini
                            // Kita gunakan item_code sebagai isinya, atau URL lengkap
                            // Skenario nyata: URL lengkap agar bisa discan HP
                            $url = ItemResource::getUrl('view', ['record' => $record]);

                            $qrCode = QrCode::size(150)
                                ->color(0, 0, 0) // Warna Hitam
                                ->generate($url);

                            // Kembalikan sebagai HTML String agar dirender browser
                            return new HtmlString('
                                <div class="flex flex-col items-center justify-center p-4 border rounded-lg bg-white w-fit">
                                    '.$qrCode.'
                                    <div class="text-xs font-bold mt-2 text-gray-500">
                                        '.($record->item_code ?? $record->name).'
                                    </div>
                                    <div class="text-[10px] text-gray-400">
                                        Scan untuk detail
                                    </div>
                                </div>
                            ');
                        })
                        ->columnSpan('full'),
                ])
                // Hanya tampilkan section ini di halaman View atau Edit
                // Sembunyikan saat Create (karena ID belum ada)
                ->visible(fn (string $operation) => $operation !== 'create'),
            // --- AKHIR SECTION BARU ---

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
                Action::make('check_out')
                    ->label('Check-out')
                    ->icon('heroicon-o-arrow-up-on-square')
                    ->color('warning')

                    ->visible(fn (Item $record): bool => $record->status === 'in_stock')

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
                    ->label('Print QR')
                    ->icon('heroicon-o-printer')
                    ->color('gray')
                    ->url(fn (Item $record) => route('items.print-qr', $record)) // Kita akan buat rute ini
                    ->openUrlInNewTab(), // Buka di tab baru

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
            LogsRelationManager::class,
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
}