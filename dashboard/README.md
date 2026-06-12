# BI Dashboard

Filament v5 dashboard visualizing the `bi_project` star schema. Answers five BI questions through interactive charts with KPI summary cards.

## Setup

```bash
composer install
php artisan migrate
php artisan filament:assets
php artisan serve
# Visit http://localhost:8000/admin
```

Requires `bi_project` database populated via `ETL/populate_bi_project.sql`.

## Dashboard Layout

```mermaid
block-beta
    columns 4

    block:kpis:4
        columns 4
        Revenue["Total Revenue\n$XX,XXX"]
        Customers["Total Customers\n5"]
        TopProd["Top Product\nProduct Name"]
        BestCity["Best Market\nCity Name"]
    end

    space:4

    block:charts:4
        columns 1
        A["A. Best Market by City — Bar Chart"]
        B["B. Highest Sales by Product — Bar Chart"]
        C["C. Best Office Support — Doughnut Chart"]
        D["D. Top Customer Revenue — Bar Chart"]
        E["E. Sales Volume Over Time — Line Chart"]
    end

    style Revenue fill:#10b981,color:#fff
    style Customers fill:#6366f1,color:#fff
    style TopProd fill:#f59e0b,color:#fff
    style BestCity fill:#0ea5e9,color:#fff
    style A fill:#f59e0b,color:#fff
    style B fill:#10b981,color:#fff
    style C fill:#3b82f6,color:#fff
    style D fill:#ef4444,color:#fff
    style E fill:#f59e0b,color:#fff
```

## File Structure

```
app/Filament/
├── Pages/
│   └── Dashboard.php              # Year filter, grid layout
├── Widgets/
│   ├── BiStatsOverviewWidget.php   # 4 KPI stat cards
│   ├── BestCityMarketWidget.php    # A: Bar — sales by city (filter: country)
│   ├── HighestProductSalesWidget.php # B: Bar — sales by product (filter: product line)
│   ├── BestOfficeSupportWidget.php # C: Doughnut — sales by office
│   ├── TopCustomerWidget.php       # D: Bar — sales by customer (filter: country)
│   └── TemporalSalesWidget.php     # E: Line — sales over time (filter: year)
└── Providers/Filament/
    └── AdminPanelProvider.php      # Panel config, widget registration
```

## Widgets

| Widget | Chart | Filter | Query Pattern |
|--------|-------|--------|---------------|
| BiStatsOverview | KPI cards | — | Aggregates across all fact tables |
| A. Best City Market | Bar | Country | `fact_market_sales` → `dim_customer` → group by city |
| B. Highest Product Sales | Bar | Product Line | `fact_product_sales` → `dim_product` → group by product |
| C. Best Office Support | Doughnut | — | `fact_support_sales` → `dim_office` → group by office |
| D. Top Customer Revenue | Bar | Country | `fact_customer_sales` → `dim_customer` → group by customer |
| E. Sales Volume Over Time | Line | Year | `fact_temporal_sales` → `dim_date` → group by year+month |

## Tech Stack

- Laravel 12 + Filament v5
- Livewire (reactive filters)
- Chart.js (visualizations)
- MySQL (`bi_project` star schema)

## Documentation

See `docs/DOCUMENTATION.md` for comprehensive documentation including BI concepts, data flow, and architectural decisions.
