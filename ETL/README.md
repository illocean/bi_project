# ETL Pipeline

Extract, Transform, Load scripts that build the `bi_project` star schema from the `bi_products` OLTP source database.

## Files

| File | Purpose |
|------|---------|
| `products.sql` | Creates `bi_products` — source OLTP database with sample data (ClassicModels) |
| `project.sql` | Creates `bi_project` — star schema with 6 dimension + 5 fact tables |
| `populate_bi_project.sql` | ETL script that populates `bi_project` from `bi_products` |
| `a.sql` through `e.sql` | Individual ETL scripts for each analytical question (development use) |

## Database Schemas

### Source: `bi_products` (OLTP)

```mermaid
erDiagram
    productlines ||--o{ products : contains
    products ||--o{ orderdetails : ordered_in
    orders ||--o{ orderdetails : contains
    customers ||--o{ orders : places
    customers ||--o{ payments : makes
    employees ||--o{ customers : serves
    offices ||--o{ employees : employs
    employees ||--o{ employees : reports_to
```

Tables: `productlines`, `products`, `offices`, `employees`, `customers`, `orders`, `orderdetails`, `payments`

### Target: `bi_project` (Star Schema)

```mermaid
erDiagram
    dim_customer ||--o{ fact_market_sales : "customer_key"
    dim_order ||--o{ fact_market_sales : "order_key"

    dim_product ||--o{ fact_product_sales : "product_key"
    dim_order ||--o{ fact_product_sales : "order_key"

    dim_office ||--o{ fact_support_sales : "office_key"
    dim_employee ||--o{ fact_support_sales : "employee_key"
    dim_order ||--o{ fact_support_sales : "order_key"

    dim_customer ||--o{ fact_customer_sales : "customer_key"
    dim_order ||--o{ fact_customer_sales : "order_key"

    dim_date ||--o{ fact_temporal_sales : "date_key"
    dim_order ||--o{ fact_temporal_sales : "order_key"
```

**Dimension Tables:** `dim_customer`, `dim_order`, `dim_product`, `dim_office`, `dim_employee`, `dim_date`

**Fact Tables:** `fact_market_sales`, `fact_product_sales`, `fact_support_sales`, `fact_customer_sales`, `fact_temporal_sales`

## ETL Flow

```mermaid
flowchart TD
    A[bi_products.customers] -->|INSERT| B[dim_customer]
    C[bi_products.orders + orderdetails] -->|INSERT| D[dim_order]
    E[bi_products.products] -->|INSERT| F[dim_product]
    G[bi_products.offices] -->|INSERT| H[dim_office]
    I[bi_products.employees] -->|INSERT| J[dim_employee]
    K[orders.orderDate] -->|DATE_FORMAT| L[dim_date]

    B & D -->|SUM qty x price| M[fact_market_sales]
    D & F -->|SUM qty x price| N[fact_product_sales]
    D & H & J -->|SUM qty x price| O[fact_support_sales]
    B & D -->|SUM qty x price| P[fact_customer_sales]
    D & L -->|SUM qty x price| Q[fact_temporal_sales]
```

## Running

```bash
# 1. Create source database
mysql -u root -p1234 < products.sql

# 2. Create star schema
mysql -u root -p1234 < project.sql

# 3. Run ETL
mysql -u root -p1234 < populate_bi_project.sql
```

## Key Design Decisions

- **Surrogate keys** (`customer_key`, `product_key`, etc.) replace natural keys for faster joins
- **`salesAmount`** is computed as `quantityOrdered × priceEach` during ETL, not at query time
- **`date_key`** uses `YYYYMMDD` integer format for efficient date dimension lookups
- **Separate fact tables** per analytical question optimize each query path
