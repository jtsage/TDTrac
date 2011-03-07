<?php
/**
 * TDTrac Budget Functions
 * 
 * Contains all budget related functions.
 * Data hardened as of 1.3.1
 *  
 * @package tdtrac
 * @version 2.0.0
 * @author J.T.Sage <jtsage@gmail.com>
 * @since 0.0.9a
 */

/**
 * BUDGET Module
 *  Allows per-show budget tracking
 * 
 * @package tdtrac
 * @version 2.0.0
 * @since 2.0.0
 * @author J.T.Sage <jtsage@gmail.com>
 */
class tdtrac_budget {
	
	/** @var array Parsed query string */
	private $action = array();
	
	/** @var bool Output format (TURE = json, FALSE = html) */
	private $output_json = false;
	
	/** @var array Formatted HTML */
	private $html = array();
	
	/** @var string Page Title */
	private $title = "Budget";
	
	/** @var array JSON Data */
	private $json = array();
	
	/** @var array List of priorities */
	private $priorities = array(array(0, 'Low'), array(1, 'Normal'), array(2, 'High'), array(3, 'Critical'));
	
	/** 
	 * Create a new instance of the TO-DO module
	 * 
	 * @param object User object
	 * @param array Parsed query string
	 * @return object Budget Object
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
					if ( $this->user->can("addbudget") ) {
						if ( $this->post ) {
							thrower($this->save(false), 'budget/add/');
						} else {
							$this->html = $this->add_form();
						}
					} else {
						thrower('Access Denied :: You cannot add new budget items', 'budget/');
					} break;
				case "reimb":
					$this->title .= " :: Reimbursments Owed";
					if ( $this->user->admin ) {
						$this->html = $this->reimb();
					} else {
						thrower('Access Denied :: You cannot view this list', 'budget/');
					} break;
				case "view":
					$this->title .= " :: View";
					if ( !$this->user->can('viewbudget') ) { 
						$this->html = $this->view($this->user->id, 'reimb');
					} else {
						if ( !isset($this->action['id']) || !is_numeric($this->action['id']) ) { $this->html = error_page('Invalid Page Requested'); }
						elseif ( !isset($this->action['type']) || !in_array($this->action['type'], array('reimb', 'show', 'pending')) ) { $this->html = error_page('Invalid Page Requested'); }
						else {
							$this->html = $this->view($this->action['id'], $this->action['type']);
						}
					}break;
				case "search":
					$this->title .= " :: Search Results";
					if ( $this->user->can("viewbudget") ) {
						if ( $this->post ) {
							if ( isset($_REQUEST['keywords']) && !empty($_REQUEST['keywords']) ) {
								thrower(false, "budget/search/keywords:{$_REQUEST['keywords']}/");
							} else {
								thrower('Error :: Data Mismatch Detected', 'budget/');
							}
						} else {
							if ( isset($this->action['keywords']) && !empty($this->action['keywords']) ) {
								$this->html = $this->search();
							} else {
								thrower('Error :: Data Mismatch Detected', 'budget/');
							}
						}
					} else {
						thrower("Access Denied :: You Cannot Search Budget Items", 'budget/');
					} break;
				case "edit":
					$this->title .= " :: Edit";
					if ( $this->user->can("editbudget") ) {
						if ( $this->post ) {
							if ( isset($_REQUEST['id']) && is_numeric($_REQUEST['id']) ) {
								thrower($this->save(true), "budget/edit/id:".intval($_REQUEST['id'])."/");
							} else {
								thrower('Error :: Data Mismatch Detected', 'budget/');
							}
						} else {
							if ( isset($this->action['id']) && is_numeric($this->action['id']) ) {
								$this->html = $this->edit_form(intval($this->action['id']));
							} else {
								thrower("Error :: Data Mismatch Detected", 'budget/');
							}
						}
					} else {
						thrower('Access Denied :: You Cannot Edit Todo Items', 'budget/');
					} break;
				case "reciept":
					if ( $this->user->can("addbudget") ) {
						if ( $this->post ) {
							thrower($this->reciept_save());
						} else {
							if ( $this->action['type'] == 'rm' && is_numeric($this->action['id']) ) {
								thrower($this->reciept_delete(intval($this->action['id'])));
							} else {
								$this->html = $this->reciept_view();
							}
						}
					} else {
						thrower('Access Denied :: You Cannot Manage Reciepts');
					} break;
				default:
					if ( !$this->user->can('viewbudget') ) { 
						$this->html = $this->view($this->user->id, 'reimb');
					} else {
						$this->html = $this->showlist();
					} break;
			}
			makePage($this->html, $this->title);
		} else { // JSON METHODS
			switch($this->action['action']) {
				case "email":
					if ( isset($this->action['id']) && is_numeric($this->action['id']) && $this->user->can('viewbudget') ) {
						$this->email(intval($this->action['id']));
					} else {
						$this->json['success'] = false;
					} break;
				case "paid":
					if ( isset($this->action['id']) && is_numeric($this->action['id']) && $this->user->can('editbudget') ) {
						$this->got_pending(intval($this->action['id']));
					} else {
						$this->json['success'] = false;
					} break;
				case "reimb":
					if ( isset($this->action['id']) && is_numeric($this->action['id']) && $this->user->can('editbudget') ) {
						$this->got_reimb(intval($this->action['id']));
					} else {
						$this->json['success'] = false;
					} break;
				case "delete":
					if ( isset($this->action['id']) && is_numeric($this->action['id']) && $this->user->can('editbudget') ) {
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
	
	
	private function showlist() {
		GLOBAL $db, $MYSQL_PREFIX;
		$sql  = "SELECT s.showid, s.showname, SUM(price+tax) bud, SUM(h.worked * u.payrate) lab FROM `{$MYSQL_PREFIX}shows` s";
		$sql .= " LEFT JOIN `{$MYSQL_PREFIX}budget` b ON s.showid = b.showid";
		$sql .= " LEFT JOIN `{$MYSQL_PREFIX}hours` h ON s.showid = h.showid";
		$sql .= " LEFT JOIN `{$MYSQL_PREFIX}users` u ON h.userid = u.userid";
		$sql .= " WHERE s.closed = 0 GROUP BY s.showid ORDER BY s.created DESC";
		$list = new tdlist('budget-showlist', true, 'add');
		$list->addAction('badd');
		$list->setFormat("<h3><a href='/budget/view/type:show/id:%d/'>%s</a></h3>"
				."<p><strong>Budget Expense:</strong> $%s"
				."<br /><strong>Labor Expense:</strong> $%s"
				."</p><span class='ui-li-count'>$%s</span>");
				
		$list->addDivide('Show Reports', 'b');
		$result = mysql_query($sql, $db);
		while ( $row = mysql_fetch_array($result) ) {
			$list->addRow(array(
					$row['showid'],
					$row['showname'],
					number_format($row['bud'],2),
					number_format($row['lab'],2),
					number_format($row['bud'] + $row['lab'], 2)
				), $row);
		}
		$list->addDivide('Other Reports', 'b');
		$allp = '$' . number_format(get_single("SELECT SUM(price+tax) AS num FROM {$MYSQL_PREFIX}budget WHERE pending = 1"),2);
		$allr = '$' . number_format(get_single("SELECT SUM(price+tax) AS num FROM {$MYSQL_PREFIX}budget WHERE needrepay = 1 AND gotrepay = 0"),2);
		$your = '$' . number_format(get_single("SELECT SUM(price+tax) AS num FROM {$MYSQL_PREFIX}budget WHERE needrepay = 1 AND gotrepay = 0 AND payto = {$this->user->id}"),2);
		$list->addRaw("<li data-theme='c'><h3><a href='/budget/view/type:reimb/id:0/'>All Pending Reimbursment</a></h3><span class='ui-li-count'>{$allr}</span></li>");
		$list->addRaw("<li data-theme='c'><h3><a href='/budget/view/type:pending/id:0/'>All Pending Payment</a></h3><span class='ui-li-count'>{$allp}</span></li>");
		$list->addRaw("<li data-theme='c'><h3><a href='/budget/view/type:reimb/id:{$this->user->id}/'>Your Reimbursments</a></h3><span class='ui-li-count'>{$your}</span></li>");
		return $list->output();
	}
	/**
	 * Remove a reciept from the database
	 * 
	 * @global object Datebase Link
	 * @global string MySQL Table Prefix
	 * @param integer Reciept ID
	 * @return string Success / Failure Message
	 */
	private function reciept_delete($id) {
		GLOBAL $db, $MYSQL_PREFIX;
		$sql = "DELETE FROM {$MYSQL_PREFIX}rcpts WHERE imgid = {$id} LIMIT 1";
		$result = mysql_query($sql, $db);
		if ( $result ) { 
			return "Reciept Image Deleted"; 
		} else { 
			return "Error :: Operation Failed";
		}
	}
	
