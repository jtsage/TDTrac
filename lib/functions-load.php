<?php
/**
 * TDTrac Function Loader
 * 
 * Loads all other function files.
 * @package tdtrac
 * @version 3.0.0
 * @author J.T.Sage <jtsage@gmail.com>
 */
 
/** Database Configuration */
require_once("dbaseconfig.php");

/** Library: Forms */
require_once("formlib.php");
/** Library: Tables */
require_once("tablelib.php");
/** Library: HTML */
require_once("htmllib.php");
/** Library: tdtrac_user */
require_once("user.php");

/** Module: tdtrac_mail */
require_once("messaging.php");
/** Module: tdtrac_admin */
require_once("admin.php");
/** Module: tdtrac_budget */
require_once("budget.php");
/** Module: tdtrac_show */
require_once("show.php");
/** Module: tdtrac_todo */
require_once("todo.php");
/** Module: tdtrac_hours */
require_once("hours.php");

/**
 * Throw a message to the user
 * 
 * @param string Message to send (or false)
 * @param string Location to navigate to
 * @global string Address to redirct to
 * @return void
 */
function thrower($msg, $loc='') {
	GLOBAL $TDTRAC_SITE;
	if ( $msg !== false ) {
		$_SESSION['infodata'] = $msg;
	}
	session_write_close();
	header("Location: {$TDTRAC_SITE}{$loc}");
}

/** 
 * Return a sql query as a one or two dimensional list
 * 
 * @param string SQL argument
 * @param string $columns Name of column to return as single list
 * @param array $columns Names of 2 columsn to return as double list
 * @global object Database Link
 * @return array Single or Double list of items
 */
function db_list($sql, $columns) {
	GLOBAL $db;
	$result = mysql_query($sql, $db);
	$listreturn = is_array($columns);
	if ( mysql_num_rows($result) == 0 ) { return False; }
	while ( $row = mysql_fetch_array($result) ) {
		if ( $listreturn ) {
			$returner[] = array($row[$columns[0]], $row[$columns[1]]);
		} else {
			$returner[] = $row[$columns];
		}
	}
	return $returner;
}

/** 
 * Get a single value from SQL
 * 
 * @param string SQL Query
 * @param string Column to retrieve
 * @global object Database Resource
 * @return string Contents of column
 */
 function get_single($sql, $col='num') {
	 GLOBAL $db;
	 $result = mysql_query($sql, $db);
	 if ( mysql_num_rows($result) < 1 ) { return 0; }
	 $row = mysql_fetch_array($result);
	 return $row[$col];
}

/**
 * Return a SQL Query constant by name
 * 
 * @param string Name of SQL Query
 * @param string Unused?
 * @global string MySQL Table Prefix
 * @global object User object
 * @return string Query string or FALSE
 */
function get_sql_const($name, $extra = null) {
	GLOBAL $MYSQL_PREFIX, $user;
	if ( $name == "showid" ) { return "SELECT showname, showid FROM {$MYSQL_PREFIX}shows WHERE closed = 0 ORDER BY created DESC;"; }
	if ( $name == "showidall" ) { return "SELECT showname, showid FROM {$MYSQL_PREFIX}shows WHERE 1 ORDER BY created DESC;"; }
	if ( $name == "vendor" ) { return "SELECT vendor FROM `{$MYSQL_PREFIX}budget` GROUP BY vendor ORDER BY COUNT(vendor) DESC, vendor ASC"; }
	if ( $name == "category" ) { return "SELECT category FROM `{$MYSQL_PREFIX}budget` GROUP BY category ORDER BY COUNT(category) DESC, category ASC"; }
	if ( $name == "emps" ) {
		$sql  = "SELECT u.userid, CONCAT(first, ' ', last) as name FROM {$MYSQL_PREFIX}users u, {$MYSQL_PREFIX}usergroups ug WHERE";
		$sql .= $user->isemp ? " username = '{$user->username}' AND" : "";
		$sql .= " active = 1 AND payroll = 1 AND ug.userid = u.userid ORDER BY last ASC";
		return $sql; }
	if ( $name == "reimb" ) {
		$sql  = "SELECT u.userid, CONCAT(first, ' ', last) as name FROM {$MYSQL_PREFIX}users u, {$MYSQL_PREFIX}usergroups ug WHERE";
		$sql .= $user->isemp ? " username = '{$user->username}' AND" : "";
		$sql .= " active = 1 AND ug.userid = u.userid ORDER BY last ASC";
		return $sql; }
	if ( $name == "todo" ) { return "SELECT u.userid, CONCAT(first, ' ', last) as name FROM {$MYSQL_PREFIX}users u WHERE active = 1 ORDER BY last ASC"; }
	return False;
}

/**
 * Format a phone number
 * 
 * @param integer Flat phone number, just numbers
 * @return string Formatted phone number
 */
function format_phone($phone) {
	$phone = preg_replace("/[^0-9]/", "", $phone);

	if(strlen($phone) == 7)
		return preg_replace("/([0-9]{3})([0-9]{4})/", "$1-$2", $phone);
	elseif(strlen($phone) == 10)
		return preg_replace("/([0-9]{3})([0-9]{3})([0-9]{4})/", "($1) $2-$3", $phone);
	else
		return $phone;
}

/**
 * Format a user-inputted date (for sql select)
 * 
 * @param string The Date
 * @return string Formatted Date
 */
function make_sql_date($date) {
	$tempdate = strtotime(urldecode($date));
	if ( $tempdate ) {
		return date('Y-m-d', $tempdate);
	} else {
		return date('Y-m-d');
	}
}

/**
 * Format a user-inputted date
 * 
 * @param string The Date
 * @return string Formatted Date
 */
function make_date($date) {
	$tempdate = strtotime($date);
	if ( $tempdate ) {
		return date('Y-m-d', $tempdate) . ' 00:00:00';
	} else {
		return date('Y-m-d') . ' 00:00:00';
	}
}
?>
