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
$TDTRAC_VERSION = "1.4.0";
$TDTRAC_DBVER = "1.3.1";
$TDTRAC_PERMS = array("addshow", "editshow", "viewshow", "addbudget", "editbudget", "viewbudget", "addhours", "edithours", "viewhours", "adduser");
$SITE_SCRIPT = array('');

require_once("config.php");
require_once("lib/functions-load.php");
$TDTRAC_HELP = $TDTRAC_SITE . "help.php?node=";
if ( !file_exists(".htaccess") ) { $TDTRAC_SITE .= "index.php?action="; }

$login = islogin();

$action = preg_split("/\//", $_REQUEST['action']);


if ( !isset($action[0]) || $action[0] == "" ) { $action[0] = 'index'; }
if ( !isset($action[1]) || $action[1] == "" ) { $action[1] = 'index'; }
if ( !isset($action[2]) || $action[2] == "" ) { $action[2] = 'index'; }

if ( !$login[0] ) { 
	if ( $action[0] == "user" ) {
		switch ($action[1]) {
			case "login":
				islogin_dologin();
				break;
			case "forgot":
				if ( $_SERVER['REQUEST_METHOD'] == "POST" ) { makePage(email_pwsend(), 'Forgotten Password'); 
				} else { makePage(islogin_pwform(), 'Forgotten Password'); }
				break;
			case "logout":
				islogin_logout();
				break;
			default:
				makePage($login[1], 'Login');
				break;
		}
	} else {
		makePage($login[1], 'Login');
	}

} else {
	$user_name = $login[1];

	switch($action[0]) {
		case "user":
			switch($action[1]) {
				case "login":
					islogin_dologin();
					break;
				case "logout":
					islogin_logout();
					break;
				default:
					makePage($login[1], 'Login');
					break;
			}

		case "index":
			echo makePage(display_home($user_name));
			break;

		case "search":
			if ( perms_checkperm($user_name, 'viewbudget') ) {
				if ( $_SERVER['REQUEST_METHOD'] == "POST" ) {
					if ( isset($_REQUEST['keywords']) && $_REQUEST['keywords'] <> "" ) { makePage(budget_search($_REQUEST['keywords']), 'Search Results'); }
					else { echo makePage(display_home($user_name)); }
				} else { echo makePage(display_home($user_name)); }
			} else { echo makePage(perms_no(), 'Access Denied'); }
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
						makePage(email_budget(intval($action[2])), 'E-Mail Budget');
					} else { makePage(perms_no(), 'Access Denied'); }
					break;
				case "delete":
					if ( perms_checkperm($user_name, 'editbudget') && is_numeric($action[2]) ) {
						if ($_SERVER['REQUEST_METHOD'] == "POST") { budget_del_do(intval($action[2])); }
						else { makePage(budget_delform(intval($action[2])), 'Delete Item'); }
					} else { makePage(perms_no(), 'Access Denied'); }
					break;
				default:
					makePage(display_home($user_name, 2), 'Budgets');
					break;
			} break;


		case "add-show":
			if ( perms_checkperm($user_name, 'addshow') ) {
				if ($_SERVER['REQUEST_METHOD'] == "POST") { show_add_do(); }
				else { echo show_add_form(); }
			} else { echo perms_no(); }
			break;
		case "view-show":
			if ( perms_checkperm($user_name, 'viewshow') ) {
				echo show_view();
			} else { echo perms_no(); }
			break;
		case "edit-show":
			if ( perms_checkperm($user_name, 'editshow') ) {
				if ($_SERVER['REQUEST_METHOD'] == "POST") { show_edit_do($_REQUEST['showid']); }
				else { echo show_edit_form($_REQUEST['id']); }
			} else { echo perms_no(); }
			break;
		case "add-todo":
			if ( perms_checkperm($user_name, 'addbudget')) {
				if ( $_SERVER['REQUEST_METHOD'] == "POST" ) { echo todo_add_do(); }
				else { echo todo_add(); }
			} else { echo perms_no(); }
			break;
		case "view-todo":
			if ( perms_checkperm($user_name, 'viewbudget')) {
				if ( isset($_REQUEST['onlyuser']) && $_REQUEST['onlyuser'] ) { echo todo_view($user_name); }
				elseif ( isset($_REQUEST['todouser']) ) { echo todo_view($_REQUEST['todouser'], 'user'); }
				elseif ( isset($_REQUEST['todoshow']) ) { echo todo_view($_REQUEST['todoshow'], 'show'); }
				elseif ( isset($_REQUEST['tododue']) ) { echo todo_view(1, 'overdue'); }
				else { echo todo_view(); }
			} else {
				echo view_todo($user_name);
			}
			break;
		case "edit-todo":
			if ( perms_checkperm($user_name, 'editbudget') ) {
				if ($_SERVER['REQUEST_METHOD'] == "POST") { todo_edit_do($_REQUEST['id']); }
				else { echo todo_edit_form($_REQUEST['id']); }
			} else { echo perms_no(); }
			break;
		case "del-todo":
			if ( perms_checkperm($user_name, 'editbudget') ) {
				if ($_SERVER['REQUEST_METHOD'] == "POST") { todo_del_do($_REQUEST['id']); }
				else { echo todo_del_form($_REQUEST['id']); }
			} else { echo perms_no(); }
			break;
		case "done-todo":
			if ( isset($_REQUEST['id']) ) {
				todo_mark_do($_REQUEST['id']); 
			} else { echo perms_no(); }
			break;

		case "add-hours":
			if ( perms_checkperm($user_name, 'addhours') ) {
				if ( isset($_REQUEST['new-hours']) && $_REQUEST['new-hours'] ) { hours_add_do(); }
				else { echo hours_add(); }
			} else { echo perms_no(); }
			break;
		case "remind-hours":
			if ( perms_isadmin($user_name) ) {
				if ( $_SERVER['REQUEST_METHOD'] == "POST" ) { echo hours_remind_do(); }
				else { echo hours_remind_pick(); }
			} else { echo perms_no(); }
			break;
		case "view-hours":
			if ( perms_checkperm($user_name, 'addhours') ) {
				if ( $_SERVER['REQUEST_METHOD'] == "POST" ) { 
					if ( isset($_REQUEST['userid']) ) { echo hours_view($_REQUEST['userid']); }
				else { echo hours_view(0); }
				} else { echo hours_view_pick(); }
			} else { echo perms_no(); }
			break;
		case "view-hours-unpaid":
			if ( perms_checkperm($user_name, 'addhours') ) {
				echo hours_view_unpaid(); 
			} else { echo perms_no(); }
			break;
		case "hours-set-paid":
			if ( perms_isadmin($user_name) ) {
				hours_set_paid($_REQUEST['id']);
			} else { echo perms_no(); }
			break;
		case "edit-hours":
			if ( perms_checkperm($user_name, 'edithours') ) {
				if ($_SERVER['REQUEST_METHOD'] == "POST") { hours_edit_do($_REQUEST['id']); }
				else { echo hours_edit($_REQUEST['id']); }
			} else { echo perms_no(); }
			break;
		case "email-hours":
			if ( perms_checkperm($user_name, 'viewhours') ) {
				echo email_hours($_REQUEST['id'], $_REQUEST['sdate'], $_REQUEST['edate']);
			} else { echo perms_no(); }
			break;
		case "email-hours-unpaid":
			if ( perms_isadmin($user_name) ) {
				echo email_hours_unpaid();
			} else { echo perms_no(); }
			break;
		case "del-hours":
			if ( perms_checkperm($user_name, 'edithours') ) {
				if ($_SERVER['REQUEST_METHOD'] == "POST") { hours_del_do($_REQUEST['id']); }
				else { echo hours_del($_REQUEST['id']); }
			} else { echo perms_no(); }
			break;

		case "msg-view":
			echo msg_sent_view();
		break;
		case "msg-read":
			echo msg_inbox_view();
		break;
		case "msg-delete":
			msg_delete($_REQUEST['id']);
		break;
		case "msg-clean":
			msg_clear_inbox();
		break;

		case "change-pass":
			if ( $user_name <> "guest" ) {
				if ($_SERVER['REQUEST_METHOD'] == "POST") { perms_changepass_do(); }
				else { echo perms_changepass_form(); }
			} else { echo perms_no(); }
			break;
		case "view-user":
		if ( perms_isadmin($user_name) ) {
			echo perms_viewuser();
		} else { echo perms_no(); }
		break;
		case "edit-user" :
			if ( perms_isadmin($user_name) ) {
				if ($_SERVER['REQUEST_METHOD'] == "POST") { perms_edituser_do($_REQUEST['id']); }
				else { echo perms_edituser_form($_REQUEST['id']); }
			} else { echo perms_no(); }
			break;
		case "groups" :
			if ( perms_isadmin($user_name) ) {
				if ($_SERVER['REQUEST_METHOD'] == "POST") {
					if ( isset($_REQUEST['newgroup']) ) { perms_group_add(); }
					if ( isset($_REQUEST['newname']) ) { perms_group_ren(); }
				} else { echo perms_groupform(); }
			} else { echo perms_no(); }
			break;
		case "mail-perms":
			if ( perms_isadmin($user_name) ) { 
				if ( $_SERVER['REQUEST_METHOD'] == "POST" ) { perms_mailcode_do(); }
				else {
					echo perms_mailcode();
				}
			} else { echo perms_no(); }
			break;
		case "edit-perms":
			if ( perms_isadmin($user_name) ) { 
				if ( $_SERVER['REQUEST_METHOD'] == "GET" ) { echo perms_editpickform(); }
				else {
					if ( isset($_REQUEST['editgroupperm']) ) { echo perms_editform(); }
					if ( isset($_REQUEST['grpid']) ) { perms_save($_REQUEST['grpid']); }
				}
			} else { echo perms_no(); }
			break;
		case "add-user":
			if ( perms_checkperm($user_name, 'adduser') ) {
				if ( $_SERVER['REQUEST_METHOD'] == "POST" ) { perms_adduser_do(); }
				else { echo perms_adduser_form(); }
			} else { echo perms_no(); }
			break;
		case "view-perms":
			if ( perms_isadmin($user_name) ) {
				echo perms_view();
			} else { echo perms_no(); }
			break;

		case "main-show":
			echo display_home($user_name, 3);
			break;
		case "main-hours":
			echo display_home($user_name, 1);
			break;
		case "main-perms":
			echo display_home($user_name, 4);
			break;
		case "main-todo":
			echo display_home($user_name, 5);
			break;
		default:
			echo display_home($user_name);
			break;
	} }


?>
