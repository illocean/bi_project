
DROP DATABASE IF EXISTS bi_products;
DROP DATABASE IF EXISTS bi_product;

CREATE DATABASE IF NOT EXISTS bi_products;

USE bi_products;

-- 1. productlines
CREATE TABLE productlines (
    productLine VARCHAR(50) NOT NULL,
    textDescription VARCHAR(4000),
    htmlDescription MEDIUMTEXT,
    image MEDIUMBLOB,
    PRIMARY KEY (productLine)
);

-- 2. products
CREATE TABLE products (
    productCode VARCHAR(15) NOT NULL,
    productName VARCHAR(70) NOT NULL,
    productLine VARCHAR(50) NOT NULL,
    productScale VARCHAR(10) NOT NULL,
    productVendor VARCHAR(50) NOT NULL,
    productDescription TEXT NOT NULL,
    quantityInStock SMALLINT NOT NULL,
    buyPrice DECIMAL(10, 2) NOT NULL,
    MSRP DECIMAL(10, 2) NOT NULL,
    PRIMARY KEY (productCode),
    FOREIGN KEY (productLine) REFERENCES productlines(productLine)
);

-- 3. offices
CREATE TABLE offices (
    officeCode VARCHAR(10) NOT NULL,
    city VARCHAR(50) NOT NULL,
    phone VARCHAR(50) NOT NULL,
    addressLine1 VARCHAR(50) NOT NULL,
    addressLine2 VARCHAR(50),
    state VARCHAR(50),
    country VARCHAR(50) NOT NULL,
    postalCode VARCHAR(15) NOT NULL,
    territory VARCHAR(10) NOT NULL,
    PRIMARY KEY (officeCode)
);

-- 4. employees
CREATE TABLE employees (
    employeeNumber INT NOT NULL,
    lastName VARCHAR(50) NOT NULL,
    firstName VARCHAR(50) NOT NULL,
    extension VARCHAR(10) NOT NULL,
    email VARCHAR(100) NOT NULL,
    officeCode VARCHAR(10) NOT NULL,
    reportsTo INT,
    jobTitle VARCHAR(50) NOT NULL,
    PRIMARY KEY (employeeNumber),
    FOREIGN KEY (officeCode) REFERENCES offices(officeCode),
    FOREIGN KEY (reportsTo) REFERENCES employees(employeeNumber)
);

-- 5. customers
CREATE TABLE customers (
    customerNumber INT NOT NULL,
    customerName VARCHAR(50) NOT NULL,
    contactLastName VARCHAR(50) NOT NULL,
    contactFirstName VARCHAR(50) NOT NULL,
    phone VARCHAR(50) NOT NULL,
    addressLine1 VARCHAR(50) NOT NULL,
    addressLine2 VARCHAR(50),
    city VARCHAR(50) NOT NULL,
    state VARCHAR(50),
    postalCode VARCHAR(15),
    country VARCHAR(50) NOT NULL,
    salesRepEmployeeNumber INT,
    creditLimit DECIMAL(10, 2),
    PRIMARY KEY (customerNumber),
    FOREIGN KEY (salesRepEmployeeNumber) REFERENCES employees(employeeNumber)
);

-- 6. orders
CREATE TABLE orders (
    orderNumber INT NOT NULL,
    orderDate DATE NOT NULL,
    requiredDate DATE NOT NULL,
    shippedDate DATE,
    status VARCHAR(15) NOT NULL,
    comments TEXT,
    customerNumber INT NOT NULL,
    PRIMARY KEY (orderNumber),
    FOREIGN KEY (customerNumber) REFERENCES customers(customerNumber)
);

-- 7. orderdetails
CREATE TABLE orderdetails (
    orderNumber INT NOT NULL,
    productCode VARCHAR(15) NOT NULL,
    quantityOrdered INT NOT NULL,
    priceEach DECIMAL(10, 2) NOT NULL,
    orderLineNumber SMALLINT NOT NULL,
    PRIMARY KEY (orderNumber, productCode),
    FOREIGN KEY (orderNumber) REFERENCES orders(orderNumber),
    FOREIGN KEY (productCode) REFERENCES products(productCode)
);

