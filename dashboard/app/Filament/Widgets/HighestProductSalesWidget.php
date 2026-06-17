<?php

namespace App\Filament\Widgets;

use App\Filament\Widgets\Concerns\HasDashboardFilters;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\DB;

class HighestProductSalesWidget extends ChartWidget
{
    use HasDashboardFilters;

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
        $dashboardFilters = $this->getDashboardFilters();

        $productLine = $this->filter ?? '';
        $country = $dashboardFilters['country'] ?? '';
        $state = $dashboardFilters['state'] ?? '';
        $city = $dashboardFilters['city'] ?? '';

        $hasLocation = ($country !== '' && $country !== null)
            || ($state !== '' && $state !== null)
            || ($city !== '' && $city !== null);

        $query = DB::connection('mysql')
            ->table('fact_product_sales')
            ->join('dim_product', 'fact_product_sales.product_key', '=', 'dim_product.product_key')
            ->join('dim_order', 'fact_product_sales.order_key', '=', 'dim_order.order_key')
            ->select('dim_product.productName', DB::raw('SUM(fact_product_sales.salesAmount) as total_sales'))
            ->groupBy('dim_product.productName')
            ->orderByDesc('total_sales');

        if ($productLine !== '' && $productLine !== null) {
            $query->where('dim_product.productLine', $productLine);
        }

        if ($hasLocation) {
            $orderNumbers = DB::connection('mysql')
                ->table('fact_market_sales')
                ->join('dim_customer', 'fact_market_sales.customer_key', '=', 'dim_customer.customer_key')
                ->join('dim_order', 'fact_market_sales.order_key', '=', 'dim_order.order_key')
                ->select('dim_order.orderNumber');

            if ($country !== '' && $country !== null) {
                $orderNumbers->where('dim_customer.country', $country);
            }
            if ($state !== '' && $state !== null) {
                $orderNumbers->where('dim_customer.state', $state);
            }
            if ($city !== '' && $city !== null) {
                $orderNumbers->where('dim_customer.city', $city);
            }

            $query->whereIn('dim_order.orderNumber', $orderNumbers->distinct()->pluck('dim_order.orderNumber'));
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
        return 'pie';
    }
}
