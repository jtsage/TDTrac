<?php
/**
 * TDTrac Admin Control Functions
 * 
 * Contains all access control framework
 * Data hardened
 * @package tdtrac
 * @version 2.0.0
 * @author J.T.Sage <jtsage@gmail.com>
 */


/**
 * ADMIN Module
 *  Allows configuration of users, groups and permissions
 * 
 * @package tdtrac
 * @version 2.0.0
 * @since 2.0.0
 * @author J.T.Sage <jtsage@gmail.com>
 */
class tdtrac_admin {
	
	/** @var array Parsed query string */
	private $action = array();
	
	/** @var bool Output format (TURE = json, FALSE = html) */
	private $output_json = false;
	
	/** @var array Formatted HTML */
	private $html = array();
	
	/** @var string Page Title */
	private $title = "Shows";
	
	/** @var array JSON Data */
	private $json = array();
	
	/** @var array Available Permissions */
	private $perms_avail = array("addshow", "editshow", "viewshow", "addbudget", "editbudget", "viewbudget", "addhours", "edithours", "viewhours", "addtodo", "edittodo", "viewtodo");
	
	/** 
	 * Create a new instance of the TO-DO module
	 * 
	 * @param object User object
	 * @param array Parsed query string
	 * @return object Admin Object
	 */
	public function __construct($user, $action = null) {
		$this->post = ($_SERVER['REQUEST_METHOD'] == "POST") ? true : false;
		$this->user = $user;
		$this->action = $action;
		$this->output_json = $action['json'];
	}
	
	/**
	 * Output todo list operation
	 * 
	 * @return void
	 */
	public function output() {
		if ( !$this->output_json ) { // HTML METHODS
			if ( !$this->user->admin ) { thrower('Access Denied :: You are not an administrator'); }
			switch ( $this->action['action'] ) {
				case "users": // View Users
					$this->title .= " :: View Users";
					$this->html = $this->user_view();
					break;
				case "useradd": // Add User
					$this->title .= " :: Add User";
					if ( $this->post ) {
						thrower($this->user_save(false), 'admin/users/');
					} else {
						$this->html = $this->user_add_form();
					} break;
				case "useredit": // Edit User
					$this->title .= " :: Edit User";
					if ( $this->post ) {
						if ( isset($_REQUEST['id']) && is_numeric($_REQUEST['id']) ) {
							thrower($this->user_save(true), "admin/users/");
						} else {
							thrower('Error :: Data Mismatch Detected', 'admin/users/');
						}
					} else {
						if ( isset($this->action['id']) && is_numeric($this->action['id']) ) {
							$this->html = $this->user_edit_form(intval($this->action['id']));
						} else {
							thrower("Error :: Data Mismatch Detected", 'admin/users/');
						}
					} break;
				case "perms":
					$this->title .= " :: View Permissions";
					$this->html = $this->perms_view();
					break;
				case "permsedit":
					$this->title .= " :: Edit Permissions";
					if ( $this->post ) {
						if ( isset($_REQUEST['id']) && is_numeric($_REQUEST['id']) ) {
							thrower($this->perms_save(intval($_REQUEST['id'])), "admin/perms/");
						} else {
							thrower('Error :: Data Mismatch Detected', 'admin/perms/');
						}
					} else {
						if ( isset($this->action['id']) && is_numeric($this->action['id']) ) {
							$this->html = $this->perms_edit_form(intval($this->action['id']));
						} else {
							thrower("Error :: Data Mismatch Detected", 'admin/perms/');
						}
					} break;
				case "mail":
					$this->title .= " :: TDTracMail Configuration";
					if ( $this->post ) {
						if ( !empty($_REQUEST['code']) && !empty($_REQUEST['email']) ) {
							thrower($this->mailcode_save(), "admin/");
						} else {
							thrower('Error :: Data Mismatch Detected', 'admin/');
						}
					} else {
						$this->html = $this->mailcode_form();
					} break;
				case "groups":
					$this->title .= " :: Group Management";
					if ( $this->post ) {
						if ( !empty($_REQUEST['newname']) && is_numeric($_REQUEST['oldname']) ) {
							if ( intval($_REQUEST['oldname']) == 1 ) {
								thrower('Error :: Cannot Rename Admin Group', 'admin/groups/');
							} else {
								thrower($this->group_rename(), 'admin/groups/');
							}
						} elseif ( !empty($_REQUEST['newgroup']) ) {
							thrower($this->group_add(), 'admin/groups/');
						} else {
							thrower('Error :: Data Mismatch Detected', 'admin/groups/');
						}
					} else {
						$this->html = $this->group_forms();
					} break;
				default:
					$this->html = $this->index();
					break;
			}
			makePage($this->html, $this->title);
		} else {
			switch($this->action['action']) {
				case "payroll":
					if ( isset($this->action['id']) && is_numeric($this->action['id']) && isset($this->action['value']) && is_numeric($this->action['value'])) {
						$this->user_payroll(intval($this->action['id']));
					} else {
						$this->json['success'] = false;
					} break;
				case "limithours":
					if ( isset($this->action['id']) && is_numeric($this->action['id']) && isset($this->action['value']) && is_numeric($this->action['value'])) {
						$this->user_limit(intval($this->action['id']));
					} else {
						$this->json['success'] = false;
					} break;
				case "notify":
					if ( isset($this->action['id']) && is_numeric($this->action['id']) && isset($this->action['value']) && is_numeric($this->action['value'])) {
						$this->user_notify(intval($this->action['id']));
					} else {
						$this->json['success'] = false;
					} break;
				case "active":
					if ( isset($this->action['id']) && is_numeric($this->action['id']) && isset($this->action['value']) && is_numeric($this->action['value'])) {
						$this->user_active(intval($this->action['id']));
					} else {
						$this->json['success'] = false;
					} break;
				default:
					$this->json['success'] = false;
					break;
			} echo json_encode($this->json);
		} 
	} // END OUTPUT FUNCTION
	
