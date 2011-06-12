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
		global $HEAD_LINK, $CANCEL, $TEST_MODE;
		if ( !$this->output_json ) { // HTML METHODS			
			switch ( $this->action['action'] ) {
				case "add":
					$CANCEL = true;
					$this->title .= "::Add";
					if ( $this->user->can("addbudget") ) {
						if ( !isset($this->action['rcpt']) || !is_numeric($this->action['rcpt']) ) {
							$this->html = $this->add_form();
						} else {
							$this->html = $this->add_form(intval($this->action['rcpt']));
						}
					} else {
						$this->html = error_page('Access Denied :: You cannot add new budget items');
					} break;
				case "item":
					$this->title .= "::Item";
					if ( $this->user->can("viewbudget") ) {
						if ( !isset($this->action['id']) || !is_numeric($this->action['id']) ) {
							$this->html = error_page('Invalid Page Requested');
						} else {
							$this->html = $this->view_item($this->action['id']);
						}
					} else {
						$this->html = error_page('Access Denied :: You cannot view detailed budget items');
					} break;
				case "view":
					$this->title .= "::View";
					if ( !$this->user->can('viewbudget') ) { 
						$this->html = $this->view($this->user->id, 'reimb');
					} else {
						if ( !isset($this->action['id']) || !is_numeric($this->action['id']) ) {
							$this->html = error_page('Invalid Page Requested');
						} else {
							if ( $this->action['type'] == 'show' ) {
								if ( $this->user->can('addbudget') ) {
									$HEAD_LINK = array("/budget/add/show:{$this->action['id']}/", 'plus', 'Add Item'); 
								}
								if ( !isset($this->action['cat']) ) {
									$this->html = $this->view_show($this->action['id']);
								} else {
									$this->html = $this->view_cat($this->action['id'], $this->action['cat']);
								}
							} else {
								$this->html = error_page('Not Implemented Yet');
							}
						}
					} break;
				case "edit":
					$CANCEL = true;
					$this->title .= "::Edit";
					if ( $this->user->can("editbudget") ) {
						if ( isset($this->action['id']) && is_numeric($this->action['id']) ) {
							$this->html = $this->edit_form(intval($this->action['id']));
						} else {
							$this->html = error_page("Error :: Data Mismatch Detected");
						}
					} else {
						$this->html = error_page('Access Denied :: You cannot add new budget items');
					} break;
				case "rcpt":
					if ( $this->user->can("addbudget") ) {
						$this->html = $this->reciept_list();
					} else {
						$this->html = error_page('Access Denied :: You cannot add new budget items');
					} break;
				default:
					if ( !$this->user->can('viewbudget') ) { 
						$this->html = $this->view($this->user->id, 'reimb');
					} else {
						if ( $this->user->can('addbudget') ) {
							$HEAD_LINK = array("/budget/add/", 'plus', 'Add Item'); 
						}
						$this->html = $this->showlist();
					} break;
			}
			makePage($this->html, $this->title);
		} else { // JSON METHODS
			switch($this->action['action']) {
				case "save":
					if ( $this->action['new'] == 0 ) {
						if ( $this->user->can("editbudget") ) {
							if ( isset($_REQUEST['id']) && is_numeric($_REQUEST['id']) ) {
								$this->json = $this->save(true);
								if ( isset($_SESSION['tdpage']['one']) ) {
									$this->json['location'] = $_SESSION['tdpage']['one'];
								} else {
									$this->json['location'] = "/budget/";
								}
							}
						} else {
							$this->json['success'] = false;
							$this->json['msg'] = "Access Denied";
						}
					} elseif ( $this->action['new'] == 1 ) {
						if ( $this->user->can("addbudget") ) {
							$this->json = $this->save(false);
							if ( isset($_SESSION['tdpage']['one']) ) {
								$this->json['location'] = $_SESSION['tdpage']['one'];
							} else {
								$this->json['location'] = "/budget/";
							}
						} else {
							$this->json['success'] = false;
							$this->json['msg'] = "Access Denied";
						}
					} else {
						$this->json['success'] = false;
						$this->json['msg'] = "Poorly Formed Request";
					} break;
				case "email":
					if ( isset($this->action['id']) && is_numeric($this->action['id']) && $this->user->can('viewbudget') ) {
						$this->email(intval($this->action['id']));
					} else {
						$this->json['success'] = false;
					} break;
				case "delete":
					if ( $TEST_MODE ) {
						$this->json['success'] = true;
					} else {
						if ( isset($this->action['id']) && is_numeric($this->action['id']) && $this->user->can('editbudget') ) {
							$this->delete(intval($this->action['id']));
						} else {
							$this->json['success'] = false;
						}
					} break;
				default:
					$this->json['success'] = false;
					$this->json['msg'] = "Poorly formed request, unknown method";
					break;
			} echo json_encode($this->json);
		}
	} // END OUTPUT FUNCTION
	
	/**
	 * Show a list of shows / options (index)
	 * 
	 * @global resource Database
	 * @global string MySQL Table prefix
	 * @return array Formatted HTML
	 */
	private function showlist() {
		GLOBAL $db, $MYSQL_PREFIX;
		$sql = "SELECT showid, showname FROM `{$MYSQL_PREFIX}shows` WHERE closed = 0 ORDER BY created DESC";
		
		$list = new tdlist(array('id' => 'budget-showlist', 'actions' => true, 'icon' => 'add'));
		$list->addAction('badd');
		$list->setFormat("<a href='/budget/view/type:show/id:%d/'><h3>%s</h3>"
				."<p><strong>Budget Expense:</strong> $%s"
				."<br /><strong>Labor Expense:</strong> $%s"
				."</p><span class='ui-li-count'>$%s</span></a>");
				
		$list->addDivide('Show Reports');
		$result = mysql_query($sql, $db);
		while ( $row = mysql_fetch_array($result) ) {
			$bud = get_single("SELECT SUM(price+tax) num FROM `{$MYSQL_PREFIX}budget` WHERE showid = {$row['showid']}");
			$lab = get_single("SELECT SUM(h.worked*u.payrate) num FROM `{$MYSQL_PREFIX}hours` h, `{$MYSQL_PREFIX}users` u WHERE h.userid = u.userid AND showid = {$row['showid']}");
			$list->addRow(array(
					$row['showid'],
					$row['showname'],
					number_format($bud,2),
					number_format($lab,2),
					number_format($bud + $lab, 2)
				), $row);
		}
		$list->addDivide('Other Reports');
		$allp = '$' . number_format(get_single("SELECT SUM(price+tax) AS num FROM {$MYSQL_PREFIX}budget WHERE pending = 1"),2);
		$allr = '$' . number_format(get_single("SELECT SUM(price+tax) AS num FROM {$MYSQL_PREFIX}budget WHERE needrepay = 1 AND gotrepay = 0"),2);
		$your = '$' . number_format(get_single("SELECT SUM(price+tax) AS num FROM {$MYSQL_PREFIX}budget WHERE needrepay = 1 AND gotrepay = 0 AND payto = {$this->user->id}"),2);
		$rrpt = get_single("SELECT COUNT(imgid) as num FROM `{$MYSQL_PREFIX}rcpts` WHERE handled = 0");
		$list->addRaw("<li data-theme='c'><a href='/budget/rcpt/'><h3>All Pending Reciepts</h3><span class='ui-li-count'>{$rrpt}</span></a></li>");
		$list->addRaw("<li data-theme='c'><a href='/budget/view/type:reimb/id:0/'><h3>All Pending Reimbursment</h3><span class='ui-li-count'>{$allr}</span></a></li>");
		$list->addRaw("<li data-theme='c'><a href='/budget/view/type:pending/id:0/'><h3>All Pending Payment</h3><span class='ui-li-count'>{$allp}</span></a></li>");
		$list->addRaw("<li data-theme='c'><a href='/budget/view/type:reimb/id:{$this->user->id}/'><h3>Your Reimbursments</h3><span class='ui-li-count'>{$your}</span></a></li>");
		return $list->output();
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
	private function reciept_view($num) {
		$html[] = "<div id='rcptbox'>";
		$html[] = "<a href='/rcpt.php?imgid={$num}&amp;hires' target='_blank'><img id='rcptimg' src='/rcpt.php?imgid={$num}' /></a>";
		$html[] = "</div><div data-role='navbar'><ul>";
		$html[] = "<li><a data-id='{$num}' data-rot='270' data-icon='back' data-iconpos='top' class='rcptrot' href='#'>Rotate -90&deg;</a></li>";
		$html[] = "<li><a data-id='{$num}' data-rot='90' data-icon='forward' data-iconpos='top' class='rcptrot' href='#'>Rotate 90&deg;</a></li>";
		$html[] = "</ul></div>";
		return $html;
	}	
	
	/**
	 * Show pending reciepts
	 * 
	 * @global object Database Link
	 * @global string MySQL Table Prefix
	 * @return array HTML Output
	 */
	private function reciept_list() {
		GLOBAL $db, $MYSQL_PREFIX;
		$sql = "SELECT added, imgid FROM `{$MYSQL_PREFIX}rcpts` WHERE handled = 0";
		$result = mysql_query($sql, $db);
		
		if ( mysql_num_rows($result) < 1 ) {
			return error_page("No unhandled receipts");
		} else {
			$list = new tdlist(array('id' => 'rcpt_list', 'inset' => true));
			$list->setFormat("<a href='/budget/add/rcpt:%d/'><img src='/rcpt.php?imgid=%d' /><h3>Recieved: %s</h3></a>");
			
			while ( $row = mysql_fetch_array($result) ) {
				$list->addRow(array($row['imgid'], $row['imgid'], $row['added']));
			}
			return $list->output();
		}
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
		GLOBAL $db, $MYSQL_PREFIX, $TDTRAC_SITE;
		$form = new tdform(array( 'action' => "{$TDTRAC_SITE}budget/save/json:1/new:1/", 'id' => 'budget-add-form'));
		$html = array();
		
		if ( $rcpt > 0 ) {
			$html = $this->reciept_view($rcpt);
		}
		
		$fesult = $form->addDrop(array(
			'name' => 'showid',
			'label' => 'Show',
			'selected' => ((isset($this->action['show']) && is_numeric($this->action['show']))?$this->action['show']:0),
			'options' => db_list(get_sql_const('showid'), array(showid, showname))
		));
		$fesult = $form->addDrop(array(
			'name' => 'category',
			'label' => 'Category',
			'options' => db_list(get_sql_const('category'), 'category'),
			'selected' => '',
			'add' => true
		));
		$fesult = $form->addDrop(array(
			'name' => 'vendor',
			'label' => 'Vendor',
			'add' => true,
			'selected' => '',
			'options' => db_list(get_sql_const('vendor'), 'vendor')
		));
		$fesult = $form->addDate(array('name' => 'date', 'label' => 'Date'));
		$fesult = $form->addText(array('name' => 'dscr', 'label' => 'Description'));
		$fesult = $form->addMoney(array('name' => 'price', 'label' => 'Price'));
		$fesult = $form->addSection('open', 'Extra Options');
		$fesult = $form->addMoney(array('name' => 'tax', 'label' => 'Tax'));
		$fesult = $form->addToggle(array(
			'name' => 'pending',
			'label' => 'Pending Payment',
			'options' => array(array(0,'Paid'),array(1,'Pending'))
		));
		$fesult = $form->addHRadio(array(
			'name' => 'repay',
			'label' => 'Reimbusment',
			'options' => array(array('no','N/A'), array('yes','Pending'), array('paid','Paid')),
			'preset' => 'no'
		));
		$fesult = $form->addDrop(array(
			'name' => 'payto',
			'label' => 'Owed to',
			'title' => 'Reimbursment Payable To',
			'options' => array_merge(array(array(0, 'N/A')), db_list(get_sql_const('reimb'), array('userid', 'name'))),
			'selected' => 0
		));
		$fesult = $form->addSection('closed');
		
		$fesult = $form->addHidden('rcptid', intval($rcpt));
		
		return array_merge($html, $form->output('Add Expense'));
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
		GLOBAL $db, $MYSQL_PREFIX, $TDTRAC_SITE;
		$html = array();
		$sql = "SELECT showname, {$MYSQL_PREFIX}budget.* FROM `{$MYSQL_PREFIX}shows`, `{$MYSQL_PREFIX}budget` WHERE {$MYSQL_PREFIX}budget.id = {$id} AND {$MYSQL_PREFIX}budget.showid = {$MYSQL_PREFIX}shows.showid LIMIT 1;";
		$result = mysql_query($sql, $db);
		$row = mysql_fetch_array($result);
		
		$form = new tdform(array('action' => "{$TDTRAC_SITE}todo/save/json:1/new:0/id:{$id}/", 'id' => 'todo-edit-form'));
		
		$fesult = $form->addDrop(array(
			'name' => 'showid',
			'label' => 'Show',
			'selected' => ((isset($this->action['showid']) && is_numeric($this->action['showid']))?$this->action['showid']:0),
			'options' => db_list(get_sql_const('showid'), array(showid, showname)),
			'selected' => $row['showid']
		));
		$fesult = $form->addDrop(array(
			'name' => 'category',
			'label' => 'Category',
			'options' => db_list(get_sql_const('category'), 'category'),
			'selected' => '',
			'add' => true,
			'selected' => $row['category']
			
		));
		$fesult = $form->addDrop(array(
			'name' => 'vendor',
			'label' => 'Vendor',
			'add' => true,
			'selected' => '',
			'options' => db_list(get_sql_const('vendor'), 'vendor'),
			'selected' => $row['vendor']
		));
		$fesult = $form->addDate(array('name' => 'date', 'label' => 'Date', 'preset' => $row['date']));
		$fesult = $form->addText(array('name' => 'dscr', 'label' => 'Description', 'preset' => $row['dscr']));
		$fesult = $form->addMoney(array('name' => 'price', 'label' => 'Price', 'preset' => $row['price']));
		$fesult = $form->addSection('open', 'Extra Options');
		$fesult = $form->addMoney(array('name' => 'tax', 'label' => 'Tax', 'preset' => $row['tax']));
		$fesult = $form->addToggle(array(
			'name' => 'pending',
			'label' => 'Pending Payment',
			'options' => array(array(0,'Paid'),array(1,'Pending')),
			'preset' => $row['pending']
		));
		$fesult = $form->addHRadio(array(
			'name' => 'repay',
			'label' => 'Reimbusment',
			'options' => array(array('no','N/A'), array('yes','Pending'), array('paid','Paid')),
			'preset' => ((!$row['needrepay'])?'no':(($row['gotrepay'])?'paid':'yes'))
		));
		$fesult = $form->addDrop(array(
			'name' => 'payto',
			'label' => 'Owed to',
			'title' => 'Reimbursment Payable To',
			'options' => array_merge(array(array(0, 'N/A')), db_list(get_sql_const('reimb'), array('userid', 'name'))),
			'selected' => $row['payto']
		));
		$fesult = $form->addSection('closed');
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
				(($_REQUEST['repay'] == "yes" || $_REQUEST['repay'] == 'paid' ) ? "1" : "0"),
				(($_REQUEST['repay'] == "paid") ? "1" : "0"),
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
				(($_REQUEST['repay'] == "yes" || $_REQUEST['repay'] == 'paid' ) ? "1" : "0"),
				(($_REQUEST['repay'] == "paid") ? "1" : "0"),
				intval($_REQUEST['payto']),
				intval($_REQUEST['id'])
			);
		}
		
		$result = mysql_query($sql, $db);
		if ( $result ) {
			return array('success' => true, 'msg' => "Todo Item Saved");
		} else {
			return array('success' => false, 'msg' => "Todo Save Failed".(($TEST_MODE)?mysql_error():""));
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
	 * Logic to display a category line (in full show view)
	 * 
	 * @param string Category Name
	 * @param double Category Total Price
	 * @param array Html For Row
	 * @param integer Show ID
	 * @param bool Use a pre-formatted link
	 * @return array Formatted HTML
	 */
	private function make_row($cat, $price, $html, $showid, $link = true) {
		$html[] = "</tbody></table>";
		if ( $link ) { array_unshift($html, "<a href='/budget/view/type:show/id:{$showid}/cat:{$cat}/'>"); }
		$list = new tdlist(array('id' => "b-view-{$cat}", 'inset' => true));
		$list->addRaw("<li data-theme='c'><h3>{$cat}</h3><span class='ui-li-count'>$".number_format($price, 2)."</span></li>");
		$list->addRaw("<li data-theme='c'>".join($html).(($link)?"</a>":"")."</li>");
		return $list->output();
	}
	
	/*
	 * View and item's detail
	 * 
	 * @param integer Budget Item ID
	 * @global resouce Datebase Resource
	 * @global string MySQL Table Prefix
	 * @global array Last header link
	 * @return array Formatted HTML
	 */
	private function view_item($id) {
		GLOBAL $db, $MYSQL_PREFIX, $HEAD_LINK;
		$sql = "SELECT * FROM `{$MYSQL_PREFIX}budget` WHERE id = '".intval($id)."' LIMIT 1";
		$result = mysql_query($sql, $db);
		$list = new tdlist(array('id' => 'b-view-item', 'inset' => 'true'));
		
		$list->setFormat("%s<span class='ui-li-count'>%s</span>");
		
		if ( mysql_num_rows($result) < 0 ) {
			return error_page('Budget Item Not Found!');
		} else {
			if ( $this->user->can('editbudget') ) {
				$HEAD_LINK = array('/budget/edit/id:'.$id.'/', 'grid', 'Edit Item'); 
			}
			$row = mysql_fetch_array($result);
			$list->addRow(array(get_single("SELECT showname as num FROM `{$MYSQL_PREFIX}shows` WHERE showid = {$row['showid']}"), 'Show'));
			$list->addRow(array($row['category'], 'Category'));
			$list->addRow(array($row['vendor'], 'Vendor'));
			$list->addRow(array('$'.number_format($row['price'],2), 'Amount'));
			if ( $row['tax'] > 0 ) {
				$list->addRow(array('$'.number_format($row['tax'],2), 'Tax'));
			}
			$list->addRow(array($row['date'], 'Date'));
			if ( $row['pending'] == 1 ) {
				$list->addRow(array('This item is Pending Payment', 'NOTICE'));
			}
			if ( $row['needrepay'] == 1 ) {
				if ( $row['gotrepay'] == 1 ) {
					$temp = "This item was reimbursed";
				} else {
					$temp = "This item needs reimbursed";
				}
				if ( $row['payto'] > 0 ) {
					$temp .= " to " . $this->user->get_name($row['payto']);
				}
				$list->addRow(array($temp, 'NOTICE'));
			}
			if ( $row['imgid'] > 0 ) {
				$temp = "<div id='rcptbox'><a href='/rcpt.php?imgid={$row['imgid']}&amp;hires' target='_blank'><img src='/rcpt.php?imgid={$row['imgid']}' /></a></div>";
			} else { $temp = ''; }
			
			return array_merge(array("<h2>{$row['dscr']}</h2>", $temp), $list->output());
		}
	}
	
	/* 
	 * View a budget category
	 * 
	 * @param integer Show ID
	 * @param string Category Name
	 * @global resouce Datebase Resource
	 * @global string MySQL Table Prefix
	 * @return array Formatted HTML
	 */
	private function view_cat($showid, $cat) {
		GLOBAL $db, $MYSQL_PREFIX;
		$showname = get_single("SELECT showname as num FROM `{$MYSQL_PREFIX}shows` WHERE showid = '".intval($showid)."'");
		$sql = "SELECT * FROM `{$MYSQL_PREFIX}budget` WHERE showid = '".intval($showid)."' AND category = '".mysql_real_escape_string($cat)."'";
		$result = mysql_query($sql, $db);
		
		$list = new tdlist(array('id' => 'cat-view', 'inset' => true));
		    
		$list->setFormat("<a href='#' class='budg-menu' data-done='0' data-recid='%d' data-edit='".($this->user->can('editbudget')?'true':'false')."'><h3>%s</h3>"
			."<p><strong>Vendor:</strong> %s"
			."<br /><strong>Other Info:</strong> %s</p>"
			."<span class='ui-li-count'>$%s</span></a>");
		
        
        while ( $item = mysql_fetch_array($result) ) {
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
			if ( $item['imgid'] > 0 ) { $extra[] = "Has Receipt"; }
				
			$list->addRow(array(
					$item['id'],
					$item['dscr'],
					$item['vendor'],
					join(', ', $extra),
					number_format($item['tax'] + $item['price'], 2)
				), $item);
		}
		
		return array_merge(array("<h3>{$showname} - {$cat}</h3>"), $list->output());
	}
	
	/* 
	 * View a show budget
	 * 
	 * @param integer Show ID
	 * @global resouce Datebase Resource
	 * @global string MySQL Table Prefix
	 * @return array Formatted HTML
	 */
	private function view_show($showid) {
		GLOBAL $db, $MYSQL_PREFIX;
		$html = array();
		$show_sql = "SELECT * FROM `{$MYSQL_PREFIX}shows` WHERE showid = '".intval($showid)."'";
		$show_res = mysql_query($show_sql, $db);
		$row = mysql_fetch_array($show_res);
		$showname = $row['showname'];
		$tableopen = "<table style='width:80%'><thead><tr><td style='width:20%'>Vendor</td><td style='width:40%'>Description</td><td style='text-align: right; width:10%'>Amount</td><td></td></thead><tbody style='font-weight:normal'>";
		
		$sql_exp = "SELECT * FROM {$MYSQL_PREFIX}budget WHERE showid = {$row['showid']}{$sqlwhere} ORDER BY category ASC, date ASC, vendor ASC";
		$res_exp= mysql_query($sql_exp, $db); 
		
		$lastcat = ''; $subtot = 0; $total = 0;
		if ( mysql_num_rows($res_exp) < 1 ) {
			$html[] = "<p>No Budget Items Found</p>";
		} else {
			$thisCatHtml = array($tableopen);
			
			while ( $item = mysql_fetch_array($res_exp) ) {
				if ( $lastcat == '' ) { $lastcat = $item['category']; }
				if ( $lastcat <> $item['category'] ) { // NEW CATEGORY
					$html = array_merge($html, $this->make_row($lastcat, $subtot, $thisCatHtml, $showid));
					$subtot = 0;
					$lastcat = $item['category'];
					$thisCatHtml = array($tableopen);
				}
				
				$subtot = $subtot + $item['tax'] + $item['price'];
				$total = $total + $item['tax'] + $item['price'];
				
				$extra = array();
				if ( $item['tax'] > 0 ) { $extra[] = "Tax"; }
				if ( $item['pending'] ) { $extra[] = "<span style='color: red'>Pending</span>"; }
				if ( $item['needrepay'] ) {
					if ( $item['gotrepay'] ) { 
						$extra[] = "Reimbursed"; 
					} else { 
						$extra[] = "<strong>Reimbursment</strong>";
					}
				}
				if ( $item['imgid'] > 0 ) { $extra[] = "Receipt"; }
				
				$thisCatHtml[] = "<tr><td>{$item['vendor']}</td><td>{$item['dscr']}</td><td style='text-align:right'>$".number_format($item['tax'] + $item['price'], 2)."</td><td>".join(", ", $extra)."</td></tr>";
			}
			$html = array_merge($html, $this->make_row($lastcat, $subtot, $thisCatHtml, $showid));
		}
		
		$sql_pay = "SELECT SUM(worked * payrate) as price, CONCAT(first, ' ', last) as name FROM {$MYSQL_PREFIX}users u, {$MYSQL_PREFIX}hours h WHERE u.userid = h.userid AND h.showid = '".intval($showid)."' GROUP BY h.userid ORDER BY last ASC";
		$res_pay = mysql_query($sql_pay, $db);
		
		if ( mysql_num_rows($res_pay) > 0 ) {
			$subtot = 0;
			$lastcat = 'Payroll';
			$thisCatHtml = array("<table style='width:80%'><thead><tr><td>Employee</td><td>Amount</td></thead><tbody style='font-weight:normal'>");
						
			while ( $pay = mysql_fetch_array($res_pay) ) {
				$thisCatHtml[] = "<tr><td>{$pay['name']}</td><td style='align:right'>$".number_format($pay['price'], 2)."</td></tr>";
				$total += $pay['price'];
				$subtot += $pay['price'];
			}
			
			$html = array_merge($html, $this->make_row($lastcat, $subtot, $thisCatHtml, $showid, false));
		}
		array_unshift($html, "<h3>{$showname} ($".number_format($total, 2).")</h3>");
		return array_merge($html, array("<br /><br /><a class=\"ajax-email\" data-email='{\"action\": \"budget\", \"id\": \"{$showid}\"}' data-role=\"button\" data-theme=\"f\" href=\"#\">E-Mail this Report to Yourself</a>"));
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


?>
