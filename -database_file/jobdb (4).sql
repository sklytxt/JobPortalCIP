-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jun 23, 2026 at 10:52 PM
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
-- Database: `jobdb`
--

-- --------------------------------------------------------

--
-- Table structure for table `applications`
--

CREATE TABLE `applications` (
  `ApplicationID` int(11) NOT NULL,
  `JobID` int(11) NOT NULL,
  `ApplicantID` int(11) NOT NULL,
  `ResumePath` varchar(255) NOT NULL,
  `AppliedDate` timestamp NOT NULL DEFAULT current_timestamp(),
  `PortfolioPath` varchar(255) DEFAULT NULL,
  `Status` enum('Pending','Accepted','Rejected') DEFAULT 'Pending',
  `EmployerID` int(11) DEFAULT NULL,
  `StatusDate` datetime DEFAULT NULL,
  `coverletter` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `applications`
--

INSERT INTO `applications` (`ApplicationID`, `JobID`, `ApplicantID`, `ResumePath`, `AppliedDate`, `PortfolioPath`, `Status`, `EmployerID`, `StatusDate`, `coverletter`) VALUES
(1, 1, 2, 'sample_resume.pdf', '2026-06-22 10:24:55', 'https://github.com/example', 'Rejected', 3, NULL, ''),
(2, 4, 6, '1782236246_6_9.1 RIP.pdf', '2026-06-23 17:37:26', NULL, 'Rejected', 5, NULL, ''),
(6, 4, 6, '1782239174_6_9.1 RIP.pdf', '2026-06-23 18:26:14', NULL, 'Rejected', 5, '2026-06-24 02:26:22', ''),
(8, 19, 9, '1782247162_9_Gary.docx', '2026-06-23 20:39:22', NULL, 'Rejected', 10, '2026-06-24 04:39:47', '1782247162_9_Gary.docx'),
(9, 20, 9, '1782247805_9_Gary.docx', '2026-06-23 20:50:05', NULL, 'Accepted', 10, '2026-06-24 04:50:56', '1782247805_9_Gary.docx');

-- --------------------------------------------------------

--
-- Table structure for table `jobs`
--

CREATE TABLE `jobs` (
  `JobID` int(11) NOT NULL,
  `EmployerID` int(11) NOT NULL,
  `JobTitle` varchar(255) NOT NULL,
  `Description` text NOT NULL,
  `JobType` enum('Full-Time','Part-Time','Contract','Internship','Freelance') NOT NULL,
  `WorkSetup` enum('On-Site','Remote','Hybrid') NOT NULL,
  `ExperienceLevel` varchar(50) DEFAULT NULL,
  `Location` varchar(255) DEFAULT NULL,
  `PostedDate` timestamp NOT NULL DEFAULT current_timestamp(),
  `Salary` varchar(255) DEFAULT NULL,
  `Status` varchar(20) DEFAULT 'Open',
  `MaxApplicants` int(11) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `jobs`
--

INSERT INTO `jobs` (`JobID`, `EmployerID`, `JobTitle`, `Description`, `JobType`, `WorkSetup`, `ExperienceLevel`, `Location`, `PostedDate`, `Salary`, `Status`, `MaxApplicants`) VALUES
(1, 3, 'Backend Developer', 'backend', 'Full-Time', 'Hybrid', 'Senior-Level', 'Caloocan Manila', '2026-06-22 10:23:57', '90000', 'Open', 1),
(3, 3, 'Senior Front End', 'The Senior Front End Developer will lead the design and implementation of highly responsive, user-centric interfaces that elevate the overall digital experience of our web platforms. You will be responsible for translating complex design mockups into clean, maintainable, and high-performance code while collaborating closely with backend engineers to integrate seamless API solutions. Beyond technical execution, you will mentor junior team members, enforce best practices for component-based development, and advocate for accessibility and performance optimizations throughout the software development lifecycle. This role requires a deep understanding of modern JavaScript frameworks, a sharp eye for aesthetic detail, and the ability to proactively solve intricate UI challenges in a fast-paced environment.', 'Internship', 'Hybrid', 'Senior-Level', 'Metropolis', '2026-06-23 10:32:14', '50000', 'Open', 3),
(4, 5, 'HR Director', 'This position reports to the Human Resources (HR) director and\r\ninterfaces with company managers and HR staff. Company XYZ is\r\ncommitted to an employee-orientated, high performance culture that\r\nemphasizes empowerment, quality, continuous improvement, and the\r\nrecruitment and ongoing development of a superior workforce.', 'Freelance', 'Hybrid', 'Senior-Level', 'Metro Manila', '2026-06-23 16:37:58', '20000', 'Open', 3),
(19, 10, 'sdfsdfsdf', 'asfasf', 'Full-Time', 'Hybrid', 'Mid-Level', 'safasfasa', '2026-06-23 20:38:51', '123123', 'Open', 1),
(20, 10, 'HR Director', 'asd', 'Full-Time', 'On-Site', 'Entry-Level', 'Metro Manilaaaa', '2026-06-23 20:49:47', '5000', 'Filled', 1);

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `UserID` int(11) NOT NULL,
  `FullName` varchar(100) NOT NULL,
  `Email` varchar(150) NOT NULL,
  `Password` varchar(255) NOT NULL,
  `Usertype` varchar(255) NOT NULL,
  `Location` varchar(200) DEFAULT NULL,
  `JobTitle` varchar(100) DEFAULT NULL,
  `CompanyName` varchar(255) DEFAULT NULL,
  `ContactNumber` varchar(20) DEFAULT NULL,
  `ProfileImagePath` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`UserID`, `FullName`, `Email`, `Password`, `Usertype`, `Location`, `JobTitle`, `CompanyName`, `ContactNumber`, `ProfileImagePath`) VALUES
(1, 'Jakeuavious Max', 'employer1@mail.com', '$2y$10$hzsrMRNv8BK1P0nuZu2Nh./Dj51yI8e/EBYEP5zbMNbTvu0pFreeG', 'employer', 'Caloocan Manila', NULL, 'company', '0916-237-0788', '1782107042_background1.jpg'),
(2, 'Ben', 'dorime@mail.com', '$2y$10$9QR9O4Tm.wxdxxBASOHgEeMmU8l5k5PDkxScciBB3LDz8sX52s8Tq', 'jobseeker', 'Metropolis', 'Jobs', NULL, '1231-1234-1234', '1782109401_background.avif'),
(3, 'Khurt Reboredo', 'minecraftme321@gmail.com', '$2y$10$Cs2kUbB40Nnhq/q63LS6quWK7S5.vxaGwAnUYKG2SgTVyMDU5Hko2', 'employer', 'Caloocan Manila', NULL, 'Khurt Industries', '412442144214', '1782118769_wp10148832-lo-fi-autumn-wallpapers.jpg'),
(4, 'Khurt Reboredo', 'jackpassword@gmail.com', '$2y$10$ziDpkgzegsIg8PEVkSwVaOpWU5IRu5GaNizbalZ0iAFTvlplcS5I6', 'jobseeker', 'Caloocan Manila', '', NULL, '0000-0000-0000', '1782214185_pre-quantization.jpg'),
(5, 'Manster Jones', 'mail@god.com', '$2y$10$Ui8Syo9fYI6eTNUv1yW.geF1yj9DdeviFZ0b9UfifH2Zpb3ioaJBi', 'employer', 'Metro Manila', NULL, 'Company Generika', '09162370788', '1782246769_Screenshot 2026-04-30 192938.png'),
(6, 'Kirsten Kush', 'jobseeker@mail.com', '$2y$10$0bC/EogmPTIPDNyM18JJYuvr8xkHjxODqd8pddrjeZXnoVs0vOylG', 'jobseeker', 'Metro Manila', 'Backend Developer', NULL, '09162370788', '1782235285_Screenshot 2026-05-14 205740.png'),
(7, 'Khurt Reboredo', 'minecraftme321@gmail.comd', '$2y$10$ipht.nZlOjD3bSWxowGBA.XF8qJOpLg6RpFyBuChyDu75prsfIW.6', 'jobseeker', 'Metro Manila', 'Backend Developer', NULL, '09162370788', '1782245120_background1.jpg'),
(8, 'Acc Acc', 'asdasd@asdsad.com', '$2y$10$YAN/SxymS4OCiws5kx4JxebW4DX52bB920rf5fytKpUPvvgmwk.zK', 'jobseeker', 'Metro Manila', 'Backend Developer', NULL, '09162370788', NULL),
(9, 'mal', 'fasf@fasf.com', '$2y$10$3qaW4vlLmWhP5qJfdbQhMOCJYZ1XSd1QRDEdU6O.zp7Qma5SXQUiC', 'jobseeker', 'Metro Manila', 'Tigusher', NULL, '09162370788', '1782246827_Screenshot_2026-04-29_175954.png'),
(10, 'Khurt Reboredo', 'reboreoko@ko.com', '$2y$10$V/RUc5GfyuNK0slmhaFqW.hWaAe6.svI6G9.A03fmWc6Q63i/Qdp2', 'employer', 'Jakville', NULL, 'Company Generika', '09162370788', '1782246962_image_2026-05-30_165021249.png');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `applications`
--
ALTER TABLE `applications`
  ADD PRIMARY KEY (`ApplicationID`),
  ADD KEY `JobID` (`JobID`),
  ADD KEY `ApplicantID` (`ApplicantID`);

--
-- Indexes for table `jobs`
--
ALTER TABLE `jobs`
  ADD PRIMARY KEY (`JobID`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`UserID`),
  ADD UNIQUE KEY `Email` (`Email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `applications`
--
ALTER TABLE `applications`
  MODIFY `ApplicationID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `jobs`
--
ALTER TABLE `jobs`
  MODIFY `JobID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `UserID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `applications`
--
ALTER TABLE `applications`
  ADD CONSTRAINT `applications_ibfk_1` FOREIGN KEY (`JobID`) REFERENCES `jobs` (`JobID`) ON DELETE CASCADE,
  ADD CONSTRAINT `applications_ibfk_2` FOREIGN KEY (`ApplicantID`) REFERENCES `users` (`UserID`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