-- 8. payments
CREATE TABLE payments (
    customerNumber INT NOT NULL,
    checkNumber VARCHAR(50) NOT NULL,
    paymentDate DATE NOT NULL,
    amount DECIMAL(10, 2) NOT NULL,
    PRIMARY KEY (customerNumber, checkNumber),
    FOREIGN KEY (customerNumber) REFERENCES customers(customerNumber)
);

INSERT INTO
    productlines (
        productLine,
        textDescription,
        htmlDescription,
        image
    )
VALUES
    (
        'Classic Cars',
        'Replica and classic car models',
        NULL,
        NULL
    ),
    (
        'Motorcycles',
        'Street and racing motorcycle models',
        NULL,
        NULL
    ),
    (
        'Planes',
        'Commercial and military plane models',
        NULL,
        NULL
    ),
    (
        'Laptops',
        'Gaming and office laptops',
        NULL,
        NULL
    );

INSERT INTO
    products (
        productCode,
        productName,
        productLine,
        productScale,
        productVendor,
        productDescription,
        quantityInStock,
        buyPrice,
        MSRP
    )
VALUES
    (
        'S10_001',
        '1948 Porsche 356-A Roadster',
        'Classic Cars',
        '1:18',
        'Min Lin Diecast',
        'Detailed die-cast model with opening parts.',
        120,
        53.90,
        95.70
    ),
    (
        'S10_002',
        '1965 Aston Martin DB5',
        'Classic Cars',
        '1:18',
        'Highway 66 Mini Classics',
        'Collector model with chrome trim details.',
        98,
        65.10,
        124.50
    ),
    (
        'S10_003',
        '1969 Dodge Charger',
        'Classic Cars',
        '1:18',
        'Autoart Studio Design',
        'Muscle car model with interior detailing.',
        150,
        48.75,
        92.00
    ),
    (
        'S20_001',
        '2002 Suzuki XREO',
        'Motorcycles',
        '1:10',
        'Red Start Diecast',
        'Sports motorcycle model with racing livery.',
        210,
        33.20,
        66.40
    ),
    (
        'S20_002',
        '1982 Ducati 900',
        'Motorcycles',
        '1:10',
        'Gearbox Collectibles',
        'Classic superbike replica model.',
        170,
        41.80,
        79.95
    ),
    (
        'S20_003',
        '1997 BMW R 1100 S',
        'Motorcycles',
        '1:10',
        'Motor City Art Classics',
        'Touring motorcycle model with side details.',
        140,
        37.60,
        72.50
    ),
    (
        'S30_001',
        'ATA: B757-300',
        'Planes',
        '1:72',
        'Second Gear Diecast',
        'Twin-engine passenger aircraft model.',
        80,
        72.90,
        138.00
    ),
    (
        'S30_002',
        'F/A 18 Hornet 1/72',
        'Planes',
        '1:72',
        'Gearbox Collectibles',
        'Military jet model with painted markings.',
        65,
        68.40,
        129.90
    ),
    (
        'S30_003',
        'Boeing X-32A JSF',
        'Planes',
        '1:72',
        'Motor City Art Classics',
        'Modern prototype aircraft display model.',
        90,
        70.00,
        134.25
    ),
    (
        'S40_001',
        'Lenovo Yoga Pro 9i Aura Edition',
        'Laptops',
        '1:90',
        'Gaming laptop buy and sell',
        'Basta siya ay on top!',
        15,
        400,
        500.23
    );

INSERT INTO
    offices (
        officeCode,
        city,
        phone,
        addressLine1,
        addressLine2,
        state,
        country,
        postalCode,
        territory
    )
VALUES
    (
        '1',
        'San Francisco',
        '+1 650 219 4782',
        '100 Market Street',
        'Suite 300',
        'CA',
        'USA',
        '94080',
        'NA'
    ),
    (
        '2',
        'Boston',
        '+1 215 837 0825',
        '1550 Court Place',
        'Suite 102',
        'MA',
        'USA',
        '02107',
        'NA'
    );

INSERT INTO
    employees (
        employeeNumber,
        lastName,
        firstName,
        extension,
        email,
        officeCode,
        reportsTo,
        jobTitle
    )
