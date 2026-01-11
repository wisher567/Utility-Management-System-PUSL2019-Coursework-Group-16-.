<?php
require_once __DIR__ . '/../includes/auth.php';
require_login();
$currentUser = ums_current_user();
require_role(['Administrator', 'Manager', 'Billing Clerk']);

$meters = get_meters();
$fieldOfficers = get_staff_by_role('Field Officer');
$statusFilter = sanitize($_GET['status'] ?? '');

try {
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && $_POST['action'] === 'generate_bill') {
        $payload = $_POST;
        $payload['generated_by'] = $currentUser['staff_id'];
        generate_bill($payload);
        set_flash('Bill generated successfully.');
        header('Location: billing.php');
        exit;
    }

    $bills = get_bills($statusFilter);
} catch (Exception $e) {
    set_flash('Error: ' . $e->getMessage(), 'danger');
    header('Location: billing.php');
    exit;
}

require_once __DIR__ . '/../includes/header.php';
?>

<section class="card">
    <div class="card-header">
        <h2>Generate Bill</h2>
    </div>
    <form method="POST" class="grid form-grid">
        <input type="hidden" name="action" value="generate_bill">
        <label>Meter
            <select name="meter_id" required>
                <option value="">Select meter</option>
                <?php foreach ($meters as $meter): ?>
                    <option value="<?= $meter['meter_id'] ?>">
                        <?= htmlspecialchars($meter['meter_number'] . ' - ' . $meter['first_name'] . ' ' . $meter['last_name']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </label>
        <label>Reading Date
            <input type="date" name="reading_date" value="<?= date('Y-m-d') ?>" required>
        </label>
        <label>Previous Reading
            <input type="number" step="0.01" name="previous_reading" required>
        </label>
        <label>Current Reading
            <input type="number" step="0.01" name="current_reading" required>
        </label>
        <label>Reader
            <select name="reader_id" required>
                <?php foreach ($fieldOfficers as $officer): ?>
                    <option value="<?= $officer['staff_id'] ?>"><?= htmlspecialchars($officer['first_name'] . ' ' . $officer['last_name']) ?></option>
                <?php endforeach; ?>
            </select>
        </label>
        <label>Reading Type
            <select name="reading_type">
                <option value="Manual">Manual</option>
                <option value="Smart">Smart</option>
            </select>
        </label>
        <label>Due Date
            <input type="date" name="due_date" value="<?= date('Y-m-d', strtotime('+15 days')) ?>" required>
        </label>
        <div class="form-actions">
            <button class="btn btn-primary" type="submit">Generate Bill</button>
        </div>
    </form>
</section>

<section class="card">
    <div class="card-header">
        <h2>Bills</h2>
        <div style="display: flex; gap: 0.75rem; align-items: center; flex-wrap: wrap;">
            <form method="GET" class="inline-form">
                <select name="status">
                    <option value="">All</option>
                    <?php foreach (['Pending', 'Paid', 'Overdue'] as $status): ?>
                        <option value="<?= $status ?>" <?= $status === $statusFilter ? 'selected' : '' ?>><?= $status ?></option>
                    <?php endforeach; ?>
                </select>
                <button class="btn btn-secondary" type="submit">Filter</button>
            </form>
            <button class="export-btn export-btn-pdf" onclick="showToast('Export', 'Generating PDF...', 'info')">📄 PDF</button>
            <button class="export-btn export-btn-excel" onclick="showToast('Export', 'Generating Excel...', 'info')">📊 Excel</button>
        </div>
    </div>
    <div class="table-wrapper">
        <table>
            <thead>
                <tr>
                    <th>Bill #</th>
                    <th>Meter</th>
                    <th>Customer</th>
                    <th>Consumption</th>
                    <th>Amount</th>
                    <th>Total</th>
                    <th>Status</th>
                    <th>Due</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($bills as $bill): ?>
                    <tr>
                        <td><?= $bill['bill_id'] ?></td>
                        <td><?= htmlspecialchars($bill['meter_number']) ?></td>
                        <td><?= htmlspecialchars($bill['first_name'] . ' ' . $bill['last_name']) ?></td>
                        <td><?= $bill['consumption'] ?></td>
                        <td>$<?= number_format($bill['amount'], 2) ?></td>
                        <td>$<?= number_format($bill['total_amount'], 2) ?></td>
                        <td><span class="status status-<?= strtolower($bill['status']) ?>"><?= e($bill['status']) ?></span></td>
                        <td><?= $bill['due_date']->format('Y-m-d') ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</section>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>

