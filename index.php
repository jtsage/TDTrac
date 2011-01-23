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
$TDTRAC_VERSION = "2.0.0";
$TDTRAC_DBVER = "1.3.1";
$SITE_SCRIPT = array('');

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
		case "budget":
			$budget = new tdtrac_budget($user, $action);
			$budget->output();
			break;
		default: 
			$html[] = "<table id=\"dashtable\"><tr><td>";
			$html[] = "<dl class=\"dashboard\"><dt>Show Information</dt>";
			$html[] = make_dash('Shows Tracked', get_single("SELECT COUNT(*) AS num FROM {$MYSQL_PREFIX}shows"));
			$html[] = make_dash('Shows Active', get_single("SELECT COUNT(*) AS num FROM {$MYSQL_PREFIX}shows WHERE closed = 0"));
			$html[] = "</dl>";
			
			$html[] = "<dl class=\"dashboard\"><dt>Budget Information</dt>";
			$html[] = make_dash('Budget Items', get_single("SELECT COUNT(*) AS num FROM {$MYSQL_PREFIX}budget"));
			$html[] = make_dash('Total Expenditure', '$'.number_format(get_single("SELECT SUM(price) AS num FROM {$MYSQL_PREFIX}budget"),2));
			$html[] = make_dash('Pending Payment', '$'.number_format(get_single("SELECT SUM(price) AS num FROM {$MYSQL_PREFIX}budget WHERE pending = 1"),2));
			$html[] = make_dash('Pending Reimbursment', '$'.number_format(get_single("SELECT SUM(price) AS num FROM {$MYSQL_PREFIX}budget WHERE needrepay = 1 AND gotrepay = 0"),2));
			$html[] = make_dash('Reciepts Available', get_single("SELECT COUNT(*) AS num FROM {$MYSQL_PREFIX}rcpts"));
			$rPending = get_single("SELECT COUNT(*) AS num FROM {$MYSQL_PREFIX}rcpts WHERE handled = 0");
			if ( $rPending > 0 ) {
				$html[] = make_dash('Reciepts Pending', $rPending, 'dRed', 'budget/reciept/');
			} else {
				$html[] = make_dash('Reciepts Pending', $rPending, 'dGrn');
			}
			$html[] = "</dl>";
			
			$html[] = "<dl class=\"dashboard\"><dt>User Information</dt>";
			$html[] = make_dash('Total Users', get_single("SELECT COUNT(*) AS num FROM {$MYSQL_PREFIX}users"));
			$html[] = make_dash('Active Users', get_single("SELECT COUNT(*) AS num FROM {$MYSQL_PREFIX}users WHERE active = 1"));
			$html[] = make_dash('Users on payroll', get_single("SELECT COUNT(*) AS num FROM {$MYSQL_PREFIX}users WHERE active = 1 AND payroll = 1"));
			$nPass = get_single("SELECT COUNT(*) AS num FROM {$MYSQL_PREFIX}users WHERE chpass = 1");
			if ( $nPass > 0 ) {
				$html[] = make_dash('Users needing new Password', $nPass, 'dRed');
			} else {
				$html[] = make_dash('Users needing new Password', $nPass, 'dGrn');
			}
			$html[] = "</dl>";
			
			$html[] = "</td><td style=\"vertical-align: middle\">";
			
			$html[] = "<dl class=\"dashboard\"><dt>Payroll Information</dt>";
			$hPending = get_single("SELECT SUM(worked) AS num FROM {$MYSQL_PREFIX}hours WHERE submitted = 0");
			if ( $hPending > 0 ) {
				$html[] = make_dash('Payroll '.(($TDTRAC_DAYRATE)?"Days":"Hours").' Pending', $hPending, 'dRed', 'hours/view/type:unpaid/');
			} else {
				$html[] = make_dash('Payroll '.(($TDTRAC_DAYRATE)?"Days":"Hours").' Pending', $hPending, 'dGrn');
			}
			$html[] = make_dash('Payroll Total '.(($TDTRAC_DAYRATE)?"Days":"Hours").' Worked', get_single("SELECT SUM(worked) AS num FROM {$MYSQL_PREFIX}hours"));
			$html[] = make_dash('Payroll Total Expenditure', '$'.number_format(get_single("SELECT SUM(worked*payrate) as num FROM {$MYSQL_PREFIX}hours h, {$MYSQL_PREFIX}users u WHERE h.userid = u.userid"),2));
			$html[] = "</dl>";
			
			$html[] = "<dl class=\"dashboard\"><dt>Mail Information</dt>";
			$mTo = get_single("SELECT COUNT(id) as num FROM `{$MYSQL_PREFIX}msg` WHERE toid = ".$user->id);
			$mFm = get_single("SELECT COUNT(id) as num FROM `{$MYSQL_PREFIX}msg` WHERE fromid = ".$user->id);
			if ( $mTo > 0 ) {
				$html[] = make_dash('Your Unread Mail', $mTo, 'dRed', 'mail/inbox/');
			} else {
				$html[] = make_dash('Your Unread Mail', $mTo, 'dGrn');
			}
			if ( $mFm > 0 ) {
				$html[] = make_dash('Unread Mail You Sent', $mFm, '', 'mail/outbox/');
			} else {
				$html[] = make_dash('Unread Mail You Sent', $mFm);
			}
			$html[] = make_dash('All Unread Messages', get_single("SELECT COUNT(id) as num FROM `{$MYSQL_PREFIX}msg`"));
			$html[] = "</dl>";
			
			$html[] = "<dl class=\"dashboard\"><dt>To-Do Information</dt>";
			$tPending = get_single("SELECT COUNT(*) as num FROM {$MYSQL_PREFIX}todo WHERE assigned = {$user->id} AND complete = 0");
			if ( $tPending > 0 ) {
				$html[] = make_dash('Your Pending To-Do Items', $tPending, 'dRed', "todo/view/id:{$user->id}/type:user/");
			} else {
				$html[] = make_dash('Your Pending To-Do Items', $tPending, 'dGrn');
			}
			$html[] = make_dash('All Pending To-Do Items', get_single("SELECT COUNT(*) as num FROM {$MYSQL_PREFIX}todo WHERE complete = 0"));
			$html[] = make_dash('All Overdue To-Do Items', get_single("SELECT COUNT(*) as num FROM {$MYSQL_PREFIX}todo WHERE complete = 0 AND due < NOW()"));
			$html[] = make_dash('To-Do Items in System', get_single("SELECT COUNT(*) as num FROM {$MYSQL_PREFIX}todo"));
			
			$html[] = "</td></tr><tr><td><br /><br /></td><td></td></tr><tr><td><div class=\"dashmenu\">";
			// Budget & Payroll
			$budg = new tdtrac_budget($user, $action);
			$hour = new tdtrac_hours($user, $action);
			$html = array_merge($html, $budg->index(), $hour->index());
			
			$html[] = "</div></td><td><div class=\"dashmenu\">";
			// Shows, Todo & Admin
			$show = new tdtrac_shows($user, $action);
			$todo = new tdtrac_todo($user, $action);
			$admn = new tdtrac_admin($user, $action);
			
			$html = array_merge($html, $show->index(), $todo->index(), $admn->index());
			
			$html[] = "</div></td></tr></table>";
			makePage($html, 'TD Tracking Made Easy');
			break;
	}
}
?>
