<?php
/**
 * TDTrac Todo List Functions
 * 
 * Contains the todo list functions
 * Data hardened since 1.3.1
 * @package tdtrac
 * @version 2.0.0
 * @author J.T.Sage <jtsage@gmail.com>
 * @since 1.3.1
 */

/**
 * TO-DO Module
 *  Allows per-user and per-show task lists
 * 
 * @package tdtrac
 * @version 2.0.0
 * @since 2.0.0
 * @author J.T.Sage <jtsage@gmail.com>
 */
class tdtrac_todo {
	
	/** @var array Parsed query string */
	private $action = array();
	
	/** @var bool Output format (TURE = json, FALSE = html) */
	private $output_json = false;
	
	/** @var array Formatted HTML */
	private $html = array();
	
	/** @var string Page Title */
	private $title = "To-Do Lists";
	
	/** @var array JSON Data */
	private $json = array();
	
	/** @var array List of priorities */
	private $priorities = array(array(0, 'Low'), array(1, 'Normal'), array(2, 'High'), array(3, 'Critical'));
	
	/** 
	 * Create a new instance of the TO-DO module
	 * 
	 * @param object User object
	 * @param array Parsed query string
	 * @return object Todo Object
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
	 * @global array Extra Header Link
	 * @global bool Make 'back' link a 'cancel' link
	 * @global bool Set app into test mode
	 * @return void
	 */
	public function output() {
		global $HEAD_LINK, $CANCEL, $TEST_MODE;
		if ( !$this->output_json ) { // HTML METHODS
			switch ( $this->action['action'] ) {
				case "add":
					$CANCEL = true;
					$this->title .= "::Add";
					if ( $this->user->can("addtodo") ) {
						$this->html = $this->add_form();
					} else {
						$this->html = error_page('Access Denied :: You cannot add new todo items');
					} break;
				case "edit":
					$CANCEL = true;
					$this->title .= "::Edit";
					if ( $this->user->can("edittodo") ) {
						if ( isset($this->action['id']) && is_numeric($this->action['id']) ) {
							$this->html = $this->edit_form(intval($this->action['id']));
						} else {
							$this->html = error_page("Error :: Data Mismatch Detected");
						}
					} else {
						$this->html = error_page('Access Denied :: You Cannot Edit Todo Items');
					} break;
				default:
					$this->title .= "::View";
					if ( $this->user->can('addtodo') ) {
						$HEAD_LINK = array('/todo/add/', 'plus', 'Add Item'); 
					}
					if ( !$this->user->can("viewtodo") ) {
						$this->html = $this->view($this->user->id, 'user');
					} else {
						if ( !isset($this->action['id']) ) {
							$this->html = $this->view(null);	
						} else {
							$type = ( isset($this->action['type']) && ( $this->action['type'] == 'overdue' || $this->action['type'] == 'show' ) ) ? $this->action['type'] : "user";
							$id   = ( isset($this->action['id']) && is_numeric($this->action['id']) ) ? intval($this->action['id']) : 1;
							$this->html = $this->view($id, $type);
						}
					} break;
			}
			makePage($this->html, $this->title);
		} else { // JSON METHODS
			switch($this->action['action']) {
				case "save":
					if ( $this->action['new'] == 0 ) {
						if ( $this->user->can("edittodo") ) {
							if ( isset($_REQUEST['id']) && is_numeric($_REQUEST['id']) ) {
								$this->json = $this->save(true);
								if ( isset($_SESSION['tdpage']['one']) ) {
									$this->json['location'] = $_SESSION['tdpage']['one'];
								} else {
									$this->json['location'] = "/todo/";
								}
							}
						} else {
							$this->json['success'] = false;
							$this->json['msg'] = "Access Denied";
						}
					} elseif ( $this->action['new'] == 1 ) {
						if ( $this->user->can("addtodo") ) {
							$this->json = $this->save(false);
							if ( isset($_SESSION['tdpage']['one']) ) {
								$this->json['location'] = $_SESSION['tdpage']['one'];
							} else {
								$this->json['location'] = "/todo/";
							}
						} else {
							$this->json['success'] = false;
							$this->json['msg'] = "Access Denied";
						}
					} else {
						$this->json['success'] = false;
						$this->json['msg'] = "Poorly Formed Request";
					} break;
				case "mark":
					if ( $TEST_MODE ) {
						$this->json['success'] = true;
					} else {
						if ( isset($this->action['id']) && is_numeric($this->action['id']) ) {
							$this->mark(intval($this->action['id']));
						} else {
							$this->json['success'] = false;
						}
					} break;
				case "email":
					if ( isset($this->action['id']) && is_numeric($this->action['id']) && isset($this->action['type']) ) {
						$this->email();
					} else {
						$this->json['success'] = false;
					} break;
				case "delete":
					if ( $TEST_MODE ) {
						$this->json['success'] = true;
					} else {
						if ( isset($this->action['id']) && is_numeric($this->action['id']) && $this->user->can('edittodo') ) {
							$this->delete(intval($this->action['id']));
						} else {
							$this->json['success'] = false;
						}
					} break;
				default:
					$this->json['success'] = true;
					break;
			} echo json_encode($this->json);
		}
	} // END OUTPUT FUNCTION
	
