<?php
/**
 * TDTrac Install Data
 * 
 * Contains main program logic.
 * @package tdtrac
 * @version 3.1.0 (db: 3.1.0)
 * @author J.T.Sage <jtsage@gmail.com>
 */
GLOBAL $MYSQL_PREFIX, $db;
//This is version 3.1.0

$sql_budget  = "CREATE TABLE IF NOT EXISTS `{$MYSQL_PREFIX}budget` (";
$sql_budget .= "  `id` int(10) unsigned NOT NULL auto_increment,";
$sql_budget .= "  `showid` smallint(5) unsigned NOT NULL,";
$sql_budget .= "  `price` float NOT NULL,";
$sql_budget .= "  `vendor` varchar(35) NOT NULL,";
$sql_budget .= "  `category` VARCHAR( 35 ) NULL,";
$sql_budget .= "  `dscr` varchar(65) NOT NULL,";
$sql_budget .= "  `date` date NOT NULL,";
$sql_budget .= "  `pending` tinyint(4) unsigned NOT NULL default '0',";
$sql_budget .= "  `needrepay` tinyint(4) unsigned NOT NULL default '0',";
$sql_budget .= "  `gotrepay` tinyint(4) unsigned NOT NULL default '0',";
$sql_budget .= "  `tax` float NOT NULL default '0',";
$sql_budget .= "  `imgid` MEDIUMINT UNSIGNED NOT NULL DEFAULT '0',";
$sql_budget .= "  `payto` MEDIUMINT UNSIGNED NOT NULL DEFAULT '0',";
$sql_budget .= "  PRIMARY KEY  (`id`),";
$sql_budget .= "  KEY `showid` (`showid`,`vendor`)";
$sql_budget .= ") ENGINE=MyISAM  DEFAULT CHARSET=latin1;";

$budget_result = mysql_query($sql_budget, $db);

$sql_gname  = "CREATE TABLE IF NOT EXISTS `{$MYSQL_PREFIX}groupnames` (";
$sql_gname .= "  `groupid` tinyint(4) unsigned NOT NULL auto_increment,";
$sql_gname .= "  `groupname` varchar(15) NOT NULL,";
$sql_gname .= "  PRIMARY KEY  (`groupid`)";
$sql_gname .= ") ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=100 ;";

$gname_result = mysql_query($sql_gname, $db);

$ins_gname  = "INSERT INTO `{$MYSQL_PREFIX}groupnames` (`groupid`, `groupname`) VALUES";
$ins_gname .= "(1, 'admin'), (2, 'atd'), (3, 'viewbudget'), (4, 'employee'), (99, 'guests');";

$gnameins_result = mysql_query($ins_gname, $db);

$sql_hours  = "CREATE TABLE IF NOT EXISTS `{$MYSQL_PREFIX}hours` (";
$sql_hours .= "  `id` int(10) unsigned NOT NULL auto_increment,";
$sql_hours .= "  `userid` smallint(5) unsigned NOT NULL,";
$sql_hours .= "  `showid` smallint(5) unsigned NOT NULL,";
$sql_hours .= "  `date` date NOT NULL,";
$sql_hours .= "  `worked` float NOT NULL,";
$sql_hours .= "  `submitted` tinyint(4) unsigned NOT NULL DEFAULT '0',";
$sql_hours .= "  `note` varchar(200),";
$sql_hours .= "  PRIMARY KEY  (`id`),";
$sql_hours .= "  KEY `userid` (`userid`,`showid`)";
$sql_hours .= ") ENGINE=MyISAM  DEFAULT CHARSET=latin1;";

$hours_result = mysql_query($sql_hours, $db);

