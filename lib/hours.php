<?php
/**
 * TDTrac Payroll Functions
 * 
 * Contains all payroll related functions. 
 * Data hardened
 * @package tdtrac
 * @version 2.0.0
 * @author J.T.Sage <jtsage@gmail.com>
 */

/**
 * PAYROLL Module
 *  Allows configuration of shows
 * 
 * @package tdtrac
 * @version 2.0.0
 * @since 2.0.0
 * @author J.T.Sage <jtsage@gmail.com>
 */
class tdtrac_hours {
	
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
	
	/** 
	 * Create a new instance of the TO-DO module
	 * 
	 * @param object User object
	 * @param array Parsed query string
	 * @return object Payroll Object
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
			switch ( $this->action['action'] ) {
				case "add":
					$this->title .= " :: Add";
					if ( $this->user->can("addhours") ) {
						if ( $this->post ) {
							thrower($this->save(false), 'hours/add/');
						} else {
							$this->html = $this->add_form();
						}
					} else {
						thrower('Access Denied :: You cannot add payroll items', 'hours/');
					} break;
				case "view":
					if ( $this->user->can('viewhours') ) {
						$this->title .= " :: View";
						if ( $this->post ) {
							$type  = ( isset($_REQUEST['type']) && ( $_REQUEST['type'] == 'unpaid' || $_REQUEST['type'] == 'user' || $_REQUEST['type'] == 'date' )) ? "type:{$_REQUEST['type']}/" : "";
							$id    = ( isset($_REQUEST['id']) && is_numeric($_REQUEST['id']) ) ? "id:".intval($_REQUEST['id'])."/" : "";
							$sdate = ( isset($_REQUEST['sdate']) && $_REQUEST['sdate'] <> "" ) ? "sdate:{$_REQUEST['sdate']}/" : "";
							$edate = ( isset($_REQUEST['edate']) && $_REQUEST['edate'] <> "" ) ? "edate:{$_REQUEST['edate']}/" : "";
							thrower(false, "hours/view/{$type}{$id}{$sdate}{$edate}");
						} else {
							if ( !isset($this->action['type']) ) {
								$this->html = $this->view_form();
							} else {
								switch($this->action['type']) {
									case "user":
									case "date":
										$this->html = $this->view();
										break;
									case "unpaid":
										if ( $this->user->admin ) {
											$this->html = $this->view();
										} else {
											thrower('Access Denied :: You Cannot View Unpaid Hours', 'hours/');
										} break;
									default:
										$this->html = $this->view_form();
										break;
								}
							}
						}
					} else {
						thrower("Access Denied :: You Cannot View Hours");
					} break;
				case "edit":
					$this->title .= " :: Edit";
					if ( $this->user->can("edithours") ) {
						if ( $this->post ) {
							if ( isset($_REQUEST['id']) && is_numeric($_REQUEST['id']) ) {
								thrower($this->save(true), "hours/edit/id:".intval($_REQUEST['id'])."/");
							} else {
								thrower('Error :: Data Mismatch Detected', 'hours/');
							}
						} else {
							if ( isset($this->action['id']) && is_numeric($this->action['id']) ) {
								$this->html = $this->edit_form(intval($this->action['id']));
							} else {
								thrower("Error :: Data Mismatch Detected", 'hours/');
							}
						}
					} else {
						thrower('Access Denied :: You Cannot Edit Payroll Items', 'hours/');
					} break;
				case "remind":
					$this->title .= " :: Send Reminders";
					if ( $this->user->admin ) {
						if ( $this->post ) {
							if ( !empty($_REQUEST['duedate']) && !empty($_REQUEST['sdate']) && !empty($_REQUEST['edate']) ) {
								thrower($this->remind_send(), 'hours/');
							} else {
								thrower('Error :: Data Mismatch Detected', 'hours/');
							}
						} else {
							$this->html = $this->remind_form();
						}
					} else {
						thrower('Access Denied :: You Cannot Send Payroll Reminders', 'hours/');
					} break;
				default:
					$this->html = $this->index();
					break;
			}
			makePage($this->html, $this->title);
		} else { 
			switch($this->action['action']) {
				case "email":
					if ( $this->user->can('edithours') ) {
						$this->email();
					} else {
						$this->json['success'] = false;
					} break;
				case "clear":
					if ( isset($this->action['id']) && is_numeric($this->action['id']) && $this->user->admin ) {
						$this->clear(intval($this->action['id']));
					} else {
						$this->json['success'] = false;
					} break;
				case "delete":
					if ( isset($this->action['id']) && is_numeric($this->action['id']) && $this->user->can('edithours') ) {
						$this->delete(intval($this->action['id']));
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
	 * Show hours add form
	 * 
	 * @global bool Use daily or hourly pay rates
	 * @global string Site address for links
	 * @return array HTML output
	 */
	private function add_form () {
		GLOBAL $TDTRAC_DAYRATE, $TDTRAC_SITE;
		$form = new tdform("{$TDTRAC_SITE}hours/add/", "hours-add-form", 1, 'genform', 'Add Payroll Record');
		
		if ( isset($this->action['own']) && $this->action['own'] && $this->user->onpayroll ) {
			$result = $form->addDrop('userid', 'Employee', null, db_list(get_sql_const('emps'), array('userid', 'name')), False, $this->user->id);
		} else {
			$result = $form->addDrop('userid', 'Employee', null, db_list(get_sql_const('emps'), array('userid', 'name')), False);
		}
		$result = $form->addDrop('showid', 'Show', null, db_list(get_sql_const('showid'), array('showid', 'showname')), False);
		$result = $form->addDate('date', 'Date');
		$result = $form->addText('worked', (($TDTRAC_DAYRATE)?"Days":"Hours")." Worked");
		$result = $form->addHidden('new-hours', true);
		
		return $form->output('Add Hours');
	}

