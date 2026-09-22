<?php

$films = [
    ['name' => 'Dune: Part Two', 'start' => '2026-08-01', 'end' => '2026-09-02', 'tickets' => 120, 'min_price' => 80000, 'max_price' => 150000, 'poster' => 'assets/posters/dune.jpg', 'summary' => 'Hành trình của Paul Atreides tiếp tục giữa sa mạc Arrakis, nơi quyền lực và số phận va chạm.'],
    ['name' => 'Interstellar', 'start' => '2026-08-05', 'end' => '2026-09-10', 'tickets' => 0, 'min_price' => 90000, 'max_price' => 180000, 'poster' => 'assets/posters/interstellar.jpg', 'summary' => 'Một phi hành đoàn vượt qua lỗ sâu để tìm hy vọng sống mới cho nhân loại.'],
    ['name' => 'Spider-Man: Across the Spider-Verse', 'start' => '2026-08-10', 'end' => '2026-09-15', 'tickets' => 35, 'min_price' => 85000, 'max_price' => 160000, 'poster' => 'assets/posters/spiderman.jpg', 'summary' => 'Miles Morales bước vào đa vũ trụ và đối mặt với những phiên bản Spider-Man khác.'],
    ['name' => 'The Batman', 'start' => '2026-07-20', 'end' => '2026-08-25', 'tickets' => 0, 'min_price' => 70000, 'max_price' => 140000, 'poster' => 'assets/posters/batman.jpg', 'summary' => 'Batman điều tra chuỗi án bí ẩn và khám phá mặt tối của Gotham.'],
    ['name' => 'Avatar: The Way of Water', 'start' => '2026-08-15', 'end' => '2026-10-01', 'tickets' => 50, 'min_price' => 100000, 'max_price' => 200000, 'poster' => 'assets/posters/avatar.jpg', 'summary' => 'Gia đình Sully tìm nơi trú ẩn mới và khám phá thế giới đại dương Pandora.'],
    ['name' => 'Oppenheimer', 'start' => '2026-08-20', 'end' => '2026-09-05', 'tickets' => 12, 'min_price' => 90000, 'max_price' => 170000, 'poster' => 'assets/posters/oppenheimer.jpg', 'summary' => 'Câu chuyện về nhà khoa học đứng sau dự án Manhattan và những hệ quả của bom nguyên tử.'],
    ['name' => 'How to Train Your Dragon', 'start' => '2026-09-01', 'end' => '2026-10-05', 'tickets' => 0, 'min_price' => 80000, 'max_price' => 150000, 'poster' => 'assets/posters/dragon.jpg', 'summary' => 'Hiccup và Toothless cùng bảo vệ quê hương và tình bạn giữa con người với loài rồng.'],
    ['name' => 'The Conjuring', 'start' => '2026-09-03', 'end' => '2026-09-30', 'tickets' => 28, 'min_price' => 85000, 'max_price' => 160000, 'poster' => 'assets/posters/conjuring.jpg', 'summary' => 'Một gia đình gặp hiện tượng siêu nhiên và phải nhờ đến hai nhà điều tra huyền bí.'],
];

$keyword = htmlspecialchars($_GET['keyword'] ?? '', ENT_QUOTES, 'UTF-8');
$fromDate = htmlspecialchars($_GET['from_date'] ?? '', ENT_QUOTES, 'UTF-8');
$toDate = htmlspecialchars($_GET['to_date'] ?? '', ENT_QUOTES, 'UTF-8');

$filteredFilms = [];

foreach ($films as $film) {
    if (($keyword === '' || mb_stripos($film['name'], $keyword, 0, 'UTF-8') !== false) &&
        !(
            ($fromDate !== '' && $fromDate > $film['end']) ||
            ($toDate !== '' && $toDate < $film['start'])
        )) {
        $filteredFilms[] = $film;
    }
}

function formatDate(string $date) {
    return date('d/m/Y', strtotime($date));
}

function formatPrice(float|int $price) {
    return number_format($price, 0, ',', '.') . 'đ';
}

$today = date('Y-m-d');

?>
<!doctype html>
<html lang="vi">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Lịch chiếu phim</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
<header class="site-header">
    <div class="header-inner">
        <a class="brand" href="index.php"><span class="brand-mark">F</span><span>FilmSchedule</span></a>
        <nav class="main-nav">
            <a href="index.php">Trang chủ</a>
            <a href="products.php">Lịch chiếu</a>
        </nav>
    </div>
</header>

<main class="main-container">
    <h1 class="page-heading">Lịch chiếu phim</h1>
    <p class="page-subtitle">Tìm phim theo tên và khoảng thời gian chiếu.</p>

    <section class="search-box">
        <form class="search-form" method="GET" action="products.php">
            <input class="form-control" type="text" name="keyword" placeholder="Tìm tên phim..." value="<?= htmlspecialchars($keyword, ENT_QUOTES, 'UTF-8') ?>">
            <input class="form-control" type="date" name="from_date" value="<?= htmlspecialchars($fromDate, ENT_QUOTES, 'UTF-8') ?>">
            <input class="form-control" type="date" name="to_date" value="<?= htmlspecialchars($toDate, ENT_QUOTES, 'UTF-8') ?>">
            <button class="btn" type="submit">Tìm kiếm</button>
        </form>
        <p class="search-note">Khoảng ngày sẽ tìm các phim có thời gian chiếu giao với khoảng bạn chọn.</p>
    </section>

    <section class="film-list">
        <?php if (empty($filteredFilms)): ?>
            <div class="empty-state">Không tìm thấy phim phù hợp.</div>
        <?php else: ?>
            <?php foreach ($filteredFilms as $film): ?>
                <article class="film-card">
                    <img class="film-poster" src="<?= htmlspecialchars($film['poster'], ENT_QUOTES, 'UTF-8') ?>"
                         alt="Poster <?= htmlspecialchars($film['name'], ENT_QUOTES, 'UTF-8') ?>">

                    <div class="film-body">
                        <div class="film-title-row">
                            <h2 class="film-name"><?= htmlspecialchars($film['name'], ENT_QUOTES, 'UTF-8') ?></h2>
                            <?php if ($today > $film['end']): ?>
                                <span class="badge badge-period">Hết chiếu</span>
                            <?php else: ?>
                                <span class="badge badge-showing">Còn trình chiếu</span>
                            <?php endif; ?>
                        </div>

                        <p class="film-period">
                            <?= formatDate($film['start']) ?> - <?= formatDate($film['end']) ?>
                        </p>

                        <p class="film-summary"><?= htmlspecialchars($film['summary'], ENT_QUOTES, 'UTF-8') ?></p>

                        <div class="film-meta">
                            <span class="badge badge-price">Giá: <?= formatPrice($film['min_price']) ?> - <?= formatPrice($film['max_price']) ?></span>
                            <?php if ($today <= $film['end']): ?>
                                <?php if ($film['tickets'] > 0): ?>
                                    <span class="badge badge-ticket">Còn vé: <?= $film['tickets'] ?></span>
                                <?php else: ?>
                                    <span class="badge badge-soldout">Hết vé</span>
                                <?php endif; ?>
                            <?php endif; ?>
                        </div>
                    </div>
                </article>
            <?php endforeach; ?>
        <?php endif; ?>
    </section>
</main>

<footer class="site-footer">
    <div class="footer-inner">FilmSchedule • Movie Universe • 2026</div>
</footer>
</body>
</html>
