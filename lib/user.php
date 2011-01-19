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
	/** @var bool True if on list of payable employees */
	public $isemp = false;
	
	/**
	 * Open a new user element
	 * 
	 * Checks for login via session info
	 * @return null
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
	 * @return null
	 */
	private function load($username) {
		GLOBAL $db, $MYSQL_PREFIX;
		$sql = sprintf("SELECT limithours, u.userid, CONCAT(first, ' ', last) as name, u.email, groupname, ug.groupid as gid FROM `{$MYSQL_PREFIX}groupnames` gn, `{$MYSQL_PREFIX}usergroups` ug, `{$MYSQL_PREFIX}users` u WHERE username = '%s' AND u.userid = ug.userid AND ug.groupid = gn.groupid",
			mysql_real_escape_string($username)
		);
		$result = mysql_query($sql, $db);
		$row = mysql_fetch_array($result);
		$this->username = $username;
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
		$form = new tdform("{$TDTRAC_SITE}user/login/", "loginform", 1, "loginform", 'Login');
	
		$result = $form->addText('tracuser', 'User Name');
		$result = $form->addPass('tracpass', 'Password');
	
		return $form->output('Login', "[-<a href=\"{$TDTRAC_SITE}user/forgot/\">Forgot Password?</a>-] ");
	}
	
	/** 
	 * Show Password Reminder Form
	 * 
	 * @global string Address for links.
	 * @return string HTML output
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
	 * @return null
	 */
	public function logout() {
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
		if ( isset($_COOKIE['loginredirect']) ) {
			thrower($infodata, $_COOKIE['loginredirect']);
		} else {
			thrower($infodata);
		}
	}

	/**
	 * Show change password form
	 * 
	 * @global string Site Address for links
	 * @return string HTML output
	 */
	public function changepass_form() {
		GLOBAL $TDTRAC_SITE;
		$form = new tdform("{$TDTRAC_SITE}/user/password/", 'genform', 1, 'genform', 'Change Password');
		$result = $form->addPass('newpass1', "New Password");
		$result = $form->addPass('newpass2', "Verify Password");
		return $form->output('Change Password');
	}
	
	/**
	 * Logic to change password in database
	 * 
	 * @global resource Database Link
	 * @global string User Name
	 * @global string MySQL Table Prefix
	 * @return null
	 */
	public function changepass() {
		GLOBAL $db, $user_name, $MYSQL_PREFIX;
		if ( $_REQUEST['newpass1'] == $_REQUEST['newpass2'] ) {
			if ( strlen($_REQUEST['newpass1']) < 4 ) { thrower("Password must be at least 5 characters"); }
			if ( strlen($_REQUEST['newpass1']) > 15 ) { thrower("Password may not exceed 15 characters"); }
			$sql = sprintf("UPDATE `{$MYSQL_PREFIX}users` SET `chpass` = 0 , `password` = '%d' WHERE `username` = '{$user_name}' LIMIT 1",
				mysql_real_escape_string($_REQUEST['newpass1'])
			);
			$result = mysql_query($sql, $db);
			thrower("Password Changed - Please Re-Login");
		} else { thrower("Password Mismatch - Not Changed"); }
	}
	
	/**
	 * Return a first name from a userid
	 * 
	 * @global resource Database Link
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
}

?>