	/**
	 * Mark a todo item completed
	 * 
	 * @param integer Todo Item ID
	 * @global object DB Resource
	 * @global string MySQL Prefix
	 * @return void
	 */
	private function mark($id) {
		GLOBAL $db, $MYSQL_PREFIX;
		
		$sql  = "UPDATE {$MYSQL_PREFIX}todo SET complete = 1 WHERE id = '".intval($id)."'";
		$result = mysql_query($sql, $db);
		if ( $result ) {
			$this->json['success'] = true;
		} else {
			$this->json['success'] = false;
		}
	}
	
	/**
	 * Delete a todo item
	 * 
	 * @param integer Todo Item ID
	 * @global object DB Resource
	 * @global string MySQL Prefix
	 * @return void
	 */
	 private function delete($id) {
		GLOBAL $db, $MYSQL_PREFIX;
		$sql  = "DELETE FROM {$MYSQL_PREFIX}todo WHERE id = '".intval($id)."'";
		$result = mysql_query($sql, $db);
		if ( $result ) {
			$this->json['success'] = true;
		} else {
			$this->json['success'] = false;
		}
	}
	
	/**
	 * Show Todo Add Form
	 * 
	 * @global string TDTrac Root Link HREF
	 * @return array Formatted HTML
	 */
	private function add_form() {
		global $TDTRAC_SITE;
		$form = new tdform(array('action' => "{$TDTRAC_SITE}todo/save/json:1/new:1/", 'id' => 'todo_add_form'));
		$result = $form->addDrop(array(
			'name' => 'showid', 
			'label' => 'Show',
			'options' => db_list(get_sql_const('showid'), array('showid', 'showname'))
		));
		$result = $form->addDrop(array(
			'name' => 'prio',
			'label' => 'Priority',
			'options' => $this->priorities,
			'selected' => 1
		));
		$result = $form->addDate(array('name' => 'date', 'label' => 'Due Date', 'options' => '{"mode":"calbox", "useModal": true}'));
		$result = $form->addDrop(array(
			'name' => 'assign',
			'label' => 'Assigned To',
			'options' => array_merge(array(array('0', '-unassigned-')), db_list(get_sql_const('todo'), array('userid', 'name')))
		));
		$result = $form->addText(array('name' => 'desc', 'label' => 'Description'));
		return $form->output('Add Todo');
	}
	