	/**
	 * Show hours edit form
	 * 
	 * @global object Database Link
	 * @global string MySQL Table Prefix
	 * @global bool Use daily or hourly pay rates
	 * @global string Site address for links
	 * @param integer Payroll ID to edit
	 * @return array HTML output
	 */
	private function edit_form ($hid) {
		GLOBAL $db, $MYSQL_PREFIX, $TDTRAC_DAYRATE, $TDTRAC_SITE;
		$sql .= "SELECT h.*, CONCAT(first, ' ', last) as name FROM {$MYSQL_PREFIX}hours h, {$MYSQL_PREFIX}users u WHERE h.userid = u.userid AND h.id = " . intval($hid) . " LIMIT 1";
		$result = mysql_query($sql, $db);
		$recd = mysql_fetch_array($result);
		$form = new tdform("{$TDTRAC_SITE}hours/edit/id:{$hid}/", "edit-hours-form", 1, 'genform', 'Edit Payroll Record');
		
		$fesult = $form->addDrop('userid', 'Employee', null, array(array($recd['userid'], $recd['name'])), False);
		$fesult = $form->addDrop('showid', 'Show', null, db_list(get_sql_const('showid'), array('showid', 'showname')), False, $recd['showid']);
		$fesult = $form->addDate('date', 'Date', null, $recd['date']);
		$fesult = $form->addText('worked', (($TDTRAC_DAYRATE)?"Days":"Hours")." Worked", null, $recd['worked']);
		$fesult = $form->addCheck('submitted', 'Hours Paid Out', null, $recd['submitted']);
		$fesult = $form->addHidden('id', $hid);
		
		return $form->output('Edit Hours');
	}

	
	
