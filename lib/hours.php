<?php

function hours_add () {
	GLOBAL $db, $user_name, $MYSQL_PREFIX, $TDTRAC_DAYRATE, $TDTRAC_SITE;
	$html  = "<h2>Add Payroll Record</h2>\n";
	$html .= "<div id=\"genform\"><form method=\"post\" action=\"{$TDTRAC_SITE}add-hours\" name=\"form1\">\n";
	$html .= "<div class=\"frmele\">Employee: <select name=\"userid\" style=\"width: 25em\" >\n";
	$sql  = "SELECT u.userid, CONCAT(first, ' ', last) as name FROM {$MYSQL_PREFIX}users u, {$MYSQL_PREFIX}usergroups ug WHERE";
	$sql .= perms_isemp($user_name) ? " username = '{$user_name}' AND" : "";
	$sql .= " active = 1 AND payroll = 1 AND ug.userid = u.userid ORDER BY last ASC"; //die($sql);
	$result = mysql_query($sql, $db);
	while ( $row = mysql_fetch_array($result) ) {
		$html .= "<option value=\"{$row['userid']}\">{$row['name']}</option>\n";
	}
	$html .= "</select></div>\n";
        $html .= "<div class=\"frmele\">Show: <select tabindex=\"1\" style=\"width: 25em;\" name=\"showid\">\n";
        $sql = "SELECT showname, showid FROM {$MYSQL_PREFIX}shows ORDER BY created DESC;";
        $result = mysql_query($sql, $db);
        while ( $row = mysql_fetch_array($result) ) {
                $html .= "<option value=\"{$row['showid']}\">{$row['showname']}</option>\n";
        }
        $html .= "</select></div>";
        $html .= "<div class=\"frmele\">Date: <input type=\"text\" size=\"22\" name=\"date\" id=\"date\" style=\"margin-right: 2px\" />\n";
	
        $html .= "<a href=\"#\" onClick=\"tdt_show_calendar(".(date(n)-1).",".date(Y).",'pickcal','date')\">[cal]</a>\n";
	$html .= " <a href=\"#\" onClick=\"document.forms['form1'].date.value='".date("Y-m-d")."'\">[today]</a></div>\n";
	$html .= "<div class=\"frmele\" id=\"pickcal\"></div>\n";

	$html .= "<div class=\"frmele\">".(($TDTRAC_DAYRATE)?"Days":"Hours")." Worked: <input type=\"text\" size=\"35\" name=\"worked\" /></div>\n";
	$html .= "<div class=\"frmele\"><input type=\"submit\" value=\"Add Hours\" /></div>\n";
	$html .= "</form></div>\n";
	return $html;
}


function hours_edit ($hid) {
	GLOBAL $db, $user_name, $MYSQL_PREFIX, $TDTRAC_DAYRATE, $TDTRAC_SITE;
	$sql .= "SELECT h.*, CONCAT(first, ' ', last) as name FROM {$MYSQL_PREFIX}hours h, {$MYSQL_PREFIX}users u WHERE h.userid = u.userid AND h.id = {$hid} LIMIT 1";
	$result = mysql_query($sql, $db);
	$recd = mysql_fetch_array($result);
	$html  = "<h2>Edit Payroll Record</h2>\n";
	$html .= "<div id=\"genform\"><form method=\"post\" action=\"{$TDTRAC_SITE}edit-hours\" name=\"form1\">\n";
	$html .= "<input type=\"hidden\" name=\"id\" value=\"{$hid}\" />\n";
	$html .= "<div class=\"frmele\">Employee: <select name=\"userid\" style=\"width: 25em\" >\n";
	$html .= "<option value=\"{$recd['userid']}\">{$recd['name']}</option>\n";
	$html .= "</select></div>\n";
        $html .= "<div class=\"frmele\">Show: <select tabindex=\"1\" style=\"width: 25em;\" name=\"showid\">\n";
        $sql = "SELECT showname, showid FROM {$MYSQL_PREFIX}shows ORDER BY created DESC;";
        $result = mysql_query($sql, $db);
        while ( $row = mysql_fetch_array($result) ) {
                $html .= "<option value=\"{$row['showid']}\"";
		$html .= ( $row['showid'] == $recd['showid'] ) ? " selected=\"selected\"" : "";
		$html .= ">{$row['showname']}</option>\n";
        }
        $html .= "</select></div>";
        $html .= "<div class=\"frmele\">Date: <input type=\"text\" size=\"18\" name=\"date\" id=\"date\" style=\"margin-right: 2px\" value=\"{$recd['date']}\" />\n";
        $html .= "<a href=\"#\" onClick=\"tdt_show_calendar(".(date(n)-1).",".date(Y).",'pickcal','date')\">[calendar popup]</a></div>\n";
	$html .= "<div class=\"frmele\" id=\"pickcal\"></div>\n";
	$html .= "<div class=\"frmele\">".(($TDTRAC_DAYRATE)?"Days":"Hours")." Worked: <input type=\"text\" size=\"35\" name=\"worked\" value=\"{$recd['worked']}\" /></div>\n";
        $html .= "<div class=\"frmele\">Hours Paid Out: <input type=\"checkbox\" name=\"submitted\" value=\"y\" ".(($row['submitted'] == 1) ? "checked=\"checked\"" : "")." /></div>";
	$html .= "<div class=\"frmele\"><input type=\"submit\" value=\"Commit\" /></div>\n";
	$html .= "</form></div>\n"; 
	return $html;
}

