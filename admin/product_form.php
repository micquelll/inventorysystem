<?php
$base = '../';
require_once '../config.php';
require_admin();

$suppliers = $pdo->query('SELECT id, name FROM suppliers ORDER BY name')->fetchAll();

// If an id is given we are editing, otherwise creating.
$id = (int)($_GET['id'] ?? 0);
$p = ['sku' => '', 'name' => '', 'category' => '', 'supplier_id' => '', 'price' => '', 'quantity' => '0', 'reorder_level' => '10'];

if ($id) {
    $stmt = $pdo->prepare('SELECT * FROM products WHERE id = ?');
    $stmt->execute([$id]);
    $p = $stmt->fetch();
    if (!$p) { header('Location: ../products.php'); exit; }
}

$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $p = [
        'sku'           => strtoupper(trim($_POST['sku'] ?? '')),
        'name'          => trim($_POST['name'] ?? ''),
        'category'      => trim($_POST['category'] ?? ''),
        'supplier_id'   => (int)($_POST['supplier_id'] ?? 0),
        'price'         => $_POST['price'] ?? '',
        'quantity'      => $_POST['quantity'] ?? '0',
        'reorder_level' => $_POST['reorder_level'] ?? '',
    ];

    // Server-side validation
    if ($p['sku'] === '')                                   $errors[] = 'SKU is required.';
    if ($p['name'] === '')                                  $errors[] = 'Name is required.';
    if (!is_numeric($p['price']) || $p['price'] < 0)        $errors[] = 'Price must be 0 or more.';
    $reorder = filter_var($p['reorder_level'], FILTER_VALIDATE_INT);
    if ($reorder === false || $reorder < 0)                 $errors[] = 'Reorder level must be a whole number, 0 or more.';
    $quantity = 0;
    if (!$id) {
        $quantity = filter_var($p['quantity'], FILTER_VALIDATE_INT);
        if ($quantity === false || $quantity < 0)           $errors[] = 'Starting quantity must be a whole number, 0 or more.';
    }
    if ($p['supplier_id']) {
        $found = $pdo->prepare('SELECT id FROM suppliers WHERE id = ?');
        $found->execute([$p['supplier_id']]);
        if (!$found->fetch())                               $errors[] = 'Choose a valid supplier.';
    }
    if (!$errors) {
        $check = $pdo->prepare('SELECT id FROM products WHERE sku = ? AND id <> ?');
        $check->execute([$p['sku'], $id]);
        if ($check->fetch())                                $errors[] = 'That SKU already exists.';
    }

    if (!$errors) {
        $supplier = $p['supplier_id'] ?: null;
        if ($id) {   // UPDATE (quantity is changed only through Stock In / Out)
            $pdo->prepare('UPDATE products SET sku=?, name=?, category=?, supplier_id=?, price=?, reorder_level=? WHERE id=?')
                ->execute([$p['sku'], $p['name'], $p['category'], $supplier, $p['price'], $reorder, $id]);
        } else {     // CREATE
            $pdo->beginTransaction();
            $pdo->prepare('INSERT INTO products (sku, name, category, supplier_id, price, quantity, reorder_level) VALUES (?, ?, ?, ?, ?, ?, ?)')
                ->execute([$p['sku'], $p['name'], $p['category'], $supplier, $p['price'], $quantity, $reorder]);
            if ($quantity > 0) {   // record the starting stock in the history
                $pdo->prepare('INSERT INTO stock_movements (product_id, user_id, type, qty, note) VALUES (?, ?, "IN", ?, "Starting stock")')
                    ->execute([$pdo->lastInsertId(), $_SESSION['user']['id'], $quantity]);
            }
            $pdo->commit();
        }
        flash('success', 'Product saved.');
        header('Location: ../products.php');
        exit;
    }
}

$title = $id ? 'Edit product' : 'Add product';
require '../header.php';
?>
<div class="row">
  <div class="col-md-8 col-lg-6">
    <h1 class="h3 mb-3"><?= e($title) ?></h1>
    <?php foreach ($errors as $err): ?>
      <div class="alert alert-danger py-2"><?= e($err) ?></div>
    <?php endforeach; ?>
    <form method="post" class="needs-validation" novalidate>
      <?= csrf_field() ?>
      <div class="row">
        <div class="col-sm-5 mb-3">
          <label class="form-label">SKU</label>
          <input type="text" name="sku" class="form-control" value="<?= e($p['sku']) ?>" required>
          <div class="invalid-feedback">Enter a SKU.</div>
        </div>
        <div class="col-sm-7 mb-3">
          <label class="form-label">Name</label>
          <input type="text" name="name" class="form-control" value="<?= e($p['name']) ?>" required>
          <div class="invalid-feedback">Enter the product name.</div>
        </div>
      </div>
      <div class="row">
        <div class="col-sm-6 mb-3">
          <label class="form-label">Category</label>
          <input type="text" name="category" class="form-control" value="<?= e($p['category']) ?>">
        </div>
        <div class="col-sm-6 mb-3">
          <label class="form-label">Supplier</label>
          <select name="supplier_id" class="form-select">
            <option value="0">None</option>
            <?php foreach ($suppliers as $s): ?>
              <option value="<?= $s['id'] ?>" <?= (int)$p['supplier_id'] === (int)$s['id'] ? 'selected' : '' ?>><?= e($s['name']) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
      </div>
      <div class="row">
        <div class="col-sm-4 mb-3">
          <label class="form-label">Price (&#8369;)</label>
          <input type="number" name="price" class="form-control" step="0.01" min="0" value="<?= e($p['price']) ?>" required>
          <div class="invalid-feedback">Enter a price.</div>
        </div>
        <div class="col-sm-4 mb-3">
          <label class="form-label">Quantity</label>
          <?php if ($id): ?>
            <input type="number" class="form-control" value="<?= (int)$p['quantity'] ?>" disabled>
            <div class="form-text">Change it with Stock In / Out.</div>
          <?php else: ?>
            <input type="number" name="quantity" class="form-control" step="1" min="0" value="<?= e($p['quantity']) ?>" required>
            <div class="invalid-feedback">Enter the starting quantity.</div>
          <?php endif; ?>
        </div>
        <div class="col-sm-4 mb-3">
          <label class="form-label">Reorder level</label>
          <input type="number" name="reorder_level" class="form-control" step="1" min="0" value="<?= e($p['reorder_level']) ?>" required>
          <div class="invalid-feedback">Enter the reorder level.</div>
        </div>
      </div>
      <button class="btn btn-success">Save</button>
      <a href="../products.php" class="btn btn-link">Cancel</a>
    </form>
  </div>
</div>
<?php require '../footer.php'; ?>
