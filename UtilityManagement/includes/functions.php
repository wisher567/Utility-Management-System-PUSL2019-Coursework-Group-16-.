<?php
require_once __DIR__ . '/config.php';

function db_query(string $sql, array $params = [], array $options = [])
{
    global $conn;
    $stmt = sqlsrv_prepare($conn, $sql, $params, $options);
    if (!$stmt) {
        throw new Exception('Query preparation failed: ' . db_last_error());
    }
    if (!sqlsrv_execute($stmt)) {
        throw new Exception('Query execution failed: ' . db_last_error());
    }
    return $stmt;
}

function db_fetch_all($stmt): array
{
    $rows = [];
    while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
        $rows[] = $row;
    }
    return $rows;
}

function db_fetch_one($stmt): ?array
{
    $row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC);
    return $row ?: null;
}

function sanitize(?string $value): string
{
    return trim((string)$value);
}

function nullable(?string $value): ?string
{
    $value = trim((string)$value);
    return $value === '' ? null : $value;
}

function e($value): string
{
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}

function ums_current_user(): ?array
{
    return $_SESSION['user'] ?? null;
}

function get_dashboard_metrics(): array
{
    $metrics = [
        'customers' => 0,
        'pending_bills' => 0,
        'monthly_revenue' => 0,
        'active_meters' => 0,
    ];

    $metrics['customers'] = (int)db_fetch_one(
        db_query('SELECT COUNT(*) AS total FROM Customers')
    )['total'];

    $metrics['pending_bills'] = (int)db_fetch_one(
        db_query("SELECT COUNT(*) AS total FROM Bills WHERE status = 'Pending'")
    )['total'];

    $metrics['monthly_revenue'] = (float)db_fetch_one(
        db_query("SELECT ISNULL(SUM(amount),0) AS total FROM Payments WHERE MONTH(payment_date) = MONTH(GETDATE()) AND YEAR(payment_date) = YEAR(GETDATE())")
    )['total'];

    $metrics['active_meters'] = (int)db_fetch_one(
        db_query("SELECT COUNT(*) AS total FROM Meters WHERE status = 'Active'")
    )['total'];

    return $metrics;
}

function get_recent_bills(int $limit = 5): array
{
    $limit = max(1, min(20, $limit));
    $sql = "
        SELECT TOP {$limit} *
        FROM vw_CustomerUtilityBills
        ORDER BY bill_date DESC
    ";
    $stmt = db_query($sql);
    return db_fetch_all($stmt);
}

function get_customers(string $search = ''): array
{
    $params = [];
    $sql = 'SELECT * FROM Customers';
    if ($search !== '') {
        $sql .= ' WHERE first_name LIKE ? OR last_name LIKE ? OR email LIKE ?';
        $needle = '%' . $search . '%';
        $params = [$needle, $needle, $needle];
    }
    $sql .= ' ORDER BY registration_date DESC';
    return db_fetch_all(db_query($sql, $params));
}

function get_customer(int $id): ?array
{
    return db_fetch_one(db_query('SELECT * FROM Customers WHERE customer_id = ?', [$id]));
}

function save_customer(array $data): bool
{
    $values = [
        sanitize($data['first_name'] ?? ''),
        sanitize($data['last_name'] ?? ''),
        nullable($data['email'] ?? null),
        nullable($data['phone'] ?? null),
        sanitize($data['address'] ?? ''),
        sanitize($data['customer_type'] ?? 'Residential'),
        sanitize($data['status'] ?? 'Active')
    ];
    if (!empty($data['customer_id'])) {
        $values[] = (int)$data['customer_id'];
        $sql = "
            UPDATE Customers
            SET first_name = ?, last_name = ?, email = ?, phone = ?, address = ?,
                customer_type = ?, status = ?
            WHERE customer_id = ?
        ";
    } else {
        $sql = "
            INSERT INTO Customers (first_name, last_name, email, phone, address, customer_type, status)
            VALUES (?, ?, ?, ?, ?, ?, ?)
        ";
    }
    db_query($sql, $values);
    return true;
}

function delete_customer(int $id): bool
{
    db_query('DELETE FROM Customers WHERE customer_id = ?', [$id]);
    return true;
}

function get_utility_types(): array
{
    return db_fetch_all(db_query('SELECT * FROM UtilityTypes ORDER BY utility_name'));
}

