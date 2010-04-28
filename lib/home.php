<?php
/**
 * TDTrac Home Page View
 * 
 * Contains site home page, post-login.
 * @package tdtrac
 * @version 1.3.0
 */

/**
 * Display homepage
 * 
 * @param string User Name
 * @global string Site Address for links
 */
function display_home($username, $type=0) {
	GLOBAL $TDTRAC_SITE;
	$html  = "";
	$html .= msg_check();
	$html .= rcpt_check();
	if ( $type == 0 || $type == 1 ) {
		$html .= "<h3>Payroll Tracking</h3><ul class=\"linklist\">\n";
		$html .= ( perms_checkperm($username, 'addhours') ) ? "<li><a href=\"{$TDTRAC_SITE}add-hours\">Add Hours Worked</a></li>\n" : "";
		$html .= ( perms_checkperm($username, 'viewhours') ) ? "<li><a href=\"{$TDTRAC_SITE}view-hours\">View Hours Worked</a></li>\n" : "";
		$html .= ( perms_isadmin($username) ) ? "<li><a href=\"{$TDTRAC_SITE}view-hours-unpaid\">View Hours Worked (unpaid)</a></li>\n" : "";
		$html .= ( perms_isadmin($username) ) ? "<li><a href=\"{$TDTRAC_SITE}remind-hours\">Send 'please submit hours' Reminder to Employees</a></li>\n" : "";
		$html .= "</ul>\n";
	}
	if ( $type == 0 || $type == 2 ) {
		$html .= "<h3>Budget Tracking</h3><ul class=\"linklist\">\n";
		$html .= ( perms_checkperm($username, 'addbudget') ) ? "<li><a href=\"{$TDTRAC_SITE}add-budget\">Add Budget Expense</a></li>\n" : "";
		$html .= ( perms_checkperm($username, 'viewbudget') ) ? "<li><a href=\"{$TDTRAC_SITE}view-budget\">View Budgets</a></li>\n" : "";
		$html .= ( perms_checkperm($username, 'viewbudget') ) ? "<li><a href=\"{$TDTRAC_SITE}view-budget-special&amp;stype=1\">View Budgets (payment pending items only, all shows)</a></li>\n" : "";
		$html .= ( perms_checkperm($username, 'viewbudget') ) ? "<li><a href=\"{$TDTRAC_SITE}view-budget-special&amp;stype=2\">View Budgets (reimbursment items only, all shows)</a></li>\n" : "";
		$html .= ( perms_checkperm($username, 'viewbudget') ) ? "<li><a href=\"{$TDTRAC_SITE}view-budget-special&amp;stype=3\">View Budgets (reimbursment recieved items only, all shows)</a></li>\n" : "";
		$html .= ( perms_checkperm($username, 'viewbudget') ) ? "<li><a href=\"{$TDTRAC_SITE}view-budget-special&amp;stype=4\">View Budgets (reimbursment not recieved items only, all shows)</a></li>\n" : "";
		$html .= "</ul>\n";
	}
	if ( $type == 0 || $type == 3 ) {
		$html .= "<h3>Show Information</h3><ul class=\"linklist\">\n";
		$html .= ( perms_checkperm($username, 'addshow') ) ? "<li><a href=\"{$TDTRAC_SITE}add-show\">Add Show</a></li>\n" : "";
		$html .= ( perms_checkperm($username, 'viewshow') ) ? "<li><a href=\"{$TDTRAC_SITE}view-show\">View Shows</a></li>\n" : "";
		$html .= "</ul>\n";
	}
	if ( $type == 0 || $type == 4 ) {
		$html .= "<h3>User Managment</h3><ul class=\"linklist\">\n";
		$html .= ( perms_checkperm($username, 'adduser') ) ? "<li><a href=\"{$TDTRAC_SITE}add-user\">Add User</a></li>\n" : "";
		if ( perms_isadmin($username) ) {
			$html .= "<li><a href=\"{$TDTRAC_SITE}view-user\">View Users</a></li>\n";
			$html .= "<li><a href=\"{$TDTRAC_SITE}edit-perms\">Edit Permissions</a></li>\n";
			$html .= "<li><a href=\"{$TDTRAC_SITE}view-perms\">View Permissions</a></li>\n";
			$html .= "<li><a href=\"{$TDTRAC_SITE}mail-perms\">Set TDTracMail Subject Code</a></li>\n";
			$html .= "<li><a href=\"{$TDTRAC_SITE}groups\">Add / Edit Groups</a></li>\n";
		}
		$html .= "</ul>\n";
	}

	return $html;
}


?>
