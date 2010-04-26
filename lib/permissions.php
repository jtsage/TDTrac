<?php
/**
 * TDTrac Access Control Functions
 * 
 * Contains all access control framework
 * @package tdtrac
 * @version 1.3.0
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
	$sql = "SELECT groupname FROM {$MYSQL_PREFIX}groupnames gn, {$MYSQL_PREFIX}usergroups ug, {$MYSQL_PREFIX}users u WHERE username = '{$username}' AND u.userid = ug.userid AND ug.groupid = gn.groupid";
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
	$sql = "SELECT userid FROM {$MYSQL_PREFIX}users WHERE username = '{$username}'";
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
        $sql = "SELECT first FROM {$MYSQL_PREFIX}users WHERE userid = {$userid}";
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
	$sql = "SELECT permcan FROM {$MYSQL_PREFIX}permissions pm, {$MYSQL_PREFIX}usergroups ug, {$MYSQL_PREFIX}users u WHERE username = '{$username}' AND u.userid = ug.userid AND ug.groupid = pm.groupid AND pm.permid = '{$permission}'";
        $result = mysql_query($sql, $db);
	if ( mysql_num_rows($result) < 1 ) { return 0; }
	while ( $row = mysql_fetch_array($result)) {
		if ( $row['permcan'] ) { return 1; }
	}
	return 0;
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
	$sql = "SELECT limithours FROM {$MYSQL_PREFIX}users u WHERE username = '{$username}' LIMIT 1";
	$result = mysql_query($sql, $db);
	$line = mysql_fetch_array($result);
	if ( $line['limithours'] == 1 ) { return 1; }
	return 0;
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
	$sql = "SELECT groupid FROM {$MYSQL_PREFIX}usergroups ug, {$MYSQL_PREFIX}users u WHERE username = '{$username}' AND u.userid = ug.userid AND ug.groupid = 1";
	$result = mysql_query($sql, $db);
	if ( mysql_num_rows($result) > 0 ) { return 1; }
	return 0;
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
	$sql = "SELECT groupid FROM {$MYSQL_PREFIX}groupnames WHERE groupname = '{$group}' LIMIT 1;";
	$result = mysql_query($sql, $db);
	$row = mysql_fetch_array($result);
	return $row['groupid'];
}

/**
 * Return a notice that user has no permissions for action
 * 
 * @return string HTML output
 */