	/**
	 * Logic to save payroll record to database
	 * 
	 * @global object Database Link
	 * @global string MySQL Table Prefix
	 * @global bool MySQL Debug
	 * @param bool False for new record, true for overwrite
	 * @return void
	 */
	private function save($exists = false) {
		GLOBAL $db, $MYSQL_PREFIX, $MYSQL_DEBUG;
		if ( !$exists ) {
			$sqlstring  = "INSERT INTO `{$MYSQL_PREFIX}hours` ( `userid`, `showid`, `date`, `worked` )";
			$sqlstring .= " VALUES ( %d, %d, '%s', '%f' )";
	
			$sql = sprintf($sqlstring,
				intval($_REQUEST['userid']),
				intval($_REQUEST['showid']),
				mysql_real_escape_string($_REQUEST['date']),
				floatval($_REQUEST['worked'])
			);
		} else {
			$sqlstring  = "UPDATE `{$MYSQL_PREFIX}hours` SET `showid` = %d, `date` = '%s', `worked` = '%f',";
			$sqlstring .= " submitted = %d WHERE id = %d";
		
			$sql = sprintf($sqlstring,
				intval($_REQUEST['showid']),
				mysql_real_escape_string($_REQUEST['date']),
				floatval($_REQUEST['worked']),
				(($_REQUEST['submitted'] == "y") ? "1" : "0"),
				intval($_REQUEST['id'])
			);
		}
		
		$result = mysql_query($sql, $db);
		
		if ( $result ) {
			if ( !$exists ) { // IF NEW HOURS, DO MESSAGING
				$mailmessage = sprintf("%s Added Payroll: %f for %s (%s)",
					$this->user->name,
					floatval($_REQUEST['worked']),
					mysql_real_escape_string($_REQUEST['data']),
					$this->user->get_name(intval($_REQUEST['userid']))
				);
				$mail_sql_str  = "INSERT INTO `{$MYSQL_PREFIX}msg` ( toid, fromid, body ) VALUES ( %d, %d, '%s' )";
				
				if ( $this->user->id == intval($_REQUEST['userid']) ) { // ADDING FOR SELF, NOTIFY WHERE `notify`
					if ( $this->user->isemp ) { // BUT ONLY FOR LIMITED ACCOUNTS
						$users_to_notify_sql = "SELECT userid FROM `{$MYSQL_PREFIX}users` WHERE notify = 1";
						$users_to_notify_res = mysql_query($users_to_notify_sql, $db);
						while ( $row = mysql_fetch_array($users_to_notify_res) ) {
							$mail_sql  = sprintf($mail_sql_str,	$row['userid'], $this->user->id, $mailmessage );
							$mail_res  = mysql_query($mail_sql, $db);
						}
					}
				} else { // ADDING FOR OTHERS, NOTIFY RECIPIENT ONLY
					$mail_sql = sprintf($mail_sql_str,
						intval($_REQUEST['userid']),
						$this->user->id,
						$mailmessage );
					$mail_res = mysql_query($mail_sql, $db);
				}
			}
			return "Payroll Item Saved";
		} else {
			return "Payroll Item Save :: Failed" . (($MYSQL_DEBUG) ? " (".mysql_error().")" : "");
		}
	}

	/**
	 * Logic to remove hours from database
	 * 
	 * @global object Database Link
	 * @global string MySQL Table Prefix
	 * @param integer Payroll ID to remove
	 * @return void
	 */
	private function delete($id) {
		GLOBAL $db, $MYSQL_PREFIX;
		$sql = "DELETE FROM {$MYSQL_PREFIX}hours WHERE id = ".intval($id)." LIMIT 1";
		$result = mysql_query($sql, $db);
		if ( $result ) {
			$this->json['success'] = true;
		} else {
			$this->json['success'] = false;
		}
	}

	/**
	 * Set all hours paid
	 * 
	 * @global object Database Link
	 * @global string MySQL Table Prefix
	 * @param integer User ID
	 * @return void
	 */
	private function clear($userid) {
		GLOBAL $db, $MYSQL_PREFIX;
		$sql = "UPDATE {$MYSQL_PREFIX}hours SET submitted = 1 WHERE userid = ".intval($userid);
		$result = mysql_query($sql, $db);
		if ( $result ) {
			$this->json['success'] = true;
		} else {
			$this->json['success'] = false;
		}
	}

	/** 
	 * Show available Payroll Functions
	 * 
	 * @global string TDTrac Root Link HREF
	 * @return array Formatted HTML
	 */
	public function index() {
		global $TDTRAC_SITE;
		$html[] = "<ul class=\"linklist\"><li><h3>Payroll Tracking</h3><ul class=\"linklist\">";
		$html[] = "<li>Manage payroll records for each employee</li>";
		$html[] = ( $this->user->onpayroll ) 		? "  <li><a href=\"{$TDTRAC_SITE}hours/add/own:1/\">Add Hours For Yourself</a></li>" : "";
		$html[] = ( $this->user->can('addhours') && !$this->user->isemp ) 	? "  <li><a href=\"{$TDTRAC_SITE}hours/add/\">Add Hours Worked</a></li>" : ""; // SUPPESS THIS ON ONLY ADD OWN.
		$html[] = ( $this->user->can('viewhours') ) ? "  <li><a href=\"{$TDTRAC_SITE}hours/view/\">View Hours Worked</a></li>" : "";
		$html[] = ( $this->user->admin ) 			? "  <li><a href=\"{$TDTRAC_SITE}hours/view/type:unpaid/\">View Hours Worked (unpaid)</a></li>" : "";
		$html[] = ( $this->user->admin ) 			? "  <li><a href=\"{$TDTRAC_SITE}hours/remind/\">Send Payroll Due Reminder To Employees</a></li>" : "";
		$html[] = "</ul></li></ul>";
		return $html;
	}
	
