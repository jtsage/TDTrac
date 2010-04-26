<?php
/**
 * TDTrac Reciept Viewer
 * 
 * Contains main program logic.
 * @package tdtrac
 * @version 1.3.0
 */
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
			
			$display_factor = ( $image_width > $image_height ) ? $input_width / 400 : $input_height / 400;
			$save_factor = ( $image_width > $image_height ) ? $input_width / 900 : $input_height / 900;
			
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
			
			if ( !$option_hires ) {
				$image_scaled = imagecreatetruecolor($display_width, $display_height);
			
				$worked = imagecopyresampled($image_scaled, $image_sql, 0, 0, 0, 0, $display_width, $display_height, $input_width, $input_height);
			
				if ( $option_save ) {
					$save_scaled = imagecreatetruecolor($save_width, $save_height);
					$worked = imagecopyresampled($save_scaled, $image_sql, 0, 0, 0, 0, $save_width, $save_height, $input_width, $input_height);
				}
			
				if ( $option_rotate ) {
					$image_display = rotateImage($image_scaled, $option_deg);
					if ( $option_save ) { $save_finished = rotateImage($save_scaled, $option_deg); }
				} else {
					$image_display = $image_scaled;
					if ( $option_save ) { $save_finished = $save_scaled; }
				}
			}
			
			if ( $option_save) {
				ob_clean();
				imagejpeg($save_finished, null, 85);
				$imageblob = ob_get_contents();
			
				$sql = "UPDATE {$MYSQL_PREFIX}rcpts SET data = '" . mysql_real_escape_string($imageblob) . "' WHERE imgid = {$_REQUEST['imgid']}";
				$result = mysql_query($sql, $db);
			}
			
			ob_clean();
			imagejpeg($image_display, null, 85);
			$imagedatasize = ob_get_length();
			$imagedata = ob_get_contents();
			
			header("Content-Type: image/jpeg");
			header("Content-Length: {$imagedatasize}");
			header("Content-Disposition: inline; filename=rcpt-{$_REQUEST['imgid']}.jpg");
			echo $imagedata;
			
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

/** 
 * Rotate a GD Image
 * 
 * @param resource GD Image
 * @param integer Rotation, either 90, 180 or 270
 * @return resource GD Image
 */
function rotateImage($img, $rotation) {
  $width = imagesx($img);
  $height = imagesy($img);
  switch($rotation) {
    case 90: $newimg= @imagecreatetruecolor($height , $width );break;
    case 180: $newimg= @imagecreatetruecolor($width , $height );break;
    case 270: $newimg= @imagecreatetruecolor($height , $width );break;
    case 0: return $img;break;
    case 360: return $img;break;
  }
  if($newimg) { 
    for($i = 0;$i < $width ; $i++) { 
      for($j = 0;$j < $height ; $j++) {
        $reference = imagecolorat($img,$i,$j);
        switch($rotation) {
          case 90: if(!@imagesetpixel($newimg, ($height - 1) - $j, $i, $reference )){return false;}break;
          case 180: if(!@imagesetpixel($newimg, $width - $i, ($height - 1) - $j, $reference )){return false;}break;
          case 270: if(!@imagesetpixel($newimg, $j, $width - $i, $reference )){return false;}break;
        }
      } 
    } return $newimg; 
  } 
  return false;
}



?>
