<?php
/**
 * TDTrac Login Functions
 * 
 * Contains all login related functions. 
 * Data hardened
 * @package tdtrac
 * @version 1.4.0
 * @author J.T.Sage <jtsage@gmail.com>
 */

/** 
 * Check user's logged in status
 * 
 * @global bool Enable login debug controls
 * @global string MySQL Table Prefix
 * @return array Logged in status + login form
 */
function islogin() {
	GLOBAL $LOGIN_DEBUG, $MYSQL_PREFIX;

	if ( !islogin_cookieexist() ) { $retty = array(0, islogin_form()); }	
	else {
		if ( !islogin_cookietest() ) { $retty = array(0, islogin_form()); }
		else { $retty = array(1, $_SESSION['tdtracuser']); }
	}
	return $retty;
}

/** 
 * Check if a user cookie exists
 * 
 * @global bool Enable login debug controls
 * @global string MySQL Table Prefix
 * @return bool Existence of cookie
 */
function islogin_cookieexist() {
	GLOBAL $LOGIN_DEBUG, $MYSQL_PREFIX;
	if ( !isset($_SESSION['tdtracuser']) ) { return 0; }
	if ( !isset($_SESSION['tdtracpass']) ) { return 0; }
	if ( $LOGIN_DEBUG ) { echo "DEBUG: Cookie Found!\n"; }
	return 1;
}

/** 
 * Check user's logged in status
 * 
 * @global resource Database Link
 * @global string MySQL Table Prefix
 * @return bool True if cookie is correct and current
 */
function islogin_cookietest() {
	GLOBAL $db, $MYSQL_PREFIX;
	$checkname = $_SESSION['tdtracuser'];
	$checkpass = $_SESSION['tdtracpass'];

	$sql = sprintf("SELECT password FROM `{$MYSQL_PREFIX}users` WHERE username = '%s' LIMIT 1",
		mysql_real_escape_string($checkname)
	);
	$result = mysql_query($sql, $db);

	$row = mysql_fetch_array($result);
	mysql_free_result($result);
	if ( md5("havesomesalt".$row['password']) == $checkpass ) { return 1; }
	return 0;
}

/** 
 * Show Login Form
 * 
 * @global string Address for links.
 * @return string HTML output
 */
function islogin_form() {
	GLOBAL $TDTRAC_SITE;
	$form = new tdform("{$TDTRAC_SITE}user/login/", "loginform", 1, "loginform", 'Login');
	
	$result = $form->addText('tracuser', 'User Name');
	$result = $form->addPass('tracpass', 'Password');
	
	$html = $form->output('Login', "[-<a href=\"{$TDTRAC_SITE}user/forgot/\">Forgot Password?</a>-] ");
	return $html;
}

/** 
 * Show Password Reminder Form
 * 
 * @global string Address for links.
 * @return string HTML output
 */
function islogin_pwform() {
	GLOBAL $TDTRAC_SITE;
	$form = new tdform("{$TDTRAC_SITE}user/forgot/", "loginform", 1, "loginform", 'Send Password Reminder');
	
	$result = $form->addText('tracemail', 'E-Mail Address');
	
	$html = $form->output('Send Reminder');
	return $html;
}

/**
 * Log a User Out
 */
function islogin_logout() {
	unset($_SESSION['tdtracuser']);
	unset($_SESSION['tdtracpass']);
	thrower("User Logged Out");
}

/**
 * Log a user in
 * 
 * @global resource Database Link
 * @global string MySQL Table Prefix
 * @global string Site address for links
 * @global string Database version string
 */
function islogin_dologin() {
	GLOBAL $db, $MYSQL_PREFIX, $TDTRAC_SITE, $TDTRAC_DBVER;
	$checkname = $_REQUEST['tracuser'];
	$checkpass = $_REQUEST['tracpass'];

	$sql = sprintf("SELECT userid, password, active, chpass, DATE_FORMAT(lastlogin, '%%b %%D %%h:%%i %%p') AS lastlog FROM `{$MYSQL_PREFIX}users` WHERE username = '%s' LIMIT 1",
		mysql_real_escape_string($checkname)
	);
	$result = mysql_query($sql, $db);

	$row = mysql_fetch_array($result);
	if ( $row['active'] == 0 ) { thrower("User Account is Locked!"); }
	if ( $row['password'] == $checkpass ) { 
		$infodata  = "Login Successful";
		$infodata .= "<br />Last Login: {$row['lastlog']}";
		$_SESSION['tdtracuser'] = $checkname;
		$_SESSION['tdtracpass'] = md5("havesomesalt".$checkpass);
		$setlastloginsql = "UPDATE {$MYSQL_PREFIX}users SET lastlogin = CURRENT_TIMESTAMP WHERE userid = {$row['userid']}";
		$setlastloginres = mysql_query($setlastloginsql, $db);
		if ( $row['userid'] == 1 ) { //CHECK UPGRADE STATUS ON ADMIN LOGIN (USER #1)
			$sql2 = "SELECT value FROM {$MYSQL_PREFIX}tdtrac WHERE name = 'version' AND value = '{$TDTRAC_DBVER}'";
			$res2 = mysql_query($sql2, $db);
			if ( mysql_num_rows($res2) < 1 ) { $infodata .= "<br><strong>WARNING:</strong> Database not up-to-date, please run upgrade script!"; }
		}
    		if ( $row['chpass'] <> 0 ) { 
			$infodata = "Login Successful, Please Change Your Password!"; header("Location: {$TDTRAC_SITE}user/password/"); ob_flush();
		} 
	}
	else {
		$infodata = "Login Failed!";
	}
	thrower($infodata);
}

