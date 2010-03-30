<?php
ob_start(); session_start();

require_once("config.php");
require_once("lib/functions-load.php");

$login = islogin();

if ( !$login[0] ) { // Not Logged In
	$quickdrop = fopen("./images/rcpt-403.jpg", 'rb');
	ob_end_clean();
	header("Content-Type: image/jpeg");
	header("Content-Length: " . filesize("./images/rcpt-403.jpg"));
	header("Content-Disposition: inline; filename=error.jpg");
	fpassthru($quickdrop);

} else { // Logged In, Proceed
	if ( isset($_REQUEST['imgid']) && is_numeric($_REQUEST['imgid']) ) { // Good Call to the script
		
	} else { // Bad Script Call
		$quickdrop = fopen("./images/rcpt-500.jpg", 'rb');
		ob_end_clean();
		header("Content-Type: image/jpeg");
		header("Content-Length: " . filesize("./images/rcpt-500.jpg"));
		header("Content-Disposition: inline; filename=error.jpg");
		fpassthru($quickdrop);
	}
}



?>
