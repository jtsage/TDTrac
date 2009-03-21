<?php

function perms_getgroups($username) {
	GLOBAL $db, $MYSQL_PREFIX;
	$sql = "SELECT groupname FROM {$MYSQL_PREFIX}groupnames gn, {$MYSQL_PREFIX}usergroups ug, {$MYSQL_PREFIX}users u WHERE username = '{$username}' AND u.userid = ug.userid AND ug.groupid = gn.groupid";
	$result = mysql_query($sql, $db);
	while ( $row = mysql_fetch_array($result) ) {
	   $retty[] = $row['groupname'];
	}
	return $retty;
}

function perms_getidbyname($username) {
	GLOBAL $db, $MYSQL_PREFIX;
	$sql = "SELECT userid FROM {$MYSQL_PREFIX}users WHERE username = '{$username}'";
	$result = mysql_query($sql, $db);
	$row = mysql_fetch_array($result);
	return $row['userid'];
}

function perms_getfnamebyid($userid) {
        GLOBAL $db, $MYSQL_PREFIX;
        $sql = "SELECT first FROM {$MYSQL_PREFIX}users WHERE userid = {$userid}";
        $result = mysql_query($sql, $db);
        $row = mysql_fetch_array($result);
        return $row['first'];
}

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

function perms_isemp($username) { // Not group dependant.  Actually checks 'limithours' property.
	GLOBAL $db, $MYSQL_PREFIX;
	$sql = "SELECT limithours FROM {$MYSQL_PREFIX}users u WHERE username = '{$username}' LIMIT 1";
	$result = mysql_query($sql, $db);
	$line = mysql_fetch_array($result);
	if ( $line['limithours'] == 1 ) { return 1; }
	return 0;
}

function perms_isadmin($username) {
	GLOBAL $db, $MYSQL_PREFIX;
	$sql = "SELECT groupid FROM {$MYSQL_PREFIX}usergroups ug, {$MYSQL_PREFIX}users u WHERE username = '{$username}' AND u.userid = ug.userid AND ug.groupid = 1";
	$result = mysql_query($sql, $db);
	if ( mysql_num_rows($result) > 0 ) { return 1; }
	return 0;
}

function perms_getgroupid($group) {
	GLOBAL $db, $MYSQL_PREFIX;
	$sql = "SELECT groupid FROM {$MYSQL_PREFIX}groupnames WHERE groupname = '{$group}' LIMIT 1;";
	$result = mysql_query($sql, $db);
	$row = mysql_fetch_array($result);
	return $row['groupid'];
}
	
function perms_no() {
	return("<h2>You Do Not Have Permission to Use This Function</h2><p>Please <a href=\"/\">return home</a>.</p>");
}

function perms_editpickform() {
	GLOBAL $db, $MYSQL_PREFIX, $TDTRAC_SITE;
	$sql = "SELECT groupname FROM {$MYSQL_PREFIX}groupnames";
	$result = mysql_query($sql, $db);
	$html  = "<h2>Select Group</h2><div id=\"genform\"><form method=\"post\" action=\"{$TDTRAC_SITE}edit-perms\">\n";
        $html .= "<input type=\"hidden\" name=\"editgroupperm\" value=\"true\" />\n";
	$html .= "<div class=\"frmele\"><select name=\"groupname\">";
	while ( $row = mysql_fetch_array($result) ) {
		$html .= "<option value=\"{$row['groupname']}\">{$row['groupname']}</option>\n"; }
	$html .= "</select></div>\n";
	$html .= "<div class=\"frmele\"><input type=\"submit\" value=\"Select\" /></div></form></div>\n";
	return $html;
}

function perms_editform() {
	GLOBAL $db, $TDTRAC_PERMS, $MYSQL_PREFIX, $TDTRAC_SITE;
	$grpname = $_REQUEST['groupname'];
	$grpid = perms_getgroupid($grpname);
	$html  = "<h2>Set {$grpname} ({$grpid}) Permissions</h2><p style=\"text-align: right\">T&nbsp;&nbsp;&nbsp;/&nbsp;&nbsp;&nbsp;F</p>\n";
	$html .= "<div class=\"genform\"><form method=\"post\" action=\"{$TDTRAC_SITE}edit-perms\">\n";
	$html .= "<input type=\"hidden\" name=\"grpid\" value=\"{$grpid}\" />\n";
	$sql = "SELECT permid, permcan FROM {$MYSQL_PREFIX}permissions pm WHERE groupid = {$grpid}";
	$result = mysql_query($sql, $db);
	while ( $row = mysql_fetch_array($result) ) {
		$pname = $row['permid']; $pvalue = $row['permcan'];
		$dbperm[$pname] = $pvalue;
	}
	foreach ( $TDTRAC_PERMS as $perm ) {
		$html .= "<div class=\"frmele\">{$perm} \n";
		if ( isset($dbperm[$perm]) && $dbperm[$perm] == "1" ) {
			$html .= "<input type=\"radio\" name=\"{$perm}\" value=\"1\" checked=\"checked\" /><input type=\"radio\" name=\"{$perm}\" value=\"0\" /></div>";
		} else {
			$html .= "<input type=\"radio\" name=\"{$perm}\" value=\"1\" /><input type=\"radio\" name=\"{$perm}\" value=\"0\" checked=\"checked\"/></div>";
		}
	}	
	$html .= "<div class=\"frmele\"><input type=\"submit\" value=\"commit\" /></div></form></div>\n";
	return $html;
}

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

