<?php

namespace App\Filament\Widgets;

use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\DB;

class TopCustomerWidget extends ChartWidget
{
    protected ?string $heading = 'D. Top Customer by Sales Revenue';
    protected static ?int $sort = 4;
    protected string $color = 'danger';
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
            ->table('fact_customer_sales')
            ->join('dim_customer', 'fact_customer_sales.customer_key', '=', 'dim_customer.customer_key')
            ->select('dim_customer.customerName', DB::raw('SUM(fact_customer_sales.salesAmount) as total_sales'))
            ->groupBy('dim_customer.customerName')
            ->orderByDesc('total_sales');

        if ($this->filter !== '' && $this->filter !== null) {
            $query->where('dim_customer.country', $this->filter);
        }

        $data = $query->get();

        return [
            'datasets' => [
                [
                    'label' => 'Total Sales Revenue',
                    'data' => $data->pluck('total_sales')->toArray(),
                    'backgroundColor' => '#ef4444',
                ],
            ],
            'labels' => $data->pluck('customerName')->toArray(),
        ];
    }

    protected function getType(): string
    {
        return 'polarArea';
    }
}
