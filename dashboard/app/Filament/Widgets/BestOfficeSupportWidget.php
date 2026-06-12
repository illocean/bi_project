<?php

namespace App\Filament\Widgets;

use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\DB;

class BestOfficeSupportWidget extends ChartWidget
{
    protected ?string $heading = 'C. Best Office Sales Support';
    protected static ?int $sort = 3;
    protected string $color = 'info';
    protected bool $isCollapsible = true;
    protected int|string|array $columnSpan = 'full';

    public function getPollingInterval(): ?string
    {
        return null;
    }

    protected function getData(): array
    {
        $data = DB::connection('mysql')
            ->table('fact_support_sales')
            ->join('dim_office', 'fact_support_sales.office_key', '=', 'dim_office.office_key')
            ->select('dim_office.city', 'dim_office.officeCode', DB::raw('SUM(fact_support_sales.salesAmount) as total_sales'))
            ->groupBy('dim_office.officeCode', 'dim_office.city')
            ->orderByDesc('total_sales')
            ->get();

        return [
            'datasets' => [
                [
                    'label' => 'Total Sales Supported',
                    'data' => $data->pluck('total_sales')->toArray(),
                    'backgroundColor' => ['#3b82f6', '#60a5fa'],
                ],
            ],
            'labels' => $data->pluck('city')->toArray(),
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }

    protected function getOptions(): array
    {
        return [
            'indexAxis' => 'y',
            'plugins' => [
                'legend' => [
                    'display' => false,
                ],
            ],
        ];
    }
}
