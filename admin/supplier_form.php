<?php
$base = '../';
require_once '../config.php';
require_admin();

// If an id is given we are editing, otherwise creating.
$id = (int)($_GET['id'] ?? 0);
$s = ['name' => '', 'contact_person' => '', 'phone' => '', 'email' => ''];

if ($id) {
    $stmt = $pdo->prepare('SELECT * FROM suppliers WHERE id = ?');
    $stmt->execute([$id]);
    $s = $stmt->fetch();
    if (!$s) { header('Location: suppliers.php'); exit; }
}

$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $s = [
        'name'           => trim($_POST['name'] ?? ''),
        'contact_person' => trim($_POST['contact_person'] ?? ''),
        'phone'          => trim($_POST['phone'] ?? ''),
        'email'          => trim($_POST['email'] ?? ''),
    ];

    // Server-side validation
    if ($s['name'] === '') $errors[] = 'Supplier name is required.';
    if ($s['email'] !== '' && !filter_var($s['email'], FILTER_VALIDATE_EMAIL)) $errors[] = 'Enter a valid email or leave it blank.';

    if (!$errors) {
        $values = [$s['name'], $s['contact_person'], $s['phone'], $s['email']];
        if ($id) {   // UPDATE
            $pdo->prepare('UPDATE suppliers SET name=?, contact_person=?, phone=?, email=? WHERE id=?')
                ->execute(array_merge($values, [$id]));
        } else {     // CREATE
            $pdo->prepare('INSERT INTO suppliers (name, contact_person, phone, email) VALUES (?, ?, ?, ?)')
                ->execute($values);
        }
        flash('success', 'Supplier saved.');
        header('Location: suppliers.php');
        exit;
    }
}

$title = $id ? 'Edit supplier' : 'Add supplier';
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
      <div class="mb-3">
        <label class="form-label">Supplier name</label>
        <input type="text" name="name" class="form-control" value="<?= e($s['name']) ?>" required>
        <div class="invalid-feedback">Enter the supplier name.</div>
      </div>
      <div class="mb-3">
        <label class="form-label">Contact person</label>
        <input type="text" name="contact_person" class="form-control" value="<?= e($s['contact_person']) ?>">
      </div>
      <div class="row">
        <div class="col-sm-6 mb-3">
          <label class="form-label">Phone</label>
          <input type="text" name="phone" class="form-control" value="<?= e($s['phone']) ?>">
        </div>
        <div class="col-sm-6 mb-3">
          <label class="form-label">Email</label>
          <input type="email" name="email" class="form-control" value="<?= e($s['email']) ?>">
          <div class="invalid-feedback">Enter a valid email.</div>
        </div>
      </div>
      <button class="btn btn-success">Save</button>
      <a href="suppliers.php" class="btn btn-link">Cancel</a>
    </form>
  </div>
</div>
<?php require '../footer.php'; ?>
