<?php
ob_start(); session_start(); 

## PROGRAM DETAILS. DO NOT EDIT UNLESS YOU KNOW WHAT YOU ARE DOING
$TDTRAC_VERSION = "0.0.9b";
$TDTRAC_PERMS = array("addshow", "editshow", "viewshow", "addbudget", "editbudget", "viewbudget", "addhours", "edithours", "viewhours", "adduser");

require_once("config.php");
require_once("lib/functions-load.php");
$login = islogin();
$page_title = substr($_SERVER['REQUEST_URI'], 1); 
$page_title = preg_replace("/\?.+$/", "", $page_title);
if ( $page_title == "" ) { $page_title = "home"; }
require_once("lib/header.php");

if ( isset($_SESSION['infodata']) ) { echo "<div id=\"infobox\">{$_SESSION['infodata']}</div>"; unset($_SESSION['infodata']); }

if ( !$login[0] ) { print $login[1]; } else { $user_name = $login[1]; }

//NO AUTH OPTIONS
switch ($page_title) {
    case "login":
	islogin_dologin();
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
	preg_match("/.+\?id=(\d+)$/", $_SERVER['REQUEST_URI'], $match);
	if ( perms_checkperm($user_name, 'editshow') ) {
		if ($_SERVER['REQUEST_METHOD'] == "POST") { show_edit_do($_REQUEST['showid']); }
		else { echo show_edit_form($match[1]); }
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
	preg_match("/.+\?id=(\d+)$/", $_SERVER['REQUEST_URI'], $match);
	if ( perms_checkperm($user_name, 'editbudget') ) {
		if ($_SERVER['REQUEST_METHOD'] == "POST") { budget_edit_do($_REQUEST['id']); }
		else { echo budget_editform($match[1]); }
	} else { echo perms_no(); }
	break;
    case "email-budget":
        preg_match("/.+\?id=(\d+)$/", $_SERVER['REQUEST_URI'], $match);
        if ( perms_checkperm($user_name, 'viewbudget') ) {
                echo email_budget($match[1]); 
        } else { echo perms_no(); }
        break;
    case "del-budget":
	preg_match("/.+\?id=(\d+)$/", $_SERVER['REQUEST_URI'], $match);
	if ( perms_checkperm($user_name, 'editbudget') ) {
		if ($_SERVER['REQUEST_METHOD'] == "POST") { budget_del_do($_REQUEST['id']); }
		else { echo budget_delform($match[1]); }
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
        preg_match("/.+\?id=(\d+)$/", $_SERVER['REQUEST_URI'], $match);
        if ( perms_checkperm($user_name, 'edithours') ) {
                if ($_SERVER['REQUEST_METHOD'] == "POST") { hours_edit_do($_REQUEST['id']); }
                else { echo hours_edit($match[1]); }
        } else { echo perms_no(); }
        break;
    case "email-hours":
        preg_match("/.+\?id=(\d+)&sdate=(.+)&edate=(.+)$/", $_SERVER['REQUEST_URI'], $match);
        if ( perms_checkperm($user_name, 'viewhours') ) {
                echo email_hours($match[1], $match[2], $match[3]);
        } else { echo perms_no(); }
        break;
    case "del-hours":
        preg_match("/.+\?id=(\d+)$/", $_SERVER['REQUEST_URI'], $match);
        if ( perms_checkperm($user_name, 'edithours') ) {
                if ($_SERVER['REQUEST_METHOD'] == "POST") { hours_del_do($_REQUEST['id']); }
                else { echo hours_del($match[1]); }
        } else { echo perms_no(); }
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
	        preg_match("/.+\?id=(\d+)$/", $_SERVER['REQUEST_URI'], $match);
                if ($_SERVER['REQUEST_METHOD'] == "POST") { perms_edituser_do($_REQUEST['id']); }
                else { echo perms_edituser_form($match[1]); }
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
