<?php
/**
 * TDTrac Admin Control Functions
 * 
 * Contains all access control framework
 * Data hardened
 * @package tdtrac
 * @version 4.0.0
 * @author J.T.Sage <jtsage@gmail.com>
 */


/**
 * ADMIN Module
 *  Allows configuration of users, groups and permissions
 * 
 * @package tdtrac
 * @version 4.0.0
 * @since 2.0.0
 * @author J.T.Sage <jtsage@gmail.com>
 */
class tdtrac_admin {
	
	/** @var array Parsed query string */
	private $action = array();
	
	/** @var array Formatted HTML */
	private $html = array();
	
	/** @var string Page Title */
	private $title = "Admin";
	
	/** @var array Available Permissions */
	private $perms_avail = array(
		"addshow",
		"editshow",
		"viewshow",
		"addbudget",
		"editbudget",
		"viewbudget",
		"addhours",
		"edithours",
		"viewhours",
		"addtodo",
		"edittodo",
		"viewtodo"
	);
	
	/** 
	 * Create a new instance of the ADMIN module
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
	 * Output admin operation
	 * 
	 * @return void
	 */
	public function output() {
		GLOBAL $TEST_MODE, $CANCEL, $HEAD_LINK;
		if ( !$this->user->admin ) { 
			$this->html = error_page('Access Denied :: You are not an administrator'); 
		} else {
			switch ( $this->action['action'] ) {
				case "users": // View Users
					$HEAD_LINK = array('admin/useradd/', 'plus', 'Add User'); 
					$this->title .= "::View Users";
					$this->html = $this->user_view();
					break;
				case "useradd": // Add User
					$CANCEL = true;
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
					$CANCEL = true;
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
		}
		makePage($this->html, $this->title);
	} // END OUTPUT FUNCTION
	
	
	/** 
	 * Show available Admin Functions
	 * 
	 * @global string TDTrac Root Link HREF
	 * @return array Formatted HTML
	 */
	public function index() {
		GLOBAL $TDTRAC_SITE;
		if ( !$this->user->admin ) { return array('',''); }
		$list = new tdlist(array('id' => 'admin_index', 'inset' => true));
		$list->setFormat("<a href='{$TDTRAC_SITE}%s'><h3>%s</h3></a>");
		$list->addRow(array('admin/useradd/', 'Add User'));
		$list->addRow(array('admin/users/', 'View Users'));
		$list->addRow(array('admin/groups/', 'Groups Managment'));
		$list->addRow(array('admin/mail/', 'TDTracMail Config'));
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
		$result = mysqli_query($db, $sql);
		while ( $row = mysqli_fetch_array($result) ) {
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
		
		$form = new tdform(array('action' => "{$TDTRAC_SITE}json/adm/base:admin/sub:saveperms/id:{$id}/"));
	
		$fesult = $form->addHidden('id', $id);
		$sql = "SELECT permid, permcan FROM {$MYSQL_PREFIX}permissions pm WHERE groupid = {$id}";
		$result = mysqli_query($db, $sql);
		while ( $row = mysqli_fetch_array($result) ) {
			$pname = $row['permid']; $pvalue = $row['permcan'];
			$dbperm[$pname] = $pvalue;
		}
		foreach ( $this->perms_avail as $perm ) {
			$fesult = $form->addToggle(array(
				'name' => $perm,
				'preset' => $dbperm[$perm],
				'label' => $perm
			));
		}	
		return $form->output('Save');
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
		$form = new tdform(array(
			'action' => "{$TDTRAC_SITE}json/adm/base:admin/sub:saveuser/id:0/",
			'id' => 'adduser'
		));
		
		$fesult = $form->addText(array(
			'name' => 'username',
			'label' => "User Name",
			'placeholder' => 'User login ID'
		));
		$fesult = $form->addText(array(
			'name' => 'password',
			'label' => "Password",
			'placeholder' => 'Initial Password'
		));
		$fesult = $form->addText(array(
			'name' => 'payrate',
			'label' => "Pay Rate",
			'placeholder' => 'User\'s Payrate'
		));
		$fesult = $form->addText(array(
			'name' => 'first',
			'label' => "First Name",
			'placeholder' => 'First Name'
		));
		$fesult = $form->addText(array(
			'name' => 'last',
			'label' => "Last Name",
			'placeholder' => 'Surname'
		));
		$fesult = $form->addText(array(
			'name' => 'phone',
			'label' => "Phone",
			'require' => false,
			'placeholder' => 'Phone Number'
		));
		$fesult = $form->addText(array(
			'name' => 'email',
			'label' => "E-Mail",
			'placeholder' => 'E-Mail Address'
		));
		$fesult = $form->addDrop(array(
			'name' => 'groupid', 
			'label' => "Group", 
			'options' => db_list("SELECT groupname, groupid FROM {$MYSQL_PREFIX}groupnames ORDER BY groupid DESC;", array('groupid', 'groupname'))
		));
		
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
		$sql = "SELECT u.*, groupid FROM `{$MYSQL_PREFIX}users` u, `{$MYSQL_PREFIX}usergroups` ug" .
			" WHERE u.userid = ug.userid AND u.userid = ".intval($id)." LIMIT 1";
		
		$result = mysqli_query($db, $sql);
		$row = mysqli_fetch_array($result);
		
		$form = new tdform(array(
			'action' => "{$TDTRAC_SITE}json/adm/base:admin/sub:saveuser/id:{$id}/",
			'id' => 'edituser'
		));
		
		$fesult = $form->addText(array(
			'name' => 'username',
			'label' => "User Name",
			'preset' => $row['username']
		));
		$fesult = $form->addText(array(
			'name' => 'password',
			'label' => "Password",
			'preset' => $row['password']
		));
		$fesult = $form->addText(array(
			'name' => 'payrate',
			'label' => "Pay Rate",
			'preset' => $row['payrate']
		));
		$fesult = $form->addText(array(
			'name' => 'first',
			'label' => "First Name",
			'preset' => $row['first']
		));
		$fesult = $form->addText(array(
			'name' => 'last',
			'label' => "Last Name",
			'preset' => $row['last']
		));
		$fesult = $form->addText(array(
			'name' => 'phone',
			'label' => "Phone",
			'preset' => $row['phone']
		));
		$fesult = $form->addText(array(
			'name' => 'email',
			'label' => "E-Mail",
			'preset' => $row['email']
		));
		$fesult = $form->addDrop(array(
			'name' => 'groupid', 
			'label' => "Group", 
			'options' => db_list("SELECT groupname, groupid FROM {$MYSQL_PREFIX}groupnames ORDER BY groupid DESC;", array('groupid', 'groupname')),
			'selected' => $row['groupid']
		));
		$fesult = $form->addHidden('id', $id);
		
		return $form->output('Save User');
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
		$sql = "SELECT *, DATE_FORMAT(lastlogin, '%b %D %h:%i %p') AS lastlog" .
			" FROM `{$MYSQL_PREFIX}users` ORDER BY last ASC, first ASC";
			
		$result = mysqli_query($db, $sql); $html = "";
		
		$list = new tdlist(array('id' => 'user_list', 'inset' => true));
		
		foreach ( array('User Name', 'Group', 'Phone Number', 'E-Mail Address', 'Pay Rate', 'Last Login') as $thisdet ) {
			$details[] = "<strong>{$thisdet}:</strong> %s";
		}
		foreach ( array('u-act' => 'Active', 'u-pay' => 'Payroll', 'u-own' => 'A/V/E Only Own Hours', 'u-not' => 'Notify on Payroll') as $thiscls => $thisdet ) {
			$sidebar[] = "<strong>{$thisdet}:</strong> <img class='{$thiscls}' src='/images/perm-%s.png'>";
		}
		$list->setFormat(
			"<a href='#' class='user-menu' data-recid='%d'><h3>%s</h3>" .
			"<p>".join("<br />", $details)."</p>" .
			"<p class='ui-li-aside'>".join("<br />", $sidebar)."</p></a>"
		);
		
		while ( $row = mysqli_fetch_array($result) ) {
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
		$perm_res = mysqli_query($db, $perm_sql);
		while ( $row = mysqli_fetch_array($perm_res) ) {
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
			$result = mysqli_query($db, $sql);
			if ( mysqli_num_rows($result) < 1 ) { 
				$members[] = "<em>N/A</em>";
			} else {
				while ( $mrow = mysqli_fetch_array($result) ) {
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
	 * Form for changing the mail code (tdtracmail enabled installs)
	 * 
	 * @global object Database Link
	 * @global string MySQL Table Prefix
	 * @return array HTML Output
	 */
	private function mailcode_form() {
		GLOBAL $db, $MYSQL_PREFIX, $TDTRAC_SITE;
		$sql = "SELECT * FROM `tdtracmail` WHERE prefix = '{$MYSQL_PREFIX}'";
		$result = mysqli_query($db, $sql);
		$line = mysqli_fetch_array($result);
		$form = new tdform(array('action' => "{$TDTRAC_SITE}json/adm/base:admin/sub:savemailcode/id:0/", 'id' => 'mcode'));
		
		$fes = $form->addText(array('name'=>"email", 'label'=>"E-Mail Address", 'preset' => $line['email']));
		$fes = $form->addText(array('name'=>"code", 'label'=>"Subject Code", 'preset' => $line['code']));
		return $form->output('Set Code');
	}

}
