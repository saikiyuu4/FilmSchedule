<!doctype html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= e($pageTitle ?? 'FilmSchedule') ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">
</head>

<body>
    <header class="site-header">
        <div class="header-inner">
            <a class="brand" href="index.php"><span class="brand-mark">F</span><span>FilmSchedule</span></a>
            <nav class="main-nav">
                <a href="index.php">Trang chủ</a>
                <a href="film.php">Danh sách</a>
                <a href="schedule.php">Lịch chiếu</a>
                <a href="create-film.php">Thêm phim</a>
            </nav>
        </div>
    </header>