DROP DATABASE IF EXISTS CustomerSalesDW;
CREATE DATABASE CustomerSalesDW;
USE CustomerSalesDW;

-- 1. DIMENSIONS
CREATE TABLE dim_customer (
    customer_key INT AUTO_INCREMENT PRIMARY KEY,
    customerNumber INT NOT NULL,
    customerName VARCHAR(50) NOT NULL,
    contactLastName VARCHAR(50) NOT NULL,
    contactFirstName VARCHAR(50) NOT NULL,
    city VARCHAR(50) NOT NULL,
    country VARCHAR(50) NOT NULL
);

CREATE TABLE dim_order (
    order_key INT AUTO_INCREMENT PRIMARY KEY,
    orderNumber INT NOT NULL,
    orderLineNumber SMALLINT NOT NULL
);

-- 2. FACT TABLE (Strictly FKs and Measures)
CREATE TABLE fact_customer_sales (
    customer_sales_key INT AUTO_INCREMENT PRIMARY KEY,
    customer_key INT NOT NULL,
    order_key INT NOT NULL,
    salesAmount DECIMAL(12, 2) NOT NULL,
    FOREIGN KEY (customer_key) REFERENCES dim_customer(customer_key),
    FOREIGN KEY (order_key) REFERENCES dim_order(order_key)
);

-- 3. ETL DATA LOAD FROM SOURCE
INSERT INTO dim_customer (customerNumber, customerName, contactLastName, contactFirstName, city, country)
SELECT customerNumber, customerName, contactLastName, contactFirstName, city, country FROM ProductsBI.customers;

INSERT INTO dim_order (orderNumber, orderLineNumber)
SELECT orderNumber, orderLineNumber FROM ProductsBI.orderdetails;

INSERT INTO fact_customer_sales (customer_key, order_key, salesAmount)
SELECT 
    dc.customer_key,
    do.order_key,
    (od.quantityOrdered * od.priceEach) AS salesAmount
FROM ProductsBI.orderdetails od
JOIN ProductsBI.orders o ON od.orderNumber = o.orderNumber
JOIN dim_customer dc ON o.customerNumber = dc.customerNumber
JOIN dim_order do ON od.orderNumber = do.orderNumber AND od.orderLineNumber = do.orderLineNumber;

-- 4. ANALYTICAL QUERY TO ANSWER QUESTION D
SELECT 
    c.customerName,
    c.contactFirstName,
    c.contactLastName,
    SUM(f.salesAmount) AS total_revenue_generated
FROM fact_customer_sales f
JOIN dim_customer c ON f.customer_key = c.customer_key
GROUP BY c.customer_key, c.customerName, c.contactFirstName, c.contactLastName
ORDER BY total_revenue_generated DESC
LIMIT 1;