	/**
	 * Mark a user as notified of new hours (or not)
	 * 
	 * @param integer User ID
	 * @global object DB Resource
	 * @global string MySQL Prefix
	 * @return void
	 */
	 private function user_notify($id) {
		GLOBAL $db, $MYSQL_PREFIX;
		$sql  = "UPDATE {$MYSQL_PREFIX}users SET notify = ".intval($this->action['value'])." WHERE userid = '".intval($id)."'";
		$result = mysql_query($sql, $db);
		if ( $result ) {
			$this->json['success'] = true;
		} else {
			$this->json['success'] = false;
		}
	}
	
	/**
	 * Mark a user on payroll (or not)
	 * 
	 * @param integer User ID
	 * @global object DB Resource
	 * @global string MySQL Prefix
	 * @return void
	 */
	 private function user_payroll($id) {
		GLOBAL $db, $MYSQL_PREFIX;
		$sql  = "UPDATE {$MYSQL_PREFIX}users SET payroll = ".intval($this->action['value'])." WHERE userid = '".intval($id)."'";
		$result = mysql_query($sql, $db);
		if ( $result ) {
			$this->json['success'] = true;
		} else {
			$this->json['success'] = false;
		}
	}
	
	/**
	 * Mark a user limited (or not)
	 * 
	 * @param integer User ID
	 * @global object DB Resource
	 * @global string MySQL Prefix
	 * @return void
	 */
	 private function user_limit($id) {
		GLOBAL $db, $MYSQL_PREFIX;
		$sql  = "UPDATE {$MYSQL_PREFIX}users SET limithours = ".intval($this->action['value'])." WHERE userid = '".intval($id)."'";
		$result = mysql_query($sql, $db);
		if ( $result ) {
			$this->json['success'] = true;
		} else {
			$this->json['success'] = false;
		}
	}

	/**
	 * Mark a user active (or inactive)
	 * 
	 * @param integer User ID
	 * @global object DB Resource
	 * @global string MySQL Prefix
	 * @return void
	 */
	 private function user_active($id) {
		GLOBAL $db, $MYSQL_PREFIX;
		$sql  = "UPDATE {$MYSQL_PREFIX}users SET active = ".intval($this->action['value'])." WHERE userid = '".intval($id)."'";
		$result = mysql_query($sql, $db);
		if ( $result ) {
			$this->json['success'] = true;
		} else {
			$this->json['success'] = false;
		}
	}
	
