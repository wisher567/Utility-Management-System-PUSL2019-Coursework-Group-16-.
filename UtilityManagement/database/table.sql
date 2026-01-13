-- Customers Table
CREATE TABLE Customers (
    customer_id INT PRIMARY KEY IDENTITY(1,1),
    first_name NVARCHAR(50) NOT NULL,
    last_name NVARCHAR(50) NOT NULL,
    email NVARCHAR(100) UNIQUE,
    phone NVARCHAR(20),
    address NVARCHAR(255) NOT NULL,
    customer_type NVARCHAR(20) CHECK (customer_type IN ('Residential', 'Commercial', 'Government')),
    registration_date DATE DEFAULT GETDATE(),
    status NVARCHAR(10) DEFAULT 'Active'
);

-- UtilityTypes Table
CREATE TABLE UtilityTypes (
    utility_id INT PRIMARY KEY IDENTITY(1,1),
    utility_name NVARCHAR(50) NOT NULL,
    unit_measure NVARCHAR(20) NOT NULL,
    description NVARCHAR(255)
);

-- Tariffs Table
CREATE TABLE Tariffs (
    tariff_id INT PRIMARY KEY IDENTITY(1,1),
    utility_id INT FOREIGN KEY REFERENCES UtilityTypes(utility_id),
    tariff_name NVARCHAR(50) NOT NULL,
    base_rate DECIMAL(10,2) NOT NULL,
    slab1_limit DECIMAL(10,2),
    slab1_rate DECIMAL(10,2),
    slab2_limit DECIMAL(10,2),
    slab2_rate DECIMAL(10,2),
    slab3_rate DECIMAL(10,2),
    effective_date DATE DEFAULT GETDATE(),
    is_active BIT DEFAULT 1
);

-- Meters Table
CREATE TABLE Meters (
    meter_id INT PRIMARY KEY IDENTITY(1,1),
    customer_id INT FOREIGN KEY REFERENCES Customers(customer_id),
    utility_id INT FOREIGN KEY REFERENCES UtilityTypes(utility_id),
    meter_number NVARCHAR(50) UNIQUE NOT NULL,
    installation_date DATE DEFAULT GETDATE(),
    location NVARCHAR(100),
    status NVARCHAR(15) DEFAULT 'Active'
);

-- MeterReadings Table
CREATE TABLE MeterReadings (
    reading_id INT PRIMARY KEY IDENTITY(1,1),
    meter_id INT FOREIGN KEY REFERENCES Meters(meter_id),
    reading_date DATE NOT NULL,
    current_reading DECIMAL(10,2) NOT NULL,
    previous_reading DECIMAL(10,2),
    consumption DECIMAL(10,2),
    reader_id INT,
    reading_type NVARCHAR(20) DEFAULT 'Manual'
);

-- Bills Table
CREATE TABLE Bills (
    bill_id INT PRIMARY KEY IDENTITY(1,1),
    meter_id INT FOREIGN KEY REFERENCES Meters(meter_id),
    reading_id INT FOREIGN KEY REFERENCES MeterReadings(reading_id),
    bill_date DATE DEFAULT GETDATE(),
    due_date DATE,
    consumption DECIMAL(10,2) NOT NULL,
    amount DECIMAL(10,2) NOT NULL,
    late_fee DECIMAL(10,2) DEFAULT 0,
    total_amount DECIMAL(10,2) NOT NULL,
    status NVARCHAR(20) DEFAULT 'Pending',
    generated_by INT
);

-- Payments Table
CREATE TABLE Payments (
    payment_id INT PRIMARY KEY IDENTITY(1,1),
    bill_id INT FOREIGN KEY REFERENCES Bills(bill_id),
    payment_date DATETIME DEFAULT GETDATE(),
    amount DECIMAL(10,2) NOT NULL,
    payment_method NVARCHAR(20) CHECK (payment_method IN ('Cash', 'Card', 'Online', 'Bank Transfer')),
    reference_number NVARCHAR(100),
    received_by INT,
    status NVARCHAR(20) DEFAULT 'Completed'
);

-- Staff Table
CREATE TABLE Staff (
    staff_id INT PRIMARY KEY IDENTITY(1,1),
    first_name NVARCHAR(50) NOT NULL,
    last_name NVARCHAR(50) NOT NULL,
    email NVARCHAR(100) UNIQUE NOT NULL,
    phone NVARCHAR(20),
    role NVARCHAR(30) NOT NULL CHECK (role IN ('Administrator', 'Manager', 'Billing Clerk', 'Cashier', 'Field Officer')),
    username NVARCHAR(50) UNIQUE NOT NULL,
    password_hash NVARCHAR(255) NOT NULL,
    is_active BIT DEFAULT 1,
    created_at DATETIME DEFAULT GETDATE(),
    last_login DATETIME NULL

);

