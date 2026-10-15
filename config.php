<?php
ob_start();      
session_start();

$DB_HOST = 'sql103.infinityfree.com';
$DB_NAME = 'if0_43174275_inventory_system';
$DB_USER = 'if0_43174275t';
$DB_PASS = '';

try {
    $pdo = new PDO("mysql:host=$DB_HOST;dbname=$DB_NAME;charset=utf8mb4", $DB_USER, $DB_PASS, [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);
} catch (PDOException $ex) {
    die('Could not connect to the database. Check config.php.');
}

function e($text) {
    return htmlspecialchars((string)$text, ENT_QUOTES, 'UTF-8');
}

function is_admin() {
    return isset($_SESSION['user']) && $_SESSION['user']['role'] === 'admin';
}

function require_login() {
    global $base;
    if (!isset($_SESSION['user'])) {
        header('Location: ' . $base . 'login.php');
        exit;
    }
}

function require_admin() {
    global $base;
    if (!is_admin()) {
        header('Location: ' . $base . (isset($_SESSION['user']) ? 'dashboard.php' : 'login.php'));
        exit;
    }
}

function stock_badge($qty, $reorder) {
    if ($qty <= 0)        return '<span class="badge bg-danger">Out of stock</span>';
    if ($qty <= $reorder) return '<span class="badge bg-warning text-dark">Low stock</span>';
    return '<span class="badge bg-success">In stock</span>';
}
function flash($type, $message) {
    $_SESSION['flash'] = [$type, $message];
}

function show_flash() {
    if (!empty($_SESSION['flash'])) {
        [$type, $message] = $_SESSION['flash'];
        unset($_SESSION['flash']);
        echo '<div class="alert alert-' . e($type) . '">' . e($message) . '</div>';
    }
}

function csrf_field() {
    if (empty($_SESSION['csrf'])) {
        $_SESSION['csrf'] = bin2hex(random_bytes(16));
    }
    return '<input type="hidden" name="csrf" value="' . $_SESSION['csrf'] . '">';
}

function csrf_check() {
    if (empty($_POST['csrf']) || !hash_equals($_SESSION['csrf'] ?? '', $_POST['csrf'])) {
        die('Invalid form submission. Go back and try again.');
    }
}
