<?php
require_once("dbaseconfig.php");
require_once("login.php");
require_once("permissions.php");
require_once("show.php");
require_once("home.php");
require_once("budget.php");
require_once("hours.php");

function thrower($msg) {
	GLOBAL $TDTRAC_SITE;
	$_SESSION['infodata'] = $msg;
	header("Location: {$TDTRAC_SITE}");
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