function hours_del ($hid) {
	GLOBAL $db, $user_name, $MYSQL_PREFIX, $TDTRAC_DAYRATE, $TDTRAC_SITE;
	$sql .= "SELECT h.*, CONCAT(first, ' ', last) as name, showname FROM {$MYSQL_PREFIX}hours h, {$MYSQL_PREFIX}users u, {$MYSQL_PREFIX}shows s WHERE h.userid = u.userid AND h.showid = s.showid AND h.id = {$hid} LIMIT 1";
	$result = mysql_query($sql, $db);
	$recd = mysql_fetch_array($result);
	$html  = "<h2>Delete Payroll Record</h2>\n";
	$html .= "<div id=\"genform\"><form method=\"post\" action=\"{$TDTRAC_SITE}del-hours\" name=\"form1\">\n";
	$html .= "<input type=\"hidden\" name=\"id\" value=\"{$hid}\" />\n";
	$html .= "<div class=\"frmele\">Employee: <select name=\"userid\" style=\"width: 25em\" disabled=\"disabled\" >\n";
	$html .= "<option value=\"{$recd['userid']}\">{$recd['name']}</option>\n";
	$html .= "</select></div>\n";
        $html .= "<div class=\"frmele\">Show: <select tabindex=\"1\" style=\"width: 25em;\" name=\"showid\" disabled=\"disabled\" >\n";
        $html .= "<option value=\"{$recd['showid']}\">{$recd['showname']}</option>\n";
        $html .= "</select></div>";
        $html .= "<div class=\"frmele\">Date: <input type=\"text\" size=\"18\" name=\"date\" id=\"date\" style=\"margin-right: 2px\" value=\"{$recd['date']}\" disabled=\"disabled\" />\n";
        $html .= "<a href=\"#\">[calendar popup]</a></div>\n";
	$html .= "<div class=\"frmele\">".(($TDTRAC_DAYRATE)?"Days":"Hours")." Worked: <input type=\"text\" size=\"35\" name=\"worked\" value=\"{$recd['worked']}\" disabled=\"disabled\" /></div>\n";
	$html .= "<div class=\"frmele\"><input type=\"submit\" value=\"Delete\" /></div>\n";
	$html .= "</form></div>\n"; 
	return $html;
}

