DROP DATABASE IF EXISTS a_bi;

CREATE DATABASE a_bi;

USE a_bi;

-- 1. DIMENSIONS
CREATE TABLE dim_customer (
    customer_key INT AUTO_INCREMENT PRIMARY KEY,
    customerNumber INT NOT NULL,
    customerName VARCHAR(50) NOT NULL,
    city VARCHAR(50) NOT NULL,
    state VARCHAR(50),
    country VARCHAR(50) NOT NULL
);

CREATE TABLE dim_order (
    order_key INT AUTO_INCREMENT PRIMARY KEY,
    orderNumber INT NOT NULL,
    orderLineNumber SMALLINT NOT NULL,
    status VARCHAR(15) NOT NULL
);

-- 2. FACT TABLE (Strictly FKs and Measures)
CREATE TABLE fact_market_sales (
    market_sales_key INT AUTO_INCREMENT PRIMARY KEY,
    customer_key INT NOT NULL,
    order_key INT NOT NULL,
    salesAmount DECIMAL(12, 2) NOT NULL,
    FOREIGN KEY (customer_key) REFERENCES dim_customer(customer_key),
    FOREIGN KEY (order_key) REFERENCES dim_order(order_key)
);

-- 3. ETL POPULATION
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
    ProductsBI.customers;

INSERT INTO
    dim_order (orderNumber, orderLineNumber, status)
SELECT
    o.orderNumber,
    od.orderLineNumber,
    o.status
FROM
    ProductsBI.orders o
    JOIN ProductsBI.orderdetails od ON o.orderNumber = od.orderNumber;

INSERT INTO
    fact_market_sales (customer_key, order_key, salesAmount)
SELECT
    dc.customer_key,
    do.order_key,
    (od.quantityOrdered * od.priceEach) AS salesAmount
FROM
    ProductsBI.orderdetails od
    JOIN ProductsBI.orders o ON od.orderNumber = o.orderNumber
    JOIN dim_customer dc ON o.customerNumber = dc.customerNumber
    JOIN dim_order do ON o.orderNumber = do.orderNumber
    AND od.orderLineNumber = do.orderLineNumber;

-- 4. ANALYTICAL QUERY
SELECT
    c.city,
    c.country,
    SUM(f.salesAmount) AS total_sales
FROM
    fact_market_sales f
    JOIN dim_customer c ON f.customer_key = c.customer_key
GROUP BY
    c.city,
    c.country
ORDER BY
    total_sales DESC
LIMIT
    1;