	/** 
	 * Show available ToDo Functions
	 * 
	 * @global string TDTrac Root Link HREF
	 * @return array Formatted HTML
	 */
	public function index() {
		global $TDTRAC_SITE;
		if ( !$this->user->admin ) { return array('',''); }
		$html[] = "<ul class=\"linklist\"><li><h3>Administrative Tasks</h3><ul class=\"linklist\">";
		$html[] = "  <li>Manage users, groups and permissions.</li>";
		$html[] = "  <li><a href=\"{$TDTRAC_SITE}admin/useradd/\">Add User</a></li>";
		$html[] = "  <li><a href=\"{$TDTRAC_SITE}admin/users/\">View Users</a></li>";
		$html[] = "  <li><a href=\"{$TDTRAC_SITE}admin/groups/\">Add / Edit Groups</a></li>"; 
		$html[] = "  <li><a href=\"{$TDTRAC_SITE}admin/perms/\">View Permissions</a></li>";
		$html[] = "  <li><a href=\"{$TDTRAC_SITE}admin/mail/\">Set TDTracMail Subject Code</a></li></ul></li></ul>";
		return $html;
	}

	/**
	 * Return a list of groups
	 * 
	 * @global object Database Link
	 * @global string MySQL Table Prefix
	 * @param integer UserID
	 * @return array List of groups
	 */
	private function groups_by_user($id) {
		GLOBAL $db, $MYSQL_PREFIX;
		$sql = sprintf("SELECT groupname FROM `{$MYSQL_PREFIX}groupnames` gn, `{$MYSQL_PREFIX}usergroups` ug, `{$MYSQL_PREFIX}users` u WHERE u.userid = %d AND u.userid = ug.userid AND ug.groupid = gn.groupid",
			intval($id)
		);
		$result = mysql_query($sql, $db);
		while ( $row = mysql_fetch_array($result) ) {
		   $retty[] = $row['groupname'];
		}
		return $retty;
	}
	