	/**
	 * Associate reciept with budget item
	 * 
	 * @global object Datebase Link
	 * @global string MySQL Table Prefix
	 * @return string Success / Failure Message
	 */
	private function reciept_save() {
		GLOBAL $db, $MYSQL_PREFIX;
		$sqla = sprintf("UPDATE `{$MYSQL_PREFIX}budget` SET imgid = %d WHERE id = %d",
			intval($_REQUEST['imgid']),
			intval($_REQUEST['budid'])
		);
		$sqlb = sprintf("UPDATE `{$MYSQL_PREFIX}rcpts` SET `handled` = 1 WHERE imgid = %d",
			intval($_REQUEST['imgid'])
		);
		$result = mysql_query($sqla);
		if ( $result ) {
			$result = mysql_query($sqlb);
			if ( $result ) {
				return "Reciept Associated with Budget Record";
			} else {
				return "Error :: Operation Failed";
			}
		} else { 
			return "Error :: Operation Failed";
		}
	}
	
	/**
	 * View box for existing reciept
	 * 
	 * @global object Database Link
	 * @global string MySQL Table Prefix
	 * @global string Site Address for links
	 * @global array JavaScript
	 * @return array HTML Formatted information
	 */
	private function reciept_view() {
		GLOBAL $db, $MYSQL_PREFIX, $TDTRAC_SITE, $SITE_SCRIPT;
		if ( isset($this->action['num']) && !is_numeric($this->action['num']) ) {
			thrower("Error :: Data Mismatch Detected");
		}
		$html[] = "<div id=\"rcptbox\">";
		$sql = "SELECT count(imgid) as num FROM `{$MYSQL_PREFIX}rcpts` WHERE handled = 0";
		$result = mysql_query($sql, $db);
		$line = mysql_fetch_array($result);
		$total = $line['num'];
		if ( isset($this->action['num']) && ($this->action['num'] > ($total - 1))  ) { thrower("Last Reciept Skipped"); } // Easier to trap later than to suppress skip link on earlier reciept.
		if ( isset($this->action['num']) ) {
			$sql = "SELECT imgid, added FROM {$MYSQL_PREFIX}rcpts WHERE handled = 0 ORDER BY added ASC LIMIT {$this->action['num']},1"; $thisnum = $this->action['num'] + 1;
		} else {
			$sql = "SELECT imgid, added FROM {$MYSQL_PREFIX}rcpts WHERE handled = 0 ORDER BY added ASC LIMIT 1;"; $thisnum = 1;
		}
		$html[] = "<span id=\"rcptnum\">Reciept No. <strong>{$thisnum}</strong> of <strong>{$total}</strong></span><br />";
		$result = mysql_query($sql, $db);
		$line = mysql_fetch_array($result);
		$html[] = "<img id=\"rcptimg\" name=\"rcptimg\" src=\"/rcpt.php?imgid={$line['imgid']}\" alt=\"Reciept Image\" /><br /><span id=\"rcptdate\"><strong>Added:</strong>{$line['added']}</span>";
		$html[] = "<div id=\"rcptcontrol\">";
		$SITE_SCRIPT[] = "$(function() { $('#rot90cc').click( function() {";
		$SITE_SCRIPT[] = "	$('#rcptimg').attr('src', '/rcpt.php?imgid={$line['imgid']}&rotate=270');";
		$SITE_SCRIPT[] = "	$('#rsave').attr('href', '/rcpt.php?imgid={$line['imgid']}&rotate=270&save'); return false; });});";
		$SITE_SCRIPT[] = "$(function() { $('#rot90c').click( function() {";
		$SITE_SCRIPT[] = "	$('#rcptimg').attr('src', '/rcpt.php?imgid={$line['imgid']}&rotate=90');";
		$SITE_SCRIPT[] = "	$('#rsave').attr('href', '/rcpt.php?imgid={$line['imgid']}&rotate=90&save'); return false; });});";
		$SITE_SCRIPT[] = "$(function() { $('#flip').click( function() {";
		$SITE_SCRIPT[] = "	$('#rcptimg').attr('src', '/rcpt.php?imgid={$line['imgid']}&rotate=180');";
		$SITE_SCRIPT[] = "	$('#rsave').attr('href', '/rcpt.php?imgid={$line['imgid']}&rotate=180&save'); return false; });});";

		$html[] = "<a id=\"rot90cc\"  title=\"Rotate Original 90deg Counter-Clockwise\" href=\"#\"><img src=\"/images/rcpt-ccw.jpg\" alt=\"Rotate CCW\" /></a>";
		$html[] = "<a id=\"rsave\"    title=\"Save this Image (new window)\" name=\"rcptsave\" href=\"#\" target=\"_blank\"><img src=\"/images/rcpt-save.jpg\" alt=\"Save\" /></a>";
		$html[] = "<a title=\"Zoom In (new window)\" href=\"/rcpt.php?imgid={$line['imgid']}&amp;hires\" target=\"_blank\"><img src=\"/images/rcpt-zoom.jpg\" alt=\"Zoom\" /></a>";
		$html[] = "<a id=\"flip\"     title=\"Flip Original 180deg\" href=\"#\"><img src=\"/images/rcpt-flip.jpg\" alt=\"Rotate 180\" /></a>";
		$html[] = "<a id=\"rot90c\"   title=\"Rotate Original 90deg Clockwise\" href=\"#\"><img src=\"/images/rcpt-cw.jpg\" alt=\"Rotate CW\" /></a>";
		$html[] = "<br />[-<a title=\"Delete This Reciept\" href=\"{$TDTRAC_SITE}budget/reciept/type:rm/id:{$line['imgid']}/\">Nuke</a>-] [-<a title=\"Skip this Reciept for Now\" href=\"/budget/reciept/num:{$thisnum}/\">Skip</a>-]";
		$html[] = "</div></div>";
		$html = array_merge($html, $this->list_form($line['imgid']));
		$html = array_merge($html, $this->add_form($line['imgid']));
		return $html;
	}	
	
