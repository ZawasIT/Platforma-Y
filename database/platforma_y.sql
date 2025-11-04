-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1:3307:3307
-- Generation Time: Lis 04, 2025 at 12:24 PM
-- Wersja serwera: 10.4.28-MariaDB
-- Wersja PHP: 8.2.4

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `platforma_y`
--

-- --------------------------------------------------------

--
-- Struktura tabeli dla tabeli `follows`
--

CREATE TABLE `follows` (
  `id` int(11) NOT NULL,
  `follower_id` int(11) NOT NULL,
  `following_id` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `follows`
--

INSERT INTO `follows` (`id`, `follower_id`, `following_id`, `created_at`) VALUES
(2, 13, 10, '2025-10-22 20:58:51'),
(24, 13, 4, '2025-10-22 21:19:32'),
(25, 13, 7, '2025-10-22 21:19:33'),
(26, 13, 8, '2025-10-22 21:19:33'),
(27, 13, 1, '2025-10-22 21:19:34'),
(28, 13, 12, '2025-10-22 21:19:35'),
(31, 13, 3, '2025-10-22 21:41:23'),
(45, 3, 13, '2025-10-23 14:57:53'),
(46, 3, 14, '2025-11-04 09:42:07');

-- --------------------------------------------------------

--
-- Struktura tabeli dla tabeli `likes`
--

CREATE TABLE `likes` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `post_id` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `likes`
--

INSERT INTO `likes` (`id`, `user_id`, `post_id`, `created_at`) VALUES
(136, 13, 8, '2025-10-22 21:41:32'),
(141, 3, 8, '2025-10-22 21:41:59'),
(154, 15, 12, '2025-10-28 12:01:17'),
(155, 15, 10, '2025-10-28 12:01:28'),
(157, 15, 8, '2025-10-28 12:01:35'),
(158, 3, 10, '2025-11-04 09:42:11');

-- --------------------------------------------------------

--
-- Struktura tabeli dla tabeli `notifications`
--

CREATE TABLE `notifications` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `actor_id` int(11) NOT NULL,
  `type` enum('like','repost','follow','reply','mention') NOT NULL,
  `post_id` int(11) DEFAULT NULL,
  `reply_id` int(11) DEFAULT NULL,
  `is_read` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `notifications`
--

INSERT INTO `notifications` (`id`, `user_id`, `actor_id`, `type`, `post_id`, `reply_id`, `is_read`, `created_at`) VALUES
(13, 3, 13, 'follow', NULL, NULL, 0, '2025-10-22 21:18:58'),
(14, 3, 13, 'follow', NULL, NULL, 0, '2025-10-22 21:19:02'),
(15, 3, 13, 'follow', NULL, NULL, 0, '2025-10-22 21:19:04'),
(17, 7, 13, 'follow', NULL, NULL, 0, '2025-10-22 21:19:28'),
(18, 4, 13, 'follow', NULL, NULL, 0, '2025-10-22 21:19:30'),
(19, 4, 13, 'follow', NULL, NULL, 0, '2025-10-22 21:19:32'),
(20, 7, 13, 'follow', NULL, NULL, 0, '2025-10-22 21:19:33'),
(21, 8, 13, 'follow', NULL, NULL, 0, '2025-10-22 21:19:33'),
(22, 1, 13, 'follow', NULL, NULL, 0, '2025-10-22 21:19:34'),
(23, 12, 13, 'follow', NULL, NULL, 0, '2025-10-22 21:19:35'),
(24, 3, 13, 'follow', NULL, NULL, 0, '2025-10-22 21:19:56'),
(26, 13, 3, 'follow', NULL, NULL, 0, '2025-10-22 21:21:16'),
(27, 3, 13, 'follow', NULL, NULL, 0, '2025-10-22 21:41:23'),
(29, 13, 3, 'follow', NULL, NULL, 0, '2025-10-22 21:41:50'),
(30, 13, 3, 'like', 8, NULL, 0, '2025-10-22 21:41:54'),
(31, 13, 3, 'like', 8, NULL, 0, '2025-10-22 21:41:59'),
(32, 13, 3, 'follow', NULL, NULL, 0, '2025-10-22 22:21:16'),
(33, 13, 3, 'follow', NULL, NULL, 0, '2025-10-22 22:21:19'),
(34, 13, 3, 'follow', NULL, NULL, 0, '2025-10-22 22:30:51'),
(35, 13, 3, 'follow', NULL, NULL, 0, '2025-10-22 22:30:53'),
(36, 13, 3, 'follow', NULL, NULL, 0, '2025-10-22 22:37:06'),
(37, 13, 3, 'follow', NULL, NULL, 0, '2025-10-22 22:37:11'),
(39, 13, 3, 'follow', NULL, NULL, 0, '2025-10-23 12:35:12'),
(40, 13, 3, 'follow', NULL, NULL, 0, '2025-10-23 12:35:14'),
(41, 13, 3, 'follow', NULL, NULL, 0, '2025-10-23 12:35:16'),
(42, 13, 3, 'follow', NULL, NULL, 0, '2025-10-23 12:35:18'),
(43, 13, 3, 'follow', NULL, NULL, 0, '2025-10-23 12:35:20'),
(44, 14, 3, 'follow', NULL, NULL, 0, '2025-10-23 12:37:23'),
(45, 14, 3, 'like', 10, NULL, 0, '2025-10-23 12:37:47'),
(46, 13, 3, 'follow', NULL, NULL, 0, '2025-10-23 14:57:53'),
(47, 14, 3, 'like', 10, NULL, 0, '2025-10-28 11:45:06'),
(48, 3, 15, 'like', 12, NULL, 0, '2025-10-28 12:01:17'),
(49, 14, 15, 'like', 10, NULL, 0, '2025-10-28 12:01:28'),
(50, 13, 15, 'like', 8, NULL, 0, '2025-10-28 12:01:29'),
(51, 13, 15, 'like', 8, NULL, 0, '2025-10-28 12:01:35'),
(52, 14, 3, 'follow', NULL, NULL, 0, '2025-11-04 09:42:07'),
(53, 14, 3, 'like', 10, NULL, 0, '2025-11-04 09:42:11');

-- --------------------------------------------------------

--
-- Struktura tabeli dla tabeli `posts`
--

CREATE TABLE `posts` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `content` text NOT NULL,
  `image_url` varchar(255) DEFAULT NULL,
  `likes_count` int(11) DEFAULT 0,
  `reposts_count` int(11) DEFAULT 0,
  `replies_count` int(11) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `posts`
--

INSERT INTO `posts` (`id`, `user_id`, `content`, `image_url`, `likes_count`, `reposts_count`, `replies_count`, `created_at`, `updated_at`) VALUES
(8, 13, 'Przykładowy post nr.3.', NULL, 3, 0, 2, '2025-10-22 21:38:00', '2025-10-28 12:01:35'),
(10, 14, 'Przykładowy post nr.2.', NULL, 2, 0, 1, '2025-10-23 12:37:02', '2025-11-04 09:42:11'),
(12, 3, 'Przykładowy post nr.1.', NULL, 1, 0, 1, '2025-10-23 15:14:25', '2025-11-04 09:40:02');

-- --------------------------------------------------------

--
-- Struktura tabeli dla tabeli `replies`
--

CREATE TABLE `replies` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `post_id` int(11) NOT NULL,
  `parent_reply_id` int(11) DEFAULT NULL,
  `content` text NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `replies`
--

INSERT INTO `replies` (`id`, `user_id`, `post_id`, `parent_reply_id`, `content`, `created_at`) VALUES
(3, 13, 8, NULL, 'Podoba mi się również!', '2025-10-22 23:13:42'),
(4, 13, 8, NULL, 'Podzielcie się swoją opinią!', '2025-10-22 23:18:00'),
(6, 3, 10, NULL, 'Siemaaaankoo!', '2025-10-23 12:37:37'),
(7, 3, 12, NULL, 'Komentarz nr.1', '2025-11-04 09:40:02');

--
-- Wyzwalacze `replies`
--
DELIMITER $$
CREATE TRIGGER `after_reply_delete` AFTER DELETE ON `replies` FOR EACH ROW BEGIN
    UPDATE posts 
    SET replies_count = GREATEST(0, replies_count - 1) 
    WHERE id = OLD.post_id;
END
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `after_reply_insert` AFTER INSERT ON `replies` FOR EACH ROW BEGIN
    UPDATE posts 
    SET replies_count = replies_count + 1 
    WHERE id = NEW.post_id;
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Struktura tabeli dla tabeli `reposts`
--

CREATE TABLE `reposts` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `post_id` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Struktura tabeli dla tabeli `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `full_name` varchar(100) DEFAULT NULL,
  `bio` text DEFAULT NULL,
  `location` varchar(30) DEFAULT NULL,
  `website` varchar(100) DEFAULT NULL,
  `profile_image` varchar(255) DEFAULT 'images/default-avatar.png',
  `banner_image` varchar(255) DEFAULT NULL,
  `verified` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `email`, `password_hash`, `full_name`, `bio`, `location`, `website`, `profile_image`, `banner_image`, `verified`, `created_at`, `updated_at`) VALUES
(1, 'jankowalski', 'jan@example.com', '$2y$10$oiO4PlEnJAz92LnTr3pZ4OzhdSTbs27ma1DZ4dA9ybRElEYB4Vuia', 'Jan Kowalski', 'Programista | Pasjonat technologii 💻 | Miłośnik kawy ☕ | Piszę o #JavaScript #React #WebDev', NULL, NULL, 'images/default-avatar.png', NULL, 1, '2025-10-22 18:21:19', '2025-10-22 18:58:01'),
(3, 'Zawas', 'zawasdj@onet.pl', '$2y$10$bQCQMTQZ7FiZbrQG1Hr0oO4ZnXumawK4LvmSUS/DVBxSdLmQY1VKW', 'Szymeon Zawadzki', '29976', 'Szczecin', 'https://djzawas.pl', 'images/default-avatar.png', NULL, 0, '2025-10-22 18:53:27', '2025-10-23 15:24:28'),
(4, 'annanowak', 'anna@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Anna Nowak', 'UX Designer 🎨 | Miłośniczka minimalizmu | Projektowanie z pasją ✨', NULL, NULL, 'images/default-avatar.png', NULL, 1, '2025-10-22 19:05:34', '2025-10-22 19:05:34'),
(5, 'piotrwisniewski', 'piotr@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Piotr Wiśniewski', 'Marketing Manager 📊 | Social Media Expert | Kocham kawę i marketing! ☕', NULL, NULL, 'images/default-avatar.png', NULL, 0, '2025-10-22 19:05:34', '2025-10-22 19:05:34'),
(6, 'karolinamazur', 'karolina@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Karolina Mazur', 'Content Creator 📝 | Fotografka amatorka 📸 | Podróże i lifestyle 🌍', NULL, NULL, 'images/default-avatar.png', NULL, 1, '2025-10-22 19:05:34', '2025-10-22 19:05:34'),
(7, 'tomaszkrawczyk', 'tomasz@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Tomasz Krawczyk', 'DevOps Engineer 🔧 | Open Source Contributor | Linux & Cloud ☁️', NULL, NULL, 'images/default-avatar.png', NULL, 0, '2025-10-22 19:05:34', '2025-10-22 19:05:34'),
(8, 'magdalenazajac', 'magdalena@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Magdalena Zając', 'Data Scientist 📊 | AI & Machine Learning | Python enthusiast 🐍', NULL, NULL, 'images/default-avatar.png', NULL, 1, '2025-10-22 19:05:34', '2025-10-22 19:05:34'),
(9, 'michallewandowski', 'michal@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Michał Lewandowski', 'Mobile Developer 📱 | React Native & Flutter | Tworzę aplikacje mobilne 🚀', NULL, NULL, 'images/default-avatar.png', NULL, 0, '2025-10-22 19:05:34', '2025-10-22 19:05:34'),
(10, 'nataliadabrowska', 'natalia@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Natalia Dąbrowska', 'Product Manager 💼 | Agile & Scrum | Innowacje w tech 💡', NULL, NULL, 'images/default-avatar.png', NULL, 1, '2025-10-22 19:05:34', '2025-10-22 19:05:34'),
(11, 'jakubwozniak', 'jakub@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Jakub Woźniak', 'Cybersecurity Specialist 🔐 | Ethical Hacker | Bezpieczeństwo IT 🛡️', NULL, NULL, 'images/default-avatar.png', NULL, 0, '2025-10-22 19:05:34', '2025-10-22 19:05:34'),
(12, 'zuzannakaminska', 'zuzanna@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Zuzanna Kamińska', 'Graphic Designer 🎨 | Brand Identity | Tworzę wizualne historie ✨', NULL, NULL, 'images/default-avatar.png', NULL, 1, '2025-10-22 19:05:34', '2025-10-22 19:05:34'),
(13, 'Lukas', 'lukas@onet.pl', '$2y$10$NYyDPhx/XeVlhX/.8X.JqeN2He6kj94cjQ985tCu5OvqhYKT00bt.', 'Łukasz Żurowski', NULL, NULL, NULL, 'images/default-avatar.png', NULL, 0, '2025-10-22 20:26:12', '2025-10-22 20:26:12'),
(14, 'Jaszczurka', 'jaszczur@example.com', '$2y$10$jPNs0N.YjrD1G1X0288Km.jLPBwNMdAwZpO0dREojCvNIXIGi3Xcq', 'Patryk Janczura', NULL, NULL, NULL, 'images/default-avatar.png', NULL, 0, '2025-10-23 12:36:49', '2025-10-23 12:36:49'),
(15, 'Jan', 'jankowalski@gmail.com', '$2y$10$Mf2nctOml.hoPoti6vkIEeXm.pRWmkhntgPye67in1lVVyN0WoiHS', 'Jan Kowalski', NULL, NULL, NULL, 'images/default-avatar.png', NULL, 0, '2025-10-28 11:58:23', '2025-10-28 11:58:23');

--
-- Indeksy dla zrzutów tabel
--

--
-- Indeksy dla tabeli `follows`
--
ALTER TABLE `follows`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_follow` (`follower_id`,`following_id`),
  ADD KEY `idx_follower_id` (`follower_id`),
  ADD KEY `idx_following_id` (`following_id`);

--
-- Indeksy dla tabeli `likes`
--
ALTER TABLE `likes`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_like` (`user_id`,`post_id`),
  ADD KEY `idx_user_id` (`user_id`),
  ADD KEY `idx_post_id` (`post_id`);

--
-- Indeksy dla tabeli `notifications`
--
ALTER TABLE `notifications`
  ADD PRIMARY KEY (`id`),
  ADD KEY `actor_id` (`actor_id`),
  ADD KEY `post_id` (`post_id`),
  ADD KEY `reply_id` (`reply_id`),
  ADD KEY `idx_user_id` (`user_id`),
  ADD KEY `idx_created_at` (`created_at`),
  ADD KEY `idx_is_read` (`is_read`);

--
-- Indeksy dla tabeli `posts`
--
ALTER TABLE `posts`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_user_id` (`user_id`),
  ADD KEY `idx_created_at` (`created_at`);

--
-- Indeksy dla tabeli `replies`
--
ALTER TABLE `replies`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_user_id` (`user_id`),
  ADD KEY `idx_post_id` (`post_id`),
  ADD KEY `idx_parent_reply_id` (`parent_reply_id`);

--
-- Indeksy dla tabeli `reposts`
--
ALTER TABLE `reposts`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_repost` (`user_id`,`post_id`),
  ADD KEY `idx_user_id` (`user_id`),
  ADD KEY `idx_post_id` (`post_id`);

--
-- Indeksy dla tabeli `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `idx_username` (`username`),
  ADD KEY `idx_email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `follows`
--
ALTER TABLE `follows`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=47;

--
-- AUTO_INCREMENT for table `likes`
--
ALTER TABLE `likes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=159;

--
-- AUTO_INCREMENT for table `notifications`
--
ALTER TABLE `notifications`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=54;

--
-- AUTO_INCREMENT for table `posts`
--
ALTER TABLE `posts`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `replies`
--
ALTER TABLE `replies`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `reposts`
--
ALTER TABLE `reposts`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `follows`
--
ALTER TABLE `follows`
  ADD CONSTRAINT `follows_ibfk_1` FOREIGN KEY (`follower_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `follows_ibfk_2` FOREIGN KEY (`following_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `likes`
--
ALTER TABLE `likes`
  ADD CONSTRAINT `likes_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `likes_ibfk_2` FOREIGN KEY (`post_id`) REFERENCES `posts` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `notifications`
--
ALTER TABLE `notifications`
  ADD CONSTRAINT `notifications_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `notifications_ibfk_2` FOREIGN KEY (`actor_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `notifications_ibfk_3` FOREIGN KEY (`post_id`) REFERENCES `posts` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `notifications_ibfk_4` FOREIGN KEY (`reply_id`) REFERENCES `replies` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `posts`
--
ALTER TABLE `posts`
  ADD CONSTRAINT `posts_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `replies`
--
ALTER TABLE `replies`
  ADD CONSTRAINT `replies_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `replies_ibfk_2` FOREIGN KEY (`post_id`) REFERENCES `posts` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `replies_ibfk_3` FOREIGN KEY (`parent_reply_id`) REFERENCES `replies` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `reposts`
--
ALTER TABLE `reposts`
  ADD CONSTRAINT `reposts_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `reposts_ibfk_2` FOREIGN KEY (`post_id`) REFERENCES `posts` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
