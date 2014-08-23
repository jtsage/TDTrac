<?php
/**
 * TDTrac Database Connect
 * 
 * Contains database connection details.
 * @package tdtrac
 * @version 4.0.0
 * @author J.T.Sage <jtsage@gmail.com>
 */
GLOBAL $MYSQL_SERVER, $MYSQL_USER, $MYSQL_PASS, $MYSQL_DATABASE;

	$db = mysql_connect($MYSQL_SERVER, $MYSQL_USER, $MYSQL_PASS);
	if (!$db) {
		die('Could not connect: ' . mysql_error());
	}

	$dbr = mysql_select_db($MYSQL_DATABASE, $db);
	if (!$dbr) {
		die ("Can\'t use tdtrac::{$MYSQL_DATABASE}:" . mysql_error());
	}
?>
