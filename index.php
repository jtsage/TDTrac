<?php
/**
 * TDTrac Main Program
 * 
 * Contains main program logic.
 * @package tdtrac
 * @version 3.0.0
 * @author J.T.Sage <jtsage@gmail.com>
 */
ob_start(); session_start(); 

## PROGRAM DETAILS. DO NOT EDIT UNLESS YOU KNOW WHAT YOU ARE DOING
$TDTRAC_VERSION = "3.0-alpha1";
$TDTRAC_DBVER = "2.0.1";
$TEST_MODE = true;
$SITE_SCRIPT = array('');
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
if ( $action['module'] <> "json" ) {
	$_SESSION['tdtrac']['two'] = $_SESSION['tdtrac']['one'];
	$_SESSION['tdtrac']['one'] = $_SESSION['tdtrac']['this'];
	$_SESSION['tdtrac']['this'] = "/{$_REQUEST['action']}";
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
			$list = new tdlist(array('id' => 'mainmenu', 'inset' => true));
			$list->setFormat("<a href='%s'><img src='/images/main-%s.png' />%s <span class='ui-li-count'>%s</span></a>");
		
			$mail_num = get_single("SELECT COUNT(id) as num FROM `{$MYSQL_PREFIX}msg` WHERE toid = ".$user->id);
			$todo_num = get_single("SELECT COUNT(*) as num FROM {$MYSQL_PREFIX}todo WHERE assigned = {$user->id} AND complete = 0");
			
			$payr_num = (!$user->isemp) ?
				number_format(get_single("SELECT SUM(worked*payrate) AS num FROM {$MYSQL_PREFIX}hours h, {$MYSQL_PREFIX}users u WHERE h.userid = u.userid AND submitted = 0{$extrasql}"),2) :
				number_format(get_single("SELECT SUM(worked*payrate) AS num FROM {$MYSQL_PREFIX}hours h, {$MYSQL_PREFIX}users u WHERE h.userid = u.userid AND submitted = 0 AND h.userid = {$user->id}"),2);
			
			$list->addRow(array('/mail/inbox/', 'msg', 'Message Inbox', $mail_num));
			$list->addRow(array('/todo/', 'todo', 'Todo Lists', $todo_num));
			$list->addRow(array('/hours/', 'hours', (($user->isemp)?"Your ":"")."Payroll", $payr_num));
			
			if ( $user->can('viewbudget') ) {
				$list->addRow(array('/budget/', 'budget', 'Budgets', number_format(get_single("SELECT SUM(price+tax) AS num FROM {$MYSQL_PREFIX}budget"),2)));
			}
			if ( $user->can('editshow') ) {
				$list->addRow(array('/shows/', 'shows', 'Show Managment', get_single("SELECT COUNT(*) AS num FROM {$MYSQL_PREFIX}shows WHERE closed = 0")));
			}
			if ( $user->admin ) {
				$list->addRaw("<li><a href=\"/admin/\"><img src='/images/main-admin.png' />Administration</a></li>");
			}
			$list->addRaw("	<li data-icon='alert'><a href=\"/user/logout/\"><img src='/images/main-logout.png' />Logout</a></li>");
			
			makePage($list->output(), 'TD Tracking Made Easy');
			break;
	}
}
?>
