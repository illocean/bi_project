<?php

namespace App\Filament\Widgets;

use App\Filament\Widgets\Concerns\HasDashboardFilters;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\DB;

class BestOfficeSupportWidget extends ChartWidget
{
    use HasDashboardFilters;

    protected ?string $heading = 'C. Best Office Sales Support';
    protected static ?int $sort = 3;
    protected string $color = 'info';
    protected bool $isCollapsible = true;
    protected int|string|array $columnSpan = 'full';

    protected function getFilters(): ?array
    {
        $countries = DB::connection('mysql')
            ->table('dim_office')
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
        $dashboardFilters = $this->getDashboardFilters();

        $country = $dashboardFilters['country'] ?? ($this->filter ?? '');
        $state = $dashboardFilters['state'] ?? '';
        $city = $dashboardFilters['city'] ?? '';
        $territory = $dashboardFilters['territory'] ?? '';

        $query = DB::connection('mysql')
            ->table('fact_support_sales')
            ->join('dim_office', 'fact_support_sales.office_key', '=', 'dim_office.office_key')
            ->select(
                'dim_office.city',
                'dim_office.state',
                'dim_office.country',
                'dim_office.territory',
                'dim_office.officeCode',
                DB::raw('SUM(fact_support_sales.salesAmount) as total_sales')
            )
            ->groupBy('dim_office.officeCode', 'dim_office.city', 'dim_office.state', 'dim_office.country', 'dim_office.territory')
            ->orderByDesc('total_sales');

        if ($country !== '' && $country !== null) {
            $query->where('dim_office.country', $country);
        }

        if ($state !== '' && $state !== null) {
            $query->where('dim_office.state', $state);
        }

        if ($city !== '' && $city !== null) {
            $query->where('dim_office.city', $city);
        }

        if ($territory !== '' && $territory !== null) {
            $query->where('dim_office.territory', $territory);
        }

        $data = $query->get();

        return [
            'datasets' => [
                [
                    'label' => 'Total Sales Supported',
                    'data' => $data->pluck('total_sales')->toArray(),
                    'backgroundColor' => ['#3b82f6', '#60a5fa', '#93c5fd', '#bfdbfe'],
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
