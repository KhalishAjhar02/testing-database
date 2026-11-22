<?php require 'config.php';
$msg = '';
function save_image($tmp, $name, $dir) {
  $ext = strtolower(pathinfo($name, PATHINFO_EXTENSION));
  if (!in_array($ext, ['jpg', 'jpeg', 'png', 'webp', 'gif']) || !@getimagesize($tmp)) return null;
  if (!is_dir($dir)) mkdir($dir, 0775, true);
  $file = $dir . '/' . bin2hex(random_bytes(6)) . '.' . $ext;
  return move_uploaded_file($tmp, $file) ? $file : null;
}

if (isset($_GET['logout'])) { session_destroy(); header('Location: admin.php'); exit; }
if (isset($_POST['login'])) {
  if ($_POST['user'] === ADMIN_USER && $_POST['pass'] === ADMIN_PASS) { $_SESSION['admin'] = true; session_regenerate_id(true); }
  else $msg = 'Username atau password salah.';
}
$in = !empty($_SESSION['admin']);

if ($in && $_SERVER['REQUEST_METHOD'] === 'POST') {
  if (isset($_POST['add_manga']) && trim($_POST['title'])) {
    $cover = !empty($_FILES['cover']['tmp_name']) ? save_image($_FILES['cover']['tmp_name'], $_FILES['cover']['name'], 'uploads/covers') : null;
    $pdo->prepare('INSERT INTO manga (title,author,genre,description,cover,status) VALUES (?,?,?,?,?,?)')
        ->execute([trim($_POST['title']), $_POST['author'], $_POST['genre'], $_POST['description'], $cover, $_POST['status'] === 'Tamat' ? 'Tamat' : 'Ongoing']);
    $msg = 'Manga ditambahkan.';
  }
  if (isset($_POST['add_chapter'])) {
    try {
      $pdo->beginTransaction();
      $pdo->prepare('INSERT INTO chapters (manga_id,number,title) VALUES (?,?,?)')->execute([(int)$_POST['manga_id'], (float)$_POST['number'], $_POST['ctitle']]);
      $cid = $pdo->lastInsertId();
      $files = $_FILES['pages']; $order = array_keys($files['name']);
      usort($order, fn($a, $b) => strnatcasecmp($files['name'][$a], $files['name'][$b]));
      $i = 0;
      foreach ($order as $k) {
        if (!$files['tmp_name'][$k]) continue;
        $path = save_image($files['tmp_name'][$k], $files['name'][$k], "uploads/chapters/$cid");
        if ($path) $pdo->prepare('INSERT INTO pages (chapter_id,page_number,image_path) VALUES (?,?,?)')->execute([$cid, ++$i, $path]);
      }
      $pdo->commit(); $msg = "Chapter ditambahkan dengan $i halaman.";
    } catch (Exception $ex) { $pdo->rollBack(); $msg = 'Gagal: nomor chapter mungkin sudah ada.'; }
  }
  if (isset($_POST['del_manga'])) { $pdo->prepare('DELETE FROM manga WHERE id=?')->execute([(int)$_POST['id']]); $msg = 'Manga dihapus.'; }
  if (isset($_POST['del_chapter'])) { $pdo->prepare('DELETE FROM chapters WHERE id=?')->execute([(int)$_POST['id']]); $msg = 'Chapter dihapus.'; }
}

head('Admin'); ?>
<main class="wrap">
<h1>Panel admin</h1>
<?php if ($msg): ?><p class="notice"><?= e($msg) ?></p><?php endif; ?>
<?php if (!$in): ?>
<form method="post" class="form narrow">
  <label>Username <input name="user" required></label>
  <label>Password <input name="pass" type="password" required></label>
  <button class="btn" name="login">Masuk</button>
</form>
<?php else:
  $mangas = $pdo->query('SELECT * FROM manga ORDER BY title')->fetchAll(); ?>
<p><a href="admin.php?logout=1">Keluar</a></p>
<div class="cols">
<form method="post" enctype="multipart/form-data" class="form">
  <h2>Tambah manga</h2>
  <label>Judul <input name="title" required></label>
  <label>Penulis <input name="author"></label>
  <label>Genre <input name="genre" placeholder="Aksi, Komedi"></label>
  <label>Sinopsis <textarea name="description" rows="4"></textarea></label>
  <label>Status <select name="status"><option>Ongoing</option><option>Tamat</option></select></label>
  <label>Cover <input type="file" name="cover" accept="image/*"></label>
  <button class="btn" name="add_manga">Simpan manga</button>
</form>
<form method="post" enctype="multipart/form-data" class="form">
  <h2>Tambah chapter</h2>
  <label>Manga <select name="manga_id" required><?php foreach ($mangas as $m) echo '<option value="' . $m['id'] . '">' . e($m['title']) . '</option>'; ?></select></label>
  <label>Nomor chapter <input name="number" type="number" step="0.1" required></label>
  <label>Judul chapter (opsional) <input name="ctitle"></label>
  <label>Halaman (pilih banyak gambar, diurutkan sesuai nama file) <input type="file" name="pages[]" accept="image/*" multiple required></label>
  <button class="btn" name="add_chapter">Unggah chapter</button>
</form>
</div>
<h2>Kelola data</h2>
<?php foreach ($mangas as $m):
  $cs = $pdo->prepare('SELECT * FROM chapters WHERE manga_id=? ORDER BY number'); $cs->execute([$m['id']]); ?>
  <div class="row"><strong><?= e($m['title']) ?></strong>
  <form method="post" onsubmit="return confirm('Hapus manga beserta semua chapter?')"><input type="hidden" name="id" value="<?= $m['id'] ?>"><button class="link" name="del_manga">Hapus manga</button></form></div>
  <?php foreach ($cs->fetchAll() as $c): ?>
  <div class="row sub">Chapter <?= num($c['number']) ?>
  <form method="post" onsubmit="return confirm('Hapus chapter ini?')"><input type="hidden" name="id" value="<?= $c['id'] ?>"><button class="link" name="del_chapter">Hapus</button></form></div>
  <?php endforeach;
endforeach; endif; ?>
</main>
<?php foot();
