<?php
/**
 * TDTrac Access Control Functions
 * 
 * Contains all access control framework
 * Data hardened
 * @package tdtrac
 * @version 1.4.0
 * @author J.T.Sage <jtsage@gmail.com>
 */

/**
 * Return a list of groups
 * 
 * @global resource Database Link
 * @global string MySQL Table Prefix
 * @param string User Name
 * @return array List of groups
 */
function perms_getgroups($username) {
	GLOBAL $db, $MYSQL_PREFIX;
	$sql = sprintf("SELECT groupname FROM `{$MYSQL_PREFIX}groupnames` gn, `{$MYSQL_PREFIX}usergroups` ug, `{$MYSQL_PREFIX}users` u WHERE username = '%s' AND u.userid = ug.userid AND ug.groupid = gn.groupid",
		mysql_real_escape_string($username)
	);
	$result = mysql_query($sql, $db);
	while ( $row = mysql_fetch_array($result) ) {
	   $retty[] = $row['groupname'];
	}
	return $retty;
}

/**
 * Return a userid from a user name
 * 
 * @global resource Database Link
 * @global string MySQL Table Prefix
 * @param string User Name
 * @return integer User ID
 */
function perms_getidbyname($username) {
	GLOBAL $db, $MYSQL_PREFIX;
	$sql = "SELECT userid FROM `{$MYSQL_PREFIX}users` WHERE username = '".mysql_real_escape_string($username)."'";
	$result = mysql_query($sql, $db);
	$row = mysql_fetch_array($result);
	return $row['userid'];
}

/**
 * Return a first name from a userid
 * 
 * @global resource Database Link
 * @global string MySQL Table Prefix
 * @param integer User ID
 * @return string User First Name
 */
function perms_getfnamebyid($userid) {
        GLOBAL $db, $MYSQL_PREFIX;
        $sql = "SELECT first FROM `{$MYSQL_PREFIX}users` WHERE userid = ".intval($userid);
        $result = mysql_query($sql, $db);
        $row = mysql_fetch_array($result);
        return $row['first'];
}

/**
 * Return if a user has the named permission
 * 
 * @global resource Database Link
 * @global string MySQL Table Prefix
 * @param string User Name
 * @param string Permission Name
 * @return bool Action is allowed
 */
function perms_checkperm($username, $permission) {
	GLOBAL $db, $MYSQL_PREFIX;
	$sql = sprintf("SELECT `permcan` FROM `{$MYSQL_PREFIX}permissions` pm, `{$MYSQL_PREFIX}usergroups` ug, `{$MYSQL_PREFIX}users` u WHERE username = '%s' AND u.userid = ug.userid AND ug.groupid = pm.groupid AND pm.permid = '{$permission}'",
		mysql_real_escape_string($username)
	);
	$result = mysql_query($sql, $db);
	if ( mysql_num_rows($result) < 1 ) { return false; }
	while ( $row = mysql_fetch_array($result)) {
		if ( $row['permcan'] ) { return true; }
	}
	return false;
}

/**
 * Return if a user is an employee ('limithours' property)
 * 
 * @global resource Database Link
 * @global string MySQL Table Prefix
 * @param string User Name
 * @return bool User is an employee
 */
function perms_isemp($username) { 
	GLOBAL $db, $MYSQL_PREFIX;
	$sql = "SELECT `limithours` FROM `{$MYSQL_PREFIX}users` u WHERE username = '".mysql_real_escape_string($username)."' LIMIT 1";
	$result = mysql_query($sql, $db);
	$line = mysql_fetch_array($result);
	if ( $line['limithours'] ) { return true; }
	return false;
}

/**
 * Return if a user is an admin
 * 
 * @global resource Database Link
 * @global string MySQL Table Prefix
 * @param string User Name
 * @return bool User is an admin
 */
function perms_isadmin($username) {
	GLOBAL $db, $MYSQL_PREFIX;
	$sql = "SELECT `groupid` FROM `{$MYSQL_PREFIX}usergroups` ug, `{$MYSQL_PREFIX}users` u WHERE username = '".mysql_real_escape_string($username)."' AND u.userid = ug.userid AND ug.groupid = 1";
	$result = mysql_query($sql, $db);
	if ( mysql_num_rows($result) > 0 ) { return true; }
	return false;
}

