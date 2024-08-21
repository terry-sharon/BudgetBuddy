-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Aug 21, 2024 at 11:52 PM
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
-- Database: `budgetbuddy`
--

-- --------------------------------------------------------

--
-- Table structure for table `admin`
--

CREATE TABLE `admin` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `role` enum('admin','user') DEFAULT 'user',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `budgets`
--

CREATE TABLE `budgets` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `total_amount` decimal(10,2) NOT NULL,
  `amount_spent` decimal(10,2) DEFAULT 0.00,
  `remaining_amount` decimal(10,2) GENERATED ALWAYS AS (`total_amount` - `amount_spent`) VIRTUAL,
  `start_date` date DEFAULT NULL,
  `end_date` date DEFAULT NULL,
  `category` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `budgets`
--

INSERT INTO `budgets` (`id`, `user_id`, `name`, `total_amount`, `amount_spent`, `start_date`, `end_date`, `category`, `created_at`, `updated_at`) VALUES
(2, 13, 'Transport', 600.00, 100.00, '2024-08-20', '2024-08-27', 'Transport', '2024-08-19 15:26:57', '2024-08-19 16:52:11'),
(3, 14, 'Terry\'s Locs', 3000.00, 900.00, '2024-08-19', '2024-09-01', 'Utilities', '2024-08-19 17:27:52', '2024-08-19 17:28:45'),
(4, 15, 'linet', 2000.00, 100.00, '2024-08-20', '2024-08-24', 'Food', '2024-08-19 18:12:25', '2024-08-19 18:19:18'),
(5, 16, 'events', 100000.00, 60000.00, '2024-08-20', '2024-09-03', 'Entertainment', '2024-08-19 20:02:58', '2024-08-19 20:04:32'),
(6, 18, 'Nakuru trip', 5000.00, 2250.00, '2024-08-18', '2024-08-24', 'Transport', '2024-08-21 07:49:01', '2024-08-21 07:50:35'),
(7, 1, 'Shopping', 60000.00, 10000.00, '2024-08-18', '2024-08-24', 'Utilities', '2024-08-21 16:02:34', '2024-08-21 16:03:32'),
(9, 19, 'Hair', 5000.00, 2000.00, '2024-08-01', '2024-08-31', 'Other', '2024-08-21 17:10:59', '2024-08-21 17:12:17');

-- --------------------------------------------------------

--
-- Table structure for table `chatbot`
--

CREATE TABLE `chatbot` (
  `id` int(255) NOT NULL,
  `queries` varchar(255) NOT NULL,
  `replies` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `reminder`
--

CREATE TABLE `reminder` (
  `title` varchar(255) NOT NULL,
  `details` varchar(255) NOT NULL,
  `reminder_date` date NOT NULL,
  `id` int(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `reminder`
--

INSERT INTO `reminder` (`title`, `details`, `reminder_date`, `id`) VALUES
('bills', 'pay water', '2024-08-20', 1),
('bills', 'pay water', '2024-08-20', 2),
('bills', 'pay water', '2024-08-20', 3),
('bills', 'pay water', '2024-08-20', 4);

-- --------------------------------------------------------

--
-- Table structure for table `transactions`
--

CREATE TABLE `transactions` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `description` varchar(255) NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `category` varchar(100) NOT NULL,
  `date` datetime NOT NULL,
  `budget_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `transactions`
--

INSERT INTO `transactions` (`id`, `user_id`, `description`, `amount`, `category`, `date`, `budget_id`) VALUES
(6, 13, 'Transport', 50.00, 'Transport', '2024-08-21 00:00:00', 2),
(7, 14, 'locs', 900.00, 'Utilities', '2024-08-21 00:00:00', 3),
(8, 16, 'Graduation', 50000.00, 'Entertainment', '2024-08-23 00:00:00', 5),
(9, 18, 'food', 1000.00, 'Food', '2024-08-20 00:00:00', 6),
(11, 19, 'Braids', 1400.00, 'Other', '2024-08-22 00:00:00', 9),
(12, 19, 'Twists', 600.00, 'Other', '2024-08-29 00:00:00', 9);

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(255) NOT NULL,
  `username` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` varchar(20) DEFAULT 'user'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `email`, `password`, `role`) VALUES
(1, 'root', 'terry@gmail.com', '$2y$10$t4KpLwGm4PEkFanPa4IiU.74JDVPYun45Cr7rHFQLN0sApctIUvFG', 'user'),
(4, 'ck123456', 'ck@gmail.com', '$2y$10$vbIBdGdxSZpTi6B62LuwW.06Cvxo/q58fFBG2LvEXg6AtSrcHTUqW', 'user'),
(12, 'bts', 'bts@gmail.com', '$2y$10$5lBjTvWNaVGKwpoUdeExSup2OUkqErQSyBkkJPzuNBseGG2nndvF2', 'user'),
(13, 'blue', 'bluee@gmail.com', '$2y$10$jRveFeCB5vSDGGfLNwHYyeY5flvgvEOzD5KITig5.pfMbBln5wkAy', 'user'),
(14, 'collo', 'collo@gmail.com', '$2y$10$6J6afdk1TavwKZV/PGMMHOmPhCcx5RYdHKt1IBoCe.SC0YkfWFJri', 'user'),
(15, 'mwambi', 'mwambi@gmail.com', '$2y$10$8g80yeAYzrdOTLlnpol5qu4GV7xNTT2OhUAMEqxaUsRsq1XawX9SG', 'user'),
(16, 'peep', 'peep@gmail.com', '$2y$10$zIBPFfryZbMi9f3NrKfuY.IPaRgPn/z0ZEi1eh1ixwbyWLr4zakvW', 'user'),
(17, 'yt', 'yt@gmail.com', '$2y$10$o8o.94Es1JqJOOYNKVqVx.Ib2i1QCjf96mJZ.hdiMbQcDAs5TKS2K', 'admin'),
(18, 'pneri', 'pneri@strathmore.edu', '$2y$10$W1XBtgC6Feaex6rl5lcMyuPPf/CpCiSAkQkNxBhf5Idyg7oGFwcKq', 'user'),
(19, 'root', 'bmk@gmail.com', '$2y$10$.7xVNL06XzVvfnGU8IGN6u56xBR6KrXIe8gtBySk4.lo2JSgMeylO', 'user');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `admin`
--
ALTER TABLE `admin`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `budgets`
--
ALTER TABLE `budgets`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `chatbot`
--
ALTER TABLE `chatbot`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `reminder`
--
ALTER TABLE `reminder`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `transactions`
--
ALTER TABLE `transactions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `budget_id` (`budget_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `admin`
--
ALTER TABLE `admin`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `budgets`
--
ALTER TABLE `budgets`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `chatbot`
--
ALTER TABLE `chatbot`
  MODIFY `id` int(255) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `reminder`
--
ALTER TABLE `reminder`
  MODIFY `id` int(255) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `transactions`
--
ALTER TABLE `transactions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(255) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=20;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `budgets`
--
ALTER TABLE `budgets`
  ADD CONSTRAINT `budgets_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `transactions`
--
ALTER TABLE `transactions`
  ADD CONSTRAINT `transactions_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `transactions_ibfk_2` FOREIGN KEY (`budget_id`) REFERENCES `budgets` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