function hours_add_do() {
	GLOBAL $db, $MYSQL_PREFIX, $user_name;
	$sql = "INSERT INTO {$MYSQL_PREFIX}hours ( userid, showid, date, worked ) VALUES ( '{$_REQUEST['userid']}' , '{$_REQUEST['showid']}' , '{$_REQUEST['date']}' , '{$_REQUEST['worked']}' )";
	$fromid = perms_getidbyname($user_name);
	$msg = "{$user_name} Added Hours: {$_REQUEST['worked']} for {$_REQUEST['date']}";

	if ( $fromid == $_REQUEST['userid'] ) { // ADDING HOURS FOR SELF
		$sqltoid = "SELECT userid FROM {$MYSQL_PREFIX}users WHERE notify = 1";
		$restoid = mysql_query($sqltoid, $db);
		while ( $toidrow = mysql_fetch_array($restoid) ) {
        		$msgsql = "INSERT INTO {$MYSQL_PREFIX}msg ( toid, fromid, body ) VALUES ( '{$toidrow['userid']}', '{$fromid}', '{$msg}')";
			$result = mysql_query($msgsql, $db);
		}
	} else { // ADDING HOURS FOR OTHERS
		$toid = $_REQUEST['userid'];
        	$msgsql = "INSERT INTO {$MYSQL_PREFIX}msg ( toid, fromid, body ) VALUES ( '{$toid}', '{$fromid}', '{$msg}')";
		$result = mysql_query($msgsql, $db);
	}
	$result = mysql_query($sql, $db);
	thrower("Hours Added");
}

function hours_edit_do($id) {
	GLOBAL $db, $MYSQL_PREFIX;
	$sql = "UPDATE {$MYSQL_PREFIX}hours SET showid = '{$_REQUEST['showid']}', date = '{$_REQUEST['date']}', worked = '{$_REQUEST['worked']}', submitted = ".(($_REQUEST['submitted'] == "y") ? "1" : "0")." WHERE id = '{$id}'";
	$result = mysql_query($sql, $db);
	thrower("Hours Record #{$id} Updated");
}

function hours_del_do($id) {
        GLOBAL $db, $MYSQL_PREFIX;
        $sql = "DELETE FROM {$MYSQL_PREFIX}hours WHERE id = '{$id}' LIMIT 1";
        $result = mysql_query($sql, $db);
        thrower("Hours Record #{$id} Deleted");
}


function hours_view_pick() {
	GLOBAL $db, $user_name, $MYSQL_PREFIX, $TDTRAC_SITE;
	$html = "<h2>View By Employee</h2>";
	$html .= "<div id=\"genform\"><form method=\"post\" action=\"{$TDTRAC_SITE}view-hours\" name=\"form1\">\n";
        $html .= "<div class=\"frmele\">Employee: <select name=\"userid\" style=\"width: 25em\" >\n";
        $sql  = "SELECT userid, CONCAT(first, ' ', last) as name FROM {$MYSQL_PREFIX}users WHERE";
        $sql .= perms_isemp($user_name) ? " username = '{$user_name}' AND" : "";
        $sql .= " payroll = 1 ORDER BY last ASC";
        $result = mysql_query($sql, $db);
        while ( $row = mysql_fetch_array($result) ) {
                $html .= "<option value=\"{$row['userid']}\">{$row['name']}</option>\n";
        }
        $html .= "</select></div>\n";
        $html .= "<div class=\"frmele\">Start Date: <input type=\"text\" size=\"18\" name=\"sdate\" id=\"sdate1\" style=\"margin-right: 2px\" />\n";
        $html .= "<a href=\"#\" onClick=\"tdt_show_calendar(".(date(n)-1).",".date(Y).",'pickcal1','sdate1')\">[calendar popup]</a></div>\n";
        $html .= "<div class=\"frmele\" id=\"pickcal1\"></div>\n";

        $html .= "<div class=\"frmele\">End Date: <input type=\"text\" size=\"22\" name=\"edate\" id=\"edate1\" style=\"margin-right: 2px\" />\n";
        $html .= "<a href=\"#\" onClick=\"tdt_show_calendar(".(date(n)-1).",".date(Y).",'pickcal2','edate1')\">[cal]</a>\n";
        $html .= " <a href=\"#\" onClick=\"document.forms['form1'].edate1.value='".date("Y-m-d")."'\">[today]</a></div>\n";
        $html .= "<div class=\"frmele\" id=\"pickcal2\"></div>\n";

	$html .= "<div class=\"frmele\">Leave Dates Blank to See All \n";
	$html .= "<input type=\"submit\" value=\"View Hours\" /></div></form></div>\n";
	if ( perms_isemp($user_name) ) { return $html; }
        $html .= "<h2>View Dated Report</h2>";
        $html .= "<div id=\"genform\"><form method=\"post\" action=\"{$TDTRAC_SITE}view-hours\" name=\"form2\">\n";

        $html .= "<div class=\"frmele\">Start Date: <input type=\"text\" size=\"18\" name=\"sdate\" id=\"sdate2\" style=\"margin-right: 2px\" />\n";
        $html .= "<a href=\"#\" onClick=\"tdt_show_calendar(".(date(n)-1).",".date(Y).",'pickcal3','sdate2')\">[calendar popup]</a></div>\n";
        $html .= "<div class=\"frmele\" id=\"pickcal3\"></div>\n";

        $html .= "<div class=\"frmele\">End Date: <input type=\"text\" size=\"22\" name=\"edate\" id=\"edate2\" style=\"margin-right: 2px\" />\n";
        $html .= "<a href=\"#\" onClick=\"tdt_show_calendar(".(date(n)-1).",".date(Y).",'pickcal4','edate2')\">[cal]</a>\n";
        $html .= " <a href=\"#\" onClick=\"document.forms['form2'].edate2.value='".date("Y-m-d")."'\">[today]</a></div>\n";
        $html .= "<div class=\"frmele\" id=\"pickcal4\"></div>\n";

	$html .= "<div class=\"frmele\">Leave Dates Blank to See All \n";
	$html .= "<input type=\"submit\" value=\"View Hours\" /></div></form></div>\n";
        if ( !perms_isadmin($user_name) ) { return $html; }
        $html .= "<h2>View All Un-Paid Hours</h2>";
        $html .= "<div id=\"genform\"><form method=\"get\" action=\"{$TDTRAC_SITE}view-hours-unpaid\" name=\"form3\">\n";
	$html .= "<div class=\"frmele\"><input type=\"submit\" value=\"View Hours\" /></div></form></div>\n";
        
	return $html;	
}