/**
 * Returns a group ID
 * 
 * @global resource Database Link
 * @global string MySQL Table Prefix
 * @param string group name
 * @return integer Group ID
 */
function perms_getgroupid($group) {
	GLOBAL $db, $MYSQL_PREFIX;
	$sql = "SELECT `groupid` FROM `{$MYSQL_PREFIX}groupnames` WHERE groupname = '".mysql_real_escape_string($group)."' LIMIT 1;";
	$result = mysql_query($sql, $db);
	$row = mysql_fetch_array($result);
	return $row['groupid'];
}

/**
 * Return a notice that user has no permissions for action
 * 
 * @return array HTML output
 */
function perms_no() {
	return(array("<h2>You Do Not Have Permission to Use This Function</h2>","<p>Please <a href=\"/\">return home</a>.</p>"));
}

/**
 * Return a notice that the user performed an illegal function (error)
 * 
 * @return array HTML output
 */
function perms_error() {
	return(array("<h2>Error</h2>","<p>An unspecified error occured.  Please try again</p>"));
}

/**
 * Return a message that the user has attempted to do something very bad.
 * Note: used as part of formatted HTML, not as standalone page
 * 
 * @return string HTML output
 */
function perms_fail() {
	return "<h2>You Have Attempted to haxor the system, or something unexplained has happened.</h2><p>Please <a href=\"/\">return home.</a></p>";
}

/**
 * Show permission edit pick form
 * 
 * @global resource Database Link
 * @global string MySQL Table Prefix
 * @global string Site Address for links
 * @return string HTML output
 */
function perms_editpickform() {
	GLOBAL $db, $MYSQL_PREFIX, $TDTRAC_SITE;
	$sql = "SELECT `groupname` FROM `{$MYSQL_PREFIX}groupnames`";
	$form = new tdform("{$TDTRAC_SITE}user/perms/edit/", 'genform', 1, 'genform', 'Select Group');
	$result = $form->addHidden("editgroupperm", "true");
	$result = $form->addDrop('groupname', "Group", null, db_list($sql, 'groupname'), False);
	return $form->output('Select');
}

/**
 * Show permission edit form
 * 
 * @global resource Database Link
 * @global array Name of all known permissions
 * @global string MySQL Table Prefix
 * @global string Site Address for links
 * @return string HTML output
 */
function perms_editform() {
	GLOBAL $db, $TDTRAC_PERMS, $MYSQL_PREFIX, $TDTRAC_SITE;
	$grpname = $_REQUEST['groupname'];
	$grpid = perms_getgroupid($grpname);
	$html[] = "<h3>Group :: {$grpname} ({$grpid})</h3>";
	$form = new tdform("{$TDTRAC_SITE}user/perms/edit/", 'genform', 1, 'genform', 'Edit Permissions');

	$fesult = $form->addInfo("T / F");
	$fesult = $form->addHidden('grpid', $grpid);
	$sql = "SELECT permid, permcan FROM {$MYSQL_PREFIX}permissions pm WHERE groupid = {$grpid}";
	$result = mysql_query($sql, $db);
	while ( $row = mysql_fetch_array($result) ) {
		$pname = $row['permid']; $pvalue = $row['permcan'];
		$dbperm[$pname] = $pvalue;
	}
	foreach ( $TDTRAC_PERMS as $perm ) {
		$fesult = $form->addRadio($perm, $perm, null, $dbperm[$perm]);
	}	
	$html = array_merge($html, $form->output('Save'));
	return $html;
}

/**
 * Save permissions to database
 * 
 * @global resource Database Link
 * @global array Name of all known permissions
 * @global string MySQL Table Prefix
 */
function perms_save($grpid) {
	GLOBAL $db, $TDTRAC_PERMS, $MYSQL_PREFIX;
	if ( !is_numeric($grpid) ) { thrower("Oops :: Operation Failed"); }
	$sql = "DELETE FROM `{$MYSQL_PREFIX}permissions` WHERE groupid = ".intval($grpid);
	$result = mysql_query($sql, $db);
	foreach ( $TDTRAC_PERMS as $perm ) {
		$sql = sprintf("INSERT INTO `{$MYSQL_PREFIX}permissions` (groupid, permid, permcan) VALUES (%d, '%s', %d)",
			intval($grpid),
			$perm,
			(($_REQUEST[$perm]) ? "1" : "0")
		);
		mysql_query($sql, $db);
	}
	thrower("Permissions Updated");
}

