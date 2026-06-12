DROP DATABASE IF EXISTS b_bi;
CREATE DATABASE b_bi;
USE b_bi;

-- 1. DIMENSIONS
CREATE TABLE dim_product (
    product_key INT AUTO_INCREMENT PRIMARY KEY,
    productCode VARCHAR(15) NOT NULL,
    productName VARCHAR(70) NOT NULL,
    productLine VARCHAR(50) NOT NULL,
    productScale VARCHAR(10) NOT NULL,
    productVendor VARCHAR(50) NOT NULL
);

CREATE TABLE dim_order (
    order_key INT AUTO_INCREMENT PRIMARY KEY,
    orderNumber INT NOT NULL,
    orderLineNumber SMALLINT NOT NULL
);

-- 2. FACT TABLE (Strictly FKs and Measures)
CREATE TABLE fact_product_sales (
    product_sales_key INT AUTO_INCREMENT PRIMARY KEY,
    product_key INT NOT NULL,
    order_key INT NOT NULL,
    quantityOrdered INT NOT NULL,
    salesAmount DECIMAL(12, 2) NOT NULL,
    FOREIGN KEY (product_key) REFERENCES dim_product(product_key),
    FOREIGN KEY (order_key) REFERENCES dim_order(order_key)
);

-- 3. ETL POPULATION
INSERT INTO dim_product (productCode, productName, productLine, productScale, productVendor)
SELECT productCode, productName, productLine, productScale, productVendor FROM ProductsBI.products;

INSERT INTO dim_order (orderNumber, orderLineNumber)
SELECT orderNumber, orderLineNumber FROM ProductsBI.orderdetails;

INSERT INTO fact_product_sales (product_key, order_key, quantityOrdered, salesAmount)
SELECT 
    dp.product_key,
    do.order_key,
    od.quantityOrdered,
    (od.quantityOrdered * od.priceEach) AS salesAmount
FROM ProductsBI.orderdetails od
JOIN dim_product dp ON od.productCode = dp.productCode
JOIN dim_order do ON od.orderNumber = do.orderNumber AND od.orderLineNumber = do.orderLineNumber;

-- 4. ANALYTICAL QUERY
SELECT 
    p.productName, 
    p.productLine,
    SUM(f.salesAmount) AS total_sales
FROM fact_product_sales f
JOIN dim_product p ON f.product_key = p.product_key
GROUP BY p.productCode, p.productName, p.productLine
ORDER BY total_sales DESC
LIMIT 1;