function hours_view($userid) {
	GLOBAL $db, $user_name, $MYSQL_PREFIX, $TDTRAC_DAYRATE, $TDTRAC_SITE;
	if ( $userid == 0 && perms_isemp($user_name) ) { return perms_no(); }
	$canedit = perms_checkperm($user_name, "edithours");
	$sql  = "SELECT CONCAT(first, ' ', last) as name, worked, date, showname, submitted, h.id as hid FROM {$MYSQL_PREFIX}users u, {$MYSQL_PREFIX}shows s, {$MYSQL_PREFIX}hours h WHERE ";
        $sql .= "u.userid = h.userid AND s.showid = h.showid";
	$sql .= ($userid <> 0) ? " AND u.userid = '{$userid}'" : "";
	$sql .= ($_REQUEST['sdate'] <> "") ? " AND h.date >= '{$_REQUEST['sdate']}'" : "";
	$sql .= ($_REQUEST['edate'] <> "") ? " AND h.date <= '{$_REQUEST['edate']}'" : "";
        $sql .= " ORDER BY last ASC, date DESC";
	$maillink  = "{$TDTRAC_SITE}email-hours&id={$userid}&sdate=";
        $maillink .= ($_REQUEST['sdate'] <> "" ) ? $_REQUEST['sdate'] : "0";
        $maillink .= "&edate=";
	$maillink .= ($_REQUEST['edate'] <> "" ) ? $_REQUEST['edate'] : "0";
	$result = mysql_query($sql, $db);
	while ( $row = mysql_fetch_array($result) ) {
		$dbarray[$row['name']][] = $row;
	}
	$html = "";
	foreach ( $dbarray as $key => $data ) {
		$html .= "<h2>Hours Worked For {$key}</h2><p>\n";
		$html .= "<div style=\"float: right\">[<a href=\"{$maillink}\">E-Mail to Self</a>]</div>\n";
		$html .= ($_REQUEST['sdate'] <> "" ) ? "Start Date: {$_REQUEST['sdate']}\n" : "";
		$html .= ( $_REQUEST['sdate'] <> "" && $_REQUEST['edate'] <> "" ) ? "<br />" : "";
		$html .= ($_REQUEST['edate'] <> "" ) ? "Ending Date: {$_REQUEST['edate']}" : "";
		$html .= "</p><table id=\"budget\">\n";
		$html .= "<tr><th style=\"width: 15em\">Date</th><th>Show</th><th style=\"width:15em\">".(($TDTRAC_DAYRATE)?"Days":"Hours")." Worked</th><th>Paid</th>";
		$html .= ( $canedit ) ? "<th style=\"width: 35px\">Edit</th><th style=\"width: 35px\">Del</th></tr>\n" : "</tr>\n";
		$tot = 0;
		foreach ( $data as $num => $line ) {
			$tot += $line['worked'];
			$html .= "<tr".(($num % 2 <> 0)?" class=\"odd\"":"")."><td>{$line['date']}</td><td>{$line['showname']}</td><td style=\"text-align: right\">{$line['worked']}</td>";
                        $html .= "<td style=\"text-align: center\">" . (($line['submitted'] == 1) ? "YES" : "NO") . "</td>";
			$html .= ( $canedit ) ? "<td style=\"text-align: center\"><a href=\"{$TDTRAC_SITE}edit-hours&id={$line['hid']}\">[-]</a></td>" : "";
			$html .= ( $canedit ) ? "<td style=\"text-align: center\"><a href=\"{$TDTRAC_SITE}del-hours&id={$line['hid']}\">[x]</a></td>" : "";
			$html .= "</tr>\n";
		}
		$html .= "<tr style=\"background-color: #FFCCFF\"><td></td><td style=\"text-align: center\">-=- TOTAL -=-</td><td style=\"text-align: right\">{$tot}</td></tr>\n";
		$html .= "</table>";
	}
	return $html;
}