	/**
	 * View all permissions
	 * 
	 * @global object Database Link
	 * @global array Name of all known permissions
	 * @global string MySQL Table Prefix
	 * @return array HTML output
	 */
	private function perms_view() {
		GLOBAL $db, $TDTRAC_PERMS, $MYSQL_PREFIX;
		
		$perm_sql = "SELECT groupname, permid FROM `{$MYSQL_PREFIX}groupnames` gn, `{$MYSQL_PREFIX}permissions` pm WHERE pm.groupid = gn.groupid AND pm.permcan = 1 ORDER BY groupname, permid";
		$perm_res = mysql_query($perm_sql, $db);
		while ( $row = mysql_fetch_array($perm_res) ) {
			$disperm[$row['groupname']][$row['permid']] = true;
		}
		
		$grop_sql = "SELECT groupname as name, groupid as id FROM `{$MYSQL_PREFIX}groupnames` ORDER BY groupid";
		$grop_res = mysql_query($grop_sql, $db);
		
		$html[] = "<h3>Permissions</h3>";
		$html[] = "<div style=\"font-size: .75em;\"><div style=\"width: 49%; float: left\">".
			"<dl><dt>addshow</dt><dd>Can Add New Shows / Jobs</dd>" .
			"<dt>viewshow</dt><dd>Can View Current and Past Shows / Jobs</dd>" .
			"<dt>editshow</dt><dd>Can Edit Shows / Jobs information</dd>" .
			"<dt>addbudget</dt><dd>Can Add Expenses</dd>" .
			"<dt>editbudget</dt><dd>Can Edit or Delete Expenses Information</dd>" .
			"<dt>viewbudget</dt><dd>Can View budget details, including the labor cost overview</dd></dl></div>";
		$html[] = "<div style=\"width: 49%; float: right\">".
			"<dl><dt>addhours</dt><dd>Can Add Hours for employees on payroll.</dd>" .
			"<dt>edithours</dt><dd>Can Edit or Delete Hours for employees on payroll.</dd>" .
			"<dt>viewhours</dt><dd>Can View Labor reports.</dd>" .
			"<dt>addtodo</dt><dd>Can Add ToDo List Items</dd>".
			"<dt>edittodo</dt><dd>Can Edit ToDo List Items</dd>".
			"<dt>viewtodo</dt><dd>Can View Todo Lists other than their personal list</dd></dl></div></div>";
		$html[] = "<div style=\"clear: both\"><br /></div>";
		
		
		$tabl = new tdtable("perms", 'permtable', true);
		$perm = array('A', 'E', 'V');
		$tabl->addRaw('<tr><th colspan="2"></th><th colspan="3">Show</th><th colspan="3">Budget</th><th colspan="3">Hours</th><th colspan="3">Todo</th><th></th></tr>');
		$tabl->addHeader(array_merge(array('Group', 'Members'), $perm, $perm, $perm, $perm));
		$tabl->addAction(array('pmedit',));
		
		foreach ( $perm as $cph ) {
			$tabl->setAlign($cph, 'center');
		}
		
		while( $row = mysql_fetch_array($grop_res) ) {
			$members  = array();
			$setperms = array();
			$sql = "SELECT u.username FROM `{$MYSQL_PREFIX}users` u, `{$MYSQL_PREFIX}groupnames` gn, `{$MYSQL_PREFIX}usergroups` ug WHERE gn.groupname = '{$row['name']}' AND gn.groupid = ug.groupid AND ug.userid = u.userid ORDER BY username ASC";
			$result = mysql_query($sql, $db);
			if ( mysql_num_rows($result) < 1 ) { 
				$members[] = "<em>N/A</em>";
			} else {
				while ( $mrow = mysql_fetch_array($result) ) {
					$members[] = $mrow['username'];
				}
			}
			foreach ( $this->perms_avail as $cp ) {
				if ( $disperm[$row['name']][$cp] ) { 
					$setperms[] = "<img class=\"ticon\" src=\"/images/perm-ya.png\" title=\"{$row['name']}::{$cp}::True\" />";
				} else {
					$setperms[] = "<img class=\"ticon\" src=\"/images/perm-no.png\" title=\"{$row['name']}::{$cp}::False\" />";
				}
			}
			
			$rowarray = array_merge(array( $row['name'], join('<br />', $members) ), $setperms);
			
			$tabl->addRow($rowarray, $row);
		}
		return array_merge($html, $tabl->output(false));
	}
	
	/**
	 * Show permission edit form
	 *
	 * @param integer ID of group to edit 
	 * @global object Database Link
	 * @global string MySQL Table Prefix
	 * @global string Site Address for links
	 * @return array HTML output
	 */
	private function perms_edit_form($id) {
		GLOBAL $db, $MYSQL_PREFIX, $TDTRAC_SITE;
		
		$form = new tdform("{$TDTRAC_SITE}admin/permsedit/{$id}", 'genform', 1, 'genform', "Edit :: ".$this->user->get_group($id));
	
		$fesult = $form->addInfo("T / F");
		$fesult = $form->addHidden('id', $id);
		$sql = "SELECT permid, permcan FROM {$MYSQL_PREFIX}permissions pm WHERE groupid = {$id}";
		$result = mysql_query($sql, $db);
		while ( $row = mysql_fetch_array($result) ) {
			$pname = $row['permid']; $pvalue = $row['permcan'];
			$dbperm[$pname] = $pvalue;
		}
		foreach ( $this->perms_avail as $perm ) {
			$fesult = $form->addRadio($perm, $perm, null, $dbperm[$perm]);
		}	
		return $form->output('Save');
	}