VALUES
    (
        1002,
        'Murphy',
        'Diane',
        'x5800',
        'dmurphy@classicmodelcars.com',
        '1',
        NULL,
        'President'
    ),
    (
        1056,
        'Patterson',
        'Mary',
        'x4611',
        'mpatterson@classicmodelcars.com',
        '1',
        1002,
        'VP Sales'
    ),
    (
        1076,
        'Firrelli',
        'Jeff',
        'x9273',
        'jfirrelli@classicmodelcars.com',
        '1',
        1056,
        'Sales Rep'
    ),
    (
        1088,
        'Jennings',
        'Julie',
        'x4871',
        'jjennings@classicmodelcars.com',
        '2',
        1056,
        'Sales Rep'
    );

INSERT INTO
    customers (
        customerNumber,
        customerName,
        contactLastName,
        contactFirstName,
        phone,
        addressLine1,
        addressLine2,
        city,
        state,
        postalCode,
        country,
        salesRepEmployeeNumber,
        creditLimit
    )
VALUES
    (
        103,
        'Atelier graphique',
        'Schmitt',
        'Carine',
        '40.32.2555',
        '54, rue Royale',
        NULL,
        'Nantes',
        NULL,
        '44000',
        'France',
        1076,
        21000.00
    ),
    (
        112,
        'Signal Gift Stores',
        'King',
        'Jean',
        '7025551838',
        '8489 Strong St.',
        NULL,
        'Las Vegas',
        'NV',
        '83030',
        'USA',
        1088,
        71800.00
    ),
    (
        114,
        'Australian Collectors Co.',
        'Ferguson',
        'Peter',
        '03 9520 4555',
        '636 St Kilda Road',
        'Level 3',
        'Melbourne',
        'VIC',
        '3004',
        'Australia',
        1076,
        117300.00
    ),
    (
        119,
        'La Rochelle Gifts',
        'Labrune',
        'Janine',
        '40.67.8555',
        '67, rue des Cinquante Otages',
        NULL,
        'Nantes',
        NULL,
        '44000',
        'France',
        1088,
        118200.00
    ),
    (
        121,
        'Baane Mini Imports',
        'Bergulfsen',
        'Jonas',
        '07-98 9555',
        'Erling Skakkes gate 78',
        NULL,
        'Stavern',
        NULL,
        '4110',
        'Norway',
        1076,
        81700.00
    );

INSERT INTO
    orders (
        orderNumber,
        orderDate,
        requiredDate,
        shippedDate,
        status,
        comments,
        customerNumber
    )
