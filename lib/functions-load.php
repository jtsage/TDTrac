<?php
/**
 * TDTrac Function Loader
 * 
 * Loads all other function files.
 * @package tdtrac
 * @version 4.0.0
 * @author J.T.Sage <jtsage@gmail.com>
 */
 
/** Database Configuration */
require_once("dbaseconfig.php");

/** Library: Forms */
require_once("formlib.php");
/** Library: HTML */
require_once("htmllib.php");
/** Library: tdtrac_user */
require_once("user.php");
/** Library: Lists */
require_once("listlib.php");

/** Meta-Module: all json functions */
require_once("json.php");

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
 * Merge default options and overrides
 * 
 * @param array Default Options
 * @param array Overrides
 * @return array Merged Options
 */
function merge_defaults($orig, $override) {
	foreach ( $orig as $key=>$value ) {
		if ( isset($override[$key]) ) { $orig[$key] = $override[$key]; }
	}
	return $orig;
}

/**
 * Generate an error page
 * 
 * @param string Error Message
 * @param string Explanation, if any
 * @return array Formatted HTML
 */
function error_page($text, $extra = '') {
	$html[] = "<div data-role='collapsible' data-theme='a' class='ui-body ui-body-a'>";
	$html[] = "<h3>{$text}</h3>";
	if ( !empty($extra) ) {
		$html[] = "<p>{$extra}</p>";
	} $html[] = "</div>";
	return $html;
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
	$result = mysqli_query($db, $sql);
	$listreturn = is_array($columns);
	if ( mysqli_num_rows($result) == 0 ) { return False; }
	while ( $row = mysqli_fetch_array($result) ) {
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
	 $result = mysqli_query($db, $sql);
	 if ( !$result || mysqli_num_rows($result) < 1 ) { return 0; }
	 $row = mysqli_fetch_array($result);
	 return $row[$col];
}

/**
 * Generate a JSON error
 * 
 * @param string Error message
 * @return array JSON entries
 */
function json_error($text) {
	return array('success' => false, 'msg' => $text);
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
