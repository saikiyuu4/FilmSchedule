-- FilmSchedule: schema Buổi 3 + dữ liệu mẫu.
-- LƯU Ý: DROP DATABASE sẽ xóa dữ liệu cũ của database filmschedule.
CREATE DATABASE IF NOT EXISTS filmschedule CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE filmschedule;
CREATE TABLE IF NOT EXISTS films (
 id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
 name VARCHAR(200) NOT NULL,
 start_date DATE NOT NULL,
 end_date DATE NOT NULL,
 tickets INT UNSIGNED NOT NULL DEFAULT 0,
 min_price INT UNSIGNED NOT NULL,
 max_price INT UNSIGNED NOT NULL,
 poster VARCHAR(255) NOT NULL DEFAULT 'assets/posters/dune.jpg',
 summary TEXT NULL
) ENGINE=InnoDB;

-- Chỉ thêm dữ liệu mẫu khi bảng chưa có dữ liệu, tránh tạo bản sao mỗi lần import.
INSERT INTO films (name,start_date,end_date,tickets,min_price,max_price,poster,summary)
SELECT * FROM (
SELECT 'Dune: Part Two', '2026-08-01', '2026-09-02', 120, 80000, 150000, 'assets/posters/dune.jpg', 'Hành trình của Paul Atreides tiếp tục giữa sa mạc Arrakis, nơi quyền lực và số phận va chạm.' UNION ALL
SELECT 'Interstellar', '2026-08-05', '2026-09-10', 0, 90000, 180000, 'assets/posters/interstellar.jpg', 'Một phi hành đoàn vượt qua lỗ sâu để tìm hy vọng sống mới cho nhân loại.' UNION ALL
SELECT 'Spider-Man: Across the Spider-Verse', '2026-08-10', '2026-09-15', 35, 85000, 160000, 'assets/posters/spiderman.jpg', 'Miles Morales bước vào đa vũ trụ và đối mặt với những phiên bản Spider-Man khác.' UNION ALL
SELECT 'The Batman', '2026-07-20', '2026-08-25', 0, 70000, 140000, 'assets/posters/batman.jpg', 'Batman điều tra chuỗi án bí ẩn và khám phá mặt tối của Gotham.' UNION ALL
SELECT 'Avatar: The Way of Water', '2026-08-15', '2026-10-01', 50, 100000, 200000, 'assets/posters/avatar.jpg', 'Gia đình Sully tìm nơi trú ẩn mới và khám phá thế giới đại dương Pandora.' UNION ALL
SELECT 'Oppenheimer', '2026-08-20', '2026-09-05', 12, 90000, 170000, 'assets/posters/oppenheimer.jpg', 'Câu chuyện về nhà khoa học đứng sau dự án Manhattan và những hệ quả của bom nguyên tử.' UNION ALL
SELECT 'How to Train Your Dragon', '2026-09-01', '2026-10-05', 0, 80000, 150000, 'assets/posters/dragon.jpg', 'Hiccup và Toothless cùng bảo vệ quê hương và tình bạn giữa con người với loài rồng.' UNION ALL
SELECT 'The Conjuring', '2026-09-03', '2026-09-30', 28, 85000, 160000, 'assets/posters/conjuring.jpg', 'Một gia đình gặp hiện tượng siêu nhiên và phải nhờ đến hai nhà điều tra huyền bí.'
) AS seed
WHERE NOT EXISTS (SELECT 1 FROM films LIMIT 1);
