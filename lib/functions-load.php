<?php
/**
 * TDTrac Function Loader
 * 
 * Loads all other function files.
 * @package tdtrac
 * @version 2.0.0
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
 * Build a dashboard entry
 * 
 * @param string Header
 * @param string Data
 * @param string Extra data classes
 * @global string Base HREF
 * @return string Well Formed Entry
 */
function make_dash($header, $data, $extra = "", $link = false) {
	GLOBAL $TDTRAC_SITE;
	if ( !$link) {
		return "  <dd><span class=\"dashhead\">{$header} :-: </span><span class=\"dashdata {$extra}\">{$data}</span></dd>";
	} else {
		return "  <dd><span class=\"dashhead\"><a href=\"{$TDTRAC_SITE}{$link}\">{$header}</a> :-: </span><span class=\"dashdata {$extra}\">{$data}</span></dd>";
	}
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
 * Make dashboard objects
 * 
 * @param string Name of object
 * @global string MySQL Table Prefix
 * @global object User Object
 * @return array Formatted HTML
 */
function get_dash($name) {
	GLOBAL $MYSQL_PREFIX, $user;
	switch ( $name ) {
		case "shows":
			$html[] = "<dl class=\"dashboard\"><dt>Show Information</dt>";
			$html[] = make_dash('Shows Tracked', get_single("SELECT COUNT(*) AS num FROM {$MYSQL_PREFIX}shows"));
			$html[] = make_dash('Shows Active', get_single("SELECT COUNT(*) AS num FROM {$MYSQL_PREFIX}shows WHERE closed = 0"));
			$html[] = "</dl>";
			break;
		case "budget":
			$html[] = "<dl class=\"dashboard\"><dt>Budget Information</dt>";
			$html[] = make_dash('Budget Items', get_single("SELECT COUNT(*) AS num FROM {$MYSQL_PREFIX}budget"));
			$html[] = make_dash('Total Expenditure', '$'.number_format(get_single("SELECT SUM(price) AS num FROM {$MYSQL_PREFIX}budget"),2));
			$html[] = make_dash('Pending Payment', '$'.number_format(get_single("SELECT SUM(price) AS num FROM {$MYSQL_PREFIX}budget WHERE pending = 1"),2));
			$html[] = make_dash('Pending Reimbursment', '$'.number_format(get_single("SELECT SUM(price) AS num FROM {$MYSQL_PREFIX}budget WHERE needrepay = 1 AND gotrepay = 0"),2));
			$html[] = make_dash('Reciepts Available', get_single("SELECT COUNT(*) AS num FROM {$MYSQL_PREFIX}rcpts"));
			$rPending = get_single("SELECT COUNT(*) AS num FROM {$MYSQL_PREFIX}rcpts WHERE handled = 0");
			if ( $rPending > 0 ) {
				$html[] = make_dash('Reciepts Pending', $rPending, 'dRed', 'budget/reciept/');
			} else {
				$html[] = make_dash('Reciepts Pending', $rPending, 'dGrn');
			}
			$html[] = "</dl>";
			break;
		case "user":
			$html[] = "<dl class=\"dashboard\"><dt>User Information</dt>";
			$html[] = make_dash('Total Users', get_single("SELECT COUNT(*) AS num FROM {$MYSQL_PREFIX}users"));
			$html[] = make_dash('Active Users', get_single("SELECT COUNT(*) AS num FROM {$MYSQL_PREFIX}users WHERE active = 1"));
			$html[] = make_dash('Users on payroll', get_single("SELECT COUNT(*) AS num FROM {$MYSQL_PREFIX}users WHERE active = 1 AND payroll = 1"));
			$nPass = get_single("SELECT COUNT(*) AS num FROM {$MYSQL_PREFIX}users WHERE chpass = 1");
			if ( $nPass > 0 ) {
				$html[] = make_dash('Users needing new Password', $nPass, 'dRed');
			} else {
				$html[] = make_dash('Users needing new Password', $nPass, 'dGrn');
			}
			$html[] = "</dl>";
			break;
		case "payroll":
			$html[] = "<dl class=\"dashboard\"><dt>Payroll Information</dt>";
			$hPending = get_single("SELECT SUM(worked) AS num FROM {$MYSQL_PREFIX}hours WHERE submitted = 0");
			if ( $hPending > 0 ) {
				$html[] = make_dash('Payroll '.(($TDTRAC_DAYRATE)?"Days":"Hours").' Pending', $hPending, 'dRed', 'hours/view/type:unpaid/');
			} else {
				$html[] = make_dash('Payroll '.(($TDTRAC_DAYRATE)?"Days":"Hours").' Pending', $hPending, 'dGrn');
			}
			$html[] = make_dash('Payroll Total '.(($TDTRAC_DAYRATE)?"Days":"Hours").' Worked', get_single("SELECT SUM(worked) AS num FROM {$MYSQL_PREFIX}hours"));
			$html[] = make_dash('Payroll Total Expenditure', '$'.number_format(get_single("SELECT SUM(worked*payrate) as num FROM {$MYSQL_PREFIX}hours h, {$MYSQL_PREFIX}users u WHERE h.userid = u.userid"),2));
			$html[] = "</dl>";
			break;
		case "mail":
			$html[] = "<dl class=\"dashboard\"><dt>Mail Information</dt>";
			$mTo = get_single("SELECT COUNT(id) as num FROM `{$MYSQL_PREFIX}msg` WHERE toid = ".$user->id);
			$mFm = get_single("SELECT COUNT(id) as num FROM `{$MYSQL_PREFIX}msg` WHERE fromid = ".$user->id);
			if ( $mTo > 0 ) {
				$html[] = make_dash('Your Unread Mail', $mTo, 'dRed', 'mail/inbox/');
			} else {
				$html[] = make_dash('Your Unread Mail', $mTo, 'dGrn');
			}
			if ( $mFm > 0 ) {
				$html[] = make_dash('Unread Mail You Sent', $mFm, '', 'mail/outbox/');
			} else {
				$html[] = make_dash('Unread Mail You Sent', $mFm);
			}
			$html[] = make_dash('All Unread Messages', get_single("SELECT COUNT(id) as num FROM `{$MYSQL_PREFIX}msg`"));
			$html[] = "</dl>";
			break;
		case "todo":
			$html[] = "<dl class=\"dashboard\"><dt>To-Do Information</dt>";
			$tPending = get_single("SELECT COUNT(*) as num FROM {$MYSQL_PREFIX}todo WHERE assigned = {$user->id} AND complete = 0");
			if ( $tPending > 0 ) {
				$html[] = make_dash('Your Pending To-Do Items', $tPending, 'dRed', "todo/view/id:{$user->id}/type:user/");
			} else {
				$html[] = make_dash('Your Pending To-Do Items', $tPending, 'dGrn');
			}
			$html[] = make_dash('All Pending To-Do Items', get_single("SELECT COUNT(*) as num FROM {$MYSQL_PREFIX}todo WHERE complete = 0"));
			$html[] = make_dash('All Overdue To-Do Items', get_single("SELECT COUNT(*) as num FROM {$MYSQL_PREFIX}todo WHERE complete = 0 AND due < NOW()"));
			$html[] = make_dash('To-Do Items in System', get_single("SELECT COUNT(*) as num FROM {$MYSQL_PREFIX}todo"));
			$html[] = "</dl>";
			break;
	}
	return $html;
}

?>
