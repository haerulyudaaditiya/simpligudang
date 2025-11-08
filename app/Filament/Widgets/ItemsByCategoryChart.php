<?php

namespace App\Filament\Widgets;

use Filament\Widgets\ChartWidget;
use App\Models\Category;
use Filament\Facades\Filament;

class ItemsByCategoryChart extends ChartWidget
{
    protected static ?string $heading = 'Aset berdasarkan Kategori';

    protected function getData(): array
    {
        $tenant = Filament::getTenant();

        $data = Category::where('team_id', $tenant->id)
                        ->withCount('items')
                        ->get();

        return [
            'datasets' => [
                [
                    'label' => 'Jumlah Item',
                    'data' => $data->pluck('items_count'),
                    'backgroundColor' => [
                        '#FF6384', '#36A2EB', '#FFCE56', '#4BC0C0', '#9966FF',
                    ],
                ],
            ],
            'labels' => $data->pluck('name'),
        ];
    }

    protected function getType(): string
    {
        return 'pie'; 
    }
}
