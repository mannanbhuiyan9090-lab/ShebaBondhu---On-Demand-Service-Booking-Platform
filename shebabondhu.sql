-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Apr 30, 2026 at 08:55 AM
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
-- Database: `shebabondhu`
--

-- --------------------------------------------------------

--
-- Table structure for table `bookings`
--

CREATE TABLE `bookings` (
  `id` int(10) UNSIGNED NOT NULL,
  `ref_code` varchar(20) NOT NULL,
  `customer_id` int(10) UNSIGNED NOT NULL,
  `provider_id` int(10) UNSIGNED NOT NULL,
  `service_type` varchar(80) NOT NULL,
  `preferred_date` date NOT NULL,
  `time_start` time NOT NULL,
  `time_end` time NOT NULL,
  `address` text NOT NULL,
  `description` text DEFAULT NULL,
  `status` enum('Pending','Approved','Completed','Cancelled') NOT NULL DEFAULT 'Pending',
  `provider_note` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `payment_method` enum('ssl','mobile_banking','cash_on_delivery') DEFAULT NULL,
  `payment_status` enum('unpaid','paid','pending_verification') DEFAULT 'unpaid',
  `payment_code` varchar(20) DEFAULT NULL,
  `service_charge` int(11) DEFAULT 0,
  `price_range` varchar(50) DEFAULT NULL,
  `estimated_price` int(11) DEFAULT 0,
  `revenue` int(11) DEFAULT 0,
  `amount` decimal(10,2) DEFAULT NULL,
  `is_read` tinyint(4) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `bookings`
--

INSERT INTO `bookings` (`id`, `ref_code`, `customer_id`, `provider_id`, `service_type`, `preferred_date`, `time_start`, `time_end`, `address`, `description`, `status`, `provider_note`, `created_at`, `payment_method`, `payment_status`, `payment_code`, `service_charge`, `price_range`, `estimated_price`, `revenue`, `amount`, `is_read`) VALUES
(1, 'HG-2026-AA001', 2, 1, 'Plumbing', '2026-04-10', '10:00:00', '14:00:00', 'ফ্ল্যাট ৫এ, রোড ১২, উত্তরা', 'রান্নাঘরের কল লিক করছে।', 'Completed', '', '2026-04-13 06:32:42', 'mobile_banking', 'paid', '', 0, NULL, 0, 0, 1500.00, 1),
(2, 'HG-2026-AA002', 2, 3, 'Electricals', '2026-04-15', '09:00:00', '12:00:00', 'ফ্ল্যাট ৫এ, রোড ১২, উত্তরা', 'ফ্যান কাজ করছে না।', 'Completed', '', '2026-04-13 06:32:42', 'cash_on_delivery', 'paid', '', 0, NULL, 0, 0, 2000.00, 1),
(3, 'HG-2026-AA003', 2, 2, 'Cleaning', '2026-05-20', '08:00:00', '14:00:00', 'ফ্ল্যাট ৫এ, রোড ১২, উত্তরা', 'সম্পূর্ণ ফ্ল্যাট ডিপ ক্লিন।', 'Completed', '', '2026-04-13 06:32:42', 'ssl', 'paid', '', 0, NULL, 0, 0, 2500.00, 1),
(4, 'HG-2026-AA004', 3, 6, 'Carpentry', '2026-04-12', '11:00:00', '15:00:00', 'হাউস ৯, রোড ৩, ধানমন্ডি', 'আলমারির দরজার হিঞ্জ ভাঙা।', 'Completed', '', '2026-04-13 06:32:42', 'mobile_banking', 'paid', '', 0, NULL, 0, 0, 1500.00, 1),
(5, 'HG-2026-AA005', 4, 4, 'Cleaning', '2026-05-22', '09:00:00', '13:00:00', 'ব্লক সি, মোহাম্মদপুর', 'রিনোভেশন পরবর্তী পরিষ্কার।', 'Cancelled', '', '2026-04-13 06:32:42', 'cash_on_delivery', 'unpaid', '', 0, NULL, 0, 0, 1700.00, 1),
(6, 'HG7EDB14', 4, 12, 'Pest Control', '2026-04-13', '09:00:00', '18:00:00', 'Lalbug kella more', '', 'Completed', '', '2026-04-16 07:31:02', 'cash_on_delivery', 'paid', '', 0, NULL, 0, 0, 2300.00, 1),
(7, 'HG-2026-023DDE', 2, 3, 'Electricals', '2026-04-20', '08:00:00', '20:00:00', 'f-123,r-234,Gulsan Naton Bazar', 'pipe line', 'Completed', '', '2026-04-19 06:58:44', 'mobile_banking', 'paid', '', 0, NULL, 0, 0, 1500.00, 1),
(8, 'HG4BC904', 2, 10, 'Cleaning', '2026-04-21', '09:00:00', '18:00:00', 'Basundhara house no-583', 'Clean bead kitchen and bathroom', 'Completed', '', '2026-04-19 07:19:25', 'cash_on_delivery', 'paid', 'COD871F', 0, NULL, 0, 0, 1000.00, 1),
(9, 'HG88AE93', 2, 12, 'Pest Control', '2026-04-22', '09:00:00', '18:00:00', 'Danmondi-32', 'paste control music', 'Completed', '', '2026-04-19 07:20:23', 'mobile_banking', 'paid', '', 0, NULL, 0, 0, 2600.00, 1),
(10, 'HG-2026-EBA08D', 4, 10, 'Pest Control', '2026-04-15', '10:00:00', '12:00:00', 'Dhaka', '', 'Completed', '', '2026-04-20 05:07:09', 'cash_on_delivery', 'paid', '', 0, NULL, 0, 0, 1800.00, 1),
(11, 'HG-2026-FB028C', 20, 4, 'Cleaning', '2026-02-15', '10:00:00', '12:00:00', 'Dhaka', '', 'Completed', '', '2026-04-20 05:07:10', 'mobile_banking', 'paid', '', 0, NULL, 0, 0, 1700.00, 1),
(12, 'HG-2026-93C5EB', 19, 5, 'Electricals', '2026-01-15', '10:00:00', '12:00:00', 'Dhaka', '', 'Completed', '', '2026-04-20 05:07:10', 'cash_on_delivery', 'paid', '', 0, NULL, 0, 0, 2200.00, 1),
(13, 'HG-2026-DAA459', 2, 1, 'Plumbing', '2026-03-15', '10:00:00', '12:00:00', 'Dhaka', '', 'Completed', '', '2026-04-20 05:07:10', 'ssl', 'paid', '', 0, NULL, 0, 0, 2300.00, 1),
(14, 'HG-2026-EA1BD2', 3, 9, 'Painter', '2026-02-15', '10:00:00', '12:00:00', 'Dhaka', '', 'Pending', '', '2026-04-20 05:07:10', 'mobile_banking', 'pending_verification', '', 0, NULL, 0, 0, 1200.00, 1),
(15, 'HG-2026-D951FF', 4, 8, 'Carpentry', '2026-03-15', '10:00:00', '12:00:00', 'Dhaka', '', 'Completed', '', '2026-04-20 05:07:10', 'cash_on_delivery', 'paid', '', 0, NULL, 0, 0, 3500.00, 1),
(16, 'HG-2026-6CA86C', 4, 13, 'Painter', '2026-03-15', '10:00:00', '12:00:00', 'Dhaka', '', 'Pending', '', '2026-04-20 05:07:10', 'mobile_banking', 'unpaid', '', 0, NULL, 0, 0, 800.00, 1),
(17, 'HG-2026-6A9B47', 2, 5, 'Electricals', '2026-01-15', '10:00:00', '12:00:00', 'Dhaka', '', 'Completed', '', '2026-04-20 05:07:10', 'ssl', 'paid', '', 0, NULL, 0, 0, 1200.00, 1),
(18, 'HG-2026-6A3D95', 3, 2, 'Plumbing', '2026-03-15', '10:00:00', '12:00:00', 'Dhaka', '', 'Completed', '', '2026-04-20 05:07:10', 'mobile_banking', 'paid', '', 0, NULL, 0, 0, 1700.00, 1),
(19, 'HG-2026-7C07C8', 22, 4, 'Painter', '2026-03-15', '10:00:00', '12:00:00', 'Dhaka', '', 'Completed', '', '2026-04-20 05:07:10', 'mobile_banking', 'unpaid', '', 0, NULL, 0, 0, 2600.00, 1),
(20, 'HG-2026-2DEB00', 23, 4, 'Mechanic', '2026-04-03', '10:00:00', '12:00:00', 'Dhaka', '', 'Completed', '', '2026-04-20 05:07:10', 'mobile_banking', 'paid', '', 0, NULL, 0, 0, 2100.00, 1),
(21, 'HG-2026-D504AC', 2, 6, 'Plumbing', '2026-04-07', '10:00:00', '12:00:00', 'Dhaka', '', 'Completed', '', '2026-04-20 05:07:10', 'ssl', 'paid', '', 0, NULL, 0, 0, 2400.00, 1),
(22, 'HG-2026-C0FB0D', 3, 10, 'Mechanic', '2026-04-02', '10:00:00', '12:00:00', 'Dhaka', '', 'Completed', '', '2026-04-20 05:07:10', 'mobile_banking', 'paid', '', 0, NULL, 0, 0, 1800.00, 1),
(23, 'HG-2026-4C4704', 4, 13, 'Painter', '2026-04-08', '10:00:00', '12:00:00', 'Dhaka', '', 'Completed', '', '2026-04-20 05:07:10', 'cash_on_delivery', 'paid', '', 0, NULL, 0, 0, 1800.00, 1),
(24, 'HG-2026-894FFC', 21, 4, 'Cleaning', '2026-04-12', '10:00:00', '12:00:00', 'Dhaka', '', 'Completed', '', '2026-04-20 05:07:10', 'cash_on_delivery', 'paid', '', 0, NULL, 0, 0, 2500.00, 1),
(25, 'HG620428', 18, 1, 'Plumbing', '2026-04-20', '09:00:00', '18:00:00', 'Uttara 9 sector', 'Pipe lickage', 'Completed', NULL, '2026-04-20 06:58:03', 'ssl', 'paid', '', 0, NULL, 0, 0, 2500.00, 1),
(26, 'HG2B6B07', 18, 12, 'Pest Control', '2026-04-22', '09:00:00', '18:00:00', 'Danmodi 32', 'paste control need', 'Completed', '', '2026-04-20 06:58:57', 'cash_on_delivery', 'paid', 'COD2494', 0, NULL, 0, 0, 1500.00, 1),
(27, 'HG7ADEB6', 18, 36, 'Home Shifting', '2026-04-29', '09:00:00', '18:00:00', 'Dhanmondi 32', 'house shift', 'Approved', '', '2026-04-26 03:28:14', 'mobile_banking', 'pending_verification', '', 0, NULL, 0, 0, 1850.00, 1),
(28, 'HG84DD72', 2, 13, 'Plumbing', '2026-04-28', '09:00:00', '18:00:00', 'রামপুরা-বনশ্রী', 'Pipe line fittings', 'Completed', '', '2026-04-26 06:55:51', 'ssl', 'paid', '', 300, '1500', 1500, 1200, 2300.00, 1),
(29, 'HG2AA629', 18, 31, 'Glass & Aluminum', '2026-04-29', '09:00:00', '18:00:00', 'মিরপুর 2', 'গ্লাস ও অ্যালুমিনিয়াম fittings', 'Approved', '', '2026-04-26 07:03:38', 'mobile_banking', 'pending_verification', '', 300, '1750', 1750, 1450, 1750.00, 1),
(30, 'HG6B796B', 19, 18, 'Security System', '2026-05-03', '09:00:00', '18:00:00', 'Uttara 11 no. sector', 'I need argent CC Camera setting for my home surrounding.', 'Pending', NULL, '2026-04-28 04:13:45', 'cash_on_delivery', 'pending_verification', 'COD45BD', 225, '1500', 1500, 1275, NULL, 0),
(31, 'HGBD73F4', 19, 19, 'Interior Design', '2026-05-07', '09:00:00', '18:00:00', 'Uttara 11 no sector', 'I want decoder my bedroom, kitchen and bathroom.', 'Pending', NULL, '2026-04-28 04:22:08', 'cash_on_delivery', 'pending_verification', 'CODFEDE', 262, '1750', 1750, 1488, NULL, 0),
(32, 'HG465385', 19, 23, 'Lift Service', '2026-05-03', '09:00:00', '18:00:00', 'Uttara 11 sector', 'Lift not work', 'Pending', NULL, '2026-04-28 05:00:32', 'ssl', 'unpaid', '', 277, '1850', 1850, 1573, NULL, 0),
(33, 'HG2D94B2', 18, 13, 'Plumbing', '2026-05-02', '09:00:00', '18:00:00', 'Rampura dhaka-1200', 'pipe filter', 'Pending', NULL, '2026-04-28 07:04:36', 'ssl', 'unpaid', '', 187, '1250', 1250, 1063, NULL, 0),
(34, 'HG445337', 22, 25, 'Interior Design', '2026-05-01', '09:00:00', '18:00:00', 'Bannani baridhara', 'Interor decorate', 'Completed', NULL, '2026-04-30 04:26:31', 'cash_on_delivery', 'paid', 'COD4FFD', 510, '1750', 3400, 2890, 3400.00, 0);

-- --------------------------------------------------------

--
-- Table structure for table `contact_messages`
--

CREATE TABLE `contact_messages` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(150) NOT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `subject` varchar(200) DEFAULT NULL,
  `message` text NOT NULL,
  `created_at` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `contact_messages`
--

INSERT INTO `contact_messages` (`id`, `name`, `email`, `phone`, `subject`, `message`, `created_at`) VALUES
(1, 'MD. Mannan Bhuiyan', 'royal@handygo.com', '01783383402', 'সেবা সম্পর্কে', 'How is this work?', '2026-04-18 09:26:07'),
(2, 'Mannan', 'husain.learn.wdpf@gmail.com', '0123897343', 'বিলিং সমস্যা', 'How can i find bill?', '2026-04-18 09:34:49'),
(3, 'mannan', 'husain.learn.wdpf@gmail.com', '0123897343', 'সাধারণ প্রশ্ন', 'How are you ?', '2026-04-18 09:40:30'),
(4, 'Rahman', 'husain.learn.wdpf@gmail.com', '0178235478', 'সাধারণ প্রশ্ন', 'How are you?', '2026-04-18 09:46:40'),
(5, 'MD. Mannan Bhuiyan', 'royal@handygo.com', '01783383402', 'সেবা সম্পর্কে', 'How is this work?', '2026-04-18 10:16:39'),
(6, 'Shahadat', 'jane@abc.in', '01213754456', 'প্রোভাইডার হতে চান', 'Can i eligible for a provider services?', '2026-04-18 10:47:39'),
(7, 'Sadiya Shikder', 'sadiya@gmail.com', '0187346346', 'সেবা সম্পর্কে', 'How many services your company provide ?', '2026-04-19 12:06:28');

-- --------------------------------------------------------

--
-- Table structure for table `notification`
--

CREATE TABLE `notification` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT 0,
  `role_target` varchar(20) NOT NULL,
  `type` varchar(30) NOT NULL,
  `title` varchar(100) NOT NULL,
  `message` text DEFAULT NULL,
  `is_read` tinyint(1) DEFAULT 0,
  `created_at` datetime NOT NULL,
  `link` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `notification`
--

INSERT INTO `notification` (`id`, `user_id`, `role_target`, `type`, `title`, `message`, `is_read`, `created_at`, `link`) VALUES
(1, 0, 'admin', 'contact', 'নতুন যোগাযোগ বার্তা', 'mannan - সাধারণ প্রশ্ন', 0, '2026-04-18 09:40:30', NULL),
(2, 0, 'admin', 'test', 'টেস্ট নোটিফিকেশন', 'টেস্ট মেসেজ', 0, '2026-04-18 09:42:21', NULL),
(3, 0, 'admin', 'debug', 'Debug Test', 'Testing notification', 0, '2026-04-18 09:42:38', NULL),
(4, 0, 'admin', 'contact', 'নতুন যোগাযোগ বার্তা', 'Rahman - সাধারণ প্রশ্ন', 1, '2026-04-18 09:46:40', NULL),
(5, 0, 'admin', 'contact', 'নতুন যোগাযোগ বার্তা', 'MD. Mannan Bhuiyan\nইমেইল: royal@handygo.com\nফোন: 01783383402\nবিষয়: সেবা সম্পর্কে\n\nমেসেজ: How is this work?', 0, '2026-04-18 10:16:39', NULL),
(6, 0, 'admin', 'contact', 'নতুন যোগাযোগ বার্তা', 'Shahadat\nইমেইল: jane@abc.in\nফোন: 01213754456\nবিষয়: প্রোভাইডার হতে চান\n\nমেসেজ: Can i eligible for a provider services?', 0, '2026-04-18 10:47:39', NULL),
(7, 0, 'admin', 'contact', 'নতুন যোগাযোগ বার্তা', 'টেস্ট মেসেজ - রাহেলা বেগম\nইমেইল: rahela@example.com\nফোন: 01711111111\nবিষয়: সাধারণ প্রশ্ন\n\nমেসেজ: হ্যালো, আমার একটি প্রশ্ন আছে।', 0, '2026-04-18 10:55:35', NULL),
(8, 0, 'admin', 'booking', 'নতুন বুকিং', 'HG-2026-AA001 - রাহেলা বেগম → রয়্যাল প্লাম্বিং সার্ভিস সম্পন্ন হয়েছে।', 1, '2026-04-18 10:55:35', NULL),
(9, 0, 'admin', 'contact', 'নতুন যোগাযোগ বার্তা', 'টেস্ট মেসেজ - রাহেলা বেগম\nইমেইল: rahela@example.com\nফোন: 01711111111\nবিষয়: সাধারণ প্রশ্ন\n\nমেসেজ: হ্যালো, আমার একটি প্রশ্ন আছে।', 0, '2026-04-18 10:56:18', NULL),
(10, 0, 'admin', 'booking', 'নতুন বুকিং', 'HG-2026-AA001 - রাহেলা বেগম → রয়্যাল প্লাম্বিং সার্ভিস সম্পন্ন হয়েছে।', 1, '2026-04-18 10:56:18', NULL),
(11, 0, 'admin', 'contact', 'নতুন যোগাযোগ বার্তা', 'Sadiya Shikder\nইমেইল: sadiya@gmail.com\nফোন: 0187346346\nবিষয়: সেবা সম্পর্কে\n\nমেসেজ: How many services your company provide ?', 0, '2026-04-19 12:06:28', NULL),
(12, 0, 'admin', 'booking', 'নতুন বুকিং!', 'একটি নতুন বুকিং হয়েছে। রেফারেন্স: HG2D94B2', 1, '2026-04-28 13:04:36', 'admin/bookings.php'),
(13, 0, 'admin', 'booking', 'নতুন বুকিং!', 'একটি নতুন বুকিং হয়েছে। রেফারেন্স: HG445337', 1, '2026-04-30 10:26:31', 'admin/bookings.php');

-- --------------------------------------------------------

--
-- Table structure for table `providers`
--

CREATE TABLE `providers` (
  `id` int(10) UNSIGNED NOT NULL,
  `user_id` int(10) UNSIGNED NOT NULL,
  `company_name` varchar(180) NOT NULL,
  `service_type` varchar(80) NOT NULL,
  `city` varchar(80) NOT NULL,
  `about` text DEFAULT NULL,
  `price_range` varchar(80) NOT NULL DEFAULT '৳৫০০–৳২০০০',
  `service_charge` int(11) DEFAULT 300,
  `work_start` time NOT NULL DEFAULT '09:00:00',
  `work_end` time NOT NULL DEFAULT '18:00:00',
  `rating` decimal(3,2) NOT NULL DEFAULT 5.00,
  `review_count` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `available` tinyint(1) NOT NULL DEFAULT 1,
  `verified` tinyint(1) NOT NULL DEFAULT 0,
  `emoji` varchar(10) NOT NULL DEFAULT '?',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `photo` varchar(255) DEFAULT NULL,
  `price_min` int(11) DEFAULT 500,
  `price_max` int(11) DEFAULT 2000
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `providers`
--

INSERT INTO `providers` (`id`, `user_id`, `company_name`, `service_type`, `city`, `about`, `price_range`, `service_charge`, `work_start`, `work_end`, `rating`, `review_count`, `available`, `verified`, `emoji`, `created_at`, `photo`, `price_min`, `price_max`) VALUES
(1, 5, 'রয়্যাল প্লাম্বিং সার্ভিস', 'Plumbing', 'Uttara', 'বিশেষজ্ঞ পাইপলাইন সমাধান — কল, পাইপ, শাওয়ার, ড্রেনেজ। ১০+ বছরের অভিজ্ঞতা।', '5500', 300, '08:00:00', '18:00:00', 4.90, 87, 1, 1, '🔧', '2026-04-13 06:32:42', NULL, 500, 7000),
(2, 6, 'টাইডি হোমস ক্লিনিং', 'Cleaning', 'Dhanmondi', 'সম্পূর্ণ ঘর পরিষ্কার, গভীর স্যানিটাইজেশন।', '৳৮০০–৳৩০০০', 300, '09:00:00', '17:00:00', 4.70, 62, 1, 1, '🧹', '2026-04-13 06:32:42', NULL, 500, 2000),
(3, 7, 'সুইফট ইলেকট্রিক্যালস', 'Electricals', 'Banani', 'তার, ফ্যান, সকেট, MCB মেরামত। লাইসেন্সপ্রাপ্ত।', '৳৬০০–৳৩৫০০', 300, '08:00:00', '20:00:00', 4.80, 104, 1, 1, '⚡', '2026-04-13 06:32:42', NULL, 500, 2000),
(4, 8, 'পারফেক্ট হোমস ক্লিনিং', 'Cleaning', 'Mohammadpur', 'পরিবেশবান্ধব পণ্য দিয়ে পেশাদার পরিষ্কার।', '৳৭০০–৳২৫০০', 300, '10:00:00', '18:00:00', 4.60, 45, 1, 1, '✨', '2026-04-13 06:32:42', NULL, 500, 2000),
(5, 9, 'ঢাকা পেস্ট সলিউশনস', 'Pest Control', 'Mirpur', 'তেলাপোকা, ইঁদুর, মশা ও উইপোকা। সার্টিফাইড।', '৳১০০০–৳৪০০০', 300, '09:00:00', '17:00:00', 4.50, 39, 1, 1, '🐛', '2026-04-13 06:32:42', NULL, 500, 2000),
(6, 10, 'মাস্টার কার্পেন্টার বিডি', 'Carpentry', 'Uttara', 'আসবাবপত্র মেরামত, কাঠের কাজ, ক্যাবিনেট।', '৳৭০০–৳৫০০০', 300, '09:00:00', '18:00:00', 4.90, 71, 1, 1, '🪚', '2026-04-13 06:32:42', NULL, 500, 2000),
(7, 11, 'অটো ফিক্স মেকানিক', 'Mechanic', 'Bashundhara', 'গাড়ি মেরামত, সার্ভিসিং, অয়েল চেঞ্জ।', '৳৫০০–৳৮০০০', 300, '08:00:00', '19:00:00', 4.40, 28, 1, 1, '🔩', '2026-04-13 06:32:42', NULL, 500, 2000),
(8, 12, 'কালার প্রো পেইন্টার্স', 'Painter', 'Banani', 'ইন্টেরিয়র ও এক্সটেরিয়র পেইন্টিং।', '৳৮০০–৳৬০০০', 300, '08:00:00', '17:00:00', 4.70, 53, 1, 1, '🎨', '2026-04-13 06:32:42', NULL, 500, 2000),
(9, 13, 'আজিমপুর ইলেকট্রিক্যালস', 'Electricals', 'Azimpur', 'আবাসিক তারের কাজ, লাইট ইনস্টলেশন।', '৳৫০০–৳২৫০০', 300, '09:00:00', '18:00:00', 4.60, 34, 1, 1, '💡', '2026-04-13 06:32:42', NULL, 500, 2000),
(10, 14, 'গ্রিন ক্লিন সার্ভিসেস', 'Cleaning', 'Bashundhara', 'মুভ-ইন/আউট ক্লিনিং, কার্পেট ওয়াশিং।', '৳৯০০–৳৪৫০০', 300, '08:00:00', '20:00:00', 4.80, 91, 1, 1, '🌿', '2026-04-13 06:32:42', NULL, 500, 2000),
(11, 15, 'মিরপুর প্লাম্ব প্রো', 'Plumbing', 'Mirpur', 'পাইপ ইনস্টলেশন, মেরামত, ড্রেন ক্লিনিং।', '৳৪০০–৳২০০০', 300, '07:00:00', '17:00:00', 4.50, 42, 1, 1, '🚿', '2026-04-13 06:32:42', NULL, 500, 2000),
(12, 16, 'পেস্ট অ্যাওয়ে বিডি', 'Pest Control', 'Dhanmondi', 'গ্যারান্টিযুক্ত পেস্ট নির্মূল।', '৳১২০০–৳৫০০০', 300, '08:00:00', '18:00:00', 5.00, 2, 1, 1, '🔫', '2026-04-13 06:32:42', NULL, 500, 2000),
(13, 17, 'রয়্যাল প্লাম্বিং সার্ভিস', 'Plumbing', 'Rampura', 'রয়্যাল প্লাম্বিং সার্ভিস (Royal Plumbing Service) মূলত একটি পেশাদার প্লাম্বিং এবং স্যানিটারি সেবা প্রদানকারী প্রতিষ্ঠান, যা সাধারণত ঢাকা ভিত্তিক গ্রাহকদের জন্য জরুরি এবং সাধারণ প্লাম্বিং সমস্যার সমাধান দিয়ে থাকে।\r\nএই সার্ভিসের মূল বৈশিষ্ট্যগুলো নিচে দেওয়া হলো:\r\nপ্রদত্ত সেবাসমূহ:\r\nপানির ট্যাংক: পানির ট্যাংক পরিষ্কার এবং মেরামত করা।\r\nস্যানিটারি কাজ: টয়লেট, বেসিন এবং পাইপ লাইন ইন্সটলেশন বা রিপেয়ার।\r\nওয়াটার ফিল্টার: নতুন ফিল্টার লাগানো বা নষ্ট হওয়া ফিল্টার মেরামত।\r\nজরুরি সাপোর্ট: পাইপ লিক বা পানি সংক্রান্ত যে কোনো জরুরি প্রয়োজনে দ্রুত সেবা প্রদান।\r\nকাজের এলাকা: সাধারণত মোহাম্মদপুর, ধানমন্ডি, লালমাটিয়া এবং শ্যামলী এলাকায় এদের বিশেষ কার্যক্রম রয়েছে।\r\nযোগাযোগের মাধ্যম: তারা সরাসরি ফোন বা হোয়াটসঅ্যাপের মাধ্যমে অর্ডার নিয়ে থাকে। আপনি এই ধরণের সেবার জন্য Plumbing & electricity Home Service এর মতো বিশ্বস্ত লোকাল সার্ভিসগুলোর রিভিউ দেখে নিতে পারেন।\r\nআপনার যদি নির্দিষ্ট কোনো এলাকা বা বিশেষ কোনো সমস্যার জন্য তাদের প্রয়োজন হয়, তবে সরাসরি কল করে আপনার লোকেশন নিশ্চিত করা ভালো।', '৳৫০০–৳২০০০', 300, '09:00:00', '18:00:00', 5.00, 0, 1, 1, '🔧', '2026-04-20 03:44:41', NULL, 500, 2000),
(14, 2, 'এসি সার্ভিস Provider', 'AC Service', 'Uttara', 'এসি সার্ভিস সেবা প্রদানকারী', '৳৫০০-৳২০০০', 300, '08:00:00', '20:00:00', 4.50, 0, 1, 1, '❄️', '2026-04-20 04:40:08', NULL, 500, 2000),
(15, 3, 'অ্যাপ্লায়েন্স রিপেয়ার Provider', 'Appliance Repair', 'Uttara', 'অ্যাপ্লায়েন্স রিপেয়ার সেবা প্রদানকারী', '৳৫০০-৳২০০০', 300, '08:00:00', '20:00:00', 4.50, 0, 1, 1, '🧰', '2026-04-20 04:40:08', NULL, 500, 2000),
(16, 4, 'বেঙ্গল মুভার্স অ্যান্ড প্যাকার্স', 'Home Shifting', 'Uttara', 'বাসা বদল সেবা প্রদানকারী', '৳৫০০-৳২০০০', 300, '08:00:00', '20:00:00', 4.50, 0, 0, 1, '🚚', '2026-04-20 04:40:08', NULL, 500, 2000),
(17, 5, 'গার্ডেনিং Provider', 'Gardening', 'Uttara', 'গার্ডেনিং সেবা প্রদানকারী', '৳৫০০-৳২০০০', 300, '08:00:00', '20:00:00', 4.50, 0, 1, 1, '🌿', '2026-04-20 04:40:08', NULL, 500, 2000),
(18, 6, 'সিকিউরিটি সিস্টেম Provider', 'Security System', 'Uttara', 'সিকিউরিটি সিস্টেম সেবা প্রদানকারী', '৳৫০০-৳২০০০', 300, '08:00:00', '20:00:00', 4.50, 0, 1, 1, '📹', '2026-04-20 04:40:08', NULL, 500, 2000),
(19, 18, 'ইন্টেরিয়র ডিজাইন কোম্পানি', 'Interior Design', 'Uttara', '🏡 ইন্টেরিয়র ডিজাইন সেবা প্রদানকারী', '৳৫০০-৳২০০০', 300, '08:00:00', '20:00:00', 4.50, 0, 1, 1, '🏡', '2026-04-20 04:43:15', NULL, 500, 2000),
(20, 19, 'গ্লাস ও অ্যালুমিনিয়াম কোম্পানি', 'Glass & Aluminum', 'Uttara', '🪟 গ্লাস ও অ্যালুমিনিয়াম সেবা প্রদানকারী', '৳৫০০-৳২০০০', 300, '08:00:00', '20:00:00', 4.50, 0, 1, 1, '🪟', '2026-04-20 04:43:15', NULL, 500, 2000),
(21, 20, 'ওয়াটার ট্যাংক ক্লিনিং কোম্পানি', 'Water Tank Cleaning', 'Uttara', '🚰 ওয়াটার ট্যাংক ক্লিনিং সেবা প্রদানকারী', '৳৫০০-৳২০০০', 300, '08:00:00', '20:00:00', 4.50, 0, 1, 1, '🚰', '2026-04-20 04:43:15', NULL, 500, 2000),
(22, 21, 'জেনারেটর সার্ভিস কোম্পানি', 'Generator Service', 'Uttara', '🔋 জেনারেটর সার্ভিস সেবা প্রদানকারী', '৳৫০০-৳২০০০', 300, '08:00:00', '20:00:00', 4.50, 0, 1, 1, '🔋', '2026-04-20 04:43:15', NULL, 500, 2000),
(23, 22, 'লিফট সার্ভিস কোম্পানি', 'Lift Service', 'Uttara', '🛗 লিফট সার্ভিস সেবা প্রদানকারী', '৳৫০০-৳২০০০', 300, '08:00:00', '20:00:00', 4.50, 0, 1, 1, '🛗', '2026-04-20 04:43:15', NULL, 500, 2000),
(24, 23, 'সোলার সার্ভিস কোম্পানি', 'Solar Service', 'Uttara', '☀️ সোলার সার্ভিস সেবা প্রদানকারী', '৳৫০০-৳২০০০', 300, '08:00:00', '20:00:00', 4.50, 0, 1, 1, '☀️', '2026-04-20 04:43:15', NULL, 500, 2000),
(25, 24, 'ইন্টেরিয়র ডিজাইন কোম্পানি', 'Interior Design', 'Baridhara', 'এটি একটি শীর্ষস্থানীয় ইন্টেরিয়র ফার্ম, যা নান্দনিক ডিজাইনে ফোকাস করে', '৳৫০০–৳২০০০', 300, '09:00:00', '18:00:00', 5.00, 0, 1, 1, '🔧', '2026-04-21 05:02:03', NULL, 500, 2000),
(26, 25, 'এয়ারকন কেয়ার (AIRCONcare)', 'AC Service', 'Badda', 'ঢাকা ভিত্তিক সেরা এসি মেরামত ও সার্ভিসিং প্রদানকারী,গ্রি, জেনারেল, ওয়ালটনের মতো ব্র্যান্ডের সার্ভিসিং প্রদান করে', '৳৫০০–৳২০০০', 300, '09:00:00', '18:00:00', 5.00, 0, 1, 1, '🔧', '2026-04-21 05:55:56', NULL, 500, 2000),
(27, 26, 'গার্ডেনিং Provider', 'Gardening', 'Tongi', 'রুফটপ, ইনডোর গার্ডেনিং এবং ল্যান্ডস্কেপিং', '৳৫০০–৳২০০০', 300, '09:00:00', '18:00:00', 5.00, 0, 1, 1, '🔧', '2026-04-21 06:04:37', NULL, 500, 2000),
(28, 27, 'সিকিউরিটি সিস্টেম Provider', 'Security System', 'Uttara', 'সিসিটিভি ক্যামেরা ইনস্টলেশনে বাংলাদেশের অন্যতম জনপ্রিয় কোম্পানি।', '৳৫০০–৳২০০০', 300, '09:00:00', '18:00:00', 5.00, 0, 1, 1, '🔧', '2026-04-21 06:04:39', NULL, 500, 2000),
(29, 28, 'জেনারেটর সার্ভিস কোম্পানি', 'Generator Service', 'Keraniganj', '২০০৫ সাল থেকে ডিজেল, পেট্রোল, এলপিজি ও ইনভার্টার জেনারেটরের বিশ্বস্ত সরবরাহকারী ও মেরামতকারী প্রতিষ্ঠান।', '৳৫০০–৳২০০০', 300, '09:00:00', '18:00:00', 5.00, 0, 1, 1, '🔧', '2026-04-21 06:13:55', NULL, 500, 2000),
(30, 29, 'পারফেক্ট মেকানিক্স', 'Mechanic', 'Mohakhali', 'ন্ত্রপাতির শক্তি, স্থায়িত্ব এবং নিরাপত্তা নিশ্চিত করতে সিমুলেশন বা পরীক্ষা চালানো', '৳৫০০–৳২০০০', 300, '09:00:00', '18:00:00', 5.00, 0, 1, 1, '🔧', '2026-04-21 06:13:57', NULL, 500, 2000),
(31, 30, 'অ্যালুমিনিয়াম থাই গ্লাস বিডি', 'Glass & Aluminum', 'Mirpur', 'ধুনিক থাই অ্যালুমিনিয়াম, অফিস/বাসার গ্লাস পার্টিশন এবং ডিজাইনার গ্লাসের জন্য পরিচিত।', '৳৫০০–৳২০০০', 300, '09:00:00', '18:00:00', 5.00, 0, 1, 1, '🔧', '2026-04-21 06:27:44', NULL, 500, 2000),
(32, 31, 'ওয়াটার ট্যাংক ক্লিনিং সেবা কোম্পানি', 'Water Tank Cleaning', 'Bashundhara', 'ভ্যাকুয়াম ক্লিনারের ব্যবহার: শ্যাওলা, তলানি ও ময়লা দূর করা ।\r\nজীবাণুনাশক স্প্রে: ব্যাকটেরিয়ামুক্ত ও বিশুদ্ধ পানির নিশ্চয়তা ।\r\nদক্ষ কর্মী: অভিজ্ঞ ও ভেরিফাইড কর্মীবাহিনী দ্বারা কাজ সম্পাদন ।', '৳৫০০–৳২০০০', 300, '09:00:00', '18:00:00', 5.00, 0, 1, 1, '🔧', '2026-04-21 06:27:46', NULL, 500, 2000),
(33, 32, 'Lift elevator service company in Bangladesh', 'Lift Service', 'Gulshan', 'প্যাসেঞ্জার লিফট, হোম লিফট এবং কার্গো লিফট ইনস্টলেশন ও রক্ষণাবেক্ষণ ,যেকোনো ব্র্যান্ডের লিফটের জন্য ২৪/৭ সার্ভিসিং এবং মেইনটেন্যান্স।', '৳৫০০–৳২০০০', 300, '09:00:00', '18:00:00', 5.00, 0, 1, 1, '🔧', '2026-04-21 06:27:49', NULL, 500, 2000),
(34, 33, 'ঢাকা সোলার লিমিটেড', 'Solar Service', 'Mohammadpur', 'সোলার প্যানেল, হাইব্রিড ইনভার্টার, ব্যাটারি এবং স্ট্রিট লাইট আমদানিকারক ও সেবাদাতা প্রতিষ্ঠান।', '৳৫০০–৳২০০০', 300, '09:00:00', '18:00:00', 5.00, 0, 1, 1, '🔧', '2026-04-21 06:27:55', NULL, 500, 2000),
(35, 34, 'ঢাকা অ্যাপ্লায়েন্স ডক্টর', 'Appliance Repair', 'Rampura', 'দক্ষ প্রকৌশলী ও স্বচ্ছতার সাথে রিপেয়ার সেবা প্রদান করে গ্রাহকের আস্থা অর্জন করা।', '৳৫০০–৳২০০০', 300, '09:00:00', '18:00:00', 5.00, 0, 1, 1, '🔧', '2026-04-21 06:58:17', NULL, 500, 2000),
(36, 35, 'ঢাকা প্যাকার্স অ্যান্ড মুভারস', 'Home Shifting', 'Dhanmondi', 'নির্ধারিত সময়ের মধ্যে গ্রাহকের মালামাল গন্তব্যে পৌঁছানো ।', '৳৫০০–৳২০০০', 300, '09:00:00', '18:00:00', 5.00, 0, 1, 1, '🔧', '2026-04-21 06:58:34', NULL, 500, 2000),
(37, 36, 'রাজধানী মুভার্স অ্যান্ড প্যাকার্স', 'Home Shifting', 'Savar', 'এটি বাংলাদেশের একটি অন্যতম বাসা ও অফিস বদল করার সার্ভিস প্রদানকারী প্রতিষ্ঠান।', '৳৫০০–৳২০০০', 300, '09:00:00', '18:00:00', 5.00, 0, 1, 1, '🔧', '2026-04-27 03:59:16', NULL, 500, 2000);

-- --------------------------------------------------------

--
-- Table structure for table `provider_applications`
--

CREATE TABLE `provider_applications` (
  `id` int(10) UNSIGNED NOT NULL,
  `fname` varchar(80) NOT NULL,
  `lname` varchar(80) NOT NULL DEFAULT '',
  `company_name` varchar(180) NOT NULL,
  `email` varchar(191) NOT NULL,
  `phone` varchar(30) NOT NULL,
  `service_type` varchar(80) NOT NULL,
  `city` varchar(80) NOT NULL,
  `work_start` time NOT NULL DEFAULT '09:00:00',
  `work_end` time NOT NULL DEFAULT '18:00:00',
  `about` text DEFAULT NULL,
  `status` enum('pending','approved','rejected') NOT NULL DEFAULT 'pending',
  `submitted_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `photo` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `provider_applications`
--

INSERT INTO `provider_applications` (`id`, `fname`, `lname`, `company_name`, `email`, `phone`, `service_type`, `city`, `work_start`, `work_end`, `about`, `status`, `submitted_at`, `photo`) VALUES
(1, 'MD Mannan', 'Bhuiyan', 'রয়্যাল প্লাম্বিং সার্ভিস', 'mannanbhuiyan9090@gmail.com', '01783383402', 'Plumbing', 'Rampura', '09:00:00', '18:00:00', 'রয়্যাল প্লাম্বিং সার্ভিস (Royal Plumbing Service) মূলত একটি পেশাদার প্লাম্বিং এবং স্যানিটারি সেবা প্রদানকারী প্রতিষ্ঠান, যা সাধারণত ঢাকা ভিত্তিক গ্রাহকদের জন্য জরুরি এবং সাধারণ প্লাম্বিং সমস্যার সমাধান দিয়ে থাকে।\r\nএই সার্ভিসের মূল বৈশিষ্ট্যগুলো নিচে দেওয়া হলো:\r\nপ্রদত্ত সেবাসমূহ:\r\nপানির ট্যাংক: পানির ট্যাংক পরিষ্কার এবং মেরামত করা।\r\nস্যানিটারি কাজ: টয়লেট, বেসিন এবং পাইপ লাইন ইন্সটলেশন বা রিপেয়ার।\r\nওয়াটার ফিল্টার: নতুন ফিল্টার লাগানো বা নষ্ট হওয়া ফিল্টার মেরামত।\r\nজরুরি সাপোর্ট: পাইপ লিক বা পানি সংক্রান্ত যে কোনো জরুরি প্রয়োজনে দ্রুত সেবা প্রদান।\r\nকাজের এলাকা: সাধারণত মোহাম্মদপুর, ধানমন্ডি, লালমাটিয়া এবং শ্যামলী এলাকায় এদের বিশেষ কার্যক্রম রয়েছে।\r\nযোগাযোগের মাধ্যম: তারা সরাসরি ফোন বা হোয়াটসঅ্যাপের মাধ্যমে অর্ডার নিয়ে থাকে। আপনি এই ধরণের সেবার জন্য Plumbing & electricity Home Service এর মতো বিশ্বস্ত লোকাল সার্ভিসগুলোর রিভিউ দেখে নিতে পারেন।\r\nআপনার যদি নির্দিষ্ট কোনো এলাকা বা বিশেষ কোনো সমস্যার জন্য তাদের প্রয়োজন হয়, তবে সরাসরি কল করে আপনার লোকেশন নিশ্চিত করা ভালো।', 'approved', '2026-04-20 03:33:09', NULL),
(2, 'MD Shakhawat', 'Hossen', 'ব্রাইট লাইন ইলেকট্রিশিয়ানস', 'jane@abc.in', '01734587955', 'Electricals', 'Bashundhara', '09:00:00', '18:00:00', 'ব্রাইট লাইন ইলেকট্রিশিয়ানস কোম্পানিগুলোর মূল লক্ষ্য হলো অখণ্ডতা, নিরাপত্তা এবং গুণমানের সাথে শীর্ষস্থানীয় ইলেকট্রিক্যাল সেবা প্রদান করা। তারা আবাসিক এবং বাণিজ্যিক গ্রাহকদের জন্য নির্ভরযোগ্য, দক্ষ এবং আধুনিক প্রযুক্তি (যেমন- LED, EV চার্জার) ব্যবহারের মাধ্যমে গ্রাহকের চাহিদাকে সর্বোচ্চ অগ্রাধিকার দিয়ে দীর্ঘস্থায়ী সম্পর্ক স্থাপন করতে চায়', 'pending', '2026-04-20 03:38:32', NULL),
(3, 'মোঃ আরমান', 'খান', 'ইন্টেরিয়র ডিজাইন কোম্পানি', 'arman@shebabondhu.com', '01728734534', 'Interior Design', 'Baridhara', '09:00:00', '18:00:00', 'এটি একটি শীর্ষস্থানীয় ইন্টেরিয়র ফার্ম, যা নান্দনিক ডিজাইনে ফোকাস করে', 'approved', '2026-04-21 05:01:32', NULL),
(4, 'Nobin', 'Ahammed', 'এয়ারকন কেয়ার (AIRCONcare)', 'nobin@shebabondhu.com', '01832489454', 'AC Service', 'Badda', '09:00:00', '18:00:00', 'ঢাকা ভিত্তিক সেরা এসি মেরামত ও সার্ভিসিং প্রদানকারী,গ্রি, জেনারেল, ওয়ালটনের মতো ব্র্যান্ডের সার্ভিসিং প্রদান করে', 'approved', '2026-04-21 05:55:34', NULL),
(5, 'Kawser', 'Hossen', 'সিকিউরিটি সিস্টেম Provider', 'kawser@shebabondhu.com', '01934547567', 'Security System', 'Uttara', '09:00:00', '18:00:00', 'সিসিটিভি ক্যামেরা ইনস্টলেশনে বাংলাদেশের অন্যতম জনপ্রিয় কোম্পানি।', 'approved', '2026-04-21 06:01:16', NULL),
(6, 'শফিকুর', 'রহমান', 'গার্ডেনিং Provider', 'shafik@shebabondhu.com', '01932895643', 'Gardening', 'Tongi', '09:00:00', '18:00:00', 'রুফটপ, ইনডোর গার্ডেনিং এবং ল্যান্ডস্কেপিং', 'approved', '2026-04-21 06:04:22', NULL),
(7, 'হাফিজুর', 'রহমান', 'পারফেক্ট মেকানিক্স', 'hafiz@shebabondhu.com', '01398237407', 'Mechanic', 'Mohakhali', '09:00:00', '18:00:00', 'ন্ত্রপাতির শক্তি, স্থায়িত্ব এবং নিরাপত্তা নিশ্চিত করতে সিমুলেশন বা পরীক্ষা চালানো', 'approved', '2026-04-21 06:10:24', NULL),
(8, 'সুমন', '', 'জেনারেটর সার্ভিস কোম্পানি', 'sumon@shebabondhu.com', '01887362489', 'Generator Service', 'Keraniganj', '09:00:00', '18:00:00', '২০০৫ সাল থেকে ডিজেল, পেট্রোল, এলপিজি ও ইনভার্টার জেনারেটরের বিশ্বস্ত সরবরাহকারী ও মেরামতকারী প্রতিষ্ঠান।', 'approved', '2026-04-21 06:13:23', NULL),
(9, 'Shawon', 'Ahammed', 'ঢাকা সোলার লিমিটেড', 'shawon@shebabondhu.com', '01923892375', 'Solar Service', 'Mohammadpur', '09:00:00', '18:00:00', 'সোলার প্যানেল, হাইব্রিড ইনভার্টার, ব্যাটারি এবং স্ট্রিট লাইট আমদানিকারক ও সেবাদাতা প্রতিষ্ঠান।', 'approved', '2026-04-21 06:17:57', NULL),
(10, 'AR Raafi', '', 'Lift elevator service company in Bangladesh', 'raafi@shebabondhu.com', '01721386943', 'Lift Service', 'Gulshan', '09:00:00', '18:00:00', 'প্যাসেঞ্জার লিফট, হোম লিফট এবং কার্গো লিফট ইনস্টলেশন ও রক্ষণাবেক্ষণ ,যেকোনো ব্র্যান্ডের লিফটের জন্য ২৪/৭ সার্ভিসিং এবং মেইনটেন্যান্স।', 'approved', '2026-04-21 06:21:31', NULL),
(11, 'Shihab', 'Hossen', 'ওয়াটার ট্যাংক ক্লিনিং সেবা কোম্পানি', 'shihab@shebabondhu.com', '013789437589', 'Water Tank Cleaning', 'Bashundhara', '09:00:00', '18:00:00', 'ভ্যাকুয়াম ক্লিনারের ব্যবহার: শ্যাওলা, তলানি ও ময়লা দূর করা ।\r\nজীবাণুনাশক স্প্রে: ব্যাকটেরিয়ামুক্ত ও বিশুদ্ধ পানির নিশ্চয়তা ।\r\nদক্ষ কর্মী: অভিজ্ঞ ও ভেরিফাইড কর্মীবাহিনী দ্বারা কাজ সম্পাদন ।', 'approved', '2026-04-21 06:24:48', NULL),
(12, 'MD Abdullah', '', 'অ্যালুমিনিয়াম থাই গ্লাস বিডি', 'abdullah@shebabondhu.com', '01609482390', 'Glass & Aluminum', 'Mirpur', '09:00:00', '18:00:00', 'ধুনিক থাই অ্যালুমিনিয়াম, অফিস/বাসার গ্লাস পার্টিশন এবং ডিজাইনার গ্লাসের জন্য পরিচিত।', 'approved', '2026-04-21 06:27:27', NULL),
(13, 'মোঃ সারোয়ার', 'আলম', 'বাংলাদেশ প্যাকার্স অ্যান্ড মুভারস', 'jane@abc.in', '01723849374', 'Home Shifting', 'Dhanmondi', '09:00:00', '18:00:00', 'নির্ধারিত সময়ের মধ্যে গ্রাহকের মালামাল গন্তব্যে পৌঁছানো ।', 'approved', '2026-04-21 06:51:20', NULL),
(14, 'মোস্তাকিম', 'রহমান', 'ঢাকা অ্যাপ্লায়েন্স ডক্টর', 'mustakim@shebabondhu.com', '01798345734', 'Appliance Repair', 'Rampura', '09:00:00', '18:00:00', 'দক্ষ প্রকৌশলী ও স্বচ্ছতার সাথে রিপেয়ার সেবা প্রদান করে গ্রাহকের আস্থা অর্জন করা।', 'approved', '2026-04-21 06:56:24', NULL),
(15, 'Asanul Haque', 'Milon', 'রাজধানী মুভার্স অ্যান্ড প্যাকার্স', 'milon@shebabondhu.com', '01700034872', 'Home Shifting', 'Savar', '09:00:00', '18:00:00', 'এটি বাংলাদেশের একটি অন্যতম বাসা ও অফিস বদল করার সার্ভিস প্রদানকারী প্রতিষ্ঠান।', 'approved', '2026-04-27 03:25:44', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `reviews`
--

CREATE TABLE `reviews` (
  `id` int(10) UNSIGNED NOT NULL,
  `booking_id` int(10) UNSIGNED NOT NULL,
  `customer_id` int(10) UNSIGNED NOT NULL,
  `provider_id` int(10) UNSIGNED NOT NULL,
  `rating` tinyint(3) UNSIGNED NOT NULL CHECK (`rating` between 1 and 5),
  `comment` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `reviews`
--

INSERT INTO `reviews` (`id`, `booking_id`, `customer_id`, `provider_id`, `rating`, `comment`, `created_at`) VALUES
(1, 1, 2, 1, 5, 'চমৎকার কাজ! সব দ্রুত ঠিক করে পরিষ্কার করে গেলেন। খুব পেশাদার।', '2026-04-13 06:32:42'),
(2, 4, 3, 6, 5, 'অসাধারণ দক্ষতা। দরজা এখন নিখুঁতভাবে কাজ করছে। আবার নেব।', '2026-04-13 06:32:42'),
(3, 3, 2, 2, 5, 'Fantastic service!', '2026-04-20 05:06:06'),
(4, 6, 4, 12, 5, 'Fantastic service!', '2026-04-20 05:06:06'),
(5, 7, 2, 3, 5, 'Great service! Very professional and timely.', '2026-04-20 05:06:06'),
(6, 8, 2, 10, 4, 'Good service, will hire again.', '2026-04-20 05:06:06'),
(7, 10, 4, 10, 5, 'Amazing work! Will definitely use again.', '2026-04-20 05:07:23'),
(8, 11, 20, 4, 5, 'Highly skilled professionals!', '2026-04-20 05:07:23'),
(9, 12, 19, 5, 5, 'Professional and courteous.', '2026-04-20 05:07:23'),
(10, 13, 2, 1, 4, 'Good service, will hire again.', '2026-04-20 05:07:23'),
(11, 14, 3, 9, 5, 'Highly recommend!', '2026-04-20 05:07:23'),
(12, 15, 4, 8, 5, 'Very helpful and friendly.', '2026-04-20 05:07:23'),
(13, 16, 4, 13, 5, 'Best service in town!', '2026-04-20 05:07:23'),
(14, 17, 2, 5, 5, 'Very happy with the result.', '2026-04-20 05:07:23'),
(15, 18, 3, 2, 5, 'Highly recommend!', '2026-04-20 05:07:23'),
(16, 19, 22, 4, 5, 'Exceeded my expectations!', '2026-04-20 05:07:23'),
(17, 20, 23, 4, 5, 'Highly recommend!', '2026-04-20 05:07:23'),
(18, 21, 2, 6, 5, 'Wonderful experience!', '2026-04-20 05:07:23'),
(19, 22, 3, 10, 5, 'Highly skilled professionals!', '2026-04-20 05:07:23'),
(20, 23, 4, 13, 5, 'Highly recommend!', '2026-04-20 05:07:23'),
(21, 24, 21, 4, 5, 'Professional and courteous.', '2026-04-20 05:07:23'),
(22, 9, 2, 12, 5, 'পেস্ট অ্যাওয়ে বিডি শতভাগ দক্ষতার সাথে বাসা, অফিস ও সরকারি প্রতিষ্ঠানে পেস্ট কন্ট্রোল সার্ভিস দিয়ে থাকে। দক্ষ কর্মী, আধুনিক পেস্ট কন্ট্রোল সিস্টেম এবং মানসম্পন্ন কাজ।', '2026-04-26 05:07:18');

-- --------------------------------------------------------

--
-- Table structure for table `service_categories`
--

CREATE TABLE `service_categories` (
  `id` int(10) UNSIGNED NOT NULL,
  `name_en` varchar(80) NOT NULL,
  `name_bn` varchar(100) NOT NULL,
  `emoji` varchar(10) NOT NULL DEFAULT '?',
  `description` text DEFAULT NULL,
  `manpower` int(11) DEFAULT 1,
  `active` tinyint(1) NOT NULL DEFAULT 1,
  `sort_order` smallint(6) NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `service_categories`
--

INSERT INTO `service_categories` (`id`, `name_en`, `name_bn`, `emoji`, `description`, `manpower`, `active`, `sort_order`, `created_at`) VALUES
(1, 'Plumbing', 'পাইপলাইন', '🚿', NULL, 1, 1, 1, '2026-04-13 06:32:42'),
(2, 'Electricals', 'ইলেকট্রিক্যাল', '⚡', NULL, 1, 1, 2, '2026-04-13 06:32:42'),
(3, 'Carpentry', 'কাঠমিস্ত্রি', '🪚', NULL, 1, 1, 3, '2026-04-13 06:32:42'),
(4, 'Cleaning', 'পরিষ্কার', '🧹', NULL, 1, 1, 4, '2026-04-13 06:32:42'),
(5, 'Pest Control', 'পেস্ট কন্ট্রোল', '🔫', NULL, 1, 1, 5, '2026-04-13 06:32:42'),
(6, 'Mechanic', 'মেকানিক', '🔩', NULL, 1, 1, 6, '2026-04-13 06:32:42'),
(7, 'Painter', 'রং মিস্ত্রি', '🎨', NULL, 1, 1, 7, '2026-04-13 06:32:42'),
(8, 'AC Service', 'এসি সার্ভিস', '❄️', '', 3, 1, 8, '2026-04-13 06:32:42'),
(9, 'Appliance Repair', 'অ্যাপ্লায়েন্স রিপেয়ার', '🧰', NULL, 1, 1, 9, '2026-04-13 06:32:42'),
(10, 'Home Shifting', 'বাসা বদল', '🚚', '', 2, 1, 10, '2026-04-13 06:32:42'),
(11, 'Gardening', 'গার্ডেনিং', '🌿', NULL, 1, 1, 11, '2026-04-13 06:32:42'),
(12, 'Security System', 'সিকিউরিটি সিস্টেম', '🔐', '', 1, 1, 12, '2026-04-13 06:32:42'),
(13, 'Interior Design', 'ইন্টেরিয়র ডিজাইন', '🏡', NULL, 1, 1, 13, '2026-04-13 06:32:42'),
(14, 'Glass & Aluminum', 'গ্লাস ও অ্যালুমিনিয়াম', '🪟', NULL, 1, 1, 14, '2026-04-13 06:32:42'),
(15, 'Water Tank Cleaning', 'ওয়াটার ট্যাংক ক্লিনিং', '🚰', NULL, 1, 1, 15, '2026-04-13 06:32:42'),
(16, 'Generator Service', 'জেনারেটর সার্ভিস', '🔋', NULL, 1, 1, 16, '2026-04-13 06:32:42'),
(17, 'Lift Service', 'লিফট সার্ভিস', '🛗', NULL, 1, 1, 17, '2026-04-13 06:32:42'),
(18, 'Solar Service', 'সোলার সার্ভিস', '☀️', '', 1, 1, 18, '2026-04-13 06:32:42');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(10) UNSIGNED NOT NULL,
  `name` varchar(120) NOT NULL,
  `email` varchar(191) NOT NULL,
  `password` varchar(255) NOT NULL,
  `phone` varchar(30) DEFAULT NULL,
  `role` enum('customer','provider','admin') NOT NULL DEFAULT 'customer',
  `avatar` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `photo` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `name`, `email`, `password`, `phone`, `role`, `avatar`, `created_at`, `photo`) VALUES
(1, 'সুপার অ্যাডমিন', 'admin@handygo.com', '$2y$10$qW/GbShcHQ0alGCbTuaxeeulkDTupQkh2nF2Rnan3M8hJQiuJEuG2', NULL, 'admin', NULL, '2026-04-13 06:32:42', NULL),
(2, 'রাহেলা বেগম', 'rahela@example.com', '$2y$10$cbpFu/UESyqdoOCiX2169OSuEKe4sOnBHSgII8tbYlSYKVmrEJaBW', '01711111111', 'customer', NULL, '2026-04-13 06:32:42', 'uploads/2_1777100260.jpg'),
(3, 'করিম সাহেব', 'karim@example.com', '$2y$10$cbpFu/UESyqdoOCiX2169OSuEKe4sOnBHSgII8tbYlSYKVmrEJaBW', '01722222222', 'customer', NULL, '2026-04-13 06:32:42', NULL),
(4, 'নুসরাত জাহান', 'nusrat@example.com', '$2y$10$cbpFu/UESyqdoOCiX2169OSuEKe4sOnBHSgII8tbYlSYKVmrEJaBW', '01733333333', 'customer', NULL, '2026-04-13 06:32:42', NULL),
(5, 'রয়্যাল সার্ভিস', 'royal@handygo.com', '$2y$10$cbpFu/UESyqdoOCiX2169OSuEKe4sOnBHSgII8tbYlSYKVmrEJaBW', '01811111111', 'provider', NULL, '2026-04-13 06:32:42', NULL),
(6, 'টাইডি হোমস', 'tidy@handygo.com', '$2y$10$cbpFu/UESyqdoOCiX2169OSuEKe4sOnBHSgII8tbYlSYKVmrEJaBW', '01822222222', 'provider', NULL, '2026-04-13 06:32:42', NULL),
(7, 'সুইফট ইলেকট্রিক', 'swift@handygo.com', '$2y$10$cbpFu/UESyqdoOCiX2169OSuEKe4sOnBHSgII8tbYlSYKVmrEJaBW', '01833333333', 'provider', NULL, '2026-04-13 06:32:42', NULL),
(8, 'পারফেক্ট হোমস', 'perfect@handygo.com', '$2y$10$cbpFu/UESyqdoOCiX2169OSuEKe4sOnBHSgII8tbYlSYKVmrEJaBW', '01844444444', 'provider', NULL, '2026-04-13 06:32:42', NULL),
(9, 'ঢাকা পেস্ট', 'pest@handygo.com', '$2y$10$cbpFu/UESyqdoOCiX2169OSuEKe4sOnBHSgII8tbYlSYKVmrEJaBW', '01855555555', 'provider', NULL, '2026-04-13 06:32:42', NULL),
(10, 'মাস্টার কার্পেন্টার', 'carpen@handygo.com', '$2y$10$cbpFu/UESyqdoOCiX2169OSuEKe4sOnBHSgII8tbYlSYKVmrEJaBW', '01866666666', 'provider', NULL, '2026-04-13 06:32:42', NULL),
(11, 'অটো ফিক্স', 'auto@handygo.com', '$2y$10$cbpFu/UESyqdoOCiX2169OSuEKe4sOnBHSgII8tbYlSYKVmrEJaBW', '01877777777', 'provider', NULL, '2026-04-13 06:32:42', NULL),
(12, 'কালার প্রো', 'color@handygo.com', '$2y$10$cbpFu/UESyqdoOCiX2169OSuEKe4sOnBHSgII8tbYlSYKVmrEJaBW', '01888888888', 'provider', NULL, '2026-04-13 06:32:42', NULL),
(13, 'আজিমপুর ইলেকট্রিক', 'azimpur@handygo.com', '$2y$10$cbpFu/UESyqdoOCiX2169OSuEKe4sOnBHSgII8tbYlSYKVmrEJaBW', '01899999999', 'provider', NULL, '2026-04-13 06:32:42', NULL),
(14, 'গ্রিন ক্লিন', 'green@handygo.com', '$2y$10$cbpFu/UESyqdoOCiX2169OSuEKe4sOnBHSgII8tbYlSYKVmrEJaBW', '01900000001', 'provider', NULL, '2026-04-13 06:32:42', NULL),
(15, 'মিরপুর প্লাম্ব প্রো', 'mirpur@handygo.com', '$2y$10$cbpFu/UESyqdoOCiX2169OSuEKe4sOnBHSgII8tbYlSYKVmrEJaBW', '01900000002', 'provider', NULL, '2026-04-13 06:32:42', NULL),
(16, 'পেস্ট অ্যাওয়ে বিডি', 'pestaway@handygo.com', '$2y$10$cbpFu/UESyqdoOCiX2169OSuEKe4sOnBHSgII8tbYlSYKVmrEJaBW', '01900000003', 'provider', NULL, '2026-04-13 06:32:42', NULL),
(17, 'MD Mannan Bhuiyan', 'mannanbhuiyan9090@gmail.com', '$2y$10$cbpFu/UESyqdoOCiX2169OSuEKe4sOnBHSgII8tbYlSYKVmrEJaBW', '01783383402', 'provider', NULL, '2026-04-20 03:44:41', NULL),
(18, 'Amin Khan', 'amin@shebabondhu.com', '$2y$10$cbpFu/UESyqdoOCiX2169OSuEKe4sOnBHSgII8tbYlSYKVmrEJaBW', '01900000000', 'customer', NULL, '2026-04-20 04:43:15', NULL),
(19, 'Hassan Mahmudh', 'hassan@shebabondhu.com', '$2y$10$cbpFu/UESyqdoOCiX2169OSuEKe4sOnBHSgII8tbYlSYKVmrEJaBW', '01900000000', 'customer', NULL, '2026-04-20 04:43:15', NULL),
(20, 'Rojina Akter', 'rojina@shebabondhu.com', '$2y$10$cbpFu/UESyqdoOCiX2169OSuEKe4sOnBHSgII8tbYlSYKVmrEJaBW', '01900000000', 'customer', NULL, '2026-04-20 04:43:15', NULL),
(21, 'Abdul Gaffer', 'gaffer@shebabondhu.com', '$2y$10$cbpFu/UESyqdoOCiX2169OSuEKe4sOnBHSgII8tbYlSYKVmrEJaBW', '01900000000', 'customer', NULL, '2026-04-20 04:43:15', NULL),
(22, 'Rofik Mawlana', 'rafik@shebabondhu.com', '$2y$10$cbpFu/UESyqdoOCiX2169OSuEKe4sOnBHSgII8tbYlSYKVmrEJaBW', '01900000000', 'customer', NULL, '2026-04-20 04:43:15', NULL),
(23, 'Karima Rahman', 'karima@shebabondhu.com', '$2y$10$cbpFu/UESyqdoOCiX2169OSuEKe4sOnBHSgII8tbYlSYKVmrEJaBW', '01900000000', 'customer', NULL, '2026-04-20 04:43:15', NULL),
(24, 'মোঃ আরমান খান', 'arman@shebabondhu.com', '$2y$10$cbpFu/UESyqdoOCiX2169OSuEKe4sOnBHSgII8tbYlSYKVmrEJaBW', '01728734534', 'provider', NULL, '2026-04-21 05:02:03', NULL),
(25, 'Nobin Ahammed', 'nobin@shebabondhu.com', '$2y$10$cbpFu/UESyqdoOCiX2169OSuEKe4sOnBHSgII8tbYlSYKVmrEJaBW', '01832489454', 'provider', NULL, '2026-04-21 05:55:56', NULL),
(26, 'শফিকুর রহমান', 'shafik@shebabondhu.com', '$2y$10$cbpFu/UESyqdoOCiX2169OSuEKe4sOnBHSgII8tbYlSYKVmrEJaBW', '01932895643', 'provider', NULL, '2026-04-21 06:04:37', NULL),
(27, 'Kawser Hossen', 'kawser@shebabondhu.com', '$2y$10$cbpFu/UESyqdoOCiX2169OSuEKe4sOnBHSgII8tbYlSYKVmrEJaBW', '01934547567', 'provider', NULL, '2026-04-21 06:04:39', NULL),
(28, 'সুমন', 'sumon@shebabondhu.com', '$2y$10$cbpFu/UESyqdoOCiX2169OSuEKe4sOnBHSgII8tbYlSYKVmrEJaBW', '01887362489', 'provider', NULL, '2026-04-21 06:13:55', NULL),
(29, 'হাফিজুর রহমান', 'hafiz@shebabondhu.com', '$2y$10$cbpFu/UESyqdoOCiX2169OSuEKe4sOnBHSgII8tbYlSYKVmrEJaBW', '01398237407', 'provider', NULL, '2026-04-21 06:13:57', NULL),
(30, 'MD Abdullah', 'abdullah@shebabondhu.com', '$2y$10$cbpFu/UESyqdoOCiX2169OSuEKe4sOnBHSgII8tbYlSYKVmrEJaBW', '01609482390', 'provider', NULL, '2026-04-21 06:27:44', NULL),
(31, 'Shihab Hossen', 'shihab@shebabondhu.com', '$2y$10$cbpFu/UESyqdoOCiX2169OSuEKe4sOnBHSgII8tbYlSYKVmrEJaBW', '013789437589', 'provider', NULL, '2026-04-21 06:27:46', NULL),
(32, 'AR Raafi', 'raafi@shebabondhu.com', '$2y$10$cbpFu/UESyqdoOCiX2169OSuEKe4sOnBHSgII8tbYlSYKVmrEJaBW', '01721386943', 'provider', NULL, '2026-04-21 06:27:49', NULL),
(33, 'Shawon Ahammed', 'shawon@shebabondhu.com', '$2y$10$cbpFu/UESyqdoOCiX2169OSuEKe4sOnBHSgII8tbYlSYKVmrEJaBW', '01923892375', 'provider', NULL, '2026-04-21 06:27:55', NULL),
(34, 'মোস্তাকিম রহমান', 'mustakim@shebabondhu.com', '$2y$10$cbpFu/UESyqdoOCiX2169OSuEKe4sOnBHSgII8tbYlSYKVmrEJaBW', '01798345734', 'provider', NULL, '2026-04-21 06:58:17', NULL),
(35, 'মোঃ সারোয়ার আলম', 'jane@abc.in', '$2y$10$cbpFu/UESyqdoOCiX2169OSuEKe4sOnBHSgII8tbYlSYKVmrEJaBW', '01723849374', 'provider', NULL, '2026-04-21 06:58:34', NULL),
(36, 'Asanul Haque Milon', 'milon@shebabondhu.com', '$2y$10$cbpFu/UESyqdoOCiX2169OSuEKe4sOnBHSgII8tbYlSYKVmrEJaBW', '01700034872', 'provider', NULL, '2026-04-27 03:59:16', NULL);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `bookings`
--
ALTER TABLE `bookings`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uk_ref` (`ref_code`),
  ADD KEY `idx_status` (`status`),
  ADD KEY `idx_cust` (`customer_id`),
  ADD KEY `idx_prov` (`provider_id`);

--
-- Indexes for table `contact_messages`
--
ALTER TABLE `contact_messages`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `notification`
--
ALTER TABLE `notification`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `providers`
--
ALTER TABLE `providers`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_prov_user` (`user_id`),
  ADD KEY `idx_svc_type` (`service_type`),
  ADD KEY `idx_city` (`city`),
  ADD KEY `idx_verified` (`verified`);

--
-- Indexes for table `provider_applications`
--
ALTER TABLE `provider_applications`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_app_status` (`status`);

--
-- Indexes for table `reviews`
--
ALTER TABLE `reviews`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uk_booking` (`booking_id`),
  ADD KEY `fk_rev_cust` (`customer_id`),
  ADD KEY `idx_rev_prov` (`provider_id`);

--
-- Indexes for table `service_categories`
--
ALTER TABLE `service_categories`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uk_name_en` (`name_en`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uk_email` (`email`),
  ADD KEY `idx_role` (`role`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `bookings`
--
ALTER TABLE `bookings`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=35;

--
-- AUTO_INCREMENT for table `contact_messages`
--
ALTER TABLE `contact_messages`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `notification`
--
ALTER TABLE `notification`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT for table `providers`
--
ALTER TABLE `providers`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=38;

--
-- AUTO_INCREMENT for table `provider_applications`
--
ALTER TABLE `provider_applications`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT for table `reviews`
--
ALTER TABLE `reviews`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=23;

--
-- AUTO_INCREMENT for table `service_categories`
--
ALTER TABLE `service_categories`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=37;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `bookings`
--
ALTER TABLE `bookings`
  ADD CONSTRAINT `fk_book_cust` FOREIGN KEY (`customer_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_book_prov` FOREIGN KEY (`provider_id`) REFERENCES `providers` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `providers`
--
ALTER TABLE `providers`
  ADD CONSTRAINT `fk_prov_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `reviews`
--
ALTER TABLE `reviews`
  ADD CONSTRAINT `fk_rev_book` FOREIGN KEY (`booking_id`) REFERENCES `bookings` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_rev_cust` FOREIGN KEY (`customer_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_rev_prov` FOREIGN KEY (`provider_id`) REFERENCES `providers` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
