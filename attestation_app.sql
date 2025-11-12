-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Nov 12, 2025 at 08:00 AM
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
-- Database: `attestation_app`
--

-- --------------------------------------------------------

--
-- Table structure for table `admins`
--

CREATE TABLE `admins` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `admins`
--

INSERT INTO `admins` (`id`, `username`, `email`, `password`, `created_at`) VALUES
(1, 'admin', 'admin@gmail.com', '$2y$10$h5EDqG.2yFyCNH/jqNjr3ez.ag4439jcYL3Y5brm8aXT2ppQG.YFm', '2025-04-15 16:45:31');

-- --------------------------------------------------------

--
-- Table structure for table `applications`
--

CREATE TABLE `applications` (
  `id` int(11) NOT NULL,
  `worker_id` int(11) NOT NULL,
  `document_id` int(11) NOT NULL,
  `status` enum('pending','approved','rejected') DEFAULT 'pending',
  `admin_comment` text DEFAULT NULL,
  `seen_by_worker` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `document_requests`
--

CREATE TABLE `document_requests` (
  `id` int(11) NOT NULL,
  `client_id` int(11) NOT NULL,
  `document_title` varchar(100) DEFAULT NULL,
  `job_type` varchar(100) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `file_name` varchar(255) DEFAULT NULL,
  `status` enum('pending','assigned','completed') DEFAULT 'pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `worker_id` int(11) DEFAULT NULL,
  `courier_status` enum('not_sent','sent','received') DEFAULT 'not_sent',
  `courier_request_note` text DEFAULT NULL,
  `sent_at` datetime DEFAULT NULL,
  `received_at` datetime DEFAULT NULL,
  `job_done_file` varchar(255) DEFAULT NULL,
  `done_at` datetime DEFAULT NULL,
  `courier_return_status` enum('not_sent','sent','received') DEFAULT 'not_sent',
  `return_sent_at` datetime DEFAULT NULL,
  `return_received_at` datetime DEFAULT NULL,
  `payment_requested` tinyint(1) DEFAULT 0,
  `payment_instruction` text DEFAULT NULL,
  `payment_screenshot` varchar(255) DEFAULT NULL,
  `payment_status` varchar(50) NOT NULL DEFAULT 'none',
  `payment_uploaded_at` datetime DEFAULT NULL,
  `payment_approved_at` datetime DEFAULT NULL,
  `courier_request_address` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `first_name` varchar(50) DEFAULT NULL,
  `last_name` varchar(50) DEFAULT NULL,
  `email` varchar(100) NOT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `city` varchar(50) DEFAULT NULL,
  `age` int(11) DEFAULT NULL,
  `gender` enum('male','female','other') DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('client','worker') NOT NULL,
  `id_image` varchar(255) DEFAULT NULL,
  `selfie_image` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `status` enum('pending','approved','rejected') DEFAULT 'pending'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `first_name`, `last_name`, `email`, `phone`, `city`, `age`, `gender`, `password`, `role`, `id_image`, `selfie_image`, `created_at`, `status`) VALUES
(9, 'Zain', 'Bahi', 'zain@gmail.com', '021', 'Abbottabad', 19, 'male', '$2y$10$pb8tqufW/mpfGvpFlSETJe9MWsSQi6RUqoFAqp.LlTI6x4wYBO786', 'worker', '67ffc6ce74c87_360_F_232922178_YCAxIU0vlGoGY2H76ZsATswNrOVbWlUv.jpg', '67ffc6ce74c8d_Screenshot 2025-04-15 212139.png', '2025-04-16 15:03:42', 'approved'),
(10, 'Muskan', 'zee', 'muskan@gmail.com', '111', 'Islamabad', 19, 'female', '$2y$10$oqdLXndbmH.LWzfWPA0NhOWLYP9bIoA2gqghYzKTpzjly.NxSSmTW', 'worker', '67ffc6f8e2513_WhatsApp Image 2025-04-16 at 6.51.34 PM.jpeg', '67ffc6f8e251b_ChatGPT Image Apr 16, 2025, 07_46_22 PM.png', '2025-04-16 15:04:24', 'approved'),
(20, 'Hassan', 'Ali', 'hassan@gmail.com', '03407336755', 'Haripur', 19, 'male', '$2y$10$p/CNotnJmY3O30woLbVoROVMcR3dzQEeSmVv8OpF4P96Q/bvdZRuC', 'client', '68186d1573643_iD.png', '68186d1573654_Selfi.png', '2025-05-05 07:47:33', 'approved'),
(21, 'Usman', 'Nawaz', 'usman@gmail.com', '111222333', 'Karachi', 21, 'male', '$2y$10$bG5lMEs3yJZcQRrsWFMSTurILx5RMzk5PqX0x52/t.46uXq2b4nMq', 'client', '68bdc27e0f83e_68186d1573643_iD.png', '68bdc27e0f861_68186d1573654_Selfi.png', '2025-09-07 17:35:58', 'approved');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `admins`
--
ALTER TABLE `admins`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `applications`
--
ALTER TABLE `applications`
  ADD PRIMARY KEY (`id`),
  ADD KEY `worker_id` (`worker_id`),
  ADD KEY `document_id` (`document_id`);

--
-- Indexes for table `document_requests`
--
ALTER TABLE `document_requests`
  ADD PRIMARY KEY (`id`),
  ADD KEY `client_id` (`client_id`),
  ADD KEY `worker_id` (`worker_id`);

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
-- AUTO_INCREMENT for table `admins`
--
ALTER TABLE `admins`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `applications`
--
ALTER TABLE `applications`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=27;

--
-- AUTO_INCREMENT for table `document_requests`
--
ALTER TABLE `document_requests`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=22;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `applications`
--
ALTER TABLE `applications`
  ADD CONSTRAINT `applications_ibfk_1` FOREIGN KEY (`worker_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `applications_ibfk_2` FOREIGN KEY (`document_id`) REFERENCES `document_requests` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `document_requests`
--
ALTER TABLE `document_requests`
  ADD CONSTRAINT `document_requests_ibfk_1` FOREIGN KEY (`client_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `document_requests_ibfk_2` FOREIGN KEY (`worker_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
