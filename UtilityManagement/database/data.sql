INSERT INTO UtilityTypes (utility_name, unit_measure, description)
VALUES
('Electricity', 'kWh', 'Electric power consumption'),
('Water', 'm³', 'Potable water supply'),
('Gas', 'Therm', 'Natural gas service');
GO

INSERT INTO Staff (first_name, last_name, email, phone, role, username, password_hash, is_active)
VALUES
('Alice', 'Morgan', 'alice.morgan@ums.com', '555-1001', 'Administrator', 'alice', CONVERT(NVARCHAR(255), HASHBYTES('SHA2_256','Admin#123!'), 2), 1),
('Brian', 'Lee', 'brian.lee@ums.com', '555-1002', 'Manager', 'brian', CONVERT(NVARCHAR(255), HASHBYTES('SHA2_256','Manager#123!'), 2), 1),
('Catherine', 'Owens', 'cathy.owens@ums.com', '555-1003', 'Billing Clerk', 'cathy', CONVERT(NVARCHAR(255), HASHBYTES('SHA2_256','Billing#123!'), 2), 1),
('David', 'Nguyen', 'david.nguyen@ums.com', '555-1004', 'Cashier', 'david', CONVERT(NVARCHAR(255), HASHBYTES('SHA2_256','Cashier#123!'), 2), 1),
('Elena', 'Rossi', 'elena.rossi@ums.com', '555-1005', 'Field Officer', 'elena', CONVERT(NVARCHAR(255), HASHBYTES('SHA2_256','Field#123!'), 2), 1),
('Faisal', 'Khan', 'faisal.khan@ums.com', '555-1006', 'Billing Clerk', 'faisal', CONVERT(NVARCHAR(255), HASHBYTES('SHA2_256','Billing#456!'), 2), 1),
('Grace', 'Parker', 'grace.parker@ums.com', '555-1007', 'Cashier', 'grace', CONVERT(NVARCHAR(255), HASHBYTES('SHA2_256','Cashier#456!'), 2), 1),
('Hector', 'Ruiz', 'hector.ruiz@ums.com', '555-1008', 'Field Officer', 'hector', CONVERT(NVARCHAR(255), HASHBYTES('SHA2_256','Field#456!'), 2), 1);
('Isabella', 'Chen', 'isabella.chen@ums.com', '555-1009', 'Administrator', 'isabella', CONVERT(NVARCHAR(255), HASHBYTES('SHA2_256','Admin#456!'), 2), 1),
('Jacob', 'Wilson', 'jacob.wilson@ums.com', '555-1010', 'Cashier', 'jacob', CONVERT(NVARCHAR(255), HASHBYTES('SHA2_256','Cashier#789!'), 2), 1);
GO

INSERT INTO Customers (first_name, last_name, email, phone, address, customer_type, registration_date, status)
VALUES
('John', 'Doe', 'john.doe@example.com', '555-2001', '12 Greenway Ave', 'Residential', '2023-01-15', 'Active'),
('Mary', 'Smith', 'mary.smith@example.com', '555-2002', '45 Lakeview Rd', 'Residential', '2023-02-10', 'Active'),
('Carlos', 'Diaz', 'carlos.diaz@example.com', '555-2003', '78 Sunset Blvd', 'Commercial', '2023-03-05', 'Active'),
('Linda', 'Brown', 'linda.brown@example.com', '555-2004', '90 Pine St', 'Government', '2022-12-01', 'Active'),
('Ravi', 'Patel', 'ravi.patel@example.com', '555-2005', '15 Market St', 'Commercial', '2023-04-22', 'Active'),
('Aisha', 'Rahman', 'aisha.rahman@example.com', '555-2006', '88 River Rd', 'Residential', '2023-05-18', 'Inactive'),
('George', 'King', 'george.king@example.com', '555-2007', '31 Harbor Ln', 'Residential', '2023-06-12', 'Active'),
('Sofia', 'Lopez', 'sofia.lopez@example.com', '555-2008', '22 Maple Ct', 'Government', '2023-07-03', 'Active'),
('Ethan', 'Clark', 'ethan.clark@example.com', '555-2009', '101 Bay Dr', 'Commercial', '2023-08-19', 'Active'),
('Nina', 'Ibrahim', 'nina.ibrahim@example.com', '555-2010', '76 Elm St', 'Residential', '2023-09-25', 'Active'),
('Patrick', 'Young', 'patrick.young@example.com', '555-2011', '210 Cedar Ave', 'Commercial', '2023-10-15', 'Active'),
('Uma', 'Singh', 'uma.singh@example.com', '555-2012', '64 Sunrise Pl', 'Government', '2023-11-01', 'Active');
GO

INSERT INTO Tariffs (utility_id, tariff_name, base_rate, slab1_limit, slab1_rate, slab2_limit, slab2_rate, slab3_rate, effective_date, is_active)
VALUES
(1, 'Domestic Saver', 5.00, 100, 4.50, 300, 6.00, 7.50, '2023-01-01', 1),
(1, 'Commercial Peak', 6.00, 200, 5.50, 500, 7.00, 8.25, '2023-01-01', 1),
(1, 'Government Flat', 5.50, NULL, NULL, NULL, NULL, 5.50, '2023-01-01', 1),
(2, 'Residential Water', 2.50, 30, 2.00, 60, 3.00, 4.50, '2023-01-01', 1),
(2, 'Commercial Water', 3.50, 50, 3.00, 100, 4.25, 5.50, '2023-01-01', 1),
(3, 'Gas Standard', 4.75, 50, 4.25, 150, 5.25, 6.25, '2023-01-01', 1),
(3, 'Gas Industrial', 4.00, 200, 3.50, 500, 4.50, 5.75, '2023-01-01', 1);
(1, 'Industrial Power', 7.50, 1000, 6.50, 5000, 7.25, 8.50, '2024-01-01', 1),
(2, 'Industrial Water', 4.50, 200, 3.75, 500, 4.50, 6.25, '2024-01-01', 1),
(3, 'Large Commercial Gas', 3.75, 500, 3.25, 1000, 4.00, 5.25, '2024-01-01', 1);
GO