/**
 * View all permissions
 * 
 * @global resource Database Link
 * @global array Name of all known permissions
 * @global string MySQL Table Prefix
 * @return string HTML output
 */
function perms_view() {
	GLOBAL $db, $TDTRAC_PERMS, $MYSQL_PREFIX;
	$sql = "SELECT groupname, permid FROM `{$MYSQL_PREFIX}groupnames` gn, `{$MYSQL_PREFIX}permissions` pm WHERE pm.groupid = gn.groupid AND pm.permcan = 1 ORDER BY groupname, permid";
	$result = mysql_query($sql, $db);
	while ( $row = mysql_fetch_array($result) ) {
		$disperm[$row['groupname']][] = $row['permid'];
	}
	foreach ( $disperm as $name => $value ) {
		$names = array();
		$html[] = "<h3>Permissions For Group :: {$name}</h3>";
		$sql = "SELECT u.username FROM `{$MYSQL_PREFIX}users` u, `{$MYSQL_PREFIX}groupnames` gn, `{$MYSQL_PREFIX}usergroups` ug WHERE gn.groupname = '{$name}' AND gn.groupid = ug.groupid AND ug.userid = u.userid ORDER BY username ASC";
		$result = mysql_query($sql, $db);
		while ( $row = mysql_fetch_array($result) ) {
			$names[] .= $row['username'];
		}
		$html[] = "<ul class=\"datalist\"><li><strong>Users :: </strong><em>" . join("</em>, <em>", $names) . "</em>";
		$html[] = "  <ul class=\"datalist\" style=\"font-size: .8em\">"; 
		foreach ( $value as $pval ) {
			$html[] = "    <li>{$pval}</li>";
		}
		$html[] = "</ul></li></ul>";
	}
	return $html;
}

/**
 * Show add user form
 * 
 * @global resource Database Link
 * @global string MySQL Table Prefix
 * @global string Site Address for links
 * @return string HTML output
 */
function perms_adduser_form() {
	GLOBAL $db, $MYSQL_PREFIX, $TDTRAC_SITE;
	$form = new tdform("{$TDTRAC_SITE}user/add/", 'genform', 1, 'genform', 'Add User');
	
	$result = $form->addText('username', "User Name");
	$result = $form->addText('password', "Password");
	$result = $form->addText('first', "First Name");
	$result = $form->addText('last', "Last Name");
	$result = $form->addText('phone', "Phone");
	$result = $form->addText('email', "E-Mail");
	$result = $form->addDrop('groupid', "Group", null, db_list("SELECT groupname, groupid FROM {$MYSQL_PREFIX}groupnames ORDER BY groupid DESC;", array('groupid', 'groupname')), False);
	
	return $form->output('Add User');
}

/**
 * Logic to add user to database
 * 
 * @global resource Database Link
 * @global string MySQL Table Prefix
 * @global double Default Payrate
 */
function perms_adduser_do() {
	GLOBAL $db, $MYSQL_PREFIX, $TDTRAC_PAYRATE;
	$sqlstring  = "INSERT INTO `{$MYSQL_PREFIX}users` ( `username`, `first`, `last`, `password`, `phone`, `email`, `payrate` )";
	$sqlstring .= " VALUES ( '%s', '%s', '%s', '%s', '%d', '%s', '%f' )";
	
	$sql = sprintf($sqlstring,
		mysql_real_escape_string($_REQUEST['username']),
		mysql_real_escape_string($_REQUEST['first']),
		mysql_real_escape_string($_REQUEST['last']),
		mysql_real_escape_string($_REQUEST['password']),
		intval($_REQUEST['phone']),
		mysql_real_escape_string($_REQUEST['email']),
		$TDTRAC_PAYRATE
	);
	$result = mysql_query($sql, $db);
	if ( $result ) {
		$userid = mysql_insert_id($db);
		$sql2 = "INSERT INTO `{$MYSQL_PREFIX}usergroups` ( `userid`, `groupid` ) VALUES ( '{$userid}' , '".intval($_REQUEST['groupid'])."' )";
		$result2 = mysql_query($sql2, $db);
		if ( $result2 ) {
			thrower("User Added");
		} else {
			thrower("Error:: Group set failed");
		}
	} else {
		thrower("User Add :: Operation Failed");
	}
}

