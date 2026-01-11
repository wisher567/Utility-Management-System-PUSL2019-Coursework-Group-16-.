<?php
require_once __DIR__ . '/../includes/auth.php';
require_login();
require_role(['Administrator', 'Manager']);

$roles = get_roles();
$editStaff = null;

try {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        if ($_POST['action'] === 'save_staff') {
            save_staff($_POST);
            set_flash('Staff saved.');
            header('Location: staff.php');
            exit;
        }
    }

    if (isset($_GET['edit'])) {
        $editStaff = db_fetch_one(db_query('SELECT * FROM Staff WHERE staff_id = ?', [(int)$_GET['edit']]));
    }

    $staffList = get_staff();
} catch (Exception $e) {
    set_flash('Error: ' . $e->getMessage(), 'danger');
    header('Location: staff.php');
    exit;
}

require_once __DIR__ . '/../includes/header.php';
?>

<section class="card">
    <div class="card-header">
        <h2><?= $editStaff ? 'Edit Staff' : 'Add Staff' ?></h2>
    </div>
    <form method="POST" class="grid form-grid">
        <input type="hidden" name="action" value="save_staff">
        <input type="hidden" name="staff_id" value="<?= e($editStaff['staff_id'] ?? '') ?>">
        <label>First Name
            <input type="text" name="first_name" value="<?= e($editStaff['first_name'] ?? '') ?>" required>
        </label>
        <label>Last Name
            <input type="text" name="last_name" value="<?= e($editStaff['last_name'] ?? '') ?>" required>
        </label>
        <label>Email
            <input type="email" name="email" value="<?= e($editStaff['email'] ?? '') ?>" required>
        </label>
        <label>Phone
            <input type="text" name="phone" value="<?= e($editStaff['phone'] ?? '') ?>">
        </label>
        <label>Role
            <select name="role" required>
                <?php foreach ($roles as $role): ?>
                    <option value="<?= $role ?>" <?= ($editStaff['role'] ?? '') === $role ? 'selected' : '' ?>><?= $role ?></option>
                <?php endforeach; ?>
            </select>
        </label>
        <label>Username
            <input type="text" name="username" value="<?= e($editStaff['username'] ?? '') ?>" required>
        </label>
        <label>Password <?= $editStaff ? '(leave blank to keep)' : '' ?>
            <input type="password" name="password" <?= $editStaff ? '' : 'required' ?>>
        </label>
        <label>Status
            <select name="is_active">
                <option value="1" <?= ($editStaff['is_active'] ?? 1) ? 'selected' : '' ?>>Active</option>
                <option value="0" <?= isset($editStaff) && !$editStaff['is_active'] ? 'selected' : '' ?>>Inactive</option>
            </select>
        </label>
        <div class="form-actions">
            <button class="btn btn-primary" type="submit">Save</button>
            <?php if ($editStaff): ?>
                <a class="btn btn-secondary" href="staff.php">Cancel</a>
            <?php endif; ?>
        </div>
    </form>
</section>

<section class="card">
    <h2>Staff Directory</h2>
    <div class="table-wrapper">
        <table>
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Role</th>
                    <th>Email</th>
                    <th>Phone</th>
                    <th>Status</th>
                    <th>Created</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($staffList as $staff): ?>
                    <tr>
                        <td><?= htmlspecialchars($staff['first_name'] . ' ' . $staff['last_name']) ?></td>
                        <td><?= e($staff['role']) ?></td>
                        <td><?= htmlspecialchars($staff['email']) ?></td>
                        <td><?= htmlspecialchars($staff['phone'] ?? '') ?></td>
                        <td><span class="status status-<?= $staff['is_active'] ? 'active' : 'inactive' ?>"><?= $staff['is_active'] ? 'Active' : 'Inactive' ?></span></td>
                        <td><?= $staff['created_at']->format('Y-m-d') ?></td>
                        <td>
                            <a class="link" href="staff.php?edit=<?= $staff['staff_id'] ?>">Edit</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</section>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>

