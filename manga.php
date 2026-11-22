<?php require 'config.php';
$st = $pdo->prepare('SELECT * FROM manga WHERE id=?'); $st->execute([(int)($_GET['id'] ?? 0)]);
$m = $st->fetch();
if (!$m) { http_response_code(404); head('Tidak ditemukan'); echo '<main class="wrap"><p class="empty">Manga tidak ditemukan.</p></main>'; foot(); exit; }
$ch = $pdo->prepare('SELECT * FROM chapters WHERE manga_id=? ORDER BY number DESC'); $ch->execute([$m['id']]);
$chapters = $ch->fetchAll();
head($m['title']); ?>
<main class="wrap">
<section class="detail">
  <div class="cover big"><?php if ($m['cover']): ?><img src="<?= e($m['cover']) ?>" alt=""><?php else: ?><span><?= e(mb_substr($m['title'], 0, 1)) ?></span><?php endif; ?></div>
  <div>
    <h1><?= e($m['title']) ?></h1>
    <p class="meta">Penulis: <?= e($m['author'] ?: '-') ?><br>Genre: <?= e($m['genre'] ?: '-') ?><br>Status: <?= e($m['status']) ?></p>
    <p><?= nl2br(e($m['description'])) ?></p>
    <?php if ($chapters): ?><a class="btn" href="read.php?id=<?= end($chapters)['id'] ?>">Mulai baca</a><?php endif; ?>
  </div>
</section>
<h2>Daftar chapter</h2>
<ul class="chlist">
<?php foreach ($chapters as $c): ?>
  <li><a href="read.php?id=<?= $c['id'] ?>">Chapter <?= num($c['number']) ?><?= $c['title'] ? ' - ' . e($c['title']) : '' ?></a><small><?= date('d M Y', strtotime($c['created_at'])) ?></small></li>
<?php endforeach; if (!$chapters) echo '<li class="empty">Belum ada chapter.</li>'; ?>
</ul></main>
<?php foot();