$sql_perms  = "CREATE TABLE IF NOT EXISTS `{$MYSQL_PREFIX}permissions` (";
$sql_perms .= "  `id` smallint(6) unsigned NOT NULL auto_increment,";
$sql_perms .= "  `groupid` smallint(6) unsigned NOT NULL,";
$sql_perms .= "  `permid` varchar(10) NOT NULL,";
$sql_perms .= "  `permcan` tinyint(4) unsigned NOT NULL default '0',";
$sql_perms .= "  PRIMARY KEY  (`id`),";
$sql_perms .= "  KEY `permid` (`permid`)";
$sql_perms .= ") ENGINE=MyISAM  DEFAULT CHARSET=latin1;";

$perms_result = mysql_query($sql_perms, $db);

$sql_shows  = "CREATE TABLE IF NOT EXISTS `{$MYSQL_PREFIX}shows` (";
$sql_shows .= "  `showid` smallint(6) unsigned NOT NULL auto_increment,";
$sql_shows .= "  `showname` varchar(35) NOT NULL,";
$sql_shows .= "  `company` varchar(35) NOT NULL,";
$sql_shows .= "  `venue` varchar(35) default NULL,";
$sql_shows .= "  `dates` text,";
$sql_shows .= "  `created` timestamp NOT NULL default CURRENT_TIMESTAMP,";
$sql_shows .= "   closed tinyint(4) unsigned NOT NULL DEFAULT '0',";
$sql_shows .= "  PRIMARY KEY  (`showid`),";
$sql_shows .= "  KEY `showname` (`showname`)";
$sql_shows .= ") ENGINE=MyISAM  DEFAULT CHARSET=latin1;";

$shows_result = mysql_query($sql_shows, $db);

$sql_ugrp  = "CREATE TABLE IF NOT EXISTS `{$MYSQL_PREFIX}usergroups` (";
$sql_ugrp .= "  `id` smallint(6) unsigned NOT NULL auto_increment,";
$sql_ugrp .= "  `userid` smallint(6) unsigned NOT NULL,";
$sql_ugrp .= "  `groupid` smallint(6) unsigned NOT NULL,";
$sql_ugrp .= "  PRIMARY KEY  (`id`),";
$sql_ugrp .= "  KEY `userid` (`userid`)";
$sql_ugrp .= ") ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=3 ;";

$ugrp_result = mysql_query($sql_ugrp, $db);

$ins_ugrp = "INSERT INTO `{$MYSQL_PREFIX}usergroups` (`id`, `userid`, `groupid`) VALUES (1, 1, 1), (2, 2, 99);";

$ugrpins_result = mysql_query($ins_ugrp, $db);

$sql_users  = "CREATE TABLE IF NOT EXISTS `{$MYSQL_PREFIX}users` (";
$sql_users .= "  `userid` smallint(5) unsigned NOT NULL auto_increment,";
$sql_users .= "  `username` varchar(15) NOT NULL,";
$sql_users .= "  `first` varchar(20) NOT NULL,";
$sql_users .= "  `last` varchar(20) NOT NULL,";
$sql_users .= "  `phone` bigint(10) unsigned default NULL,";
$sql_users .= "  `email` varchar(50) default NULL,";
$sql_users .= "  `since` timestamp NOT NULL default CURRENT_TIMESTAMP,";
$sql_users .= "  `password` varchar(15) NOT NULL,";
$sql_users .= "  `active` tinyint(4) unsigned NOT NULL default '1',";
$sql_users .= "  `chpass` tinyint(4) unsigned NOT NULL default '1',";
$sql_users .= "  `payroll` tinyint(4) unsigned NOT NULL default '1',";
$sql_users .= "  `payrate` double NULL ,";
$sql_users .= "  `notify` tinyint(4) NOT NULL default '0',";
$sql_users .= "  `limithours` tinyint(4) NOT NULL default '0',";
$sql_users .= "  `lastlogin` timestamp,";
$sql_users .= "  PRIMARY KEY  (`userid`),";
$sql_users .= "  KEY `username` (`username`)";
$sql_users .= ") ENGINE=MyISAM  DEFAULT CHARSET=latin1 COMMENT='Program User Table' AUTO_INCREMENT=3 ;";

