<?php
require_once 'config.php';
require_login();

// DELETE (admin only). The product's stock history is deleted too.
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    if (is_admin()) {
        $pdo->prepare('DELETE FROM products WHERE id = ?')->execute([(int)($_POST['id'] ?? 0)]);
        flash('success', 'Product deleted.');
    }
    header('Location: products.php');
    exit;
}

// READ with search and a "low stock only" filter
$q   = trim($_GET['q'] ?? '');
$low = isset($_GET['low']);

$sql = 'SELECT p.*, s.name AS supplier
        FROM products p LEFT JOIN suppliers s ON s.id = p.supplier_id
        WHERE (p.name LIKE ? OR p.sku LIKE ? OR p.category LIKE ?)';
if ($low) $sql .= ' AND p.quantity <= p.reorder_level';
$sql .= ' ORDER BY p.name';
$stmt = $pdo->prepare($sql);
$stmt->execute(['%' . $q . '%', '%' . $q . '%', '%' . $q . '%']);
$products = $stmt->fetchAll();

$title = 'Products';
require 'header.php';
?>
<div class="d-flex flex-wrap justify-content-between align-items-center mb-3">
  <h1 class="h3 mb-2">Products</h1>
  <?php if (is_admin()): ?>
    <a href="admin/product_form.php" class="btn btn-success">Add product</a>
  <?php endif; ?>
</div>

<form class="row g-2 mb-3" method="get">
  <div class="col-sm-6 col-md-5">
    <input class="form-control" type="search" name="q" placeholder="Search name, SKU, or category" value="<?= e($q) ?>">
  </div>
  <div class="col-auto d-flex align-items-center">
    <div class="form-check">
      <input class="form-check-input" type="checkbox" name="low" id="low" value="1" <?= $low ? 'checked' : '' ?>>
      <label class="form-check-label" for="low">Low stock only</label>
    </div>
  </div>
  <div class="col-auto"><button class="btn btn-outline-success">Search</button></div>
</form>

<?php if (!$products): ?>
  <div class="alert alert-info">No products found.</div>
<?php else: ?>
<div class="table-responsive">
  <table class="table align-middle bg-white">
    <thead>
      <tr><th>Product</th><th>Category</th><th>Supplier</th><th>Price</th><th>Qty</th><th>Status</th><th>Quick stock</th><?php if (is_admin()): ?><th></th><?php endif; ?></tr>
    </thead>
    <tbody>
    <?php foreach ($products as $p): ?>
      <tr>
        <td><strong><?= e($p['name']) ?></strong><br><small class="text-muted"><?= e($p['sku']) ?></small></td>
        <td><?= e($p['category']) ?></td>
        <td><?= e($p['supplier']) ?></td>
        <td>&#8369;<?= number_format($p['price'], 2) ?></td>
        <td class="qty-cell fw-semibold"><?= (int)$p['quantity'] ?></td>
        <td class="status-cell"><?= stock_badge($p['quantity'], $p['reorder_level']) ?></td>
        <td>
          <form method="post" action="stock.php" class="stock-form d-flex">
            <?= csrf_field() ?>
            <input type="hidden" name="product_id" value="<?= $p['id'] ?>">
            <input type="hidden" name="back" value="products.php">
            <input type="number" name="qty" class="form-control form-control-sm" style="width: 75px;" min="1" value="1" required>
            <button name="type" value="IN" class="btn btn-sm btn-success ms-1">In</button>
            <button name="type" value="OUT" class="btn btn-sm btn-outline-danger ms-1">Out</button>
          </form>
        </td>
        <?php if (is_admin()): ?>
        <td class="text-end">
          <a class="btn btn-sm btn-outline-primary" href="admin/product_form.php?id=<?= $p['id'] ?>">Edit</a>
          <form method="post" class="d-inline confirm-delete" data-message="Delete this product and its stock history?">
            <?= csrf_field() ?>
            <input type="hidden" name="id" value="<?= $p['id'] ?>">
            <button class="btn btn-sm btn-outline-danger">Delete</button>
          </form>
        </td>
        <?php endif; ?>
      </tr>
    <?php endforeach; ?>
    </tbody>
  </table>
</div>
<?php endif; ?>
<?php require 'footer.php'; ?>
