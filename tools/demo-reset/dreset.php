#!/usr/bin/php
<?php
/**
 * TDTrac Demo Site Reset Script
 * 
 * Dump and reset the sample data in the TDTrac Demo
 * @package tdtrac
 * @version 1.3.0
 * 
 * This is intended *only* to be used on demo sites, and will delete all
 * of your data, with extream prejudice.  It is intentionally difficult
 * to run, won't work from a browser, and requires a passcode to
 * complete.  For reference, that passcode is 'killitall'.  Usage is:
 * 
 *   dreset <passcode>
 * 
 * Have I made this hard enough?  Seriously, don't run this little 
 * bastard, it's dangerous! (NOTE, all config is hard coded.  If you
 * know what your are doing enough to want to use this, you'll be 
 * able to figure out how to point it in the right place)
 */


## PROGRAM DETAILS. DO NOT EDIT UNLESS YOU KNOW WHAT YOU ARE DOING
$TDTRAC_VERSION = "1.3.0";

$TRUNC_TABLES = array(
	"demo_users",
	"demo_budget",
	"demo_groupnames",
	"demo_hours",
	"demo_msg",
	"demo_permissions",
	"demo_shows",
	"demo_usergroups"
	);

$MYSQL_SERVER = "mysql.jtsage.com";
$MYSQL_USER = "tdtrac";
$MYSQL_PASS = "tdtrac";
$MYSQL_DATABASE = "tdtrac";
$MYSQL_PREFIX = "demo_"; //hardcoded.

$DEFAULT_DATA = array(
	"INSERT INTO `demo_groupnames` (`groupid`, `groupname`) VALUES (1, 'admin'), (2, 'atd'), (3, 'viewbudget'), (4, 'employee'), (99, 'guests');",
	"INSERT INTO `demo_permissions` (`id`, `groupid`, `permid`, `permcan`) VALUES (1, 1, 'addshow', 1), (2, 1, 'editshow', 1), (3, 1, 'viewshow', 1), (4, 1, 'addbudget', 1), (5, 1, 'editbudget', 1), (6, 1, 'viewbudget', 1), (7, 1, 'addhours', 1), (8, 1, 'edithours', 1), (9, 1, 'viewhours', 1), (10, 1, 'adduser', 1), (11, 4, 'addshow', 0), (12, 4, 'editshow', 0), (13, 4, 'viewshow', 1), (14, 4, 'addbudget', 0), (15, 4, 'editbudget', 0), (16, 4, 'viewbudget', 1), (17, 4, 'addhours', 1), (18, 4, 'edithours', 0), (19, 4, 'viewhours', 1), (20, 4, 'adduser', 0);",
	"INSERT INTO `demo_shows` (`showid`, `showname`, `company`, `venue`, `dates`, `created`, `closed`) VALUES (1, 'Example Show', 'Example Company', 'Example Venue', 'Example Dates', '2009-02-05 23:00:00', 0), (2, 'Example Closed Show', 'Example Company', 'Example Venue', 'Example Dates', '2009-09-11 22:00:00', 1);",
	"INSERT INTO `demo_usergroups` (`id`, `userid`, `groupid`) VALUES (1, 1, 1), (2, 2, 99), (3, 3, 4), (4, 4, 4);",
	"INSERT INTO `demo_users` (`userid`, `username`, `first`, `last`, `phone`, `email`, `since`, `password`, `active`, `chpass`, `payroll`, `payrate`, `notify`, `limithours`, `lastlogin`) VALUES (1, 'admin', 'Administrative', 'User', 0, 'jtsage@gmail.com', '2009-01-17 00:00:00', 'password', 1, 0, 0, 0, 1, 0, '2010-04-27 13:49:03'), (2, 'guest', 'Guest', 'User', 0, '', '2009-01-17 00:00:01', 'guest', 0, 1, 0, 0, 0, 1, '0000-00-00 00:00:00'), (3, 'faker', 'Fake', 'Employee', 1234567890, 'jtsage@gmail.com', '2009-02-05 23:00:31', 'faker', 1, 0, 1, 10, 0, 1, '2010-04-04 19:45:37'), (4, 'faker2', 'Fake', 'Employee2', 0, 'jtsage@gmail.com', '2009-02-11 18:35:49', 'faker', 1, 1, 1, 12.5, 0, 1, '0000-00-00 00:00:00');",
	"INSERT INTO `demo_budget` (`id`, `showid`, `price`, `vendor`, `category`, `dscr`, `date`, `pending`, `needrepay`, `gotrepay`, `imgid`, `tax`) VALUES (1, 1, 100.01, 'Example Vendor', 'Example Category', 'Example Description', '2009-02-05', 0, 0, 0, 0, 0), (2, 1, 59.95, 'Example Vendor', 'Another Example Category', 'Example Description', '2009-02-09', 0, 0, 0, 1, 0), (3, 1, 1000, 'Example Vendor', 'Example Category', 'Pending payment example', '2009-03-17', 1, 0, 0, 0, 0), (4, 1, 124, 'Another Example Vendor', 'Consultancy', 'Needs Reimbursment Example', '2009-03-17', 0, 1, 0, 0, 15), (5, 1, 248, 'Another Example Vendor', 'Example Category', 'Needs Reimbursment Example (got it)', '2009-03-17', 0, 1, 1, 0, 0);",
	"INSERT INTO `demo_msg` (`id`, `toid`, `fromid`, `body`, `stamp`) VALUES (1, 3, 1, 'admin Added Hours: 1 for 2009-02-05', '2009-02-05 23:01:25'), (2, 4, 1, 'admin Added Hours: 2 for 2009-02-11', '2009-02-11 18:36:20'), (3, 1, 1, 'Updated to v1.2.5 :: Added Tax Tracking', '2010-03-25 12:09:27'), (4, 1, 1, 'Updated to v1.2.6 :: Added Reciept by E-Mail Tracking', '2010-03-30 02:05:46');",
	"INSERT INTO `demo_hours` (`id`, `userid`, `showid`, `date`, `worked`, `submitted`) VALUES (1, 3, 1, '2009-02-05', 5.75, 1), (2, 3, 1, '2009-02-10', 25, 0), (3, 3, 1, '2009-02-05', 1, 1), (4, 4, 1, '2009-02-11', 2, 1), (5, 3, 1, '2009-02-11', 2, 0);"
	);

$db = mysql_connect($MYSQL_SERVER, $MYSQL_USER, $MYSQL_PASS);
if (!$db) { die('Could not connect: ' . mysql_error()); }
$dbr = mysql_select_db($MYSQL_DATABASE, $db);
if (!$dbr) { die ('Can\'t use tdtrac : ' . mysql_error()); }

if ( !isset($argv[1]) ) {
	die("TDTrac Demo Data Reset.  Use password as first argument to actually run.\nExiting Now...\n");
}

if ( !(md5($argv[1]) == "7fafc020e706834d600af31c8da520c5") ) { 
	die("Incorrect Passcode, Exiting.\n(Hint: read the source code for the passcode)\n");
}

foreach ( $TRUNC_TABLES as $thistab ) {
	$sql = "TRUNCATE TABLE `{$thistab}`";
	$result = mysql_query($sql, $db);
	if ( mysql_errno() ) { echo mysql_error(); }
}

foreach ( $DEFAULT_DATA as $thissql ) {
	$result = mysql_query($thissql, $db);
	if ( mysql_errno() ) { echo mysql_error(); }
}