	/**
	 * Show form to associate reciept with current budget record
	 * 
	 * @param integer Reciept ID
	 * @global object Datebase Link
	 * @global string MySQL Table Prefix
	 * @return array HTML Output
	 */
	private function list_form($rcpt = 0) {
		GLOBAL $db, $MYSQL_PREFIX;
		$sql = "SELECT budget.*, showname FROM `{$MYSQL_PREFIX}budget` as budget, `{$MYSQL_PREFIX}shows` as shows WHERE budget.showid = shows.showid AND budget.imgid = 0 AND shows.closed = 0 ORDER BY budget.date DESC, budget.id DESC";
		$result = mysql_query($sql, $db);
		while ( $row = mysql_fetch_array($result) ) {
			$picklist[] = array($row['id'], "{$row['showname']} - {$row['date']} - {$row['vendor']} - \${$row['price']}");
		}
		
		$form = new tdform("{$TDTRAC_SITE}budget/reciept/", "forma", 80, "genform2", 'Add To Budget Item');
		$result = $form->addDrop('budid', 'Item', 'Item to associate with', $picklist, False);
		$result = $form->addHidden('imgid', intval($rcpt));
		return $form->output('Associate');
	}
	
	/**
	 * Logic to mark item reimbursed
	 * 
	 * @param integer Budget item to mark
	 * @global object Database Connection
	 * @global string MySQL Table Prefix
	 * @return void
	 */
	private function got_pending($id) {
		GLOBAL $db, $MYSQL_PREFIX;
		$sql = "UPDATE `{$MYSQL_PREFIX}budget` SET pending = 0 WHERE id = '".intval($id)."'";
		$result = mysql_query($sql, $db);
			if ( $result ) {
				$this->json['success'] = true;
			} else {
				$this->json['success'] = false;
			}
	}
	/**
	 * Logic to mark item reimbursed
	 * 
	 * @param integer Budget item to mark
	 * @global object Database Connection
	 * @global string MySQL Table Prefix
	 * @return void
	 */
	private function got_reimb($id) {
		GLOBAL $db, $MYSQL_PREFIX;
		$sql = "UPDATE `{$MYSQL_PREFIX}budget` SET gotrepay = 1 WHERE id = '".intval($id)."'";
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
		$html[] = "<div class=\"tasks\"><ul class=\"linklist\"><li><h3>Budget Tracking</h3><ul class=\"linklist\">";
		$html[] = "  <li>Manage Budget Items for each show.</li>";
		$html[] = ( $this->user->can('addbudget') ) ? "  <li><a href=\"{$TDTRAC_SITE}budget/add/\">Add Budget Expense</a></li>" : "";
		$html[] = ( $this->user->can('viewbudget') ) ? "  <li><a href=\"{$TDTRAC_SITE}budget/view/\">View Budgets</a></li>" : "";
		$html[] = ( $this->user->can('viewbudget') ) ? "  <li><a href=\"{$TDTRAC_SITE}budget/view/id:0/type:pending/\">View All Payment Pending Items</a></li>" : "";
		$html[] = ( $this->user->can('viewbudget') && !$this->user->isemp ) ? "  <li><a href=\"{$TDTRAC_SITE}budget/view/id:0/type:reimb/\">View All Reimbursments</a></li>" : "";
		$html[] = ( $this->user->can('viewbudget') && !$this->user->isemp ) ? "  <li><a href=\"{$TDTRAC_SITE}budget/view/id:0/type:paid/\">View All Reimbursments (unpaid)</a></li>" : "";
		$html[] = ( $this->user->can('viewbudget') && !$this->user->isemp ) ? "  <li><a href=\"{$TDTRAC_SITE}budget/view/id:0/type:unpaid/\">View All Reimbursments (paid)</a></li>" : "";
		$html[] = "  <li><a href=\"{$TDTRAC_SITE}budget/view/id:0/type:reimb/user:{$this->user->id}/\">View Reimbusments Owed to You (all)</a></li>";
		$html[] = "  <li><a href=\"{$TDTRAC_SITE}budget/view/id:0/type:paid/user:{$this->user->id}/\">View Reimbusments Owed to You (paid)</a></li>";
		$html[] = "  <li><a href=\"{$TDTRAC_SITE}budget/view/id:0/type:unpaid/user:{$this->user->id}/\">View Reimbusments Owed to You (unpaid)</a></li>";
		$html[] = ( $this->user->admin ) 			 ? "  <li><a href=\"{$TDTRAC_SITE}budget/reimb/\">View Reimbusments Owed to Others (unpaid)</a></li>" : "";
		$html[] = "</ul></li></ul></div>";
		return $html;
	}
	