	/**
	 * Show pick form for hours view.
	 * 
	 * @global object Database Link
	 * @global string MySQL Table Prefix
	 * @global string Site address for links
	 * @return array HTML Output
	 */
	private function view_form() {
		GLOBAL $db, $MYSQL_PREFIX, $TDTRAC_SITE;
		$form1 = new tdform("{$TDTRAC_SITE}hours/view/", "view-hours-user-form", 1, 'genform', 'View By Employee');
		$fesult = $form1->addDrop('id', 'Employee', null, db_list(get_sql_const('emps'), array('userid', 'name')), False);
		$fesult = $form1->addDate('sdate', 'Start Date', null, null, True, 'sdate1');
		$fesult = $form1->addDate('edate', 'End Date', null, null, True, 'edate1');
		$fesult = $form1->addHidden('type', 'user');
		$html = $form1->output('View Hours', 'Leave Dates Blank to See All');
		
		if ( $this->user->isemp ) { return $html; }
		
		$form2 = new tdform("{$TDTRAC_SITE}hours/view/", "view-hours-date-form", $form1->getlasttab(), "genform2", 'View Dated Report');
		$fesult = $form2->addDate('sdate', 'Start Date', null, null, True, 'sdate2');
		$fesult = $form2->addDate('edate', 'End Date', null, null, True, 'edate2');
		$fesult = $form2->addHidden('type', 'date');
		$html = array_merge($html, $form2->output('View Hours', 'Leave Dates Blank to See All'));
	
		if ( !$this->user->admin ) { return $html; }
		
		$form3 = new tdform("{$TDTRAC_SITE}hours/view/", "view-hours-unpaid-form", $form2->getlasttab(), "genform3", "View Unpaid Hours");
		$fesult = $form3->addHidden('type', 'unpaid');
		return array_merge($html, $form3->output('View Hours'));
	}
	
