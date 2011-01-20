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
			switch( $action['action'] ) {
				case "logout":
					$user->logout();
					thrower("User Logged Out", '');
				case "password":
					if ( $user->username == "guest" ) { thrower("You Cannot Change Your Password"); }
					if ( $_SERVER['REQUEST_METHOD'] == "POST" ) {
						$user->changepass();
					} else {
						makePage($user->changepass_form(), 'Change Password');
					} break;
				default:
					thrower(false, ''); 
			}
		case "todo":
			$todo = new tdtrac_todo($user, $action);
			$todo->output();
			break;
		case "shows":
			$shows = new tdtrac_shows($user, $action);
			$shows->output();
			break;
		case "hours":
			$hours = new tdtrac_hours($user, $action);
			$hours->output();
			break;
		case "mail":
			$mail = new tdtrac_mail($user, $action);
			$mail->output();
			break;
		case "admin":
			$admin = new tdtrac_admin($user, $action);
			$admin->output();
			break;
		default: 
			$html[] = mail_check();
			//$html[] = rcpt_check();
			$html[] = todo_check();
			$html[] = "<br /><br /><div style=\"float: left; min-height: 400px; width: 48%\">";
			// Budget & Payroll
			$hour = new tdtrac_hours($user, $action);
			$html = array_merge($html, $hour->index());
			
			$html[] = "<br /><br /><br /><br /><br /><br /></div><div style=\"width: 48%; float: right;\">";
			// Shows, Todo & Admin
			$show = new tdtrac_shows($user, $action);
			$todo = new tdtrac_todo($user, $action);
			$admn = new tdtrac_admin($user, $action);
			
			$html = array_merge($html, $show->index(), $todo->index(), $admn->index());
			
			$html[] = "</div>";
			makePage($html, 'TD Tracking Made Easy');
			break;
	}
}

			
/*

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
		
*/

?>
