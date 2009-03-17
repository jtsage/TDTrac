<?php
function display_home($username) {
        GLOBAL $TDTRAC_SITE;
	$html  = "";
	$html .= msg_check();
	$html .= "<h2>Payroll Tracking</h2><p><ul>\n";
	$html .= ( perms_checkperm($username, 'addhours') ) ? "<li><a href=\"{$TDTRAC_SITE}add-hours\">Add Hours Worked</a></li>\n" : "";
	$html .= ( perms_checkperm($username, 'viewhours') ) ? "<li><a href=\"{$TDTRAC_SITE}view-hours\">View Hours Worked</a></li>\n" : "";
	$html .= "</ul></p>\n";
	$html .= "<h2>Budget Tracking</h2><p><ul>\n";
	$html .= ( perms_checkperm($username, 'addbudget') ) ? "<li><a href=\"{$TDTRAC_SITE}add-budget\">Add Budget Expense</a></li>\n" : "";
	$html .= ( perms_checkperm($username, 'viewbudget') ) ? "<li><a href=\"{$TDTRAC_SITE}view-budget\">View Budgets</a></li>\n" : "";
	$html .= "</ul></p>\n";
	$html .= "<h2>Show Information</h2><p><ul>\n";
	$html .= ( perms_checkperm($username, 'addshow') ) ? "<li><a href=\"{$TDTRAC_SITE}add-show\">Add Show</a></li>\n" : "";
	$html .= ( perms_checkperm($username, 'viewshow') ) ? "<li><a href=\"{$TDTRAC_SITE}view-show\">View Shows</a></li>\n" : "";
	$html .= "</ul></p>\n";
	$html .= "<h2>User Managment</h2><p><ul>\n";
	$html .= ( perms_checkperm($username, 'adduser') ) ? "<li><a href=\"{$TDTRAC_SITE}add-user\">Add User</a></li>\n" : "";
	if ( perms_isadmin($username) ) {
		$html .= "<li><a href=\"{$TDTRAC_SITE}view-user\">View Users</a></li>\n";
		$html .= "<li><a href=\"{$TDTRAC_SITE}edit-perms\">Edit Permissions</a></li>\n";
		$html .= "<li><a href=\"{$TDTRAC_SITE}view-perms\">View Permissions</a></li>\n";
		$html .= "<li><a href=\"{$TDTRAC_SITE}groups\">Add / Edit Groups</a></li>\n";
	}
	$html .= "</ul></p>\n";

	return $html;
}


?>
