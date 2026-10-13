<?php
require_once __DIR__ . '/config/database.php';

// Buổi 4: không cho xóa bằng GET; chỉ chấp nhận form POST.
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit('Phương thức không hợp lệ. Hãy xóa phim từ nút Xóa trong danh sách.');
}
$id = (int)($_POST['id'] ?? 0);
if ($id <= 0) exit('ID phim không hợp lệ.');

$stmt = $pdo->prepare('DELETE FROM films WHERE id = :id');
$stmt->execute(['id' => $id]);
// rowCount() xác nhận có record thực sự bị xóa.
if ($stmt->rowCount() === 0) exit('Không tìm thấy phim cần xóa.');
header('Location: film.php?deleted=1');
exit;