function hours_view_unpaid() {
	GLOBAL $db, $user_name, $MYSQL_PREFIX, $TDTRAC_DAYRATE, $TDTRAC_SITE;
	$canedit = perms_checkperm($user_name, "edithours");
	$sql  = "SELECT CONCAT(first, ' ', last) as name, u.userid, worked, date, showname, submitted, h.id as hid FROM {$MYSQL_PREFIX}users u, {$MYSQL_PREFIX}shows s, {$MYSQL_PREFIX}hours h WHERE ";
        $sql .= "u.userid = h.userid AND s.showid = h.showid AND h.submitted = 0";
        $sql .= " ORDER BY last ASC, date DESC";
	$maillink  = "{$TDTRAC_SITE}email-hours-unpaid";
	$result = mysql_query($sql, $db);
	while ( $row = mysql_fetch_array($result) ) {
		$dbarray[$row['name']][] = $row;
	}
	$html = "";
	foreach ( $dbarray as $key => $data ) {
		$html .= "<h2>Hours Worked For {$key}</h2><p>\n";
		$html .= "<div style=\"float: right; text-align: right\">[<a href=\"{$maillink}\">E-Mail to Self</a>]\n";
                $html .= "<br />[<a href=\"{$TDTRAC_SITE}hours-set-paid&id={$data[0]['userid']}\">Set All Paid</a>]</div>";
		$html .= ($_REQUEST['sdate'] <> "" ) ? "Start Date: {$_REQUEST['sdate']}\n" : "";
		$html .= ( $_REQUEST['sdate'] <> "" && $_REQUEST['edate'] <> "" ) ? "<br />" : "";
		$html .= ($_REQUEST['edate'] <> "" ) ? "Ending Date: {$_REQUEST['edate']}" : "";
		$html .= "</p><table id=\"budget\">\n";
		$html .= "<tr><th style=\"width: 15em\">Date</th><th>Show</th><th style=\"width:15em\">".(($TDTRAC_DAYRATE)?"Days":"Hours")." Worked</th><th>Paid</th>";
		$html .= ( $canedit ) ? "<th style=\"width: 35px\">Edit</th><th style=\"width: 35px\">Del</th></tr>\n" : "</tr>\n";
		$tot = 0;
		foreach ( $data as $num => $line ) {
			$tot += $line['worked'];
			$html .= "<tr".(($num % 2 <> 0)?" class=\"odd\"":"")."><td>{$line['date']}</td><td>{$line['showname']}</td><td style=\"text-align: right\">{$line['worked']}</td>";
                        $html .= "<td style=\"text-align: center\">" . (($line['submitted'] == 1) ? "YES" : "NO") . "</td>";
			$html .= ( $canedit ) ? "<td style=\"text-align: center\"><a href=\"{$TDTRAC_SITE}edit-hours&id={$line['hid']}\">[-]</a></td>" : "";
			$html .= ( $canedit ) ? "<td style=\"text-align: center\"><a href=\"{$TDTRAC_SITE}del-hours&id={$line['hid']}\">[x]</a></td>" : "";
			$html .= "</tr>\n";
		}
		$html .= "<tr style=\"background-color: #FFCCFF\"><td></td><td style=\"text-align: center\">-=- TOTAL -=-</td><td style=\"text-align: right\">{$tot}</td></tr>\n";
		$html .= "</table>";
	}
	return $html;
}

