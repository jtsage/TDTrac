<?php
/**
 * TDTrac Payroll Functions
 * 
 * Contains all payroll related functions. 
 * Data hardened
 * @package tdtrac
 * @version 3.0.0
 * @author J.T.Sage <jtsage@gmail.com>
 */

/**
 * PAYROLL Module
 *  Allows configuration of shows
 * 
 * @package tdtrac
 * @version 3.0.0
 * @since 2.0.0
 * @author J.T.Sage <jtsage@gmail.com>
 */
class tdtrac_hours {
	
	/** @var array Parsed query string */
	private $action = array();
	
	/** @var array Formatted HTML */
	private $html = array();
	
	/** @var string Page Title */
	private $title = "Payroll";
	
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
	}
	
	/**
	 * Output todo list operation
	 * 
	 * @return void
	 */
	public function output() {
		global $HEAD_LINK, $CANCEL;
	
		switch ( $this->action['action'] ) {
			case "add":
				$this->title .= "::Add";
				if ( $this->user->can("addhours") || $this->user->isemp ) {
					$this->html = $this->add_form();
				} else {
					$this->html = error_page('Access Denied :: You cannot add new hour items');
				} break;
			case "view":
				if ( $this->user->can('addhours') ) {
					$HEAD_LINK = array('/hours/add/', 'plus', 'Add Hours'); 
				}
				$this->title .= "::View";
				$type = (isset($this->action['type']))?$this->action['type']:'user';
				$id = (isset($this->action['id']))?intval($this->action['id']):$this->user->id;
					
				if ( $this->user->isemp ) { $type = 'user'; $id = $this->user->id; }
					
				switch ($type) {
					case 'user':
						$this->html = $this->view_user(intval($this->action['id']));
						break;
					case 'show':
						$this->html = $this->view_show(intval($this->action['id']));
						break;
					case 'unpaid':
						$this->html = $this->view_pending();
						break;
				} break;
			case "edit":
				$this->title .= "::Edit";
				if ( $this->user->can("edithours") ) {
					if ( isset($this->action['id']) && is_numeric($this->action['id']) ) {
						$this->html = $this->edit_form(intval($this->action['id']));
					} else {
						$this->html = error_page('Access Denied :: Data Mismatch');
					}
				} else {
					$this->html = error_page('Access Denied :: You cannot add new hour items');
				} break;
			case "remind":
				$this->title .= "::Remind";
				if ( $this->user->admin ) {
					$this->html = $this->remind_form();
				} else {
					$this->html = error_page('Access Denied :: You cannot send reminders');
				} break;
			default:
				if ( $this->user->can('addhours') ) {
					$HEAD_LINK = array('/hours/add/', 'plus', 'Add Hours'); 
				}
				$this->html = $this->index();
				break;
		}
		makePage($this->html, $this->title);
		
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
		$form = new tdform(array( 'action' => "{$TDTRAC_SITE}json/save/base:hours/id:0/", 'id' => 'hours-add-form'));
		
		$fesult = $form->addDrop(array(
			'name' => 'userid',
			'label' => 'Employee',
			'selected' => ( isset($this->action['own']) && $this->action['own'] && $this->user->onpayroll ) ? $this->user->id : '',
			'options' => db_list(get_sql_const('emps'), array('userid', 'name'))
		));
			
		$fesult = $form->addDrop(array(
			'name' => 'showid',
			'label' => 'Show',
			'selected' => ((isset($this->action['show']) && is_numeric($this->action['show']))?$this->action['show']:0),
			'options' => db_list(get_sql_const('showid'), array(showid, showname))
		));
		
		$fesult = $form->addDate(array('name' => 'date', 'label' => 'Date', 'placeholder' => 'Date worked'));
		$fesult = $form->addText(array('name' => 'worked', 'label' => (($TDTRAC_DAYRATE)?"Days":"Hours")." Worked", 'placeholder' => 'Amount Worked'));
		
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
		
		$form = new tdform(array( 'action' => "{$TDTRAC_SITE}json/save/base:hours/id:0/", 'id' => 'hours-add-form'));
		
		$fesult = $form->addDrop(array(
			'name' => 'userid',
			'label' => 'Employee',
			'selected' => $recd['userid'],
			'options' => array(array($recd['userid'], $recd['name']))
		));
			
		$fesult = $form->addDrop(array(
			'name' => 'showid',
			'label' => 'Show',
			'selected' => $recd['showid'],
			'options' => db_list(get_sql_const('showid'), array(showid, showname))
		));
		
		$fesult = $form->addDate(array('name' => 'date', 'label' => 'Date', 'placeholder' => 'Date worked', 'preset' => $recd['date']));
		$fesult = $form->addText(array('name' => 'worked', 'label' => (($TDTRAC_DAYRATE)?"Days":"Hours")." Worked", 'placeholder' => 'Amount Worked', 'preset' => $recd['worked']));
		
		$fesult = $form->addToggle(array(
			'name' => 'submitted',
			'label' => 'Hours Paid Out',
			'options' => array(array(1,'Paid'),array(0,'Pending')),
			'preset' => $recd['submitted']
		));
		$fesult = $form->addHidden('id', $hid);
		
		return $form->output('Edit Hours');
	}
	
	/** 
	 * Show available Payroll Functions
	 * 
	 * @global string TDTrac Root Link HREF
	 * @return array Formatted HTML
	 */
	public function index() {
		global $MYSQL_PREFIX;
		
		$shows = db_list(get_sql_const('showid'), array(showid, showname));
		$usrs  = db_list(get_sql_const('emps'), array(userid, name));
		
		$list = new tdlist(array('id' => 'hours-index', 'actions' => false, 'icon' => 'add', 'inset' => true));
		$list->setFormat("<a href='/hours/view/type:%s/id:%d/%s'><h3>%s</h3>"
				."<span class='ui-li-count'>$%s</span></a>");
				
		$list->addDivide('Operations');
		
		if ( $this->user->isemp ) { $list->addRaw("<li><a href='/hours/add/own:1/'><h3>Add Your Hours</h3></a></li>"); }
		
		$list->addRaw("<!--".var_export($usrs, true)."-->");
		if ( $this->user->can('addhours') && !$this->user->isemp ) {
			$list->addRaw("<li><a href='/hours/add/'><h3>Add Hours</h3></a></li>");
		}
		if ( $this->user->admin ) {
			$list->addRaw("<li><a href='/hours/remind/'><h3>Send Payroll Reminders</h3></a></li>");
			$list->addDivide('Special Reports');
			$total = get_single("SELECT SUM(h.worked*u.payrate) num FROM `{$MYSQL_PREFIX}hours` h, `{$MYSQL_PREFIX}users` u, `{$MYSQL_PREFIX}shows` s WHERE h.userid = u.userid AND s.showid = h.showid AND closed = 0 AND submitted = 0");
			$list->addRow(array(
					'unpaid',
					0,
					null,
					'View Unpaid Hours',
					number_format($total, 2)
				), null);
		}
		
		if ( $this->user->can('viewhours') ) {
			$list->addDivide('View Hours By Show');
			foreach ( $shows as $show ) {
				$total = get_single("SELECT SUM(h.worked*u.payrate) num FROM `{$MYSQL_PREFIX}hours` h, `{$MYSQL_PREFIX}users` u WHERE h.userid = u.userid AND showid = {$show[0]}");
				$list->addRow(array(
					'show',
					$show[0],
					null,
					$show[1],
					number_format($total, 2)
				), null);
			}
		}
		$list->addDivide('View Hours By Employee');
		foreach ( $usrs as $usr ) {
			if ( $this->user->can('viewhours') || $this->user->id == $usr[0] ) {
				$total = get_single("SELECT SUM(h.worked*u.payrate) num FROM `{$MYSQL_PREFIX}hours` h, `{$MYSQL_PREFIX}users` u WHERE h.userid = u.userid AND u.userid = {$usr[0]}");
				$list->addRow(array(
					'user',
					$usr[0],
					'year:'.date('Y').'/month:'.date('n').'/',
					$usr[1],
					number_format($total, 2)
				), null);
			}
		}
		
		
		return $list->output();
	}
	
	/** 
	 * Show pending Hours
	 * 
	 * @global object MySQL Database Resource
	 * @global string MySQL Table Prefix
	 * @return array Formatted HTML
	 */
	public function view_pending() {
		GLOBAL $db, $MYSQL_PREFIX;
		
		$sql = "SELECT userid, sum(worked) as total FROM `{$MYSQL_PREFIX}hours` WHERE submitted = 0 GROUP BY userid ORDER BY userid";
		$result = mysql_query($sql, $db);
		
		$list = new tdlist(array('id' => 'hours-index', 'actions' => true, 'icon' => 'check', 'inset' => true));
		$list->addAction('hclear');
		$list->setFormat("<a href='/hours/view/type:user/id:%d/'><h3>%s</h3><p>%s</p>"
				."<span class='ui-li-count'>%s</span></a>");
				
		$list->addDivide('Unpaid Hours');
		
		if ( mysql_num_rows($result) < 1 ) {
			$list->addRaw("<li data-theme='a'>No Unpaid Hours Found</li>");
		} else {
			while ( $row = mysql_fetch_array($result) ) {
				$hoursowed = array();
				$sql2 = "SELECT date,worked FROM `{$MYSQL_PREFIX}hours` WHERE submitted = 0 AND userid = {$row['userid']}";
				$result2 = mysql_query($sql2, $db);
				while ( $row2 = mysql_fetch_array($result2) ) {
					$hoursowed[] = "<strong>{$row2['date']} :</strong> {$row2['worked']}";
				}
				$list->addRow(array(
					$row['userid'],
					$this->user->get_name($row['userid']),
					join('<br />', $hoursowed),
					$row['total']
				), $row);
			}
		}
		
		return array_merge($list->output(), array("<br /><br /><a class=\"ajax-email\" data-email='{\"action\": \"hours\", \"id\": \"0\"}' data-role=\"button\" data-theme=\"f\" href=\"#\">E-Mail this Report to Yourself</a>"));
	}
	
	/** 
	 * Show Hours by Show
	 * 
	 * @global object MySQL Database Resource
	 * @global string MySQL Table Prefix
	 * @param integer Show or User ID
	 * @param string Type to display
	 * @return array Formatted HTML
	 */
	public function view_show($id) {
		GLOBAL $db, $MYSQL_PREFIX;
		
		$sql = "SELECT h.*, (h.worked*u.payrate) as amount FROM `{$MYSQL_PREFIX}hours` h, `{$MYSQL_PREFIX}shows` s, `{$MYSQL_PREFIX}users` u WHERE h.showid = s.showid AND h.userid = u.userid AND h.showid = {$id} ORDER BY date DESC";
		$result = mysql_query($sql, $db);
		
		$list = new tdlist(array('id' => 'hours-view', 'actions' => true, 'icon' => 'check', 'inset' => true));
		$list->addAction('hmark');
		$list->setFormat("<a href='/hours/view/type:".(($type=='show')?'user':'show')."/id:%d/'>"
			."<h3>%s</h3><p>%s</p>"
			."<span class='ui-li-count'>$%s</span></a>");
		
		$list->addDivide(get_single("SELECT showname as num FROM `{$MYSQL_PREFIX}shows` WHERE showid = {$id}"));
		
		if ( mysql_num_rows($result) < 1 ) {
			$list->addRaw("<li data-theme='a'>No Hours Found</li>");
		} else {
			while ( $row = mysql_fetch_array($result) ) {
				$extra = "<strong>{$row['date']} :</strong> {$row['worked']}";
				if ( $row['submitted'] == 0 ) {
					$extra .= "&nbsp;&nbsp;<span class='pending'> (PENDING)</span>";
				}
				$list->addRow(array(
					$row['userid'],
					$this->user->get_name($row['userid']),
					$extra,
					number_format($row['amount'],2)
				), $row, array('theme' => (($row['submitted'] == 1)?'c':'b')));
			}
		}
		
		return $list->output();
	}
	
	/** 
	 * Show Hours by Show
	 * 
	 * @global object MySQL Database Resource
	 * @global string MySQL Table Prefix
	 * @param integer Show or User ID
	 * @param string Type to display
	 * @return array Formatted HTML
	 */
	public function view_user($id) {
		GLOBAL $db, $MYSQL_PREFIX, $TDTRAC_DAYRATE;
		
		if ( !isset($this->action['year']) ) { $this->action['year'] = date('Y'); }
		if ( !isset($this->action['month']) ) { $this->action['month'] = date('n'); }
		
		$nextmonth = $this->action['month'] + 1;
		$nextyear = (( $nextmonth > 12 ) ? $this->action['year']+1:$this->action['year']);
		$nextmonth = (( $nextmonth > 12 ) ? 1:$nextmonth);
		
		$html = array();
		
		$sql = "SELECT h.*, showname, (h.worked*u.payrate) as amount "
			. "FROM `{$MYSQL_PREFIX}hours` h, `{$MYSQL_PREFIX}shows` s, `{$MYSQL_PREFIX}users` u "
			. "WHERE h.showid = s.showid AND h.userid = u.userid AND h.userid = {$id} "
			. "AND date >= '{$this->action['year']}-".sprintf('%02d',$this->action['month'])."-01' "
			. "AND date < '{$nextyear}-".sprintf('%02d',$nextmonth)."-01' "
			. "ORDER BY date DESC";
		
		$result = mysql_query($sql);
		$extrainfo = array();
		$theseHighDates = array();
		$theseHighDatesAlt = array();
		
		if ( mysql_num_rows($result) < 1 ) {
			$theseHighDates = False;
			$theseHighDatesAlt = False;
		} else {
			$html[] = "<div id='hours-data' style='display:none;'>";
			while ( $row = mysql_fetch_array($result) ) {
				$html[] = "  <div data-date='{$row['date']}' data-type='".(($TDTRAC_DAYRATE)?"Days":"Hours")."' data-submitted='{$row['submitted']}' data-show='{$row['showname']}' data-worked='{$row['worked']}' data-amount='".number_format($row['amount'],2)."'></div>";
				if ( $row['submitted'] == 0 ) {
					$theseHighDates[] = $row['date'];
				} else {
					$theseHighDatesAlt[] = $row['date'];
				}
			}
			$html[] = "</div>";
		}
		
		$html[] = "<div id='hours-user-show'>";
		$dboxopt = array(
			'useInline' => True,
			'useInlineHideInput' => True,
			'mode' => 'calbox',
			'calHighToday' => false,
			'calShowOnlyMonth' => false,
			'calHighPicked' => false,
			'highDates' => (empty($theseHighDates)?false:$theseHighDates),
			'highDatesAlt' => (empty($theseHighDatesAlt)?false:$theseHighDatesAlt),
			'pickPageOAHighButtonTheme' => 'b',
			'pickPageOHighButtonTheme' => 'e',
			'pickPageButtonTheme' => 'a',
			'defaultPickerValue' => array($this->action['year'],($this->action['month']-1),1)
		);
		
		$html[] = "<input type='date' data-role='datebox' id='hoursview' name='hoursview' data-options='".json_encode($dboxopt)."' />";
		$html[] = "</div>";
		
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
	public function email() {
		GLOBAL $db, $MYSQL_PREFIX, $TDTRAC_DAYRATE;
		if ( ! $this->user->admin ) {
			return false;
		} else {
			$sql  = "SELECT CONCAT(first, ' ', last) as name, u.userid, worked, date, showname, submitted, h.id as hid FROM {$MYSQL_PREFIX}users u, {$MYSQL_PREFIX}shows s, {$MYSQL_PREFIX}hours h WHERE ";
			$sql .= "u.userid = h.userid AND s.showid = h.showid AND h.submitted = 0 ORDER BY last ASC, date DESC";
			
			$result = mysql_query($sql, $db);
			while ( $row = mysql_fetch_array($result) ) {
				$dbarray[$row['name']][] = $row;
			}
			
			$body = "";
			
			$subject = "TDTrac Hours Worked :: Unpaid Hours";
			$headers  = 'MIME-Version: 1.0' . "\r\n";
			$headers .= 'Content-type: text/html; charset=iso-8859-1' . "\r\n";
			
			foreach ( $dbarray as $key => $data ) {
				$body .= "<h2>Hours Worked For {$key}</h2>\n";
				
				$tabl = new tdtable("hours-{$ident}", 'datatable', false);
				$tabl->addHeader(array('Date', 'Show', (($TDTRAC_DAYRATE)?"Days":"Hours")." Worked"));
				$tabl->addNumber((($TDTRAC_DAYRATE)?"Days":"Hours")." Worked");
				
				foreach ( $data as $num => $line ) {
					$tabl->addRow(array($line['date'], $line['showname'], $line['worked']), $line);
				}
				
				$body .= $tabl->output(true);
			}
			
			return mail($this->user->email, $subject, $body, $headers);
		}
	}
	
	/**
	 * Show hours reminder email options form
	 * 
	 * @global string MySQL Table Prefix
	 * @global string Site address for links
	 * @return array HTML output
	 */
	private function remind_form() {
		GLOBAL $MYSQL_PREFIX, $TDTRAC_SITE;
		$sql = "SELECT CONCAT(first, ' ', last) as name, userid FROM {$MYSQL_PREFIX}users WHERE payroll = 1 AND active = 1 ORDER BY last DESC";
		
		$form = new tdform(array( 'action' => "{$TDTRAC_SITE}json/email/base:hours/type:remind/id:0/", 'id' => 'hours-remind-form'));
		
		$fesult = $form->addDate(array('name' => 'duedate', 'label' => 'Hours Due Date', 'placeholder' => 'Hours Due Date'));
		$fesult = $form->addDate(array('name' => 'sdate', 'label' => 'Start Date', 'placeholder' => 'Start of Pay Period'));
		$fesult = $form->addDate(array('name' => 'edate', 'label' => 'End Date', 'placeholder' => 'End of Pay Period'));
		
		$listofnames = db_list($sql, array('userid', 'name'));
		
		$fesult = $form->addMultiCheck(array(
			'name' => 'toremind',
			'label' => 'Employees to remind',
			'value' => $listofnames
		));
		
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
	public function remind_send() {
		GLOBAL $db, $MYSQL_PREFIX, $TDTRAC_SITE;
		foreach ( $_REQUEST['toremind'] as $remid ) {
			$this->remind_email(
				intval($remid), 
				mysql_real_escape_string($_REQUEST['duedate']),
				mysql_real_escape_string($_REQUEST['sdate']),
				mysql_real_escape_string($_REQUEST['edate'])
			);
		}
		return true;
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
		
		mail($sendto, $subject, $body, $headers);
	}
}
?>
