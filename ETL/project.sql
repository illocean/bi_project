DROP DATABASE IF EXISTS bi_project;
DROP DATABASE IF EXISTS bi_projects;

CREATE DATABASE IF NOT EXISTS bi_project;

USE bi_project;

-- -A: Best Market for Sales (Geographic Focus)-------------------------------------------------------------------------------------------------
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

-- -B: Highest Sales by Product (Product Performance Focus)-------------------------------------------------------------------------------------------------
CREATE TABLE dim_product (
    product_key INT AUTO_INCREMENT PRIMARY KEY,
    productCode VARCHAR(15) NOT NULL,
    productName VARCHAR(70) NOT NULL,
    productLine VARCHAR(50) NOT NULL,
    productScale VARCHAR(10) NOT NULL,
    productVendor VARCHAR(50) NOT NULL
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

-- -C: Best Sales Support by Office (Organizational Focus)-------------------------------------------------------------------------------------------------
CREATE TABLE dim_office (
    office_key INT AUTO_INCREMENT PRIMARY KEY,
    officeCode VARCHAR(10) NOT NULL,
    city VARCHAR(50) NOT NULL,
    state VARCHAR(50),
    country VARCHAR(50) NOT NULL,
    territory VARCHAR(10) NOT NULL
);

CREATE TABLE dim_employee (
    employee_key INT AUTO_INCREMENT PRIMARY KEY,
    employeeNumber INT NOT NULL,
    lastName VARCHAR(50) NOT NULL,
    firstName VARCHAR(50) NOT NULL,
    jobTitle VARCHAR(50) NOT NULL
);

-- 2. FACT TABLE (Strictly FKs and Measures)
CREATE TABLE fact_support_sales (
    support_sales_key INT AUTO_INCREMENT PRIMARY KEY,
    office_key INT NOT NULL,
    employee_key INT NOT NULL,
    order_key INT NOT NULL,
    salesAmount DECIMAL(12, 2) NOT NULL,
    FOREIGN KEY (office_key) REFERENCES dim_office(office_key),
    FOREIGN KEY (employee_key) REFERENCES dim_employee(employee_key),
    FOREIGN KEY (order_key) REFERENCES dim_order(order_key)
);

-- -D: Which individual customer generates the highest sales revenue?---------------------------------------------------------------------------

-- 2. FACT TABLE (Strictly FKs and Measures)
CREATE TABLE fact_customer_sales (
    customer_sales_key INT AUTO_INCREMENT PRIMARY KEY,
    customer_key INT NOT NULL,
    order_key INT NOT NULL,
    salesAmount DECIMAL(12, 2) NOT NULL,
    FOREIGN KEY (customer_key) REFERENCES dim_customer(customer_key),
    FOREIGN KEY (order_key) REFERENCES dim_order(order_key)
);

-- -E: Which historical year and month experienced the highest sales volume?---------------------------------------------------------------------
CREATE TABLE dim_date (
    date_key INT PRIMARY KEY,
    -- Formatted as YYYYMMDD
    full_date DATE NOT NULL,
    month_number INT NOT NULL,
    month_name VARCHAR(15) NOT NULL,
    quarter INT NOT NULL,
    year INT NOT NULL
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

-- ---------------------------------------------------------------------------------------------------