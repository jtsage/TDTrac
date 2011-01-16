<?php
/**
 * TDTrac Payroll Functions
 * 
 * Contains all payroll related functions. 
 * Data hardened
 * @package tdtrac
 * @version 1.4.0
 * @author J.T.Sage <jtsage@gmail.com>
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
	$form = new tdform("{$TDTRAC_SITE}hours/add/", "form1", 1, 'genform', 'Add Payroll Record');
	
	$result = $form->addDrop('userid', 'Employee', null, db_list(get_sql_const('emps'), array('userid', 'name')), False);
	$result = $form->addDrop('showid', 'Show', null, db_list(get_sql_const('showid'), array('showid', 'showname')), False);
	$result = $form->addDate('date', 'Date');
	$result = $form->addText('worked', (($TDTRAC_DAYRATE)?"Days":"Hours")." Worked");
	$result = $form->addHidden('new-hours', true);
	
	return $form->output('Add Hours');
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
	$sql .= "SELECT h.*, CONCAT(first, ' ', last) as name FROM {$MYSQL_PREFIX}hours h, {$MYSQL_PREFIX}users u WHERE h.userid = u.userid AND h.id = " . intval($hid) . " LIMIT 1";
	$result = mysql_query($sql, $db);
	$recd = mysql_fetch_array($result);
	$form = new tdform("{$TDTRAC_SITE}hours/edit/{$hid}/", "form1", 1, 'genform', 'Edit Payroll Record');
	
	$fesult = $form->addDrop('userid', 'Employee', null, array(array($recd['userid'], $recd['name'])), False);
	$fesult = $form->addDrop('showid', 'Show', null, db_list(get_sql_const('showid'), array('showid', 'showname')), False, $recd['showid']);
	$fesult = $form->addDate('date', 'Date', null, $recd['date']);
	$fesult = $form->addText('worked', (($TDTRAC_DAYRATE)?"Days":"Hours")." Worked", null, $recd['worked']);
	$fesult = $form->addCheck('submitted', 'Hours Paid Out', null, $recd['submitted']);
	$fesult = $form->addHidden('id', $hid);
	
	return $form->output('Edit Hours');
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
	$sql .= "SELECT h.*, CONCAT(first, ' ', last) as name, showname FROM {$MYSQL_PREFIX}hours h, {$MYSQL_PREFIX}users u, {$MYSQL_PREFIX}shows s WHERE h.userid = u.userid AND h.showid = s.showid AND h.id = ".intval($hid)." LIMIT 1";
	$result = mysql_query($sql, $db);
	$recd = mysql_fetch_array($result);
	$form = new tdform("{$TDTRAC_SITE}hours/del/{$del}/", "form1", 1, 'genform', 'Delete Payroll Record');
	
	$fesult = $form->addDrop('userid', 'Employee', null, array(array($recd['userid'], $recd['name'])), False, $recd['userid'], False);
	$fesult = $form->addDrop('showid', 'Show', null, db_list(get_sql_const('showid'), array('showid', 'showname')), False, $recd['showid'], False);
	$fesult = $form->addDate('date', 'Date', null, $recd['date'], False);
	$fesult = $form->addText('worked', (($TDTRAC_DAYRATE)?"Days":"Hours")." Worked", null, $recd['worked'], False);
	$fesult = $form->addCheck('submitted', 'Hours Paid Out', null, $recd['submitted'], False);
	$fesult = $form->addHidden('id', $hid);
	
	return $form->output('Confirm Delete');
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
	$sqlstring  = "INSERT INTO `{$MYSQL_PREFIX}hours` ( `userid`, `showid`, `date`, `worked` )";
	$sqlstring .= " VALUES ( %d, %d, '%s', '%f' )";

	$sql = sprintf($sqlstring,
		intval($_REQUEST['userid']),
		intval($_REQUEST['showid']),
		mysql_real_escape_string($_REQUEST['data']),
		floatval($_REQUEST['worked'])
	);

	$fromid = perms_getidbyname($user_name);
	$msg = "{$user_name} Added Hours: ".floatval($_REQUEST['worked'])." for ".mysql_real_escape_string($_REQUEST['date']);

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
	if ( $result ) {
		thrower("Hours Added");
	} else {
		thrower("Hours Add :: Operation Failed");
	}
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
	$thissubmit = ($_REQUEST['submitted'] == "y") ? "1" : "0";
	
	$sqlstring = "UPDATE `{$MYSQL_PREFIX}hours` SET `showid` = %d, `date` = '%s', `worked` = '%f', submitted = %d WHERE id = %d";

	$sql = sprintf($sqlstring,
		intval($_REQUEST['showid']),
		mysql_real_escape_string($_REQUEST['date']),
		floatval($_REQUEST['worked']),
		$thissubmit,
		intval($id)
	);

	$result = mysql_query($sql, $db);

	if ( $result ) {
		thrower("Hours Record #{$id} Updated");
	} else {
		thrower("Hours Update :: Operation Failed");
	}
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
	$sql = "DELETE FROM {$MYSQL_PREFIX}hours WHERE id = ".intval($id)." LIMIT 1";
	$result = mysql_query($sql, $db);
	if ( $result ) {
		thrower("Hours Record #{$id} Deleted");
	} else {
		thrower("Hours Delete :: Operation Failed");
	}
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
	$form1 = new tdform("{$TDTRAC_SITE}hours/view/", "form1", 1, 'genform', 'View By Employee');
	$fesult = $form1->addDrop('userid', 'Employee', null, db_list(get_sql_const('emps'), array('userid', 'name')), False);
	$fesult = $form1->addDate('sdate', 'Start Date', null, null, True, 'sdate1');
	$fesult = $form1->addDate('edate', 'End Date', null, null, True, 'edate1');
	$html = $form1->output('View Hours', 'Leave Dates Blank to See All');
	
	if ( perms_isemp($user_name) ) { return $html; }
	
	$form2 = new tdform("{$TDTRAC_SITE}hours/view/", "form2", $form1->getlasttab(), "genform2", 'View Dated Report');
	$fesult = $form2->addDate('sdate', 'Start Date', null, null, True, 'sdate2');
	$fesult = $form2->addDate('edate', 'End Date', null, null, True, 'edate2');
	$html = array_merge($html, $form2->output('View Hours', 'Leave Dates Blank to See All'));

	if ( !perms_isadmin($user_name) ) { return $html; }
	
	$form3 = new tdform("{$TDTRAC_SITE}hours/view/unpaid/", "form3", $form2->getlasttab(), "genform3", "View Unpaid Hours");
	$html = array_merge($html, $form3->output('View Hours'));
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
		$sql .= ($_REQUEST['sdate'] <> "") ? " AND h.date >= '".mysql_real_escape_string($_REQUEST['sdate'])."'" : "";
		$sql .= ($_REQUEST['edate'] <> "") ? " AND h.date <= '".mysql_real_escape_string($_REQUEST['edate'])."'" : "";
	} else {
		$sql .= " AND h.submitted = 0";
	}
	$sql .= " ORDER BY last ASC, date DESC";
	if ( !$unpaidonly ) {
		$maillink  = "{$TDTRAC_SITE}hours/email/&amp;id={$userid}&amp;sdate=";
		$maillink .= ($_REQUEST['sdate'] <> "" ) ? $_REQUEST['sdate'] : "0";
		$maillink .= "&amp;edate=";
		$maillink .= ($_REQUEST['edate'] <> "" ) ? $_REQUEST['edate'] : "0";
	} else { $maillink  = "{$TDTRAC_SITE}hours/email/unpaid/"; }
	$result = mysql_query($sql, $db);
	if ( mysql_num_rows($result) < 1 ) { return array("<h3>Empty Data Set</h3>", "<p>There are no payroll items matching your terms.</p>"); }
	while ( $row = mysql_fetch_array($result) ) {
		$dbarray[$row['name']][] = $row;
	}
	foreach ( $dbarray as $key => $data ) {
		$html[] = "<h3>Hours Worked For: {$key}</h3>";
		$html[] = "<span class=\"upright\">[<a href=\"{$maillink}\">E-Mail to Self</a>]".
			($unpaidonly) ? " [<a href=\"{$TDTRAC_SITE}hours/clear/{$data[0]['userid']}/\">Set All Paid</a>]" : "" .
			"</span>";
		$html[] = "<ul class=\"datalist\">";
		$html[] = ($_REQUEST['sdate'] <> "" ) ? "<li>Start Date: {$_REQUEST['sdate']}</li>" : "";
		$html[] = ($_REQUEST['edate'] <> "" ) ? "<li>Ending Date: {$_REQUEST['edate']}</li>" : "";
		$html[] = "</ul>";
		$tabl = new tdtable("hours");
		$tabl->addHeader(array('Date', 'Show', (($TDTRAC_DAYRATE)?"Days":"Hours")." Worked", 'Paid'));
		$tabl->addNumber((($TDTRAC_DAYRATE)?"Days":"Hours")." Worked");
		$tabl->setAlign('Paid', "center");
		if ( $canedit ) { $tabl->addAction(array('pedit', 'pdel')); }
		
		foreach ( $data as $num => $line ) {
			$tabl->addRow(array($line['date'], $line['showname'], $line['worked'], (($line['submitted'] == 1) ? "YES" : "NO")), $line);
		}
		$html = array_merge($html, $tabl->output(false));
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
	$sql = "UPDATE {$MYSQL_PREFIX}hours SET submitted = 1 WHERE userid = ".intval($userid);
	$result = mysql_query($sql, $db);
	$uname = perms_getfnamebyid($userid);
	if ( $result ) {
		thrower("Hours for {$name} (ID:{$userid}) Marked Paid");
	} else {
		thrower("Hours Clear :: Operation Failed");
	}
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
	$form = new tdform("{$TDTRAC_SITE}hours/remind/", 'form2', 1, 'genform', 'Send Payroll Reminder');
	
	$fesult = $form->addDate('duedate', 'Hours Due Date');
	$fesult = $form->addDate('sdate', 'Start Date');
	$fesult = $form->addDate('edate', 'End Date');
	$fesult = $form->addInfo('<strong>Employees to remind:</strong>');
	while ( $row = mysql_fetch_array($result) ) {
		$fesult = $form->addCheck('toremind[]', $row['name'], null, False, True, $row['userid']);
	}
	
	return $form->output('Send Reminders');
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
	foreach ( $_REQUEST['toremind'] as $remid ) {
		$results[] = email_remind(
			intval($remid), 
			mysql_real_escape_string($_REQUEST['duedate']),
			mysql_real_escape_string($_REQUEST['sdate']),
			mysql_real_escape_string($_REQUEST['edate'])
		);
	}
	$msg  = "Sent Reminders<br />";
	$msg .= join($results);
	thrower($msg);
}

?>
