<?php
/**
 * TDTrac Home Page View
 * 
 * Contains site home page, post-login.
 * @package tdtrac
 * @version 1.3.1
 * @author J.T.Sage <jtsage@gmail.com>
 */

/**
 * Display homepage
 * 
 * @param string User Name
 * @global string Site Address for links
 */
function display_home($username, $type=0) {
	GLOBAL $TDTRAC_SITE;
	if( $type == 0 ) {
		$html[] = msg_check();
		$html[] = rcpt_check();
		$html[] = todo_check();
		$html[] = "<br /><br /><div style=\"float: left; width: 49%\">"; }
	if ( $type == 0 || $type == 1 ) {
		$html[] = "<h3>Payroll Tracking</h3><ul class=\"linklist\">";
		$html[] = ( perms_checkperm($username, 'addhours') ) 	? "  <li><a href=\"{$TDTRAC_SITE}hours/add/\">Add Hours Worked</a></li>" : "";
		$html[] = ( perms_checkperm($username, 'viewhours') ) 	? "  <li><a href=\"{$TDTRAC_SITE}hours/view/\">View Hours Worked</a></li>" : "";
		$html[] = ( perms_isadmin($username) ) 			? "  <li><a href=\"{$TDTRAC_SITE}hours/view/unpaid/\">View Hours Worked (unpaid)</a></li>" : "";
		$html[] = ( perms_isadmin($username) ) 			? "  <li><a href=\"{$TDTRAC_SITE}hours/remind/\">Send 'please submit hours' Reminder to Employees</a></li>" : "";
		$html[] = "</ul>";
	}
	if ( $type == 0 || $type == 2 ) {
		$html[] = "<h3>Budget Tracking</h3><ul class=\"linklist\">";
		$html[] = ( perms_checkperm($username, 'addbudget') ) ? "<li><a href=\"{$TDTRAC_SITE}budget/add/\">Add Budget Expense</a></li>" : "";
		$html[] = ( perms_checkperm($username, 'viewbudget') ) ? "<li><a href=\"{$TDTRAC_SITE}budget/view/\">View Budgets</a></li>" : "";
		$html[] = ( perms_checkperm($username, 'viewbudget') ) ? "<li><a href=\"{$TDTRAC_SITE}budget/view/1/\">View Budgets (payment pending items only, all shows)</a></li>" : "";
		$html[] = ( perms_checkperm($username, 'viewbudget') ) ? "<li><a href=\"{$TDTRAC_SITE}budget/view/2/\">View Budgets (reimbursment items only, all shows)</a></li>" : "";
		$html[] = ( perms_checkperm($username, 'viewbudget') ) ? "<li><a href=\"{$TDTRAC_SITE}budget/view/3\">View Budgets (reimbursment recieved items only, all shows)</a></li>" : "";
		$html[] = ( perms_checkperm($username, 'viewbudget') ) ? "<li><a href=\"{$TDTRAC_SITE}budget/view/4/\">View Budgets (reimbursment not recieved items only, all shows)</a></li>" : "";
		$html[] = "</ul>\n";
	}
	if ( $type == 0 ) { $html[] = "<br /><br /><br /><br /><br /><br /></div>"; }
	if ( $type == 0 || $type == 3 ) {
		$html[] = "<h3>Show Information</h3><ul class=\"linklist\">";
		$html[] = ( perms_checkperm($username, 'addshow') ) ? "<li><a href=\"{$TDTRAC_SITE}shows/add/\">Add Show</a></li>" : "";
		$html[] = ( perms_checkperm($username, 'viewshow') ) ? "<li><a href=\"{$TDTRAC_SITE}shows/view/\">View Shows</a></li>" : "";
		$html[] = "</ul>";
	}
	if ( $type == 0 || $type == 5 ) {
		$html[] = "<h3>ToDo Lists</h3><ul class=\"linklist\">";
		$html[] = ( perms_checkperm($username, 'addbudget') ) ? "<li><a href=\"{$TDTRAC_SITE}todo/add/\">Add ToDo Item</a></li>" : "";
		$html[] = ( perms_checkperm($username, 'viewbudget') ) ? "<li><a href=\"{$TDTRAC_SITE}todo/view/\">View ToDo Items</a></li>" : "";
		$html[] = "<li><a href=\"{$TDTRAC_SITE}/todo/view/own/\">View Personal ToDo Items</a></li>";
		$html[] = "</ul>";
	}
	if ( $type == 0 || $type == 4 ) {
		$html[] = "<h3>Administrative Tasks</h3><ul class=\"linklist\">";
		$html[] = ( perms_checkperm($username, 'adduser') ) ? "<li><a href=\"{$TDTRAC_SITE}user/add/\">Add User</a></li>" : "";
		if ( perms_isadmin($username) ) {
			$html[] = "<li><a href=\"{$TDTRAC_SITE}user/view/\">View Users</a></li>";
			$html[] = "<li><a href=\"{$TDTRAC_SITE}user/perms/edit/\">Edit Permissions</a></li>";
			$html[] = "<li><a href=\"{$TDTRAC_SITE}user/perms/\">View Permissions</a></li>\n";
			$html[] = "<li><a href=\"{$TDTRAC_SITE}user/mail/\">Set TDTracMail Subject Code</a></li>";
			$html[] = "<li><a href=\"{$TDTRAC_SITE}user/groups/\">Add / Edit Groups</a></li>";
		}
		$html[] = "</ul>\n";
	}
	return $html;
}


?>
