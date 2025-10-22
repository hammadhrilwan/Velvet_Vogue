-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Oct 12, 2025 at 07:58 PM
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
-- Database: `velvet_vogue`
--

-- --------------------------------------------------------

--
-- Table structure for table `addresses`
--

CREATE TABLE `addresses` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `type` enum('shipping','billing') NOT NULL,
  `first_name` varchar(50) NOT NULL,
  `last_name` varchar(50) NOT NULL,
  `company` varchar(100) DEFAULT NULL,
  `address_line_1` varchar(255) NOT NULL,
  `address_line_2` varchar(255) DEFAULT NULL,
  `city` varchar(100) NOT NULL,
  `state` varchar(100) NOT NULL,
  `postal_code` varchar(20) NOT NULL,
  `country` varchar(100) NOT NULL DEFAULT 'United States',
  `phone` varchar(20) DEFAULT NULL,
  `is_default` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `admin`
--

CREATE TABLE `admin` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `full_name` varchar(100) NOT NULL,
  `role` varchar(20) DEFAULT 'admin',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `admin`
--

INSERT INTO `admin` (`id`, `username`, `email`, `password`, `full_name`, `role`, `created_at`, `updated_at`) VALUES
(1, 'admin', 'admin@velvetvogue.com', '$2y$10$mK/6l0aDwvru3SOJCapgmumdXiHENjck4qWrHCZyFmTwdsyYsDWR6', 'Admin User', 'admin', '2025-10-11 15:31:46', '2025-10-11 16:23:56'),
(3, 'superadmin', 'superadmin@velvetvogue.com', '$2y$10$D/qy8NOB7xWjh0DLUOmyt.FDZ.CTfmX6OicM95N3ukhND4gdrakLO', 'Super Administrator', 'super_admin', '2025-10-11 16:23:56', '2025-10-11 16:23:56');

-- --------------------------------------------------------

--
-- Table structure for table `cart_items`
--

CREATE TABLE `cart_items` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `session_id` varchar(255) DEFAULT NULL,
  `product_id` int(11) NOT NULL,
  `quantity` int(11) NOT NULL DEFAULT 1,
  `size` varchar(10) DEFAULT NULL,
  `color` varchar(50) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `categories`
--

