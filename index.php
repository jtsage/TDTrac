<?php
/**
 * TDTrac Main Program
 * 
 * Contains main program logic.
 * @package tdtrac
 * @version 1.3.1
 * @author J.T.Sage <jtsage@gmail.com>
 */
ob_start(); session_start(); 

## PROGRAM DETAILS. DO NOT EDIT UNLESS YOU KNOW WHAT YOU ARE DOING
$TDTRAC_VERSION = "2.0.0";
$TDTRAC_DBVER = "1.3.1";
$TDTRAC_PERMS = array("addshow", "editshow", "viewshow", "addbudget", "editbudget", "viewbudget", "addhours", "edithours", "viewhours", "adduser", "edittodo", "viewtodo", "addtodo");
$SITE_SCRIPT = array('');

require_once("config.php");
require_once("lib/functions-load.php");
if ( !file_exists(".htaccess") ) { $TDTRAC_SITE .= "index.php?action="; }

$user = new tdtrac_user();

$rawaction = preg_split("/\//", $_REQUEST['action']);

if ( !isset($rawaction[0]) || $rawaction[0] == "" ) {
	$action['module'] = 'index';
} else { 
	$action['module'] = $rawaction[0];
}
if ( !isset($rawaction[1]) || preg_match("/:/", $rawaction[1]) || $rawaction[1] == "" ) {
	$action['action'] = 'index';
} else {
	$action['action'] = $rawaction[1];
}
foreach ( $rawaction as $maybevar ) {
	if ( preg_match("/:/", $maybevar) ) {
		$goodvar = preg_split("/:/", $maybevar);
		$action[$goodvar[0]] = $goodvar[1];
	}
}

