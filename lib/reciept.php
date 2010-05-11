<?php
/**
 * TDTrac Reciept Functions
 * 
 * Contains all reciept related functions. 
 * @package tdtrac
 * @version 1.3.1
 * @author J.T.Sage <jtsage@gmail.com>
 */

/**
 * Associate reciept with budget item
 * 
 * @global resource Datebase Link
 * @global string MySQL Table Prefix
 */
function rcpt_do() {
	GLOBAL $db, $MYSQL_PREFIX;
	$sqla = "UPDATE {$MYSQL_PREFIX}budget SET imgid = {$_REQUEST['imgid']} WHERE id = {$_REQUEST['budid']}";
	$sqlb = "UPDATE {$MYSQL_PREFIX}rcpts SET handled = '1' WHERE imgid = {$_REQUEST['imgid']}";
	$result = mysql_query($sqla);
	$result = mysql_query($sqlb);
	thrower("Reciept Associated with Budget Record");
}

/**
 * Check for pending reciepts
 * 
 * @global resource Database Link
 * @global string MySQL Table Prefix
 * @global string User Name
 * @global string Site Address for links
 * @return string HTML Formatted information
 */
function rcpt_check() {
	GLOBAL $db, $MYSQL_PREFIX, $user_name, $TDTRAC_SITE;
	$html  = "";
	$html .= "<div class=\"infobox\"><span style=\"font-size: .7em\">";
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

/**
 * Remove a reciept from the database
 * 
 * @global resource Datebase Link
 * @global string MySQL Table Prefix
 */
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

/**
 * Show form to associate reciept with current budget record
 * 
 * @param integer Reciept ID
 * @global resource Datebase Link
 * @global string MySQL Table Prefix
 * @return string HTML Output
 */
function rcpt_list_budget($rcpt = 0) {
	GLOBAL $db, $MYSQL_PREFIX;
	$html = "<h3>Add to Existing Budget Item</h3>\n";
	$sql = "SELECT budget.*, showname FROM {$MYSQL_PREFIX}budget as budget, {$MYSQL_PREFIX}shows as shows WHERE budget.showid = shows.showid AND budget.imgid = 0 AND shows.closed = 0 ORDER BY budget.date DESC, budget.id DESC";
	$result = mysql_query($sql, $db);
	while ( $row = mysql_fetch_array($result) ) {
		$picklist[] = array($row['id'], "{$row['showname']} - {$row['date']} - {$row['vendor']} - \${$row['price']}");
	}
	
	$form = new tdform("{$TDTRAC_SITE}rcpt", "forma", 80, "genform2");
	$result = $form->addDrop('budid', 'Item', 'Item to associate with', $picklist, False);
	$result = $form->addHidden('imgid', $rcpt);
	$html .= $form->output('Associate');
	
	return $html;
}

/**
 * View box for existing reciept
 * 
 * @global resource Database Link
 * @global string MySQL Table Prefix
 * @global string User Name
 * @global string Site Address for links
 * @return string HTML Formatted information
 */
function rcpt_view() {
	GLOBAL $db, $MYSQL_PREFIX, $user_name, $TDTRAC_SITE;
	$html = "";
	$html .= "<div id=\"rcptbox\">";
	$sql = "SELECT count(imgid) as num FROM {$MYSQL_PREFIX}rcpts WHERE handled = 0";
	$result = mysql_query($sql, $db);
	$line = mysql_fetch_array($result);
	$total = $line['num'];
	if ( isset($_REQUEST['num']) && ($_REQUEST['num'] > ($total - 1))  ) { thrower("Last Reciept Skipped"); } // Easier to trap later than to suppress skip link on earlier reciept.
	if ( isset($_REQUEST['num']) ) {
		$sql = "SELECT imgid, added FROM {$MYSQL_PREFIX}rcpts WHERE handled = 0 ORDER BY added ASC LIMIT {$_REQUEST['num']},1"; $thisnum = $_REQUEST['num'] + 1;
	} else {
		$sql = "SELECT imgid, added FROM {$MYSQL_PREFIX}rcpts WHERE handled = 0 ORDER BY added ASC LIMIT 1;"; $thisnum = 1;
	}
	$html .= "<span id=\"rcptnum\">Reciept No. <strong>{$thisnum}</strong> of <strong>{$total}</strong></span><br />";
	$result = mysql_query($sql, $db);
	$line = mysql_fetch_array($result);
	$html .= "<img id=\"rcptimg\" name=\"rcptimg\" src=\"rcpt.php?imgid={$line['imgid']}\" alt=\"Reciept Image\" /><br /><span id=\"rcptdate\"><strong>Added:</strong>{$line['added']}</span>";
	$html .= "<div id=\"rcptcontrol\">";
	$html .= "<a title=\"Rotate Original 90deg Counter-Clockwise\" href=\"javascript:document['rcptimg'].src='rcpt.php?imgid={$line['imgid']}&amp;rotate=270';document.links['rcptsave'].href='rcpt.php?imgid={$line['imgid']}&amp;rotate=270&amp;save';return true;\"><img src=\"images/rcpt-ccw.jpg\" alt=\"Rotate CCW\" /></a>";
	$html .= "<a title=\"Save this Image (new window)\" name=\"rcptsave\" href=\"#\" target=\"_blank\"><img src=\"images/rcpt-save.jpg\" alt=\"Save\" /></a>";
	$html .= "<a title=\"Zoom In (new window)\" href=\"rcpt.php?imgid={$line['imgid']}&amp;hires\" target=\"_blank\"><img src=\"images/rcpt-zoom.jpg\" alt=\"Zoom\" /></a>";
	$html .= "<a title=\"Flip Original 180deg\" href=\"javascript:document['rcptimg'].src='rcpt.php?imgid={$line['imgid']}&amp;rotate=180';document.links['rcptsave'].href='rcpt.php?imgid={$line['imgid']}&amp;rotate=180&amp;save';return true;\"><img src=\"images/rcpt-flip.jpg\" alt=\"Rotate 180\" /></a>";
	$html .= "<a title=\"Rotate Original 90deg Clockwise\" href=\"javascript:document['rcptimg'].src='rcpt.php?imgid={$line['imgid']}&amp;rotate=90';document.links['rcptsave'].href='rcpt.php?imgid={$line['imgid']}&amp;rotate=90&amp;save';return true;\"><img src=\"images/rcpt-cw.jpg\" alt=\"Rotate CW\" /></a>";
	$html .= "<br />[-<a title=\"Delete This Reciept\" href=\"/rcpt-delete&amp;imgid={$line['imgid']}\">Nuke</a>-] [-<a title=\"Skip this Reciept for Now\" href=\"/rcpt&amp;num={$thisnum}\">Skip</a>-]";
	$html .= "</div></div>";
	$html .= rcpt_list_budget($line['imgid']);
	$html .= budget_addform($line['imgid']);
	return $html;
}



?>

