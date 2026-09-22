<?php
$year = '2026';
$theater = 'Movie Universe';
?>
<!doctype html>
<html lang="vi">
<head>
<meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
<title>FilmSchedule</title><link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
<header class="site-header"><div class="header-inner"><a class="brand" href="index.php"><span class="brand-mark">F</span><span>FilmSchedule</span></a><nav class="main-nav"><a href="index.php">Trang chủ</a><a href="products.php">Lịch chiếu</a></nav></div></header>
<main class="main-container"><section class="hero"><h1>FilmSchedule</h1><p>Tra cứu lịch chiếu phim tại rạp <?= htmlspecialchars($theater) ?> trong năm <?= htmlspecialchars($year) ?>.</p><a class="btn" href="products.php">Xem lịch chiếu</a></section></main>
<footer class="site-footer"><div class="footer-inner">Movie Universe • FilmSchedule • 2026</div></footer>
</body></html>
