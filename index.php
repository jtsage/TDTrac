<?php
ob_start(); session_start(); 

## PROGRAM DETAILS. DO NOT EDIT UNLESS YOU KNOW WHAT YOU ARE DOING
$TDTRAC_VERSION = "1.1.0";
$TDTRAC_PERMS = array("addshow", "editshow", "viewshow", "addbudget", "editbudget", "viewbudget", "addhours", "edithours", "viewhours", "adduser");

require_once("config.php");
require_once("lib/functions-load.php");
if ( !file_exists(".htaccess") ) { $TDTRAC_SITE .= "index.php?action="; }

$login = islogin();

$page_title = $_REQUEST['action'];

//$page_title = substr($_SERVER['REQUEST_URI'], 1); 
//$page_title = preg_replace("/\?.+$/", "", $page_title);
if ( $page_title == "" ) { $page_title = "home"; }
require_once("lib/header.php");

if ( isset($_SESSION['infodata']) ) { echo "<div id=\"infobox\"><span style=\"font-size: .7em\">{$_SESSION['infodata']}</span></div>"; unset($_SESSION['infodata']); }

if ( !$login[0] ) { print $login[1]; } else { $user_name = $login[1]; }

//NO AUTH OPTIONS
switch ($page_title) {
    case "login":
	islogin_dologin();
	break;
    case "pwremind":
	if ($_SERVER['REQUEST_METHOD'] == "POST") { echo email_pwsend(); 
	} else { echo islogin_pwform(); }
	break;
    case "logout":
	islogin_logout();
	break;
}
if ( $login[0] ) {
  switch($page_title) {
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

    case "add-budget":
	if ( perms_checkperm($user_name, 'addbudget') ) {
		if ($_SERVER['REQUEST_METHOD'] == "POST") { budget_add(); }
		else { echo budget_addform(); }
	} else { echo perms_no(); }
	break;
    case "view-budget":
	if ( perms_checkperm($user_name, 'viewbudget') ) {
		if ( $_SERVER['REQUEST_METHOD'] == "POST" ) { echo budget_view($_REQUEST['showid']); }
		else { echo budget_viewselect(); }
	} else { echo perms_no(); }
	break;
    case "edit-budget":
	if ( perms_checkperm($user_name, 'editbudget') ) {
		if ($_SERVER['REQUEST_METHOD'] == "POST") { budget_edit_do($_REQUEST['id']); }
		else { echo budget_editform($_REQUEST['id']); }
	} else { echo perms_no(); }
	break;
    case "email-budget":
        if ( perms_checkperm($user_name, 'viewbudget') ) {
                echo email_budget($_REQUEST['id']); 
        } else { echo perms_no(); }
        break;
    case "del-budget":
	if ( perms_checkperm($user_name, 'editbudget') ) {
		if ($_SERVER['REQUEST_METHOD'] == "POST") { budget_del_do($_REQUEST['id']); }
		else { echo budget_delform($_REQUEST['id']); }
	} else { echo perms_no(); }
	break;
    case "add-hours":
        if ( perms_checkperm($user_name, 'addhours') ) {
                if ( $_SERVER['REQUEST_METHOD'] == "POST" ) { hours_add_do(); }
                else { echo hours_add(); }
        } else { echo perms_no(); }
        break;
    case "view-hours":
        if ( perms_checkperm($user_name, 'addhours') ) {
                if ( $_SERVER['REQUEST_METHOD'] == "POST" ) { 
			if ( isset($_REQUEST['userid']) ) { echo hours_view($_REQUEST['userid']); }
			else { echo hours_view(0); }
		}
                else { echo hours_view_pick(); }
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
		}
		else { echo perms_groupform(); }
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
	
    case "home":
	echo display_home($user_name);
	break;
  }
}

require_once("lib/footer.php");

?>
