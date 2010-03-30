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
		$sql = "SELECT * FROM {$MYSQL_PREFIX}rcpts WHERE imgid = {$_REQUEST['imgid']}";
		$result = mysql_query($sql, $db);
		if ( mysql_num_rows($result) > 0 ) { // Good Image, proceed to show.
			$line = mysql_fetch_array($result);
			
			$image_sql = imagecreatefromstring($line['data']);
			
			$input_width = imagesx($image_sql);
			$input_height = imagesy($image_sql);
			
			$display_factor = ( $image_width > $image_height ) ? $image_width / 400 : $image_height / 400;
			$save_factor = ( $image_width > $image_height ) ? $image_width / 900 : $image_height / 900;
			
			$display_width = $input_width / $display_factor;
			$display_height = $input_height / $display_factor;
			
			$save_width = $input_width / $save_factor;
			$save_height = $input_height / $save_factor;
			
			if ( isset($_REQUEST['hires']) ) { 
				$option_hires = true;
				$option_rotate = false;
				$option_save = false;
				$image_display = $image_sql;
			} else {
				if ( isset($_REQUEST['save']) ) { $option_save = true; } else { $option_save = false; }
				if ( isset($_REQUEST['rotate']) && is_numeric($_REQUEST['rotate']) ) {
					switch($_REQUEST['rotate']) {
						case 90: $option_rotate = true; $option_deg = 90; break;
						case 180: $option_rotate = true; $option_deg = 180; break;
						case 270: $option_rotate = true; $option_deg = 270; break;
						default: $option_rotate = false;
					}
				} else { $option_rotate = false; }
			}
			
			
			
		} else { // Bad Image, show 404
			$quickdrop = fopen("./images/rcpt-404.jpg", 'rb');
			ob_end_clean();
			header("Content-Type: image/jpeg");
			header("Content-Length: " . filesize("./images/rcpt-404.jpg"));
			header("Content-Disposition: inline; filename=error.jpg");
			fpassthru($quickdrop);
		}
		
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
