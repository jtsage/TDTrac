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
	$html  = "<h3>Add Payroll Record</h3>\n";
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
	$html  = "<h3>Edit Payroll Record</h3>\n";
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
	$html  = "<h3>Delete Payroll Record</h3>\n";
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
	$html = "<h3>View By Employee</h3>";
	$form1 = new tdform("{$TDTRAC_SITE}view-hours", "form1");
	$fesult = $form1->addDrop('userid', 'Employee', null, db_list(get_sql_const('emps'), array('userid', 'name')), False);
	$fesult = $form1->addDate('sdate', 'Start Date', null, null, True, 'sdate1');
	$fesult = $form1->addDate('edate', 'End Date', null, null, True, 'edate1');
	$html .= $form1->output('View Hours', 'Leave Dates Blank to See All');
	
	if ( perms_isemp($user_name) ) { return $html; }
	
	$html .= "<h3>View Dated Report</h3>";
	$form2 = new tdform("{$TDTRAC_SITE}view-hours", "form2", $form1->getlasttab(), "genform2");
	$fesult = $form2->addDate('sdate', 'Start Date', null, null, True, 'sdate2');
	$fesult = $form2->addDate('edate', 'End Date', null, null, True, 'edate2');
	$html .= $form2->output('View Hours', 'Leave Dates Blank to See All');

	if ( !perms_isadmin($user_name) ) { return $html; }
	
	$html .= "<h3>View All Un-Paid Hours</h3>";
	$form3 = new tdform("{$TDTRAC_SITE}view-hours-unpaid", "form3", $form2->getlasttab(), "genform3");
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
 * @param integer UserID to view, 0 for all
 * @param bool Show unpaid hours only
 * @return string HTML Output
 */
function hours_view($userid, $unpaidonly = 0) {
	GLOBAL $db, $user_name, $MYSQL_PREFIX, $TDTRAC_DAYRATE, $TDTRAC_SITE;
	if ( $userid == 0 && perms_isemp($user_name) ) { return perms_no(); }
	$canedit = perms_checkperm($user_name, "edithours");
	$sql  = "SELECT CONCAT(first, ' ', last) as name, u.userid, worked, date, showname, submitted, h.id as hid FROM {$MYSQL_PREFIX}users u, {$MYSQL_PREFIX}shows s, {$MYSQL_PREFIX}hours h WHERE ";
	$sql .= "u.userid = h.userid AND s.showid = h.showid";
	$sql .= ($userid <> 0) ? " AND u.userid = '{$userid}'" : "";
	if ( !$unpaidonly ) {
		$sql .= ($_REQUEST['sdate'] <> "") ? " AND h.date >= '{$_REQUEST['sdate']}'" : "";
		$sql .= ($_REQUEST['edate'] <> "") ? " AND h.date <= '{$_REQUEST['edate']}'" : "";
	} else {
		$sql .= " AND h.submitted = 0";
	}
	$sql .= " ORDER BY last ASC, date DESC";
	if ( !$unpaidonly ) {
		$maillink  = "{$TDTRAC_SITE}email-hours&amp;id={$userid}&amp;sdate=";
		$maillink .= ($_REQUEST['sdate'] <> "" ) ? $_REQUEST['sdate'] : "0";
		$maillink .= "&amp;edate=";
		$maillink .= ($_REQUEST['edate'] <> "" ) ? $_REQUEST['edate'] : "0";
	} else { $maillink  = "{$TDTRAC_SITE}email-hours-unpaid"; }
	$result = mysql_query($sql, $db);
	while ( $row = mysql_fetch_array($result) ) {
		$dbarray[$row['name']][] = $row;
	}
	$html = "";
	foreach ( $dbarray as $key => $data ) {
		$html .= "<h3>Hours Worked For: {$key}</h3>\n";
		$html .= "<span class=\"upright\">[<a href=\"{$maillink}\">E-Mail to Self</a>]";
		$html .= ($unpaidonly) ? " [<a href=\"{$TDTRAC_SITE}hours-set-paid&amp;id={$data[0]['userid']}\">Set All Paid</a>]" : "";
		$html .= "</span><ul class=\"datalist\">\n";
		$html .= ($_REQUEST['sdate'] <> "" ) ? "<li>Start Date: {$_REQUEST['sdate']}</li>" : "";
		$html .= ($_REQUEST['edate'] <> "" ) ? "<li>Ending Date: {$_REQUEST['edate']}</li>" : "";
		$html .= "</ul><table class=\"datatable\">\n";
		$html .= "<tr><th>Date</th><th>Show</th><th>".(($TDTRAC_DAYRATE)?"Days":"Hours")." Worked</th><th>Paid</th>";
		$html .= ( $canedit ) ? "<th>Action</th></tr>\n" : "</tr>\n";
		$tot = 0;
		foreach ( $data as $num => $line ) {
			$tot += $line['worked'];
			$html .= "<tr".(($num % 2 <> 0)?" class=\"odd\"":"")."><td>{$line['date']}</td><td>{$line['showname']}</td><td style=\"text-align: right\">{$line['worked']}</td>";
			$html .= "<td style=\"text-align: center\">" . (($line['submitted'] == 1) ? "YES" : "NO") . "</td>";
			$html .= ( $canedit ) ? "<td style=\"text-align: center\"><a title=\"Edit\" href=\"{$TDTRAC_SITE}edit-hours&amp;id={$line['hid']}\"><img class=\"ticon\" src=\"images/edit.png\" alt=\"Edit\" /></a> " : "";
			$html .= ( $canedit ) ? "<a title=\"Delete\" href=\"{$TDTRAC_SITE}del-hours&amp;id={$line['hid']}\"><img class=\"ticon\" src=\"images/delete.png\" alt=\"Delete\" /></a></td>" : "";
			$html .= "</tr>\n";
		}
		$html .= "<tr class=\"datatotal\"><td></td><td style=\"text-align: center\">-=- TOTAL -=-</td><td style=\"text-align: right\">{$tot}</td><td></td><td></td></tr>\n";
		$html .= "</table>\n<br />";
	}
	return $html;
}

/**
 * Show unpaid payroll report (alias to hours_view())
 * 
 * @return string HTML Output
 */
function hours_view_unpaid() {
	return hours_view(0, 1);
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
	$html  = "<h3>Send Hours Due Reminder to Employees</h3>\n";
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
