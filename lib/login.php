<?php

function islogin() {
	GLOBAL $LOGIN_DEBUG, $MYSQL_PREFIX;

	if ( !islogin_cookieexist() ) { $retty = array(0, islogin_form()); }	
	else {
		if ( !islogin_cookietest() ) { $retty = array(0, islogin_form()); }
		else { $retty = array(1, $_SESSION['tdtracuser']); }
	}
	return $retty;
}

function islogin_cookieexist() {
	GLOBAL $LOGIN_DEBUG, $MYSQL_PREFIX;
	if ( !isset($_SESSION['tdtracuser']) ) { return 0; }
	if ( !isset($_SESSION['tdtracpass']) ) { return 0; }
	if ( $LOGIN_DEBUG ) { echo "DEBUG: Cookie Found!\n"; }
	return 1;
}

function islogin_cookietest() {
	GLOBAL $db, $MYSQL_PREFIX;
        $checkname = $_SESSION['tdtracuser'];
	$checkpass = $_SESSION['tdtracpass'];

	$sql = "SELECT password FROM {$MYSQL_PREFIX}users WHERE username = '{$checkname}' LIMIT 1";
        $result = mysql_query($sql, $db);

	$row = mysql_fetch_array($result);
	mysql_free_result($result);
	if ( md5("havesomesalt".$row['password']) == $checkpass ) { return 1; }
	return 0;
}


function islogin_form() {
        GLOBAL $TDTRAC_SITE;
	$html  = "<div id=\"loginform\"><form method=\"post\" action=\"{$TDTRAC_SITE}login\">";
	$html .= '<div style="text-align: right">User Name: <input type="text" size="20" name="tracuser" /></div>';
	$html .= '<div style="text-align: right">Password: <input type="password" size="20" name="tracpass" /></div>';
	$html .= '<div style="text-align: right"><input type="submit" value="Login" /></div></form>';
	$html .= "<div style=\"text-align: right\">[-<a href=\"{$TDTRAC_SITE}pwremind\">Forgot Password?</a>-]</div></div>";
	return $html;
}

function islogin_pwform() {
        GLOBAL $TDTRAC_SITE;
	$html  = '<h2>Send Password Via E-Mail</h2>';
        $html .= "<div id=\"loginform\"><form method=\"post\" action=\"{$TDTRAC_SITE}pwremind\">";
        $html .= '<div style="text-align: right">E-Mail Address: <input type="text" size="20" name="tracemail" /></div>';
        $html .= '<div style="text-align: right"><input type="submit" value="Send Reminder" /></div></form></div>';
        return $html;
}

function islogin_logout() {
	unset($_SESSION['tdtracuser']);
	unset($_SESSION['tdtracpass']);
	thrower("User Logged Out");
}

function islogin_dologin() {
	GLOBAL $db, $MYSQL_PREFIX, $TDTRAC_SITE, $TDTRAC_DBVER;
	$checkname = $_REQUEST['tracuser'];
	$checkpass = $_REQUEST['tracpass'];

	$sql = "SELECT userid, password, active, chpass FROM {$MYSQL_PREFIX}users WHERE username = '{$checkname}' LIMIT 1";
        $result = mysql_query($sql, $db);

	$row = mysql_fetch_array($result);
	if ( $row['active'] == 0 ) { thrower("User Account is Locked!"); }
	if ( $row['password'] == $checkpass ) { 
		$infodata = "Login Successful";
		$_SESSION['tdtracuser'] = $checkname;
		$_SESSION['tdtracpass'] = md5("havesomesalt".$checkpass);
		if ( $row['userid'] == 1 ) { //CHECK UPGRADE STATUS ON ADMIN LOGIN (USER #1)
			$sql2 = "SELECT value FROM {$MYSQL_PREFIX}tdtrac WHERE name = 'version' AND value = '{$TDTRAC_DBVER}'";
			$res2 = mysql_query($sql2, $db);
			if ( mysql_num_rows($res2) < 1 ) { $infodata .= "<br><strong>WARNING:</strong> Database not up-to-date, please run upgrade script!"; }
		}
    		if ( $row['chpass'] <> 0 ) { 
			$infodata = "Login Successful, Please Change Your Password!"; header("Location: {$TDTRAC_SITE}change-pass"); ob_flush();
		} 
	}
	else {
		$infodata = "Login Failed!";
	}

	thrower($infodata);
}

