-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Nov 27, 2025 at 04:18 AM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `test_login`
--

-- --------------------------------------------------------

--
-- Table structure for table `artworks`
--

CREATE TABLE `artworks` (
  `id_artwork` int(11) NOT NULL,
  `id_user` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `price` decimal(10,2) NOT NULL DEFAULT 0.00,
  `status` enum('draft','published','sold') DEFAULT 'draft',
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `artworks`
--

INSERT INTO `artworks` (`id_artwork`, `id_user`, `title`, `description`, `price`, `status`, `created_at`, `updated_at`) VALUES
(20, 2, 'Miku Art', 'Miku Miku Beam', 300000.00, 'draft', '2025-11-26 09:07:19', '2025-11-26 15:07:19'),
(21, 3, 'Firework', 'Boom Boom Bakudan', 125000.00, 'draft', '2025-11-26 09:09:36', '2025-11-26 15:09:36');

-- --------------------------------------------------------

--
-- Table structure for table `artwork_images`
--

CREATE TABLE `artwork_images` (
  `id_image` int(11) NOT NULL,
  `id_artwork` int(11) NOT NULL,
  `image_url` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `artwork_images`
--

INSERT INTO `artwork_images` (`id_image`, `id_artwork`, `image_url`) VALUES
(22, 20, '2a2d46190247e1c2aaa1658f818d650e.jpg'),
(23, 20, 'f021f4ba196c37db0992b7708401f899.jpg'),
(24, 21, '34fc3e4865b89d990d4bfa43f2ee14e3.jpg');

-- --------------------------------------------------------

--
-- Table structure for table `artwork_tags`
--

CREATE TABLE `artwork_tags` (
  `id_tag` int(11) NOT NULL,
  `tag_name` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `artwork_tags`
--

INSERT INTO `artwork_tags` (`id_tag`, `tag_name`) VALUES
(1, 'hatsunemiku'),
(2, 'anime'),
(3, 'klee'),
(4, 'genshin'),
(5, 'asd'),
(6, 'herta'),
(7, 'honkai'),
(8, 'honkaistarrail'),
(9, 'weqwe'),
(10, 'ertertet'),
(11, 'vocaloid'),
(12, 'prosekai'),
(13, 'genshinimpact');

-- --------------------------------------------------------

--
-- Table structure for table `artwork_tag_map`
--

CREATE TABLE `artwork_tag_map` (
  `id_artwork` int(11) NOT NULL,
  `id_tag` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `artwork_tag_map`
--

INSERT INTO `artwork_tag_map` (`id_artwork`, `id_tag`) VALUES
(20, 1),
(20, 11),
(20, 12),
(21, 3),
(21, 13);

-- --------------------------------------------------------

--
-- Table structure for table `comments`
--

CREATE TABLE `comments` (
  `id_comment` int(11) NOT NULL,
  `id_artwork` int(11) NOT NULL,
  `id_user` int(11) NOT NULL,
  `comment_text` text NOT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `favorites`
--

CREATE TABLE `favorites` (
  `id_user` int(11) NOT NULL,
  `id_artwork` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `orders`
--

CREATE TABLE `orders` (
  `id_order` int(11) NOT NULL,
  `id_buyer` int(11) NOT NULL,
  `total_price` decimal(10,2) NOT NULL,
  `payment_status` enum('pending','paid','cancelled') DEFAULT 'pending',
  `order_status` enum('waiting','processing','completed','cancelled') DEFAULT 'waiting',
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `order_items`
--

CREATE TABLE `order_items` (
  `id_order_item` int(11) NOT NULL,
  `id_order` int(11) NOT NULL,
  `id_artwork` int(11) NOT NULL,
  `price` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id_user` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `email` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `name` varchar(16) NOT NULL,
  `bio` text DEFAULT NULL,
  `avatar` varchar(255) DEFAULT NULL,
  `token_login` text NOT NULL,
  `last_login` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `role` enum('admin','creator','user') DEFAULT 'user'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id_user`, `username`, `email`, `password`, `name`, `bio`, `avatar`, `token_login`, `last_login`, `updated_at`, `created_at`, `role`) VALUES
(1, 'yay', 'admin@example.com', '$2y$10$tOY/WWSRUKp6xD9dbH2yr.fp5Ryy9XJ5LT85aPoMawgPiyAwUvjPi', 'Aku Admin', 'pe by1', 'http://192.168.6.15/project_api/uploads/avatar/avatar_1_1763023639.png', 'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJmbHlzdHVkaW9fYXBpIiwiYXVkIjoiZmx1dHRlcl9jbGllbnQiLCJpYXQiOjE3NjQyMTMyNzksImV4cCI6MTc2NDI5OTY3OSwianRpIjoiZTNkYTgwYTE4Nzk5YWFlZmU3N2JmZWRmOWY0Yzk2ZWYiLCJkYXRhIjp7ImlkX3VzZXIiOiIxIiwidXNlcm5hbWUiOiJ5YXkiLCJlbWFpbCI6ImFkbWluQGV4YW1wbGUuY29tIiwicm9sZSI6ImFkbWluIn19.79_11z2AgMXY3-UJBjd_QSXoHFxKu5WpwAL7zhYj51U', NULL, '2025-11-13 02:31:50', NULL, 'admin'),
(2, 'dhimas', 'dhimas@example.com', '$2y$10$tOY/WWSRUKp6xD9dbH2yr.fp5Ryy9XJ5LT85aPoMawgPiyAwUvjPi', 'Dhimas', 'UwU\nOwO\n>_<', 'http://192.168.6.15/project_api/uploads/avatar/avatar_2_1763001737.png', 'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJmbHlzdHVkaW9fYXBpIiwiYXVkIjoiZmx1dHRlcl9jbGllbnQiLCJpYXQiOjE3NjQyMTI1MzMsImV4cCI6MTc2NDI5ODkzMywianRpIjoiNTJkNzMwYzAxZGIwNjI3NzhhNGM5OWFiNzM3NzUwYWIiLCJkYXRhIjp7ImlkX3VzZXIiOiIyIiwidXNlcm5hbWUiOiJkaGltYXMiLCJlbWFpbCI6ImRoaW1hc0BleGFtcGxlLmNvbSIsInJvbGUiOiJjcmVhdG9yIn19.PszZdsLJjgCWhwyZKpyBonCG2_Wto5sVT0tX8l_RA_o', NULL, '2025-11-07 08:39:23', NULL, 'creator'),
(3, 'tasya', 'tasya@example.com', '$2y$10$tMe8ZgfXslCn1RjNxNxE3O2VifvETIHcbshcaUDFjeS8ZRH.sdnD6', 'Tasya', NULL, 'http://192.168.6.15/project_api/uploads/avatar/avatar_3_1764032743.png', 'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJmbHlzdHVkaW9fYXBpIiwiYXVkIjoiZmx1dHRlcl9jbGllbnQiLCJpYXQiOjE3NjQyMTI1NjMsImV4cCI6MTc2NDI5ODk2MywianRpIjoiNjEyYmQ5ZjIzNWM2NDdhN2Q3YmI4M2E3NTJmZjUyNWMiLCJkYXRhIjp7ImlkX3VzZXIiOiIzIiwidXNlcm5hbWUiOiJ0YXN5YSIsImVtYWlsIjoidGFzeWFAZXhhbXBsZS5jb20iLCJyb2xlIjoiY3JlYXRvciJ9fQ.Vqj6SGbkWAbzs_d7iiwRaoe3RnC8yCDbZSRauatjKMM', NULL, NULL, NULL, 'creator'),
(5, 'hafizh', 'hafizh@example.com', '$2y$10$UJMmeFw.1WgHpZfviSFZF.1WDy/pr16ZUcmC2S7OorLDbEI486yaS', '', NULL, NULL, 'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJmbHlzdHVkaW9fYXBpIiwiYXVkIjoiZmx1dHRlcl9jbGllbnQiLCJpYXQiOjE3NjMwOTQ3MzksImV4cCI6MTc2MzE4MTEzOSwianRpIjoiNDMzOTBlY2VkN2U3NjM4MDk4ZDQzOWNkM2U2YTQ5OWUiLCJkYXRhIjp7ImlkX3VzZXIiOiI1IiwidXNlcm5hbWUiOiJoYWZpemgiLCJlbWFpbCI6ImhhZml6aEBleGFtcGxlLmNvbSIsInJvbGUiOiJ1c2VyIn19.Ke_HzaqoVWzsR8kCFB5WEgMunPP8Xlj_D7lFPvscmhc', NULL, NULL, '2025-10-31 09:18:41', 'user');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `artworks`
--
ALTER TABLE `artworks`
  ADD PRIMARY KEY (`id_artwork`),
  ADD KEY `id_user` (`id_user`);

--
-- Indexes for table `artwork_images`
--
ALTER TABLE `artwork_images`
  ADD PRIMARY KEY (`id_image`),
  ADD KEY `id_artwork` (`id_artwork`);

--
-- Indexes for table `artwork_tags`
--
ALTER TABLE `artwork_tags`
  ADD PRIMARY KEY (`id_tag`);

--
-- Indexes for table `artwork_tag_map`
--
ALTER TABLE `artwork_tag_map`
  ADD PRIMARY KEY (`id_artwork`,`id_tag`),
  ADD KEY `id_tag` (`id_tag`);

--
-- Indexes for table `comments`
--
ALTER TABLE `comments`
  ADD PRIMARY KEY (`id_comment`),
  ADD KEY `id_artwork` (`id_artwork`),
  ADD KEY `id_user` (`id_user`);

--
-- Indexes for table `favorites`
--
ALTER TABLE `favorites`
  ADD PRIMARY KEY (`id_user`,`id_artwork`),
  ADD KEY `id_artwork` (`id_artwork`);

--
-- Indexes for table `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`id_order`),
  ADD KEY `id_buyer` (`id_buyer`);

--
-- Indexes for table `order_items`
--
ALTER TABLE `order_items`
  ADD PRIMARY KEY (`id_order_item`),
  ADD KEY `id_order` (`id_order`),
  ADD KEY `id_artwork` (`id_artwork`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id_user`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `artworks`
--
ALTER TABLE `artworks`
  MODIFY `id_artwork` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=22;

--
-- AUTO_INCREMENT for table `artwork_images`
--
ALTER TABLE `artwork_images`
  MODIFY `id_image` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=25;

--
-- AUTO_INCREMENT for table `artwork_tags`
--
ALTER TABLE `artwork_tags`
  MODIFY `id_tag` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT for table `comments`
--
ALTER TABLE `comments`
  MODIFY `id_comment` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `orders`
--
ALTER TABLE `orders`
  MODIFY `id_order` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `order_items`
--
ALTER TABLE `order_items`
  MODIFY `id_order_item` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id_user` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `artworks`
--
ALTER TABLE `artworks`
  ADD CONSTRAINT `artworks_ibfk_1` FOREIGN KEY (`id_user`) REFERENCES `users` (`id_user`) ON DELETE CASCADE;

--
-- Constraints for table `artwork_images`
--
ALTER TABLE `artwork_images`
  ADD CONSTRAINT `artwork_images_ibfk_1` FOREIGN KEY (`id_artwork`) REFERENCES `artworks` (`id_artwork`) ON DELETE CASCADE;

--
-- Constraints for table `artwork_tag_map`
--
ALTER TABLE `artwork_tag_map`
  ADD CONSTRAINT `artwork_tag_map_ibfk_1` FOREIGN KEY (`id_artwork`) REFERENCES `artworks` (`id_artwork`) ON DELETE CASCADE,
  ADD CONSTRAINT `artwork_tag_map_ibfk_2` FOREIGN KEY (`id_tag`) REFERENCES `artwork_tags` (`id_tag`) ON DELETE CASCADE;

--
-- Constraints for table `comments`
--
ALTER TABLE `comments`
  ADD CONSTRAINT `comments_ibfk_1` FOREIGN KEY (`id_artwork`) REFERENCES `artworks` (`id_artwork`) ON DELETE CASCADE,
  ADD CONSTRAINT `comments_ibfk_2` FOREIGN KEY (`id_user`) REFERENCES `users` (`id_user`) ON DELETE CASCADE;

--
-- Constraints for table `favorites`
--
ALTER TABLE `favorites`
  ADD CONSTRAINT `favorites_ibfk_1` FOREIGN KEY (`id_user`) REFERENCES `users` (`id_user`) ON DELETE CASCADE,
  ADD CONSTRAINT `favorites_ibfk_2` FOREIGN KEY (`id_artwork`) REFERENCES `artworks` (`id_artwork`) ON DELETE CASCADE;

--
-- Constraints for table `orders`
--
ALTER TABLE `orders`
  ADD CONSTRAINT `orders_ibfk_1` FOREIGN KEY (`id_buyer`) REFERENCES `users` (`id_user`) ON DELETE CASCADE;

--
-- Constraints for table `order_items`
--
ALTER TABLE `order_items`
  ADD CONSTRAINT `order_items_ibfk_1` FOREIGN KEY (`id_order`) REFERENCES `orders` (`id_order`) ON DELETE CASCADE,
  ADD CONSTRAINT `order_items_ibfk_2` FOREIGN KEY (`id_artwork`) REFERENCES `artworks` (`id_artwork`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
