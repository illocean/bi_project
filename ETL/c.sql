DROP DATABASE IF EXISTS c_bi;
CREATE DATABASE c_bi;
USE c_bi;

-- 1. DIMENSIONS
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

CREATE TABLE dim_order (
    order_key INT AUTO_INCREMENT PRIMARY KEY,
    orderNumber INT NOT NULL,
    orderLineNumber SMALLINT NOT NULL
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

-- 3. ETL POPULATION
INSERT INTO dim_office (officeCode, city, state, country, territory)
SELECT officeCode, city, state, country, territory FROM ProductsBI.offices;

INSERT INTO dim_employee (employeeNumber, lastName, firstName, jobTitle)
SELECT employeeNumber, lastName, firstName, jobTitle FROM ProductsBI.employees;

INSERT INTO dim_order (orderNumber, orderLineNumber)
SELECT orderNumber, orderLineNumber FROM ProductsBI.orderdetails;

INSERT INTO fact_support_sales (office_key, employee_key, order_key, salesAmount)
SELECT 
    df.office_key,
    de.employee_key,
    do.order_key,
    (od.quantityOrdered * od.priceEach) AS salesAmount
FROM ProductsBI.orderdetails od
JOIN ProductsBI.orders o ON od.orderNumber = o.orderNumber
JOIN ProductsBI.customers c ON o.customerNumber = c.customerNumber
JOIN ProductsBI.employees e ON c.salesRepEmployeeNumber = e.employeeNumber
JOIN dim_employee de ON e.employeeNumber = de.employeeNumber
JOIN dim_office df ON e.officeCode = df.officeCode
JOIN dim_order do ON od.orderNumber = do.orderNumber AND od.orderLineNumber = do.orderLineNumber;

-- 4. ANALYTICAL QUERY
SELECT 
    o.officeCode, 
    o.city AS office_city, 
    o.country AS office_country,
    SUM(f.salesAmount) AS total_sales_supported
FROM fact_support_sales f
JOIN dim_office o ON f.office_key = o.office_key
GROUP BY o.officeCode, o.city, o.country
ORDER BY total_sales_supported DESC
LIMIT 1;