/**
 * Show change password form
 * 
 * @global string Site Address for links
 * @return string HTML output
 */
function perms_changepass_form() {
	GLOBAL $TDTRAC_SITE;
	$form = new tdform("{$TDTRAC_SITE}/user/password/", 'genform', 1, 'genform', 'Change Password');
	$result = $form->addPass('newpass1', "New Password");
	$result = $form->addPass('newpass2', "Verify Password");
	return $form->output('Change Password');
}

/**
 * Logic to change password in database
 * 
 * @global resource Database Link
 * @global string User Name
 * @global string MySQL Table Prefix
 */
function perms_changepass_do() {
	GLOBAL $db, $user_name, $MYSQL_PREFIX;
	if ( $_REQUEST['newpass1'] == $_REQUEST['newpass2'] ) {
		if ( strlen($_REQUEST['newpass1']) < 4 ) { thrower("Password must be at least 5 characters"); }
		if ( strlen($_REQUEST['newpass1']) > 15 ) { thrower("Password may not exceed 15 characters"); }
		$sql = sprintf("UPDATE `{$MYSQL_PREFIX}users` SET `chpass` = 0 , `password` = '%d' WHERE `username` = '{$user_name}' LIMIT 1",
			mysql_real_escape_string($_REQUEST['newpass1'])
		);
		$result = mysql_query($sql, $db);
		thrower("Password Changed - Please Re-Login");
	} else { thrower("Password Mismatch - Not Changed"); }
}

/**
 * View all users
 * 
 * @global resource Database Link
 * @global string MySQL Table Prefix
 * @global string Site Address for links
 * @return string HTML output
 */
function perms_viewuser() {
	GLOBAL $db, $MYSQL_PREFIX, $TDTRAC_SITE;
	$sql = "SELECT *, DATE_FORMAT(lastlogin, '%b %D %h:%i %p') AS lastlog FROM `{$MYSQL_PREFIX}users` ORDER BY last ASC, first ASC";
	$result = mysql_query($sql, $db); $html = "";
	while ( $row = mysql_fetch_array($result) ) {
		$html[] = "<h3>User: {$row['first']} {$row['last']}</h3><p><ul class=\"datalist\">";
		$html[] = "<span class=\"overright\">[<a href=\"{$TDTRAC_SITE}user/edit/{$row['userid']}/\">Edit</a>]</span>";
		$html[] = "  <li>Internal UserID: <strong>{$row['userid']}</strong> (Last Login: {$row['lastlog']})<ul><li> (Active: <input type=\"checkbox\" disabled=\"disabled\"".(($row['active'])?" checked=\"checked\" ":"").">)</li>";
		$html[] = "  <li>(On Payroll: <input type=\"checkbox\" disabled=\"disabled\"".(($row['payroll'])?" checked=\"checked\" ":"").">)</li>";
		$html[] = "  <li>(Add / View / Edit only Own Hours: <input type=\"checkbox\" disabled=\"disabled\"".(($row['limithours'])?" checked=\"checked\" ":"").">)</li>";
		$html[] = "  <li>(Notify of Employee add Payroll: <input type=\"checkbox\" disabled=\"disabled\"".(($row['notify'])?" checked=\"checked\" ":"").">)</li></ul></li>";
		$html[] = "  <li>User Name: <strong>{$row['username']}</strong></li>";
		$html[] = "  <li>Group : <strong>" . join(", ", perms_getgroups($row['username'])) . "</strong></li>";
		if ( !is_null($row['phone']) && $row['phone'] <> "" && $row['phone'] <> 0 ) {
			$html[] = "  <li>Phone Number: <strong>". format_phone($row['phone']) . "</strong></li>";
		}
		if ( !is_null($row['email']) && $row['email'] <> "" ) {
			$html[] = "  <li>E-Mail Address: <a href=\"mailto:{$row['email']}\"><strong>{$row['email']}</strong></a></li>";
		}
		$html[] = "  <li>Pay Rate: \$" . number_format($row['payrate'], 2) . "</li>";
		$html[] = "</ul></p>";
	}
	return $html;
}