function get_meters(): array
{
    $sql = "
        SELECT M.*, C.first_name, C.last_name, U.utility_name
        FROM Meters M
        INNER JOIN Customers C ON M.customer_id = C.customer_id
        INNER JOIN UtilityTypes U ON M.utility_id = U.utility_id
        ORDER BY installation_date DESC
    ";
    return db_fetch_all(db_query($sql));
}

function get_meter(int $id): ?array
{
    $sql = "
        SELECT M.*, C.first_name, C.last_name, U.utility_name
        FROM Meters M
        INNER JOIN Customers C ON M.customer_id = C.customer_id
        INNER JOIN UtilityTypes U ON M.utility_id = U.utility_id
        WHERE meter_id = ?
    ";
    return db_fetch_one(db_query($sql, [$id]));
}

function save_meter(array $data): bool
{
    $params = [
        (int)$data['customer_id'],
        (int)$data['utility_id'],
        sanitize($data['meter_number']),
        sanitize($data['location'] ?? ''),
        sanitize($data['status'] ?? 'Active')
    ];
    if (!empty($data['meter_id'])) {
        $params[] = (int)$data['meter_id'];
        $sql = "
            UPDATE Meters
            SET customer_id = ?, utility_id = ?, meter_number = ?, location = ?, status = ?
            WHERE meter_id = ?
        ";
    } else {
        $sql = "
            INSERT INTO Meters (customer_id, utility_id, meter_number, location, status)
            VALUES (?, ?, ?, ?, ?)
        ";
    }
    db_query($sql, $params);
    return true;
}

function delete_meter(int $id): bool
{
    db_query('DELETE FROM Meters WHERE meter_id = ?', [$id]);
    return true;
}

function get_meter_readings(int $meterId = null): array
{
    $sql = "
        SELECT R.*, M.meter_number, C.first_name, C.last_name
        FROM MeterReadings R
        INNER JOIN Meters M ON R.meter_id = M.meter_id
        INNER JOIN Customers C ON M.customer_id = C.customer_id
    ";
    $params = [];
    if ($meterId) {
        $sql .= ' WHERE R.meter_id = ?';
        $params[] = $meterId;
    }
    $sql .= ' ORDER BY reading_date DESC';
    return db_fetch_all(db_query($sql, $params));
}

function save_meter_reading(array $data): bool
{
    $params = [
        (int)$data['meter_id'],
        $data['reading_date'],
        (float)$data['current_reading'],
        (float)$data['previous_reading'],
        (float)$data['current_reading'] - (float)$data['previous_reading'],
        (int)$data['reader_id'],
        sanitize($data['reading_type'] ?? 'Manual')
    ];
    $sql = "
        INSERT INTO MeterReadings (meter_id, reading_date, current_reading, previous_reading, consumption, reader_id, reading_type)
        VALUES (?, ?, ?, ?, ?, ?, ?)
    ";
    db_query($sql, $params);
    return true;
}

function generate_bill(array $data): bool
{
    $sql = "
        EXEC dbo.usp_GenerateBill
            @MeterId = ?,
            @ReadingDate = ?,
            @CurrentReading = ?,
            @PreviousReading = ?,
            @ReaderId = ?,
            @ReadingType = ?,
            @DueDate = ?,
            @GeneratedBy = ?
    ";
    $params = [
        (int)$data['meter_id'],
        $data['reading_date'],
        (float)$data['current_reading'],
        (float)$data['previous_reading'],
        (int)$data['reader_id'],
        sanitize($data['reading_type'] ?? 'Manual'),
        $data['due_date'],
        (int)$data['generated_by'],
    ];
    db_query($sql, $params);
    return true;
}

function get_bills(string $statusFilter = ''): array
{
    $sql = "
        SELECT B.*, M.meter_number, C.first_name, C.last_name
        FROM Bills B
        INNER JOIN Meters M ON B.meter_id = M.meter_id
        INNER JOIN Customers C ON M.customer_id = C.customer_id
    ";
    $params = [];
    if ($statusFilter) {
        $sql .= ' WHERE B.status = ?';
        $params[] = $statusFilter;
    }
    $sql .= ' ORDER BY bill_date DESC';
    return db_fetch_all(db_query($sql, $params));
}

