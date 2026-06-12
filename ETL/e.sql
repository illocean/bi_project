DROP DATABASE IF EXISTS TemporalSalesDW;
CREATE DATABASE TemporalSalesDW;
USE TemporalSalesDW;

-- 1. DIMENSIONS
CREATE TABLE dim_date (
    date_key INT PRIMARY KEY, -- Formatted as YYYYMMDD
    full_date DATE NOT NULL,
    month_number INT NOT NULL,
    month_name VARCHAR(15) NOT NULL,
    quarter INT NOT NULL,
    year INT NOT NULL
);

CREATE TABLE dim_order (
    order_key INT AUTO_INCREMENT PRIMARY KEY,
    orderNumber INT NOT NULL,
    orderLineNumber SMALLINT NOT NULL
);

-- 2. FACT TABLE (Strictly FKs and Measures)
CREATE TABLE fact_temporal_sales (
    temporal_sales_key INT AUTO_INCREMENT PRIMARY KEY,
    date_key INT NOT NULL,
    order_key INT NOT NULL,
    salesAmount DECIMAL(12, 2) NOT NULL,
    FOREIGN KEY (date_key) REFERENCES dim_date(date_key),
    FOREIGN KEY (order_key) REFERENCES dim_order(order_key)
);

-- 3. ETL DATA LOAD FROM SOURCE
INSERT INTO dim_date (date_key, full_date, month_number, month_name, quarter, year)
SELECT DISTINCT
    CAST(DATE_FORMAT(orderDate, '%Y%m%d') AS UNSIGNED) AS date_key,
    orderDate AS full_date,
    MONTH(orderDate) AS month_number,
    MONTHNAME(orderDate) AS month_name,
    QUARTER(orderDate) AS quarter,
    YEAR(orderDate) AS year
FROM ProductsBI.orders;

INSERT INTO dim_order (orderNumber, orderLineNumber)
SELECT orderNumber, orderLineNumber FROM ProductsBI.orderdetails;

INSERT INTO fact_temporal_sales (date_key, order_key, salesAmount)
SELECT 
    dd.date_key,
    do.order_key,
    (od.quantityOrdered * od.priceEach) AS salesAmount
FROM ProductsBI.orderdetails od
JOIN ProductsBI.orders o ON od.orderNumber = o.orderNumber
JOIN dim_date dd ON CAST(DATE_FORMAT(o.orderDate, '%Y%m%d') AS UNSIGNED) = dd.date_key
JOIN dim_order do ON od.orderNumber = do.orderNumber AND od.orderLineNumber = do.orderLineNumber;

-- 4. ANALYTICAL QUERY TO ANSWER QUESTION E
SELECT 
    d.year,
    d.month_name,
    SUM(f.salesAmount) AS total_sales
FROM fact_temporal_sales f
JOIN dim_date d ON f.date_key = d.date_key
GROUP BY d.year, d.month_number, d.month_name
ORDER BY total_sales DESC
LIMIT 1;