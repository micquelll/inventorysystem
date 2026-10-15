<?php
require_once 'config.php';

$errors = [];
$name = $email = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $name     = trim($_POST['name'] ?? '');
    $email    = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm  = $_POST['confirm'] ?? '';

    // Server-side validation
    if ($name === '')                                $errors[] = 'Name is required.';
    if (!filter_var($email, FILTER_VALIDATE_EMAIL))  $errors[] = 'Enter a valid email.';
    if (strlen($password) < 6)                       $errors[] = 'Password must be at least 6 characters.';
    if ($password !== $confirm)                      $errors[] = 'Passwords do not match.';

    if (!$errors) {
        $check = $pdo->prepare('SELECT id FROM users WHERE email = ?');
        $check->execute([$email]);
        if ($check->fetch()) {
            $errors[] = 'That email is already registered.';
        }
    }

    if (!$errors) {
        $stmt = $pdo->prepare('INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, "staff")');
        $stmt->execute([$name, $email, password_hash($password, PASSWORD_DEFAULT)]);
        flash('success', 'Account created. You can log in now.');
        header('Location: login.php');
        exit;
    }
}

$title = 'Register';
require 'header.php';
?>
<h2 class="h5 mb-3">Create a staff account</h2>
<?php foreach ($errors as $err): ?>
  <div class="alert alert-danger py-2"><?= e($err) ?></div>
<?php endforeach; ?>
<form method="post" class="needs-validation" novalidate>
  <?= csrf_field() ?>
  <div class="mb-3">
    <label class="form-label">Full name</label>
    <input type="text" name="name" class="form-control" value="<?= e($name) ?>" required>
    <div class="invalid-feedback">Please enter your name.</div>
  </div>
  <div class="mb-3">
    <label class="form-label">Email</label>
    <input type="email" name="email" class="form-control" value="<?= e($email) ?>" required>
    <div class="invalid-feedback">Please enter a valid email.</div>
  </div>
  <div class="mb-3">
    <label class="form-label">Password</label>
    <input type="password" name="password" class="form-control" minlength="6" required>
    <div class="invalid-feedback">At least 6 characters.</div>
  </div>
  <div class="mb-3">
    <label class="form-label">Confirm password</label>
    <input type="password" name="confirm" class="form-control" required>
    <div class="invalid-feedback">Passwords must match.</div>
  </div>
  <button class="btn btn-success w-100">Register</button>
</form>
<p class="text-center small mt-3 mb-0">Already have an account? <a href="login.php">Login</a></p>
<?php require 'footer.php'; ?>
