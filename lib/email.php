<?php
/**
 * TDTrac E-Mail Functions
 * 
 * Contains all e-mail related functions. 
 * Note: email function should never return, use thrower()
 * @package tdtrac
 * @version 1.4.0
 * @author J.T.Sage <jtsage@gmail.com>
 */

/**
 * Send budget via email
 * 
 * @param integer Show ID for budget
 * @global resource Database connection
 * @global string User Name
 * @global string MySQL Table Prefix
 */
function email_budget($showid) {
	GLOBAL $db, $user_name, $MYSQL_PREFIX;
	$sql1 = "SELECT email FROM {$MYSQL_PREFIX}users WHERE username = '{$user_name}'";
	$resul1 = mysql_query($sql1, $db);
	$row1 = mysql_fetch_array($resul1);
	$sendto = $row1['email'];
	mysql_free_result($resul1);
	$sql = "SELECT * FROM {$MYSQL_PREFIX}shows WHERE showid = {$showid}";
	$result = mysql_query($sql, $db); 
	$body = "";
	$row = mysql_fetch_array($result);
	$body .= "<h2>{$row['showname']}</h2><p><ul>\n";
	$body .= "<li><strong>Company</strong>: {$row['company']}</li>\n";
	$body .= "<li><strong>Venue</strong>: {$row['venue']}</li>\n";
	$body .= "<li><strong>Dates</strong>: {$row['dates']}</li>\n";
	$body .= "</ul></p>\n";

	$subject = "TDTrac Budget: {$row['showname']}";
	$headers  = 'MIME-Version: 1.0' . "\r\n";
	$headers .= 'Content-type: text/html; charset=iso-8859-1' . "\r\n";

	$body .= "<h2>Materials Expenses</h2><pre>\n";
	$body .= "Date\t\tPrice\tPending\tReimburse\tVendor\tDescription\n";
	$sql = "SELECT * FROM {$MYSQL_PREFIX}budget WHERE showid = {$showid} ORDER BY category ASC, date ASC, vendor ASC";
	$result = mysql_query($sql, $db); $intr = 0; $tot = 0; $last = "";
	while ( $row = mysql_fetch_array($result) ) {
		if ( $last != "" && $last != $row['category'] ) { 
			$body .= "-=- {$last} SUB-TOTAL -=-\t" . number_format($subtot, 2) . "\n"; $subtot = 0; }
		$intr++;
		$body .= "{$row['date']}\t".number_format($row['price'], 2)."\t";
		$body .= (($row['pending'] == 1) ? "YES" : "NO") . "\t";
		$body .= (($row['needrepay'] == 1) ? (($row['didrepay'] == 1) ? "PAID" : "UNPAID") : "N/A") . "\t";
		$body .= "{$row['vendor']}\t{$row['category']}\t{$row['dscr']}\n";
		$tot += $row['price']; $subtot += $row['price'];
		$last = $row['category'];
	}
	$body .= "-=- {$last} SUB-TOTAL -=-\t" . number_format($subtot, 2) . "\n";
	$body .= "-=- TOTAL -=-\t" . number_format($tot, 2) . "\n";
	$body .= "</pre>\n";

	$result = mail($sendto, $subject, $body, $headers);
	if ( $result ) {
		thrower("E-Mail Sent");
	} else {
		thrower("E-Mail Send Failed");
	}
}





/**
 * Send password reminder via email
 * 
 * @global resource Database connection
 * @global string MySQL Table Prefix
 */
function email_pwsend() {
	GLOBAL $db, $MYSQL_PREFIX;
	if ( !($_REQUEST["tracemail"]) || $_REQUEST["tracemail"] == "" ) { 
		thrower("E-Mail Address Invalid");
	} else {
		$sql = "SELECT username, password FROM {$MYSQL_PREFIX}users WHERE email = '".mysql_real_escape_string($_REQUEST["tracemail"])."'";
		$result = mysql_query($sql, $db);
		if ( mysql_num_rows($result) == 0 ) { thrower("E-Mail Address Invalid"); }
		else {
			$body = "TDTrac Password Reminder:<br /><br />\n";
			while ( $row = mysql_fetch_array($result) ) {
				$body .= "Username: {$row['username']}<br />\n";
				$body .= "Password: {$row['password']}<br /><br />\n";
			}
			$body .= "Note: For security pusposes, you should change this password when you first log in!<br />\n";
			$subject = "TDTrac Password Reminder";
			$headers  = 'MIME-Version: 1.0' . "\r\n";
			$headers .= 'Content-type: text/html; charset=iso-8859-1' . "\r\n";
			$sendto = $_REQUEST['tracemail'];
			mail($sendto, $subject, $body, $headers);
		}
	}
	thrower("Password Reminder Sent!");
}


?>
