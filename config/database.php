<?php
$dsn = 'mysql:host=localhost;dbname=filmschedule;charset=utf8mb4';

try {
    $pdo = new PDO(
        $dsn,
        'dung',
        '1',
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]
    );

} catch (PDOException $e) {
    echo "Kết nối thất bại!";
    echo "Lỗi chi tiết: " . $e->getMessage();
}
