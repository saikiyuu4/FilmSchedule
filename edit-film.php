<?php
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/config/database.php';

// Buổi 4: SELECT một record bằng prepare/execute/fetch.
$id = (int)($_GET['id'] ?? $_POST['id'] ?? 0);
if ($id <= 0) exit('ID phim không hợp lệ.');
$stmt = $pdo->prepare('SELECT * FROM films WHERE id = :id');
$stmt->execute(['id' => $id]);
$film = $stmt->fetch();
if (!$film) exit('Không tìm thấy phim.');

$name = $film['name']; $start = $film['start_date']; $end = $film['end_date'];
$minPrice = (string)$film['min_price']; $maxPrice = (string)$film['max_price'];
$description = $film['summary'] ?? ''; $errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $start = trim($_POST['start'] ?? '');
    $end = trim($_POST['end'] ?? '');
    $minPrice = trim($_POST['min_price'] ?? '');
    $maxPrice = trim($_POST['max_price'] ?? '');
    $description = trim($_POST['description'] ?? '');

    if (mb_strlen($name, 'UTF-8') < 2) $errors['name'] = 'Tên phim phải có ít nhất 2 ký tự.';
    $datePattern = '/^\d{4}-\d{2}-\d{2}$/';
    if (!preg_match($datePattern, $start)) $errors['start'] = 'Ngày bắt đầu không hợp lệ.';
    if (!preg_match($datePattern, $end)) $errors['end'] = 'Ngày kết thúc không hợp lệ.';
    if (preg_match($datePattern, $start) && preg_match($datePattern, $end) && $end < $start) $errors['end'] = 'Ngày kết thúc phải sau hoặc bằng ngày bắt đầu.';
    if (!ctype_digit($minPrice) || (int)$minPrice <= 0) $errors['min_price'] = 'Giá thấp nhất phải là số nguyên lớn hơn 0.';
    if (!ctype_digit($maxPrice) || (int)$maxPrice <= 0) $errors['max_price'] = 'Giá cao nhất phải là số nguyên lớn hơn 0.';
    if (ctype_digit($minPrice) && ctype_digit($maxPrice) && (int)$maxPrice < (int)$minPrice) $errors['max_price'] = 'Giá cao nhất phải lớn hơn hoặc bằng giá thấp nhất.';
    if (mb_strlen($description, 'UTF-8') > 500) $errors['description'] = 'Mô tả không được vượt quá 500 ký tự.';

    if (!$errors) {
        // UPDATE chỉ những cột người dùng có thể chỉnh sửa; giữ số vé và poster hiện tại.
        $update = $pdo->prepare('UPDATE films SET name=:name, start_date=:start_date, end_date=:end_date, min_price=:min_price, max_price=:max_price, summary=:summary WHERE id=:id');
        $update->execute(['name'=>$name, 'start_date'=>$start, 'end_date'=>$end, 'min_price'=>(int)$minPrice, 'max_price'=>(int)$maxPrice, 'summary'=>$description, 'id'=>$id]);
        header('Location: film.php?updated=1'); exit;
    }
}
$pageTitle = 'Sửa phim - FilmSchedule';
require __DIR__ . '/includes/header.php';
?>
<main class="main-container"><h1 class="page-heading">Sửa phim #<?= $id ?></h1>
<p class="page-subtitle">Các trường được nạp sẵn từ database. Lưu thay đổi bằng UPDATE prepared statement.</p>
<section class="form-card"><form method="POST" action="edit-film.php?id=<?= $id ?>">
<input type="hidden" name="id" value="<?= $id ?>">
<div class="form-group"><label for="name">Tên phim *</label><input class="form-control" id="name" name="name" value="<?= e($name) ?>" required><?php if(isset($errors['name'])): ?><p class="field-error"><?= e($errors['name']) ?></p><?php endif; ?></div>
<div class="form-group"><label for="start">Ngày bắt đầu chiếu *</label><input class="form-control" type="date" id="start" name="start" value="<?= e($start) ?>" required><?php if(isset($errors['start'])): ?><p class="field-error"><?= e($errors['start']) ?></p><?php endif; ?></div>
<div class="form-group"><label for="end">Ngày kết thúc chiếu *</label><input class="form-control" type="date" id="end" name="end" value="<?= e($end) ?>" required><?php if(isset($errors['end'])): ?><p class="field-error"><?= e($errors['end']) ?></p><?php endif; ?></div>
<div class="form-group"><label for="min_price">Giá vé thấp nhất *</label><input class="form-control" type="number" min="1" id="min_price" name="min_price" value="<?= e($minPrice) ?>" required><?php if(isset($errors['min_price'])): ?><p class="field-error"><?= e($errors['min_price']) ?></p><?php endif; ?></div>
<div class="form-group"><label for="max_price">Giá vé cao nhất *</label><input class="form-control" type="number" min="1" id="max_price" name="max_price" value="<?= e($maxPrice) ?>" required><?php if(isset($errors['max_price'])): ?><p class="field-error"><?= e($errors['max_price']) ?></p><?php endif; ?></div>
<div class="form-group"><label for="description">Mô tả</label><textarea class="form-control" id="description" name="description"><?= e($description) ?></textarea><?php if(isset($errors['description'])): ?><p class="field-error"><?= e($errors['description']) ?></p><?php endif; ?></div>
<div class="form-actions"><button class="btn" type="submit">Lưu thay đổi</button> <a class="btn btn-secondary" href="products.php">Quay lại</a></div>
</form></section></main>
<?php require __DIR__ . '/includes/footer.php'; ?>
