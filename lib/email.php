<?php
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
	$html = "";
        $row = mysql_fetch_array($result);
        $body .= "<h2>{$row['showname']}</h2><p><ul>\n";
        $body .= "<li><strong>Company</strong>: {$row['company']}</li>\n";
        $body .= "<li><strong>Venue</strong>: {$row['venue']}</li>\n";
        $body .= "<li><strong>Dates</strong>: {$row['dates']}</li>\n";
        $body .= "</ul></p>\n";

        $html .= "<h2>{$row['showname']}</h2><p><ul>\n";
        $html .= "<li><strong>Company</strong>: {$row['company']}</li>\n";
        $html .= "<li><strong>Venue</strong>: {$row['venue']}</li>\n";
        $html .= "<li><strong>Dates</strong>: {$row['dates']}</li>\n";
        $html .= "</ul><br />Budget E-Mailed to: {$sendto}</p>\n";

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

	mail($sendto, $subject, $body, $headers);
	return $html;
}

function email_hours($userid, $sdate, $edate) {
        GLOBAL $db, $user_name, $MYSQL_PREFIX, $TDTRAC_DAYRATE;
        $sql1 = "SELECT email FROM {$MYSQL_PREFIX}users WHERE username = '{$user_name}'";
        $resul1 = mysql_query($sql1, $db);
        $row1 = mysql_fetch_array($resul1);
        $sendto = $row1['email'];
        mysql_free_result($resul1);
        if ( $userid == 0 && perms_isemp($user_name) ) { return perms_no(); }
        $sql  = "SELECT CONCAT(first, ' ', last) as name, worked, date, showname, h.id as hid FROM {$MYSQL_PREFIX}users u, {$MYSQL_PREFIX}shows s, {$MYSQL_PREFIX}hours h WHERE ";
        $sql .= "u.userid = h.userid AND s.showid = h.showid";
        $sql .= ($userid <> 0) ? " AND u.userid = '{$userid}'" : "";
        $sql .= ($sdate <> 0) ? " AND h.date >= '{$sdate}'" : "";
        $sql .= ($edate <> 0) ? " AND h.date <= '{$edate}'" : "";
        $sql .= " ORDER BY last ASC, date DESC";

        $result = mysql_query($sql, $db);
        while ( $row = mysql_fetch_array($result) ) {
                $dbarray[$row['name']][] = $row;
        }
        $body = "";
	$html = "";
	$html .= "<h2>Hours Worked Report</h2><p>\n";
        $html .= ($sdate <> 0 ) ? "Start Date: {$sdate}\n" : "";
        $html .= ($sdate <> 0 && $edate <> 0 ) ? "<br />" : "";
        $html .= ($edate <> 0 ) ? "Ending Date: {$edate}" : "";

        $subject = "TDTrac Hours Worked: ";
	$subject .= $userid == 0 ? "All Employees, " : "Employee Number {$userid}, ";
	$subject .= ($sdate <> 0 ) ? "Start Date: {$sdate}" : "";
        $subject .= ($sdate <> 0 && $edate <> 0 ) ? ", " : "";
        $subject .= ($edate <> 0 ) ? "Ending Date: {$edate}" : "";

        $headers  = 'MIME-Version: 1.0' . "\r\n";
        $headers .= 'Content-type: text/html; charset=iso-8859-1' . "\r\n";

        foreach ( $dbarray as $key => $data ) {
		$html .= "<br />Included Hours For: {$key}\n";
                $body .= "<h2>Hours Worked For {$key}</h2><p>\n";
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
	mail($sendto, $subject, $body, $headers);
        return $html;
}

function email_pwsend() {
	GLOBAL $db, $MYSQL_PREFIX;
	$html = "<h2>Password Reminder</h2>";
	if ( !($_REQUEST["tracemail"]) || $_REQUEST["tracemail"] == "" ) { 
		$html .= "<p>No E-Mail Address Supplied!</p>";
	} else {
		$sql = "SELECT username, password FROM {$MYSQL_PREFIX}users WHERE email = '{$_REQUEST["tracemail"]}'";
		$result = mysql_query($sql, $db);
		if ( mysql_num_rows($result) == 0 ) { $html .= "<p>E-Mail Address Not Found In Database!</p>\n"; }
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

			$html .= "<p>Password Reminder Sent!</p>\n";
		}
	}
	return $html;

}


?>