	/**
	 * Save permissions to database
	 * 
	 * @global object Database Link
	 * @global string MySQL Table Prefix
	 * @param integer Group ID to save
	 * @return string Success / Failure Message
	 */
	private function perms_save($grpid) {
		GLOBAL $db, $MYSQL_PREFIX;
		if ( !is_numeric($grpid) ) { thrower("Oops :: Operation Failed"); }
		$sql = "DELETE FROM `{$MYSQL_PREFIX}permissions` WHERE groupid = ".intval($grpid);
		$result = mysql_query($sql, $db);
		foreach ( $this->perms_avail as $perm ) {
			$sql = sprintf("INSERT INTO `{$MYSQL_PREFIX}permissions` (groupid, permid, permcan) VALUES (%d, '%s', %d)",
				intval($grpid),
				$perm,
				(($_REQUEST[$perm]) ? "1" : "0")
			);
			mysql_query($sql, $db);
		}
		return "Permissions for ".$this->user->get_group($grpid)." Updated";
	}

	/**
	 * Show add user form
	 * 
	 * @global object Database Link
	 * @global string MySQL Table Prefix
	 * @global string Site Address for links
	 * @return array HTML output
	 */
	private function user_add_form() {
		GLOBAL $db, $MYSQL_PREFIX, $TDTRAC_SITE;
		$form = new tdform("{$TDTRAC_SITE}admin/useradd/", 'add-user-form', 1, 'genform', 'Add User');
		
		$result = $form->addText('username', "User Name");
		$result = $form->addText('password', "Password");
		$result = $form->addText('first', "First Name");
		$result = $form->addText('last', "Last Name");
		$result = $form->addText('phone', "Phone");
		$result = $form->addText('email', "E-Mail");
		$result = $form->addDrop('groupid', "Group", null, db_list("SELECT groupname, groupid FROM {$MYSQL_PREFIX}groupnames ORDER BY groupid DESC;", array('groupid', 'groupname')), False);
		
		return $form->output('Add User');
	}

	/**
	 * Show edit user form
	 * 
	 * @global object Database Link
	 * @global string MySQL Table Prefix
	 * @global string Site Address for links
	 * @param integer User ID to edit
	 * @return array HTML output
	 */
	private function user_edit_form($id) {
		GLOBAL $db, $MYSQL_PREFIX, $TDTRAC_SITE;
		$sql = "SELECT u.*, groupid FROM `{$MYSQL_PREFIX}users` u, `{$MYSQL_PREFIX}usergroups` ug WHERE u.userid = ug.userid AND u.userid = ".intval($id)." LIMIT 1";
		$result = mysql_query($sql, $db);
		$row = mysql_fetch_array($result);
		$form = new tdform("{$TDTRAC_SITE}admin/useredit/{$id}", 'edit-user-form', 1, 'genform', 'Edit User');
		
		$fesult = $form->addText('username', "User Name", null, $row['username']);
		$fesult = $form->addText('password', "Password", null, $row['password']);
		$fesult = $form->addText('payrate', "Pay Rate", null, $row['payrate']);
		$fesult = $form->addText('first', "First Name", null, $row['first']);
		$fesult = $form->addText('last', "Last Name", null, $row['last']);
		$fesult = $form->addText('phone', "Phone", null, $row['phone']);
		$fesult = $form->addText('email', "E-Mail", null, $row['email']);
		$fesult = $form->addDrop('groupid', "Group", null, db_list("SELECT groupname, groupid FROM {$MYSQL_PREFIX}groupnames ORDER BY groupid DESC;", array('groupid', 'groupname')), False, $row['groupid']);
		$fesult = $form->addHidden('id', $id);
		
		return $form->output('Save User');
	}