CREATE TABLE `categories` (
  `id` int(11) NOT NULL,
  `name` varchar(50) NOT NULL,
  `description` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `categories`
--

INSERT INTO `categories` (`id`, `name`, `description`, `created_at`) VALUES
(1, 'casual', 'Comfortable everyday wear for relaxed occasions', '2025-10-11 10:50:46'),
(2, 'formal', 'Elegant attire for professional and special occasions', '2025-10-11 10:50:46'),
(3, 'accessories', 'Fashion accessories to complete your look', '2025-10-11 10:50:46'),
(4, 'footwear', 'Stylish and comfortable shoes for every occasion', '2025-10-11 10:50:46'),
(5, 'T-Shirts', 'Comfortable t-shirts for everyday wear', '2025-10-11 11:57:25'),
(6, 'Dresses', 'Elegant dresses for special occasions', '2025-10-11 11:57:25'),
(7, 'Jeans', 'Classic denim jeans and pants', '2025-10-11 11:57:25'),
(8, 'Jackets', 'Stylish jackets and outerwear', '2025-10-11 11:57:25'),
(9, 'Shoes', 'Comfortable and fashionable footwear', '2025-10-11 11:57:25'),
(41, 'Clothing', 'Apparel and clothing items', '2025-10-11 13:08:27'),
(44, 'Bags', 'Handbags and bags', '2025-10-11 13:08:27');

-- --------------------------------------------------------

--
-- Table structure for table `contact_messages`
--

CREATE TABLE `contact_messages` (
  `id` int(11) NOT NULL,
  `first_name` varchar(50) NOT NULL,
  `last_name` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `inquiry_type` varchar(50) DEFAULT NULL,
  `order_number` varchar(20) DEFAULT NULL,
  `subject` varchar(255) NOT NULL,
  `message` text NOT NULL,
  `status` enum('new','in_progress','resolved') DEFAULT 'new',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `admin_response` text DEFAULT NULL,
  `responded_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `orders`
--

CREATE TABLE `orders` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `order_number` varchar(20) NOT NULL,
  `total_amount` decimal(10,2) NOT NULL,
  `status` enum('pending','processing','shipped','delivered','cancelled') DEFAULT 'pending',
  `shipping_address` text DEFAULT NULL,
  `billing_address` text DEFAULT NULL,
  `payment_method` varchar(50) DEFAULT NULL,
  `payment_status` enum('pending','paid','failed','refunded') DEFAULT 'pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `order_items`
--

CREATE TABLE `order_items` (
  `id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `quantity` int(11) NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `size` varchar(10) DEFAULT NULL,
  `color` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `products`
--

CREATE TABLE `products` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `price` decimal(10,2) NOT NULL,
  `category_id` int(11) DEFAULT NULL,
  `stock_quantity` int(11) DEFAULT 0,
  `sizes` text DEFAULT NULL,
  `colors` text DEFAULT NULL,
  `gender` enum('Men','Women','Unisex','Kids') DEFAULT 'Unisex',
  `image_url` varchar(500) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `products`
--

INSERT INTO `products` (`id`, `name`, `description`, `price`, `category_id`, `stock_quantity`, `sizes`, `colors`, `gender`, `image_url`, `created_at`) VALUES
(1, 'Classic White Shirt', 'Timeless white button-up shirt made from premium cotton. Perfect for both professional and casual settings.', 89.99, 2, 50, '[', '[', 'Men', 'images/products/casual-shirt.jpg', '2025-10-11 10:50:46'),
(2, 'Casual Denim Jacket', 'Versatile denim jacket with modern fit. Essential wardrobe piece for layering and style.', 129.99, 1, 30, '[', '[', 'Men', 'images/products/denim-jacket.jpg', '2025-10-11 10:50:46'),
(6, 'Business Suit Set', 'Professional two-piece suit perfect for business meetings and formal occasions.', 299.99, 2, 20, '[\"28\", \"30\", \"32\", \"34\", \"36\"]', '[\"Blue\", \"Black\", \"Dark Blue\"]', 'Unisex', 'images/products/white-tshirt.jpg', '2025-10-11 10:50:46'),
(7, 'Summer Midi Skirt', 'Light and breezy midi skirt perfect for summer days. Comfortable and stylish.', 69.99, 1, 45, '[\"S\", \"M\", \"L\", \"XL\"]', '[\"White\", \"Black\", \"Gray\"]', 'Women', 'images/products/white-tshirt.jpg', '2025-10-11 10:50:46'),
(8, 'Casual Sneakers', 'Comfortable and stylish sneakers for everyday wear. Perfect blend of comfort and style.', 89.99, 4, 60, '[\"S\", \"M\", \"L\", \"XL\"]', '[\"Black\", \"Brown\", \"Navy\"]', 'Unisex', 'images/products/sneakers.jpg', '2025-10-11 10:50:46'),
(10, 'Elegant Summer Dress', 'Flowing summer dress perfect for warm weather and special occasions.', 89.99, 2, 30, '[\"28\", \"30\", \"32\", \"34\", \"36\"]', '[\"Blue\", \"Black\", \"Dark Blue\"]', 'Women', 'images/products/summer-dress.jpg', '2025-10-11 11:01:34'),
(15, 'Classic Cotton T-Shirt', 'Comfortable cotton t-shirt perfect for everyday wear', 29.99, 1, 50, '[\"S\", \"M\", \"L\", \"XL\"]', '[\"White\", \"Black\", \"Gray\"]', 'Unisex', 'images/products/white-tshirt.jpg', '2025-10-11 13:08:27'),
(16, 'Designer Jeans', 'Premium denim jeans with modern fit', 89.99, 1, 30, '[\"S\", \"M\", \"L\", \"XL\"]', '[\"White\", \"Black\", \"Gray\"]', 'Unisex', 'images/products/slim-jeans.jpg', '2025-10-11 13:08:27'),
(19, 'Sports Shoes', 'Comfortable athletic shoes for active lifestyle', 129.99, 3, 40, '[\"XS\", \"S\", \"M\", \"L\", \"XL\"]', '[\"Black\", \"Navy\", \"Red\"]', 'Unisex', 'images/products/sneakers.jpg', '2025-10-11 13:08:27'),
(20, 'Classic Cotton T-Shirt', 'Comfortable cotton t-shirt perfect for everyday wear', 29.99, 1, 50, '[\"S\", \"M\", \"L\", \"XL\"]', '[\"White\", \"Black\", \"Gray\"]', 'Unisex', 'images/products/white-tshirt.jpg', '2025-10-11 13:15:35'),
(21, 'Designer Jeans', 'Premium denim jeans with modern fit', 89.99, 1, 30, '[', '[', 'Men', 'images/products/slim-jeans.jpg', '2025-10-11 13:15:35'),
(22, 'Elegant Dress', 'Beautiful dress for special occasions', 159.99, 1, 25, '[\"S\", \"M\", \"L\", \"XL\"]', '[\"White\", \"Black\", \"Gray\"]', 'Women', 'images/products/black-dress.jpg', '2025-10-11 13:15:35'),
(23, 'Leather Jacket', 'Stylish leather jacket for a bold look', 299.99, 1, 15, '[\"S\", \"M\", \"L\", \"XL\"]', '[\"White\", \"Black\", \"Gray\"]', 'Unisex', 'images/products/leather-jacket.jpg', '2025-10-11 13:15:36'),
(24, 'Sports Shoes', 'Comfortable athletic shoes for active lifestyle', 129.99, 3, 40, '[\"XS\", \"S\", \"M\", \"L\", \"XL\"]', '[\"Black\", \"Navy\", \"Red\"]', 'Unisex', 'images/products/sneakers.jpg', '2025-10-11 13:15:36'),
(27, 'Heidi High Rise Wide Leg Jeans', 'Heidi is the hip-hugging jean that gives you legs for days. It sits right at your waist and has a wide leg from the thigh down to the hem for a lengthening look. Made from our comfort denim, it gives a small amount of stretch right where you need it.', 60.00, 7, 35, 'S, M, L, XL', 'Blue', 'Women', 'images/products/heidi-jean.jpg', '2025-10-11 21:25:21');

-- --------------------------------------------------------

--
-- Table structure for table `product_colors`
--

CREATE TABLE `product_colors` (
  `id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `color` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `product_colors`
--

INSERT INTO `product_colors` (`id`, `product_id`, `color`) VALUES
(2, 1, 'Light Blue'),
(3, 1, 'Pink'),
(1, 1, 'White'),
(5, 2, 'Black'),
(4, 2, 'Blue'),
(6, 2, 'Gray'),
(18, 6, 'Black'),
(17, 6, 'Charcoal'),
(16, 6, 'Navy'),
(19, 7, 'Floral'),
(20, 7, 'Solid Blue'),
(21, 7, 'White'),
(23, 8, 'Black'),
(24, 8, 'Navy'),
(22, 8, 'White'),
(30, 10, 'Floral'),
(28, 10, 'Navy'),
(29, 10, 'Red'),
(37, 15, 'Black'),
(39, 15, 'Blue'),
(40, 15, 'Red'),
(38, 15, 'White'),
(41, 16, 'Black'),
(43, 16, 'Blue'),
(44, 16, 'Red'),
(42, 16, 'White'),
(53, 19, 'Black'),
(55, 19, 'Blue'),
(56, 19, 'Red'),
(54, 19, 'White'),
(57, 20, 'Black'),
(59, 20, 'Blue'),
(60, 20, 'Red'),
(58, 20, 'White'),
(61, 21, 'Black'),
(63, 21, 'Blue'),
(64, 21, 'Red'),
(62, 21, 'White'),
(65, 22, 'Black'),
(67, 22, 'Blue'),
(68, 22, 'Red'),
(66, 22, 'White'),
(69, 23, 'Black'),
(71, 23, 'Blue'),
(72, 23, 'Red'),
(70, 23, 'White'),
(73, 24, 'Black'),
(75, 24, 'Blue'),
(76, 24, 'Red'),
(74, 24, 'White');

-- --------------------------------------------------------

-- --------------------------------------------------------

--
-- Table structure for table `product_images`
--

CREATE TABLE `product_images` (
  `id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `image_url` varchar(500) NOT NULL,
  `alt_text` varchar(255) DEFAULT NULL,
  `is_primary` tinyint(1) DEFAULT 0,
  `sort_order` int(11) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `product_sizes`
--

CREATE TABLE `product_sizes` (
  `id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `size` varchar(10) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `product_sizes`
--

INSERT INTO `product_sizes` (`id`, `product_id`, `size`) VALUES
(4, 1, 'L'),
(3, 1, 'M'),
(2, 1, 'S'),
(5, 1, 'XL'),
(1, 1, 'XS'),
(8, 2, 'L'),
(7, 2, 'M'),
(6, 2, 'S'),
(9, 2, 'XL'),
(22, 6, 'L'),
(21, 6, 'M'),
(20, 6, 'S'),
(23, 6, 'XL'),
(27, 7, 'L'),
(26, 7, 'M'),
(25, 7, 'S'),
(24, 7, 'XS'),
(32, 8, '10'),
(33, 8, '11'),
(28, 8, '6'),
(29, 8, '7'),
(30, 8, '8'),
(31, 8, '9'),
(42, 10, 'L'),
(41, 10, 'M'),
(40, 10, 'S'),
(43, 10, 'XL'),
(39, 10, 'XS'),
(55, 15, 'L'),
(54, 15, 'M'),
(53, 15, 'S'),
(56, 15, 'XL'),
(59, 16, 'L'),
(58, 16, 'M'),
(57, 16, 'S'),
(60, 16, 'XL'),
(71, 19, 'L'),
(70, 19, 'M'),
(69, 19, 'S'),
(72, 19, 'XL'),
(75, 20, 'L'),
(74, 20, 'M'),
(73, 20, 'S'),
(76, 20, 'XL'),
(79, 21, 'L'),
(78, 21, 'M'),
(77, 21, 'S'),
(80, 21, 'XL'),
(83, 22, 'L'),
(82, 22, 'M'),
(81, 22, 'S'),
(84, 22, 'XL'),
(87, 23, 'L'),
(86, 23, 'M'),
(85, 23, 'S'),
(88, 23, 'XL'),
(91, 24, 'L'),
(90, 24, 'M'),
(89, 24, 'S'),
(92, 24, 'XL');

-- --------------------------------------------------------

--
-- Table structure for table `shopping_cart`
--

CREATE TABLE `shopping_cart` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `session_id` varchar(100) DEFAULT NULL,
  `product_id` int(11) NOT NULL,
  `quantity` int(11) NOT NULL,
  `size` varchar(10) DEFAULT NULL,
  `color` varchar(50) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `first_name` varchar(50) NOT NULL,
  `last_name` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `user_type` enum('customer','admin') DEFAULT 'customer',
  `phone` varchar(20) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `is_active` tinyint(1) DEFAULT 1,
  `address` varchar(255) DEFAULT NULL,
  `city` varchar(100) DEFAULT NULL,
  `state` varchar(50) DEFAULT NULL,
  `zip_code` varchar(20) DEFAULT NULL,
  `date_of_birth` date DEFAULT NULL,
  `gender` enum('male','female','other','prefer_not_to_say') DEFAULT NULL,
  `status` enum('active','inactive','suspended') DEFAULT 'active'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `first_name`, `last_name`, `email`, `password_hash`, `user_type`, `phone`, `created_at`, `updated_at`, `is_active`, `address`, `city`, `state`, `zip_code`, `date_of_birth`, `gender`, `status`) VALUES
(1, 'Admin', 'User', 'admin@velvetvogue.com', '$2y$10$ydmZlDPMzGRXF/04jtaW3OrhZzP0m2/IIoKaAlkwkNb6I40Qu23d.', 'admin', NULL, '2025-10-11 10:50:46', '2025-10-11 12:01:01', 1, NULL, NULL, NULL, NULL, NULL, NULL, 'active'),
(2, 'Customer', 'Demo', 'customer@demo.com', '$2y$10$nA5Z3BwJZomjmvU0RJ2xv.KfsalwcZrGu40S26vgW2cjOyPgfQvXa', 'customer', NULL, '2025-10-11 10:50:46', '2025-10-12 16:36:10', 1, NULL, NULL, NULL, NULL, NULL, NULL, 'active'),
(3, 'Jane', 'Smith-Updated', 'jane.smith@example.com', '$2y$10$lohTpav5ml586cu2N9a5puP.xFrYutwEMy3fF0Q5TK.tDG/XoLI/e', 'customer', '555-0198', '2025-10-11 16:39:34', '2025-10-12 15:16:49', 1, '123 Test Street', 'Test City', 'TS', '12345', NULL, NULL, 'active'),
(4, 'Imaz', 'Nazeer', 'imaz@gmail.com', '$2y$10$nHf3pQICMWJlqxwdMfJGPOvbSN9JVmAlErpHeG0/aA2rkl1yN499i', 'customer', '0776856890', '2025-10-11 16:43:43', '2025-10-12 16:35:43', 1, NULL, NULL, NULL, NULL, NULL, NULL, 'active');

-- --------------------------------------------------------

--
-- Structure for view `product_details`
--
DROP TABLE IF EXISTS `product_details`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `product_details`  AS SELECT `p`.`id` AS `id`, `p`.`name` AS `name`, `p`.`description` AS `description`, `p`.`price` AS `price`, `p`.`category_id` AS `category_id`, `p`.`gender` AS `gender`, `p`.`type` AS `type`, `p`.`image_url` AS `image_url`, `p`.`fit` AS `fit`, `p`.`stock_quantity` AS `stock_quantity`, `p`.`is_featured` AS `is_featured`, `p`.`is_active` AS `is_active`, `p`.`created_at` AS `created_at`, `p`.`updated_at` AS `updated_at`, `c`.`name` AS `category_name`, group_concat(distinct `ps`.`size` order by `ps`.`size` ASC separator ',') AS `sizes`, group_concat(distinct `pc`.`color` order by `pc`.`color` ASC separator ',') AS `colors` FROM (((`products` `p` left join `categories` `c` on(`p`.`category_id` = `c`.`id`)) left join `product_sizes` `ps` on(`p`.`id` = `ps`.`product_id`)) left join `product_colors` `pc` on(`p`.`id` = `pc`.`product_id`)) WHERE `p`.`is_active` = 1 GROUP BY `p`.`id` ;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `addresses`
--
ALTER TABLE `addresses`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `admin`
--
ALTER TABLE `admin`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `cart_items`
--
ALTER TABLE `cart_items`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_cart_item` (`user_id`,`product_id`,`size`,`color`),
  ADD UNIQUE KEY `unique_session_cart_item` (`session_id`,`product_id`,`size`,`color`),
  ADD KEY `product_id` (`product_id`);

--
-- Indexes for table `categories`
--
ALTER TABLE `categories`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `name` (`name`);

--
-- Indexes for table `contact_messages`
--
ALTER TABLE `contact_messages`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `order_number` (`order_number`),
  ADD KEY `idx_orders_user` (`user_id`),
  ADD KEY `idx_orders_status` (`status`);

--
-- Indexes for table `order_items`
--
ALTER TABLE `order_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `order_id` (`order_id`),
  ADD KEY `product_id` (`product_id`);

--
-- Indexes for table `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `product_colors`
--
ALTER TABLE `product_colors`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_product_color` (`product_id`,`color`);

--
-- Indexes for table `product_images`
--
ALTER TABLE `product_images`
  ADD PRIMARY KEY (`id`),
  ADD KEY `product_id` (`product_id`);

--
-- Indexes for table `product_sizes`
--
ALTER TABLE `product_sizes`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_product_size` (`product_id`,`size`);

--
-- Indexes for table `shopping_cart`
--
ALTER TABLE `shopping_cart`
  ADD PRIMARY KEY (`id`),
  ADD KEY `product_id` (`product_id`),
  ADD KEY `idx_cart_user` (`user_id`),
  ADD KEY `idx_cart_session` (`session_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `addresses`
--
ALTER TABLE `addresses`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `admin`
--
ALTER TABLE `admin`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `cart_items`
--
ALTER TABLE `cart_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `categories`
--
ALTER TABLE `categories`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=56;

--
-- AUTO_INCREMENT for table `contact_messages`
--
ALTER TABLE `contact_messages`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `orders`
--
ALTER TABLE `orders`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `order_items`
--
ALTER TABLE `order_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `products`
--
ALTER TABLE `products`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=28;

--
-- AUTO_INCREMENT for table `product_colors`
--
ALTER TABLE `product_colors`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=77;

--
-- AUTO_INCREMENT for table `product_images`
--
ALTER TABLE `product_images`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `product_sizes`
--
ALTER TABLE `product_sizes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=93;

--
-- AUTO_INCREMENT for table `shopping_cart`
--
ALTER TABLE `shopping_cart`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `addresses`
--
ALTER TABLE `addresses`
  ADD CONSTRAINT `addresses_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `cart_items`
--
ALTER TABLE `cart_items`
  ADD CONSTRAINT `cart_items_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `cart_items_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `orders`
--
ALTER TABLE `orders`
  ADD CONSTRAINT `orders_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `order_items`
--
ALTER TABLE `order_items`
  ADD CONSTRAINT `order_items_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `order_items_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `product_colors`
--
ALTER TABLE `product_colors`
  ADD CONSTRAINT `product_colors_ibfk_1` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `product_images`
--
ALTER TABLE `product_images`
  ADD CONSTRAINT `product_images_ibfk_1` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `product_sizes`
--
ALTER TABLE `product_sizes`
  ADD CONSTRAINT `product_sizes_ibfk_1` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `shopping_cart`
--
ALTER TABLE `shopping_cart`
  ADD CONSTRAINT `shopping_cart_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `shopping_cart_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
