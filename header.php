<?php
require_once __DIR__ . '/config.php';
$base = $base ?? '';
$user = $_SESSION['user'] ?? null; 
$menu = [
    ['dashboard.php', 'Dashboard'],
    ['products.php',  'Products', 'admin/product_form.php'],
    ['stock.php',     'Stock In / Out'],
    ['movements.php', 'Movements'],
];
if (is_admin()) {
    $menu[] = ['admin/suppliers.php', 'Suppliers', 'admin/supplier_form.php'];
    $menu[] = ['admin/users.php',     'Users'];
}

function ends_with($text, $end) {
    return substr($text, -strlen($end)) === $end;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?= e($title ?? 'Inventory') ?> - Inventory Management</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="<?= $base ?>assets/style.css" rel="stylesheet">
</head>
<body>
<?php if (!$user): ?>
 
<div class="auth-wrap">
  <div class="auth-card">
    <h1 class="h4 text-center mb-1">Inventory Management</h1>
    <p class="text-center text-muted small mb-4">Track your stock in one place</p>
    <?php show_flash(); ?>

<?php else: ?>
 
<div class="d-flex">
  <nav id="sidebar" class="offcanvas-md offcanvas-start sidebar" tabindex="-1">
    <div class="sidebar-inner d-flex flex-column p-3">
      <div class="d-flex justify-content-between align-items-center mb-4">
        <a class="brand" href="<?= $base ?>dashboard.php">Inventory</a>
        <button type="button" class="btn-close btn-close-white d-md-none" data-bs-dismiss="offcanvas" data-bs-target="#sidebar"></button>
      </div>

      <ul class="nav flex-column mb-auto">
        <?php foreach ($menu as $item): ?>
          <?php
            $pages = array_merge([$item[0]], array_slice($item, 2));
            $active = false;
            foreach ($pages as $p) {
                if (ends_with($_SERVER['SCRIPT_NAME'], $p)) $active = true;
            }
          ?>
          <li class="nav-item">
            <a class="nav-link <?= $active ? 'active' : '' ?>" href="<?= $base . $item[0] ?>"><?= e($item[1]) ?></a>
          </li>
        <?php endforeach; ?>
      </ul>

      <div class="user-box">
        <div class="fw-semibold"><?= e($user['name']) ?></div>
        <div class="small text-uppercase opacity-75 mb-2"><?= e($user['role']) ?></div>
        <a class="btn btn-sm btn-outline-light w-100" href="<?= $base ?>logout.php">Logout</a>
      </div>
    </div>
  </nav>

  <div class="content flex-grow-1">
    <div class="topbar d-md-none d-flex align-items-center p-2">
      <button class="btn btn-sm btn-dark me-2" type="button" data-bs-toggle="offcanvas" data-bs-target="#sidebar">Menu</button>
      <strong>Inventory</strong>
    </div>
    <main class="p-3 p-md-4">
      <?php show_flash(); ?>

<?php endif; ?>
