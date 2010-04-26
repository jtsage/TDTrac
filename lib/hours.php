<?php
/**
 * TDTrac Payroll Functions
 * 
 * Contains all payroll related functions. 
 * @package tdtrac
 * @version 1.3.0
 */

/**
 * Show hours add form
 * 
 * @global resource Database Link
 * @global string User Name
 * @global string MySQL Table Prefix
 * @global bool Use daily or hourly pay rates
 * @global string Site address for links
 * @return HTML output
 */
function hours_add () {
	GLOBAL $db, $user_name, $MYSQL_PREFIX, $TDTRAC_DAYRATE, $TDTRAC_SITE;
	$html  = "<h2>Add Payroll Record</h2>\n";
	$form = new tdform("{$TDTRAC_SITE}add-hours", "form1");
	
	$result = $form->addDrop('userid', 'Employee', null, db_list(get_sql_const('emps'), array('userid', 'name')), False);
	$result = $form->addDrop('showid', 'Show', null, db_list(get_sql_const('showid'), array('showid', 'showname')), False);
	$result = $form->addDate('date', 'Date');
	$result = $form->addText('worked', (($TDTRAC_DAYRATE)?"Days":"Hours")." Worked");
	
	$html .= $form->output('Add Hours');
	return $html;
}

/**
 * Show hours edit form
 * 
 * @global resource Database Link
 * @global string User Name
 * @global string MySQL Table Prefix
 * @global bool Use daily or hourly pay rates
 * @global string Site address for links
 * @param integer Payroll ID to edit
 * @return HTML output
 */
function hours_edit ($hid) {
	GLOBAL $db, $user_name, $MYSQL_PREFIX, $TDTRAC_DAYRATE, $TDTRAC_SITE;
	$sql .= "SELECT h.*, CONCAT(first, ' ', last) as name FROM {$MYSQL_PREFIX}hours h, {$MYSQL_PREFIX}users u WHERE h.userid = u.userid AND h.id = {$hid} LIMIT 1";
	$result = mysql_query($sql, $db);
	$recd = mysql_fetch_array($result);
	$html  = "<h2>Edit Payroll Record</h2>\n";
	$form = new tdform("{$TDTRAC_SITE}edit-hours", "form1");
	
	$fesult = $form->addDrop('userid', 'Employee', null, array(array($recd['userid'], $recd['name'])), False);
	$fesult = $form->addDrop('showid', 'Show', null, db_list(get_sql_const('showid'), array('showid', 'showname')), False, $recd['showid']);
	$fesult = $form->addDate('date', 'Date', null, $recd['date']);
	$fesult = $form->addText('worked', (($TDTRAC_DAYRATE)?"Days":"Hours")." Worked", null, $recd['worked']);
	$fesult = $form->addCheck('submitted', 'Hours Paid Out', null, $recd['submitted']);
	$fesult = $form->addHidden('id', $hid);
	
	$html .= $form->output('Edit Hours');
	return $html;
}

/**
 * Show hours delete confirmation form
 * 
 * @global resource Database Link
 * @global string User Name
 * @global string MySQL Table Prefix
 * @global bool Use daily or hourly pay rates
 * @global string Site address for links
 * @param integer Payroll ID to remove
 * @return HTML output
 */
function hours_del ($hid) {
	GLOBAL $db, $user_name, $MYSQL_PREFIX, $TDTRAC_DAYRATE, $TDTRAC_SITE;
	$sql .= "SELECT h.*, CONCAT(first, ' ', last) as name, showname FROM {$MYSQL_PREFIX}hours h, {$MYSQL_PREFIX}users u, {$MYSQL_PREFIX}shows s WHERE h.userid = u.userid AND h.showid = s.showid AND h.id = {$hid} LIMIT 1";
	$result = mysql_query($sql, $db);
	$recd = mysql_fetch_array($result);
	$html  = "<h2>Delete Payroll Record</h2>\n";
	$form = new tdform("{$TDTRAC_SITE}edit-hours", "form1");
	
	$fesult = $form->addDrop('userid', 'Employee', null, array(array($recd['userid'], $recd['name'])), False, $recd['userid'], False);
	$fesult = $form->addDrop('showid', 'Show', null, db_list(get_sql_const('showid'), array('showid', 'showname')), False, $recd['showid'], False);
	$fesult = $form->addDate('date', 'Date', null, $recd['date'], False);
	$fesult = $form->addText('worked', (($TDTRAC_DAYRATE)?"Days":"Hours")." Worked", null, $recd['worked'], False);
	$fesult = $form->addCheck('submitted', 'Hours Paid Out', null, $recd['submitted'], False);
	$fesult = $form->addHidden('id', $hid);
	
	$html .= $form->output('Confirm Delete');
	return $html;
}

/**
 * Logic to add payroll record to database
 * 
 * @global resource Database Link
 * @global string MySQL Table Prefix
 * @global string User Name
 */
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

/**
 * Logic to commit edit to database
 * 
 * @global resource Database Link
 * @global string MySQL Table Prefix
 * @param integer Payroll ID to edit
 */
