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

	$db = mysqli_connect($MYSQL_SERVER, $MYSQL_USER, $MYSQL_PASS);
	if (!$db) {
		die('Could not connect: ' . mysqli_error());
	}

	$dbr = mysqli_select_db($db, $MYSQL_DATABASE);
	if (!$dbr) {
		die ("Can\'t use tdtrac::{$MYSQL_DATABASE}:" . mysqli_error());
	}
?>
