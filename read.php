<?php require 'config.php';
$st = $pdo->prepare('SELECT c.*, m.title AS mtitle, m.id AS mid FROM chapters c JOIN manga m ON m.id=c.manga_id WHERE c.id=?');
$st->execute([(int)($_GET['id'] ?? 0)]);
$c = $st->fetch();
if (!$c) { http_response_code(404); head('Tidak ditemukan'); echo '<main class="wrap"><p class="empty">Chapter tidak ditemukan.</p></main>'; foot(); exit; }
$pg = $pdo->prepare('SELECT image_path FROM pages WHERE chapter_id=? ORDER BY page_number'); $pg->execute([$c['id']]);
$pages = $pg->fetchAll();
$prev = $pdo->prepare('SELECT id FROM chapters WHERE manga_id=? AND number<? ORDER BY number DESC LIMIT 1'); $prev->execute([$c['mid'], $c['number']]); $prev = $prev->fetch();
$next = $pdo->prepare('SELECT id FROM chapters WHERE manga_id=? AND number>? ORDER BY number ASC LIMIT 1'); $next->execute([$c['mid'], $c['number']]); $next = $next->fetch();
head($c['mtitle'] . ' Ch. ' . num($c['number']), 'reader');

function nav($c, $prev, $next) { ?>
<nav class="rnav">
  <?php if ($prev): ?><a class="btn" href="read.php?id=<?= $prev['id'] ?>">Sebelumnya</a><?php else: ?><span></span><?php endif; ?>
  <a href="manga.php?id=<?= $c['mid'] ?>"><?= e($c['mtitle']) ?> - Ch. <?= num($c['number']) ?></a>
  <?php if ($next): ?><a class="btn" href="read.php?id=<?= $next['id'] ?>">Berikutnya</a><?php else: ?><span></span><?php endif; ?>
</nav>
<?php }
nav($c, $prev, $next); ?>
<main class="pages">
<?php foreach ($pages as $p): ?><img src="<?= e($p['image_path']) ?>" alt="Halaman" loading="lazy"><?php endforeach;
if (!$pages) echo '<p class="empty">Chapter ini belum punya halaman.</p>'; ?>
</main>
<?php nav($c, $prev, $next); foot();
