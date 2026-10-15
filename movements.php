<?php
require_once 'config.php';
require_login();

// Filters
$q    = trim($_GET['q'] ?? '');
$type = $_GET['type'] ?? '';

$sql = 'SELECT m.*, p.name AS product, p.sku, u.name AS staff
        FROM stock_movements m
        JOIN products p ON p.id = m.product_id
        JOIN users u ON u.id = m.user_id
        WHERE (p.name LIKE ? OR p.sku LIKE ?)';
$params = ['%' . $q . '%', '%' . $q . '%'];
if (in_array($type, ['IN', 'OUT'], true)) {
    $sql .= ' AND m.type = ?';
    $params[] = $type;
}
$sql .= ' ORDER BY m.id DESC LIMIT 100';
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$rows = $stmt->fetchAll();

$title = 'Movements';
require 'header.php';
?>
<h1 class="h3 mb-3">Stock movements</h1>

<form class="row g-2 mb-3" method="get">
  <div class="col-sm-6 col-md-4">
    <input class="form-control" type="search" name="q" placeholder="Search product or SKU" value="<?= e($q) ?>">
  </div>
  <div class="col-auto">
    <select name="type" class="form-select">
      <option value="">In and Out</option>
      <option value="IN"  <?= $type === 'IN' ? 'selected' : '' ?>>Stock In</option>
      <option value="OUT" <?= $type === 'OUT' ? 'selected' : '' ?>>Stock Out</option>
    </select>
  </div>
  <div class="col-auto"><button class="btn btn-outline-success">Filter</button></div>
</form>

<?php if (!$rows): ?>
  <div class="alert alert-info">No movements found.</div>
<?php else: ?>
<div class="table-responsive">
  <table class="table align-middle bg-white">
    <thead><tr><th>Date</th><th>Product</th><th>Type</th><th>Qty</th><th>By</th><th>Note</th></tr></thead>
    <tbody>
    <?php foreach ($rows as $m): ?>
      <tr>
        <td><?= e($m['created_at']) ?></td>
        <td><?= e($m['product']) ?><br><small class="text-muted"><?= e($m['sku']) ?></small></td>
        <td><span class="badge bg-<?= $m['type'] === 'IN' ? 'success' : 'danger' ?>"><?= e($m['type']) ?></span></td>
        <td><?= (int)$m['qty'] ?></td>
        <td><?= e($m['staff']) ?></td>
        <td><?= e($m['note']) ?></td>
      </tr>
    <?php endforeach; ?>
    </tbody>
  </table>
</div>
<small class="text-muted">Showing the latest 100 movements.</small>
<?php endif; ?>
<?php require 'footer.php'; ?>
