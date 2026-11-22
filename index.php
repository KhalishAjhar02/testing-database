<?php require 'config.php';
$q = trim($_GET['q'] ?? '');
$st = $pdo->prepare("SELECT m.*, (SELECT MAX(number) FROM chapters WHERE manga_id=m.id) AS last_ch
  FROM manga m WHERE m.title LIKE :q OR m.genre LIKE :q ORDER BY m.created_at DESC");
$st->execute([':q' => "%$q%"]);
$list = $st->fetchAll();
head('Beranda'); ?>
<main class="wrap">
<h1><?= $q ? 'Hasil untuk "' . e($q) . '"' : 'Manga terbaru' ?></h1>
<?php if (!$list): ?><p class="empty">Belum ada manga. Tambahkan lewat <a href="admin.php">panel admin</a>.</p><?php endif; ?>
<div class="grid">
<?php foreach ($list as $m): ?>
  <a class="card" href="manga.php?id=<?= $m['id'] ?>">
    <div class="cover"><?php if ($m['cover']): ?><img src="<?= e($m['cover']) ?>" alt="" loading="lazy"><?php else: ?><span><?= e(mb_substr($m['title'], 0, 1)) ?></span><?php endif; ?></div>
    <strong><?= e($m['title']) ?></strong>
    <small><?= $m['last_ch'] !== null ? 'Chapter ' . num($m['last_ch']) : 'Belum ada chapter' ?> &middot; <?= e($m['status']) ?></small>
  </a>
<?php endforeach; ?>
</div></main>
<?php foot();
