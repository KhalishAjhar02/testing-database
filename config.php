<?php
session_start();
const DB_HOST = 'localhost';
const DB_NAME = 'manga_db';
const DB_USER = 'root';
const DB_PASS = 'password_anda';
const ADMIN_USER = 'admin';
const ADMIN_PASS = 'admin123'; // GANTI sebelum online

try {
  $pdo = new PDO('mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4', DB_USER, DB_PASS, [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
  ]);
} catch (PDOException $e) {
  die('Koneksi database gagal. Periksa config.php dan pastikan database manga_db sudah diimpor.');
}

function e($s) { return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); }
function num($n) { return rtrim(rtrim(number_format((float)$n, 1, '.', ''), '0'), '.'); }

function head($title, $body = '') { ?>
<!DOCTYPE html>
<html lang="id"><head>
<meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title><?= e($title) ?> - KomikKu</title>
<link rel="stylesheet" href="style.css">
</head><body class="<?= e($body) ?>">
<header class="top"><a class="logo" href="index.php">KomikKu</a>
<form action="index.php" method="get"><input name="q" placeholder="Cari judul atau genre" value="<?= e($_GET['q'] ?? '') ?>"></form>
<a href="admin.php">Admin</a></header>
<?php }

function foot() { echo '<footer class="foot">KomikKu &copy; ' . date('Y') . '</footer></body></html>'; }
