<?php

namespace App\Filament\Pages;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\Section;
use Filament\Pages\Dashboard as BaseDashboard;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\DB;

class Dashboard extends BaseDashboard
{
    public function filtersForm(Schema $schema): Schema
    {
        $countries = DB::connection('mysql')
            ->table('dim_customer')
            ->distinct()
            ->pluck('country')
            ->toArray();

        $states = DB::connection('mysql')
            ->table('dim_customer')
            ->whereNotNull('state')
            ->where('state', '!=', '')
            ->distinct()
            ->pluck('state')
            ->toArray();

        $cities = DB::connection('mysql')
            ->table('dim_customer')
            ->distinct()
            ->pluck('city')
            ->toArray();

        $officeCountries = DB::connection('mysql')
            ->table('dim_office')
            ->distinct()
            ->pluck('country')
            ->toArray();

        $officeStates = DB::connection('mysql')
            ->table('dim_office')
            ->whereNotNull('state')
            ->where('state', '!=', '')
            ->distinct()
            ->pluck('state')
            ->toArray();

        $officeCities = DB::connection('mysql')
            ->table('dim_office')
            ->distinct()
            ->pluck('city')
            ->toArray();

        $officeTerritories = DB::connection('mysql')
            ->table('dim_office')
            ->whereNotNull('territory')
            ->where('territory', '!=', '')
            ->distinct()
            ->pluck('territory')
            ->toArray();

        $allCountries = array_unique(array_merge($countries, $officeCountries));
        sort($allCountries);

        $allStates = array_unique(array_merge($states, $officeStates));
        sort($allStates);

        $allCities = array_unique(array_merge($cities, $officeCities));
        sort($allCities);

        $allTerritories = array_unique($officeTerritories);
        sort($allTerritories);

        $years = DB::connection('mysql')
            ->table('dim_date')
            ->distinct()
            ->pluck('year')
            ->sort()
            ->values()
            ->toArray();

        return $schema
            ->schema([
                Section::make('Date Filters')
                    ->schema([
                        Select::make('year')
                            ->label('Year')
                            ->options(['' => 'All Years'] + array_combine($years, $years))
                            ->default('')
                            ->live()
                            ->searchable(),

                        Select::make('period_view')
                            ->label('View Period')
                            ->options([
                                'monthly' => 'Monthly',
                                'quarterly' => 'Quarterly',
                                'semi_annual' => 'Semi-Annual',
                                'annual' => 'Annual',
                            ])
                            ->default('monthly')
                            ->live()
                            ->searchable(),
                    ])
                    ->columns(2)
                    ->collapsible(),

                Section::make('Location Filters')
                    ->schema([
                        Select::make('country')
                            ->label('Country')
                            ->options(['' => 'All Countries'] + array_combine($allCountries, $allCountries))
                            ->default('')
                            ->live()
                            ->searchable(),

                        Select::make('state')
                            ->label('State / Province')
                            ->options(['' => 'All States'] + array_combine($allStates, $allStates))
                            ->default('')
                            ->live()
                            ->searchable(),

                        Select::make('city')
                            ->label('City')
                            ->options(['' => 'All Cities'] + array_combine($allCities, $allCities))
                            ->default('')
                            ->live()
                            ->searchable(),

                        Select::make('territory')
                            ->label('Territory (Offices)')
                            ->options(['' => 'All Territories'] + array_combine($allTerritories, $allTerritories))
                            ->default('')
                            ->live()
                            ->searchable(),
                    ])
                    ->columns(2)
                    ->collapsible(),
            ])
            ->columns(1);
    }
}