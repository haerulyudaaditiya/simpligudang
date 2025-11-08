<?php

namespace App\Filament\Pages;

use Filament\Pages\Dashboard as BasePage;
use App\Filament\Widgets\StatsOverview;
use App\Filament\Widgets\ItemsByCategoryChart;

class Dashboard extends BasePage
{
    protected static ?string $navigationIcon = 'heroicon-o-home';

    public function getWidgets(): array
    {
        return [
            StatsOverview::class,
            ItemsByCategoryChart::class, 
        ];
    }
}