	/**
	 * Show Todo Edit Form
	 * 
	 * @param integer ToDo List Entry ID
	 * @global object DB Resource
	 * @global string MySQL Datebase Prefix
	 * @global string TDTrac Root Link HREF
	 * @return array Formatted HTML
	 */
	private function edit_form($id) {
		GLOBAL $db, $MYSQL_PREFIX, $TDTRAC_SITE;
		$sql = "SELECT *, DATE_FORMAT(`due`, '%Y-%m-%d') as duedate FROM `{$MYSQL_PREFIX}todo` WHERE id = {$id}";
		$result = mysql_query($sql, $db);
		$row = mysql_fetch_array($result);
		
		$form = new tdform(array('action' => "{$TDTRAC_SITE}todo/save/json:1/new:0/id:{$id}/", 'id' => 'todo-edit-form'));
		$result = $form->addDrop(array(
			'name' => 'showid', 
			'label' => 'Show', 
			'selected' => $row['showid'], 
			'options' => db_list(get_sql_const('showid'), array('showid', 'showname'))
		));
		$result = $form->addDrop(array(
			'name' => 'prio',
			'label' => 'Priority',
			'selected' => $row['priority'],
			'options' => $this->priorities
		));
		$result = $form->addDate(array('name' => 'date', 'label' => 'Due Date', 'preset' => $row['duedate'], 'options' => '{"mode":"calbox", "useModal": true}'));
		$result = $form->addDrop(array(
			'name' => 'assign',
			'label' => 'Assigned To',
			'selected' => $row['assigned'],
			'options' => array_merge(array(array('0', '-unassigned-')), db_list(get_sql_const('todo'), array('userid', 'name')))
		));
		$result = $form->addText(array('name' => 'desc', 'label' => 'Description', 'preset' => $row['dscr']));
		$result = $form->addToggle(array(
			'name' => 'complete',
			'label' => 'Task Completed',
			'preset' => $row['complete'],
			'options' => array(array(0,'Pending'),array(1,'Complete'))
		));
		$result = $form->addHidden('id', $id);
		return $form->output('Edit Todo');
	}
	
	/**
	 * Todo Item Save Logic
	 * 
	 * @global object Database Link
	 * @global string MySQL Table Prefix
	 * @global bool MySQL DEBUG Status
	 * @return string Success or Failure
	 */
	private function save($exists = false) {
		GLOBAL $db, $MYSQL_PREFIX, $MYSQL_DEBUG;
		if ( !$exists ) {
			$sqlstring  = "INSERT INTO `{$MYSQL_PREFIX}todo` ( showid, priority, due, assigned, dscr )";
			$sqlstring .= " VALUES ( '%d', '%d', '%s', '%d', '%s' )";
		
			$sql = sprintf($sqlstring,
				intval($_REQUEST['showid']),
				intval($_REQUEST['prio']),
				make_date($_REQUEST['date']),
				intval($_REQUEST['assign']),
				mysql_real_escape_string($_REQUEST['desc'])
			);
		} else {
			$sqlstring  = "UPDATE `{$MYSQL_PREFIX}todo` SET showid = '%d', priority = '%d', assigned = '%d',";
			$sqlstring .= " dscr = '%s', due = '%s', complete = '%d' WHERE id = '%d'";
		
			$sql = sprintf($sqlstring,
				intval($_REQUEST['showid']),
				intval($_REQUEST['prio']),
				intval($_REQUEST['assign']),
				mysql_real_escape_string($_REQUEST['desc']),
				make_date($_REQUEST['date']),
				intval($_REQUEST['complete']),
				intval($_REQUEST['id'])
		); }
		
		if ( empty($_REQUEST['showid']) ) { return json_error('Please pick a show'); }
		if ( empty($_REQUEST['desc'])   ) { return json_error('Description is required'); }
		
		$result = mysql_query($sql, $db);
		if ( $result ) {
			return array('success' => true, 'msg' => "Todo Item Saved");
		} else {
			return array('success' => false, 'msg' => "Todo Save Failed".(($TEST_MODE)?mysql_error():""));
		}
	}
	