	public function reimb() {
		global $TDTRAC_SITE, $MYSQL_PREFIX, $db;
		$html[] = "<div class=\"tasks\"><ul class=\"linklist\"><li><h3>Reimbursment Tracking</h3><ul class=\"linklist\">";
		$html[] = "  <li>View Reimbursment Items owed to each user.</li>";
		$sql = "SELECT CONCAT(user.first, ' ', user.last) as name, user.userid id, count(b.payto) cnt, sum(b.price + b.tax) amount FROM `{$MYSQL_PREFIX}users` user, `{$MYSQL_PREFIX}budget` b WHERE b.payto = user.userid AND b.needrepay = 1 AND b.gotrepay = 0 GROUP BY b.payto ORDER BY b.payto";
		$result = mysql_query($sql, $db);
		if ( mysql_num_rows($result) < 1 ) {
			$html[] = "  <li>No Users owed reimbursments</li>";
			$html[] = "</ul></li></ul></div>";
		} else {
			$html[] = "</ul></li></ul></div>";
			$tabl = new tdtable('reimb', 'datatable', false);
			$tresult = $tabl->addHeader(array('User', 'Count', 'Amount'));
			$tresult = $tabl->setAlign('Count', 'right');
			$tresult = $tabl->setAlign('Amount', 'right');
			while ( $row = mysql_fetch_array($result) ) {
				$tresult = $tabl->addRow(array(
					"<a href=\"{$TDTRAC_SITE}budget/view/id:0/type:unpaid/user:{$row['id']}/\">{$row['name']}</a>",
					$row['cnt'],
					'$'.number_format($row['amount'],2)
				));
			}
			$html = array_merge($html, $tabl->output(false));
		}
		
		return $html;
	}
		
