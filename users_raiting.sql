-- phpMyAdmin SQL Dump
-- version 4.3.13.1
-- http://www.phpmyadmin.net
--
-- Host: localhost
-- Generation Time: Mar 03, 2016 at 12:20 AM
-- Server version: 5.5.47-MariaDB-1ubuntu0.14.04.1
-- PHP Version: 5.5.9-1ubuntu4.14

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- Database: `users_raiting`
--

-- --------------------------------------------------------

--
-- Table structure for table `raiting_user`
--

CREATE TABLE IF NOT EXISTS `raiting_user` (
  `id` int(11) NOT NULL,
  `user` int(11) NOT NULL,
  `score` int(11) NOT NULL
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

-- --------------------------------------------------------

--
-- Table structure for table `raiting_user_index`
--

CREATE TABLE IF NOT EXISTS `raiting_user_index` (
  `id` int(11) NOT NULL,
  `user` int(11) NOT NULL,
  `score` int(11) NOT NULL,
  `update_at` datetime NOT NULL,
  `last_update` datetime NOT NULL,
  `update_date` date NOT NULL
) ENGINE=InnoDB AUTO_INCREMENT=106 DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `raiting_user`
--
ALTER TABLE `raiting_user`
  ADD PRIMARY KEY (`id`), ADD KEY `user` (`user`), ADD KEY `score` (`score`);

--
-- Indexes for table `raiting_user_index`
--
ALTER TABLE `raiting_user_index`
  ADD PRIMARY KEY (`id`), ADD KEY `score` (`score`), ADD KEY `user` (`user`), ADD KEY `update_at` (`update_at`), ADD KEY `update_at_2` (`update_at`), ADD KEY `last_update` (`last_update`), ADD KEY `update_date` (`update_date`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `raiting_user`
--
ALTER TABLE `raiting_user`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=7;
--
-- AUTO_INCREMENT for table `raiting_user_index`
--
ALTER TABLE `raiting_user_index`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=106;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
