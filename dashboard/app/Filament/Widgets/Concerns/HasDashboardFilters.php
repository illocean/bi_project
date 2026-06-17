<?php

namespace App\Filament\Widgets\Concerns;

use App\Filament\Pages\Dashboard;

trait HasDashboardFilters
{
    protected function getDashboardFilters(): array
    {
        return session()->get(
            md5(Dashboard::class) . '_filters',
            []
        );
    }
}