	/**
	 * Form to add a new budget item
	 * 
	 * @param integer Reciept Number if applicable
	 * @global object Database Connection
	 * @global string MySQL Table Prefix
	 * @global string TDTrac site address, for form actions
	 * @return array HTML Output
	 */
	private function add_form($rcpt = 0) {
		GLOBAL $db, $MYSQL_PREFIX, $TDTRAC_SITE, $SITE_SCRIPT;
		$form = new tdform("{$TDTRAC_SITE}budget/add/", 'budget-add-form', 1, null, 'Add Budget Expense');
		
		$fesult = $form->addDrop('showid', 'Show', 'Show to Charge', db_list(get_sql_const('showid'), array(showid, showname)), False);
		$fesult = $form->addDate('date', 'Date', 'Date of Charge');
		$fesult = $form->addACText('vendor', 'Vendor', 'Vendor for Charge', db_list(get_sql_const('vendor'), 'vendor'));
		$fesult = $form->addACText('category', 'Category', 'Category for Charge', db_list(get_sql_const('category'), 'category'));
		$fesult = $form->addText('dscr', 'Description', 'Description of Charge');
		$fesult = $form->addMoney('price', 'Price', 'Amount of charge, no tax');
		$fesult = $form->addMoney('tax', 'Tax', 'Amount of tax paid, if any');
		$fesult = $form->addCheck('pending', 'Pending Payment');
		$fesult = $form->addCheck('needrepay', 'Reimbursable Charge');
		$fesult = $form->addCheck('gotrepay', 'Reimbursment Recieved');
		$SITE_SCRIPT[] = "$(function() {";
		$SITE_SCRIPT[] = "	$('.drop-payto').hide();";
		$SITE_SCRIPT[] = "	$('.check-needrepay').click(function() { ";
		$SITE_SCRIPT[] = "		if ( $('input[name=needrepay]').is(':checked') ) { $('.drop-payto').show(); }";
		$SITE_SCRIPT[] = "		else { $('.drop-payto').hide(); }";
		$SITE_SCRIPT[] = "});});";
		if ( $this->user->onpayroll ) {
			$result = $form->addDrop('payto', 'Owed to', 'Reimbursment Payable To', array_merge(array(0, 'N/A'), db_list(get_sql_const('reimb'), array('userid', 'name'))), False);
		} else {
			$result = $form->addDrop('payto', 'Owed to', 'Reimbursment Payable To', array_merge(array(0 => 'N/A'), db_list(get_sql_const('reimb'), array('userid', 'name'))), False);
		}
		 
		$fesult = $form->addHidden('rcptid', intval($rcpt));
		return $form->output('Add Expense');
	}

	/**
	 * Form to edit a budget item
	 * 
	 * @param integer Id of Budget Item
	 * @global object Database Connection
	 * @global string MySQL Table Prefix
	 * @global string TDTrac site address, for form actions
	 * @return array HTML Output
	 */
	private function edit_form($id) {
		GLOBAL $db, $MYSQL_PREFIX, $TDTRAC_SITE, $SITE_SCRIPT;
		$html = array();
		$sql = "SELECT showname, {$MYSQL_PREFIX}budget.* FROM `{$MYSQL_PREFIX}shows`, `{$MYSQL_PREFIX}budget` WHERE {$MYSQL_PREFIX}budget.id = {$id} AND {$MYSQL_PREFIX}budget.showid = {$MYSQL_PREFIX}shows.showid LIMIT 1;";
		$result = mysql_query($sql, $db);
		$row = mysql_fetch_array($result);
		if ( $row['imgid'] > 0 ) {
			$html[] = "<div id=\"rcptbox\"><a href=\"/rcpt.php?imgid={$row['imgid']}&amp;hires\" title=\"Zoom In (new window)\" target=\"_blank\"><img src=\"/rcpt.php?imgid={$row['imgid']}\" alt=\"Reciept Image\" /></a></div>\n"; }
		$form = new tdform("{$TDTRAC_SITE}budget/edit/id:{$id}/", 'budget-edit-form', 1, 'genform', 'Edit Budget Item');
		
		$fesult = $form->addDrop('showid', 'Show', 'Show to Charge', db_list(get_sql_const('showid'), array(showid, showname)), False, $row['showid']);
		$fesult = $form->addDate('date', 'Date', 'Date of Charge', $row['date']);
		$fesult = $form->addACText('vendor', 'Vendor', 'Vendor for Charge', db_list(get_sql_const('vendor'), 'vendor'), $row['vendor']);
		$fesult = $form->addACText('category', 'Category', 'Category for Charge', db_list(get_sql_const('category'), 'category'), $row['category']);
		$fesult = $form->addText('dscr', 'Description', 'Description of Charge', $row['dscr']);
		$fesult = $form->addMoney('price', 'Price', 'Amount of charge, no tax', $row['price']);
		$fesult = $form->addMoney('tax', 'Tax', 'Amount of tax paid, if any', $row['tax']);
		$fesult = $form->addCheck('pending', 'Pending Payment', null, $row['pending']);
		$fesult = $form->addCheck('needrepay', 'Reimbursable Charge', null, $row['needrepay']);
		$fesult = $form->addCheck('gotrepay', 'Reimbursment Recieved', null, $row['gotrepay']);
		$SITE_SCRIPT[] = "$(function() {";
		if ( $row['payto'] == 0 ) { $SITE_SCRIPT[] = "	$('.drop-payto').hide();"; }
		$SITE_SCRIPT[] = "	$('.check-needrepay').click(function() { ";
		$SITE_SCRIPT[] = "		if ( $('input[name=needrepay]').is(':checked') ) { $('.drop-payto').show(); }";
		$SITE_SCRIPT[] = "		else { $('.drop-payto').hide(); }";
		$SITE_SCRIPT[] = "});});";
		if ( $this->user->onpayroll ) {
			$result = $form->addDrop('payto', 'Owed to', 'Reimbursment Payable To', array_merge(array(0, 'N/A'), db_list(get_sql_const('reimb'), array('userid', 'name'))), False, $row['payto']);
		} else {
			$result = $form->addDrop('payto', 'Owed to', 'Reimbursment Payable To', array_merge(array(0 => 'N/A'), db_list(get_sql_const('reimb'), array('userid', 'name'))), False, $row['payto']);
		}
		$fesult = $form->addHidden('id', $id);
		return array_merge($html, $form->output('Update Expense'));
	}


