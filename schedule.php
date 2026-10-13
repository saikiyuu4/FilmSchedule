<?php
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/config/database.php';

$rangeStmt =$pdo->query('SELECT MIN(start_date) AS min_date, MAX(end_date) AS max_date FROM films');
$dateRange =$rangeStmt->fetch(PDO::FETCH_ASSOC);

$currentYearNum  = (int)date('Y');
$currentMonthNum = (int)date('m');$todayDateStr    = date('Y-m-d');

$minYear = !empty($dateRange['min_date']) ? (int)date('Y', strtotime($dateRange['min_date'])) :$currentYearNum;
$maxYear = !empty($dateRange['max_date']) ? (int)date('Y', strtotime($dateRange['max_date'])) :$currentYearNum;

if ($minYear >$maxYear) {
    [$minYear,$maxYear] = [$maxYear,$minYear];
}

$filmsStmt =$pdo->query('SELECT * FROM films ORDER BY start_date ASC');
$films =$filmsStmt->fetchAll(PDO::FETCH_ASSOC);

$dayOfWeekMap = [
    'Monday'    => 'Thứ Hai',
    'Tuesday'   => 'Thứ Ba',
    'Wednesday' => 'Thứ Tư',
    'Thursday'  => 'Thứ Năm',
    'Friday'    => 'Thứ Sáu',
    'Saturday'  => 'Thứ Bảy',
    'Sunday'    => 'Chủ Nhật'
];

$quarters = [];$todayChunkIndex = 0;
$chunkCounter = 0;

for ($year =$minYear; $year <=$maxYear; $year++) {$yearQuarters = [
        ['quarter' => 1, 'months' => [1, 2, 3]],
        ['quarter' => 2, 'months' => [4, 5, 6]],
        ['quarter' => 3, 'months' => [7, 8, 9]],
        ['quarter' => 4, 'months' => [10, 11, 12]],
    ];

    foreach ($yearQuarters as$q) {
        if ($year ===$currentYearNum && in_array($currentMonthNum,$q['months'], true)) {
            $todayChunkIndex =$chunkCounter;
        }

        $quarters[] = [
            'chunk_index' => $chunkCounter,
            'year'        => $year,
            'quarter'     => $q['quarter'],
            'months'      => $q['months']
        ];
        $chunkCounter++;
    }
}

function renderMonthHtml(int $year, int$month, array $films, string$todayDateStr, int $currentYearNum, int$currentMonthNum, array $dayOfWeekMap): string {$monthStr = str_pad($month, 2, '0', STR_PAD_LEFT);$ymKey = "{$year}-{$monthStr}";
    $daysInMonth = cal_days_in_month(CAL_GREGORIAN,$month, $year);$isCurrentMonth = ($year ===$currentYearNum && $month ===$currentMonthNum);

    ob_start();
    ?>
    <div class="schedule-month-block" id="month-<?= $ymKey ?>">
        <div class="schedule-month-header">
            <h3 class="schedule-month-title">
                Tháng <?= $month ?>/<?= $year ?>
                <?php if ($isCurrentMonth): ?>
                    <span class="badge-tag tag-month">Tháng Này</span>
                <?php endif; ?>
            </h3>
            <button type="button" class="collapse-toggle-btn" onclick="toggleMonth('<?= $ymKey ?>', this)">[ẩn tháng]</button>
        </div>

        <div id="month-body-<?= $ymKey ?>" class="schedule-day-list">
            <?php for ($d = 1; $d <= $daysInMonth; $d++): ?>
                <?php
                $dayStr = str_pad($d, 2, '0', STR_PAD_LEFT);
                $dateStr = "{$year}-{$monthStr}-{$dayStr}";
                $isToday = ($dateStr ===$todayDateStr);

                $dayFilms = array_filter($films, function ($film) use ($dateStr) {
                    return ($dateStr >=$film['start_date'] && $dateStr <=$film['end_date']);
                });

                $dayName =$dayOfWeekMap[date('l', strtotime($dateStr))] ?? date('l', strtotime($dateStr));
                ?>
                <div class="schedule-day-card <?= $isToday ? 'is-today' : '' ?>" <?= $isToday ? 'id="today"' : "id=\"day-{$dateStr}\"" ?>>
                    <div class="schedule-day-header">
                        <span class="schedule-day-title">
                            <?= $dayName ?> - Ngày <?= $dayStr ?>/<?= $monthStr ?>/<?= $year ?>
                        </span>
                        <?php if ($isToday): ?>
                            <span class="badge-tag tag-today">Hôm Nay</span>
                        <?php endif; ?>
                    </div>

                    <div class="schedule-poster-grid">
                        <?php if (empty($dayFilms)): ?>
                            <span class="schedule-empty">Không có suất chiếu</span>
                        <?php else: ?>
                            <?php foreach ($dayFilms as$film): ?>
                                <?php
                                $filmJson = htmlspecialchars(json_encode([
                                    'name'    => $film['name'],
                                    'date'    => "{$dayStr}/{$monthStr}/{$year}",
                                    'period'  => formatDate($film['start_date']) . ' - ' . formatDate($film['end_date']),
                                    'price'   => formatPrice($film['min_price']) . ' - ' . formatPrice($film['max_price']),
                                    'tickets' => (int)$film['tickets'],
                                    'summary' => !empty($film['summary']) ?$film['summary'] : 'Đang cập nhật tóm tắt phim.'
                                ], JSON_UNESCAPED_UNICODE), ENT_QUOTES, 'UTF-8');
                                ?>
                                <img 
                                    class="schedule-poster" 
                                    src="<?= e($film['poster']) ?>" 
                                    alt="<?= e($film['name']) ?>" 
                                    title="<?= e($film['name']) ?> (Bấm để xem chi tiết)"
                                    onclick="showFilmDetail(<?= $filmJson ?>)"
                                >
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endfor; ?>
        </div>
    </div>
    <?php
    return ob_get_clean();
}

