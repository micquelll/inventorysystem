<?php 
require_once 'config.php';
require_login();

$errors = [];
$selected = (int)($_GET['product'] ?? 0);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $productId = (int)($_POST['product_id'] ?? 0);
    $type      = $_POST['type'] ?? '';
    $qty       = filter_var($_POST['qty'] ?? '', FILTER_VALIDATE_INT);
    $note      = substr(trim($_POST['note'] ?? ''), 0, 255);
    $selected  = $productId;

    $ok = false;
    $message = '';
    $newQty = 0;
    $statusHtml = '';

    try { 
        if (!in_array($type, ['IN', 'OUT'], true)) throw new Exception('Choose Stock In or Stock Out.');
        if ($qty === false || $qty < 1)            throw new Exception('Quantity must be a whole number, 1 or more.');

        $pdo->beginTransaction();
 
        $stmt = $pdo->prepare('SELECT * FROM products WHERE id = ? FOR UPDATE');
        $stmt->execute([$productId]);
        $product = $stmt->fetch();
        if (!$product) throw new Exception('Product not found.');
 
        if ($type === 'OUT' && $qty > $product['quantity']) {
            throw new Exception('Not enough stock. Only ' . $product['quantity'] . ' left.');
        }

        $newQty = $type === 'IN' ? $product['quantity'] + $qty : $product['quantity'] - $qty;
        $pdo->prepare('UPDATE products SET quantity = ? WHERE id = ?')->execute([$newQty, $productId]);
        $pdo->prepare('INSERT INTO stock_movements (product_id, user_id, type, qty, note) VALUES (?, ?, ?, ?, ?)')
            ->execute([$productId, $_SESSION['user']['id'], $type, $qty, $note === '' ? null : $note]);
        $pdo->commit();

        $ok = true;
        $statusHtml = stock_badge($newQty, $product['reorder_level']);
        $message = 'Stock ' . ($type === 'IN' ? 'added to' : 'removed from') . ' ' . $product['name'] . '. Now ' . $newQty . ' in stock.';
    } catch (Exception $ex) {
        if ($pdo->inTransaction()) $pdo->rollBack();
        $message = $ex->getMessage();
    }
 
    if (($_SERVER['HTTP_X_REQUESTED_WITH'] ?? '') === 'fetch') {
        header('Content-Type: application/json');
        echo json_encode(['ok' => $ok, 'message' => $message, 'quantity' => $newQty, 'status_html' => $statusHtml]);
        exit;
    }

    if ($ok) {
        flash('success', $message);
        header('Location: ' . (($_POST['back'] ?? '') === 'products.php' ? 'products.php' : 'stock.php'));
        exit;
    }
    $errors[] = $message;
}

$products = $pdo->query('SELECT id, sku, name, quantity FROM products ORDER BY name')->fetchAll();

$title = 'Stock In / Out';
require 'header.php';
?>
<div class="row">
  <div class="col-md-8 col-lg-6">
    <h1 class="h3 mb-3">Stock In / Out</h1>
    <?php foreach ($errors as $err): ?>
      <div class="alert alert-danger py-2"><?= e($err) ?></div>
    <?php endforeach; ?>

    <form method="post" class="needs-validation" novalidate>
      <?= csrf_field() ?>
      <div class="mb-3">
        <label class="form-label">Product</label>
        <select name="product_id" class="form-select" required>
          <option value="">Choose a product</option>
          <?php foreach ($products as $p): ?>
            <option value="<?= $p['id'] ?>" <?= $selected === (int)$p['id'] ? 'selected' : '' ?>>
              <?= e($p['name']) ?> (<?= e($p['sku']) ?>) - <?= (int)$p['quantity'] ?> in stock
            </option>
          <?php endforeach; ?>
        </select>
        <div class="invalid-feedback">Choose a product.</div>
      </div>

      <div class="mb-3">
        <div class="form-check form-check-inline">
          <input class="form-check-input" type="radio" name="type" id="in" value="IN" required <?= ($_POST['type'] ?? 'IN') === 'IN' ? 'checked' : '' ?>>
          <label class="form-check-label" for="in">Stock In (received)</label>
        </div>
        <div class="form-check form-check-inline">
          <input class="form-check-input" type="radio" name="type" id="out" value="OUT" required <?= ($_POST['type'] ?? '') === 'OUT' ? 'checked' : '' ?>>
          <label class="form-check-label" for="out">Stock Out (sold or used)</label>
        </div>
      </div>

      <div class="mb-3">
        <label class="form-label">Quantity</label>
        <input type="number" name="qty" class="form-control" min="1" step="1" value="<?= e($_POST['qty'] ?? '') ?>" required>
        <div class="invalid-feedback">Enter a quantity of 1 or more.</div>
      </div>

      <div class="mb-3">
        <label class="form-label">Note <small class="text-muted">(optional)</small></label>
        <input type="text" name="note" class="form-control" maxlength="255" placeholder="Delivery from supplier, damaged, sold..." value="<?= e($_POST['note'] ?? '') ?>">
      </div>

      <button class="btn btn-success">Save</button>
    </form>
  </div>
</div>
<?php require 'footer.php'; ?>
