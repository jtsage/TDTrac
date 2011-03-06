<?php
/**
 * TDTrac Main Program
 * 
 * Contains main program logic.
 * @package tdtrac
 * @version 2.0.0
 * @author J.T.Sage <jtsage@gmail.com>
 */
ob_start(); session_start(); 

## PROGRAM DETAILS. DO NOT EDIT UNLESS YOU KNOW WHAT YOU ARE DOING
$TDTRAC_VERSION = "2.0.1";
$TDTRAC_DBVER = "2.0.1";
$TEST_MODE = false;
$SITE_SCRIPT = array('');
$CANCEL = false;
$CLOSE = false;
$HEAD_LINK = array('');

/** Site Confiuration File */
require_once("config.php");
/** Function, Library and Module loader */
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
if ( !$action['json'] ) {
	$_SESSION['tdtrac']['two'] = $_SESSION['tdtrac']['one'];
	$_SESSION['tdtrac']['one'] = $_SESSION['tdtrac']['this'];
	$_SESSION['tdtrac']['this'] = "/" . $_REQUEST['action'];
}

if ( !$user->loggedin ) {
	switch( $action['action'] ) {
		case "login":
			echo $user->login();
			break;
		case "forgot":
			if ( $_SERVER['REQUEST_METHOD'] == "POST" ) {
				email_pwsend();
			} else {
				makePage($user->password_form(), 'Forgotten Password');
			} break;
		default:
			if ( !isset($_SESSION['infodata']) ) { $_SESSION['infodata'] = "Please Login"; }
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
			break;
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
		case "budget":
			$budget = new tdtrac_budget($user, $action);
			$budget->output();
			break;
		case "help":
			if ( !isset($helpnode[$action['action']][$action['oper']]) ) {
				$hdivTitle = $helpnode['error']['title'];
				$hdivData = $helpnode['error']['data'];
			} else {
				$hdivTitle = $helpnode[$action['action']][$action['oper']]['title'];
				$hdivData = $helpnode[$action['action']][$action['oper']]['data'];
			}
			foreach ( $hdivData as $line ) {
				$html[] = "			<p>{$line}</p>";
			}
			$CLOSE = true;
			makePage($html, "TDTrac Help");
			break;
	
		default: 
			$html[] = "<ul data-role=\"listview\" data-inset=\"true\">";
			
			$mail_num = get_single("SELECT COUNT(id) as num FROM `{$MYSQL_PREFIX}msg` WHERE toid = ".$user->id);
			$todo_num = get_single("SELECT COUNT(*) as num FROM {$MYSQL_PREFIX}todo WHERE assigned = {$user->id} AND complete = 0");
			
			$payr_num = (!$user->isemp) ?
				number_format(get_single("SELECT SUM(worked*payrate) AS num FROM {$MYSQL_PREFIX}hours h, {$MYSQL_PREFIX}users u WHERE h.userid = u.userid AND submitted = 0{$extrasql}"),2) :
				number_format(get_single("SELECT SUM(worked*payrate) AS num FROM {$MYSQL_PREFIX}hours h, {$MYSQL_PREFIX}users u WHERE h.userid = u.userid AND submitted = 0 AND h.userid = {$user->id}"),2);
			
			$html[] = "	<li><a href=\"/mail/inbox/\">Message Inbox</a> <span class=\"ui-li-count\">{$mail_num}</span></li>";
			$html[] = "	<li><a href=\"/todo/\">Todo Lists</a> <span class=\"ui-li-count\">{$todo_num}</span></li>";
			$html[] = "	<li><a href=\"/hours/\">".(($user->isemp)?"Your ":"")."Payroll</a> <span class=\"ui-li-count\">\${$payr_num}</span></li>";
			
			if ( $user->can('viewbudget') ) {
				$budg_num = number_format(get_single("SELECT SUM(price+tax) AS num FROM {$MYSQL_PREFIX}budget"),2);
				$html[] = "	<li><a href=\"/budget/\">Budgets</a> <span class=\"ui-li-count\">\${$budg_num}</span></li>";
			}
			if ( $user->can('editshow') ) {
				$show_num = get_single("SELECT COUNT(*) AS num FROM {$MYSQL_PREFIX}shows WHERE closed = 0");
				$html[] = "	<li><a href=\"/shows/\">Show Management</a> <span class=\"ui-li-count\">{$show_num}</span></li>";
			}
			if ( $user->admin ) {
				$html[] = "	<li><a href=\"/admin/\">Administration</a></li>";
			}
			$html[] = "	<li><a href=\"/user/logout/\">Logout</a></li>";

			$html[] = "</ul>";
			makePage($html, 'TD Tracking Made Easy');
			break;
	}
}
?>
