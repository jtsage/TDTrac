<?php
/**
 * TDTrac Admin Control Functions
 * 
 * Contains all access control framework
 * Data hardened
 * @package tdtrac
 * @version 3.0.0
 * @author J.T.Sage <jtsage@gmail.com>
 */


/**
 * ADMIN Module
 *  Allows configuration of users, groups and permissions
 * 
 * @package tdtrac
 * @version 3.0.0
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
	private $title = "Admin";
	
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
		GLOBAL $TEST_MODE, $CANCEL;
		if ( !$this->output_json ) { // HTML METHODS
			if ( !$this->user->admin ) { $this->html = error_page('Access Denied :: You are not an administrator'); }
			switch ( $this->action['action'] ) {
				case "users": // View Users
					$this->title .= "::View Users";
					$this->html = $this->user_view();
					break;
				case "useradd": // Add User
					$this->title .= "::Add User";
					$this->html = $this->user_add_form();
					break;
				case "useredit": // Edit User
					$this->title .= "::Edit User";
					if ( isset($this->action['id']) && is_numeric($this->action['id']) ) {
						$this->html = $this->user_edit_form(intval($this->action['id']));
					} else {
						$this->html = error_page('Error :: Data Mismatch Detected');
					} break;
				case "perms":
					$this->title .= "::Permissions";
					$this->html = $this->perms_view();
					break;
				case "permsedit":
					$CANCEL = true;
					$this->title .= "::Edit Permissions";
					if ( isset($this->action['id']) && is_numeric($this->action['id']) ) {
						$this->html = $this->perms_edit_form(intval($this->action['id']));
					} else {
						$this->html = error_page('Error :: Data Mismatch Detected');
					} break;
				case "mail":
					$this->title .= "::TDTracMail Configuration";
					$this->html = $this->mailcode_form();
					break;
				case "groups":
					$this->title .= "::Group Management";
					$this->html = $this->groups();
					break;
				default:
					$this->html = $this->index();
					break;
			}
			makePage($this->html, $this->title);
		} else {
			if ( !$this->user->admin ) { 
				$this->json['success'] = false; $this->json['msg'] = "Permission Denied"; 
			} else {
				switch($this->action['action']) {
					case "saveuser":
						if ( $this->action['new'] == 0 ) {
							if ( isset($_REQUEST['id']) && is_numeric($_REQUEST['id']) ) {
								$this->json = $this->saveuser(true);
								$this->json['location'] = "/admin/users/";
							} else {
								$this->json['success'] = false;
								$this->json['msg'] = "Poorly Formed Request";
							}
						} elseif ( $this->action['new'] == 1 ) {
							$this->json = $this->saveuser(false);
							$this->json['location'] = "/admin/users/";
						} else {
							$this->json['success'] = false;
							$this->json['msg'] = "Poorly Formed Request";
						} break;
					case "remgroup":
						if ( isset($this->action['oldname']) && is_numeric($this->action['oldname']) ) {
							$this->json = $this->group_delete($this->action['oldname']);
							$this->json['location'] = "/admin/groups/";
						} else {
							$this->json['success'] = false;
							$this->json['msg'] = "Poorly Formed Request";
						} break;
					case "group":
						if ( !empty($this->action['newname']) && is_numeric($this->action['oldname']) ) {
							if ( $this->action['oldname'] ==  1 ) {
								$this->json['success'] = false;
								$this->json['msg'] = "Cannot rename admin group";
							} else {
								$this->json = $this->group_rename();
								$this->json['location'] = "/admin/groups/";
							}
						} elseif ( !empty($this->action['newname']) ) {
							$this->json = $this->group_add();
							$this->json['location'] = "/admin/groups/";
						} else {
							$this->json['success'] = false;
							$this->json['msg'] = "Poorly Formed Request";
						} break;
					case "permsave":
						if ( isset($this->action['id']) && is_numeric($this->action['id']) ) {
							$this->json = $this->perms_save(intval($this->action['id']));
							$this->json['location'] = "/admin/groups/";
						} else {
							$this->json['success'] = false;
							$this->json['msg'] = "Poorly Formed Request";
						} break;
					case "mailcode":
						if ( !empty($this->action['code']) && !empty($this->action['email']) ) {
							$this->json = $this->mailcode_save();
						} else {
							$this->json['success'] = false;
							$this->json['msg'] = "Poorly Formed Request";
						} break;
					case "toggle":
						if ( !isset($this->action['switch']) || empty($this->action['switch']) || !isset($this->action['id']) || !is_numeric($this->action['id']) ) {
							$this->json['success'] = false;
							$this->json['msg'] = "Poorly Formed Request";
						} else {
							switch($this->action['switch']) {
								case "payroll":
									$this->json = $this->user_payroll(intval($this->action['id']));
									break;
								case "limit":
									$this->json = $this->user_limit(intval($this->action['id']));
									break;
								case "notify":
									$this->json = $this->user_notify(intval($this->action['id']));
									break;
								case "active":
									$this->json = $this->user_active(intval($this->action['id']));
									break;
								default:
									$this->json['success'] = false;
									$this->json['msg'] = 'Unknown switch';
									break;
							}
						} break;
					default:
						$this->json['success'] = false;
						break;
				}
			}
			if ( $TEST_MODE ) {
				$this->json['action'] = $this->action;
				$this->json['request'] = $_REQUEST;
			}
			echo json_encode($this->json);
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
		$current = get_single("SELECT notify AS num FROM `{$MYSQL_PREFIX}users` WHERE userid = {$id}");
		
		if ( $current == 1 ) {
			$sql  = "UPDATE {$MYSQL_PREFIX}users SET notify = 0 WHERE userid = '".intval($id)."'";
		} else {
			$sql  = "UPDATE {$MYSQL_PREFIX}users SET notify = 1 WHERE userid = '".intval($id)."'";
		}
		
		$result = mysql_query($sql, $db);
		
		if ( $result ) {
			return array('success' => true, 'msg' => "User #{$id} Marked ".(($current==1)?"Not Notified":"Notified"), 'newval' => (($current==1)?0:1) );
		} else {
			return array('success' => false, 'msg' => "Toggle Failed ".(($TEST_MODE)?mysql_error():""));
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
		$current = get_single("SELECT payroll AS num FROM `{$MYSQL_PREFIX}users` WHERE userid = {$id}");
		
		if ( $current == 1 ) {
			$sql  = "UPDATE {$MYSQL_PREFIX}users SET payroll = 0 WHERE userid = '".intval($id)."'";
		} else {
			$sql  = "UPDATE {$MYSQL_PREFIX}users SET payroll = 1 WHERE userid = '".intval($id)."'";
		}
		
		$result = mysql_query($sql, $db);
		
		if ( $result ) {
			return array('success' => true, 'msg' => "User #{$id} Marked ".(($current==1)?"Off Payroll":"On Payroll"), 'newval' => (($current==1)?0:1) );
		} else {
			return array('success' => false, 'msg' => "Toggle Failed ".(($TEST_MODE)?mysql_error():""));
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
		$current = get_single("SELECT limithours AS num FROM `{$MYSQL_PREFIX}users` WHERE userid = {$id}");
		
		if ( $current == 1 ) {
			$sql  = "UPDATE {$MYSQL_PREFIX}users SET limithours = 0 WHERE userid = '".intval($id)."'";
		} else {
			$sql  = "UPDATE {$MYSQL_PREFIX}users SET limithours = 1 WHERE userid = '".intval($id)."'";
		}
		
		$result = mysql_query($sql, $db);
		
		if ( $result ) {
			return array('success' => true, 'msg' => "User #{$id} Marked ".(($current==1)?"Not Limitied":"Limited"), 'newval' => (($current==1)?0:1) );
		} else {
			return array('success' => false, 'msg' => "Toggle Failed ".(($TEST_MODE)?mysql_error():""));
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
		$current = get_single("SELECT active AS num FROM `{$MYSQL_PREFIX}users` WHERE userid = {$id}");
		
		if ( $current == 1 ) {
			$sql  = "UPDATE {$MYSQL_PREFIX}users SET active = 0 WHERE userid = '".intval($id)."'";
		} else {
			$sql  = "UPDATE {$MYSQL_PREFIX}users SET active = 1 WHERE userid = '".intval($id)."'";
		}
		
		$result = mysql_query($sql, $db);
		
		if ( $result ) {
			return array('success' => true, 'msg' => "User #{$id} Marked ".(($current==1)?"Inactive":"Active"), 'newval' => (($current==1)?0:1) );
		} else {
			return array('success' => false, 'msg' => "Toggle Failed ".(($TEST_MODE)?mysql_error():""));
		}
	}
	
	/** 
	 * Show available ToDo Functions
	 * 
	 * @global string TDTrac Root Link HREF
	 * @return array Formatted HTML
	 */
	public function index() {
		if ( !$this->user->admin ) { return array('',''); }
		$list = new tdlist(array('id' => 'admin_index', 'inset' => true));
		$list->setFormat("<a href='%s'><h3>%s</h3></a>");
		$list->addRow(array('/admin/useradd/', 'Add User'));
		$list->addRow(array('/admin/users/', 'View Users'));
		$list->addRow(array('/admin/groups/', 'Groups Managment'));
		$list->addRow(array('/admin/mail/', 'TDTracMail Config'));
		return $list->output();
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
		
		$form = new tdform(array('action' => "{$TDTRAC_SITE}admin/permsave/json:1/id:{$id}/"));
	
		$fesult = $form->addHidden('id', $id);
		$sql = "SELECT permid, permcan FROM {$MYSQL_PREFIX}permissions pm WHERE groupid = {$id}";
		$result = mysql_query($sql, $db);
		while ( $row = mysql_fetch_array($result) ) {
			$pname = $row['permid']; $pvalue = $row['permcan'];
			$dbperm[$pname] = $pvalue;
		}
		foreach ( $this->perms_avail as $perm ) {
			$fesult = $form->addToggle(array('name' => $perm, 'preset' => $dbperm[$perm], 'label' => $perm));
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
			$result2 = mysql_query($sql, $db);
			if (!$result2) {
				return array('success' => false, 'msg' => "Permission Save Failed".(($TEST_MODE)?mysql_error():""));
			}
		}
		
		if ( $result ) {
			return array('success' => true, 'msg' => "Permissions Saved");
		} else {
			return array('success' => false, 'msg' => "Permission Save Failed".(($TEST_MODE)?mysql_error():""));
		}
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
		
		$list = new tdlist(array('id' => 'user_list', 'inset' => true));
		
		foreach ( array('User Name', 'Group', 'Phone Number', 'E-Mail Address', 'Pay Rate', 'Last Login') as $thisdet ) {
			$details[] = "<strong>{$thisdet}:</strong> %s";
		}
		foreach ( array('u-act' => 'Active', 'u-pay' => 'Payroll', 'u-own' => 'A/V/E Only Own Hours', 'u-not' => 'Notify on Payroll') as $thiscls => $thisdet ) {
			$sidebar[] = "<strong>{$thisdet}:</strong> <img class='{$thiscls}' src='/images/perm-%s.png'>";
		}
		$list->setFormat("<a href='#' class='user-menu' data-recid='%d'><h3>%s</h3><p>".join("<br />", $details)."</p><p class='ui-li-aside'>".join("<br />", $sidebar)."</p></a>");
		
		while ( $row = mysql_fetch_array($result) ) {
			$list->addRow(array(
				$row['userid'],
				$row['first'] . " " . $row['last'],
				$row['username'],
				join(", ", $this->groups_by_user($row['userid'])),
				(($row['phone']!='0')?format_phone($row['phone']):"N/A"),
				(!empty($row['email'])?$row['email']:"N/A"),
				"$".number_format($row['payrate'], 2),
				((!empty($row['lastlog']))?$row['lastlog']:"Never"),
				(($row['active'])?"ya":"no"),
				(($row['payroll'])?"ya":"no"),
				(($row['limithours'])?"ya":"no"),
				(($row['notify'])?"ya":"no")
			));
		}
		
		return $list->output();
	}

	/**
	 * Show group related forms
	 * 
	 * @global object Database Link
	 * @global string MySQL Table Prefix
	 * @global string Site Address for links
	 * @return array HTML output
	 */
	private function groups() {
		GLOBAL $db, $MYSQL_PREFIX, $TDTRAC_SITE, $HEAD_LINK;
		$perm_sql = "SELECT groupname, permid FROM `{$MYSQL_PREFIX}groupnames` gn, `{$MYSQL_PREFIX}permissions` pm WHERE pm.groupid = gn.groupid AND pm.permcan = 1 ORDER BY groupname, permid";
		$perm_res = mysql_query($perm_sql, $db);
		while ( $row = mysql_fetch_array($perm_res) ) {
			$disperm[$row['groupname']][$row['permid']] = true;
		}
		
		$sql = "SELECT `groupname`, `groupid` FROM `{$MYSQL_PREFIX}groupnames` ORDER BY groupid";
		$groups = db_list($sql, array('groupid', 'groupname'));
		
		
		$list = new tdlist(array('id' => 'grouplist', 'inset' => true));
		
		$img = "<img src='/images/perm-%s.png' title='Add' /><img src='/images/perm-%s.png' title='Edit' /><img src='/images/perm-%s.png' title='View' />";
		$perms  = "<pre><strong>Shows    : </strong>{$img}<br />";
		$perms .= "<strong>Budget   : </strong>{$img}<br />";
		$perms .= "<strong>Payroll  : </strong>{$img}<br />";
		$perms .= "<strong>Todo     : </strong>{$img}<br />";
		$perms .= "<strong>Memebers : </strong>%s</pre>";
		
		$list->setFormat("<a class='group-menu' data-id='%d' href='#'><h3>%s</h3><p>{$perms}</p></a>");
		
		foreach ( $groups as $group ) {
			$permtext = array();
			$members  = array();
			foreach ( $this->perms_avail as $cp ) {
				if ( $disperm[$group[1]][$cp] ) { 
					$permtext[] = 'ya';
				} else {
					$permtext[] = 'no';
				}
			}
			$sql = "SELECT u.username FROM `{$MYSQL_PREFIX}users` u, `{$MYSQL_PREFIX}groupnames` gn, `{$MYSQL_PREFIX}usergroups` ug WHERE gn.groupname = '{$group[1]}' AND gn.groupid = ug.groupid AND ug.userid = u.userid ORDER BY username ASC";
			$result = mysql_query($sql, $db);
			if ( mysql_num_rows($result) < 1 ) { 
				$members[] = "<em>N/A</em>";
			} else {
				while ( $mrow = mysql_fetch_array($result) ) {
					$members[] = $mrow['username'];
				}
			}
			$list->addRow(array_merge(array($group[0], $group[1]." (".$group[0].")"),$permtext,array(join(', ', $members))));
		}
		$list->addRaw("<li data-theme='c'><a data-id='0' class='group-add' href='#'><h3>Add Group</h3></a></li>");
		return $list->output();
		
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
			mysql_real_escape_string($this->action['newname'])
		);
		$request = mysql_query($sql, $db);
		if ( $request ) {
			return array('success' => true, 'msg' => "Group Added");
		} else {
			return array('success' => false, 'msg' => "Group Add Failed".(($TEST_MODE)?mysql_error():""));
		}
	}
	
	/**
	 * Logic to remove a group
	 * 
	 * @param integer Group to delete
	 * @global object Database Link
	 * @global string MySQL Table Prefix
	 * @return void
	 */
	private function group_delete($id) {
		GLOBAL $db, $MYSQL_PREFIX;
		$sql = sprintf("DELETE FROM `{$MYSQL_PREFIX}groupnames` WHERE groupid = %d",
			intval($id)
		);
		if ( $id < 100 ) { 
			return array('success' => false, 'msg' => "You Cannot remove the special groups (ID < 100)");
		} else {
			$request = mysql_query($sql, $db);
			if ( $request ) {
				return array('success' => true, 'msg' => "Group Removed");
			} else {
				return array('success' => false, 'msg' => "Group Remove Failed".(($TEST_MODE)?mysql_error():""));
			}
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
			mysql_real_escape_string($this->action['newname']),
			intval($this->action['oldname'])
		);
		$request = mysql_query($sql, $db);
		if ( $request ) {
			return array('success' => true, 'msg' => "Group Renamed");
		} else {
			return array('success' => false, 'msg' => "Group Rename Failed".(($TEST_MODE)?mysql_error():""));
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