	/**
	 * Show todo views - form for pick or todo list
	 * 
	 * @global object Database Link
	 * @global string MySQL Table Prefix
	 * @global string Base HREF
	 * @param integer UserID or ShowID for display
	 * @param string Type of display (user, show, overdue)
	 * @return array HTML output
	 */
	private function view($condition = null, $type = 'user') {
		GLOBAL $db, $MYSQL_PREFIX, $TDTRAC_SITE;
		if ( is_null($condition) ) {
		
			$list = new tdlist(array('id' => 'todo_view_pick', 'inset' => true));
			$list->setFormat("<a href='%s'><h3>%s</h3><span class='ui-li-count'>%d</span></a>");
			
			$sql = "SELECT u.userid, CONCAT(first, ' ', last) as name, count(t.id) as num FROM {$MYSQL_PREFIX}users u LEFT JOIN {$MYSQL_PREFIX}todo t ON t.complete = 0 AND u.userid = t.assigned WHERE active = 1 ORDER BY last ASC";
			$result = mysql_query($sql, $db);
			
			$list->addDivide("List By User",mysql_num_rows($result));
			if ( mysql_num_rows($result) > 0 ) {
				while ( $row = mysql_fetch_array($result) ) {
					$list->addRow(array("/todo/view/type:user/id:{$row['userid']}/", $row['name'], $row['num']));
				}
			}
			
			$sql = "SELECT showname, s.showid, count(t.id) as num FROM {$MYSQL_PREFIX}shows s LEFT JOIN {$MYSQL_PREFIX}todo t ON t.complete = 0 AND s.showid = t.showid WHERE closed = 0 ORDER BY created DESC;";
			$result = mysql_query($sql, $db);
			
			$list->addDivide("List By Show",mysql_num_rows($result));
			if ( mysql_num_rows($result) > 0 ) {
				while ( $row = mysql_fetch_array($result) ) {
					$list->addRow(array("/todo/view/type:show/id:{$row['showid']}/", $row['showname'], $row['num']));
				}
			}
			$todo_num = get_single("SELECT COUNT(*) as num FROM {$MYSQL_PREFIX}todo WHERE assigned = {$this->user->id} AND complete = 0");
			$odue_num = get_single("SELECT COUNT(*) as num FROM {$MYSQL_PREFIX}todo WHERE complete = 0 AND due < NOW()");
			
			$list->addDivide("Other Options");
			$list->addRow(array('/todo/view/id:1/type:overdue/', 'Overdue Items', $odue_num));
			$list->addRow(array("/todo/view/id:{$this->user->id}/type:user/", 'Your Personal List', $todo_num));
			
			return $list->output();
		}
		else {
			if ( is_numeric($condition) ) { $thiscond = $condition; }
			else { $thiscond = perms_getidbyname($condition); }
			
			$list = new tdlist(array('id' => 'todo_view', 'actions' => 'true', 'icon' => 'check'));
			$list->setFormat(
				"<a class='todo-menu' data-done='0' data-recid='%d' data-edit='%d' href='#'><h3>%s</h3><p>"
				.(($type=="user")?"<strong>Show:</strong> %s":"<strong>User:</strong> %s")
				."</p><span class='ui-li-count'>%s</span></a>");
			
			if ( $type == 'user' ) {
				$sql = "SELECT todo.*, showname, DATE_FORMAT(`due`, '%Y-%m-%d') as duedate, TIME_TO_SEC( TIMEDIFF(`due` , NOW())) AS remain FROM {$MYSQL_PREFIX}todo as todo, {$MYSQL_PREFIX}shows as shows WHERE shows.showid = todo.showid AND todo.assigned = '{$thiscond}' ORDER BY complete ASC, due DESC, added DESC";
				$num = get_single("SELECT COUNT(*) as num FROM {$MYSQL_PREFIX}todo WHERE complete = 0 AND assigned = {$thiscond}");
				$list->addRaw("<li data-theme='f' id='todo-list-header'><h3>".$this->user->get_name($thiscond)."'s Todo List</h3> <span class='ui-li-count'>{$num}</span></li>");
			} elseif ( $type =='show' ) {
				$showname = db_list("SELECT showname FROM {$MYSQL_PREFIX}shows WHERE showid = {$thiscond}", 'showname');
				$sql = "SELECT todo.*, showname, DATE_FORMAT(`due`, '%Y-%m-%d') as duedate, TIME_TO_SEC( TIMEDIFF(`due` , NOW())) AS remain FROM {$MYSQL_PREFIX}todo as todo, {$MYSQL_PREFIX}shows as shows WHERE shows.showid = todo.showid AND todo.showid = '{$thiscond}' ORDER BY complete ASC, due DESC, added DESC";
				$num = get_single("SELECT COUNT(*) as num FROM {$MYSQL_PREFIX}todo WHERE complete = 0 AND showid = {$thiscond}");
				$list->addRaw("<li data-theme='f' id='todo-list-header'><h3>{$showname[0]}'s Todo List</h3> <span class='ui-li-count'>{$num}</span></li>");
			} elseif ( $type == 'overdue' ) {
				$sql = "SELECT todo.*, showname, DATE_FORMAT(`due`, '%Y-%m-%d') as duedate, TIME_TO_SEC( TIMEDIFF(`due` , NOW())) AS remain FROM {$MYSQL_PREFIX}todo as todo, {$MYSQL_PREFIX}shows as shows WHERE shows.showid = todo.showid AND todo.due < CURRENT_TIMESTAMP AND todo.complete = 0 ORDER BY due DESC, added DESC";
				$num = get_single("SELECT COUNT(*) as num FROM {$MYSQL_PREFIX}todo WHERE complete = 0 AND due < NOW()");
				$list->addRaw("<li data-theme='f' id='todo-list-header'><h3>Overdue Items Todo List</h3> <span class='ui-li-count'>{$num}</span></li>");
			}
			$result = mysql_query($sql, $db);
			$priorities = $this->priorities;
			
			$list->addAction("tdone");
			$laststatus = -1;
			if ( mysql_num_rows($result) < 1 ) {
				$list->addRaw("<li><h3>No Todo Items Found</h3></li>");
			} else {
				while ( $row = mysql_fetch_array($result) ) {
					if ( $laststatus < $row['complete'] ) {
						$laststatus = $row['complete'];
						$list->addRaw("<li data-theme='g'".(($laststatus == 1)?" id='todo-list-done'":"")."><h3>".(($laststatus == 0)?"Incomplete Items":"Completed Items")."</h3></li>");
					}
					$theme = (($row['remain'] < 0 && $row['complete'] == 0) ? 'e': 'c');
					$assig = ( $type == 'user' ) ? $row['showname'] : (($row['assigned'] > 0) ? $this->user->get_name($row['assigned']) : "-unassigned-");
					$statu = (($row['complete'] == 1 ) ? 'done' : "Due: {$row['duedate']}");
					if ( $this->user->can('edittodo') ) {
						$list->addRow(array($row['id'], 1, $row['dscr'], $assig, $statu), $row, array('theme' => $theme));
					} else {
						$list->addRow(array($row['id'], 0, $row['dscr'], $assig, $statu), $row, array('theme' => $theme));
					}
				}
			}
			
			return array_merge($list->output(), array("<br /><br /><a class=\"ajax-email\" data-email='{\"action\": \"todo\", \"id\": \"{$thiscond}\", \"type\": \"{$type}\"}' data-role=\"button\" data-theme=\"f\" href=\"#\">E-Mail this Report to Yourself</a>"));
		}
	}