	/**
	 * Logic to save a budget item
	 * 
	 * @param bool False on new record, true on overwrite
	 * @global object Database Connection
	 * @global string MySQL Table Prefix
	 * @return string Success / Failure message
	 */
	private function save($exists = false) {
		GLOBAL $db, $MYSQL_PREFIX;
	
		if ( !$exists ) {
			$rcptid = ( $_REQUEST['rcptid'] > 0 && is_numeric($_REQUEST['rcptid'])) ? $_REQUEST['rcptid'] : 0;
			$sqlstring  = "INSERT INTO `{$MYSQL_PREFIX}budget` ";
			$sqlstring .= "( showid, price, tax, imgid, vendor, category, dscr, date, pending, needrepay, gotrepay, payto )";
			$sqlstring .= " VALUES ( '%d','%f','%f','%d','%s','%s','%s','%s','%d','%d','%d', '%d' )";
		
			$sql = sprintf($sqlstring,
				intval($_REQUEST['showid']),
				floatval($_REQUEST['price']),
				(($_REQUEST['tax'] > 0 && is_numeric($_REQUEST['tax'])) ? $_REQUEST['tax'] : 0 ),
				intval($rcptid),
				mysql_real_escape_string($_REQUEST['vendor']),
				mysql_real_escape_string($_REQUEST['category']),
				mysql_real_escape_string($_REQUEST['dscr']),
				make_date($_REQUEST['date']),
				(($_REQUEST['pending'] == "y") ? "1" : "0"),
				(($_REQUEST['needrepay'] == "y") ? "1" : "0"),
				(($_REQUEST['gotrepay'] == "y") ? "1" : "0"),
				intval($_REQUEST['payto'])
			);
			
			if ( $rcptid > 0 ) {
				$sql2 = "UPDATE {$MYSQL_PREFIX}rcpts SET handled = '1' WHERE imgid = '{$rcptid}'";
				$result2 = mysql_query($sql2, $db);
			}
		} else {
			$sqlstring  = "UPDATE `{$MYSQL_PREFIX}budget` SET showid = '%d', price = '%f', tax = '%f' , vendor = '%s', ";
			$sqlstring .= "category = '%s', dscr = '%s' , date = '%s', pending = '%d', needrepay = '%d', gotrepay = '%d', payto = '%d'";
			$sqlstring .= " WHERE id = %d";
			
			$sql = sprintf($sqlstring,
				intval($_REQUEST['showid']),
				floatval($_REQUEST['price']),
				floatval($_REQUEST['tax']),
				mysql_real_escape_string($_REQUEST['vendor']),
				mysql_real_escape_string($_REQUEST['category']),
				mysql_real_escape_string($_REQUEST['dscr']),
				make_date($_REQUEST['date']),
				(($_REQUEST['pending'] == "y") ? "1" : "0"),
				(($_REQUEST['needrepay'] == "y") ? "1" : "0"),
				(($_REQUEST['gotrepay'] == "y") ? "1" : "0"),
				intval($_REQUEST['payto']),
				intval($_REQUEST['id'])
			);
		}
			
		$result = mysql_query($sql, $db);
		
		if ( $result ) {
			return "Expense Saved";
		} else {
			return "Expense Save :: Operation Failed ";
		}
	}

	/**
	 * Logic to delete a budget item
	 * 
	 * @param integer Budget item to remove
	 * @global object Database Connection
	 * @global string MySQL Table Prefix
	 * @return void
	 */
	private function delete($id) {
		GLOBAL $db, $MYSQL_PREFIX;
		$sql = "DELETE FROM `{$MYSQL_PREFIX}budget` WHERE id = '".intval($id)."'";
		$result = mysql_query($sql, $db);
			if ( $result ) {
				$this->json['success'] = true;
			} else {
				$this->json['success'] = false;
			}
	}

	/**
	 * Form to select show budget to view
	 * 
	 * @global object Database Connection
	 * @global string MySQL Table Prefix
	 * @global string TDTrac site address, for form actions
	 * @return array HTML Output
	 */
	private function view_form() {
		GLOBAL $db, $MYSQL_PREFIX, $TDTRAC_SITE;
		$sql = "SELECT showid, showname FROM `{$MYSQL_PREFIX}shows` ORDER BY created DESC";
		$result = mysql_query($sql, $db);
		$form = new tdform("{$TDTRAC_SITE}budget/view/", 'genform', 1, 'genform', 'View Budget');
		
		$result = $form->addDrop('id', 'Show Name', null, db_list(get_sql_const('showidall'), array(showid, showname)), False);
		return $form->output("View Selected");
		
		return $html;
	}
	
