<?php
/**
 * TDTrac Payroll Functions
 * 
 * Contains all payroll related functions. 
 * Data hardened
 * @package tdtrac
 * @version 4.0.0
 * @author J.T.Sage <jtsage@gmail.com>
 */

/**
 * PAYROLL Module
 *  Allows configuration of shows
 * 
 * @package tdtrac
 * @version 4.0.0
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
				$CANCEL = true;
				$this->title .= "::Add";
				if ( $this->user->can("addhours") || $this->user->isemp ) {
					$this->html = $this->add_form();
				} else {
					$this->html = error_page('Access Denied :: You cannot add new hour items');
				} break;
			case "find":
				$CANCEL = true;
				$this->title .= "::Search";
				if ( $this->user->admin ) {
					$this->html = $this->find_form();
				} else {
					$this->html = error_page('Access Denied :: You cannot search hours');
				} break;
			case "view":
				if ( $this->user->can('addhours') ) {
					$HEAD_LINK = array('hours/add/', 'plus', 'Add Hours'); 
				}
				$this->title .= "::View";
				$type = (isset($this->action['type']))?$this->action['type']:'user';
				$id = (isset($this->action['id']))?intval($this->action['id']):$this->user->id;
					
				if ( $this->user->isemp ) { $type = 'user'; $id = $this->user->id; }
					
				switch ($type) {
					case 'user':
						$this->html = $this->view_standard(intval($this->action['id']), 'user');
						break;
					case 'show':
						$this->html = $this->view_standard(intval($this->action['id']), 'show');
						break;
					case 'showlist':
						$this->html = $this->view_list(intval($this->action['id']), intval($this->action['listtype']));
						break;
					case 'unpaid':
						$this->html = $this->view_pending();
						break;
					case 'search':
						$this->html = $this->view_search($this->action['start'], $this->action['end'], intval($this->action['listtype']));
						break;
				} break;
			case "edit":
				$CANCEL = true;
				$this->title .= "::Edit";
				if ( $this->user->can("edithours") ) {
					if ( isset($this->action['id']) && is_numeric($this->action['id']) ) {
						$HEAD_LINK = array('#', 'minus', 'Delete Hours', 'hours-delete'); 
						$this->html = $this->edit_form(intval($this->action['id']));
					} else {
						$this->html = error_page('Access Denied :: Data Mismatch');
					}
				} else {
					$this->html = error_page('Access Denied :: You cannot add new hour items');
				} break;
			case "remind":
				$CANCEL = true;
				$this->title .= "::Remind";
				if ( $this->user->admin ) {
					$this->html = $this->remind_form();
				} else {
					$this->html = error_page('Access Denied :: You cannot send reminders');
				} break;
			default:
				if ( $this->user->can('addhours') ) {
					$HEAD_LINK = array('hours/add/', 'plus', 'Add Hours'); 
				}
				$this->html = $this->index();
				break;
		}
		makePage($this->html, $this->title, $this->sidebar());
		
	} // END OUTPUT FUNCTION
	
	/**
	 * Show hours search form
	 * 
	 * @global bool Use daily or hourly pay rates
	 * @global string Site address for links
	 * @return array HTML output
	 */
	private function find_form () {
		GLOBAL $TDTRAC_DAYRATE, $TDTRAC_SITE, $TDTRAC_PAYDAYLIMIT;
		$form = new tdform(array(
			'action' => "{$TDTRAC_SITE}json/nav/id:0/base:hours/type:listview/",
			'id' => 'hours-add-form')
		);
		
		$fesult = $form->addDate(array(
			'name' => 'start',
			'label' => 'Start Date',
			'placeholder' => 'Start Date',
		));
		$fesult = $form->addDate(array(
			'name' => 'end',
			'label' => 'End Date',
			'placeholder' => 'End Date',
		));
		$fesult = $form->addToggle(array(
			'name' => 'listtype',
			'label' => 'View By',
			'options' => array(array(1,'Date'),array(2,'Name')),
			'preset' => 1
		));
		return $form->output('Find Hours');
	}
	
	/**
	 * Show hours add form
	 * 
	 * @global bool Use daily or hourly pay rates
	 * @global string Site address for links
	 * @return array HTML output
	 */
	private function add_form () {
		GLOBAL $TDTRAC_DAYRATE, $TDTRAC_SITE, $TDTRAC_PAYDAYLIMIT;
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

	    $dateopt = ($this->user->groupnum > 2 && $TDTRAC_PAYDAYLIMIT > 0) ? '{"minDays":'.$TDTRAC_PAYDAYLIMIT.', "useNewStyle": true, "pickPageButtonTheme":"c", "mode": "calbox", "useModal": true}':'{"useNewStyle": true, "pickPageButtonTheme":"c", "mode": "calbox", "useModal": true}';

		if ( $this->user->groupnum > 2 && $TDTRAC_PAYDAYLIMIT > 0 ) {
			$fesult = $form->addInfo("<div style='text-align:center'><strong>Note:</strong> You may only add hours that occured within the last 48hrs.  For old payroll, please contact your administrator.</div>");
		}
		$fesult = $form->addDate(array('name' => 'date', 'label' => 'Date', 'placeholder' => 'Date worked', 'options' => $dateopt));
		$fesult = $form->addText(array('name' => 'worked', 'label' => (($TDTRAC_DAYRATE)?"Days":"Hours")." Worked", 'placeholder' => 'Amount Worked'));
		$fesult = $form->addText(array('name' => 'note', 'label' => "Note", 'placeholder' => 'Note'));
		
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
		
		$form = new tdform(array( 'action' => "{$TDTRAC_SITE}json/save/base:hours/id:{$hid}/", 'id' => 'hours-add-form'));
		
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
		$fesult = $form->addText(array('name' => 'note', 'label' => "Note", 'placeholder' => 'Note', 'preset' => $recd['note']));
		
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
		global $MYSQL_PREFIX, $TDTRAC_SITE;
		
		$shows = db_list(get_sql_const('showid'), array(showid, showname));
		$usrs  = db_list(get_sql_const('emps'), array(userid, name));
		
		$list = new tdlist(array('id' => 'hours-index', 'actions' => false, 'icon' => 'add', 'inset' => true));
		$list->setFormat("<a href='{$TDTRAC_SITE}hours/view/type:%s/id:%d/%s'><h3>%s</h3>"
				."<span class='ui-li-count'>$%s</span></a>");
				
		$list->addDivide('Operations');
		
		if ( $this->user->isemp ) { $list->addRaw("<li><a href='{$TDTRAC_SITE}hours/add/own:1/'><h3>Add Your Hours</h3></a></li>"); }
		
		$list->addRaw("<!--".var_export($usrs, true)."-->");
		if ( $this->user->can('addhours') && !$this->user->isemp ) {
			$list->addRaw("<li><a href='{$TDTRAC_SITE}hours/add/'><h3>Add Hours</h3></a></li>");
		}
		if ( $this->user->admin ) {
			$list->addRaw("<li><a href='{$TDTRAC_SITE}hours/remind/'><h3>Send Payroll Reminders</h3></a></li>");
			$list->addDivide('Special Reports');
			$list->addRaw("<li><a href='{$TDTRAC_SITE}hours/find/'><h3>Find by Date Range</h3></a></li>");
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
					'year:'.date('Y').'/month:'.date('n').'/',
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
		GLOBAL $db, $MYSQL_PREFIX, $TDTRAC_SITE;
		
		$sql = "SELECT userid, sum(worked) as total FROM `{$MYSQL_PREFIX}hours` WHERE submitted = 0 GROUP BY userid ORDER BY userid";
		$result = mysql_query($sql, $db);
		
		$list = new tdlist(array('id' => 'hours-index', 'actions' => true, 'icon' => 'check', 'inset' => true));
		$list->addAction('hclear');
		$list->setFormat("<a href='{$TDTRAC_SITE}hours/view/type:user/id:%d/'><h3>%s</h3><p>%s</p>"
				."<span class='ui-li-count'>%s</span></a>");
				
		$list->addDivide('Unpaid Hours');
		
		if ( mysql_num_rows($result) < 1 ) {
			$list->addRaw("<li data-theme='a'>No Unpaid Hours Found</li>");
		} else {
			while ( $row = mysql_fetch_array($result) ) {
				$hoursowed = array();
				$sql2 = "SELECT date,worked,note FROM `{$MYSQL_PREFIX}hours` WHERE submitted = 0 AND userid = {$row['userid']}";
				$result2 = mysql_query($sql2, $db);
				while ( $row2 = mysql_fetch_array($result2) ) {
					$hoursowed[] = "<strong>{$row2['date']} :</strong> {$row2['worked']} <em>{$row2['note']}</em>";
				}
				$list->addRow(array(
					$row['userid'],
					$this->user->get_name($row['userid']),
					join('<br />', $hoursowed),
					$row['total']
				), $row);
			}
		}
		
		return array_merge($list->output(), array("<br /><br /><a class=\"ajax-email\" data-email='{\"action\": \"hours\", \"id\": \"0\"}' data-role=\"button\" data-theme=\"d\" href=\"#\">E-Mail this Report to Yourself</a>"));
	}
	
	/** 
	 * Show Hours by Show or User
	 * 
	 * @global object MySQL Database Resource
	 * @global string MySQL Table Prefix
	 * @param integer Show or User ID
	 * @param string Type to display
	 * @return array Formatted HTML
	 */
	public function view_standard($id, $type='user') {
		GLOBAL $db, $MYSQL_PREFIX, $TDTRAC_DAYRATE, $TDTRAC_SITE;
		
		if ( !isset($this->action['year']) ) { $this->action['year'] = date('Y'); }
		if ( !isset($this->action['month']) ) { $this->action['month'] = date('n'); }
		
		$nextmonth = $this->action['month'] + 1;
		$nextyear = (( $nextmonth > 12 ) ? $this->action['year']+1:$this->action['year']);
		$nextmonth = (( $nextmonth > 12 ) ? 1:$nextmonth);
		
		$html = array();
		
		$thename = ( $type=='user' ) ? $this->user->get_name($id) : get_single("SELECT showname as num FROM `{$MYSQL_PREFIX}shows` WHERE showid = {$id}");
		$html[] = "<h3>Hours For: {$thename}</h3>";
		
		$sql = "SELECT h.*, showname, (h.worked*u.payrate) as amount, h.note as note, CONCAT(u.first, ' ', u.last) as name "
			. "FROM `{$MYSQL_PREFIX}hours` h, `{$MYSQL_PREFIX}shows` s, `{$MYSQL_PREFIX}users` u "
			. "WHERE h.showid = s.showid AND h.userid = u.userid "
			. (($type=='user')?"AND h.userid = {$id} ":"AND s.showid = {$id} ")
			. "AND date >= '{$this->action['year']}-".sprintf('%02d',$this->action['month'])."-01' "
			. "AND date < '{$nextyear}-".sprintf('%02d',$nextmonth)."-01' "
			. "ORDER BY date DESC";
			
		$sqlnext = "SELECT date as num "
			. "FROM `{$MYSQL_PREFIX}hours` h WHERE "
			. (($type=='user')?"h.userid = {$id} ":"h.showid = {$id} ")
			. "AND date >= '{$nextyear}-".sprintf('%02d',$nextmonth)."-01' "
			. "ORDER BY date ASC LIMIT 1";
			
		$sqlprev = "SELECT date as num "
			. "FROM `{$MYSQL_PREFIX}hours` h WHERE "
			. (($type=='user')?"h.userid = {$id} ":"h.showid = {$id} ")
			. "AND date < '{$this->action['year']}-".sprintf('%02d',$this->action['month'])."-01' "
			. "ORDER BY date DESC LIMIT 1";
			
		$nextdate = get_single($sqlnext);
		$prevdate = get_single($sqlprev);
		
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
				$html[] = "  <div data-recid='{$row['id']}' data-note='".htmlspecialchars($row['note'])."' data-date='{$row['date']}' data-type='".(($TDTRAC_DAYRATE)?"Days":"Hours")."' data-submitted='{$row['submitted']}' data-show=\"".(($type=='user')?$row['showname']:$row['name'])."\" data-worked='{$row['worked']}' data-amount='".number_format($row['amount'],2)."'></div>";
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
			'hideInput' => True,
			'mode' => 'calbox',
			'calHighToday' => false,
			'themeDateToday' => 'd',
			'calOnlyMonth' => true,
			'calHighPick' => false,
			'calControlGroup' => false,
			'themeDateHighAlt' => 'e',
			'themeDateHigh' => 'c',
			'highDatesAlt' => (empty($theseHighDates)?false:$theseHighDates),
			'highDates' => (empty($theseHighDatesAlt)?false:$theseHighDatesAlt),
			'defaultValue' => array(intval($this->action['year']),($this->action['month']-1),1)
		);
		
		$html[] = "<input type='date' data-role='datebox' id='hoursview' name='hoursview' data-options='".json_encode($dboxopt)."' />";
		$html[] = "</div>";
		
		$nextlink = ($nextdate==0)?'#':$TDTRAC_SITE . 'hours/view/type:' . $type . '/id:' . $id . '/year:' . date('Y', strtotime($nextdate)) . '/month:' . date('n', strtotime($nextdate)) . '/';
		$prevlink = ($prevdate==0)?'#':$TDTRAC_SITE . 'hours/view/type:' . $type . '/id:' . $id . '/year:' . date('Y', strtotime($prevdate)) . '/month:' . date('n', strtotime($prevdate)) . '/';
		
		$html[] = '<div data-role="controlgroup" data-type="horizontal" style="text-align: center">';
		$html[] = '	<a href="'.$prevlink.'" rel="external" data-role="button" data-theme="'.(($prevdate==0)?'d':'c').'" data-icon="arrow-l">Previous Hours</a>';
		$html[] = '	<a href="'.$nextlink.'" rel="external" data-role="button" data-theme="'.(($nextdate==0)?'d':'c').'" data-icon="arrow-r">Next Hours</a>';
		$html[] = '</div>';
		return $html;
	}
	
	/** 
	 * Show Hours by Date Match in a list
	 * 
	 * @global object MySQL Database Resource
	 * @global string MySQL Table Prefix
	 * @param string Start Date
	 * @param string End Date
	 * @param integer Type to display (by date/name)
	 * @return array Formatted HTML
	 */
	public function view_search($start, $end, $type=1) {
		GLOBAL $db, $MYSQL_PREFIX, $TDTRAC_DAYRATE, $TDTRAC_SITE;
		
		$html = array();
		
		if ( $type == 1 ) {
			$extext = "(by Date)";
			$order = "ORDER BY date ASC, u.last ASC";
		} else if ( $type == 2 ) {
			$extext = "(by Name)";
			$order = "ORDER BY u.last ASC, date ASC";
		}
		
		$thename = get_single("SELECT showname as num FROM `{$MYSQL_PREFIX}shows` WHERE showid = {$id}");
		$html[] = "<h3>Hours For: {$start} - {$end} {$extext}</h3>";
		
		$sql = "SELECT h.*, showname, (h.worked*u.payrate) as amount, h.note as note, CONCAT(u.first, ' ', u.last) as name "
			. "FROM `{$MYSQL_PREFIX}hours` h, `{$MYSQL_PREFIX}shows` s, `{$MYSQL_PREFIX}users` u "
			. "WHERE h.showid = s.showid AND h.userid = u.userid "
			. "AND h.date >= '{$start}' AND h.date <= '{$end}' "
			. $order;
			
		$result = mysql_query($sql);
		
		if ( mysql_num_rows($result) > 0 ) {
			$html[] = "<table style='width:100%' border='1' cellspacing='0'><tr><th>Date</th><th>Show</th><th>Employee</th><th>Hours</th><th>Amount</th><th>Note</th></tr>";
			
			while ( $row = mysql_fetch_array($result) ) {
				$bdon = $row['submitted'] == 0 ? "<strong>":"";
				$bdof = $row['submitted'] == 0 ? "</strong>":"";
				$html[] = "  <tr><td>{$bdon}{$row['date']}{$bdof}</td><td>{$bdon}{$row['showname']}{$bdof}</td><td>{$bdon}{$row['name']}{$bdof}</td><td style='text-align:right'>{$bdon}{$row['worked']}{$bdof}".
				  "</td><td style='text-align:right'>{$bdon}$".number_format($row['amount'],2)."{$bdof}</td><td>{$row['note']}</td></tr>";
			}
			$html[] = "</table>";
		}
		return $html;
	}
	/** 
	 * Show Hours by Show in a list
	 * 
	 * @global object MySQL Database Resource
	 * @global string MySQL Table Prefix
	 * @param integer Show or User ID
	 * @param integer Type to display (by date/name)
	 * @return array Formatted HTML
	 */
	public function view_list($id, $type=1) {
		GLOBAL $db, $MYSQL_PREFIX, $TDTRAC_DAYRATE, $TDTRAC_SITE;
		
		$html = array();
		if ( $type == 1 ) {
			$extext = "(by Date)";
			$order = "ORDER BY date ASC, u.last ASC";
		} else if ( $type == 2 ) {
			$extext = "(by Name)";
			$order = "ORDER BY u.last ASC, date ASC";
		}
		
		$thename = get_single("SELECT showname as num FROM `{$MYSQL_PREFIX}shows` WHERE showid = {$id}");
		$html[] = "<h3>Hours For: {$thename} {$extext}</h3>";
		
		$sql = "SELECT h.*, showname, (h.worked*u.payrate) as amount, h.note as note, CONCAT(u.first, ' ', u.last) as name "
			. "FROM `{$MYSQL_PREFIX}hours` h, `{$MYSQL_PREFIX}shows` s, `{$MYSQL_PREFIX}users` u "
			. "WHERE h.showid = s.showid AND h.userid = u.userid AND h.showid = {$id} "
			. $order;
			
		$result = mysql_query($sql);
		
		if ( mysql_num_rows($result) > 0 ) {
			$html[] = "<table style='width:100%' border='1' cellspacing='0'><tr><th>Date</th><th>Employee</th><th>Hours</th><th>Amount</th><th>Note</th></tr>";
			
			while ( $row = mysql_fetch_array($result) ) {
				$bdon = $row['submitted'] == 0 ? "<strong>":"";
				$bdof = $row['submitted'] == 0 ? "</strong>":"";
				$html[] = "  <tr><td>{$bdon}{$row['date']}{$bdof}</td><td>{$bdon}{$row['name']}{$bdof}</td><td style='text-align:right'>{$bdon}{$row['worked']}{$bdof}".
				  "</td><td style='text-align:right'>{$bdon}$".number_format($row['amount'],2)."{$bdof}</td><td>{$row['note']}</td></tr>";
			}
			$html[] = "</table>";
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
	public function email() {
		GLOBAL $db, $MYSQL_PREFIX, $TDTRAC_DAYRATE;
		if ( ! $this->user->admin ) {
			return false;
		} else {
			$sql  = "SELECT CONCAT(first, ' ', last) as name, u.userid, h.note as note, worked, date, showname, submitted, h.id as hid FROM {$MYSQL_PREFIX}users u, {$MYSQL_PREFIX}shows s, {$MYSQL_PREFIX}hours h WHERE ";
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
				
				$body .= "<table><tr><th>Date</th><th>Show</th><th>".(($TDTRAC_DAYRATE)?"Days":"Hours")." Worked</th></tr>";
				
				foreach ( $data as $num => $line ) {
					$body .= sprintf("<tr><td>%s</td><td>%s</td><td>%s</td><td>%s</td></tr>",
						$line['date'],
						$line['showname'],
						number_format($line['worked'],2),
						$line['note']
					);
				}
				
				$body .= "</table>";
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
	
	/**
	 * View sidebar of hours
	 * 
	 * @global string MySQL Table Prefix
	 * @return array HTML Output
	 */
	private function sidebar() {
		GLOBAL $MYSQL_PREFIX, $TDTRAC_DAYRATE, $TDTRAC_SITE;
	
		$htype = (($TDTRAC_DAYRATE)?"days":"hours");
		$hours_open = number_format(get_single("SELECT SUM(h.worked*u.payrate) as num FROM `{$MYSQL_PREFIX}hours` h, `{$MYSQL_PREFIX}shows` s, `{$MYSQL_PREFIX}users` u WHERE h.showid = s.showid AND h.userid = u.userid AND s.closed = 0"),0);
		$hours_unpaidn = number_format(get_single("SELECT SUM(h.worked) as num FROM `{$MYSQL_PREFIX}hours` h, `{$MYSQL_PREFIX}shows` s WHERE h.showid = s.showid AND h.submitted = 0 AND s.closed = 0"),0);
		$hours_unpaidm = number_format(get_single("SELECT SUM(h.worked*u.payrate) as num FROM `{$MYSQL_PREFIX}hours` h, `{$MYSQL_PREFIX}shows` s, `{$MYSQL_PREFIX}users` u WHERE h.showid = s.showid AND h.userid = u.userid AND h.submitted = 0 AND s.closed = 0"),0);
		$hours_total = number_format(get_single("SELECT SUM(h.worked*u.payrate) as num FROM `{$MYSQL_PREFIX}hours` h, `{$MYSQL_PREFIX}shows` s, `{$MYSQL_PREFIX}users` u WHERE h.showid = s.showid AND h.userid = u.userid"),0);
		
		$list = new tdlist(array('id' => 'todo_sidebar', 'actions' => false, 'inset' => true));
		$showsopen = true;
		
		$html = array('<h4 class="intro">Manage Payroll Records</h4>');
		
		$list->setFormat("%s");
		$list->addRow("<h3>Open Shows</h3><p>Payroll total for open shows</p><p class='ui-li-count'>\${$hours_open}</p>");
		$list->addRow("<h3>Unpaid {$htype}</h3><p>Unpaid {$htype}</p><p class='ui-li-count'>{$hours_unpaidn}</p>");
		$list->addRow("<h3>Unpaid Amount</h3><p>Total unpaid amount</p><p class='ui-li-count'>\${$hours_unpaidm}</p>");
		$list->addRow("<h3>Total Payroll</h3><p>Total payroll amount</p><p class='ui-li-count'>\${$hours_total}</p>");
		if ( $this->action['action'] == 'view' ) {
			$type = (isset($this->action['type']))?$this->action['type']:'user';
			if ( $type == 'show' ) {
				$list->addRaw("<li data-icon='arrow-r'><a href='{$TDTRAC_SITE}hours/view/type:showlist/listtype:1/id:{$this->action['id']}'><h3>View as List (by Date)</h3></a></li>");
				$list->addRaw("<li data-icon='arrow-r'><a href='{$TDTRAC_SITE}hours/view/type:showlist/listtype:2/id:{$this->action['id']}'><h3>View as List (by Name)</h3></a></li>");
			}
			if ( $type == 'showlist' ) {
				$list->addRaw("<li data-icon='arrow-r'><a href='{$TDTRAC_SITE}hours/view/type:show/id:{$this->action['id']}'><h3>View as Calendar</h3></a></li>");
			}
		}
		if ( $this->action['action'] <> 'add' ) {
			$list->addRaw("<li data-icon='plus'><a href='{$TDTRAC_SITE}hours/add/'><h3>Add Item</h3></a></li>");
		}
		
		return array_merge($html,$list->output());
	}
}
?>
