<?php

namespace App\Filament\Widgets;

use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\DB;

class BiStatsOverviewWidget extends StatsOverviewWidget
{
    protected static ?int $sort = 0;

    protected function getStats(): array
    {
        $db = DB::connection('mysql');

        $totalSales = (float) $db->table('fact_market_sales')->sum('salesAmount');
        $totalCustomers = (int) $db->table('dim_customer')->count();

        $topProductRow = $db->table('fact_product_sales')
            ->join('dim_product', 'fact_product_sales.product_key', '=', 'dim_product.product_key')
            ->select('dim_product.productName', DB::raw('SUM(fact_product_sales.salesAmount) as total'))
            ->groupBy('dim_product.productName')
            ->orderByDesc('total')
            ->first();

        $topProductName = $topProductRow->productName ?? 'N/A';
        $topProductRevenue = (float) ($topProductRow->total ?? 0);

        $topCity = $db->table('fact_market_sales')
            ->join('dim_customer', 'fact_market_sales.customer_key', '=', 'dim_customer.customer_key')
            ->select('dim_customer.city')
            ->groupBy('dim_customer.city')
            ->orderByDesc(DB::raw('SUM(fact_market_sales.salesAmount)'))
            ->pluck('dim_customer.city')
            ->first();

        $monthlySales = $db->table('fact_temporal_sales')
            ->join('dim_date', 'fact_temporal_sales.date_key', '=', 'dim_date.date_key')
            ->select(DB::raw('SUM(fact_temporal_sales.salesAmount) as total'))
            ->groupBy('dim_date.year', 'dim_date.month_number')
            ->orderBy('dim_date.year')
            ->orderBy('dim_date.month_number')
            ->pluck('total')
            ->map(fn($v) => (int) round((float) $v))
            ->toArray();

        return [
            Stat::make('Total Revenue', '$' . number_format($totalSales, 2))
                ->description('Across all cities & products')
                ->descriptionIcon('heroicon-o-currency-dollar')
                ->color('success')
                ->chart($monthlySales),

            Stat::make('Total Customers', number_format($totalCustomers))
                ->description('Active customer accounts')
                ->descriptionIcon('heroicon-o-users')
                ->color('primary'),

            Stat::make('Top Product', $topProductName)
                ->description('$' . number_format($topProductRevenue, 2) . ' revenue')
                ->descriptionIcon('heroicon-o-shopping-bag')
                ->color('warning'),

            Stat::make('Best Market', $topCity ?? 'N/A')
                ->description('Highest sales city')
                ->descriptionIcon('heroicon-o-map-pin')
                ->color('info'),
        ];
    }
}
