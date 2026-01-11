<?php
require_once __DIR__ . '/../includes/auth.php';
require_login();
require_role(['Administrator', 'Manager', 'Billing Clerk', 'Field Officer']);

$meterToEdit = null;
$customersList = get_customers();
$utilities = get_utility_types();

try {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        if ($_POST['action'] === 'save_meter') {
            save_meter($_POST);
            set_flash('Meter saved successfully.');
            header('Location: meters.php');
            exit;
        }
        if ($_POST['action'] === 'delete_meter') {
            delete_meter((int)$_POST['meter_id']);
            set_flash('Meter removed.', 'warning');
            header('Location: meters.php');
            exit;
        }
    }

    if (isset($_GET['edit'])) {
        $meterToEdit = get_meter((int)$_GET['edit']);
    }

    $meters = get_meters();
} catch (Exception $e) {
    set_flash('Error: ' . $e->getMessage(), 'danger');
    header('Location: meters.php');
    exit;
}

require_once __DIR__ . '/../includes/header.php';
?>

<section class="card">
    <div class="card-header">
        <h2><?= $meterToEdit ? 'Edit Meter' : 'Register Meter' ?></h2>
    </div>
    <form method="POST" class="grid form-grid">
        <input type="hidden" name="action" value="save_meter">
        <input type="hidden" name="meter_id" value="<?= e($meterToEdit['meter_id'] ?? '') ?>">

        <label>Customer
            <select name="customer_id" required>
                <option value="">Select customer</option>
                <?php foreach ($customersList as $customer): ?>
                    <?php $selected = ($meterToEdit['customer_id'] ?? '') == $customer['customer_id'] ? 'selected' : ''; ?>
                    <option value="<?= $customer['customer_id'] ?>" <?= $selected ?>>
                        <?= htmlspecialchars($customer['first_name'] . ' ' . $customer['last_name']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </label>
        <label>Utility
            <select name="utility_id" required>
                <?php foreach ($utilities as $utility): ?>
                    <?php $selected = ($meterToEdit['utility_id'] ?? '') == $utility['utility_id'] ? 'selected' : ''; ?>
                    <option value="<?= $utility['utility_id'] ?>" <?= $selected ?>><?= $utility['utility_name'] ?> (<?= $utility['unit_measure'] ?>)</option>
                <?php endforeach; ?>
            </select>
        </label>
        <label>Meter Number
            <input type="text" name="meter_number" value="<?= e($meterToEdit['meter_number'] ?? '') ?>" required>
        </label>
        <label>Location
            <input type="text" name="location" value="<?= e($meterToEdit['location'] ?? '') ?>">
        </label>
        <label>Status
            <select name="status">
                <?php foreach (['Active', 'Inactive', 'Maintenance'] as $status): ?>
                    <option value="<?= $status ?>" <?= ($meterToEdit['status'] ?? '') === $status ? 'selected' : '' ?>><?= $status ?></option>
                <?php endforeach; ?>
            </select>
        </label>
        <div class="form-actions">
            <button class="btn btn-primary" type="submit">Save</button>
            <?php if ($meterToEdit): ?>
                <a class="btn btn-secondary" href="meters.php">Cancel</a>
            <?php endif; ?>
        </div>
    </form>
</section>

<section class="card">
    <h2>Meters</h2>
    <div class="table-wrapper">
        <table>
            <thead>
                <tr>
                    <th>Meter #</th>
                    <th>Customer</th>
                    <th>Utility</th>
                    <th>Location</th>
                    <th>Status</th>
                    <th>Installed</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($meters as $meter): ?>
                    <tr>
                        <td><?= htmlspecialchars($meter['meter_number']) ?></td>
                        <td><?= htmlspecialchars($meter['first_name'] . ' ' . $meter['last_name']) ?></td>
                        <td><?= htmlspecialchars($meter['utility_name']) ?></td>
                        <td><?= htmlspecialchars($meter['location']) ?></td>
                        <td><span class="status status-<?= strtolower($meter['status']) ?>"><?= e($meter['status']) ?></span></td>
                        <td><?= $meter['installation_date']->format('Y-m-d') ?></td>
                        <td class="actions">
                            <a class="link" href="meters.php?edit=<?= $meter['meter_id'] ?>">Edit</a>
                            <form method="POST" onsubmit="return confirm('Delete this meter?');">
                                <input type="hidden" name="action" value="delete_meter">
                                <input type="hidden" name="meter_id" value="<?= $meter['meter_id'] ?>">
                                <button class="link link-danger" type="submit">Delete</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</section>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>

