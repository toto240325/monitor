-- phpMyAdmin SQL Dump
-- version 4.7.0
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Oct 03, 2018 at 12:56 AM
-- Server version: 10.1.25-MariaDB
-- PHP Version: 7.1.7

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `mydatabase`
--

-- --------------------------------------------------------

--
-- Table structure for table `db`
--

CREATE TABLE `db` (
  `name` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `event`
--

CREATE TABLE `event` (
  `event_id` int(11) NOT NULL,
  `event_time` datetime DEFAULT NULL,
  `event_host` varchar(20) NOT NULL,
  `event_text` varchar(255) DEFAULT NULL,
  `event_type` varchar(20) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `fgw`
--

CREATE TABLE `fgw` (
  `fgw_id` int(11) NOT NULL,
  `fgw_time` datetime DEFAULT NULL,
  `fgw_host` varchar(20) NOT NULL,
  `fgw_title` varchar(255) DEFAULT NULL,
  `fgw_cpu` float DEFAULT NULL,
  `duration` int(11) DEFAULT NULL,
  `fgw_duration` int(11) DEFAULT NULL,
  `fgw_isgame` tinyint(1) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `gametime`
--

CREATE TABLE `gametime` (
  `id` int(11) NOT NULL,
  `nbMin` int(11) NOT NULL,
  `date` date NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `keywords`
--

CREATE TABLE `keywords` (
  `id` int(11) NOT NULL,
  `keyword` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `temp`
--

CREATE TABLE `temp` (
  `temp_id` int(11) NOT NULL,
  `temp_temp` float DEFAULT NULL,
  `temp_time` datetime DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `upload`
--

CREATE TABLE `upload` (
  `id` int(11) NOT NULL,
  `camera` varchar(15) NOT NULL,
  `name` varchar(30) NOT NULL,
  `type` varchar(30) NOT NULL,
  `size` int(11) NOT NULL,
  `content` mediumblob NOT NULL,
  `upload_time` datetime NOT NULL,
  `PIR_detection` tinyint(1) NOT NULL,
  `ultrasonic_detection` tinyint(1) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `event`
--
ALTER TABLE `event`
  ADD PRIMARY KEY (`event_id`),
  ADD KEY `event_id` (`event_id`),
  ADD KEY `host` (`event_host`);

--
-- Indexes for table `fgw`
--
ALTER TABLE `fgw`
  ADD PRIMARY KEY (`fgw_id`),
  ADD KEY `idx_fgw_fgw_title` (`fgw_title`),
  ADD KEY `idx_fgw_fgw_time` (`fgw_time`),
  ADD KEY `myindex` (`fgw_host`),
  ADD KEY `fgw_isgame` (`fgw_isgame`);

--
-- Indexes for table `gametime`
--
ALTER TABLE `gametime`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id` (`id`),
  ADD KEY `date` (`date`);

--
-- Indexes for table `keywords`
--
ALTER TABLE `keywords`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `temp`
--
ALTER TABLE `temp`
  ADD PRIMARY KEY (`temp_id`);

--
-- Indexes for table `upload`
--
ALTER TABLE `upload`
  ADD PRIMARY KEY (`id`),
  ADD KEY `camera` (`camera`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `event`
--
ALTER TABLE `event`
  MODIFY `event_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=132;
--
-- AUTO_INCREMENT for table `fgw`
--
ALTER TABLE `fgw`
  MODIFY `fgw_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1407434;
--
-- AUTO_INCREMENT for table `gametime`
--
ALTER TABLE `gametime`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=39;
--
-- AUTO_INCREMENT for table `keywords`
--
ALTER TABLE `keywords`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;
--
-- AUTO_INCREMENT for table `temp`
--
ALTER TABLE `temp`
  MODIFY `temp_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8884;
--
-- AUTO_INCREMENT for table `upload`
--
ALTER TABLE `upload`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1132;COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
