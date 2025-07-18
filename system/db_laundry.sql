-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: localhost
-- Generation Time: Jul 16, 2025 at 10:39 AM
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
-- Database: `db_laundry`
--

-- --------------------------------------------------------

--
-- Table structure for table `admin_login`
--

CREATE TABLE `admin_login` (
  `id` int(11) NOT NULL,
  `Username` varchar(50) NOT NULL,
  `Password` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `admin_login`
--

INSERT INTO `admin_login` (`id`, `Username`, `Password`) VALUES
(1, 'admin1', '$2y$10$r7t0VA/5eUqqJwN8qpHdTujLVPmPUkih13EdoYdHx91q1O4dFwsUW');

-- --------------------------------------------------------

--
-- Table structure for table `booking`
--

CREATE TABLE `booking` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `firstname` varchar(255) NOT NULL,
  `lastname` varchar(255) NOT NULL,
  `address` text NOT NULL,
  `date` date NOT NULL,
  `garment_type` varchar(50) NOT NULL,
  `package` varchar(50) NOT NULL,
  `detergent_powder` tinyint(1) NOT NULL DEFAULT 0,
  `detergent_downy` tinyint(1) NOT NULL DEFAULT 0,
  `payment_mode` varchar(50) NOT NULL,
  `status` varchar(50) NOT NULL DEFAULT 'Pending',
  `weight` decimal(10,2) DEFAULT NULL,
  `timestamp` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `booking`
--

INSERT INTO `booking` (`id`, `user_id`, `firstname`, `lastname`, `address`, `date`, `garment_type`, `package`, `detergent_powder`, `detergent_downy`, `payment_mode`, `status`, `weight`, `timestamp`) VALUES
(1, 1, 'Raysel', 'Sabellano', 'A.Gabuya Street, La Purisima, Cogon Pardo, Cebu City', '2025-07-16', 'linen', 'wash-dry-fold', 1, 1, 'COD Only', 'Processing', 5.00, '2025-07-16 03:49:52'),
(2, 1, 'Leandro', 'Neri', 'Bisag Asa Street', '2025-07-17', 'bedding', 'wash-dryer', 0, 1, 'COD Only', 'Ready to Pickup', 10.00, '2025-07-16 03:50:11'),
(3, 1, 'marlon', 'cereno', 'jai alai', '2025-07-21', 'linen', 'wash-dry-fold', 1, 1, 'COD Only', 'Pending', NULL, '2025-07-16 06:43:13'),
(4, 1, 'marlon', 'cereno', 'jai alai', '2025-07-21', 'tshirt', 'wash-only', 1, 1, 'COD Only', 'Pending', NULL, '2025-07-16 06:43:47'),
(5, 1, 'marlon', 'cereno', 'jai alai', '2025-07-21', 'mixed', 'wash-dry-fold', 1, 1, 'COD Only', 'Pending', NULL, '2025-07-16 06:48:04'),
(6, 3, 'Rhobert', 'Carwana', 'A.Gabuya Street, La Purisima, Cogon Pardo, Cebu City', '2025-07-18', 'linen', 'wash-dryer', 1, 1, 'COD Only', 'Processing', NULL, '2025-07-16 07:00:58'),
(7, 3, 'Rhobert', 'Carwana', 'Bisag Asa Street', '2025-07-29', 'bedding', 'wash-dryer', 0, 1, 'COD Only', 'Ready to Pickup', NULL, '2025-07-16 07:45:48'),
(8, 3, 'Rhobert', 'Carwana', 'siquijor', '2025-07-20', 'tshirt', 'wash-only', 1, 0, 'COD Only', 'Completed', NULL, '2025-07-16 08:35:45');

-- --------------------------------------------------------

--
-- Table structure for table `userregistration`
--

CREATE TABLE `userregistration` (
  `id` int(11) NOT NULL,
  `fullname` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `firstname` varchar(255) DEFAULT NULL,
  `lastname` varchar(255) DEFAULT NULL,
  `num` varchar(20) NOT NULL,
  `password` varchar(255) NOT NULL,
  `gender` varchar(10) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `userregistration`
--

INSERT INTO `userregistration` (`id`, `fullname`, `email`, `firstname`, `lastname`, `num`, `password`, `gender`) VALUES
(1, 'Raysel Sabellano', 'sabellanoraysel16@gmail.com', NULL, NULL, '09053446245', '$2y$10$6.9I3UpM5S4FQrHgNsmRx.QzzZRj.10tKLCRq5D2aaQHjhZogUnm2', NULL),
(3, '', 'rhobertcarwana@gmail.com', 'Rhobert', 'Carwana', '12345678901', '$2y$10$31qGA3CTI1q.5uVtB86X/OmTkItUhuPBL1VlMxVfAxFygdWXD09Fy', NULL);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `admin_login`
--
ALTER TABLE `admin_login`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `Username` (`Username`);

--
-- Indexes for table `booking`
--
ALTER TABLE `booking`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `userregistration`
--
ALTER TABLE `userregistration`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `admin_login`
--
ALTER TABLE `admin_login`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `booking`
--
ALTER TABLE `booking`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `userregistration`
--
ALTER TABLE `userregistration`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `booking`
--
ALTER TABLE `booking`
  ADD CONSTRAINT `booking_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `userregistration` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
