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
 * Send hours reminders
 * 
 * @param integer User id of sender
 * @param string Date hours are due
 * @param string Start Date of payperiod
 * @param string End Date of payperiod
 * @global resource Database connection
 * @global string MySQL Table Prefix
 * @global string Site Address for redirect
 */
function email_remind($userid, $duedate, $sdate, $edate) {
	GLOBAL $db, $MYSQL_PREFIX, $TDTRAC_SITE;
	$sql1 = "SELECT CONCAT(first, ' ', last) as name, username, email, password FROM {$MYSQL_PREFIX}users WHERE userid = '{$userid}'";
	$resul1 = mysql_query($sql1, $db);
	$row1 = mysql_fetch_array($resul1);
	$sendto = $row1['email'];

	$subject = "TDTrac Hours Are Due: {$duedate}";	

	$headers  = 'MIME-Version: 1.0' . "\r\n";
	$headers .= 'Content-type: text/html; charset=iso-8859-1' . "\r\n";

	$body  = "<p>This e-mail is being sent to you to remind you that hours for the payperiod of {$sdate} true {$edate} are due on {$duedate}.  Please take a moment to log into the system and update or double check your hours.<br />";
	$body .= "<br />As a reminder, your <strong>username:</strong> {$row1['username']} and <strong>password:</strong> {$row1['password']} for <a href=\"{$TDTRAC_SITE}home\">{$TDTRAC_SITE}home</a></p>";

	$result = mail($sendto, $subject, $body, $headers);
	if ( $result ) {
		return "Sent: {$row1['username']}";
	} else {
		return "Fail: {$row1['username']}";
	}
}

/**
 * Send hours via email
 * 
 * @param integer User ID For Hours
 * @param string Start date for hours
 * @param string End date for hours
 * @global resource Database connection
 * @global string User Name
 * @global string MySQL Table Prefix
 * @global bool Use dayrate or hourly rate
 */
function email_hours($userid, $sdate, $edate) {
	GLOBAL $db, $user_name, $MYSQL_PREFIX, $TDTRAC_DAYRATE;
	$sql1 = "SELECT email FROM {$MYSQL_PREFIX}users WHERE username = '{$user_name}'";
	$resul1 = mysql_query($sql1, $db);
	$row1 = mysql_fetch_array($resul1);
	$sendto = $row1['email'];
	mysql_free_result($resul1);
	if ( $userid == 0 && perms_isemp($user_name) ) { return perms_no(); }
	$sql  = "SELECT CONCAT(first, ' ', last) as name, worked, date, showname, h.submitted, h.id as hid FROM {$MYSQL_PREFIX}users u, {$MYSQL_PREFIX}shows s, {$MYSQL_PREFIX}hours h WHERE ";
	$sql .= "u.userid = h.userid AND s.showid = h.showid";
	$sql .= ($userid <> 0) ? " AND u.userid = '".intval($userid)."'" : "";
	$sql .= ($sdate <> 0) ? " AND h.date >= '".mysql_real_escape_string($sdate)."'" : "";
	$sql .= ($edate <> 0) ? " AND h.date <= '".mysql_real_escape_string($edate)."'" : "";
	$sql .= " ORDER BY last ASC, date DESC";

	$result = mysql_query($sql, $db);
	while ( $row = mysql_fetch_array($result) ) {
		$dbarray[$row['name']][] = $row;
	}
	$body = "";

	$subject = "TDTrac Hours Worked: ";
	$subject .= $userid == 0 ? "All Employees, " : "Employee Number {$userid}, ";
	$subject .= ($sdate <> 0 ) ? "Start Date: {$sdate}" : "";
	$subject .= ($sdate <> 0 && $edate <> 0 ) ? ", " : "";
	$subject .= ($edate <> 0 ) ? "Ending Date: {$edate}" : "";

	$headers  = 'MIME-Version: 1.0' . "\r\n";
	$headers .= 'Content-type: text/html; charset=iso-8859-1' . "\r\n";

	foreach ( $dbarray as $key => $data ) {
		$body .= "<h2>Hours Worked For {$key}</h2><p>\n";
		$body .= ($sdate <> 0 ) ? "Start Date: {$sdate}\n" : "";
		$body .= ($sdate <> 0 && $edate <> 0 ) ? "<br />" : "";
		$body .= ($edate <> 0 ) ? "Ending Date: {$edate}" : "";
		$body .= "</p><pre>\n";
		$body .= "Date\t\t".(($TDTRAC_DAYRATE)?"Days":"Hours")." Worked\tPaid\tShow\n";
		$tot = 0;
		foreach ( $data as $num => $line ) {
			$tot += $line['worked'];
			$body .= "{$line['date']}\t{$line['worked']}\t\t".(($line['submitted'] == 1) ? "YES" : "NO")."\t{$line['showname']}\n";
		}
		$body .= "-=- TOTAL -=-\t{$tot}\n";
		$body .= "</pre>";
	}
	$result = mail($sendto, $subject, $body, $headers);
	if ( $result ) {
		thrower("E-Mail Sent");
	} else {
		thrower("E-Mail Send Failed");
	}
}

