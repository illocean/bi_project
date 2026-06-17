<?php

namespace App\Filament\Widgets;

use App\Filament\Widgets\Concerns\HasDashboardFilters;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\DB;

class BiStatsOverviewWidget extends StatsOverviewWidget
{
    use HasDashboardFilters;

    protected static ?int $sort = 0;

    protected function getStats(): array
    {
        $filters = $this->getDashboardFilters();
        $year = $filters['year'] ?? '';
        $country = $filters['country'] ?? '';
        $state = $filters['state'] ?? '';
        $city = $filters['city'] ?? '';

        $hasLocation = $this->hasFilter($country) || $this->hasFilter($state) || $this->hasFilter($city);
        $hasDate = $this->hasFilter($year);

        $totalSales = $hasDate
            ? $this->salesViaTemporal($year, $country, $state, $city, $hasLocation)
            : $this->salesViaMarket($country, $state, $city, $hasLocation);

        $totalCustomers = $this->customerCount($country, $state, $city);

        $topProduct = $hasDate
            ? $this->topProductViaTemporal($year, $country, $state, $city, $hasLocation)
            : $this->topProductViaMarket($country, $state, $city, $hasLocation);

        $topCity = $hasDate
            ? $this->topCityViaTemporal($year, $country, $state, $city)
            : $this->topCityViaMarket($country, $state, $city);

        $monthlySales = $this->monthlySalesForChart($year, $country, $state, $city, $hasLocation);

        return [
            Stat::make('Total Revenue', '$' . number_format((float) $totalSales, 2))
                ->description('Across all cities & products')
                ->descriptionIcon('heroicon-o-currency-dollar')
                ->color('success')
                ->chart($monthlySales),

            Stat::make('Total Customers', number_format((int) $totalCustomers))
                ->description('Active customer accounts')
                ->descriptionIcon('heroicon-o-users')
                ->color('primary'),

            Stat::make('Top Product', $topProduct['name'])
                ->description('$' . number_format((float) $topProduct['revenue'], 2) . ' revenue')
                ->descriptionIcon('heroicon-o-shopping-bag')
                ->color('warning'),

            Stat::make('Best Market', $topCity ?? 'N/A')
                ->description('Highest sales city')
                ->descriptionIcon('heroicon-o-map-pin')
                ->color('info'),
        ];
    }

    private function hasFilter($val): bool
    {
        return $val !== null && $val !== '';
    }

    private function applyCustomerLocation($query, $country, $state, $city): void
    {
        if ($this->hasFilter($country)) $query->where('dim_customer.country', $country);
        if ($this->hasFilter($state)) $query->where('dim_customer.state', $state);
        if ($this->hasFilter($city)) $query->where('dim_customer.city', $city);
    }

    private function getMatchingOrderNumbers($country, $state, $city): \Illuminate\Support\Collection
    {
        $q = DB::connection('mysql')
            ->table('fact_market_sales')
            ->join('dim_customer', 'fact_market_sales.customer_key', '=', 'dim_customer.customer_key')
            ->join('dim_order', 'fact_market_sales.order_key', '=', 'dim_order.order_key')
            ->select('dim_order.orderNumber');

        $this->applyCustomerLocation($q, $country, $state, $city);

        return $q->distinct()->pluck('dim_order.orderNumber');
    }

    private function salesViaMarket($country, $state, $city, $hasLocation): float
    {
        $q = DB::connection('mysql')->table('fact_market_sales');
        if ($hasLocation) {
            $q->join('dim_customer', 'fact_market_sales.customer_key', '=', 'dim_customer.customer_key');
            $this->applyCustomerLocation($q, $country, $state, $city);
        }
        return (float) $q->sum('fact_market_sales.salesAmount');
    }

    private function salesViaTemporal($year, $country, $state, $city, $hasLocation): float
    {
        $q = DB::connection('mysql')
            ->table('fact_temporal_sales')
            ->join('dim_date', 'fact_temporal_sales.date_key', '=', 'dim_date.date_key');

        if ($hasLocation) {
            $q->join('dim_order', 'fact_temporal_sales.order_key', '=', 'dim_order.order_key');
            $orderNumbers = $this->getMatchingOrderNumbers($country, $state, $city);
            $q->whereIn('dim_order.orderNumber', $orderNumbers);
        }

        return (float) $q->where('dim_date.year', $year)->sum('fact_temporal_sales.salesAmount');
    }

    private function customerCount($country, $state, $city): int
    {
        $q = DB::connection('mysql')->table('dim_customer');
        if ($this->hasFilter($country)) $q->where('country', $country);
        if ($this->hasFilter($state)) $q->where('state', $state);
        if ($this->hasFilter($city)) $q->where('city', $city);
        return (int) $q->count();
    }

