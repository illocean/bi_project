<?php

namespace App\Filament\Widgets;

use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\DB;

class BestCityMarketWidget extends ChartWidget
{
    protected ?string $heading = 'A. Best Market for Sales (City)';
    protected static ?int $sort = 1;
    protected string $color = 'primary';
    protected bool $isCollapsible = true;
    protected int|string|array $columnSpan = 'full';

    protected function getFilters(): ?array
    {
        $countries = DB::connection('mysql')
            ->table('dim_customer')
            ->distinct()
            ->pluck('country');

        return ['' => 'All Countries'] + $countries->mapWithKeys(fn($c) => [$c => $c])->toArray();
    }

    public function getPollingInterval(): ?string
    {
        return null;
    }

    protected function getData(): array
    {
        $query = DB::connection('mysql')
            ->table('fact_market_sales')
            ->join('dim_customer', 'fact_market_sales.customer_key', '=', 'dim_customer.customer_key')
            ->select('dim_customer.city', DB::raw('SUM(fact_market_sales.salesAmount) as total_sales'))
            ->groupBy('dim_customer.city')
            ->orderByDesc('total_sales');

        if ($this->filter !== '' && $this->filter !== null) {
            $query->where('dim_customer.country', $this->filter);
        }

        $data = $query->get();

        return [
            'datasets' => [
                [
                    'label' => 'Total Sales',
                    'data' => $data->pluck('total_sales')->toArray(),
                    'backgroundColor' => '#f59e0b',
                ],
            ],
            'labels' => $data->pluck('city')->toArray(),
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }
}
