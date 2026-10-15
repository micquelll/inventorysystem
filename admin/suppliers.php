<?php
$base = '../';
require_once '../config.php';
require_admin();

// DELETE (the supplier's products stay, with no supplier)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $pdo->prepare('DELETE FROM suppliers WHERE id = ?')->execute([(int)($_POST['id'] ?? 0)]);
    flash('success', 'Supplier deleted.');
    header('Location: suppliers.php');
    exit;
}

// READ
$suppliers = $pdo->query(
    'SELECT s.*, (SELECT COUNT(*) FROM products p WHERE p.supplier_id = s.id) AS product_count
     FROM suppliers s ORDER BY s.name'
)->fetchAll();

$title = 'Suppliers';
require '../header.php';
?>
<div class="d-flex justify-content-between align-items-center mb-3">
  <h1 class="h3">Suppliers</h1>
  <a href="supplier_form.php" class="btn btn-success">Add supplier</a>
</div>
<div class="table-responsive">
  <table class="table align-middle bg-white">
    <thead><tr><th>Name</th><th>Contact person</th><th>Phone</th><th>Email</th><th>Products</th><th></th></tr></thead>
    <tbody>
    <?php foreach ($suppliers as $s): ?>
      <tr>
        <td><?= e($s['name']) ?></td>
        <td><?= e($s['contact_person']) ?></td>
        <td><?= e($s['phone']) ?></td>
        <td><?= e($s['email']) ?></td>
        <td><?= (int)$s['product_count'] ?></td>
        <td class="text-end">
          <a class="btn btn-sm btn-outline-primary" href="supplier_form.php?id=<?= $s['id'] ?>">Edit</a>
          <form method="post" class="d-inline confirm-delete" data-message="Delete this supplier? Its products will have no supplier.">
            <?= csrf_field() ?>
            <input type="hidden" name="id" value="<?= $s['id'] ?>">
            <button class="btn btn-sm btn-outline-danger">Delete</button>
          </form>
        </td>
      </tr>
    <?php endforeach; ?>
    </tbody>
  </table>
</div>
<?php require '../footer.php'; ?>