VALUES
    (
        2001,
        '2026-01-05',
        '2026-01-12',
        '2026-01-09',
        'Shipped',
        'Priority handling requested.',
        103
    ),
    (
        2002,
        '2026-01-06',
        '2026-01-14',
        '2026-01-11',
        'Shipped',
        NULL,
        112
    ),
    (
        2003,
        '2026-01-08',
        '2026-01-16',
        '2026-01-13',
        'Shipped',
        NULL,
        114
    ),
    (
        2004,
        '2026-01-10',
        '2026-01-18',
        '2026-01-14',
        'Shipped',
        'Gift wrapping for select items.',
        119
    ),
    (
        2005,
        '2026-01-12',
        '2026-01-20',
        '2026-01-16',
        'Shipped',
        NULL,
        121
    ),
    (
        2006,
        '2026-01-15',
        '2026-01-23',
        '2026-01-19',
        'Shipped',
        NULL,
        103
    ),
    (
        2007,
        '2026-01-17',
        '2026-01-25',
        '2026-01-21',
        'Shipped',
        NULL,
        112
    ),
    (
        2008,
        '2026-01-20',
        '2026-01-28',
        '2026-01-24',
        'Shipped',
        'Deliver before weekend.',
        114
    ),
    (
        2009,
        '2026-01-22',
        '2026-01-30',
        '2026-01-26',
        'Shipped',
        NULL,
        119
    ),
    (
        2010,
        '2026-01-24',
        '2026-02-01',
        '2026-01-29',
        'Shipped',
        NULL,
        121
    ),
    (
        2011,
        '2026-01-27',
        '2026-02-04',
        '2026-01-31',
        'Shipped',
        NULL,
        103
    ),
    (
        2012,
        '2026-01-29',
        '2026-02-06',
        NULL,
        'In Process',
        'Awaiting final packaging.',
        112
    ),
    (
        2013,
        '2026-02-01',
        '2026-02-09',
        NULL,
        'In Process',
        NULL,
        114
    ),
    (
        2014,
        '2026-02-03',
        '2026-02-11',
        NULL,
        'In Process',
        'Split shipment allowed.',
        119
    ),
    (
        2015,
        '2026-02-05',
        '2026-02-13',
        NULL,
        'In Process',
        NULL,
        121
    ),
    -- March 2026
    (2016, '2026-03-02', '2026-03-09', '2026-03-06', 'Shipped', NULL, 103),
    (2017, '2026-03-10', '2026-03-18', '2026-03-14', 'Shipped', NULL, 112),
    (2018, '2026-03-20', '2026-03-28', '2026-03-25', 'Shipped', 'Express delivery.', 114),
    -- April 2026
    (2019, '2026-04-05', '2026-04-13', '2026-04-09', 'Shipped', NULL, 119),
    (2020, '2026-04-18', '2026-04-26', '2026-04-22', 'Shipped', NULL, 121),
    -- May 2026
    (2021, '2026-05-03', '2026-05-11', '2026-05-07', 'Shipped', 'Bulk order discount applied.', 103),
    (2022, '2026-05-15', '2026-05-23', '2026-05-19', 'Shipped', NULL, 112),
    -- June 2026
    (2023, '2026-06-01', '2026-06-09', '2026-06-05', 'Shipped', NULL, 114),
    (2024, '2026-06-12', '2026-06-20', '2026-06-16', 'Shipped', NULL, 119),
    -- July 2026
    (2025, '2026-07-04', '2026-07-12', '2026-07-08', 'Shipped', 'Holiday season order.', 121),
    (2026, '2026-07-18', '2026-07-26', '2026-07-22', 'Shipped', NULL, 103),
    -- August 2026
    (2027, '2026-08-02', '2026-08-10', '2026-08-06', 'Shipped', NULL, 112),
    (2028, '2026-08-20', '2026-08-28', '2026-08-24', 'Shipped', NULL, 114),
    -- September 2026
    (2029, '2026-09-05', '2026-09-13', '2026-09-09', 'Shipped', 'Back to school promo.', 119),
    (2030, '2026-09-18', '2026-09-26', '2026-09-22', 'Shipped', NULL, 121),
    -- October 2026
    (2031, '2026-10-03', '2026-10-11', '2026-10-07', 'Shipped', NULL, 103),
    (2032, '2026-10-20', '2026-10-28', '2026-10-24', 'Shipped', NULL, 112),
    -- November 2026
    (2033, '2026-11-05', '2026-11-13', '2026-11-09', 'Shipped', 'Black Friday sale.', 114),
    (2034, '2026-11-22', '2026-11-30', '2026-11-26', 'Shipped', NULL, 119),
    -- December 2026
    (2035, '2026-12-01', '2026-12-09', '2026-12-05', 'Shipped', 'Holiday gift orders.', 121),
    (2036, '2026-12-15', '2026-12-23', '2026-12-19', 'Shipped', 'Year-end clearance.', 103),
    -- 2025 orders
    (3001, '2025-01-10', '2025-01-18', '2025-01-14', 'Shipped', NULL, 103),
    (3002, '2025-02-05', '2025-02-13', '2025-02-09', 'Shipped', NULL, 112),
    (3003, '2025-03-12', '2025-03-20', '2025-03-16', 'Shipped', NULL, 114),
    (3004, '2025-04-08', '2025-04-16', '2025-04-12', 'Shipped', NULL, 119),
    (3005, '2025-05-15', '2025-05-23', '2025-05-19', 'Shipped', NULL, 121),
    (3006, '2025-06-10', '2025-06-18', '2025-06-14', 'Shipped', NULL, 103),
    (3007, '2025-07-20', '2025-07-28', '2025-07-24', 'Shipped', NULL, 112),
    (3008, '2025-08-05', '2025-08-13', '2025-08-09', 'Shipped', NULL, 114),
    (3009, '2025-09-12', '2025-09-20', '2025-09-16', 'Shipped', NULL, 119),
    (3010, '2025-10-08', '2025-10-16', '2025-10-12', 'Shipped', NULL, 121),
    (3011, '2025-11-15', '2025-11-23', '2025-11-19', 'Shipped', NULL, 103),
    (3012, '2025-12-10', '2025-12-18', '2025-12-14', 'Shipped', NULL, 112);

