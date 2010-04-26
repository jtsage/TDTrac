<?php
require_once("dbaseconfig.php");
require_once("login.php");
require_once("formlib.php");
require_once("permissions.php");
require_once("show.php");
require_once("home.php");
require_once("budget.php");
require_once("hours.php");
require_once("email.php");
require_once("messaging.php");
require_once("reciept.php");

function thrower($msg) {
	GLOBAL $TDTRAC_SITE;
	$_SESSION['infodata'] = $msg;
	header("Location: {$TDTRAC_SITE}home");
}

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

function get_sql_const($name) {
	GLOBAL $MYSQL_PREFIX;
	if ( $name == "showid" ) { return "SELECT showname, showid FROM {$MYSQL_PREFIX}shows WHERE closed = 0 ORDER BY created DESC;"; }
	if ( $name == "showidall" ) { return "SELECT showname, showid FROM {$MYSQL_PREFIX}shows WHERE 1 ORDER BY created DESC;"; }
	if ( $name == "vendor" ) { return "SELECT vendor FROM `{$MYSQL_PREFIX}budget` GROUP BY vendor ORDER BY COUNT(vendor) DESC, vendor ASC"; }
	if ( $name == "category" ) { return "SELECT category FROM `{$MYSQL_PREFIX}budget` GROUP BY category ORDER BY COUNT(category) DESC, category ASC"; }
	return False;
}

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
