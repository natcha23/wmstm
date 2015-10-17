-- phpMyAdmin SQL Dump
-- version 4.0.10
-- http://www.phpmyadmin.net
--
-- Host: localhost
-- Generation Time: Jun 17, 2015 at 02:42 PM
-- Server version: 5.1.71
-- PHP Version: 5.3.27

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- Database: `main_tu`
--

-- --------------------------------------------------------

--
-- Table structure for table `ztest_data`
--

CREATE TABLE IF NOT EXISTS `ztest_data` (
  `id_pk` int(11) NOT NULL AUTO_INCREMENT,
  `program_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `name` varchar(200) NOT NULL,
  `desc` text NOT NULL,
  `date_create` datetime NOT NULL,
  `date_update` datetime NOT NULL,
  `date` date NOT NULL,
  `status` int(11) NOT NULL,
  PRIMARY KEY (`id_pk`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=6 ;

--
-- Dumping data for table `ztest_data`
--

INSERT INTO `ztest_data` (`id_pk`, `program_id`, `user_id`, `name`, `desc`, `date_create`, `date_update`, `date`, `status`) VALUES
(1, 44, 0, 'eofficeonline', '', '2015-06-17 13:48:44', '2015-06-17 13:48:44', '2015-06-24', 0),
(2, 44, 0, 'eofficeonline', '', '2015-06-17 13:51:17', '2015-06-17 13:51:17', '2015-06-18', 0),
(3, 44, 0, 'support@eoffice7.com', '', '2015-06-17 13:52:00', '2015-06-17 13:55:23', '2015-06-23', 1),
(4, 45, 0, 'eofficeonline', '', '2015-06-17 14:04:58', '2015-06-17 14:30:22', '2015-06-03', 0),
(5, 45, 0, 'eofficeonline', '', '2015-06-17 14:08:45', '2015-06-17 14:08:45', '2015-06-23', 0);

-- --------------------------------------------------------

--
-- Table structure for table `ztest_files`
--

CREATE TABLE IF NOT EXISTS `ztest_files` (
  `id_auto` int(11) NOT NULL AUTO_INCREMENT,
  `file_id` int(11) NOT NULL,
  `program_id` int(11) NOT NULL,
  `id_pk` int(11) NOT NULL,
  `date_create` datetime NOT NULL,
  `date_update` datetime NOT NULL,
  `status` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id_auto`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=6 ;

--
-- Dumping data for table `ztest_files`
--

INSERT INTO `ztest_files` (`id_auto`, `file_id`, `program_id`, `id_pk`, `date_create`, `date_update`, `status`) VALUES
(1, 325, 44, 1, '2015-06-17 00:00:00', '0000-00-00 00:00:00', 0),
(2, 326, 44, 2, '2015-06-17 00:00:00', '0000-00-00 00:00:00', 0),
(3, 327, 44, 3, '2015-06-17 00:00:00', '0000-00-00 00:00:00', 1),
(4, 329, 45, 4, '2015-06-17 14:04:58', '2015-06-17 14:04:58', 0),
(5, 330, 45, 5, '2015-06-17 14:08:45', '2015-06-17 14:08:45', 0);

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
