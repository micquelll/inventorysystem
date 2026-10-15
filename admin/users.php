<?php
$base = '../';
require_once '../config.php';
require_admin();
$myId = (int)$_SESSION['user']['id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $id     = (int)($_POST['id'] ?? 0);
    $action = $_POST['action'] ?? '';

    if ($id === $myId) {
        flash('danger', 'You cannot change or delete your own account here.');
    } elseif ($action === 'delete') {
        // Users with stock history cannot be deleted; change their role instead.
        try {
            $pdo->prepare('DELETE FROM users WHERE id = ?')->execute([$id]);
            flash('success', 'User deleted.');
        } catch (PDOException $ex) {
            flash('danger', 'This user has recorded stock movements and cannot be deleted.');
        }
    } elseif ($action === 'role' && in_array($_POST['role'] ?? '', ['admin', 'staff'], true)) {
        $pdo->prepare('UPDATE users SET role = ? WHERE id = ?')->execute([$_POST['role'], $id]);
        flash('success', 'Role updated.');
    }
    header('Location: users.php');
    exit;
}

$users = $pdo->query('SELECT * FROM users ORDER BY role, name')->fetchAll();

$title = 'Users';
require '../header.php';
?>
<h1 class="h3 mb-3">Users</h1>
<div class="table-responsive">
  <table class="table align-middle bg-white">
    <thead><tr><th>Name</th><th>Email</th><th>Role</th><th></th></tr></thead>
    <tbody>
    <?php foreach ($users as $u): ?>
      <tr>
        <td><?= e($u['name']) ?></td>
        <td><?= e($u['email']) ?></td>
        <td>
          <?php if ((int)$u['id'] === $myId): ?>
            <span class="badge bg-secondary"><?= e($u['role']) ?></span> <small class="text-muted">(you)</small>
          <?php else: ?>
            <form method="post" class="d-flex" style="max-width: 190px;">
              <?= csrf_field() ?>
              <input type="hidden" name="action" value="role">
              <input type="hidden" name="id" value="<?= $u['id'] ?>">
              <select name="role" class="form-select form-select-sm me-1">
                <option value="staff" <?= $u['role'] === 'staff' ? 'selected' : '' ?>>staff</option>
                <option value="admin" <?= $u['role'] === 'admin' ? 'selected' : '' ?>>admin</option>
              </select>
              <button class="btn btn-sm btn-outline-primary">Save</button>
            </form>
          <?php endif; ?>
        </td>
        <td class="text-end">
          <?php if ((int)$u['id'] !== $myId): ?>
            <form method="post" class="d-inline confirm-delete" data-message="Delete this user?">
              <?= csrf_field() ?>
              <input type="hidden" name="action" value="delete">
              <input type="hidden" name="id" value="<?= $u['id'] ?>">
              <button class="btn btn-sm btn-outline-danger">Delete</button>
            </form>
          <?php endif; ?>
        </td>
      </tr>
    <?php endforeach; ?>
    </tbody>
  </table>
</div>
<?php require '../footer.php'; ?>