	/**
	 * Show payroll report
	 * 
	 * @global object Database Link
	 * @global string MySQL Table Prefix
	 * @global bool Use daily or hourly wages
	 * @global string Site address for links
	 * @global array JavaScript
	 * @return array HTML Output
	 */
	private function view() {
		GLOBAL $db, $MYSQL_PREFIX, $TDTRAC_DAYRATE, $TDTRAC_SITE, $SITE_SCRIPT;
		if ( $this->user->isemp && ! ( $this->action['type'] == 'user' && $this->action['id'] == $this->user->id ) ) {
			thrower("Access Denied :: You do not have access to the view", 'hours/');
		}
		
		$sql  = "SELECT CONCAT(first, ' ', last) as name, u.userid, worked, date, showname, submitted, h.id as hid FROM {$MYSQL_PREFIX}users u, {$MYSQL_PREFIX}shows s, {$MYSQL_PREFIX}hours h WHERE ";
		$sql .= "u.userid = h.userid AND s.showid = h.showid";
		$sql .= ($this->action['type'] == 'user') ? " AND u.userid = '".intval($this->action['id'])."'" : "";
		if ( $this->action['type'] <> 'unpaid' ) {
			$sql .= (isset($this->action['sdate'])) ? " AND h.date >= '".mysql_real_escape_string($this->action['sdate'])."'" : "";
			$sql .= (isset($this->action['edate'])) ? " AND h.date <= '".mysql_real_escape_string($this->action['edate'])."'" : "";
		} else {
			$sql .= " AND h.submitted = 0";
		}
		$sql .= " ORDER BY last ASC, date DESC";
		$html[] = "<!--{$sql}-->";
		
		$type  = ( isset($this->action['type']) && ( $this->action['type'] == 'unpaid' || $this->action['type'] == 'user' || $this->action['type'] == 'date' )) ? "type:{$this->action['type']}/" : "";
		$id    = ( isset($this->action['id']) && is_numeric($this->action['id']) ) ? "id:".intval($this->action['id'])."/" : "";
		$sdate = ( isset($this->action['sdate']) ) ? "sdate:{$this->action['sdate']}/" : "";
		$edate = ( isset($this->action['edate']) ) ? "edate:{$this->action['edate']}/" : "";
		
		$maillink = "{$TDTRAC_SITE}hours/email/json:1/{$type}{$id}{$sdate}{$edate}";
		
		if ( $this->action['type'] == 'unpaid' ) {
			$html[] = "<h3>All Unpaid Hours</h3>";
			$html[] = "<span class=\"upright\">[<a class=\"ALL-email\" href=\"#\">E-Mail All to Self</a>]</span>";
			$html[] = "<br /><br /><br />";
			$SITE_SCRIPT[] = "$(function() { $('.ALL-email').click( function() {";
			$SITE_SCRIPT[] = "  $('#popper').html(\"Please wait...\"); $('#popperdiv').show('blind');";
			$SITE_SCRIPT[] = "	$.getJSON(\"{$TDTRAC_SITE}hours/email/json:1/type:unpaid/id:0/\", function(data) {";
			$SITE_SCRIPT[] = "		if ( data.success === true ) { ";
			$SITE_SCRIPT[] = "			$('#popper').html(\"All Unpaid Hours :: Sent\");";
			$SITE_SCRIPT[] = "		} else { $('#popper').html(\"E-Mail Send :: Failed\"); }";
			$SITE_SCRIPT[] = "		$('#popperdiv').show('blind');";			
			$SITE_SCRIPT[] = "	}); return false;";
			$SITE_SCRIPT[] = "});});";
		}
		$result = mysql_query($sql, $db);
		if ( mysql_num_rows($result) < 1 ) { return array("<h3>Empty Data Set</h3>", "<p>There are no payroll items matching your terms.</p>"); }
		while ( $row = mysql_fetch_array($result) ) {
			$dbarray[$row['name']][] = $row;
		}
		foreach ( $dbarray as $key => $data ) {
			$html[] = "<h3>Hours Worked For: {$key}</h3>";
			$ident = preg_replace("/ /", "", $key);
			if ( $this->action['type'] == 'unpaid' ) { $maillink = "{$TDTRAC_SITE}hours/email/json:1/type:unpaid/id:{$data[0]['userid']}"; }
			$SITE_SCRIPT[] = "$(function() { $('.{$ident}-email').click( function() {";
			$SITE_SCRIPT[] = "  $('#popper').html(\"Please wait...\"); $('#popperdiv').show('blind');";
			$SITE_SCRIPT[] = "	$.getJSON(\"{$maillink}\", function(data) {";
			$SITE_SCRIPT[] = "		if ( data.success === true ) { ";
			$SITE_SCRIPT[] = "			$('#popper').html(\"Hours For {$key} :: Sent\");";
			$SITE_SCRIPT[] = "		} else { $('#popper').html(\"E-Mail Send :: Failed\"); }";
			$SITE_SCRIPT[] = "		$('#popperdiv').show('blind');";			
			$SITE_SCRIPT[] = "	}); return false;";
			$SITE_SCRIPT[] = "});});";
			$tmphtml = "<span class=\"upright\">[<a class=\"{$ident}-email\" href=\"#\">E-Mail to Self</a>]";
			if ( $this->action['type'] == 'unpaid') {
				$SITE_SCRIPT[] = "var hclear{$ident} = true;";
				$SITE_SCRIPT[] = "$(function() { $('.{$ident}-clear').click( function() {";
				$SITE_SCRIPT[] = "	if ( hclear{$ident} && confirm('Mark All Paid for {$key}?')) {";
				$SITE_SCRIPT[] = "		$.getJSON(\"{$TDTRAC_SITE}hours/clear/json:1/id:{$data[0]['userid']}\", function(data) {";
				$SITE_SCRIPT[] = "			if ( data.success === true ) { ";
				$SITE_SCRIPT[] = "				$('#hours-{$ident}').find('td').css('background-color', '#778177');";
				$SITE_SCRIPT[] = "				$('.{$ident}-clear').html('PAID');";
				$SITE_SCRIPT[] = "				$('#popper').html(\"All hours for {$key} marked PAID\");";
				$SITE_SCRIPT[] = "			} else { $('#popper').html(\"Hours Mark :: Failed\"); }";
				$SITE_SCRIPT[] = "			hclear{$ident} = false;";
				$SITE_SCRIPT[] = "			$('#popperdiv').show('blind');";			
				$SITE_SCRIPT[] = "	});} return false;";
				$SITE_SCRIPT[] = "});});";
				$tmphtml .= " [<a class=\"{$ident}-clear\" href=\"#\">Set All Paid</a>]";
			}
			
			$html[] = $tmphtml . "</span>";
			$html[] = "<ul class=\"datalist\">";
			$html[] = ($sdate <> "" ) ? "<li>Start Date: {$this->action['sdate']}</li>" : "";
			$html[] = ($edate <> "" ) ? "<li>Ending Date: {$this->action['edate']}</li>" : "";
			$html[] = "</ul>";
			$tabl = new tdtable("hours-{$ident}", 'datatable', $this->user->can('edithours'));
			$tabl->addHeader(array('Date', 'Show', (($TDTRAC_DAYRATE)?"Days":"Hours")." Worked", 'Paid'));
			$tabl->addNumber((($TDTRAC_DAYRATE)?"Days":"Hours")." Worked");
			$tabl->setAlign('Paid', "center");
			if ( $this->user->can('edithours') ) { $tabl->addAction(array('pedit', 'pdel')); }
			
			foreach ( $data as $num => $line ) {
				$tabl->addRow(array($line['date'], $line['showname'], $line['worked'], (($line['submitted'] == 1) ? "YES" : "NO")), $line);
			}
			$html = array_merge($html, $tabl->output(false));
		}
		return $html;
	}