function hours_edit_do($id) {
	GLOBAL $db, $MYSQL_PREFIX;
	$sql = "UPDATE {$MYSQL_PREFIX}hours SET showid = '{$_REQUEST['showid']}', date = '{$_REQUEST['date']}', worked = '{$_REQUEST['worked']}', submitted = ".(($_REQUEST['submitted'] == "y") ? "1" : "0")." WHERE id = '{$id}'";
	$result = mysql_query($sql, $db);
	thrower("Hours Record #{$id} Updated");
}

/**
 * Logic to remove hours from database
 * 
 * @global resource Database Link
 * @global string MySQL Table Prefix
 * @param integer Payroll ID to remove
 */
function hours_del_do($id) {
	GLOBAL $db, $MYSQL_PREFIX;
	$sql = "DELETE FROM {$MYSQL_PREFIX}hours WHERE id = '{$id}' LIMIT 1";
	$result = mysql_query($sql, $db);
	thrower("Hours Record #{$id} Deleted");
}


/**
 * Show pick form for hours view.
 * 
 * @global resource Database Link
 * @global string User Name
 * @global string MySQL Table Prefix
 * @global string Site address for links
 * @return string HTML Output
 */
function hours_view_pick() {
	GLOBAL $db, $user_name, $MYSQL_PREFIX, $TDTRAC_SITE;
	$html = "<h2>View By Employee</h2>";
	$form1 = new tdform("{$TDTRAC_SITE}view-hours", "form1");
	$fesult = $form1->addDrop('userid', 'Employee', null, db_list(get_sql_const('emps'), array('userid', 'name')), False);
	$fesult = $form1->addDate('sdate', 'Start Date', null, null, True, 'sdate1');
	$fesult = $form1->addDate('edate', 'End Date', null, null, True, 'edate1');
	$html .= $form1->output('View Hours', 'Leave Dates Blank to See All');
	
	if ( perms_isemp($user_name) ) { return $html; }
	
	$html .= "<h2>View Dated Report</h2>";
	$form2 = new tdform("{$TDTRAC_SITE}view-hours", "form2", $form1->getlasttab());
	$fesult = $form2->addDate('sdate', 'Start Date', null, null, True, 'sdate2');
	$fesult = $form2->addDate('edate', 'End Date', null, null, True, 'edate2');
	$html .= $form2->output('View Hours', 'Leave Dates Blank to See All');

	if ( !perms_isadmin($user_name) ) { return $html; }
	
	$html .= "<h2>View All Un-Paid Hours</h2>";
	$form3 = new tdform("{$TDTRAC_SITE}view-hours-unpaid", "form3", $form2->getlasttab());
	$html .= $form3->output('View Hours');
	return $html;	
}

/**
 * Show payroll report
 * 
 * @global resource Database Link
 * @global string User Name
 * @global string MySQL Table Prefix
 * @global bool Use daily or hourly wages
 * @global string Site address for links
 * @return string HTML Output
 */
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

/**
 * Show unpaid payroll report
 * 
 * @global resource Database Link
 * @global string User Name
 * @global string MySQL Table Prefix
 * @global bool Use daily or hourly wages
 * @global string Site address for links
 * @return string HTML Output
 */
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

/**
 * Set all hours paid
 * 
 * @global resource Database Link
 * @global string MySQL Table Prefix
 */
function hours_set_paid($userid) {
	GLOBAL $db, $MYSQL_PREFIX;
	$sql = "UPDATE {$MYSQL_PREFIX}hours SET submitted = 1 WHERE userid = {$userid}";
	$result = mysql_query($sql, $db);
	$uname = perms_getfnamebyid($userid);
	thrower("Hours for {$name} (ID:{$userid}) Marked Paid");
}

/**
 * Show hours reminder email options form
 * 
 * @global resource Database Link
 * @global string MySQL Table Prefix
 * @global string Site address for links
 * @return HTML output
 */
function hours_remind_pick() {
	GLOBAL $db, $MYSQL_PREFIX, $TDTRAC_SITE;
	$sql = "SELECT CONCAT(first, ' ', last) as name, userid FROM {$MYSQL_PREFIX}users WHERE payroll = 1 ORDER BY last DESC";
	$result = mysql_query($sql, $db);
	$html  = "<h2>Send Hours Due Reminder to Employees</h2>\n";
	$form = new tdform("{$TDTRAC_SITE}remind-hours", 'form2');
	
	$fesult = $form->addDate('duedate', 'Hours Due Date');
	$fesult = $form->addDate('sdate', 'Start Date of Pay Period');
	$fesult = $form->addDate('edate', 'End Date of Pay Period');
	$fesult = $form->addInfo('<strong>Employees to remind:</strong>');
	while ( $row = mysql_fetch_array($result) ) {
		$fesult = $form->addCheck('toremind[]', $row['name'], null, False, True, $row['userid']);
	}
	
	$html .= $form->output('Send Reminders');
	return $html;
}

/**
 * Logic to send reminders
 * 
 * @global resource Database Link
 * @global string MySQL Table Prefix
 * @global string Site address for links
 * @return HTML output
 */
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
