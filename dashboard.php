<?php
require_once 'config.php';
require_login();
 
$totals = $pdo->query(
    'SELECT COUNT(*) AS products,
            COALESCE(SUM(quantity), 0) AS units,
            COALESCE(SUM(price * quantity), 0) AS value,
            COALESCE(SUM(quantity <= reorder_level), 0) AS low
     FROM products'
)->fetch();
$suppliers = (int)$pdo->query('SELECT COUNT(*) FROM suppliers')->fetchColumn();
 
$lowStock = $pdo->query(
    'SELECT p.id, p.sku, p.name, p.quantity, p.reorder_level, s.name AS supplier
     FROM products p LEFT JOIN suppliers s ON s.id = p.supplier_id
     WHERE p.quantity <= p.reorder_level
     ORDER BY p.quantity, p.name'
)->fetchAll();
 
$recent = $pdo->query(
    'SELECT m.type, m.qty, m.created_at, p.name AS product, u.name AS staff
     FROM stock_movements m
     JOIN products p ON p.id = m.product_id
     JOIN users u ON u.id = m.user_id
     ORDER BY m.id DESC LIMIT 6'
)->fetchAll();

$title = 'Dashboard';
require 'header.php';
?>
<h1 class="h3 mb-3">Dashboard</h1>

<div class="row g-3 mb-4">
  <div class="col-6 col-xl-3"><div class="card stat-card"><div class="card-body"><div class="text-muted">Products</div><div class="num"><?= (int)$totals['products'] ?></div></div></div></div>
  <div class="col-6 col-xl-3"><div class="card stat-card"><div class="card-body"><div class="text-muted">Units in stock</div><div class="num"><?= number_format($totals['units']) ?></div></div></div></div>
  <div class="col-6 col-xl-3"><div class="card stat-card"><div class="card-body"><div class="text-muted">Stock value</div><div class="num">&#8369;<?= number_format($totals['value'], 2) ?></div></div></div></div>
  <div class="col-6 col-xl-3"><div class="card stat-card"><div class="card-body"><div class="text-muted">Need restocking</div><div class="num text-danger"><?= (int)$totals['low'] ?></div></div></div></div>
</div>

<div class="row g-3">
  <div class="col-lg-7">
    <div class="card">
      <div class="card-header">Low stock alerts</div>
      <?php if (!$lowStock): ?>
        <div class="card-body text-muted">All products are above their reorder level.</div>
      <?php else: ?>
      <div class="table-responsive">
        <table class="table align-middle mb-0">
          <thead><tr><th>Product</th><th>Left</th><th>Reorder at</th><th>Supplier</th><th></th></tr></thead>
          <tbody>
          <?php foreach ($lowStock as $p): ?>
            <tr>
              <td><?= e($p['name']) ?><br><small class="text-muted"><?= e($p['sku']) ?></small></td>
              <td><?= stock_badge($p['quantity'], $p['reorder_level']) ?> <?= (int)$p['quantity'] ?></td>
              <td><?= (int)$p['reorder_level'] ?></td>
              <td><?= e($p['supplier']) ?></td>
              <td><a class="btn btn-sm btn-outline-success" href="stock.php?product=<?= $p['id'] ?>">Stock in</a></td>
            </tr>
          <?php endforeach; ?>
          </tbody>
        </table>
      </div>
      <?php endif; ?>
    </div>
  </div>

  <div class="col-lg-5">
    <div class="card">
      <div class="card-header">Latest movements</div>
      <?php if (!$recent): ?>
        <div class="card-body text-muted">No stock movements yet.</div>
      <?php else: ?>
      <ul class="list-group list-group-flush">
        <?php foreach ($recent as $m): ?>
          <li class="list-group-item d-flex justify-content-between">
            <span>
              <span class="badge bg-<?= $m['type'] === 'IN' ? 'success' : 'danger' ?>"><?= e($m['type']) ?></span>
              <?= (int)$m['qty'] ?> &times; <?= e($m['product']) ?>
            </span>
            <small class="text-muted"><?= e($m['staff']) ?></small>
          </li>
        <?php endforeach; ?>
      </ul>
      <?php endif; ?>
      <div class="card-footer text-muted small">Suppliers on file: <?= $suppliers ?></div>
    </div>
  </div>
</div>
<?php require 'footer.php'; ?>
