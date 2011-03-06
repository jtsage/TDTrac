<?php
/**
 * TDTrac User Functions
 * 
 * Functions that pertain to the logged in user
 * @package tdtrac
 * @version 2.0.0
 * @since 2.0.0
 * @author J.T.Sage <jtsage@gmail.com>
 */
 
/**
 * TDTrac User Object
 * 
 * Contains all login and user related functions. 
 * Data hardened
 * @package tdtrac
 * @version 2.0.0
 * @since 2.0.0
 * @author J.T.Sage <jtsage@gmail.com>
 */
class tdtrac_user {
	
	/** @var integer User ID */
	public $id = null;
	/** @var string User's Fullname */
	public $name = null;
	/** @var string User login name */
	public $username = null;
	/** @var string User's E-mail address */
	public $email = null;
	/** @var string User's group (name) */
	public $group = null;
	/** @var bool True if logged in */
	public $loggedin = false;
	/** @var bool True if an administrator */
	public $admin = false;
	/** @var bool True if limited to adding own hours */
	public $isemp = false;
	/** @var bool True if on payroll */
	public $onpayroll = false;
	
	
	/**
	 * Open a new user element
	 * 
	 * Checks for login via session info
	 * @return void
	 */
	public function __construct() {
		if ( !$this->cookieexist() ) { 
			$this->loggedin = false;
		} else {
			if ( !$this->cookietest() ) { 
				$this->loggedin = false;
			} else { 
				$this->loggedin = true;
				$this->load($_SESSION['tdtracuser']);
			}
		}
	}
	
