<?php

namespace App\Filament\Widgets;

use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use App\Models\Item;
use Filament\Facades\Filament;

class StatsOverview extends BaseWidget
{
    protected function getStats(): array
    {
        // 1. Dapatkan ID Tenant yang sedang aktif
        $tenantId = Filament::getTenant()->id;

        // 2. Query data HANYA untuk tenant ini
        $items = Item::where('team_id', $tenantId);

        // 3. Hitung statistik
        $totalItems = $items->count();
        $totalValue = $items->sum(Item::raw('quantity * price')); // Hitung total nilai (qty * harga)
        $itemsInUse = Item::where('team_id', $tenantId)->where('status', 'in_use')->count();

        // 4. Kembalikan dalam bentuk kartu
        return [
            Stat::make('Total Jenis Barang', $totalItems)
                ->description('Jumlah item unik yang terdaftar')
                ->descriptionIcon('heroicon-m-archive-box')
                ->color('success'),

            Stat::make('Total Nilai Aset', 'Rp ' . number_format($totalValue, 0, ',', '.'))
                ->description('Total nilai dari (kuantitas x harga)')
                ->descriptionIcon('heroicon-m-banknotes')
                ->color('info'),

            Stat::make('Barang Dipinjam (In Use)', $itemsInUse)
                ->description('Jumlah item yang sedang dipinjam')
                ->descriptionIcon('heroicon-m-arrow-up-on-square')
                ->color('warning'),
        ];
    }
}