	/** 
	 * Logic to display a searched item
	 * 
	 * @global object Database Link
	 * @global string MySQL Table Prefix
	 * @global string TDTrac site address for links
	 * @return array HTML output
	 */
	private function search() {
		GLOBAL $db, $MYSQL_PREFIX, $TDTRAC_SITE;
		$keywords = $this->action['keywords'];
		
		$sqlwhere  = "( category LIKE '%" . mysql_real_escape_string($keywords) . "%' OR "; 
		$sqlwhere .= "vendor LIKE '%" . mysql_real_escape_string($keywords) . "%' OR "; 
		$sqlwhere .= "date = '" . mysql_real_escape_string($keywords) . "' OR "; 
		$sqlwhere .= "dscr LIKE '%" . mysql_real_escape_string($keywords) . "%' )";
		
		$sql = "SELECT * FROM {$MYSQL_PREFIX}budget b, {$MYSQL_PREFIX}shows s WHERE b.showid = s.showid AND {$sqlwhere} ORDER BY b.showid DESC, category ASC, date ASC, vendor ASC";
		$result = mysql_query($sql, $db);
		
		$html[] = "<h3>Search Results</h3>\n";
		if ( mysql_num_rows($result) == 0 ) { return array_merge($html, array("<br /><br /><h4>No Records Found!</h4>")); }
		
		$tabl = new tdtable("searchresult");
		$tabl->addHeader(array('Show', 'Date', 'Category', 'Vendor', 'Description', 'Price', 'Tax'));
		$tabl->addSubtotal('Show');
		$tabl->addCurrency('Price');
		$tabl->addCurrency('Tax');
		$tabl->addAction(array('bpend','breim','rview'));
		if ( $this->user->can("editbudget") ) { $tabl->addAction(array('bedit', 'bdel')); }
		
		while( $line = mysql_fetch_array($result) ) {
			$tabl->addRow(array($line['showname'], $line['date'], $line['category'], $line['vendor'], $line['dscr'], $line['price'], $line['tax']), $line);
		}
		return array_merge($html, $tabl->output(false));
	}

	/**
	 * Logic to display a show's budget
	 * 
	 * @param integer Id of Show
	 * @param string Special Budget type
	 * @global object Database Connection
	 * @global string MySQL Table Prefix
	 * @global bool Daily payrate vs. Hourly Payrate
	 * @global double Default payrate
	 * @global string TDTrac site address, for form actions
	 * @global array JavaScript
	 * @return array Formatted HTML
	 */
	private function view($showid, $type) {
		GLOBAL $db, $MYSQL_PREFIX, $TDTRAC_DAYRATE, $TDTRAC_PAYRATE, $TDTRAC_SITE;
		$list = new tdlist('budget-view', true);
		
		if ( $type == "show" ) {
			$show_sql = "SELECT * FROM `{$MYSQL_PREFIX}shows` WHERE showid = '".intval($showid)."'";
		} else {
			$show_sql = "SELECT * FROM `{$MYSQL_PREFIX}shows` WHERE closed = 0 ORDER BY showid DESC";
		}
		
		switch($type) {
			case "pending":
				$list->addDivide('Pending Payment Items', 'b');
				$sqlwhere = " AND pending = 1";
				break;
			case "reimb":
				$sqlwhere = " AND needrepay = 1";
				if ( $showid > 0 ) {
					$list->addDivide('Your Reimbursment Items', 'b');
					$sqlwhere .= " AND payto = {$showid}";
				} else {
					$list->addDivide('Pending Reimbursment Items', 'b');
					$sqlwhere .= " AND gotrepay = 0";
				}
				break;
			default:
				$sqlwhere = "";
				break;
			}
	
		if ( $this->user->can('editbudget') ) {
			$list->setFormat("<h3><a title='Edit Item' href='/budget/edit/id:%d/'>%s</a></h3>"
				."<p><strong>Vendor:</strong> %s"
				."<br /><strong>Other Info:</strong> %s</p>"
				."<span class='ui-li-count'>$%s</span>");
		} else {
			$list->setFormat("<h3><a data-bid='%d' href='#'>%s</a></h3>"
				."<p><strong>Vendor:</strong> %s"
				."<br /><strong>Other Info:</strong> %s</p>"
				."<span class='ui-li-count'>$%s</span>");
		}
		if ( $this->user->can('editbudget') ) { $list->addAction('bdel'); }
		
		$show_res = mysql_query($show_sql, $db); echo mysql_num_rows($show_res);
		while ( $row = mysql_fetch_array($show_res) ) {
			$sql_exp = "SELECT * FROM {$MYSQL_PREFIX}budget WHERE showid = {$row['showid']}{$sqlwhere} ORDER BY category ASC, date ASC, vendor ASC";
			$res_exp= mysql_query($sql_exp, $db); 
			
			$lastcat = ''; $subtot = 0; $total = 0;
			if ( mysql_num_rows($res_exp) < 1 ) {
				if ( $type == 'show' ) { $list->addDivide('No Budget Items Found', 'a'); }
			} else {
				$list->addRaw("<li data-theme='f'><h3>{$row['showname']}</h3></li>");
				
				while ( $item = mysql_fetch_array($res_exp) ) {
					if ( $lastcat == '' ) { $lastcat = $item['category']; }
					if ( $lastcat <> $item['category'] ) {
						$list->addRaw("<li data-theme='g'><h3>{$lastcat}</h3><span class='ui-li-count'>$".number_format($subtot, 2)."</span></li>");
						$subtot = 0;
						$lastcat = $item['category'];
					}
					
					$subtot = $subtot + $item['tax'] + $item['price'];
					$total = $total + $item['tax'] + $item['price'];
					
					$extra = array();
					if ( $item['tax'] > 0 ) { $extra[] = "Taxed ($".number_format($item['tax'],2).")"; }
					if ( $item['pending'] ) { $extra[] = "<span style='color: red'>Pending Payment</span>"; }
					if ( $item['needrepay'] ) {
						if ( $item['gotrepay'] ) { 
							$extra[] = "Reimbursed"; 
						} else { 
							if ( $item['payto'] > 0 ) { 
								$extra[] = "<strong>" . $this->user->get_name($item['payto']) . " Needs Reimbursment</strong>";
							} else {
								$extra[] = "<strong>Needs Reimbursed</strong>";
							}
						}
					}
					if ( $item['imgid'] > 0 ) { $extra[] = "<a href='/rcpt.php?imgid={$item['imgid']}&amp;hires' target='_blank' data-role='none'>Has Receipt</a>"; }
						
					$list->addRow(array(
							$item['id'],
							$item['dscr'],
							$item['vendor'],
							join(', ', $extra),
							number_format($item['tax'] + $item['price'], 2)
						), $item);
				}
				$list->addRaw("<li data-theme='g'><h3>{$lastcat}</h3><span class='ui-li-count'>$".number_format($subtot, 2)."</span></li>");
				$list->addRaw("<li data-theme='a'><h3>Materials Total</h3><span class='ui-li-count'>$".number_format($total, 2)."</span></li>");
			}
		}
						
		
		if ( $type == "show" ) {
			$subtot = 0;
			
			$sql_pay = "SELECT SUM(worked * payrate) as price, CONCAT(first, ' ', last) as name FROM {$MYSQL_PREFIX}users u, {$MYSQL_PREFIX}hours h WHERE u.userid = h.userid AND h.showid = '".intval($showid)."' GROUP BY h.userid ORDER BY last ASC";
			$res_pay = mysql_query($sql_pay, $db);
			
			while ( $pay = mysql_fetch_array($res_pay) ) {
				$list->addRaw("<li data-theme='c'><h3>{$pay['name']}</h3><span class='ui-li-count'>$".number_format($pay['price'], 2)."</span></li>");
				$total += $pay['price'];
				$subtot += $pay['price'];
			}
			$list->addRaw("<li data-theme='g'><h3>Labor Expense</h3><span class='ui-li-count'>$".number_format($subtot, 2)."</span></li>");
			$list->addRaw("<li data-theme='a'><h3>Total with Labor</h3><span class='ui-li-count'>$".number_format($total, 2)."</span></li>");
		}
		
		if ( $type == "show" ) {
			return array_merge($list->output(), array("<br /><br /><a class=\"ajax-email\" data-email='{\"action\": \"budget\", \"id\": \"{$showid}\"}' data-role=\"button\" data-theme=\"e\" href=\"#\">E-Mail this Report to Yourself</a>"));
		} else {
			return $list->output();
		}
	}

