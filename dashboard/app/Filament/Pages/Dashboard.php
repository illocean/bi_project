<?php

namespace App\Filament\Pages;

use Filament\Forms\Components\Select;
use Filament\Pages\Dashboard as BaseDashboard;
use Filament\Schemas\Schema;

class Dashboard extends BaseDashboard
{
    public function filtersForm(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Select::make('year')
                    ->label('Year')
                    ->options([
                        '' => 'All Years',
                        '2025' => '2025',
                        '2026' => '2026',
                    ])
                    ->default('')
                    ->live(),
            ])
            ->columns(1);
    }
}
