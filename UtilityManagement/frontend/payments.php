<?php
require_once __DIR__ . '/../includes/auth.php';
require_login();
$currentUser = ums_current_user();
require_role(['Administrator', 'Manager', 'Cashier', 'Billing Clerk']);

$pendingBills = array_filter(get_bills(), fn($bill) => in_array($bill['status'], ['Pending', 'Overdue']));
$cashiers = get_staff_by_role('Cashier');

try {
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && $_POST['action'] === 'record_payment') {
        $payload = $_POST;
        if (empty($payload['received_by'])) {
            $payload['received_by'] = $currentUser['staff_id'];
        }
        record_payment($payload);
        set_flash('Payment recorded successfully! 🎉');
        header('Location: payments.php?success=1');
        exit;
    }

    $payments = get_payments();
} catch (Exception $e) {
    set_flash('Error: ' . $e->getMessage(), 'danger');
    header('Location: payments.php');
    exit;
}

require_once __DIR__ . '/../includes/header.php';
?>

<section class="card">
    <div class="card-header">
        <h2>Record Payment</h2>
    </div>
    <form method="POST" class="grid form-grid">
        <input type="hidden" name="action" value="record_payment">
        <label>Bill
            <select name="bill_id" required>
                <option value="">Select bill</option>
                <?php foreach ($pendingBills as $bill): ?>
                    <option value="<?= $bill['bill_id'] ?>">
                        #<?= $bill['bill_id'] ?> - <?= htmlspecialchars($bill['first_name'] . ' ' . $bill['last_name']) ?> (<?= $bill['status'] ?>)
                    </option>
                <?php endforeach; ?>
            </select>
        </label>
        <label>Amount
            <input type="number" step="0.01" name="amount" required>
        </label>
        <label>Method
            <select name="payment_method">
                <?php foreach (['Cash', 'Card', 'Online', 'Bank Transfer'] as $method): ?>
                    <option value="<?= $method ?>"><?= $method ?></option>
                <?php endforeach; ?>
            </select>
        </label>
        <label>Reference #
            <input type="text" name="reference_number">
        </label>
        <label>Received By
            <select name="received_by">
                <option value="">Current user</option>
                <?php foreach ($cashiers as $cashier): ?>
                    <option value="<?= $cashier['staff_id'] ?>">
                        <?= htmlspecialchars($cashier['first_name'] . ' ' . $cashier['last_name']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </label>
        <label>Status
            <select name="status">
                <option value="Completed">Completed</option>
                <option value="Pending">Pending</option>
            </select>
        </label>
        <div class="form-actions">
            <button class="btn btn-primary" type="submit">Record Payment</button>
        </div>
    </form>
</section>

<section class="card">
    <h2>Payment History</h2>
    <div class="table-wrapper">
        <table>
            <thead>
                <tr>
                    <th>Payment #</th>
                    <th>Bill</th>
                    <th>Customer</th>
                    <th>Amount</th>
                    <th>Method</th>
                    <th>Date</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($payments as $payment): ?>
                    <tr>
                        <td><?= $payment['payment_id'] ?></td>
                        <td>#<?= $payment['bill_id'] ?></td>
                        <td><?= htmlspecialchars($payment['first_name'] . ' ' . $payment['last_name']) ?></td>
                        <td>$<?= number_format($payment['amount'], 2) ?></td>
                        <td><?= e($payment['payment_method']) ?></td>
                        <td><?= $payment['payment_date']->format('Y-m-d') ?></td>
                        <td><span class="status status-<?= strtolower($payment['status']) ?>"><?= e($payment['status']) ?></span></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</section>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>

