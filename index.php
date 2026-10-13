<?php
require_once __DIR__ . '/includes/functions.php';
$year = '2026';
$theater = 'Movie Universe';
$pageTitle = 'Trang chủ - FilmSchedule';

require __DIR__ . '/includes/header.php';
?>


<main class="main-container">
    <section class="hero">
        <h1>FilmSchedule</h1>
        <p>Tra cứu lịch chiếu phim tại rạp <?= e($theater) ?> trong năm <?= e($year) ?>.</p>
        <a class="btn" href="products.php">Xem lịch chiếu</a>
    </section>
</main>
<?php require_once __DIR__ . '/includes/floating-nav.php'; ?>


<?php require __DIR__ . '/includes/footer.php'; ?>