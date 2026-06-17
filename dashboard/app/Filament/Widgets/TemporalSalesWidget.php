<?php

namespace App\Filament\Widgets;

use App\Filament\Widgets\Concerns\HasDashboardFilters;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\DB;

class TemporalSalesWidget extends ChartWidget
{
    use HasDashboardFilters;

    public function getHeading(): ?string
    {
        $dashboardFilters = $this->getDashboardFilters();
        $periodView = $dashboardFilters['period_view'] ?? 'monthly';
        $year = $dashboardFilters['year'] ?? '';

        $labels = [
            'monthly' => 'by Year & Month',
            'quarterly' => 'by Quarter',
            'semi_annual' => 'by Half-Year',
            'annual' => 'by Year',
        ];

        $periodLabel = $labels[$periodView] ?? 'by Year & Month';
        $yearSuffix = ($year !== '' && $year !== null) ? " ({$year})" : '';

        return "E. Sales Volume {$periodLabel}{$yearSuffix}";
    }
    protected static ?int $sort = 5;
    protected string $color = 'warning';
    protected bool $isCollapsible = true;
    protected int|string|array $columnSpan = 'full';

    protected function getData(): array
    {
        $dashboardFilters = $this->getDashboardFilters();

        $year = $dashboardFilters['year'] ?? '';
        $periodView = $dashboardFilters['period_view'] ?? 'monthly';

        $query = DB::connection('mysql')
            ->table('fact_temporal_sales')
            ->join('dim_date', 'fact_temporal_sales.date_key', '=', 'dim_date.date_key')
            ->select(
                'dim_date.year',
                'dim_date.month_number',
                'dim_date.month_name',
                'dim_date.quarter',
                DB::raw('SUM(fact_temporal_sales.salesAmount) as total_sales')
            )
            ->groupBy('dim_date.year', 'dim_date.month_number', 'dim_date.month_name', 'dim_date.quarter');

        if ($year !== '' && $year !== null) {
            $query->where('dim_date.year', $year);
        }

        $data = $query->get();

        $groupedData = $this->groupByPeriod($data, $periodView);

        $labels = $groupedData->pluck('label')->toArray();
        $values = $groupedData->pluck('total_sales')->toArray();

        return [
            'datasets' => [
                [
                    'label' => 'Sales Volume',
                    'data' => $values,
                    'borderColor' => '#f59e0b',
                    'backgroundColor' => 'rgba(245, 158, 11, 0.2)',
                    'fill' => true,
                    'tension' => 0.3,
                ],
            ],
            'labels' => $labels,
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }

    private function groupByPeriod($data, $periodView)
    {
        switch ($periodView) {
            case 'quarterly':
                return $data->groupBy(function ($row) {
                    return "Q{$row->quarter} {$row->year}";
                })->map(function ($group) {
                    return [
                        'label' => $group->first()->quarter ? "Q{$group->first()->quarter} {$group->first()->year}" : "{$group->first()->year}",
                        'total_sales' => $group->sum('total_sales'),
                    ];
                })->sortBy(function ($item) {
                    preg_match('/Q(\d+) (\d+)/', $item['label'], $matches);
                    if ($matches) {
                        return $matches[2] * 10 + $matches[1];
                    }
                    return $item['label'];
                })->values();

            case 'semi_annual':
                return $data->groupBy(function ($row) {
                    $half = $row->quarter <= 2 ? 'H1' : 'H2';
                    return "{$half} {$row->year}";
                })->map(function ($group) {
                    return [
                        'label' => $group->first()->quarter <= 2 ? "H1 {$group->first()->year}" : "H2 {$group->first()->year}",
                        'total_sales' => $group->sum('total_sales'),
                    ];
                })->sortBy(function ($item) {
                    preg_match('/H(\d+) (\d+)/', $item['label'], $matches);
                    if ($matches) {
                        return $matches[2] * 10 + $matches[1];
                    }
                    return $item['label'];
                })->values();

            case 'annual':
                return $data->groupBy('year')->map(function ($group) {
                    return [
                        'label' => (string) $group->first()->year,
                        'total_sales' => $group->sum('total_sales'),
                    ];
                })->sortBy('label')->values();

            case 'monthly':
            default:
                return $data->map(function ($row) {
                    return [
                        'label' => $row->month_name . ' ' . $row->year,
                        'total_sales' => $row->total_sales,
                    ];
                })->sortBy(function ($item) {
                    preg_match('/(\w+) (\d+)/', $item['label'], $matches);
                    if ($matches) {
                        $monthNames = ['January' => 1, 'February' => 2, 'March' => 3, 'April' => 4, 'May' => 5, 'June' => 6,
                                     'July' => 7, 'August' => 8, 'September' => 9, 'October' => 10, 'November' => 11, 'December' => 12];
                        $monthNum = $monthNames[$matches[1]] ?? 1;
                        return $matches[2] * 100 + $monthNum;
                    }
                    return $item['label'];
                })->values();
        }
    }
}
