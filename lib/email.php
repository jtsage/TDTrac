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
        $body .= "Date\t\tPrice\tVendor\tDescription\n";
        $sql = "SELECT * FROM {$MYSQL_PREFIX}budget WHERE showid = {$showid} ORDER BY date ASC, vendor ASC";
        $result = mysql_query($sql, $db); $intr = 0; $tot = 0;
        while ( $row = mysql_fetch_array($result) ) {
                $intr++;
                $body .= "{$row['date']}\t".number_format($row['price'], 2)."\t{$row['vendor']}\t{$row['dscr']}\n";
                $tot += $row['price'];
        }
        $body .= "-=- TOTAL -=-\t" . number_format($tot, 2) . "\n";
        $body .= "</pre>\n";

	mail($sendto, $subject, $body, $headers);
	return $html;
}



?>