    private function topProductViaMarket($country, $state, $city, $hasLocation): array
    {
        $q = DB::connection('mysql')
            ->table('fact_product_sales')
            ->join('dim_product', 'fact_product_sales.product_key', '=', 'dim_product.product_key')
            ->join('dim_order', 'fact_product_sales.order_key', '=', 'dim_order.order_key')
            ->select('dim_product.productName', DB::raw('SUM(fact_product_sales.salesAmount) as total'))
            ->groupBy('dim_product.productName')
            ->orderByDesc('total');

        if ($hasLocation) {
            $orderNumbers = $this->getMatchingOrderNumbers($country, $state, $city);
            $q->whereIn('dim_order.orderNumber', $orderNumbers);
        }

        $row = $q->first();
        return ['name' => $row->productName ?? 'N/A', 'revenue' => (float) ($row->total ?? 0)];
    }

    private function topProductViaTemporal($year, $country, $state, $city, $hasLocation): array
    {
        $temporalOrderNumbers = DB::connection('mysql')
            ->table('fact_temporal_sales')
            ->join('dim_date', 'fact_temporal_sales.date_key', '=', 'dim_date.date_key')
            ->join('dim_order', 'fact_temporal_sales.order_key', '=', 'dim_order.order_key')
            ->where('dim_date.year', $year)
            ->pluck('dim_order.orderNumber');

        $q = DB::connection('mysql')
            ->table('fact_product_sales')
            ->join('dim_product', 'fact_product_sales.product_key', '=', 'dim_product.product_key')
            ->join('dim_order', 'fact_product_sales.order_key', '=', 'dim_order.order_key')
            ->select('dim_product.productName', DB::raw('SUM(fact_product_sales.salesAmount) as total'))
            ->groupBy('dim_product.productName')
            ->orderByDesc('total')
            ->whereIn('dim_order.orderNumber', $temporalOrderNumbers);

        if ($hasLocation) {
            $orderNumbers = $this->getMatchingOrderNumbers($country, $state, $city);
            $q->whereIn('dim_order.orderNumber', $orderNumbers);
        }

        $row = $q->first();
        return ['name' => $row->productName ?? 'N/A', 'revenue' => (float) ($row->total ?? 0)];
    }

    private function topCityViaMarket($country, $state, $city): ?string
    {
        $q = DB::connection('mysql')
            ->table('fact_market_sales')
            ->join('dim_customer', 'fact_market_sales.customer_key', '=', 'dim_customer.customer_key')
            ->select('dim_customer.city')
            ->groupBy('dim_customer.city')
            ->orderByDesc(DB::raw('SUM(fact_market_sales.salesAmount)'));

        $this->applyCustomerLocation($q, $country, $state, $city);

        return $q->pluck('dim_customer.city')->first();
    }

    private function topCityViaTemporal($year, $country, $state, $city): ?string
    {
        $temporalOrderNumbers = DB::connection('mysql')
            ->table('fact_temporal_sales')
            ->join('dim_date', 'fact_temporal_sales.date_key', '=', 'dim_date.date_key')
            ->join('dim_order', 'fact_temporal_sales.order_key', '=', 'dim_order.order_key')
            ->where('dim_date.year', $year)
            ->pluck('dim_order.orderNumber');

        $q = DB::connection('mysql')
            ->table('fact_market_sales')
            ->join('dim_order', 'fact_market_sales.order_key', '=', 'dim_order.order_key')
            ->join('dim_customer', 'fact_market_sales.customer_key', '=', 'dim_customer.customer_key')
            ->select('dim_customer.city')
            ->groupBy('dim_customer.city')
            ->orderByDesc(DB::raw('SUM(fact_market_sales.salesAmount)'))
            ->whereIn('dim_order.orderNumber', $temporalOrderNumbers);

        $this->applyCustomerLocation($q, $country, $state, $city);

        return $q->pluck('dim_customer.city')->first();
    }

    private function monthlySalesForChart($year, $country, $state, $city, $hasLocation): array
    {
        $q = DB::connection('mysql')
            ->table('fact_temporal_sales')
            ->join('dim_date', 'fact_temporal_sales.date_key', '=', 'dim_date.date_key');

        if ($this->hasFilter($year)) $q->where('dim_date.year', $year);

        if ($hasLocation) {
            $q->join('dim_order', 'fact_temporal_sales.order_key', '=', 'dim_order.order_key');
            $orderNumbers = $this->getMatchingOrderNumbers($country, $state, $city);
            $q->whereIn('dim_order.orderNumber', $orderNumbers);
        }

        $q->select(DB::raw('SUM(fact_temporal_sales.salesAmount) as total'))
          ->groupBy('dim_date.year', 'dim_date.month_number')
          ->orderBy('dim_date.year')
          ->orderBy('dim_date.month_number');

        return $q->pluck('total')
            ->map(fn($v) => (int) round((float) $v))
            ->toArray();
    }
}