INSERT INTO
    orderdetails (
        orderNumber,
        productCode,
        quantityOrdered,
        priceEach,
        orderLineNumber
    )
VALUES
    (2001, 'S10_001', 25, 95.70, 1),
    (2001, 'S20_001', 30, 66.40, 2),
    (2001, 'S30_001', 12, 138.00, 3),
    (2002, 'S10_002', 20, 124.50, 1),
    (2002, 'S20_002', 18, 79.95, 2),
    (2002, 'S30_002', 10, 129.90, 3),
    (2003, 'S10_003', 35, 92.00, 1),
    (2003, 'S20_003', 16, 72.50, 2),
    (2003, 'S30_003', 8, 134.25, 3),
    (2004, 'S10_001', 22, 95.70, 1),
    (2004, 'S20_002', 14, 79.95, 2),
    (2004, 'S30_001', 9, 138.00, 3),
    (2005, 'S10_002', 19, 124.50, 1),
    (2005, 'S20_001', 28, 66.40, 2),
    (2005, 'S30_003', 7, 134.25, 3),
    (2006, 'S10_003', 24, 92.00, 1),
    (2006, 'S20_003', 15, 72.50, 2),
    (2006, 'S30_002', 11, 129.90, 3),
    (2007, 'S10_001', 21, 95.70, 1),
    (2007, 'S20_002', 20, 79.95, 2),
    (2007, 'S30_003', 9, 134.25, 3),
    (2008, 'S10_002', 18, 124.50, 1),
    (2008, 'S20_001', 25, 66.40, 2),
    (2008, 'S30_001', 10, 138.00, 3),
    (2009, 'S10_003', 30, 92.00, 1),
    (2009, 'S20_003', 14, 72.50, 2),
    (2009, 'S30_002', 8, 129.90, 3),
    (2010, 'S10_001', 27, 95.70, 1),
    (2010, 'S20_002', 17, 79.95, 2),
    (2010, 'S30_003', 6, 134.25, 3),
    (2011, 'S10_002', 23, 124.50, 1),
    (2011, 'S20_001', 26, 66.40, 2),
    (2011, 'S30_001', 9, 138.00, 3),
    (2012, 'S10_003', 28, 92.00, 1),
    (2012, 'S20_003', 13, 72.50, 2),
    (2012, 'S30_002', 7, 129.90, 3),
    (2013, 'S10_001', 20, 95.70, 1),
    (2013, 'S20_002', 16, 79.95, 2),
    (2013, 'S30_003', 8, 134.25, 3),
    (2014, 'S10_002', 26, 124.50, 1),
    (2014, 'S20_001', 24, 66.40, 2),
    (2014, 'S30_001', 10, 138.00, 3),
    (2015, 'S10_003', 29, 92.00, 1),
    (2015, 'S20_003', 12, 72.50, 2),
    (2015, 'S30_002', 9, 129.90, 3),

    -- March 2026
    (2016, 'S10_001', 30, 95.70, 1),
    (2016, 'S20_002', 22, 79.95, 2),
    (2017, 'S10_002', 15, 124.50, 1),
    (2017, 'S30_001', 18, 138.00, 2),
    (2018, 'S20_003', 25, 72.50, 1),
    (2018, 'S30_003', 12, 134.25, 2),

    -- April 2026
    (2019, 'S10_003', 35, 92.00, 1),
    (2019, 'S20_001', 20, 66.40, 2),
    (2020, 'S10_001', 28, 95.70, 1),
    (2020, 'S30_002', 14, 129.90, 2),

    -- May 2026
    (2021, 'S10_002', 40, 124.50, 1),
    (2021, 'S20_002', 18, 79.95, 2),
    (2021, 'S30_001', 10, 138.00, 3),
    (2022, 'S10_003', 22, 92.00, 1),
    (2022, 'S20_003', 16, 72.50, 2),

    -- June 2026
    (2023, 'S10_001', 32, 95.70, 1),
    (2023, 'S20_001', 24, 66.40, 2),
    (2023, 'S30_003', 8, 134.25, 3),
    (2024, 'S10_002', 19, 124.50, 1),
    (2024, 'S30_002', 11, 129.90, 2),

    -- July 2026
    (2025, 'S10_003', 27, 92.00, 1),
    (2025, 'S20_002', 21, 79.95, 2),
    (2026, 'S10_001', 35, 95.70, 1),
    (2026, 'S20_001', 28, 66.40, 2),
    (2026, 'S30_001', 15, 138.00, 3),

    -- August 2026
    (2027, 'S10_002', 25, 124.50, 1),
    (2027, 'S20_003', 18, 72.50, 2),
    (2028, 'S10_003', 30, 92.00, 1),
    (2028, 'S30_003', 10, 134.25, 2),

    -- September 2026
    (2029, 'S10_001', 22, 95.70, 1),
    (2029, 'S20_002', 16, 79.95, 2),
    (2029, 'S30_002', 12, 129.90, 3),
    (2030, 'S10_002', 28, 124.50, 1),
    (2030, 'S20_001', 20, 66.40, 2),

    -- October 2026
    (2031, 'S10_003', 38, 92.00, 1),
    (2031, 'S20_003', 22, 72.50, 2),
    (2031, 'S30_001', 14, 138.00, 3),
    (2032, 'S10_001', 26, 95.70, 1),
    (2032, 'S30_003', 9, 134.25, 2),

    -- November 2026
    (2033, 'S10_002', 42, 124.50, 1),
    (2033, 'S20_002', 30, 79.95, 2),
    (2033, 'S30_002', 16, 129.90, 3),
    (2034, 'S10_003', 20, 92.00, 1),
    (2034, 'S20_001', 24, 66.40, 2),

    -- December 2026
    (2035, 'S10_001', 45, 95.70, 1),
    (2035, 'S20_003', 28, 72.50, 2),
    (2035, 'S30_001', 18, 138.00, 3),
    (2036, 'S10_002', 35, 124.50, 1),
    (2036, 'S20_002', 22, 79.95, 2),
    (2036, 'S30_003', 12, 134.25, 3),

    -- 2025 orders (for year-over-year comparison)
    (3001, 'S10_001', 18, 95.70, 1),
    (3001, 'S20_001', 15, 66.40, 2),
    (3002, 'S10_002', 22, 124.50, 1),
    (3002, 'S30_002', 10, 129.90, 2),
    (3003, 'S10_003', 25, 92.00, 1),
    (3003, 'S20_003', 12, 72.50, 2),
    (3004, 'S10_001', 30, 95.70, 1),
    (3004, 'S30_001', 8, 138.00, 2),
    (3005, 'S10_002', 20, 124.50, 1),
    (3005, 'S20_002', 18, 79.95, 2),
    (3006, 'S10_003', 28, 92.00, 1),
    (3006, 'S30_003', 14, 134.25, 2),
    (3007, 'S10_001', 35, 95.70, 1),
    (3007, 'S20_001', 22, 66.40, 2),
    (3007, 'S30_002', 10, 129.90, 3),
    (3008, 'S10_002', 24, 124.50, 1),
    (3008, 'S20_003', 16, 72.50, 2),
    (3009, 'S10_003', 32, 92.00, 1),
    (3009, 'S30_001', 12, 138.00, 2),
    (3010, 'S10_001', 20, 95.70, 1),
    (3010, 'S20_002', 14, 79.95, 2),
    (3011, 'S10_002', 28, 124.50, 1),
    (3011, 'S30_003', 9, 134.25, 2),
    (3012, 'S10_003', 30, 92.00, 1),
    (3012, 'S20_001', 20, 66.40, 2);

INSERT INTO
    payments (customerNumber, checkNumber, paymentDate, amount)
VALUES
    (103, 'CHK-2026-001', '2026-01-10', 4500.00),
    (112, 'CHK-2026-002', '2026-01-12', 6200.00),
    (114, 'CHK-2026-003', '2026-01-15', 7100.00),
    (119, 'CHK-2026-004', '2026-01-18', 5300.00),
    (121, 'CHK-2026-005', '2026-01-20', 4800.00);

-- =============================
-- Appended from populate_bi_project.sql
-- =============================

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