<?php
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/config/database.php';

$stmt = $pdo->query('SELECT * FROM films ORDER BY id DESC');
$films = $stmt->fetchAll();

$keyword = trim($_GET['keyword'] ?? '');
$fromDate = trim($_GET['from_date'] ?? '');
$toDate = trim($_GET['to_date'] ?? '');

$filteredFilms = [];

foreach ($films as $film) {
    if (($keyword === '' || mb_stripos($film['name'], $keyword, 0, 'UTF-8') !== false) &&
        !(
            ($fromDate !== '' && $fromDate > $film['end_date']) ||
            ($toDate !== '' && $toDate < $film['start_date'])
        )
    ) {
        $filteredFilms[] = $film;
    }
}

$today = date('Y-m-d');
$pageTitle = 'Danh sách phim chiếu - FilmSchedule';
require __DIR__ . '/includes/header.php';
?>

<main class="main-container">
    <h1 class="page-heading">Lịch chiếu phim</h1>
    <p class="page-subtitle">Tìm phim theo tên và khoảng thời gian chiếu.</p>
    <?php if (($_GET['created'] ?? '') === '1'): ?><div class="alert alert-success">Đã thêm phim.</div><?php endif; ?>
    <?php if (($_GET['updated'] ?? '') === '1'): ?><div class="alert alert-success">Đã cập nhật phim.</div><?php endif; ?>
    <?php if (($_GET['deleted'] ?? '') === '1'): ?><div class="alert alert-success">Đã xóa phim.</div><?php endif; ?>
    <div class="film-header">
        <p class="film-count">Tìm thấy <strong><?= count($filteredFilms) ?></strong> phim</p>
        <a href="create-film.php" class="btn">+ Thêm phim mới</a>
    </div>
    <section class="search-box">
        <form class="search-form" method="GET" action="film.php">
            <input class="form-control" type="text" name="keyword" placeholder="Tìm tên phim..." value="<?= e($keyword) ?>">
            <input class="form-control" type="date" name="from_date" value="<?= e($fromDate) ?>">
            <input class="form-control" type="date" name="to_date" value="<?= e($toDate) ?>">
            <button class="btn" type="submit">Tìm kiếm</button>
            <a class="btn btn-secondary" href="film.php">Đặt lại</a>
        </form>
        <p class="search-note">Khoảng ngày sẽ tìm các phim có thời gian chiếu giao với khoảng bạn chọn.</p>
    </section>

    <section class="film-list">
        <?php if (empty($filteredFilms)): ?>
            <div class="empty-state">Không tìm thấy phim phù hợp.</div>
        <?php else: ?>
            <?php foreach ($filteredFilms as $film): ?>
                <article class="film-card">
                    <img class="film-poster" src="<?= e($film['poster']) ?>" alt="Poster <?= e($film['name']) ?>">
                    <div class="film-body">
                        <div class="film-title-row">
                            <h2 class="film-name"><?= e($film['name']) ?></h2>
                            <?php if ($today > $film['end_date']): ?>
                                <span class="badge badge-period">Hết chiếu</span>
                            <?php else: ?>
                                <span class="badge badge-showing">Còn trình chiếu</span>
                            <?php endif; ?>
                        </div>
                        <p class="film-period"><?= formatDate($film['start_date']) ?> - <?= formatDate($film['end_date']) ?></p>
                        <p class="film-summary"><?= e($film['summary'] ?? '') ?></p>
                        <div class="film-meta">
                            <span class="badge badge-price">Giá: <?= formatPrice($film['min_price']) ?> - <?= formatPrice($film['max_price']) ?></span>
                            <?php if ($today <= $film['end_date']): ?>
                                <?php if ((int)$film['tickets'] > 0): ?>
                                    <span class="badge badge-ticket">Còn vé: <?= (int)$film['tickets'] ?></span>
                                <?php else: ?>
                                    <span class="badge badge-soldout">Hết vé</span>
                                <?php endif; ?>
                            <?php endif; ?>
                        </div>

                        <div class="card-actions">
                            <a class="btn btn-secondary" href="edit-film.php?id=<?= (int) $film['id'] ?>">Sửa</a>
                            <form class="inline-form" method="POST" action="delete-film.php" onsubmit="return confirm('Bạn có chắc muốn xóa phim này?');">
                                <input type="hidden" name="id" value="<?= (int) $film['id'] ?>">
                                <button class="btn btn-danger" type="submit">Xóa</button>
                            </form>
                        </div>
                    </div>
                </article>
            <?php endforeach; ?>
        <?php endif; ?>
    </section>
</main>
<?php require __DIR__ . '/includes/floating-nav.php'; ?>
<?php require __DIR__ . '/includes/footer.php'; ?>