function perms_no() {
	return("<h2>You Do Not Have Permission to Use This Function</h2><p>Please <a href=\"/\">return home</a>.</p>");
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
	$sql = "SELECT groupname FROM {$MYSQL_PREFIX}groupnames";
	$html  = "<h2>Select Group</h2>";
	$form = new tdform("{$TDTRAC_SITE}edit-perms");
	$result = $form->addHidden("editgroupperm", "true");
	$result = $form->addDrop('groupname', "Group to Edit", null, db_list($sql, 'groupname'), False);
	$html .= $form->output('Select');
	return $html;
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
	$html  = "<h2>Set {$grpname} ({$grpid}) Permissions</h2><p style=\"text-align: right\">T&nbsp;&nbsp;&nbsp;/&nbsp;&nbsp;&nbsp;F</p>\n";
	$form = new tdform("{$TDTRAC_SITE}edit-perms");

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
	$html .= $form->output('Commit');
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
	$sql = "DELETE FROM {$MYSQL_PREFIX}permissions WHERE groupid = {$grpid}";
	$result = mysql_query($sql, $db);
	foreach ( $TDTRAC_PERMS as $perm ) {
		$sql = "INSERT INTO {$MYSQL_PREFIX}permissions (groupid, permid, permcan) VALUES ({$grpid}, '{$perm}', {$_REQUEST[$perm]})";
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
	$sql = "SELECT groupname, permid FROM {$MYSQL_PREFIX}groupnames gn, {$MYSQL_PREFIX}permissions pm WHERE pm.groupid = gn.groupid AND pm.permcan = 1 ORDER BY groupname";
	$result = mysql_query($sql, $db);
	while ( $row = mysql_fetch_array($result) ) {
		$disperm[$row['groupname']][] = $row['permid'];
	}
	$html = "";
	foreach ( $disperm as $name => $value ) {
		$html .= "<h2>Permissions For {$name}</h2><p>  ";
		$sql = "SELECT u.username FROM {$MYSQL_PREFIX}users u, {$MYSQL_PREFIX}groupnames gn, {$MYSQL_PREFIX}usergroups ug WHERE gn.groupname = '{$name}' AND gn.groupid = ug.groupid AND ug.userid = u.userid ORDER BY username ASC";
		$result = mysql_query($sql, $db);
		while ( $row = mysql_fetch_array($result) ) {
			$html .= "{$row['username']}, ";
		}
		$html = substr($html, 0, -2);
		$html .= "</p>\n<ul>\n"; 
		foreach ( $value as $pval ) {
			$html .= "<li>{$pval}</li>\n";
		}
		$html .= "</ul>\n";
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
	$html  = "<h2>Add User</h2>\n";
	$form = new tdform("{$TDTRAC_SITE}add-user");
	
	$result = $form->addText('username', "User Name");
	$result = $form->addText('password', "Password");
	$result = $form->addText('first', "First Name");
	$result = $form->addText('last', "Last Name");
	$result = $form->addText('phone', "Phone");
	$result = $form->addText('email', "E-Mail");
	$result = $form->addDrop('groupid', "Group", null, db_list("SELECT groupname, groupid FROM {$MYSQL_PREFIX}groupnames ORDER BY groupid DESC;", array('groupid', 'groupname')), False);
	
	$html .= $form->output('Add User');
	return $html;
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
	$sql = "INSERT INTO {$MYSQL_PREFIX}users ( username, first, last, password, phone, email, payrate ) VALUES ( '{$_REQUEST['username']}' , '{$_REQUEST['first']}' , '{$_REQUEST['last']}' , '{$_REQUEST['password']}' , '{$_REQUEST['phone']}' , '{$_REQUEST['email']}' , '{$TDTRAC_PAYRATE}')";
	$result = mysql_query($sql, $db);
	$userid = mysql_insert_id($db);
	$sql2 = "INSERT INTO {$MYSQL_PREFIX}usergroups ( userid, groupid ) VALUES ( '{$userid}' , '{$_REQUEST['groupid']}' )";
	$result2 = mysql_query($sql2, $db);
	thrower("User Added");
}

/**
 * Show change password form
 * 
 * @global string Site Address for links
 * @return string HTML output
 */
function perms_changepass_form() {
	GLOBAL $TDTRAC_SITE;
	$html  = "<h2>Change Password</h2>\n";
	$form = new tdform("{$TDTRAC_SITE}change-pass");
	$result = $form->addPass('newpass1', "New Password");
	$result = $form->addPass('newpass2', "Verify New Password");
	$html .= $form->output('Change Password');
	return $html;
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
		$sql = "UPDATE {$MYSQL_PREFIX}users SET chpass = 0 , password = '{$_REQUEST['newpass1']}' WHERE username = '{$user_name}' LIMIT 1";
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
	$sql = "SELECT *, DATE_FORMAT(lastlogin, '%b %D %h:%i %p') AS lastlog FROM {$MYSQL_PREFIX}users ORDER BY last ASC, first ASC";
	$result = mysql_query($sql, $db); $html = "";
	while ( $row = mysql_fetch_array($result) ) {
		$html .= "<h2>User: {$row['first']} {$row['last']}</h2><p><ul>\n";
		$html .= "<div style=\"float: right\">[<a href=\"{$TDTRAC_SITE}edit-user&id={$row['userid']}\">Edit</a>]</div>\n";
		$html .= "<li>Internal UserID: <strong>{$row['userid']}</strong> (Last Login: {$row['lastlog']})<ul><li> (Active: <input type=\"checkbox\" disabled=\"disabled\"".(($row['active'])?" checked=\"checked\" ":"").">)</li>\n";
		$html .= "<li>(On Payroll: <input type=\"checkbox\" disabled=\"disabled\"".(($row['payroll'])?" checked=\"checked\" ":"").">)</li>\n";
		$html .= "<li>(Add / View / Edit only Own Hours: <input type=\"checkbox\" disabled=\"disabled\"".(($row['limithours'])?" checked=\"checked\" ":"").">)</li>\n";
		$html .= "<li>(Notify of Employee add Payroll: <input type=\"checkbox\" disabled=\"disabled\"".(($row['notify'])?" checked=\"checked\" ":"").">)</li></ul></li>\n";
		$html .= "<li>User Name: <strong>{$row['username']}</strong></li>\n";
		$html .= "<li>Group : <strong>\n";
		$groups = perms_getgroups($row['username']);
		foreach ( $groups as $group ) { $html .= "{$group} "; }
		$html .= "</strong></li>\n";
		if ( !is_null($row['phone']) && $row['phone'] <> "" && $row['phone'] <> 0 ) {
			$html .= "<li>Phone Number: <strong>". format_phone($row['phone']) . "</strong></li>\n";
		}
		if ( !is_null($row['email']) && $row['email'] <> "" ) {
			$html .= "<li>E-Mail Address: <a href=\"mailto:{$row['email']}\"><strong>{$row['email']}</strong></a></li>\n";
		}
		$html .= "<li>Pay Rate: \$" . number_format($row['payrate'], 2) . "</li>";
		$html .= "</ul></p>\n";
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
	$sql = "SELECT u.*, groupid FROM {$MYSQL_PREFIX}users u, {$MYSQL_PREFIX}usergroups ug WHERE u.userid = ug.userid AND u.userid = {$id} LIMIT 1";
	$result = mysql_query($sql, $db);
	$row = mysql_fetch_array($result);
	$html  = "<h2>Edit User</h2>\n";
	$form = new tdform("{$TDTRAC_SITE}edit-user");
	
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
	
	$html .= $form->output('Save User');
	return $html;
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
	$sql   = "UPDATE {$MYSQL_PREFIX}users SET password = '{$_REQUEST['password']}' , username = '{$_REQUEST['username']}' , last = '{$_REQUEST['last']}' , first = '{$_REQUEST['first']}' , phone = '{$_REQUEST['phone']}' , email = '{$_REQUEST['email']}' , payrate = '{$_REQUEST['payrate']}' , active = '";
	$sql  .= ( $_REQUEST['active'] == "y" ) ? "1" : "0";
        $sql  .= "', payroll = '";
	$sql  .= ( $_REQUEST['payroll'] == "y" ) ? "1" : "0";
	$sql  .= "', limithours = '";
	$sql  .= ( $_REQUEST['limithours'] == "y" ) ? "1" : "0";
	$sql  .= "', notify = '";
	$sql  .= ( $_REQUEST['notify'] == "y" ) ? "1" : "0";
	$sql  .= "' WHERE userid = '{$id}' LIMIT 1";
	$sql2  = "UPDATE {$MYSQL_PREFIX}usergroups SET groupid = {$_REQUEST['groupid']} WHERE userid = '{$id}'";
	$result = mysql_query($sql, $db);
	$result = mysql_query($sql2, $db);
	thrower("User #{$id} Updated");
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
	$html  = "<h2>Add A Group</h2>\n";
	$form1 = new tdform("{$TDTRAC_SITE}groups", 'form1');
	$result = $form1->addText('newgroup', "Group Name");
	$html .= $form1->output("Add Group");
	
	$html .= "<h2>Rename Group</h2>\n";
	$form2 = new tdform("{$TDTRAC_SITE}groups", 'form2', $form1->getlasttab());
	$sql = "SELECT groupname, groupid FROM {$MYSQL_PREFIX}groupnames WHERE groupid > 1 ORDER BY groupid";
	$result = $form2->addDrop('oldname', "Current Name", null, db_list($sql, array('groupid', 'groupname')), False);
	$result = $form2->addText('newname', "New Name");
	$html .= $form2->output('Rename Group');
	return $html;
}

/**
 * Logic to add a group
 * 
 * @global resource Database Link
 * @global string MySQL Table Prefix
 */
function perms_group_add() {
	GLOBAL $db, $MYSQL_PREFIX;
	$sql = "INSERT INTO {$MYSQL_PREFIX}groupnames (groupname) VALUES ('{$_REQUEST['newgroup']}')";
	$request = mysql_query($sql, $db);
	thrower("Group \"{$_REQUEST['newgroup']}\" Added");
}

/**
 * Logic to rename a group
 * 
 * @global resource Database Link
 * @global string MySQL Table Prefix
 */
function perms_group_ren() {
	GLOBAL $db, $MYSQL_PREFIX;
	$sql = "UPDATE {$MYSQL_PREFIX}groupnames SET groupname = '{$_REQUEST['newname']}' WHERE groupid = {$_REQUEST['oldname']}";
	$request = mysql_query($sql, $db);
	thrower("Group Renamed to \"{$_REQUEST['newname']}\"");
}

?>
