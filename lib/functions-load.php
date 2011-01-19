<?php
/**
 * TDTrac Function Loader
 * 
 * Loads all other function files.
 * @package tdtrac
 * @version 2.0.0
 * @author J.T.Sage <jtsage@gmail.com>
 */
require_once("dbaseconfig.php");
require_once("login.php");

require_once("formlib.php");
require_once("tablelib.php");
require_once("htmllib.php");
require_once("user.php");
//require_once("permissions.php");
//require_once("home.php");
//require_once("email.php");
require_once("messaging.php");
//require_once("admin.php");
//require_once("budget.php");
//require_once("reciept.php");
require_once("show.php");
require_once("todo.php");
require_once("hours.php");

/**
 * Throw a message to the user
 * 
 * @param string Message to send
 * @global string Address to redirct to
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
 * @global resource Database Link
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
 * Return a SQL Query constant by name
 * 
 * @param string Name of SQL Query
 * @global string MySQL Table Prefix
 * @global string User Name
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

?>