	/**
	 * Send hours via email
	 * 
	 * @global object Database connection
	 * @global string MySQL Table Prefix
	 * @global bool Use dayrate or hourly rate
	 * @return void
	 */
	private function email() {
		GLOBAL $db, $MYSQL_PREFIX, $TDTRAC_DAYRATE;
		if ( $this->user->isemp && ! ( $this->action['type'] == 'user' && $this->action['id'] == $this->user->id ) ) {
			return false;
		}
		
		$sql  = "SELECT CONCAT(first, ' ', last) as name, u.userid, worked, date, showname, submitted, h.id as hid FROM {$MYSQL_PREFIX}users u, {$MYSQL_PREFIX}shows s, {$MYSQL_PREFIX}hours h WHERE ";
		$sql .= "u.userid = h.userid AND s.showid = h.showid";
		$sql .= ($this->action['type'] == 'user' || ( $this->action['type'] == 'unpaid' && $this->action['id'] <> 0 ) ) ? " AND u.userid = '".intval($this->action['id'])."'" : "";
		if ( $this->action['type'] <> 'unpaid' ) {
			$sql .= (isset($this->action['sdate'])) ? " AND h.date >= '".mysql_real_escape_string($this->action['sdate'])."'" : "";
			$sql .= (isset($this->action['edate'])) ? " AND h.date <= '".mysql_real_escape_string($this->action['edate'])."'" : "";
		} else {
			$sql .= " AND h.submitted = 0";
		}
		$sql .= " ORDER BY last ASC, date DESC";
		
		$uid    = ( isset($this->action['id']) && is_numeric($this->action['id']) ) ? intval($this->action['id']) : "";
		$sdate = ( isset($this->action['sdate']) ) ? $this->action['sdate'] : 0;
		$edate = ( isset($this->action['edate']) ) ? $this->action['edate'] : 0;
			
		$result = mysql_query($sql, $db);
		while ( $row = mysql_fetch_array($result) ) {
			$dbarray[$row['name']][] = $row;
		}
		$body = "";
	
		$subject = "TDTrac Hours Worked ::";
		$subject .= ( $this->action['type'] == 'user' ) ? " ".$this->user->get_name($uid) : "";
		$subject .= ($sdate <> 0 ) ? " [Start Date: {$sdate}]" : "";
		$subject .= ($edate <> 0 ) ? " [Ending Date: {$edate}]" : "";
		$subject .= ( $this->action['id'] == 0 && $this->action['type'] == 'unpaid' ) ? " All Unpaid Hours" : "";
	
		$headers  = 'MIME-Version: 1.0' . "\r\n";
		$headers .= 'Content-type: text/html; charset=iso-8859-1' . "\r\n";
	
		foreach ( $dbarray as $key => $data ) {
			$body .= "<h2>Hours Worked For {$key}</h2><p>\n";
			$body .= ($sdate <> 0 ) ? "Start Date: {$sdate}\n" : "";
			$body .= ($sdate <> 0 && $edate <> 0 ) ? "<br />" : "";
			$body .= ($edate <> 0 ) ? "Ending Date: {$edate}" : "";
			$body .= "</p>\n";

			$tabl = new tdtable("hours-{$ident}", 'datatable', false);
			$tabl->addHeader(array('Date', 'Show', (($TDTRAC_DAYRATE)?"Days":"Hours")." Worked", 'Paid'));
			$tabl->addNumber((($TDTRAC_DAYRATE)?"Days":"Hours")." Worked");
			$tabl->setAlign('Paid', "center");
			
			foreach ( $data as $num => $line ) {
				$tabl->addRow(array($line['date'], $line['showname'], $line['worked'], (($line['submitted'] == 1) ? "YES" : "NO")), $line);
			}
			$body .= $tabl->output(true);
		}
		
		$result = mail($this->user->email, $subject, $body, $headers);
		if ( $result ) {
			$this->json['success'] = true;
		} else {
			$this->json['success'] = false;
		}
	}
	
