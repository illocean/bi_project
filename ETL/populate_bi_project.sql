-- ETL: Populate bi_project star schema from bi_products source data
USE bi_project;

-- Populate dim_customer from bi_products
INSERT INTO
    dim_customer (
        customerNumber,
        customerName,
        city,
        state,
        country
    )
SELECT
    customerNumber,
    customerName,
    city,
    state,
    country
FROM
    bi_products.customers;

-- Populate dim_order from bi_products
INSERT INTO
    dim_order (orderNumber, orderLineNumber, status)
SELECT
    o.orderNumber,
    od.orderLineNumber,
    o.status
FROM
    bi_products.orders o
    JOIN bi_products.orderdetails od ON o.orderNumber = od.orderNumber;

-- Populate dim_product from bi_products
INSERT INTO
    dim_product (
        productCode,
        productName,
        productLine,
        productScale,
        productVendor
    )
SELECT
    productCode,
    productName,
    productLine,
    productScale,
    productVendor
FROM
    bi_products.products;

-- Populate dim_office from bi_products
INSERT INTO
    dim_office (officeCode, city, state, country, territory)
SELECT
    officeCode,
    city,
    state,
    country,
    territory
FROM
    bi_products.offices;

-- Populate dim_employee from bi_products
INSERT INTO
    dim_employee (employeeNumber, lastName, firstName, jobTitle)
SELECT
    employeeNumber,
    lastName,
    firstName,
    jobTitle
FROM
    bi_products.employees;

-- Populate dim_date from bi_products orders
INSERT INTO
    dim_date (
        date_key,
        full_date,
        month_number,
        month_name,
        quarter,
        year
    )
SELECT
    DISTINCT DATE_FORMAT(orderDate, '%Y%m%d') * 1 AS date_key,
    orderDate AS full_date,
    MONTH(orderDate) AS month_number,
    MONTHNAME(orderDate) AS month_name,
    QUARTER(orderDate) AS quarter,
    YEAR(orderDate) AS year
FROM
    bi_products.orders;

-- Populate fact_market_sales
INSERT INTO
    fact_market_sales (customer_key, order_key, salesAmount)
SELECT
    dc.customer_key,
    do.order_key,
    (od.quantityOrdered * od.priceEach) AS salesAmount
FROM
    bi_products.orderdetails od
    JOIN bi_products.orders o ON od.orderNumber = o.orderNumber
    JOIN dim_customer dc ON o.customerNumber = dc.customerNumber
    JOIN dim_order do ON o.orderNumber = do.orderNumber
    AND od.orderLineNumber = do.orderLineNumber;

-- Populate fact_product_sales
INSERT INTO
    fact_product_sales (
        product_key,
        order_key,
        quantityOrdered,
        salesAmount
    )
SELECT
    dp.product_key,
    do.order_key,
    od.quantityOrdered,
    (od.quantityOrdered * od.priceEach) AS salesAmount
FROM
    bi_products.orderdetails od
    JOIN dim_product dp ON od.productCode = dp.productCode
    JOIN dim_order do ON od.orderNumber = do.orderNumber
    AND od.orderLineNumber = do.orderLineNumber;

-- fact_support_sales: traces order -> customer -> salesRep employee -> office
INSERT INTO
    fact_support_sales (office_key, employee_key, order_key, salesAmount)
SELECT
    df.office_key,
    de.employee_key,
    do.order_key,
    (od.quantityOrdered * od.priceEach) AS salesAmount
FROM
    bi_products.orderdetails od
    JOIN bi_products.orders o ON od.orderNumber = o.orderNumber
    JOIN bi_products.customers c ON o.customerNumber = c.customerNumber
    JOIN bi_products.employees e ON c.salesRepEmployeeNumber = e.employeeNumber
    JOIN dim_employee de ON e.employeeNumber = de.employeeNumber
    JOIN dim_office df ON e.officeCode = df.officeCode
    JOIN dim_order do ON od.orderNumber = do.orderNumber
    AND od.orderLineNumber = do.orderLineNumber;

-- Populate fact_customer_sales
INSERT INTO
    fact_customer_sales (customer_key, order_key, salesAmount)
SELECT
    dc.customer_key,
    do.order_key,
    (od.quantityOrdered * od.priceEach) AS salesAmount
FROM
    bi_products.orderdetails od
    JOIN bi_products.orders o ON od.orderNumber = o.orderNumber
    JOIN dim_customer dc ON o.customerNumber = dc.customerNumber
    JOIN dim_order do ON o.orderNumber = do.orderNumber
    AND od.orderLineNumber = do.orderLineNumber;

-- Populate fact_temporal_sales
INSERT INTO
    fact_temporal_sales (date_key, order_key, salesAmount)
SELECT
    DATE_FORMAT(o.orderDate, '%Y%m%d') * 1 AS date_key,
    do.order_key,
    (od.quantityOrdered * od.priceEach) AS salesAmount
FROM
    bi_products.orderdetails od
    JOIN bi_products.orders o ON od.orderNumber = o.orderNumber
    JOIN dim_order do ON o.orderNumber = do.orderNumber
    AND od.orderLineNumber = do.orderLineNumber;