function perms_adduser_form() {
	GLOBAL $db, $MYSQL_PREFIX, $TDTRAC_SITE;
	$html  = "<h2>Add User</h2>\n";
	$html .= "<div id=\"genform\"><form method=\"post\" action=\"{$TDTRAC_SITE}add-user\">\n";
	$html .= "<div class=\"frmele\">User Name: <input type=\"text\" name=\"username\" size=\"35\" /></div>\n";
	$html .= "<div class=\"frmele\">Password: <input type=\"text\" name=\"password\" size=\"35\" /></div>\n";
	$html .= "<div class=\"frmele\">First Name: <input type=\"text\" name=\"first\" size=\"35\" /></div>\n";
	$html .= "<div class=\"frmele\">Last Name: <input type=\"text\" name=\"last\" size=\"35\" /></div>\n";
	$html .= "<div class=\"frmele\">Phone: <input type=\"text\" name=\"phone\" size=\"35\" /></div>\n";
	$html .= "<div class=\"frmele\">E-Mail: <input type=\"text\" name=\"email\" size=\"35\" /></div>\n";
	$html .= "<div class=\"frmele\">Group: <select style=\"width: 25em\" name=\"groupid\" />\n";
        $sql = "SELECT groupname, groupid FROM {$MYSQL_PREFIX}groupnames ORDER BY groupid DESC;";
        $result = mysql_query($sql, $db);
        while ( $row = mysql_fetch_array($result) ) {
                $html .= "<option value=\"{$row['groupid']}\">{$row['groupname']}</option>\n";
        }
        $html .= "</select></div>";
	$html .= "<div class=\"frmele\"><input type=\"submit\" value=\"Add User\" /></div></form></div>\n";
	return $html;
}

function perms_adduser_do() {
	GLOBAL $db, $MYSQL_PREFIX, $TDTRAC_PAYRATE;
	$sql = "INSERT INTO {$MYSQL_PREFIX}users ( username, first, last, password, phone, email, payrate ) VALUES ( '{$_REQUEST['username']}' , '{$_REQUEST['first']}' , '{$_REQUEST['last']}' , '{$_REQUEST['password']}' , '{$_REQUEST['phone']}' , '{$_REQUEST['email']}' , '{$TDTRAC_PAYRATE}')";
	$result = mysql_query($sql, $db);
	$userid = mysql_insert_id($db);
	$sql2 = "INSERT INTO {$MYSQL_PREFIX}usergroups ( userid, groupid ) VALUES ( '{$userid}' , '{$_REQUEST['groupid']}' )";
	$result2 = mysql_query($sql2, $db);
	thrower("User Added");
}

function perms_changepass_form() {
        GLOBAL $TDTRAC_SITE;
	$html  = "<h2>Change Password</h2>\n";
	$html .= "<div id=\"genform\"><form method=\"post\" action=\"{$TDTRAC_SITE}change-pass\">\n";
	$html .= "<div class=\"frmele\">New Password: <input name=\"newpass1\" type=\"password\" size=\"35\" value=\"\" /></div>\n";
	$html .= "<div class=\"frmele\">Verify New Password: <input name=\"newpass2\" type=\"password\" size=\"35\" value=\"\" /></div>\n";
	$html .= "<div class=\"frmele\"><input type=\"submit\" value=\"Change Password\" /></div></form></div>\n";
	return $html;
}

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

