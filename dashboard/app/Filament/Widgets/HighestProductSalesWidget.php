<?php

namespace App\Filament\Widgets;

use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\DB;

class HighestProductSalesWidget extends ChartWidget
{
    protected ?string $heading = 'B. Highest Sales by Product';
    protected static ?int $sort = 2;
    protected string $color = 'success';
    protected bool $isCollapsible = true;
    protected int|string|array $columnSpan = 'full';

    protected function getFilters(): ?array
    {
        $lines = DB::connection('mysql')
            ->table('dim_product')
            ->distinct()
            ->pluck('productLine');

        return ['' => 'All Product Lines'] + $lines->mapWithKeys(fn($l) => [$l => $l])->toArray();
    }

    public function getPollingInterval(): ?string
    {
        return null;
    }

    protected function getData(): array
    {
        $query = DB::connection('mysql')
            ->table('fact_product_sales')
            ->join('dim_product', 'fact_product_sales.product_key', '=', 'dim_product.product_key')
            ->select('dim_product.productName', DB::raw('SUM(fact_product_sales.salesAmount) as total_sales'))
            ->groupBy('dim_product.productName')
            ->orderByDesc('total_sales');

        if ($this->filter !== '' && $this->filter !== null) {
            $query->where('dim_product.productLine', $this->filter);
        }

        $data = $query->get();

        return [
            'datasets' => [
                [
                    'label' => 'Total Sales',
                    'data' => $data->pluck('total_sales')->toArray(),
                    'backgroundColor' => '#10b981',
                ],
            ],
            'labels' => $data->pluck('productName')->toArray(),
        ];
    }

    protected function getType(): string
    {
        return 'radar';
    }
}