/**
 * Show edit user form
 * 
 * @global resource Database Link
 * @global string MySQL Table Prefix
 * @global string Site Address for links
 * @param integer User ID to edit
 * @return string HTML output
 */
function perms_edituser_form($id) {
	GLOBAL $db, $MYSQL_PREFIX, $TDTRAC_SITE;
	$sql = "SELECT u.*, groupid FROM `{$MYSQL_PREFIX}users` u, `{$MYSQL_PREFIX}usergroups` ug WHERE u.userid = ug.userid AND u.userid = ".intval($id)." LIMIT 1";
	$result = mysql_query($sql, $db);
	$row = mysql_fetch_array($result);
	$form = new tdform("{$TDTRAC_SITE}user/edit/{$id}", 'genform', 1, 'genform', 'Edit User');
	
	$fesult = $form->addText('username', "User Name", null, $row['username']);
	$fesult = $form->addText('password', "Password", null, $row['password']);
	$fesult = $form->addText('payrate', "Pay Rate", null, $row['payrate']);
	$fesult = $form->addText('first', "First Name", null, $row['first']);
	$fesult = $form->addText('last', "Last Name", null, $row['last']);
	$fesult = $form->addText('phone', "Phone", null, $row['phone']);
	$fesult = $form->addText('email', "E-Mail", null, $row['email']);
	$fesult = $form->addDrop('groupid', "Group", null, db_list("SELECT groupname, groupid FROM {$MYSQL_PREFIX}groupnames ORDER BY groupid DESC;", array('groupid', 'groupname')), False, $row['groupid']);
	$fesult = $form->addCheck('active', "User Active", null, $row['active']);
	$fesult = $form->addCheck('payroll', "User on Payroll", null, $row['payroll']);
	$fesult = $form->addCheck('limithours', "Add / Edit / View only Own Hours", null, $row['limithours']);
	$fesult = $form->addCheck('notify', "Admin Notify on Employee Add of Payroll", null, $row['notify']);
	$fesult = $form->addHidden('id', $id);
	
	return $form->output('Save User');
}

/**
 * Save User Info on Edit
 * 
 * @global resource Database Link
 * @global string MySQL Table Prefix
 * @param integer User ID to edit
 */
function perms_edituser_do($id) {
	GLOBAL $db, $MYSQL_PREFIX;
	
	$sqlstring  = "UPDATE `{$MYSQL_PREFIX}users` SET `password` = '%s', `username` = '%s', `last` = '%s', `first` = '%s',";
	$sqlstring .= " `phone` = '%d', `email` = '%s', `payrate` = '%f', `active` = %d, `payroll` = %d, `limithours` = %d,";
	$sqlstring .= " `notify` = %d WHERE `userid` = %d LIMIT 1";

	$this_active  = ( $_REQUEST['active'] == "y" ) ? "1" : "0";
	$this_payroll = ( $_REQUEST['payroll'] == "y" ) ? "1" : "0";
	$this_lhours  = ( $_REQUEST['limithours'] == "y" ) ? "1" : "0";
	$this_notify  = ( $_REQUEST['notify'] == "y" ) ? "1" : "0";

	$sql = sprintf($sqlstring,
		mysql_real_escape_string($_REQUEST['password']),
		mysql_real_escape_string($_REQUEST['username']),
		mysql_real_escape_string($_REQUEST['last']),
		mysql_real_escape_string($_REQUEST['first']),
		intval($_REQUEST['phone']),
		mysql_real_escape_string($_REQUEST['email']),
		floatval($_REQUEST['payrate']),
		$this_active,
		$this_payroll,
		$this_lhours,
		$this_notify,
		intval($id)
	);

	$sql2  = sprintf("UPDATE `{$MYSQL_PREFIX}usergroups` SET groupid = %d WHERE userid = %d",
		intval($_REQUEST['groupid']),
		intval($id)
	);

	$result = mysql_query($sql, $db);
	if ( $result ) {
		$result = mysql_query($sql2, $db);
		if ( $result ) {
			thrower("User #{$id} Updated");
		} else {
			thrower("Error :: Group Update Failed");
		}
	} else {
		thrower("Error :: User Update Failed");
	}
}

