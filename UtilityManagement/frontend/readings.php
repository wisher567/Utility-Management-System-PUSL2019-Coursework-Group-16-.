<?php
require_once __DIR__ . '/../includes/auth.php';
require_login();
require_role(['Administrator', 'Manager', 'Field Officer', 'Billing Clerk']);

$meters = get_meters();
$fieldOfficers = get_staff_by_role('Field Officer');

try {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        if ($_POST['action'] === 'save_reading') {
            save_meter_reading($_POST);
            set_flash('Reading captured.');
            header('Location: readings.php');
            exit;
        }
    }

    $readings = get_meter_readings();
} catch (Exception $e) {
    set_flash('Error: ' . $e->getMessage(), 'danger');
    header('Location: readings.php');
    exit;
}

require_once __DIR__ . '/../includes/header.php';
?>

<section class="card">
    <div class="card-header">
        <h2>Log Meter Reading</h2>
    </div>
    <form method="POST" class="grid form-grid">
        <input type="hidden" name="action" value="save_reading">
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
                <option value="">Assign officer</option>
                <?php foreach ($fieldOfficers as $officer): ?>
                    <option value="<?= $officer['staff_id'] ?>">
                        <?= htmlspecialchars($officer['first_name'] . ' ' . $officer['last_name']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </label>
        <label>Reading Type
            <select name="reading_type">
                <option value="Manual">Manual</option>
                <option value="Smart">Smart</option>
            </select>
        </label>
        <div class="form-actions">
            <button class="btn btn-primary" type="submit">Save Reading</button>
        </div>
    </form>
</section>

<section class="card">
    <h2>Reading History</h2>
    <div class="table-wrapper">
        <table>
            <thead>
                <tr>
                    <th>Meter</th>
                    <th>Customer</th>
                    <th>Date</th>
                    <th>Prev</th>
                    <th>Current</th>
                    <th>Consumption</th>
                    <th>Type</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($readings as $reading): ?>
                    <tr>
                        <td><?= htmlspecialchars($reading['meter_number']) ?></td>
                        <td><?= htmlspecialchars($reading['first_name'] . ' ' . $reading['last_name']) ?></td>
                        <td><?= $reading['reading_date']->format('Y-m-d') ?></td>
                        <td><?= $reading['previous_reading'] ?></td>
                        <td><?= $reading['current_reading'] ?></td>
                        <td><?= $reading['consumption'] ?></td>
                        <td><?= e($reading['reading_type']) ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</section>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>

