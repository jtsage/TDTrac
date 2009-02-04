-- phpMyAdmin SQL Dump
-- version 2.11.8.1deb1
-- http://www.phpmyadmin.net
--
-- Host: localhost
-- Generation Time: Jan 18, 2009 at 08:46 PM
-- Server version: 5.0.67
-- PHP Version: 5.2.6-2ubuntu4

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";

--
-- Database: `tdtrac`
--

-- --------------------------------------------------------

--
-- Table structure for table `budget`
--

CREATE TABLE IF NOT EXISTS `budget` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `showid` smallint(5) unsigned NOT NULL,
  `price` float NOT NULL,
  `vendor` varchar(35) NOT NULL,
  `dscr` varchar(65) NOT NULL,
  `date` date NOT NULL,
  PRIMARY KEY  (`id`),
  KEY `showid` (`showid`,`vendor`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1;

--
-- Dumping data for table `budget`
--

-- --------------------------------------------------------

--
-- Table structure for table `groupnames`
--

CREATE TABLE IF NOT EXISTS `groupnames` (
  `groupid` tinyint(4) unsigned NOT NULL auto_increment,
  `groupname` varchar(15) NOT NULL,
  PRIMARY KEY  (`groupid`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=100 ;

--
-- Dumping data for table `groupnames`
--

INSERT INTO `groupnames` (`groupid`, `groupname`) VALUES
(1, 'admin'),
(2, 'atd'),
(3, 'viewbudget'),
(4, 'employee'),
(99, 'guests');

-- --------------------------------------------------------

--
-- Table structure for table `hours`
--

CREATE TABLE IF NOT EXISTS `hours` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `userid` smallint(5) unsigned NOT NULL,
  `showid` smallint(5) unsigned NOT NULL,
  `date` date NOT NULL,
  `worked` float NOT NULL,
  PRIMARY KEY  (`id`),
  KEY `userid` (`userid`,`showid`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1;

--
-- Dumping data for table `hours`
--

-- --------------------------------------------------------

--
-- Table structure for table `permissions`
--

CREATE TABLE IF NOT EXISTS `permissions` (
  `id` smallint(6) unsigned NOT NULL auto_increment,
  `groupid` smallint(6) unsigned NOT NULL,
  `permid` varchar(10) NOT NULL,
  `permcan` tinyint(4) unsigned NOT NULL default '0',
  PRIMARY KEY  (`id`),
  KEY `permid` (`permid`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1;

--
-- Dumping data for table `permissions`
--

-- --------------------------------------------------------

--
-- Table structure for table `shows`
--

CREATE TABLE IF NOT EXISTS `shows` (
  `showid` smallint(6) unsigned NOT NULL auto_increment,
  `showname` varchar(35) NOT NULL,
  `company` varchar(35) NOT NULL,
  `venue` varchar(35) default NULL,
  `dates` text,
  `created` timestamp NOT NULL default CURRENT_TIMESTAMP,
  PRIMARY KEY  (`showid`),
  KEY `showname` (`showname`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1;

--
-- Dumping data for table `shows`
--

-- --------------------------------------------------------

--
-- Table structure for table `usergroups`
--

CREATE TABLE IF NOT EXISTS `usergroups` (
  `id` smallint(6) unsigned NOT NULL auto_increment,
  `userid` smallint(6) unsigned NOT NULL,
  `groupid` smallint(6) unsigned NOT NULL,
  PRIMARY KEY  (`id`),
  KEY `userid` (`userid`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=3 ;

--
-- Dumping data for table `usergroups`
--

INSERT INTO `usergroups` (`id`, `userid`, `groupid`) VALUES
(1, 1, 1),
(2, 2, 99);

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE IF NOT EXISTS `users` (
  `userid` smallint(5) unsigned NOT NULL auto_increment,
  `username` varchar(15) NOT NULL,
  `first` varchar(20) NOT NULL,
  `last` varchar(20) NOT NULL,
  `phone` bigint(10) unsigned default NULL,
  `email` varchar(50) default NULL,
  `since` timestamp NOT NULL default CURRENT_TIMESTAMP,
  `password` varchar(15) NOT NULL,
  `active` tinyint(4) unsigned NOT NULL default '1',
  PRIMARY KEY  (`userid`),
  KEY `username` (`username`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 COMMENT='Program User Table' AUTO_INCREMENT=3 ;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`userid`, `username`, `first`, `last`, `phone`, `email`, `since`, `password`, `active`) VALUES
(1, 'admin', 'Administrative', 'User', 0, '', '2009-01-17 00:00:00', 'password', 1),
(2, 'guest', 'Guest', 'User', 0, '', '2009-01-17 00:00:01', 'guest', 0);