/**
 * Show group related forms
 * 
 * @global resource Database Link
 * @global string MySQL Table Prefix
 * @global string Site Address for links
 * @return string HTML output
 */
function perms_groupform() {
	GLOBAL $db, $MYSQL_PREFIX, $TDTRAC_SITE;
	$form1 = new tdform("{$TDTRAC_SITE}user/groups/", 'form1', 1, 'genform'. 'Add Group');
	$result = $form1->addText('newgroup', "Group Name");
	$html = $form1->output("Add Group");
	
	$form2 = new tdform("{$TDTRAC_SITE}user/groups/", 'form2', $form1->getlasttab(), 'genform2', 'Rename Group');
	$sql = "SELECT `groupname`, `groupid` FROM `{$MYSQL_PREFIX}groupnames` WHERE `groupid` > 1 ORDER BY groupid";
	$result = $form2->addDrop('oldname', "Current Name", null, db_list($sql, array('groupid', 'groupname')), False);
	$result = $form2->addText('newname', "New Name");
	$html = array_merge($html, $form2->output('Rename Group'));
	return $html;
}

/**
 * Form for changing the mail code (tdtracmail enabled installs)
 * 
 * @global resource Database Link
 * @global string MySQL Table Prefix
 * @return string HTML Output
 */
function perms_mailcode() {
	GLOBAL $db, $MYSQL_PREFIX;
	$sql = "SELECT * FROM `tdtracmail` WHERE prefix = '{$MYSQL_PREFIX}'";
	$result = mysql_query($sql, $db);
	$line = mysql_fetch_array($result);
	$form = new tdform("{$TDTRAC_SITE}mail-perms", "form1", 1, 'genform', 'Set TDTracMail Code');
	
	$fes = $form->addText("email", "E-Mail Address", null, $line['email']);
	$fes = $form->addText("code", "Subject Code", null, $line['code']);
	return $form->output('Set Code');
}

/**
 * Logic to save TDTracMail code
 * 
 * @global resource Database Link
 * @global string MySQL Table Prefix
 */
function perms_mailcode_do() {
	GLOBAL $db, $MYSQL_PREFIX;
	$sql = sprintf("UPDATE tdtracmail SET code = '%s', email = '%s' WHERE prefix = '{$MYSQL_PREFIX}'",
		mysql_real_escape_string($_REQUEST['code']),
		mysql_real_escape_string($_REQUEST['email'])
	);
	$result = mysql_query($sql, $db);
	if ( !$result ) { thrower("Code Update Failed:<br />".mysql_error()); }
	else { thrower("Code Updated"); }
}

/**
 * Logic to add a group
 * 
 * @global resource Database Link
 * @global string MySQL Table Prefix
 */
function perms_group_add() {
	GLOBAL $db, $MYSQL_PREFIX;
	$sql = sprintf("INSERT INTO {$MYSQL_PREFIX}groupnames (groupname) VALUES ('%s')",
		mysql_real_escape_string($_REQUEST['newgroup'])
	);
	$request = mysql_query($sql, $db);
	if ( $request ) {
		thrower("Group \"{$_REQUEST['newgroup']}\" Added");
	} else {
		thrower("Group Add :: Operation Failed");
	}
}

/**
 * Logic to rename a group
 * 
 * @global resource Database Link
 * @global string MySQL Table Prefix
 */
function perms_group_ren() {
	GLOBAL $db, $MYSQL_PREFIX;
	$sql = sprintf("UPDATE `{$MYSQL_PREFIX}groupnames` SET groupname = '%s' WHERE groupid = %d",
		mysql_real_escape_string($_REQUEST['newname']),
		intval($_REQUEST['oldname'])
	);
	$request = mysql_query($sql, $db);
	if ( $request ) {
		thrower("Group Renamed to \"{$_REQUEST['newname']}\"");
	} else {
		thrower("Group Update :: Operation Failed");
	}
}

?>