if ( !$user->loggedin ) {
	switch( $action['action'] ) {
		case "login":
			$user->login();
			break;
		case "forgot":
			if ( $_SERVER['REQUEST_METHOD'] == "POST" ) {
				email_pwsend();
			} else {
				makePage($user->password_form(), 'Forgotten Password');
			} break;
		default:
			makePage($user->login_form(), 'Please Login');
			break;
	}
} else {
	switch ($action['module']) {
		case "user":
			if ( $action['action'] == "logout" ) { $user->logout(); }
			thrower(false, ''); 
		case "todo":
			$todo = new tdtrac_todo($user, $action);
			$todo->output();
			break;
		case "shows":
			$shows = new tdtrac_shows($user, $action);
			$shows->output();
			break;
		default: 
			//$html[] = msg_check();
			//$html[] = rcpt_check();
			$html[] = todo_check();
			$html[] = "<br /><br /><div style=\"float: left; min-height: 400px; width: 48%\">";
			// Budget & Payroll
			$html[] = "<br /><br /><br /><br /><br /><br /></div><div style=\"width: 48%; float: right;\">";
			// Shows, Todo & Admin
			$show = new tdtrac_shows($user, $action);
			$todo = new tdtrac_todo($user, $action);
			
			$html = array_merge($html, $show->index(), $todo->index());
			
			$html[] = "</div>";
			makePage($html, 'TD Tracking Made Easy');
			break;
	}
	
	//echo "Logged In!";
	//print_r($action);
}

			
/*
} else {
	$user_name = $login[1];
	switch($action[0]) {
		case "search":
			if ( perms_checkperm($user_name, 'viewbudget') ) {
				if ( $_SERVER['REQUEST_METHOD'] == "POST" ) {
					if ( isset($_REQUEST['keywords']) && $_REQUEST['keywords'] <> "" ) { makePage(budget_search($_REQUEST['keywords']), 'Search Results'); }
					else { makePage(display_home($user_name)); }
				} else { makePage(display_home($user_name)); }
			} else { makePage(perms_no(), 'Access Denied'); }
			break;
		case "rcpt":
			switch ($action[1]) {
				case "delete":
					if ( perms_checkperm($user_name, 'addbudget') ) { rcpt_nuke(); 
					} else { makePage(perms_no(), 'Access Denied'); }
					break;
				default:
					if ( perms_checkperm($user_name, 'addbudget') ) {
						if ($_SERVER['REQUEST_METHOD'] == "POST") { rcpt_do(); }
						else { makePage(rcpt_view(), 'Reciepts'); }
					} else { makePage(perms_no(), 'Access Denied'); }
				break;
			} break;
		case "budget":
			switch ($action[1]) {
				case "add":
					if ( perms_checkperm($user_name, 'addbudget') ) {
						if ($_SERVER['REQUEST_METHOD'] == "POST") { budget_add(); }
						else { makePage(budget_addform(), 'Add Budget Item'); }
					} else { makePage(perms_no(), 'Access Denied'); }
					break;
				case "view":
					if ( perms_checkperm($user_name, 'viewbudget') ) {
						if ( is_numeric($action[2]) && $action[2] > 0 && $action[2] < 5 ) {
							makePage(budget_view_special($action[2]), 'View Budget');
						} else {
							if ( $_SERVER['REQUEST_METHOD'] == "POST" ) { makePage(budget_view(intval($_REQUEST['showid'])), 'View Budget'); }
							else { makePage(budget_viewselect(), 'Select Budget'); }
						}
					} else { makePage(perms_no(), 'Access Denied'); }
					break;
				case "edit":
					if ( perms_checkperm($user_name, 'editbudget') && is_numeric($action[2]) ) {
						if ($_SERVER['REQUEST_METHOD'] == "POST") { budget_edit_do(intval($action[2])); }
						else { 
							if ( is_numeric($action[2]) ) { makePage(budget_editform(intval($action[2])), 'Edit Budget Item'); }
							else { makePage(perms_error(), 'FATAL:: Error'); } 
						}
					} else { makePage(perms_no(), 'Access Denied'); }
					break;
				case "item":
					if ( perms_checkperm($user_name, 'viewbudget') && is_numeric($action[2]) ) {
						makePage(budget_viewitem(intval($action[2])), "Budget Item #{$action[2]}"); 
					} else { makePage(perms_no(), 'Access Denied'); }
					break;
				case "email":
					if ( perms_checkperm($user_name, 'viewbudget') && is_numeric($action[2]) ) {
						email_budget(intval($action[2]));
					} else { makePage(perms_no(), 'Access Denied'); }
					break;
				case "del":
					if ( perms_checkperm($user_name, 'editbudget') && is_numeric($action[2]) ) {
						if ($_SERVER['REQUEST_METHOD'] == "POST") { budget_del_do(intval($action[2])); }
						else { makePage(budget_delform(intval($action[2])), 'Delete Item'); }
					} else { makePage(perms_no(), 'Access Denied'); }
					break;
				default:
					makePage(display_home($user_name, 2), 'Budgets');
					break;
			} break;
		
		case "hours":
			switch ( $action[1] ) {
				case "add":
					if ( perms_checkperm($user_name, 'addhours') ) {
						if ( isset($_REQUEST['new-hours']) && $_REQUEST['new-hours'] ) { hours_add_do(); }
						else { makePage(hours_add(), 'Add Payroll Hours'); }
					} else { makePage(perms_no(), 'Access Denied'); }
					break;
				case "remind":
					if ( perms_isadmin($user_name) ) {
						if ( $_SERVER['REQUEST_METHOD'] == "POST" ) { hours_remind_do(); }
						else { makePage(hours_remind_pick(), 'Send Payroll Reminders'); }
					} else { makePage(perms_no(), 'Access Denied'); }
					break;
				case "view":
					if ( perms_checkperm($user_name, 'addhours') ) {
						if ( $_SERVER['REQUEST_METHOD'] == "POST" ) { 
							if ( isset($_REQUEST['userid']) ) { makePage(hours_view(intval($_REQUEST['userid'])), 'View Hours'); }
							else { makePage(hours_view(0), 'View Your Hours'); }
						} else { 
							if ( $action[2] == 'unpaid' ) { makePage(hours_view_unpaid(), 'Veiw Pending Hours'); }
							else { makePage(hours_view_pick(), 'View Hours'); }
						}
					} else { makePage(perms_no(), 'Access Denied'); }
					break;
				case "clear":
					if ( perms_isadmin($user_name) && is_numeric($action[2]) ) {
						hours_set_paid(intval($action[2]));
					} else { makePage(perms_no(), 'Access Denied'); }
					break;
				case "edit":
					if ( perms_checkperm($user_name, 'edithours') && is_numeric($action[2]) ) {
						if ($_SERVER['REQUEST_METHOD'] == "POST") { hours_edit_do($_REQUEST['id']); }
						else { makePage(hours_edit(intval($action[2])), 'Edit Payroll Hours'); }
					} else { makePage(perms_no(), 'Access Denied'); }
					break;
				case "email":
					if ( $action[2] == 'unpaid' && perms_isadmin($user_name) ) {
							makePage(email_hours_unpaid(), 'Send Pending Payroll');
					} else {
						if ( perms_checkperm($user_name, 'viewhours') ) {
							email_hours($_REQUEST['id'], $_REQUEST['sdate'], $_REQUEST['edate']);
						} else { makePage(perms_no(), 'Access Denied'); }
					}
					break;
				case "del":
					if ( perms_checkperm($user_name, 'edithours') && is_numeric($action[2]) ) {
						if ($_SERVER['REQUEST_METHOD'] == "POST") { hours_del_do($_REQUEST['id']); }
						else { makePage(hours_del(intval($action[2])), 'Delete Payroll Hours'); }
					} else { makePage(perms_no(), 'Access Denied'); }
					break;
				default:
					makePage(display_home($user_name, 1), 'Payroll Recording');
					break;
			} break;
		case "mail":
			switch ( $action[1] ) {
				case "view":
					makePage(msg_sent_view(), 'Message Outbox');
					break;
				case "delete":
					if ( is_numeric($action[2]) ) {
						msg_delete(intval($action[2]));
					} else { makePage(perms_error(), 'Oops!'); }
					break;
				case "clean":
					msg_clear_inbox();
					break;
				default:
					makePage(msg_inbox_view(), 'Message Inbox');
					break;
			} break;
		case "user":
			switch ( $action[1] ) {
				case "password":
					if ( $user_name <> "guest" ) {
						if ($_SERVER['REQUEST_METHOD'] == "POST") { perms_changepass_do(); }
						else { makePage(perms_changepass_form(), 'Change Password'); }
					} else { makePage(perms_no(), 'Access Denied'); }
					break;
				case "view":
					if ( perms_isadmin($user_name) ) {
						makePage(perms_viewuser(), 'List Users');
					} else { makePage(perms_no(), 'Access Denied'); }
					break;
				case "edit" :
					if ( perms_isadmin($user_name) && is_numeric($action[2]) ) {
						if ($_SERVER['REQUEST_METHOD'] == "POST") { perms_edituser_do($_REQUEST['id']); }
						else { makePage(perms_edituser_form(intval($action[2])), 'Edit User'); }
					} else { makePage(perms_no(), 'Access Denied'); }
					break;
				case "groups" :
					if ( perms_isadmin($user_name) ) {
						if ($_SERVER['REQUEST_METHOD'] == "POST") {
							if ( isset($_REQUEST['newgroup']) ) { perms_group_add(); }
							if ( isset($_REQUEST['newname']) ) { perms_group_ren(); }
						} else { makePage(perms_groupform(), 'Group Management'); }
					} else { makePage(perms_no(), 'Access Denied'); }
					break;
				case "mail":
					if ( perms_isadmin($user_name) ) { 
						if ( $_SERVER['REQUEST_METHOD'] == "POST" ) { perms_mailcode_do(); }
						else { makePage(perms_mailcode(), 'Configure TDTracMail'); }
					} else { makePage(perms_no(), 'Access Denied'); }
					break;
				case "perms":
					if ( perms_isadmin($user_name) ) { 
						switch ( $action[2] ) {
							case "edit":
								if ( $_SERVER['REQUEST_METHOD'] == "GET" ) { makePage(perms_editpickform(), 'Edit Permissions'); }
								else {
									if ( isset($_REQUEST['editgroupperm']) ) { makePage(perms_editform(), 'Edit Permissions'); }
									if ( isset($_REQUEST['grpid']) ) { perms_save($_REQUEST['grpid']); }
								}
								break;
							default:
								makePage(perms_view(), 'View Permissions');
								break;
							}
					} else { makePage(perms_no(), 'Access Denied'); }
					break;
				case "add":
					if ( perms_checkperm($user_name, 'adduser') ) {
						if ( $_SERVER['REQUEST_METHOD'] == "POST" ) { perms_adduser_do(); }
						else { makePage(perms_adduser_form(), 'Add User'); }
					} else { makePage(perms_no(), 'Access Denied'); }
					break;
				case "login":
					islogin_dologin();
					break;
				case "logout":
					islogin_logout();
					break;
				default:
					makePage(display_home($user_name, 4), 'User Management');
					break;
			} break;
		default:
			makePage(display_home($user_name), 'TD Management Made Easy');
			break;
	} 
}
*/

?>