function record_payment(array $data): bool
{
    $params = [
        (int)$data['bill_id'],
        (float)$data['amount'],
        sanitize($data['payment_method'] ?? 'Cash'),
        nullable($data['reference_number'] ?? null),
        (int)$data['received_by'],
        sanitize($data['status'] ?? 'Completed')
    ];
    $sql = "
        INSERT INTO Payments (bill_id, amount, payment_method, reference_number, received_by, status)
        VALUES (?, ?, ?, ?, ?, ?)
    ";
    db_query($sql, $params);
    return true;
}

function get_payments(): array
{
    $sql = "
        SELECT P.*, B.bill_id, C.first_name, C.last_name
        FROM Payments P
        INNER JOIN Bills B ON P.bill_id = B.bill_id
        INNER JOIN Meters M ON B.meter_id = M.meter_id
        INNER JOIN Customers C ON M.customer_id = C.customer_id
        ORDER BY payment_date DESC
    ";
    return db_fetch_all(db_query($sql));
}

function get_revenue_report(string $range = 'monthly'): array
{
    switch ($range) {
        case 'daily':
            $groupSql = "FORMAT(payment_date, 'yyyy-MM-dd')";
            break;
        case 'yearly':
            $groupSql = "FORMAT(payment_date, 'yyyy')";
            break;
        default:
            $groupSql = "FORMAT(payment_date, 'yyyy-MM')";
    }

    $sql = "
        SELECT {$groupSql} AS bucket, SUM(amount) AS total
        FROM Payments
        GROUP BY {$groupSql}
        ORDER BY bucket DESC
    ";
    return db_fetch_all(db_query($sql));
}

function get_defaulters(): array
{
    $sql = "
        SELECT C.first_name, C.last_name, B.bill_id, B.total_amount, B.due_date
        FROM Bills B
        INNER JOIN Meters M ON B.meter_id = M.meter_id
        INNER JOIN Customers C ON M.customer_id = C.customer_id
        WHERE B.status IN ('Pending', 'Overdue') AND B.due_date < GETDATE()
    ";
    return db_fetch_all(db_query($sql));
}

function get_staff(): array
{
    return db_fetch_all(db_query('SELECT * FROM Staff ORDER BY created_at DESC'));
}

function save_staff(array $data): bool
{
    if (!empty($data['staff_id']) && empty($data['password'])) {
        $existing = db_fetch_one(db_query('SELECT password_hash FROM Staff WHERE staff_id = ?', [(int)$data['staff_id']]));
        $hash = $existing['password_hash'];
    } else {
        $hash = strtoupper(hash('sha256', $data['password'] ?? 'ChangeMe#123'));
    }
    $params = [
        sanitize($data['first_name'] ?? ''),
        sanitize($data['last_name'] ?? ''),
        sanitize($data['email'] ?? ''),
        nullable($data['phone'] ?? null),
        sanitize($data['role'] ?? 'Administrator'),
        sanitize($data['username'] ?? ''),
        $hash,
        !empty($data['is_active']) ? 1 : 0
    ];

    if (!empty($data['staff_id'])) {
        $params[] = (int)$data['staff_id'];
        $sql = "
            UPDATE Staff
            SET first_name = ?, last_name = ?, email = ?, phone = ?, role = ?,
                username = ?, password_hash = ?, is_active = ?
            WHERE staff_id = ?
        ";
    } else {
        $sql = "
            INSERT INTO Staff (first_name, last_name, email, phone, role, username, password_hash, is_active)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)
        ";
    }
    db_query($sql, $params);
    return true;
}

function get_roles(): array
{
    return ['Administrator', 'Manager', 'Billing Clerk', 'Cashier', 'Field Officer'];
}

function get_staff_by_role(string $role): array
{
    return db_fetch_all(db_query('SELECT staff_id, first_name, last_name FROM Staff WHERE role = ? AND is_active = 1', [$role]));
}

function get_consumption_analysis(): array
{
    $sql = "
        SELECT U.utility_name, SUM(B.consumption) AS total_consumption
        FROM Bills B
        INNER JOIN Meters M ON B.meter_id = M.meter_id
        INNER JOIN UtilityTypes U ON M.utility_id = U.utility_id
        GROUP BY U.utility_name
    ";
    return db_fetch_all(db_query($sql));
}