function perms_edituser_form($id) {
	GLOBAL $db, $MYSQL_PREFIX, $TDTRAC_SITE;
	$sql = "SELECT u.*, groupid FROM {$MYSQL_PREFIX}users u, {$MYSQL_PREFIX}usergroups ug WHERE u.userid = ug.userid AND u.userid = {$id} LIMIT 1";
	$result = mysql_query($sql, $db);
	$row = mysql_fetch_array($result);
	$html  = "<h2>Edit User</h2>\n";
	$html .= "<div id=\"genform\"><form method=\"post\" action=\"{$TDTRAC_SITE}edit-user\">\n";
	$html .= "<input type=\"hidden\" name=\"id\" value=\"{$id}\" />\n";
	$html .= "<div class=\"frmele\">User Name: <input type=\"text\" name=\"username\" size=\"35\" value=\"{$row['username']}\" /></div>\n";
	$html .= "<div class=\"frmele\">Password: <input type=\"text\" name=\"password\" size=\"35\" value=\"{$row['password']}\" /></div>\n";
	$html .= "<div class=\"frmele\">Pay Rate: <input type=\"text\" name=\"payrate\" size=\"35\" value=\"{$row['payrate']}\" /></div>\n";
	$html .= "<div class=\"frmele\">First Name: <input type=\"text\" name=\"first\" size=\"35\" value=\"{$row['first']}\" /></div>\n";
	$html .= "<div class=\"frmele\">Last Name: <input type=\"text\" name=\"last\" size=\"35\" value=\"{$row['last']}\" /></div>\n";
	$html .= "<div class=\"frmele\">Phone: <input type=\"text\" name=\"phone\" size=\"35\" value=\"{$row['phone']}\" /></div>\n";
	$html .= "<div class=\"frmele\">E-Mail: <input type=\"text\" name=\"email\" size=\"35\" value=\"{$row['email']}\" /></div>\n";

	$html .= "<div class=\"frmele\">Group: <select style=\"width: 25em\" name=\"groupid\" />\n";
	$sql = "SELECT groupname, groupid FROM {$MYSQL_PREFIX}groupnames ORDER BY groupid DESC;";
	$result2 = mysql_query($sql, $db);
	while ( $row2 = mysql_fetch_array($result2) ) {
		$html .= "<option value=\"{$row2['groupid']}\"";
		$html .= (( $row['groupid'] == $row2['groupid'] ) ? " selected=\"selected\" " : "");
		$html .= ">{$row2['groupname']}</option>\n";
	}
	$html .= "</select></div>";
	$html .= "<div class=\"frmele\">User Active: <input type=\"checkbox\" name=\"active\" value=\"y\"".(($row['active'])?" checked=\"checked\" ":"")."/></div>\n";
	$html .= "<div class=\"frmele\">User On Payroll: <input type=\"checkbox\" name=\"payroll\" value=\"y\"".(($row['payroll'])?" checked=\"checked\" ":"")."/></div>\n";
        $html .= "<div class=\"frmele\">Add / Edit / View only Own hours: <input type=\"checkbox\" name=\"limithours\" value=\"y\"".(($row['limithours'])?" checked=\"checked\" ":"")."/></div>\n";
	$html .= "<div class=\"frmele\">Admin Notify on Employee Add of Payroll: <input type=\"checkbox\" name=\"notify\" value=\"y\"".(($row['notify'])?" checked=\"checked\" ":"")."/></div>\n";
	$html .= "<div class=\"frmele\"><input type=\"submit\" value=\"Commit\" /></div></form></div>\n";
	return $html;
}

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

function perms_groupform() {
	GLOBAL $db, $MYSQL_PREFIX, $TDTRAC_SITE;
	$html  = "<h2>Add A Group</h2>\n";
	$html .= "<div id=\"genform\"><form method=\"POST\" action=\"{$TDTRAC_SITE}groups\">\n";
	$html .= "<div class=\"frmele\">Group Name: <input type=\"text\" name=\"newgroup\" size=\"35\" /></div>\n";
	$html .= "<div class=\"frmele\"><input type=\"submit\" value=\"Add Group\" /></div></form></div>\n";
	
	$sql = "SELECT groupname, groupid FROM {$MYSQL_PREFIX}groupnames WHERE groupid > 1 ORDER BY groupid";
	$result = mysql_query($sql, $db);
	$html .= "<h2>Rename Group</h2>\n";
	$html .= "<div id=\"genform\"><form method=\"POST\" action=\"{$TDTRAC_SITE}groups\">\n";
	$html .= "<div class=\"frmele\">Current Name: <select name=\"oldname\" style=\"width: 25em\">\n";
	while ( $row = mysql_fetch_array($result) ) {
		$html .= "<option value=\"{$row['groupid']}\">{$row['groupname']}</option>\n";
	}
	$html .= "</select></div>\n";
	$html .= "<div class=\"frmele\">New Name: <input type=\"text\" size=\"35\" name=\"newname\" /></div>\n";
	$html .= "<div class=\"frmele\"><input type=\"submit\" value=\"Rename Group\" /></div></form></div>\n";
	return $html;
}

function perms_group_add() {
	GLOBAL $db, $MYSQL_PREFIX;
	$sql = "INSERT INTO {$MYSQL_PREFIX}groupnames (groupname) VALUES ('{$_REQUEST['newgroup']}')";
	$request = mysql_query($sql, $db);
	thrower("Group \"{$_REQUEST['newgroup']}\" Added");
}

function perms_group_ren() {
	GLOBAL $db, $MYSQL_PREFIX;
	$sql = "UPDATE {$MYSQL_PREFIX}groupnames SET groupname = '{$_REQUEST['newname']}' WHERE groupid = {$_REQUEST['oldname']}";
	$request = mysql_query($sql, $db);
	thrower("Group Renamed to \"{$_REQUEST['newname']}\"");
}

?>