/**
 * Send owed hours email
 * 
 * @global resource Database connection
 * @global string User Name
 * @global string MySQL Table Prefix
 * @global bool Use dayrate or hourly rate
 */
function email_hours_unpaid() {
	GLOBAL $db, $user_name, $MYSQL_PREFIX, $TDTRAC_DAYRATE;
	$sql1 = "SELECT email FROM {$MYSQL_PREFIX}users WHERE username = '{$user_name}'";
	$resul1 = mysql_query($sql1, $db);
	$row1 = mysql_fetch_array($resul1);
	$sendto = $row1['email'];
	mysql_free_result($resul1);
	$userid == 0;
	if ( $userid == 0 && perms_isemp($user_name) ) { return perms_no(); }
	$sql  = "SELECT CONCAT(first, ' ', last) as name, worked, date, showname, h.id as hid FROM {$MYSQL_PREFIX}users u, {$MYSQL_PREFIX}shows s, {$MYSQL_PREFIX}hours h WHERE ";
	$sql .= "u.userid = h.userid AND s.showid = h.showid AND h.submitted = 0";
	$sql .= ($userid <> 0) ? " AND u.userid = '".intval($userid)."'" : "";
	$sql .= ($sdate <> 0) ? " AND h.date >= '".mysql_real_escape_string($sdate)."'" : "";
	$sql .= ($edate <> 0) ? " AND h.date <= '".mysql_real_escape_string($edate)."'" : "";
	$sql .= " ORDER BY last ASC, date DESC";

	$result = mysql_query($sql, $db);
	while ( $row = mysql_fetch_array($result) ) {
		$dbarray[$row['name']][] = $row;
	}
	$body = "";

	$subject = "TDTrac Payment Owed Hours Worked: ";
	$subject .= $userid == 0 ? "All Employees, " : "Employee Number {$userid}, ";
	$subject .= ($sdate <> 0 ) ? "Start Date: {$sdate}" : "";
	$subject .= ($sdate <> 0 && $edate <> 0 ) ? ", " : "";
	$subject .= ($edate <> 0 ) ? "Ending Date: {$edate}" : "";

	$headers  = 'MIME-Version: 1.0' . "\r\n";
	$headers .= 'Content-type: text/html; charset=iso-8859-1' . "\r\n";

	foreach ( $dbarray as $key => $data ) {
		$body .= "<h3>Hours Worked For {$key}</h3><p>\n";
		$body .= ($sdate <> 0 ) ? "Start Date: {$sdate}\n" : "";
		$body .= ($sdate <> 0 && $edate <> 0 ) ? "<br />" : "";
		$body .= ($edate <> 0 ) ? "Ending Date: {$edate}" : "";
		$body .= "</p><pre>\n";
		$body .= "Date\t\t".(($TDTRAC_DAYRATE)?"Days":"Hours")." Worked\tShow\n";
		$tot = 0;
		foreach ( $data as $num => $line ) {
			$tot += $line['worked'];
			$body .= "{$line['date']}\t{$line['worked']}\t\t{$line['showname']}\n";
		}
		$body .= "-=- TOTAL -=-\t{$tot}\n";
		$body .= "</pre>";
	}
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