	/**
	 * Load user detail from database
	 * 
	 * @param string User login name
	 * @global object DB Resource
	 * @global string MySQL Prefix
	 * @return void
	 */
	private function load($username) {
		GLOBAL $db, $MYSQL_PREFIX;
		$sql = sprintf("SELECT payroll, limithours, u.userid, CONCAT(first, ' ', last) as name, u.email, groupname, ug.groupid as gid FROM `{$MYSQL_PREFIX}groupnames` gn, `{$MYSQL_PREFIX}usergroups` ug, `{$MYSQL_PREFIX}users` u WHERE username = '%s' AND u.userid = ug.userid AND ug.groupid = gn.groupid",
			mysql_real_escape_string($username)
		);
		$result = mysql_query($sql, $db);
		$row = mysql_fetch_array($result);
		$this->username = $username;
		$this->onpayroll = $row['payroll'];
		$this->name = $row['name'];
		$this->id = $row['userid'];
		$this->email = $row['email'];
		$this->group = $row['groupname'];
		$this->isemp = ($row['limithours']) ? true : false;
		if ( $row['gid'] == 1 ) { $this->admin = true; }
	}
	
	
	/** 
	 * Check if a user cookie exists
	 * 
	 * @global bool Enable login debug controls
	 * @global string MySQL Table Prefix
	 * @return bool Existence of cookie
	 */
	private function cookieexist() {
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
	private function cookietest() {
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
	 * Show the login form
	 * 
	 * @global string TDTrac Base URL HREF
	 * @return array Formatted HTML
	 */
	public function login_form() {
		GLOBAL $TDTRAC_SITE;
		setcookie("loginredirect", $_REQUEST['action'], time()+600, "/");
		$form = new tdform(array('action' => "{$TDTRAC_SITE}user/login/", 'id' => "loginform"));
	
		$result = $form->addText(array('name' => 'tracuser', 'label' => 'User Name'));
		$result = $form->addPass(array('name' => 'tracpass', 'label' => 'Password'));
	
		return array_merge($form->output('Login'), array("<a data-role=\"button\" data-theme=\"c\" href=\"{$TDTRAC_SITE}user/forgot/\">Forgot Password?</a>"));
	}
	
	/** 
	 * Show Password Reminder Form
	 * 
	 * @global string Address for links.
	 * @return array HTML output
	 */
	public function password_form() {
		GLOBAL $TDTRAC_SITE;
		$form = new tdform("{$TDTRAC_SITE}user/forgot/", "loginform", 1, "loginform", 'Send Password Reminder');
		
		$result = $form->addText('tracemail', 'E-Mail Address');
		
		return $form->output('Send Reminder');
	}
	
	/**
	 * Return if a user has the named permission
	 * 
	 * @global object Database Link
	 * @global string MySQL Table Prefix
	 * @param string Permission Name
	 * @return bool Action is allowed
	 */
	public function can($permission) {
		GLOBAL $db, $MYSQL_PREFIX;
		$sql = "SELECT `permcan` FROM `{$MYSQL_PREFIX}permissions` pm, `{$MYSQL_PREFIX}usergroups` ug, `{$MYSQL_PREFIX}users` u WHERE u.userid = {$this->id} AND u.userid = ug.userid AND ug.groupid = pm.groupid AND pm.permid = '{$permission}'";
		$result = mysql_query($sql, $db);
		//die(mysql_error());
		if ( mysql_num_rows($result) < 1 ) { return false; }
		while ( $row = mysql_fetch_array($result)) {
			if ( $row['permcan'] ) { return true; }
		}
		return false;
	}
	
	/**
	 * Log a User Out
	 * 
	 * @return void
	 */
	public function logout() {
		unset($_SESSION['tdtracuser']);
		unset($_SESSION['tdtracpass']);
	}
	
	/**
	 * Log a user in
	 * 
	 * @global object Database Link
	 * @global string MySQL Table Prefix
	 * @global string Site address for links
	 * @global string Database version string
	 * @return null
	 */
	public function login() {
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
			$json['msg'] = "Login Successful<br />Last Login: {$row['lastlog']}";
			$json['success'] = true;
			$_SESSION['tdtracuser'] = $checkname;
			$_SESSION['tdtracpass'] = md5("havesomesalt".$checkpass);
			$setlastloginsql = "UPDATE {$MYSQL_PREFIX}users SET lastlogin = CURRENT_TIMESTAMP WHERE userid = {$row['userid']}";
			$setlastloginres = mysql_query($setlastloginsql, $db);
			if ( $row['userid'] == 1 ) { //CHECK UPGRADE STATUS ON ADMIN LOGIN (USER #1)
				$sql2 = "SELECT value FROM {$MYSQL_PREFIX}tdtrac WHERE name = 'version' AND value = '{$TDTRAC_DBVER}'";
				$res2 = mysql_query($sql2, $db);
				if ( mysql_num_rows($res2) < 1 ) { $json['msg'] .= "<br><strong>WARNING:</strong> Database not up-to-date, please run upgrade"; }
			}
	    		if ( $row['chpass'] <> 0 ) { 
				$json['msg'] .= "Login Successful, Please Change Your Password!";
			} 
		}
		else {
			$json['msg'] = "Login Failed!";
			$json['success'] = false;
		}
		if ( isset($_COOKIE['loginredirect']) ) {
			$json['location'] = $_COOKIE['loginredirect'];
		} else {
			$json['location'] = "/";
		}
		return json_encode($json);
	}

	/**
	 * Show change password form
	 * 
	 * @global string Site Address for links
	 * @return array HTML output
	 */
	public function changepass_form() {
		GLOBAL $TDTRAC_SITE;
		$form = new tdform("{$TDTRAC_SITE}user/password/", 'genform', 1, 'genform', 'Change Password');
		$result = $form->addPass('newpass1', "New Password");
		$result = $form->addPass('newpass2', "Verify Password");
		return $form->output('Change Password');
	}
	
	/**
	 * Logic to change password in database
	 * 
	 * @global object Database Link
	 * @global string User Name
	 * @global string MySQL Table Prefix
	 * @return void
	 */
	public function changepass() {
		GLOBAL $db, $MYSQL_PREFIX;
		if ( $_REQUEST['newpass1'] == $_REQUEST['newpass2'] ) {
			if ( strlen($_REQUEST['newpass1']) < 4 ) { thrower("Password must be at least 5 characters"); }
			if ( strlen($_REQUEST['newpass1']) > 15 ) { thrower("Password may not exceed 15 characters"); }
			$sql = sprintf("UPDATE `{$MYSQL_PREFIX}users` SET `chpass` = 0 , `password` = '%s' WHERE `userid` = %d LIMIT 1",
				mysql_real_escape_string($_REQUEST['newpass1']),
				$this->id
			);
			$result = mysql_query($sql, $db);
			if ( $result ) { thrower("Password Changed - Please Re-Login"); }
			else { thrower("Password Change Failed"); }
		} else { thrower("Password Mismatch - Not Changed"); }
	}
	
	/**
	 * Return a full name from a userid
	 * 
	 * @global object Database Link
	 * @global string MySQL Table Prefix
	 * @param integer User ID
	 * @return string User First Name
	 */
	public function get_name($userid) {
	        GLOBAL $db, $MYSQL_PREFIX;
	        $sql = "SELECT CONCAT(first, ' ', last) as name FROM `{$MYSQL_PREFIX}users` WHERE userid = ".intval($userid);
	        $result = mysql_query($sql, $db);
	        $row = mysql_fetch_array($result);
	        return $row['name'];
	}
	
	/**
	 * Return a group name from a groupid
	 * 
	 * @global object Database Link
	 * @global string MySQL Table Prefix
	 * @param integer User ID
	 * @return string User First Name
	 */
	public function get_group($gid) {
	        GLOBAL $db, $MYSQL_PREFIX;
	        $sql = "SELECT groupname as name FROM `{$MYSQL_PREFIX}groupnames` WHERE groupid = ".intval($gid);
	        $result = mysql_query($sql, $db);
	        $row = mysql_fetch_array($result);
	        return $row['name'];
	}
}



/**
 * Send password reminder via email
 * 
 * @global object Database connection
 * @global string MySQL Table Prefix
 * @return void
 */
function email_pwsend() {
	GLOBAL $db, $MYSQL_PREFIX;
	if ( !($_REQUEST["tracemail"]) || $_REQUEST["tracemail"] == "" ) { 
		thrower("E-Mail Address Invalid");
	} else {
		$sql = "SELECT username, password FROM {$MYSQL_PREFIX}users WHERE email = '".mysql_real_escape_string($_REQUEST["tracemail"])."'";
		$result = mysql_query($sql, $db);
		if ( mysql_num_rows($result) == 0 ) { thrower("E-Mail Address Invalid"); }
		else {
			$body = "TDTrac Password Reminder:<br /><br />\n";
			while ( $row = mysql_fetch_array($result) ) {
				$body .= "Username: {$row['username']}<br />\n";
				$body .= "Password: {$row['password']}<br /><br />\n";
			}
			$body .= "Note: For security pusposes, you should change this password when you first log in!<br />\n";
			$subject = "TDTrac Password Reminder";
			$headers  = 'MIME-Version: 1.0' . "\r\n";
			$headers .= 'Content-type: text/html; charset=iso-8859-1' . "\r\n";
			$sendto = $_REQUEST['tracemail'];
			mail($sendto, $subject, $body, $headers);
		}
	}
	thrower("Password Reminder Sent!");
}
?>
