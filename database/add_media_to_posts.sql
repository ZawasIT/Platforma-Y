-- Dodaj kolumny dla multimediów w postach
ALTER TABLE `posts` 
ADD COLUMN `media_type` ENUM('none', 'image', 'gif') DEFAULT 'none' AFTER `content`,
ADD COLUMN `media_url` VARCHAR(500) NULL AFTER `media_type`;

-- Dodaj indeks dla szybszego wyszukiwania postów z mediami
ALTER TABLE `posts` 
ADD INDEX `idx_media_type` (`media_type`);