	/**
	 * Logic to save user to database
	 * 
	 * @param bool True if editing, False for new
	 * @global object Database Link
	 * @global string MySQL Table Prefix
	 * @global double Default Payrate
	 * @return string Success or Failure
	 */
	private function user_save($exists = false) {
		GLOBAL $db, $MYSQL_PREFIX, $TDTRAC_PAYRATE;
		
		if ( !$exists ) {
			$sqlstring  = "INSERT INTO `{$MYSQL_PREFIX}users` ( `username`, `first`, `last`, `password`, `phone`, `email`, `payrate` )";
			$sqlstring .= " VALUES ( '%s', '%s', '%s', '%s', '%d', '%s', '%f' )";
		
			$sql = sprintf($sqlstring,
				mysql_real_escape_string($_REQUEST['username']),
				mysql_real_escape_string($_REQUEST['first']),
				mysql_real_escape_string($_REQUEST['last']),
				mysql_real_escape_string($_REQUEST['password']),
				intval($_REQUEST['phone']),
				mysql_real_escape_string($_REQUEST['email']),
				$TDTRAC_PAYRATE
			);
		} else {
			$sqlstring  = "UPDATE `{$MYSQL_PREFIX}users` SET `password` = '%s', `username` = '%s', `last` = '%s', `first` = '%s',";
			$sqlstring .= " `phone` = '%d', `email` = '%s', `payrate` = '%f'  WHERE `userid` = %d LIMIT 1";
		
			$sql = sprintf($sqlstring,
				mysql_real_escape_string($_REQUEST['password']),
				mysql_real_escape_string($_REQUEST['username']),
				mysql_real_escape_string($_REQUEST['last']),
				mysql_real_escape_string($_REQUEST['first']),
				intval($_REQUEST['phone']),
				mysql_real_escape_string($_REQUEST['email']),
				floatval($_REQUEST['payrate']),
				intval($_REQUEST['id'])
			);
		}
		
		$result = mysql_query($sql, $db);
		
		if ( !$result ) { return "User Save :: Failed to save user<br />".mysql_error(); }
		
		if ( !$exists ) {
			$sql2 = sprintf("INSERT INTO `{$MYSQL_PREFIX}usergroups` ( `userid`, `groupid` ) VALUES ( %d, %d )",
				mysql_insert_id($db),
				intval($_REQUEST['groupid'])
			);
		} else {
			$sql2  = sprintf("UPDATE `{$MYSQL_PREFIX}usergroups` SET groupid = %d WHERE userid = %d",
				intval($_REQUEST['groupid']),
				intval($id)
			);
		}
			
		$result = mysql_query($sql2, $db);
		
		if ( !$result ) {
			return "User Save :: Failed to set group<br />".mysql_error();
		} else {
			return "User Saved :: ".$_REQUEST['username'];
		}
	}

