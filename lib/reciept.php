<?php
function rcpt_check() {
        GLOBAL $db, $MYSQL_PREFIX, $user_name, $TDTRAC_SITE;
        $html  = "";
        $html .= "<div id=\"infobox\"><span style=\"font-size: .7em\">";
        $userid = perms_getidbyname($user_name);
	if ( $userid == 1 ) {
		$sql = "SELECT COUNT(imgid) as num FROM {$MYSQL_PREFIX}rcpts WHERE handled = 0";
		$result = mysql_query($sql, $db);
		if ( mysql_num_rows($result) > 0 ) {
			$num = mysql_fetch_array($result);
			$html .= "You have {$num['num']} Unhandled Reciepts Waiting (<a href=\"{$TDTRAC_SITE}rcpt\">[-View-]</a>)";
		}
		$html .= "</span></div>\n";
		return $html;
	} else {
		return "";
	}
}

function rcpt_view() {
	GLOBAL $db, $MYSQL_PREFIX, $user_name, $TDTRAC_SITE;
	$html = "";
	$html .= "<div id=\"rcptbox\">";
	if ( isset($_REQUEST['num']) ) {
		$sql = "SELECT imgid, added FROM {$MYSQL_PREFIX}rcpts WHERE handled = 0 ORDER BY added ASC LIMIT {$_REQUEST['num']},1"; $thisnum = $_REQUEST['num'] + 1;
	} else {
		$sql = "SELECT imgid, added FROM {$MYSQL_PREFIX}rcpts WHERE handled = 0 ORDER BY added ASC LIMIT 1;"; $thisnum = 1;
	}
	$result = mysql_query($sql, $db);
	$line = mysql_fetch_array($result);
	$html .= "<img id=\"rcptimg\" name=\"rcptimg\" src=\"rcpt.php?imgid={$line['imgid']}\"><br><span id=\"rcptdate\"><strong>Added:</strong>{$line['added']}</span>";
	$html .= "<div id=\"rcptcontrol\">";
	$html .= "<a title=\"Rotate Original 90deg Counter-Clockwise\" href=\"javascript:document['rcptimg'].src='rcpt.php?imgid={$line['imgid']}&rotate=270';document.links['rcptsave'].href='rcpt.php?imgid={$line['imgid']}&rotate=270&save';return true;\"><img src=\"images/rcpt-ccw.jpg\"></a>";
	$html .= "<a title=\"Save this Image (new window)\" name=\"rcptsave\" href=\"#\" target=\"_blank\"><img src=\"images/rcpt-save.jpg\"></a>";
	$html .= "<a title=\"Zoom In (new window)\" href=\"rcpt.php?imgid={$line['imgid']}&hires\" target=\"_blank\"><img src=\"images/rcpt-zoom.jpg\"></a>";
	$html .= "<a title=\"Flip Original 180deg\" href=\"javascript:document['rcptimg'].src='rcpt.php?imgid={$line['imgid']}&rotate=180';document.links['rcptsave'].href='rcpt.php?imgid={$line['imgid']}&rotate=180&save';return true;\"><img src=\"images/rcpt-flip.jpg\"></a>";
	$html .= "<a title=\"Rotate Original 90deg Clockwise\" href=\"javascript:document['rcptimg'].src='rcpt.php?imgid={$line['imgid']}&rotate=90';document.links['rcptsave'].href='rcpt.php?imgid={$line['imgid']}&rotate=90&save';return true;\"><img src=\"images/rcpt-cw.jpg\"></a>";
	$html .= "<br />[-<a title=\"Delete This Reciept\" href=\"/rcpt-delete&imgid={$line['imgid']}\">Nuke</a>-] [-<a title=\"Skip this Reciept for Now\" href=\"/rcpt&num={$thisnum}\">Skip</a>-]";
	$html .= "</div></div>";
	return $html;
}



?>