	/**
	 * Show hours reminder email options form
	 * 
	 * @global object Database Link
	 * @global string MySQL Table Prefix
	 * @global string Site address for links
	 * @return array HTML output
	 */
	private function remind_form() {
		GLOBAL $db, $MYSQL_PREFIX, $TDTRAC_SITE;
		$sql = "SELECT CONCAT(first, ' ', last) as name, userid FROM {$MYSQL_PREFIX}users WHERE payroll = 1 ORDER BY last DESC";
		$result = mysql_query($sql, $db);
		$form = new tdform("{$TDTRAC_SITE}hours/remind/", 'form2', 1, 'genform', 'Send Payroll Reminder');
		
		$fesult = $form->addDate('duedate', 'Hours Due Date');
		$fesult = $form->addDate('sdate', 'Start Date');
		$fesult = $form->addDate('edate', 'End Date');
		$fesult = $form->addInfo('<strong>Employees to remind:</strong>');
		while ( $row = mysql_fetch_array($result) ) {
			$fesult = $form->addCheck('toremind[]', $row['name'], null, False, True, $row['userid']);
		}
		
		return $form->output('Send Reminders');
	}

	/**
	 * Logic to send reminders
	 * 
	 * @global object Database Link
	 * @global string MySQL Table Prefix
	 * @global string Site address for links
	 * @return string HTML output
	 */
	private function remind_send() {
		GLOBAL $db, $MYSQL_PREFIX, $TDTRAC_SITE;
		foreach ( $_REQUEST['toremind'] as $remid ) {
			$results[] = $this->remind_email(
				intval($remid), 
				mysql_real_escape_string($_REQUEST['duedate']),
				mysql_real_escape_string($_REQUEST['sdate']),
				mysql_real_escape_string($_REQUEST['edate'])
			);
		}
		return "Sent Reminders<br />" . join($results);
	}
	
	/**
	 * Send hours reminders
	 * 
	 * @param integer User id of sender
	 * @param string Date hours are due
	 * @param string Start Date of payperiod
	 * @param string End Date of payperiod
	 * @global object Database connection
	 * @global string MySQL Table Prefix
	 * @global string Site Address for redirect
	 * @return string Success / Fail Message
	 */
	private function remind_email($userid, $duedate, $sdate, $edate) {
		GLOBAL $db, $MYSQL_PREFIX, $TDTRAC_SITE;
		$sql1 = "SELECT CONCAT(first, ' ', last) as name, username, email, password FROM {$MYSQL_PREFIX}users WHERE userid = '{$userid}'";
		$resul1 = mysql_query($sql1, $db);
		$row1 = mysql_fetch_array($resul1);
		$sendto = $row1['email'];
	
		$subject = "TDTrac Hours Are Due: {$duedate}";	
	
		$headers  = 'MIME-Version: 1.0' . "\r\n";
		$headers .= 'Content-type: text/html; charset=iso-8859-1' . "\r\n";
	
		$body  = "<p>This e-mail is being sent to you to remind you that hours for the payperiod of {$sdate} true {$edate} are due on {$duedate}.  Please take a moment to log into the system and update or double check your hours.<br />";
		$body .= "<br />As a reminder, your <strong>username:</strong> {$row1['username']} and <strong>password:</strong> {$row1['password']} for <a href=\"{$TDTRAC_SITE}\">{$TDTRAC_SITE}</a></p>";
	
		$result = mail($sendto, $subject, $body, $headers);
		$retty = $row1['name'];
		if ( $result ) {
			return $retty . " :: OK";
		} else {
			return $retty . " :: FAIL";
		}
	}
}
?>