	/**
	 * View all users
	 * 
	 * @global object Database Link
	 * @global string MySQL Table Prefix
	 * @global string Site Address for links
	 * @global array JavaScript
	 * @return array HTML output
	 */
	private function user_view() {
		GLOBAL $db, $MYSQL_PREFIX, $TDTRAC_SITE, $SITE_SCRIPT;
		$sql = "SELECT *, DATE_FORMAT(lastlogin, '%b %D %h:%i %p') AS lastlog FROM `{$MYSQL_PREFIX}users` ORDER BY last ASC, first ASC";
		$result = mysql_query($sql, $db); $html = "";
		while ( $row = mysql_fetch_array($result) ) {
			
			$jqoptions['active']     = array("Active", "Inactive");
			$jqoptions['payroll']    = array("On Payroll", "Off Payroll");
			$jqoptions['notify']     = array("Notify on Hours", "No Notification");
			$jqoptions['limithours'] = array("Add only own hours", "Add any hours");
			
			foreach ( $jqoptions as $rname => $opts ) {
				$SITE_SCRIPT[] = "var u{$rname}{$row['userid']} = ".(($row[$rname])?"true":"false")."     ;";
				$SITE_SCRIPT[] = "$(function() { $('#{$rname}-{$row['userid']}').click( function() {";
				$SITE_SCRIPT[] = "	if ( u{$rname}{$row['userid']} && confirm('Mark User #{$row['userid']} {$opts[1]}?')) {";
				$SITE_SCRIPT[] = "		$.getJSON(\"{$TDTRAC_SITE}admin/{$rname}/json:1/id:{$row['userid']}/value:0\", function(data) {";
				$SITE_SCRIPT[] = "			if ( data.success === true ) { ";
				$SITE_SCRIPT[] = "				$('#popper').html(\"User #{$row['userid']} Marked {$opts[1]}\"); u{$rname}{$row['userid']} = false;";
				$SITE_SCRIPT[] = "			} else { $('#popper').html(\"User #{$row['userid']} Mark :: Failed\"); $('#{$rname}-{$row['userid']}').attr('checked', true);}";
				$SITE_SCRIPT[] = "			$('#popperdiv').show('blind');";			
				$SITE_SCRIPT[] = "	});} ";
				$SITE_SCRIPT[] = "	if ( !u{$rname}{$row['userid']} && confirm('Mark User #{$row['userid']} {$opts[0]}?')) {";
				$SITE_SCRIPT[] = "		$.getJSON(\"{$TDTRAC_SITE}admin/{$rname}/json:1/id:{$row['userid']}/value:1\", function(data) {";
				$SITE_SCRIPT[] = "			if ( data.success === true ) { ";
				$SITE_SCRIPT[] = "				$('#popper').html(\"User #{$row['userid']} Marked {$opts[0]}\"); u{$rname}{$row['userid']} = true;";
				$SITE_SCRIPT[] = "			} else { $('#popper').html(\"User #{$row['userid']} Mark :: Failed\"); $('#{$rname}-{$row['userid']}').attr('checked', false);}";
				$SITE_SCRIPT[] = "			$('#popperdiv').show('blind');";			
				$SITE_SCRIPT[] = "	});} ";
				$SITE_SCRIPT[] = "});});";
			}
			
			$html[] = "<h3>User: {$row['first']} {$row['last']}</h3>";
			$html[] = "<span class=\"overright\">[<a href=\"{$TDTRAC_SITE}admin/useredit/id:{$row['userid']}/\">Edit Detailed</a>]</span>";
			
			$html[] = "<table>";
			$html[] = "  <tr><td style=\"width: 200px\">Internal UserID</td><td><strong>{$row['userid']}</strong> (Last Login: ".((!empty($row['lastlog']))?$row['lastlog']:"Never").")</td></tr>";
			$html[] = "  <tr><td colspan=\"2\"><ul style=\"margin-left: 2em\">";
			$html[] = "    <li class=\"small\">(Active: <input id=\"active-{$row['userid']}\" type=\"checkbox\"".(($row['active'])?" checked=\"checked\" ":"").">)</li>";
			$html[] = "    <li class=\"small\">(On Payroll: <input id=\"payroll-{$row['userid']}\" type=\"checkbox\"".(($row['payroll'])?" checked=\"checked\" ":"").">)</li>";
			$html[] = "    <li class=\"small\">(Add / View / Edit only Own Hours: <input id=\"limithours-{$row['userid']}\" type=\"checkbox\"".(($row['limithours'])?" checked=\"checked\" ":"").">)</li>";
			$html[] = "    <li class=\"small\">(Notify when Employees add Payroll: <input id=\"notify-{$row['userid']}\" type=\"checkbox\"".(($row['notify'])?" checked=\"checked\" ":"").">)</li>";
			$html[] = "  </ul></td></tr>";
			$html[] = "  <tr><td>User Name</td><td><strong>{$row['username']}</strong></td></tr>";
			$html[] = "  <tr><td>Group</td><td><strong>" . join(", ", $this->groups_by_user($row['userid'])) . "</strong></td></tr>";
			if ( !is_null($row['phone']) && $row['phone'] <> "" && $row['phone'] <> 0 ) {
				$html[] = "  <tr><td>Phone Number</td><td><strong>". format_phone($row['phone']) . "</strong></td></tr>";
			}
			if ( !is_null($row['email']) && $row['email'] <> "" ) {
				$html[] = "  <tr><td>E-Mail Address</td><td><a href=\"mailto:{$row['email']}\"><strong>{$row['email']}</strong></a></td></tr>";
			}
			$html[] = "  <tr><td>Pay Rate</td><td>\$" . number_format($row['payrate'], 2) . "</td></tr>";
			$html[] = "</table><br /><br />";
		}
		$html[] = "<p><strong>Please Note:</strong> You can adjust user flags from this page.</p>";
		return $html;
	}

