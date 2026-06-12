<?php

namespace App\Filament\Widgets;

use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\DB;

class TemporalSalesWidget extends ChartWidget
{
    protected ?string $heading = 'E. Sales Volume by Year & Month';
    protected static ?int $sort = 5;
    protected string $color = 'warning';
    protected bool $isCollapsible = true;
    protected int|string|array $columnSpan = 'full';

    protected function getFilters(): ?array
    {
        $years = DB::connection('mysql')
            ->table('dim_date')
            ->distinct()
            ->pluck('year');

        return ['' => 'All Years'] + $years->mapWithKeys(fn($y) => [$y => $y])->toArray();
    }

    public function getPollingInterval(): ?string
    {
        return null;
    }

    protected function getData(): array
    {
        $query = DB::connection('mysql')
            ->table('fact_temporal_sales')
            ->join('dim_date', 'fact_temporal_sales.date_key', '=', 'dim_date.date_key')
            ->select('dim_date.year', 'dim_date.month_name', 'dim_date.month_number', DB::raw('SUM(fact_temporal_sales.salesAmount) as total_sales'))
            ->groupBy('dim_date.year', 'dim_date.month_name', 'dim_date.month_number')
            ->orderBy('dim_date.year')
            ->orderBy('dim_date.month_number');

        if ($this->filter !== '' && $this->filter !== null) {
            $query->where('dim_date.year', $this->filter);
        }

        $data = $query->get();

        $labels = $data->map(fn($row) => $row->month_name . ' ' . $row->year)->toArray();

        return [
            'datasets' => [
                [
                    'label' => 'Sales Volume',
                    'data' => $data->pluck('total_sales')->toArray(),
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
}
