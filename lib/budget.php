<?php
/**
 * TDTrac Budget Functions
 * 
 * Contains all budget related functions.
 * Data hardened as of 1.3.1
 *  
 * @package tdtrac
 * @version 4.0.0
 * @author J.T.Sage <jtsage@gmail.com>
 * @since 0.0.9a
 */

/**
 * BUDGET Module
 *  Allows per-show budget tracking
 * 
 * @package tdtrac
 * @version 4.0.0
 * @since 2.0.0
 * @author J.T.Sage <jtsage@gmail.com>
 */
class tdtrac_budget {
	
	/** @var array Parsed query string */
	private $action = array();
	
	/** @var array Formatted HTML */
	private $html = array();
	
	/** @var string Page Title */
	private $title = "Budget";
	
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
	}
	
	/**
	 * Output todo list operation
	 * 
	 * @return void
	 */
	public function output() {
		global $HEAD_LINK, $CANCEL, $TEST_MODE;		
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
								$HEAD_LINK = array("budget/add/show:{$this->action['id']}/", 'plus', 'Add Item'); 
							}
							if ( !isset($this->action['cat']) ) {
								$this->html = $this->view_show($this->action['id']);
							} else {
								$this->html = $this->view_cat($this->action['id'], $this->action['cat']);
							}
						} elseif ( $this->action['type'] == 'pending' ) {
							$this->html = $this->view_pending();
						} elseif ( $this->action['type'] == 'reimb' ) {
							$this->html = $this->view_reimb($this->action['id']);
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
						$HEAD_LINK = array("budget/add/", 'plus', 'Add Item'); 
					}
					$this->html = $this->showlist();
				} break;
		}
		makePage($this->html, $this->title, $this->sidebar());
	} // END OUTPUT FUNCTION
	
	/**
	 * Show a list of shows / options (index)
	 * 
	 * @global resource Database
	 * @global string MySQL Table prefix
	 * @return array Formatted HTML
	 */
	private function showlist() {
		GLOBAL $db, $MYSQL_PREFIX, $TDTRAC_SITE;
		$sql = "SELECT showid, showname FROM `{$MYSQL_PREFIX}shows` WHERE closed = 0 ORDER BY created DESC";
		
		$list = new tdlist(array('id' => 'budget-showlist', 'actions' => true, 'icon' => 'plus', 'inset' => true));
		$list->addAction('badd');
		$list->setFormat("<a href='{$TDTRAC_SITE}budget/view/type:show/id:%d/'><h3>%s</h3>"
				."<p><strong>Budget Expense:</strong> $%s"
				."<br /><strong>Labor Expense:</strong> $%s"
				."</p><span class='ui-li-count'>$%s</span></a>");
				
		$list->addDivide('Show Reports');
		$result = mysqli_query($db, $sql);
		while ( $row = mysqli_fetch_array($result) ) {
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
		$list->addRaw("<li data-theme='a'><a href='{$TDTRAC_SITE}budget/rcpt/'><h3>All Pending Reciepts</h3><span class='ui-li-count'>{$rrpt}</span></a></li>");
		$list->addRaw("<li data-theme='a'><a href='{$TDTRAC_SITE}budget/view/type:reimb/id:0/'><h3>All Pending Reimbursment</h3><span class='ui-li-count'>{$allr}</span></a></li>");
		$list->addRaw("<li data-theme='a'><a href='{$TDTRAC_SITE}budget/view/type:pending/id:0/'><h3>All Pending Payment</h3><span class='ui-li-count'>{$allp}</span></a></li>");
		$list->addRaw("<li data-theme='a'><a href='{$TDTRAC_SITE}budget/view/type:reimb/id:{$this->user->id}/'><h3>Your Reimbursments</h3><span class='ui-li-count'>{$your}</span></a></li>");
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
		GLOBAL $TDTRAC_SITE;
		$html[] = "<div id='rcptbox'>";
		$html[] = "<a href='{$TDTRAC_SITE}rcpt.php?imgid={$num}&amp;hires' target='_blank'><img id='rcptimg' src='/rcpt.php?imgid={$num}' /></a>";
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
		GLOBAL $db, $MYSQL_PREFIX, $TDTRAC_SITE;
		$sql = "SELECT added, imgid FROM `{$MYSQL_PREFIX}rcpts` WHERE handled = 0";
		$result = mysqli_query($db, $sql);
		
		if ( mysqli_num_rows($result) < 1 ) {
			return error_page("No unhandled receipts");
		} else {
			$list = new tdlist(array('id' => 'rcpt_list', 'inset' => true));
			$list->setFormat("<a href='{$TDTRAC_SITE}budget/add/rcpt:%d/'><img src='{$TDTRAC_SITE}rcpt.php?imgid=%d' /><h3>Recieved: %s</h3></a>");
			
			while ( $row = mysqli_fetch_array($result) ) {
				$list->addRow(array($row['imgid'], $row['imgid'], $row['added']));
			}
			return $list->output();
		}
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
		$form = new tdform(array( 'action' => "{$TDTRAC_SITE}json/save/base:budget/id:0/", 'id' => 'budget-add-form'));
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
		$fesult = $form->addDate(array('name' => 'date', 'label' => 'Date', 'placeholder' => 'Date of transaction'));
		$fesult = $form->addText(array('name' => 'dscr', 'label' => 'Description', 'placeholder' => 'Description of Purchase'));
		$fesult = $form->addMoney(array('name' => 'price', 'label' => 'Price', 'placeholder' => 'Amount, minus Tax'));
		$fesult = $form->addSection('open', 'Extra Options');
		$fesult = $form->addMoney(array('name' => 'tax', 'label' => 'Tax', 'placeholder' => 'Tax, if any', 'require' => false));
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
		$result = mysqli_query($db, $sql);
		$row = mysqli_fetch_array($result);
		
		$form = new tdform(array('action' => "{$TDTRAC_SITE}json/save/base:budget/id:{$id}/", 'id' => 'todo-edit-form'));
		
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
	 * Logic to display a category line (in full show view)
	 * 
	 * @param string Category Name
	 * @param double Category Total Price
	 * @param array Html For Row
	 * @param integer Show ID
	 * @param bool Use a pre-formatted link
	 * @return array Formatted HTML
	 */
	private function make_row($cat, $price, $html, $showid, $link = true, $theme = "a") {
		GLOBAL $TDTRAC_SITE;
		$html[] = "</tbody></table>";
		if ( $link ) { array_unshift($html, "<a href='{$TDTRAC_SITE}budget/view/type:show/id:{$showid}/cat:{$cat}/'>"); }
		$list = new tdlist(array('id' => "b-view-{$cat}", 'inset' => true));
		$list->addRaw("<li data-role='list-divider'>{$cat}<span class='ui-li-count'>$".number_format($price, 2)."</span></li>");
		$list->addRaw("<li data-theme='{$theme}'>".join($html).(($link)?"</a>":"")."</li>");
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
		GLOBAL $db, $MYSQL_PREFIX, $HEAD_LINK, $TDTRAC_SITE;
		$sql = "SELECT * FROM `{$MYSQL_PREFIX}budget` WHERE id = '".intval($id)."' LIMIT 1";
		$result = mysqli_query($db, $sql);
		$list = new tdlist(array('id' => 'b-view-item', 'inset' => 'true'));
		
		$list->setFormat("%s<span class='ui-li-count'>%s</span>");
		
		if ( mysqli_num_rows($result) < 0 ) {
			return error_page('Budget Item Not Found!');
		} else {
			if ( $this->user->can('editbudget') ) {
				$HEAD_LINK = array('budget/edit/id:'.$id.'/', 'grid', 'Edit Item'); 
			}
			$row = mysqli_fetch_array($result);
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
				$temp = "<div id='rcptbox'><a href='{$TDTRAC_SITE}rcpt.php?imgid={$row['imgid']}&amp;hires' target='_blank'><img src='{$TDTRAC_SITE}rcpt.php?imgid={$row['imgid']}' /></a></div>";
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
		$sql = "SELECT * FROM `{$MYSQL_PREFIX}budget` WHERE showid = '".intval($showid)."' AND category = '".mysqli_real_escape_string($db, $cat)."'";
		$result = mysqli_query($db, $sql);
		
		$list = new tdlist(array('id' => 'cat-view', 'inset' => true));
		    
		$list->setFormat("<a href='#' class='budg-menu' data-done='0' data-recid='%d' data-edit='".($this->user->can('editbudget')?'true':'false')."'><h3>%s</h3>"
			."<p><strong>Vendor:</strong> %s"
			."<br /><strong>Other Info:</strong> %s</p>"
			."<span class='ui-li-count'>$%s</span></a>");
		
		
		while ( $item = mysqli_fetch_array($result) ) {
			$theme = 'a';
			$extra = array();
			if ( $item['tax'] > 0 ) { $extra[] = "Taxed ($".number_format($item['tax'],2).")"; }
			if ( $item['pending'] ) { $theme = 'e'; $extra[] = "<span style='color: red'>Pending Payment</span>"; }
			if ( $item['needrepay'] ) {
				if ( $item['gotrepay'] ) { 
					$extra[] = "Reimbursed"; 
					$theme = 'c';
				} else {
					$theme = 'e'; 
					if ( $item['payto'] > 0 ) { 
						$extra[] = "<strong>" . $this->user->get_name($item['payto']) . " Needs Reimbursment</strong>";
					} else {
						$extra[] = "<strong>Needs Reimbursed</strong>";
					}
				}
			}
			if ( $item['imgid'] > 0 ) { $extra[] = "Has Receipt"; }
				
			$list->addRow(
				array(
					$item['id'],
					$item['dscr'],
					$item['vendor'],
					join(', ', $extra),
					number_format($item['tax'] + $item['price'], 2)
				),
				$item,
				array(
					"theme" => $theme
				)
			);
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
		$show_res = mysqli_query($show_sql, $db);
		$row = mysqli_fetch_array($show_res);
		$showname = $row['showname'];
		$tableopen = "<table style='width:80%'><thead><tr><td style='width:20%'>Vendor</td><td style='width:40%'>Description</td><td style='text-align: right; width:10%'>Amount</td><td></td></thead><tbody style='font-weight:normal'>";
		
		$sql_exp = "SELECT * FROM {$MYSQL_PREFIX}budget WHERE showid = {$row['showid']}{$sqlwhere} ORDER BY category ASC, date ASC, vendor ASC";
		$res_exp= mysqli_query($db, $sql_exp); 
		
		$lastcat = ''; $subtot = 0; $total = 0;
		if ( mysqli_num_rows($res_exp) < 1 ) {
			$html[] = "<p>No Budget Items Found</p>";
		} else {
			$thisCatHtml = array($tableopen);
			
			while ( $item = mysqli_fetch_array($res_exp) ) {
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
		$res_pay = mysqli_query($db, $sql_pay);
		
		if ( mysqli_num_rows($res_pay) > 0 ) {
			$subtot = 0;
			$lastcat = 'Payroll';
			$thisCatHtml = array("<table style='width:80%'><thead><tr><td>Employee</td><td>Amount</td></thead><tbody style='font-weight:normal'>");
						
			while ( $pay = mysqli_fetch_array($res_pay) ) {
				$thisCatHtml[] = "<tr><td>{$pay['name']}</td><td style='align:right'>$".number_format($pay['price'], 2)."</td></tr>";
				$total += $pay['price'];
				$subtot += $pay['price'];
			}
			
			$html = array_merge($html, $this->make_row($lastcat, $subtot, $thisCatHtml, $showid, false));
		}
		array_unshift($html, "<h3>{$showname} ($".number_format($total, 2).")</h3>");
		return array_merge($html, array("<br /><br /><a class=\"ajax-email\" data-email='{\"action\": \"budget\", \"id\": \"{$showid}\"}' data-role=\"button\" data-theme=\"d\" href=\"#\">E-Mail this Report to Yourself</a>"));
	}
	
	/* 
	 * View a pending payment items
	 * 
	 * @global resouce Datebase Resource
	 * @global string MySQL Table Prefix
	 * @return array Formatted HTML
	 */
	private function view_pending() {
		GLOBAL $db, $MYSQL_PREFIX;
		$sql = "SELECT b.*, showname FROM `{$MYSQL_PREFIX}budget` b, `{$MYSQL_PREFIX}shows` s WHERE s.showid = b.showid AND b.pending = 1";
		$result = mysqli_query($db, $sql);
		
		$list = new tdlist(array('id' => 'cat-view', 'inset' => true));
		    
		$list->setFormat("<a href='#' class='budg-menu' data-done='0' data-recid='%d' data-edit='".($this->user->can('editbudget')?'true':'false')."'><h3>%s</h3>"
			."<p><strong>Show:</strong> %s"
			."<p><strong>Category :: Vendor:</strong> %s :: %s"
			."<br /><strong>Other Info:</strong> %s</p>"
			."<span class='ui-li-count'>$%s</span></a>");
		
		if ( mysqli_num_rows($result) == 0 ) {
			$list->addRaw("<li data-theme='a'>No Items Found</li>");
		}
		
		while ( $item = mysqli_fetch_array($result) ) {
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
					$item['showname'],
					$item['category'],
					$item['vendor'],
					join(', ', $extra),
					number_format($item['tax'] + $item['price'], 2)
				), $item);
		}
		
		return array_merge(array("<h3>All Pending Payment</h3>"), $list->output());
	}
	
	/* 
	 * View a reimbusment pending items
	 * 
	 * @param integer User ID
	 * @global resouce Datebase Resource
	 * @global string MySQL Table Prefix
	 * @return array Formatted HTML
	 */
	private function view_reimb($id) {
		GLOBAL $db, $MYSQL_PREFIX;
		if ( $id == 0 ) {
			$sql = "SELECT b.*, showname FROM `{$MYSQL_PREFIX}budget` b, `{$MYSQL_PREFIX}shows` s WHERE s.showid = b.showid AND b.needrepay = 1 AND b.gotrepay = 0";
			$extratitle = "ALL";
		} else {
			$sql = "SELECT b.*, showname FROM `{$MYSQL_PREFIX}budget` b, `{$MYSQL_PREFIX}shows` s WHERE s.showid = b.showid AND b.needrepay = 1 AND b.gotrepay = 0 AND b.payto = ".intval($id);
			$extratitle = $this->user->get_name($id);
		}
		$result = mysqli_query($sql, $db);
		
		$list = new tdlist(array('id' => 'cat-view', 'inset' => true));
		    
		$list->setFormat("<a href='#' class='budg-menu' data-done='0' data-recid='%d' data-edit='".($this->user->can('editbudget')?'true':'false')."'><h3>%s</h3>"
			."<p><strong>Show:</strong> %s"
			."<p><strong>Category :: Vendor:</strong> %s :: %s"
			."<br /><strong>Other Info:</strong> %s</p>"
			."<span class='ui-li-count'>$%s</span></a>");
		
		if ( mysqli_num_rows($result) == 0 ) {
			$list->addRaw("<li data-theme='a'>No Items Found</li>");
		}
		
        while ( $item = mysqli_fetch_array($result) ) {
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
					$item['showname'],
					$item['category'],
					$item['vendor'],
					join(', ', $extra),
					number_format($item['tax'] + $item['price'], 2)
				), $item);
		}
		
		
		return array_merge(array("<h3>Reimbursment Items - {$extratitle}</h3>"), $list->output());
	}
	
	/**
	 * Send budget via email
	 * 
	 * @param integer Show ID for budget
	 * @global object Database connection
	 * @global string MySQL Table Prefix
	 * @return void
	 */
	public function email($showid) {
		GLOBAL $db, $MYSQL_PREFIX;
		$sql = "SELECT * FROM {$MYSQL_PREFIX}shows WHERE showid = {$showid}";
		$result = mysqli_query($db, $sql); 
		$body = "";
		$row = mysqli_fetch_array($result);
		$body .= "<h2>{$row['showname']}</h2><p><ul>\n";
		$body .= "<li><strong>Company</strong>: {$row['company']}</li>\n";
		$body .= "<li><strong>Venue</strong>: {$row['venue']}</li>\n";
		$body .= "<li><strong>Dates</strong>: {$row['dates']}</li>\n";
		$body .= "</ul></p>\n";
	
		$subject = "TDTrac Budget: {$row['showname']}";
		$headers  = 'MIME-Version: 1.0' . "\r\n";
		$headers .= 'Content-type: text/html; charset=iso-8859-1' . "\r\n";
	
		$body .= "<h2>Materials Expenses</h2>\n";
		$body .= "<table><tr><th>Date</th><th>Vendor</th><th>Category</th><th>Description</th><th>Price</th><th>Tax</th></tr>";
		
		$sql = "SELECT * FROM {$MYSQL_PREFIX}budget WHERE showid = {$showid} ORDER BY category ASC, date ASC, vendor ASC";
		$result = mysqli_query($sql, $db); 
		
		while ( $exp = mysqli_fetch_array($result) ) {
			$body .= sprintf("<tr><td>%s</td><td>%s</td><td>%s</td><td>%s</td><td>%s</td><td>%s</td></tr>",
				$exp['date'],
				$exp['vendor'],
				$exp['category'],
				$exp['dscr'],
				number_format($exp['price'],2),
				number_format($exp['tax'],2)
			);
		}
		$body .= "</table>";
	
		return mail($this->user->email, $subject, $body, $headers);
	}
	
	/**
	 * View sidebar of budget
	 * 
	 * @global string MySQL Table Prefix
	 * @return array HTML Output
	 */
	private function sidebar() {
		GLOBAL $MYSQL_PREFIX, $TDTRAC_SITE;
	
		$budg_open = number_format(get_single("SELECT SUM(price) as num FROM `{$MYSQL_PREFIX}budget` b, `{$MYSQL_PREFIX}shows` s WHERE b.showid = s.showid AND s.closed = 0"),0);
		$budg_all  = number_format(get_single("SELECT SUM(price) as num FROM `{$MYSQL_PREFIX}budget` WHERE 1"),0);
		$budg_pend = number_format(get_single("SELECT SUM(price) as num FROM `{$MYSQL_PREFIX}budget` WHERE pending = 1"),0);
		$budg_reim = number_format(get_single("SELECT SUM(price) as num FROM `{$MYSQL_PREFIX}budget` WHERE needrepay = 1 AND gotrepay = 0"),0);
		
		$list = new tdlist(array('id' => 'todo_sidebar', 'actions' => false, 'inset' => true));
		$showsopen = true;
		
		$html = array('<h4 class="intro">Manage Show Budgets</h4>');
		
		$list->setFormat("%s");
		$list->addRow("<h3>Open Shows</h3><p>Expenses for open shows</p><p class='ui-li-count'>\${$budg_open}</p></h3>");
		$list->addRow("<h3>All Shows</h3><p>Expenses for all shows</p><p class='ui-li-count'>\${$budg_all}</p></h3>");
		$list->addRow("<h3>Pending Items</h3><p>Expenses pending payment</p><p class='ui-li-count'>\${$budg_pend}</p></h3>");
		$list->addRow("<h3>Reimbursable</h3><p>Pending reimbursmentsp</p><p class='ui-li-count'>\${$budg_reim}</p></h3>");
		if ( $this->action['action'] <> 'add' ) {
			$list->addRaw("<li data-icon='plus'><a href='{$TDTRAC_SITE}budget/add/'><h3>Add Item</h3></a></li>");
		}
		
		
		return array_merge($html,$list->output());
	}
}

?>