	/**
	 * Show group related forms
	 * 
	 * @global object Database Link
	 * @global string MySQL Table Prefix
	 * @global string Site Address for links
	 * @return array HTML output
	 */
	private function group_forms() {
		GLOBAL $db, $MYSQL_PREFIX, $TDTRAC_SITE;
		$form1 = new tdform("{$TDTRAC_SITE}admin/groups/", 'form1', 1, 'genform', 'Add Group');
		$result = $form1->addText('newgroup', "Group Name");
		$html = $form1->output("Add Group");
		
		$form2 = new tdform("{$TDTRAC_SITE}admin/groups/", 'form2', $form1->getlasttab(), 'genform2', 'Rename Group');
		$sql = "SELECT `groupname`, `groupid` FROM `{$MYSQL_PREFIX}groupnames` WHERE `groupid` > 1 ORDER BY groupid";
		$result = $form2->addDrop('oldname', "Current Name", null, db_list($sql, array('groupid', 'groupname')), False);
		$result = $form2->addText('newname', "New Name");
		$html = array_merge($html, $form2->output('Rename Group'));
		return $html;
	}


	/**
	 * Logic to add a group
	 * 
	 * @global object Database Link
	 * @global string MySQL Table Prefix
	 * @return void
	 */
	private function group_add() {
		GLOBAL $db, $MYSQL_PREFIX;
		$sql = sprintf("INSERT INTO {$MYSQL_PREFIX}groupnames (groupname) VALUES ('%s')",
			mysql_real_escape_string($_REQUEST['newgroup'])
		);
		$request = mysql_query($sql, $db);
		if ( $request ) {
			thrower("Group \"{$_REQUEST['newgroup']}\" Added");
		} else {
			thrower("Group Add :: Operation Failed");
		}
	}
	
	/**
	 * Logic to rename a group
	 * 
	 * @global object Database Link
	 * @global string MySQL Table Prefix
	 * @return void
	 */
	private function group_rename() {
		GLOBAL $db, $MYSQL_PREFIX;
		$sql = sprintf("UPDATE `{$MYSQL_PREFIX}groupnames` SET groupname = '%s' WHERE groupid = %d",
			mysql_real_escape_string($_REQUEST['newname']),
			intval($_REQUEST['oldname'])
		);
		$request = mysql_query($sql, $db);
		if ( $request ) {
			thrower("Group Renamed to \"{$_REQUEST['newname']}\"");
		} else {
			thrower("Group Update :: Operation Failed");
		}
	}
	
	/**
	 * Form for changing the mail code (tdtracmail enabled installs)
	 * 
	 * @global object Database Link
	 * @global string MySQL Table Prefix
	 * @return array HTML Output
	 */
	private function mailcode_form() {
		GLOBAL $db, $MYSQL_PREFIX;
		$sql = "SELECT * FROM `tdtracmail` WHERE prefix = '{$MYSQL_PREFIX}'";
		$result = mysql_query($sql, $db);
		$line = mysql_fetch_array($result);
		$form = new tdform("{$TDTRAC_SITE}admin/mail/", "form1", 1, 'genform', 'Set TDTracMail Code');
		
		$fes = $form->addText("email", "E-Mail Address", null, $line['email']);
		$fes = $form->addText("code", "Subject Code", null, $line['code']);
		return $form->output('Set Code');
	}
	
	/**
	 * Logic to save TDTracMail code
	 * 
	 * @global object Database Link
	 * @global string MySQL Table Prefix
	 * @return string Success or Failure
	 */
	function mailcode_save() {
		GLOBAL $db, $MYSQL_PREFIX;
		$sql = sprintf("UPDATE tdtracmail SET code = '%s', email = '%s' WHERE prefix = '{$MYSQL_PREFIX}'",
			mysql_real_escape_string($_REQUEST['code']),
			mysql_real_escape_string($_REQUEST['email'])
		);
		$result = mysql_query($sql, $db);
		if ( !$result ) { return "Code Update Failed:<br />".mysql_error(); }
		else { return "Code Updated"; }
	}
}