INSERT INTO Meters (customer_id, utility_id, meter_number, installation_date, location, status)
VALUES
(1, 1, 'ELEC-1001', '2023-01-20', 'Basement', 'Active'),
(2, 1, 'ELEC-1002', '2023-02-15', 'Garage', 'Active'),
(3, 1, 'ELEC-1003', '2023-02-28', 'Main Panel', 'Active'),
(4, 2, 'WATR-2001', '2022-12-05', 'Pump House', 'Active'),
(5, 1, 'ELEC-1004', '2023-04-25', 'Warehouse', 'Active'),
(6, 2, 'WATR-2002', '2023-05-20', 'Garden', 'Inactive'),
(7, 3, 'GAS-3001', '2023-06-15', 'Kitchen', 'Active'),
(8, 2, 'WATR-2003', '2023-07-10', 'Utility Room', 'Active'),
(9, 1, 'ELEC-1005', '2023-08-25', 'Main Floor', 'Active'),
(10, 3, 'GAS-3002', '2023-09-30', 'Roof', 'Active'),
(11, 1, 'ELEC-1006', '2023-10-20', 'Server Room', 'Active'),
(12, 2, 'WATR-2004', '2023-11-12', 'Mechanical', 'Active');
GO

INSERT INTO MeterReadings (meter_id, reading_date, current_reading, previous_reading, consumption, reader_id, reading_type)
VALUES
(1, '2023-10-01', 820.5, 750.0, 70.5, 5, 'Manual'),
(2, '2023-10-02', 650.0, 600.0, 50.0, 5, 'Manual'),
(3, '2023-10-03', 1200.0, 1100.0, 100.0, 8, 'Manual'),
(4, '2023-10-04', 310.0, 280.0, 30.0, 8, 'Manual'),
(5, '2023-10-05', 2000.0, 1830.0, 170.0, 5, 'Manual'),
(6, '2023-10-06', 140.0, 120.0, 20.0, 8, 'Manual'),
(7, '2023-10-07', 300.0, 250.0, 50.0, 8, 'Manual'),
(8, '2023-10-08', 500.0, 430.0, 70.0, 5, 'Manual'),
(9, '2023-10-09', 950.0, 900.0, 50.0, 8, 'Manual'),
(10, '2023-10-10', 410.0, 360.0, 50.0, 5, 'Manual'),
(11, '2023-10-11', 1500.0, 1380.0, 120.0, 5, 'Manual'),
(12, '2023-10-12', 380.0, 330.0, 50.0, 8, 'Manual');
GO

INSERT INTO Bills (meter_id, reading_id, bill_date, due_date, consumption, amount, late_fee, total_amount, status, generated_by)
VALUES
(1, 1, '2023-10-05', '2023-10-25', 70.5, 360.75, 0, 360.75, 'Pending', 3),
(2, 2, '2023-10-06', '2023-10-26', 50.0, 275.00, 0, 275.00, 'Pending', 3),
(3, 3, '2023-10-07', '2023-10-27', 100.0, 550.00, 0, 550.00, 'Pending', 3),
(4, 4, '2023-10-08', '2023-10-28', 30.0, 75.00, 0, 75.00, 'Pending', 3),
(5, 5, '2023-10-09', '2023-10-29', 170.0, 935.00, 0, 935.00, 'Pending', 3),
(6, 6, '2023-10-10', '2023-10-30', 20.0, 50.00, 0, 50.00, 'Pending', 3),
(7, 7, '2023-10-11', '2023-10-31', 50.0, 262.50, 0, 262.50, 'Pending', 3),
(8, 8, '2023-10-12', '2023-11-01', 70.0, 175.00, 0, 175.00, 'Pending', 3),
(9, 9, '2023-10-13', '2023-11-02', 50.0, 275.00, 0, 275.00, 'Pending', 3),
(10, 10, '2023-10-14', '2023-11-03', 50.0, 237.50, 0, 237.50, 'Pending', 3),
(11, 11, '2023-10-15', '2023-11-04', 120.0, 660.00, 0, 660.00, 'Pending', 3),
(12, 12, '2023-10-16', '2023-11-05', 50.0, 125.00, 0, 125.00, 'Pending', 3);
GO

INSERT INTO Payments (bill_id, payment_date, amount, payment_method, reference_number, received_by, status)
VALUES
(1, '2023-10-20', 360.75, 'Online', 'PAY-1001', 4, 'Completed'),
(2, '2023-10-22', 275.00, 'Cash', 'PAY-1002', 4, 'Completed'),
(4, '2023-10-23', 75.00, 'Card', 'PAY-1003', 4, 'Completed'),
(5, '2023-10-24', 935.00, 'Bank Transfer', 'PAY-1004', 4, 'Completed'),
(7, '2023-10-25', 262.50, 'Cash', 'PAY-1005', 4, 'Completed'),
(8, '2023-10-26', 175.00, 'Online', 'PAY-1006', 4, 'Completed'),
(10, '2023-10-27', 237.50, 'Card', 'PAY-1007', 4, 'Completed');
(3, '2023-10-28', 180.25, 'Online', 'PAY-1008', 4, 'Completed'),
(6, '2023-10-29', 450.00, 'Bank Transfer', 'PAY-1009', 4, 'Completed'),
(9, '2023-10-30', 315.75, 'Card', 'PAY-1010', 4, 'Completed');
GO