	/**
	 * Send budget via email
	 * 
	 * @param integer Show ID for budget
	 * @global object Database connection
	 * @global string MySQL Table Prefix
	 * @return void
	 */
	private function email($showid) {
		GLOBAL $db, $MYSQL_PREFIX;
		$sql = "SELECT * FROM {$MYSQL_PREFIX}shows WHERE showid = {$showid}";
		$result = mysql_query($sql, $db); 
		$body = "";
		$row = mysql_fetch_array($result);
		$body .= "<h2>{$row['showname']}</h2><p><ul>\n";
		$body .= "<li><strong>Company</strong>: {$row['company']}</li>\n";
		$body .= "<li><strong>Venue</strong>: {$row['venue']}</li>\n";
		$body .= "<li><strong>Dates</strong>: {$row['dates']}</li>\n";
		$body .= "</ul></p>\n";
	
		$subject = "TDTrac Budget: {$row['showname']}";
		$headers  = 'MIME-Version: 1.0' . "\r\n";
		$headers .= 'Content-type: text/html; charset=iso-8859-1' . "\r\n";
	
		$body .= "<h2>Materials Expenses</h2>\n";
		$sql = "SELECT * FROM {$MYSQL_PREFIX}budget WHERE showid = {$showid} ORDER BY category ASC, date ASC, vendor ASC";

		$result = mysql_query($sql, $db); 

		$tabl = new tdtable("budget", 'datatable', false, "");
		$tabl->addHeader(array('Date', 'Vendor', 'Category', 'Description', 'Price', 'Tax'));
		$tabl->addSubtotal('Category');
		$tabl->addCurrency('Price');
		$tabl->addCurrency('Tax');
		while ( $exp = mysql_fetch_array($result) ) {
			$tabl->addRow(array($exp['date'], $exp['vendor'], $exp['category'], $exp['dscr'], $exp['price'], $exp['tax']), $exp);
		}
		$body .= $tabl->output(true);
	
		$result = mail($this->user->email, $subject, $body, $headers);
		if ( $result ) {
			$this->json['success'] = true;
		} else {
			$this->json['success'] = false;
		}
	}
}



/**
 * Check for pending reciepts
 * 
 * @global object Database Link
 * @global string MySQL Table Prefix
 * @global string User Object
 * @global string Site Address for links
 * @return array HTML Formatted information
 */
function reciept_check() {
	GLOBAL $db, $MYSQL_PREFIX, $user, $TDTRAC_SITE;
	$html = "<div class=\"infobox\"><span style=\"font-size: .7em\">";
	if ( $user->id == 1 ) {
		$sql = "SELECT COUNT(imgid) as num FROM `{$MYSQL_PREFIX}rcpts` WHERE handled = 0";
		$result = mysql_query($sql, $db);
		if ( mysql_num_rows($result) > 0 ) {
			$num = mysql_fetch_array($result);
			if ( $num['num'] < 1 ) { return ""; }
			$html .= "You have <strong>{$num['num']}</strong> Unhandled Reciepts Waiting (<a href=\"{$TDTRAC_SITE}budget/reciept/\">[-View-]</a>)";
		}
		$html .= "</span></div>\n";
		return $html;
	} else {
		return "";
	}
}

?>
