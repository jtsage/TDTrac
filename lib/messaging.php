<?php
/**
 * TDTrac Messaging Functions
 * 
 * Contains all messaging framework
 * @package tdtrac
 * @version 1.3.0
 */

/** 
 * Check for messages
 * 
 * @global resource Database Link
 * @global string MySQL Table Prefix
 * @global string User Name
 * @global string Site Address for links
 * @return HTML Output
 */
function msg_check() {
	GLOBAL $db, $MYSQL_PREFIX, $user_name, $TDTRAC_SITE;
	$html  = "";
	$html .= "<div id=\"infobox\"><span style=\"font-size: .7em\">";
	$userid = perms_getidbyname($user_name);
	$tosql = "SELECT COUNT(id) as num FROM {$MYSQL_PREFIX}msg WHERE toid = '{$userid}'";
	$fmsql = "SELECT COUNT(id) as num FROM {$MYSQL_PREFIX}msg WHERE fromid = '{$userid}'";
	$result1 = mysql_query($tosql, $db);
	$result2 = mysql_query($fmsql, $db);
	$row1 = mysql_fetch_array($result1);
	$row2 = mysql_fetch_array($result2);
	mysql_free_result($result1);
	mysql_free_result($result2);
	$ret = 0;
	if ( !is_null($row1['num']) && $row1['num'] > 0 ) { $html .= "You Have {$row1['num']} Unread Messages Waiting (<a href=\"{$TDTRAC_SITE}msg-read\">[-Read-]</a>)<br />"; $ret = 1; }
	if ( !is_null($row2['num']) && $row2['num'] > 0 ) { $html .= "You Have {$row2['num']} Sent Messages Waiting (<a href=\"{$TDTRAC_SITE}msg-view\">[-View-]</a>)"; $ret = 1; }
	$html .= "</span></div>\n";
	if ( $ret ) { return $html; } else { return ""; }
}

/** 
 * View outbox
 * 
 * @global resource Database Link
 * @global string User Name
 * @global string MySQL Table Prefix
 * @global string Site Address for links
 * @return HTML Output
 */
function msg_sent_view() {
	GLOBAL $db, $user_name, $MYSQL_PREFIX, $TDTRAC_SITE;
	$userid = perms_getidbyname($user_name);
	$cannuke = perms_isadmin($user_name);
	$sql = "SELECT id, toid, body, DATE_FORMAT(stamp, '%m-%d-%y %h:%i %p') as wtime FROM {$MYSQL_PREFIX}msg WHERE fromid = {$userid} ORDER BY stamp DESC";
	$result = mysql_query($sql, $db);
	$html  = "<h2>Message Outbox</h2><p>";
	$html .= "<table id=\"budget\">\n";
	$html .= "<tr><th>Date</th><th>Recipient</th><th>Message</th>";
	$html .= ($cannuke) ? "<th>Nuke</th></tr>\n" : "</tr>\n";
	while ( $row = mysql_fetch_array($result) ) {
		$html .= "<tr><td>{$row['wtime']}</td><td>";
		$html .= perms_getfnamebyid($row['toid']);
		$html .= "</td><td>{$row['body']}</td>";
		$html .= ($cannuke) ? "<td align=\"center\"><a href=\"{$TDTRAC_SITE}msg-delete&id={$row['id']}\">[x]</a></td></tr>\n" : "</tr>\n";
	}
	$html .= "</table></p>\n";
	return $html;
}

/** 
 * View inbox
 * 
 * @global resource Database Link
 * @global string User Name
 * @global string MySQL Table Prefix
 * @global string Site Address for links
 * @return HTML Output
 */
function msg_inbox_view() {
	GLOBAL $db, $user_name, $MYSQL_PREFIX, $TDTRAC_SITE;
	$userid = perms_getidbyname($user_name);
	$sql = "SELECT id, fromid, body, DATE_FORMAT(stamp, '%m-%d-%y %h:%i %p') as wtime FROM {$MYSQL_PREFIX}msg WHERE toid = {$userid} ORDER BY stamp DESC";
	$result = mysql_query($sql, $db);
	$html  = "<h2>Message Inbox</h2><p>";
	$html .= "<div style=\"float: right\">[<a href=\"{$TDTRAC_SITE}msg-clean\">Clear Inbox</a>]</div>\n";
	$html .= "<table id=\"budget\">\n";
	$html .= "<tr><th>Date</th><th>Sender</th><th>Message</th><th>Delete</th></tr>\n";
	while ( $row = mysql_fetch_array($result) ) {
		$html .= "<tr><td>{$row['wtime']}</td><td>";
		$html .= perms_getfnamebyid($row['fromid']);
		$html .= "</td><td>{$row['body']}</td>";
		$html .= "<td align=\"center\"><a href=\"{$TDTRAC_SITE}msg-delete&id={$row['id']}\">[x]</a></td></tr>\n";
	}
	$html .= "</table></p>\n";
	return $html;
}

/** 
 * Remove a message form the datebase
 * 
 * @global resource Database Link
 * @global string User Name
 * @global string MySQL Table Prefix
 * @param integer Message ID to remove
 */
function msg_delete($msgid) {
	GLOBAL $db, $user_name, $MYSQL_PREFIX;
	$userid = perms_getidbyname($user_name);
	$nocheck = perms_isadmin($user_name);
	if ( !$nocheck ) {
		$sql = "SELECT toid FROM {$MYSQL_PREFIX}msg WHERE id = {$msgid}";
		$result = mysql_query($sql, $db);
		$row = mysql_fetch_array($result);
		if ( $row['toid'] <> $userid ) { thrower("You Cannot Delete Messages Not Sent To You"); }
	}
	$dsql = "DELETE FROM {$MYSQL_PREFIX}msg WHERE id = {$msgid} LIMIT 1";
	$result = mysql_query($dsql, $db);
	thrower("Message ID:{$msgid} Removed");
}

/** 
 * Clear inbox
 * 
 * @global resource Database Link
 * @global string User Name
 * @global string MySQL Table Prefix
 */
function msg_clear_inbox() {
	GLOBAL $db, $user_name, $MYSQL_PREFIX;
	$userid = perms_getidbyname($user_name);
	$sql = "DELETE FROM {$MYSQL_PREFIX}msg WHERE toid = $userid";
	$result = mysql_query($sql, $db);
	thrower("All Inbox Messsages Deleted");
}
?>
