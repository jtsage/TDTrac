<?php
/**
 * TDTrac Main Program
 * 
 * Contains main program logic.
 * @package tdtrac
 * @version 4.0.0
 * @author J.T.Sage <jtsage@gmail.com>
 */
#error_reporting(-1);
#ini_set('display_errors', 'On');
ob_start(); session_start(); 

## PROGRAM DETAILS. DO NOT EDIT UNLESS YOU KNOW WHAT YOU ARE DOING
$TDTRAC_VERSION = "4.0.0";
$TDTRAC_DBVER = "3.1.0";
$TEST_MODE = false; // BE VERY, VERY VERBOSE
$TEST_MODE_STOPUDSQL = false; // STOP UPDATE/DELETE SQL FROM RUNNING
$TEST_MODE_STOPISQL = false; // STOP INSERT SQL FROM RUNNING - INPLIES STOPUDSQL
$CANCEL = false;
$CLOSE = false;
$EXTRA_NAV = false;
$HEAD_LINK = array('');

/** Site Confiuration File */
require_once("config.php");
/** Function, Library and Module loader */
require_once("lib/functions-load.php");

if ( !file_exists(".htaccess") ) { $TDTRAC_SITE .= "index.php?action="; }

$user = new tdtrac_user();

$rawaction = preg_split("/\//", ltrim($_REQUEST['action'],"/"));

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
if ( $action['module'] <> "json" ) {
	$_SESSION['tdtrac']['two'] = $_SESSION['tdtrac']['one'];
	$_SESSION['tdtrac']['one'] = $_SESSION['tdtrac']['this'];
	$_SESSION['tdtrac']['this'] = "{$TDTRAC_SITE}{$_REQUEST['action']}";
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
		case "json":
			$json = new tdtrac_json($user, $action);
			$json->handler();
			break;
		case "user":			
			switch( $action['action'] ) {
				case "logout":
					$user->logout();
					makePage(error_page('You Have Been Logged Out'), 'Logged Out');
					break;
				case "password":
					if ( $user->username == "guest" ) { 
						makePage(error_page('You Cannot Change Guest Password'), 'Error');
					}
					if ( $_SERVER['REQUEST_METHOD'] == "POST" ) {
						$user->changepass();
					} else {
						makePage($user->changepass_form(), 'Change Password');
					} break;
				default:
					makePage(error_page('Unknown Page'), 'Unknown Page');
			}
			break;
		case "todo":
			$EXTRA_NAV = true;
			$todo = new tdtrac_todo($user, $action);
			$todo->output();
			break;
		case "shows":
			$EXTRA_NAV = true;
			$shows = new tdtrac_shows($user, $action);
			$shows->output();
			break;
		case "hours":
			$EXTRA_NAV = true;
			$hours = new tdtrac_hours($user, $action);
			$hours->output();
			break;
		case "mail":
			$mail = new tdtrac_mail($user, $action);
			$mail->output();
			break;
		case "admin":
			$EXTRA_NAV = true;
			$admin = new tdtrac_admin($user, $action);
			$admin->output();
			break;
		case "budget":
			$EXTRA_NAV = true;
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
			$html[] = "<h3>{$hdivTitle}</h3>";
			foreach ( $hdivData as $line ) {
				$html[] = "			<p>{$line}</p>";
			}
			$CLOSE = true;
			makePage($html, "Help");
			break;
	
		default: 
			$index_items = array();
			$html = array();
		
			$mail_num = get_single("SELECT COUNT(id) as num FROM `{$MYSQL_PREFIX}msg` WHERE toid = ".$user->id);
			$todo_num = get_single("SELECT COUNT(*) as num FROM {$MYSQL_PREFIX}todo WHERE assigned = {$user->id} AND complete = 0");
			
			$payr_num = (!$user->isemp) ?
				number_format(get_single("SELECT SUM(worked*payrate) AS num FROM {$MYSQL_PREFIX}hours h, {$MYSQL_PREFIX}users u WHERE h.userid = u.userid AND submitted = 0{$extrasql}"),2) :
				number_format(get_single("SELECT SUM(worked*payrate) AS num FROM {$MYSQL_PREFIX}hours h, {$MYSQL_PREFIX}users u WHERE h.userid = u.userid AND submitted = 0 AND h.userid = {$user->id}"),2);
			
			$index_items[] = array($TDTRAC_SITE.'mail/inbox/', 'msg', 'Messages', "Unread: {$mail_num}");
			$index_items[] = array($TDTRAC_SITE.'todo/', 'todo', 'Todo Lists', "Incomplete: {$todo_num}");
			$index_items[] = array($TDTRAC_SITE.'hours/', 'hours', (($user->isemp)?"Your ":"")."Payroll", "Pending: \${$payr_num}");
			
			if ( $user->can('viewbudget') ) {
				$index_items[] = array($TDTRAC_SITE.'budget/', 'budget', 'Budgets', "Pending: \$".number_format(get_single("SELECT SUM(price+tax) AS num FROM {$MYSQL_PREFIX}budget"),2));
			}
			if ( $user->can('editshow') ) {
				$index_items[] = array($TDTRAC_SITE.'shows/', 'shows', 'Shows', "Open: ".get_single("SELECT COUNT(*) AS num FROM {$MYSQL_PREFIX}shows WHERE closed = 0"));
			}
			if ( $user->admin ) {
				$index_items[] = array($TDTRAC_SITE.'admin/', 'admin', 'Administration', '&nbsp;');
			}
			
			$col = 1;
			$parts = array('', 'a','b','c');
			$html[] = "<div data-theme='a' class='ui-grid-b tdtrac-index'>";
			foreach ( $index_items as $item ) {
				$html[] = "<div class='ui-block-{$parts[$col]}'><div class='main-index-img ui-bar ui-bar-a'>"
					."<a href='{$item[0]}'><img src='{$TDTRAC_SITE}images/main-{$item[1]}.png' />"
					."<br /><h2>{$item[2]}</h2>"
					."<p>{$item[3]}</p>"
					."</a></div></div>";
				$col++; if ( $col == 4 ) { $col = 1; }
			}
			$html[] = "</div>";
			
			makePage($html, 'TD Tracking Made Easy');
			break;
	}
}
?>