if (isset($_GET['ajax']) &&$_GET['ajax'] === 'load_quarter') {
    header('Content-Type: application/json; charset=utf-8');
    $chunkIndex = (int)($_GET['chunk'] ?? 0);

    if (!isset($quarters[$chunkIndex])) {
        echo json_encode(['hasMore' => false]);
        exit;
    }

    $qData =$quarters[$chunkIndex];$monthsHtml = '';
    foreach ($qData['months'] as$m) {
        $monthsHtml .= renderMonthHtml($qData['year'], $m,$films, $todayDateStr,$currentYearNum, $currentMonthNum,$dayOfWeekMap);
    }

    echo json_encode([
        'hasMore'              => isset($quarters[$chunkIndex + 1]),
        'nextChunk'            => $chunkIndex + 1, 'year'                 =>$qData['year'],
        'quarter'              => $qData['quarter'],
        'isFirstQuarterOfYear' => ($qData['quarter'] === 1),
        'isCurrentYear'        => ($qData['year'] ===$currentYearNum),
        'html'                 => $monthsHtml
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

$pageTitle = 'Lịch Chiếu Phim - FilmSchedule';
require __DIR__ . '/includes/header.php';
?>

<link rel="stylesheet" href="assets/css/schedule.css">

<main class="main-container">
    <h1 class="page-heading">Lịch Chiếu Phim</h1>
    <p class="page-subtitle">Nhấp vào poster phim để xem thông tin chi tiết từng suất chiếu.</p>

    <div class="toc-box">
        <div class="toc-header">
            <span class="toc-title">Mục lục nội dung</span>
            <button type="button" class="collapse-toggle-btn" id="btn-toggle-toc" onclick="toggleToc(this)">[ẩn]</button>
        </div>
        
        <div id="toc-content">
            <ol class="toc-list">
                <?php for ($y = $minYear; $y <= $maxYear; $y++): ?>
                    <li>
                        <a href="#year-<?= $y ?>" onclick="navigateToYear(<?= $y ?>)">
                            Năm <?= $y ?>
                        </a>
                        <?php if ($y ===$currentYearNum): ?>
                            <span class="badge-tag tag-year">Năm Nay</span>
                        <?php endif; ?>

                        <ol class="toc-sublist">
                            <?php for ($m = 1; $m <= 12; $m++): ?>
                                <?php 
                                $mStr = str_pad($m, 2, '0', STR_PAD_LEFT);$isCurrM = ($y ===$currentYearNum && $m ===$currentMonthNum);
                                ?>
                                <li>
                                    <a href="#month-<?= $y ?>-<?= $mStr ?>" onclick="navigateToMonth(<?= $y ?>, <?= $m ?>)">
                                        Tháng <?= $m ?>/<?= $y ?>
                                    </a>
                                    <?php if ($isCurrM): ?>
                                        <span class="badge-tag tag-month">Tháng Này</span>
                                    <?php endif; ?>
                                </li>
                            <?php endfor; ?>
                        </ol>
                    </li>
                <?php endfor; ?>
            </ol>
        </div>
    </div>

    <div id="schedule-viewport">
        <?php
        $firstQ =$quarters[0];
        ?>
        <div class="schedule-year-block" id="year-<?= $firstQ['year'] ?>">
            <div class="schedule-year-header">
                <h2 class="schedule-year-title">
                     Năm <?= $firstQ['year'] ?>
                    <?php if ($firstQ['year'] ===$currentYearNum): ?>
                        <span class="badge-tag tag-year">Năm Nay</span>
                    <?php endif; ?>
                </h2>
                <button type="button" class="collapse-toggle-btn" onclick="toggleYear(<?= $firstQ['year'] ?>, this)">[ẩn năm]</button>
            </div>
            
            <div class="schedule-year-body" id="year-body-<?= $firstQ['year'] ?>">
                <?php foreach ($firstQ['months'] as$m): ?>
                    <?= renderMonthHtml($firstQ['year'], $m,$films, $todayDateStr,$currentYearNum, $currentMonthNum,$dayOfWeekMap) ?>
                <?php endforeach; ?>
            </div>
        </div>
    </div>

    <div id="scroll-sentinel" class="scroll-sentinel">Đang tải thêm lịch chiếu...</div>
</main>

<?php require_once __DIR__ . '/includes/floating-nav.php'; ?>


<script>
let currentLoadedChunk = 0;
const totalChunks = <?= count($quarters) ?>;
const todayChunkIndex = <?= $todayChunkIndex ?>;
let isLoading = false;

function toggleToc(btn) {
    const content = document.getElementById('toc-content');
    if (!content) return;
    const isHidden = content.style.display === 'none';
    content.style.display = isHidden ? 'block' : 'none';
    btn.textContent = isHidden ? '[ẩn]' : '[hiện]';
}

function toggleYear(year, btn) {
    const yearBody = document.getElementById('year-body-' + year);
    if (!yearBody) return;
    const isHidden = yearBody.style.display === 'none';
    yearBody.style.display = isHidden ? 'block' : 'none';
    btn.textContent = isHidden ? '[ẩn năm]' : '[hiện năm]';
}

function toggleMonth(ymKey, btn) {
    const monthBody = document.getElementById('month-body-' + ymKey);
    if (!monthBody) return;
    const isHidden = monthBody.style.display === 'none';
    monthBody.style.display = isHidden ? 'flex' : 'none';
    btn.textContent = isHidden ? '[ẩn tháng]' : '[hiện tháng]';
}

async function loadNextQuarter() {
    if (isLoading || currentLoadedChunk >= totalChunks - 1) return;
    isLoading = true;

    const nextChunk = currentLoadedChunk + 1;
    const sentinel = document.getElementById('scroll-sentinel');
    sentinel.textContent = 'Đang tải thêm 3 tháng tiếp theo...';

    try {
        const res = await fetch(`schedule.php?ajax=load_quarter&chunk=${nextChunk}`);
        const data = await res.json();

        if (data.html) {
            let yearBlock = document.getElementById('year-' + data.year);
            let yearBody = document.getElementById('year-body-' + data.year);

            if (!yearBlock) {
                const viewport = document.getElementById('schedule-viewport');
                yearBlock = document.createElement('div');
                yearBlock.className = 'schedule-year-block';
                yearBlock.id = 'year-' + data.year;

                const yearTag = data.isCurrentYear ? '<span class="badge-tag tag-year">Năm Nay</span>' : '';
                yearBlock.innerHTML = `
                    <div class="schedule-year-header">
                        <h2 class="schedule-year-title"> Năm ${data.year} ${yearTag}</h2>
                        <button type="button" class="collapse-toggle-btn" onclick="toggleYear(${data.year}, this)">[ẩn năm]</button>
                    </div>
                    <div class="schedule-year-body" id="year-body-${data.year}"></div>
                `;
                viewport.appendChild(yearBlock);
                yearBody = document.getElementById('year-body-' + data.year);
            }

            yearBody.insertAdjacentHTML('beforeend', data.html);
            currentLoadedChunk = nextChunk;
        }

        if (!data.hasMore || currentLoadedChunk >= totalChunks - 1) {
            sentinel.textContent = '— Đã tải toàn bộ lịch chiếu —';
        } else {
            sentinel.textContent = 'Đang cuộn để tải thêm...';
        }
    } catch (e) {
        sentinel.textContent = 'Lỗi kết nối khi tải thêm lịch chiếu.';
    } finally {
        isLoading = false;
    }
}

window.addEventListener('scroll', () => {
    if ((window.innerHeight + window.scrollY) >= document.body.offsetHeight - 500) {
        loadNextQuarter();
    }
});

async function ensureTargetLoaded(targetId, targetChunk) {
    while (!document.getElementById(targetId) && currentLoadedChunk < targetChunk) {
        await loadNextQuarter();
    }
    const el = document.getElementById(targetId);
    if (el) {
        const parentYearBody = el.closest('.schedule-year-body');
        if (parentYearBody) parentYearBody.style.display = 'block';

        const parentMonthBody = el.closest('.schedule-day-list');
        if (parentMonthBody) parentMonthBody.style.display = 'flex';

        el.scrollIntoView({ behavior: 'smooth', block: 'start' });
    }
}

function navigateToYear(year) {
    const targetChunk = (year - <?= $minYear ?>) * 4;
    ensureTargetLoaded('year-' + year, targetChunk);
}

function navigateToMonth(year, month) {
    const mStr = String(month).padStart(2, '0');
    const qIndex = Math.floor((month - 1) / 3);
    const targetChunk = ((year - <?= $minYear ?>) * 4) + qIndex;
    ensureTargetLoaded(`month-${year}-${mStr}`, targetChunk);
}

async function jumpToToday() {
    await ensureTargetLoaded('today', todayChunkIndex);
    const todayEl = document.getElementById('today');
    if (todayEl) {
        todayEl.scrollIntoView({ behavior: 'smooth', block: 'center' });
    } else {
        alert('Không tìm thấy dữ liệu cho ngày hôm nay.');
    }
}

function showFilmDetail(film) {
    const ticketStatus = film.tickets > 0 
        ? ("Còn vé (" + film.tickets + " vé)") 
        : "Đã hết vé";

    const msg = 
        "PHIM: " + film.name.toUpperCase() + "\n" +
        "--------------------------------------------------\n" +
        "Ngày xem: " + film.date + "\n" +
        "Thời gian phát hành: " + film.period + "\n" +
        "Giá vé: " + film.price + "\n" +
        "Tình trạng: " + ticketStatus + "\n\n" +
        "Tóm tắt:\n" + film.summary;

    const popup = document.createElement('div');
    popup.style.cssText = `
        position: fixed; top: 0; left: 0; width: 100vw; height: 100vh;
        background: rgba(0,0,0,0.7); display: flex; align-items: center; justify-content: center;
        z-index: 99999;
    `;

    popup.innerHTML = `
        <div style="background: #241414; border: 1px solid #ff9d00; border-radius: 12px; padding: 22px; max-width: 440px; color: #f8eeee; box-shadow: 0 10px 30px rgba(0,0,0,0.8);">
            <h3 style="margin: 0 0 12px; color: #ff9d00; border-bottom: 1px solid #5e3838; padding-bottom: 8px;">Thông tin phim</h3>
            <div style="white-space: pre-line; line-height: 1.6; font-size: 0.95rem;">${msg}</div>
            <div style="text-align: right; margin-top: 16px;">
                <button id="closePopupBtn" style="background: #ff9d00; color: #321000; border: none; padding: 7px 18px; border-radius: 6px; font-weight: 700; cursor: pointer;">Đóng</button>
            </div>
        </div>
    `;

    document.body.appendChild(popup);

    popup.querySelector('#closePopupBtn').onclick = () => popup.remove();
    popup.onclick = (e) => { if (e.target === popup) popup.remove(); };
}
</script>

<?php require __DIR__ . '/includes/footer.php'; ?>