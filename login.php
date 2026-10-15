<?php
require_once 'config.php';

if (isset($_SESSION['user'])) {
    header('Location: dashboard.php');
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $email    = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    $stmt = $pdo->prepare('SELECT * FROM users WHERE email = ?');
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['password'])) {
        session_regenerate_id(true);   // protects against session fixation
        $_SESSION['user'] = ['id' => $user['id'], 'name' => $user['name'], 'role' => $user['role']];
        header('Location: dashboard.php');
        exit;
    }
    $error = 'Wrong email or password.';
}

$title = 'Login';
require 'header.php';
?>
<?php if ($error): ?>
  <div class="alert alert-danger py-2"><?= e($error) ?></div>
<?php endif; ?>
<form method="post" class="needs-validation" novalidate>
  <?= csrf_field() ?>
  <div class="mb-3">
    <label class="form-label">Email</label>
    <input type="email" name="email" class="form-control" required>
    <div class="invalid-feedback">Please enter your email.</div>
  </div>
  <div class="mb-3">
    <label class="form-label">Password</label>
    <input type="password" name="password" class="form-control" required>
    <div class="invalid-feedback">Please enter your password.</div>
  </div>
  <button class="btn btn-success w-100">Login</button>
</form>
<p class="text-center small mt-3 mb-0">Need an account? <a href="register.php">Register</a></p>
<?php require 'footer.php'; ?>
