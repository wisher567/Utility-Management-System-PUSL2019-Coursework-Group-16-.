<?php
require_once __DIR__ . '/../includes/auth.php';
require_login();
require_role(['Administrator', 'Manager', 'Billing Clerk']);

$search = sanitize($_GET['search'] ?? '');
$editCustomer = null;

try {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        if (isset($_POST['action']) && $_POST['action'] === 'save_customer') {
            save_customer($_POST);
            set_flash('Customer saved successfully.');
            header('Location: customers.php');
            exit;
        }

        if (isset($_POST['action']) && $_POST['action'] === 'delete_customer') {
            delete_customer((int)$_POST['customer_id']);
            set_flash('Customer removed.', 'warning');
            header('Location: customers.php');
            exit;
        }
    }

    if (isset($_GET['edit'])) {
        $editCustomer = get_customer((int)$_GET['edit']);
    }

    $customers = get_customers($search);
} catch (Exception $e) {
    set_flash('Error: ' . $e->getMessage(), 'danger');
    header('Location: customers.php');
    exit;
}

require_once __DIR__ . '/../includes/header.php';
?>

<section class="card">
    <div class="card-header">
        <h2><?= $editCustomer ? 'Edit Customer' : 'Add Customer' ?></h2>
    </div>
    <form method="POST" class="grid form-grid">
        <input type="hidden" name="action" value="save_customer">
        <input type="hidden" name="customer_id" value="<?= e($editCustomer['customer_id'] ?? '') ?>">

        <label>First Name
            <input type="text" name="first_name" value="<?= e($editCustomer['first_name'] ?? '') ?>" required>
        </label>
        <label>Last Name
            <input type="text" name="last_name" value="<?= e($editCustomer['last_name'] ?? '') ?>" required>
        </label>
        <label>Email
            <input type="email" name="email" value="<?= e($editCustomer['email'] ?? '') ?>">
        </label>
        <label>Phone
            <input type="text" name="phone" value="<?= e($editCustomer['phone'] ?? '') ?>">
        </label>
        <label>Address
            <input type="text" name="address" value="<?= e($editCustomer['address'] ?? '') ?>" required>
        </label>
        <label>Customer Type
            <select name="customer_type" required>
                <?php
                $types = ['Residential', 'Commercial', 'Government'];
                $selectedType = $editCustomer['customer_type'] ?? '';
                foreach ($types as $type):
                ?>
                    <option value="<?= $type ?>" <?= $type === $selectedType ? 'selected' : '' ?>><?= $type ?></option>
                <?php endforeach; ?>
            </select>
        </label>
        <label>Status
            <select name="status">
                <?php foreach (['Active', 'Inactive'] as $status): ?>
                    <option value="<?= $status ?>" <?= ($editCustomer['status'] ?? '') === $status ? 'selected' : '' ?>><?= $status ?></option>
                <?php endforeach; ?>
            </select>
        </label>
        <div class="form-actions">
            <button class="btn btn-primary" type="submit">Save</button>
            <?php if ($editCustomer): ?>
                <a class="btn btn-secondary" href="customers.php">Cancel</a>
            <?php endif; ?>
        </div>
    </form>
</section>

<section class="card">
    <div class="card-header">
        <h2>Customers</h2>
        <form method="GET" class="inline-form">
            <input type="search" name="search" placeholder="Search..." value="<?= e($search) ?>">
            <button class="btn btn-secondary" type="submit">Search</button>
        </form>
    </div>
    <div class="table-wrapper">
        <table>
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Phone</th>
                    <th>Type</th>
                    <th>Status</th>
                    <th>Registered</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($customers as $customer): ?>
                    <tr>
                        <td><?= htmlspecialchars($customer['first_name'] . ' ' . $customer['last_name']) ?></td>
                        <td><?= htmlspecialchars($customer['email']) ?></td>
                        <td><?= htmlspecialchars($customer['phone']) ?></td>
                        <td><?= $customer['customer_type'] ?></td>
                        <td><span class="status status-<?= strtolower($customer['status']) ?>"><?= e($customer['status']) ?></span></td>
                        <td><?= $customer['registration_date']->format('Y-m-d') ?></td>
                        <td class="actions">
                            <a class="link" href="customers.php?edit=<?= $customer['customer_id'] ?>">Edit</a>
                            <form method="POST" onsubmit="return confirm('Delete this customer?');">
                                <input type="hidden" name="action" value="delete_customer">
                                <input type="hidden" name="customer_id" value="<?= $customer['customer_id'] ?>">
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