function hours_set_paid($userid) {
        GLOBAL $db, $MYSQL_PREFIX;
        $sql = "UPDATE {$MYSQL_PREFIX}hours SET submitted = 1 WHERE userid = {$userid}";
        $result = mysql_query($sql, $db);
        $uname = perms_getfnamebyid($userid);
        thrower("Hours for {$name} (ID:{$userid}) Marked Paid");
}

function hours_remind_pick() {
	GLOBAL $db, $MYSQL_PREFIX, $TDTRAC_SITE;
	$sql = "SELECT CONCAT(first, ' ', last) as name, userid FROM {$MYSQL_PREFIX}users WHERE payroll = 1 ORDER BY last DESC";
	$result = mysql_query($sql, $db);
	$html  = "<h2>Send Hours Due Reminder to Employees</h2>\n";
        $html .= "<div id=\"genform\"><form method=\"post\" action=\"{$TDTRAC_SITE}remind-hours\" name=\"form2\">\n";

        $html .= "<div class=\"frmele\">Hours Due Date: <input type=\"text\" size=\"18\" name=\"duedate\" id=\"duedate\" style=\"margin-right: 2px\" />\n";
        $html .= "<a href=\"#\" onClick=\"tdt_show_calendar(".(date(n)-1).",".date(Y).",'pickcal2','duedate')\">[calendar popup]</a></div>\n";
        $html .= "<div class=\"frmele\" id=\"pickcal2\"></div>\n";

        $html .= "<br /><div class=\"frmele\">Start Date of Pay Period: <input type=\"text\" size=\"18\" name=\"sdate\" id=\"sdate2\" style=\"margin-right: 2px\" />\n";
        $html .= "<a href=\"#\" onClick=\"tdt_show_calendar(".(date(n)-1).",".date(Y).",'pickcal3','sdate2')\">[calendar popup]</a></div>\n";
        $html .= "<div class=\"frmele\" id=\"pickcal3\"></div>\n";

        $html .= "<div class=\"frmele\">End Date of Pay Period: <input type=\"text\" size=\"22\" name=\"edate\" id=\"edate2\" style=\"margin-right: 2px\" />\n";
        $html .= "<a href=\"#\" onClick=\"tdt_show_calendar(".(date(n)-1).",".date(Y).",'pickcal4','edate2')\">[cal]</a>\n";
        $html .= " <a href=\"#\" onClick=\"document.forms['form2'].edate2.value='".date("Y-m-d")."'\">[today]</a></div>\n";
        $html .= "<div class=\"frmele\" id=\"pickcal4\"></div>\n";

	$html .= "<br /><div class=\"frmele\"><strong>Employees to remind:</strong><br />";
	while ( $row = mysql_fetch_array($result) ) {
		$html .= "{$row['name']} <input type=\"checkbox\" name=\"toremind[]\" value=\"{$row['userid']}\" /><br />";
	}
	$html .= "</div>\n";
	$html .= "<div class=\"frmele\"><input type=\"submit\" value=\"Send Reminders\" /></div></form></div>\n";

	return $html;
}

function hours_remind_do() {
	GLOBAL $db, $MYSQL_PREFIX, $TDTRAC_SITE;
	$html  = "<h2>Sending Reminders</h2><p>";
	foreach ( $_REQUEST['toremind'] as $remid ) {
		$html .= email_remind($remid, $_REQUEST['duedate'], $_REQUEST['sdate'], $_REQUEST['edate']);
	}
	$html .= "</p>\n";
	return $html;
}

?>
