<?php
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/config/database.php';

$pageTitle = 'Thêm phim - FilmSchedule';

$name = '';
$start = '';
$end = '';
$minPrice = '';
$maxPrice = '';
$description = '';
$errors = [];
$isSuccess = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $start = trim($_POST['start'] ?? '');
    $end = trim($_POST['end'] ?? '');
    $minPrice = trim($_POST['min_price'] ?? '');
    $maxPrice = trim($_POST['max_price'] ?? '');
    $description = trim($_POST['description'] ?? '');

    if ($name === '') {
        $errors['name'] = 'Vui lòng nhập tên phim.';
    } elseif (mb_strlen($name, 'UTF-8') < 2) {
        $errors['name'] = 'Tên phim phải có ít nhất 2 ký tự.';
    }

    if ($start !== '' && $end !== '' && $end < $start) {
        $errors['end'] = 'Ngày kết thúc phải sau hoặc bằng ngày bắt đầu.';
    }

    if ($minPrice === '') {
        $errors['min_price'] = 'Vui lòng nhập giá vé thấp nhất.';
    } elseif (!ctype_digit($minPrice) || (int)$minPrice <= 0) {
        $errors['min_price'] = 'Giá vé thấp nhất phải là số nguyên lớn hơn 0.';
    }

    if ($maxPrice === '') {
        $errors['max_price'] = 'Vui lòng nhập giá vé cao nhất.';
    } elseif (!ctype_digit($maxPrice) || (int)$maxPrice <= 0) {
        $errors['max_price'] = 'Giá vé cao nhất phải là số nguyên lớn hơn 0.';
    }

    if ($minPrice !== '' && $maxPrice !== '' &&
        ctype_digit($minPrice) && ctype_digit($maxPrice) &&
        (int)$maxPrice < (int)$minPrice) {
        $errors['max_price'] = 'Giá vé cao nhất phải lớn hơn hoặc bằng giá thấp nhất.';
    }

    if (mb_strlen($description, 'UTF-8') > 500) {
        $errors['description'] = 'Mô tả không được vượt quá 500 ký tự.';
    }

    if (empty($errors)) {
        $stmt = $pdo->prepare('INSERT INTO films (name, start_date, end_date, tickets, min_price, max_price, poster, summary) VALUES (:name, :start_date, :end_date, 0, :min_price, :max_price, :poster, :summary)');
        $stmt->execute([
            'name' => $name,
            'start_date' => $start,
            'end_date' => $end,
            'min_price' => (int)$minPrice,
            'max_price' => (int)$maxPrice,
            'poster' => 'assets/posters/dune.jpg',
            'summary' => $description,
        ]);
        header('Location: film.php?created=1');
        exit;
    }
}

require __DIR__ . '/includes/header.php';
?>

<main class="main-container">
    <h1 class="page-heading">Thêm phim</h1>
    <p class="page-subtitle">Nhập thông tin phim để kiểm tra và xem trước lịch chiếu.</p>

    <div class="form-layout">
        <section class="form-card">
            <?php if ($isSuccess): ?>
                <div class="alert alert-success">Dữ liệu hợp lệ.</div>
            <?php endif; ?>

            <form method="POST" action="create-film.php" novalidate>
                <div class="form-group">
                    <label for="name">Tên phim <span class="required">*</span></label>
                    <input class="form-control" id="name" name="name" type="text"
                           value="<?= e($name) ?>" placeholder="Ví dụ: Dune: Part Two">
                    <?php if (isset($errors['name'])): ?><p class="field-error"><?= e($errors['name']) ?></p><?php endif; ?>
                </div>

                <div class="form-group">
                    <label for="start">Ngày bắt đầu chiếu <span class="required">*</span></label>
                    <input class="form-control" id="start" name="start" type="date" value="<?= e($start) ?>">
                    <?php if (isset($errors['start'])): ?><p class="field-error"><?= e($errors['start']) ?></p><?php endif; ?>
                </div>

                <div class="form-group">
                    <label for="end">Ngày kết thúc chiếu <span class="required">*</span></label>
                    <input class="form-control" id="end" name="end" type="date" value="<?= e($end) ?>">
                    <?php if (isset($errors['end'])): ?><p class="field-error"><?= e($errors['end']) ?></p><?php endif; ?>
                </div>

                <div class="form-group">
                    <label for="min_price">Giá vé thấp nhất (VND) <span class="required">*</span></label>
                    <input class="form-control" id="min_price" name="min_price" type="number" min="1" value="<?= e($minPrice) ?>" placeholder="80000">
                    <?php if (isset($errors['min_price'])): ?><p class="field-error"><?= e($errors['min_price']) ?></p><?php endif; ?>
                </div>

                <div class="form-group">
                    <label for="max_price">Giá vé cao nhất (VND) <span class="required">*</span></label>
                    <input class="form-control" id="max_price" name="max_price" type="number" min="1" value="<?= e($maxPrice) ?>" placeholder="150000">
                    <?php if (isset($errors['max_price'])): ?><p class="field-error"><?= e($errors['max_price']) ?></p><?php endif; ?>
                </div>

                <div class="form-group">
                    <label for="description">Mô tả</label>
                    <textarea class="form-control" id="description" name="description" placeholder="Mô tả ngắn về phim..."><?= e($description) ?></textarea>
                    <?php if (isset($errors['description'])): ?><p class="field-error"><?= e($errors['description']) ?></p><?php endif; ?>
                </div>

                <div class="form-actions">
                    <button class="btn" type="submit">Thêm Phim</button>
                    <a class="btn btn-secondary" href="film.php">Quay lại lịch chiếu</a>
                </div>
            </form>
        </section>

        <aside>
            <section class="info-card">
                <h2>Yêu cầu form</h2>
                <ul>
                    <li>Nhận dữ liệu bằng POST.</li>
                    <li>Validate phía server.</li>
                    <li>Lỗi hiển thị cạnh đúng field.</li>
                    <li>Giữ lại dữ liệu khi có lỗi.</li>
                    <li>Không lưu database.</li>
                </ul>
            </section>

            <?php if ($isSuccess): ?>
                <section class="info-card">
                    <h2>Preview Card</h2>
                    <p><strong>Tên phim:</strong> <?= e($name) ?></p>
                    <p><strong>Thời gian:</strong> <?= e($start) ?> đến <?= e($end) ?></p>
                    <p><strong>Giá vé:</strong> <?= e($minPrice) ?>đ - <?= e($maxPrice) ?>đ</p>
                    <p><strong>Mô tả:</strong> <?= $description === '' ? 'Không có.' : e($description) ?></p>
                    <p class="alert alert-success">✓ Dữ liệu hợp lệ.</p>
                </section>
            <?php endif; ?>
        </aside>
    </div>
</main>

<?php require __DIR__ . '/includes/footer.php'; ?>