$users_result = mysql_query($sql_users, $db);

$ins_users  = "INSERT INTO `{$MYSQL_PREFIX}users` (`userid`, `username`, `first`, `last`, `phone`, `email`, `since`, `password`, `active`) VALUES";
$ins_users .= "(1, 'admin', 'Administrative', 'User', 0, '', '2009-01-17 00:00:00', 'password', 1),";
$ins_users .= "(2, 'guest', 'Guest', 'User', 0, '', '2009-01-17 00:00:01', 'guest', 0);";

$usersins_result = mysql_query($ins_users, $db);

$sql_msg  = "CREATE TABLE IF NOT EXISTS `{$MYSQL_PREFIX}msg` (";
$sql_msg .= "  `id` SMALLINT UNSIGNED NOT NULL auto_increment,";
$sql_msg .= "  `toid` SMALLINT UNSIGNED NOT NULL ,";
$sql_msg .= "  `fromid` SMALLINT UNSIGNED NOT NULL ,";
$sql_msg .= "  `body` VARCHAR( 255 ) NOT NULL ,";
$sql_msg .= "  `stamp` timestamp NOT NULL default CURRENT_TIMESTAMP,";
$sql_msg .= "  PRIMARY KEY ( `id` ) ,";
$sql_msg .= "  INDEX ( `toid` )";
$sql_msg .= ") ENGINE = MYISAM DEFAULT CHARSET=latin1 COMMENT='Program Internal Messages';";

$msg_result = mysql_query($sql_msg, $db);

$sql_tdtrac =    "CREATE TABLE IF NOT EXISTS `{$MYSQL_PREFIX}tdtrac` (  
  `id` int(10) unsigned NOT NULL auto_increment,
  `name` varchar(10) NOT NULL,
  `value` varchar(35) NOT NULL,
    PRIMARY KEY  (`id`)
  ) ENGINE=MyISAM  DEFAULT CHARSET=latin1;";

$tdtrac_result = mysql_query($sql_tdtrac, $db);

$sql_rcpts = "CREATE TABLE IF NOT EXISTS `{$MYSQL_PREFIX}rcpts` (
  `imgid` MEDIUMINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY ,
  `type` VARCHAR( 100 ) NOT NULL ,
  `name` VARCHAR( 25 ) NOT NULL ,
  `data` MEDIUMBLOB NOT NULL,
  `added` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `handled` TINYINT UNSIGNED NOT NULL DEFAULT  '0'
  ) ENGINE = MYISAM ;";

$rcpts_result = mysql_query($sql_rcpts, $db);

$sql_todo = "CREATE TABLE IF NOT EXISTS `{$MYSQL_PREFIX}todo` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `showid` INT UNSIGNED NOT NULL, `due` TIMESTAMP NULL DEFAULT NULL, 
  `assigned` SMALLINT UNSIGNED NOT NULL DEFAULT '0', 
  `dscr` TEXT NOT NULL, `priority` TINYINT NOT NULL DEFAULT '0', 
  `added` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `complete` SMALLINT UNSIGNED NOT NULL DEFAULT '0'
  ) ENGINE = MyISAM;";
  
$todo_result = mysql_query($sql_todo, $db);

$ins_tdtrac = "INSERT INTO `{$MYSQL_PREFIX}tdtrac` (`name`, `value`) VALUES ( 'version', '1.1.0' ), ( 'version', '1.2.0'), ( 'version', '1.2.1'), ( 'version', '1.2.2'), ( 'version', '1.2.4'), ( 'version', '1.2.5'), ( 'version', '1.2.6'), ( 'version', '1.3.0'), ( 'version', '1.3.1' ), ('version', '2.0.1'), ('version', '3.1.0')";

$tdtracins_result = mysql_query($ins_tdtrac, $db);

?>