	/**
	 * Send a todo list as email
	 * 
	 * @global object Database resource
	 * @global string MySQL Prefix
	 * @return void
	 */
	private function email() {
		GLOBAL $db, $MYSQL_PREFIX;
		$thiscond = $this->action['id'];
		$type = $this->action['type'];
			
		$subject = "TDTrac Todo {$this->action['type']}: {$this->action['id']}";
		$headers  = 'MIME-Version: 1.0' . "\r\n";
		$headers .= 'Content-type: text/html; charset=iso-8859-1' . "\r\n";
	
		if ( $type == 'user' ) {
			$sql = "SELECT todo.*, showname, DATE_FORMAT(`due`, '%Y-%m-%d') as duedate, TIME_TO_SEC( TIMEDIFF(`due` , NOW())) AS remain FROM {$MYSQL_PREFIX}todo as todo, {$MYSQL_PREFIX}shows as shows WHERE shows.showid = todo.showid AND todo.assigned = '{$thiscond}' ORDER BY due DESC, added DESC";
			$html[] = "<h3>Todo Tasks by User (".$this->user->get_name($thiscond).")</h3>\n";
		} elseif ( $type =='show' ) {
			$showname = db_list("SELECT showname FROM {$MYSQL_PREFIX}shows WHERE showid = {$thiscond}", 'showname');
			$sql = "SELECT todo.*, showname, DATE_FORMAT(`due`, '%Y-%m-%d') as duedate, TIME_TO_SEC( TIMEDIFF(`due` , NOW())) AS remain FROM {$MYSQL_PREFIX}todo as todo, {$MYSQL_PREFIX}shows as shows WHERE shows.showid = todo.showid AND todo.showid = '{$thiscond}' ORDER BY due DESC, added DESC";
			$html[] = "<h3>Todo Tasks by Show ({$showname[0]})</h3>\n";
		} elseif ( $type == 'overdue' ) {
			$sql = "SELECT todo.*, showname, DATE_FORMAT(`due`, '%Y-%m-%d') as duedate, TIME_TO_SEC( TIMEDIFF(`due` , NOW())) AS remain FROM {$MYSQL_PREFIX}todo as todo, {$MYSQL_PREFIX}shows as shows WHERE shows.showid = todo.showid AND todo.due < CURRENT_TIMESTAMP AND todo.complete = 0 ORDER BY due DESC, added DESC";
			$html[] = "<h3>Overdue Todo Tasks</h3>\n";
		}
		$result = mysql_query($sql, $db);
		$priorities = $this->priorities;
		$html[] = "<br /><br />";
		$tabl = new tdtable("todo", 'datatable', false);
		$tabl->addHeader(array('Status', 'Due', 'Priority', 'Assigned To', 'Description'));
		while ( $row = mysql_fetch_array($result) ) {
			$tabl->addRow(array((($row['complete'])?"DONE":""), $row['duedate'], $priorities[$row['priority']][1], (($row['assigned'] > 0) ? $this->user->get_name($row['assigned']) : "-unassigned-"), $row['dscr']), $row, (($row['complete']=='1') ? "tododone" : (($row['remain'] < 0 ) ? "tododue": null))  );
		}
		$html = array_merge($html, $tabl->output(false));

		$result = mail($this->user->email, $subject, join($html), $headers);
		if ( $result ) {
			$this->json['success'] = true;
		} else {
			$this->json['success'] = false;
		}
	}
}

/**
 * Check for outstanding todo items
 * 
 * @global resource Database Link
 * @global string MySQL Table Prefix
 * @global object User Object
 * @global string Site address for links
 * @return string HTML output
 */
function todo_check() {
	GLOBAL $db, $MYSQL_PREFIX, $user, $TDTRAC_SITE;
	$html = "<div class=\"infobox\"><span style=\"font-size: .7em\">";
	$tosql = "SELECT COUNT(id) as num FROM {$MYSQL_PREFIX}todo WHERE assigned = '{$user->id}' AND complete = 0";
	$result1 = mysql_query($tosql, $db);
	if ( !mysql_error() && mysql_num_rows($result1) > 0 ) {
		$row1 = mysql_fetch_array($result1);
		mysql_free_result($result1);
		$ret = 0;
		if ( !is_null($row1['num']) && $row1['num'] > 0 ) { $html .= "You Have <strong>{$row1['num']}</strong> Incomplete Tasks Waiting (<a href=\"{$TDTRAC_SITE}todo/view/id:{$user->id}/type:user/\">[-View-]</a>)<br />"; $ret = 1; }
		$html .= "</span></div>\n";
	} else { $ret = 0; }
	if ( $ret ) { return $html; } else { return ""; }
}


?>
