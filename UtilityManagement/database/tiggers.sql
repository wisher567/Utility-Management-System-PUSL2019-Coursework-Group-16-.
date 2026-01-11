-- Trigger: Update bill status after payment
CREATE TRIGGER trg_update_bill_status
ON Payments
AFTER INSERT
AS
BEGIN
    SET NOCOUNT ON;

    UPDATE b
    SET b.status = 'Paid'
    FROM Bills b
    INNER JOIN inserted i
        ON b.bill_id = i.bill_id;
END;

-- Trigger: Calculate consumption automatically
CREATE TRIGGER trg_calculate_consumption
ON MeterReadings
AFTER INSERT
AS
BEGIN
    SET NOCOUNT ON;

    UPDATE mr
    SET consumption = i.current_reading - i.previous_reading
    FROM MeterReadings mr
    INNER JOIN inserted i
        ON mr.reading_id = i.reading_id;
END;

-- Function: Calculate bill amount
CREATE FUNCTION fn_calculate_bill_amount
(
    @consumption DECIMAL(10,2),
    @rate DECIMAL(10,2)
)
RETURNS DECIMAL(10,2)
AS
BEGIN
    RETURN @consumption * @rate;
END;

-- Function: Calculate late fee
CREATE FUNCTION fn_calculate_late_fee
(
    @due_date DATE,
    @payment_date DATE
)
RETURNS DECIMAL(10,2)
AS
BEGIN
    RETURN CASE 
        WHEN @payment_date > @due_date THEN 50.00
        ELSE 0
    END;
END;

-- View: Unpaid bills
CREATE VIEW vw_unpaid_bills
AS
SELECT 
    c.first_name + ' ' + c.last_name AS customer_name,
    u.utility_name,
    b.bill_id,
    b.total_amount,
    b.status
FROM Bills b
JOIN Meters m ON b.meter_id = m.meter_id
JOIN Customers c ON m.customer_id = c.customer_id
JOIN UtilityTypes u ON m.utility_id = u.utility_id
WHERE b.status <> 'Paid';

-- View: Monthly revenue
CREATE VIEW vw_monthly_revenue
AS
SELECT 
    u.utility_name,
    FORMAT(p.payment_date, 'yyyy-MM') AS payment_month,
    SUM(p.amount) AS total_revenue
FROM Payments p
JOIN Bills b ON p.bill_id = b.bill_id
JOIN Meters m ON b.meter_id = m.meter_id
JOIN UtilityTypes u ON m.utility_id = u.utility_id
GROUP BY u.utility_name, FORMAT(p.payment_date, 'yyyy-MM');

-- Procedure: Generate bill
CREATE PROCEDURE sp_generate_bill
    @reading_id INT,
    @rate DECIMAL(10,2),
    @due_days INT
AS
BEGIN
    DECLARE @consumption DECIMAL(10,2);
    DECLARE @amount DECIMAL(10,2);

    SELECT @consumption = consumption
    FROM MeterReadings
    WHERE reading_id = @reading_id;

    SET @amount = dbo.fn_calculate_bill_amount(@consumption, @rate);

    INSERT INTO Bills
    (
        meter_id,
        reading_id,
        due_date,
        consumption,
        amount,
        total_amount,
        status
    )
    SELECT
        meter_id,
        reading_id,
        DATEADD(DAY, @due_days, GETDATE()),
        consumption,
        @amount,
        @amount,
        'Pending'
    FROM MeterReadings
    WHERE reading_id = @reading_id;
END;

-- Procedure: List customers with unpaid bills
CREATE PROCEDURE sp_list_defaulters
AS
BEGIN
    SELECT 
        c.first_name + ' ' + c.last_name AS customer_name,
        COUNT(b.bill_id) AS unpaid_bills,
        SUM(b.total_amount) AS total_due
    FROM Customers c
    JOIN Meters m ON c.customer_id = m.customer_id
    JOIN Bills b ON m.meter_id = b.meter_id
    WHERE b.status <> 'Paid'
    GROUP BY c.first_name, c.last_name
    ORDER BY total_due DESC;
END;
















