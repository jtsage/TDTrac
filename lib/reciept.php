<?php

function rcpt_do() {
	GLOBAL $db, $MYSQL_PREFIX;
	$sqla = "UPDATE {$MYSQL_PREFIX}budget SET imgid = {$_REQUEST['imgid']} WHERE id = {$_REQUEST['budid']}";
	$sqlb = "UPDATE {$MYSQL_PREFIX}rcpts SET handled = '1' WHERE imgid = {$_REQUEST['imgid']}";
	$result = mysql_query($sqla);
	$result = mysql_query($sqlb);
	thrower("Reciept Associated with Budget Record");
}

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
			if ( $num['num'] < 1 ) { return ""; }
			$html .= "You have {$num['num']} Unhandled Reciepts Waiting (<a href=\"{$TDTRAC_SITE}rcpt\">[-View-]</a>)";
		}
		$html .= "</span></div>\n";
		return $html;
	} else {
		return "";
	}
}

function rcpt_nuke() {
	GLOBAL $db, $MYSQL_PREFIX;
	if ( isset($_REQUEST['imgid']) && is_numeric($_REQUEST['imgid']) ) {
		$sql = "DELETE FROM {$MYSQL_PREFIX}rcpts WHERE imgid = {$_REQUEST['imgid']}";
		$result = mysql_query($sql, $db);
		thrower("Reciept Image Deleted");
	} else {
		thrower("Invalid Reciept Image");
	}
}

function rcpt_list_budget($rcpt = 0) {
	GLOBAL $db, $MYSQL_PREFIX;
	$html = "";
	$html .= "<h2>Add to Existing Budget Item</h2>\n";
	$html .= "<div id=\"genform\"><form method=\"post\" action=\"{$TDTRAC_SITE}rcpt\" name=\"forma\">\n";
	$sql = "SELECT budget.*, showname FROM {$MYSQL_PREFIX}budget as budget, {$MYSQL_PREFIX}shows as shows WHERE budget.showid = shows.showid AND budget.imgid = 0 AND shows.closed = 0 ORDER BY budget.date DESC, budget.id DESC";
	$result = mysql_query($sql, $db);
	$html .= "<div class=\"frmele\" title=\"Item to associate with\">Item: <select style=\"width: 35em;\" name=\"budid\">\n";
        while ( $row = mysql_fetch_array($result) ) {
                $html .= "<option value=\"{$row['id']}\">{$row['showname']} - {$row['date']} - {$row['vendor']} - \${$row['price']}</option>\n";
        }
	$html .= "</select></div><div class=\"frmele\"><input type=\"hidden\" name=\"imgid\" value=\"{$rcpt}\" /><input type=\"submit\" value=\"Associate\" /></div></form></div>\n";
	return $html;
}

function rcpt_view() {
	GLOBAL $db, $MYSQL_PREFIX, $user_name, $TDTRAC_SITE;
	$html = "";
	$html .= "<div id=\"rcptbox\">";
	$sql = "SELECT count(imgid) as num FROM {$MYSQL_PREFIX}rcpts WHERE handled = 0";
	$result = mysql_query($sql, $db);
	$line = mysql_fetch_array($result);
	$total = $line['num'];
	if ( isset($_REQUEST['num']) ) {
		$sql = "SELECT imgid, added FROM {$MYSQL_PREFIX}rcpts WHERE handled = 0 ORDER BY added ASC LIMIT {$_REQUEST['num']},1"; $thisnum = $_REQUEST['num'] + 1;
	} else {
		$sql = "SELECT imgid, added FROM {$MYSQL_PREFIX}rcpts WHERE handled = 0 ORDER BY added ASC LIMIT 1;"; $thisnum = 1;
	}
	$html .= "<span id=\"rcptnum\">Reciept No. <strong>{$thisnum}</strong> of <strong>{$total}</strong></span><br />";
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
	$html .= rcpt_list_budget($line['imgid']);
	$html .= budget_addform($line['imgid']);
	return $html;
}



?>

