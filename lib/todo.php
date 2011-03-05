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
		global $HEAD_LINK, $CANCEL, $TEST_MODE
		if ( !$this->output_json ) { // HTML METHODS
			switch ( $this->action['action'] ) {
				case "add":
					$CANCEL = true;
					$this->title .= "::Add";
					if ( $this->user->can("addtodo") ) {
						if ( $this->post ) {
							thrower($this->save(false), 'todo/add/');
						} else {
							$this->html = $this->add_form();
						}
					} else {
						thrower('Access Denied :: You cannot add new todo items', 'todo/');
					} break;
				case "view":
					$this->title .= "::View";
					if ( $this->post ) {
						$type = ( isset($_REQUEST['type']) && ( $_REQUEST['type'] == 'show' || $_REQUEST['type'] == 'overdue') ) ? $_REQUEST['type'] : "user";
						$id   = ( isset($_REQUEST['id']) && is_numeric($_REQUEST['id']) ) ? intval($_REQUEST['id']) : 1;
						thrower(false, "todo/view/id:{$id}/type:{$type}/");
					} else {
						if ( $this->user->can('addtodo') ) {
							$HEAD_LINK = array('/todo/add/', 'plus', 'Add Item'); 
						}
						if ( !isset($this->action['id']) ) {
							$this->html = $this->view(null);	
						} else {
							if ( !$this->user->can("viewtodo") ) {
								$this->html = $this->view($this->user->id, 'user');
							} else {
								$type = ( isset($this->action['type']) && ( $this->action['type'] == 'overdue' || $this->action['type'] == 'show' ) ) ? $this->action['type'] : "user";
								$id   = ( isset($this->action['id']) && is_numeric($this->action['id']) ) ? intval($this->action['id']) : 1;
								$this->html = $this->view($id, $type);
							}
						}
					} break;
				case "edit":
					$this->title .= " :: Edit";
					if ( $this->user->can("edittodo") ) {
						if ( $this->post ) {
							if ( isset($_REQUEST['id']) && is_numeric($_REQUEST['id']) ) {
								thrower($this->save(true), "todo/edit/id:".intval($_REQUEST['id'])."/");
							} else {
								thrower('Error :: Data Mismatch Detected', 'todo/');
							}
						} else {
							if ( isset($this->action['id']) && is_numeric($this->action['id']) ) {
								$this->html = $this->edit_form(intval($this->action['id']));
							} else {
								thrower("Error :: Data Mismatch Detected", 'todo/');
							}
						}
					} else {
						thrower('Access Denied :: You Cannot Edit Todo Items', 'todo/');
					} break;
				default:
					if ( $this->user->can('viewtodo') ) {
						thrower(false, 'todo/view/');
					} else {
						thrower(flase, 'todo/view/id:{$this->user->id}/type:user/');
					}
					break;
			}
			makePage($this->html, $this->title);
		} else { // JSON METHODS
			switch($this->action['action']) {
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
					$this->json['success'] = true;//false;
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
	 * Show available ToDo Functions
	 * 
	 * @global string TDTrac Root Link HREF
	 * @return array Formatted HTML
	 */
	public function index() {
		global $TDTRAC_SITE;
		$html[] = "<div class=\"tasks\"><ul class=\"linklist\"><li><h3>ToDo Lists</h3><ul class=\"linklist\">";
		$html[] = "  <li>Manage per-user and per-show task lists.</li>";
		$html[] = ( $this->user->can('addtodo') ) ? "  <li><a href=\"{$TDTRAC_SITE}todo/add/\">Add ToDo Item</a></li>" : "";
		$html[] = ( $this->user->can('viewtodo') ) ? "  <li><a href=\"{$TDTRAC_SITE}todo/view/\">View ToDo Items</a></li>" : "";
		$html[] = "  <li><a href=\"{$TDTRAC_SITE}todo/view/id:{$this->user->id}/type:user/\">View Personal ToDo Items</a></li>";
		$html[] = "</ul></li></ul></div>";
		return $html;
	}
	
	/**
	 * Show Todo Add Form
	 * 
	 * @global string TDTrac Root Link HREF
	 * @return array Formatted HTML
	 */
	private function add_form() {
		global $TDTRAC_SITE;
		$form = new tdform("{$TDTRAC_SITE}todo/add/", 'todo_add_form', 1, 'genform', 'Add To-Do Item');
		$result = $form->addDrop('showid', 'Show', null, db_list(get_sql_const('showid'), array('showid', 'showname')), False);
		$result = $form->addDrop('prio', 'Priority', null, $this->priorities, False, 1);
		$result = $form->addDate('date', 'Due Date');
		$result = $form->addDrop('assign', 'Assigned To', null, array_merge(array(array('0', '-unassigned-')), db_list(get_sql_const('todo'), array('userid', 'name'))), False);
		$result = $form->addText('desc', 'Description');
		$result = $form->addHidden('new-todo', true);
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
		
		$form = new tdform("{$TDTRAC_SITE}todo/edit/id:{$id}/", 'todo-edit-form', 1, 'genform', 'Edit To-Do Item');
		$result = $form->addDrop('showid', 'Show', null, db_list(get_sql_const('showid'), array('showid', 'showname')), False, $row['showid']);
		$result = $form->addDrop('prio', 'Priority', null, $this->priorities, False, $row['priority']);
		$result = $form->addDate('date', 'Due Date', null, $row['duedate']);
		$result = $form->addDrop('assign', 'Assigned To', null, array_merge(array(array('0', '-unassigned-')), db_list(get_sql_const('todo'), array('userid', 'name'))), False, $row['assigned']);
		$result = $form->addText('desc', 'Description', null, $row['dscr']);
		$result = $form->addCheck('complete', 'Completed', null, $row['complete']);
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
				(($_REQUEST['complete'] == "y") ? 1 : 0),
				intval($_REQUEST['id'])
		); }
		
		$result = mysql_query($sql, $db);
		if ( $result ) {
			return "To-Do Item Saved";
		} else {
			return "To-Do Item Save :: Failed" . (($MYSQL_DEBUG) ? " (".mysql_error().")" : "");
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
		
			$list = new tdlist('todo_view_pick', false);
			$list->setFormat("<h3><a href=\"%s\">%s</a></h3><span class=\"ui-li-count\">%d</span>");
			
			$sql = "SELECT u.userid, CONCAT(first, ' ', last) as name, count(t.id) as num FROM {$MYSQL_PREFIX}users u LEFT JOIN {$MYSQL_PREFIX}todo t ON t.complete = 0 AND u.userid = t.assigned WHERE active = 1 ORDER BY last ASC";
			$result = mysql_query($sql, $db);
			
			$list->addRow("<li data-role=\"list-divider\">List By User<span class=\"ui-li-count\">".mysql_num_rows($result)."</span></li>", null, null, true);
			if ( mysql_num_rows($result) > 0 ) {
				while ( $row = mysql_fetch_array($result) ) {
					$list->addRow(array("/todo/view/type:user/id:{$row['userid']}/", $row['name'], $row['num']));
				}
			}
			
			$sql = "SELECT showname, s.showid, count(t.id) as num FROM {$MYSQL_PREFIX}shows s LEFT JOIN {$MYSQL_PREFIX}todo t ON t.complete = 0 AND s.showid = t.showid WHERE closed = 0 ORDER BY created DESC;";
			$result = mysql_query($sql, $db);
			
			$list->addRow("<li data-role=\"list-divider\">List By Show<span class=\"ui-li-count\">".mysql_num_rows($result)."</span></li>", null, null, true);
			if ( mysql_num_rows($result) > 0 ) {
				while ( $row = mysql_fetch_array($result) ) {
					$list->addRow(array("/todo/view/type:show/id:{$row['showid']}/", $row['showname'], $row['num']));
				}
			}
			$todo_num = get_single("SELECT COUNT(*) as num FROM {$MYSQL_PREFIX}todo WHERE assigned = {$this->user->id} AND complete = 0");
			$odue_num = get_single("SELECT COUNT(*) as num FROM {$MYSQL_PREFIX}todo WHERE complete = 0 AND due < NOW()");
			
			$list->addRow("<li data-role=\"list-divider\">Other Options</li>", null, null, true);
			$list->addRow("<li><h3><a href=\"/todo/view/id:1/type:overdue/\">Overdue Items</a></h3><span class=\"ui-li-count\">{$odue_num}</span></li>", null, null, true);
			$list->addRow("<li><h3><a href=\"/todo/view/id:{$this->user->id}/type:user/\">Your Personal List</a></h3><span class=\"ui-li-count\">{$todo_num}</span></li>", null, null, true);
			
			return $list->output();
		}
		else {
			if ( is_numeric($condition) ) { $thiscond = $condition; }
			else { $thiscond = perms_getidbyname($condition); }
			
			$list = new tdlist('todo_view');
			$list->setFormat("<a class=\"todo-done\" data-done=\"%d\" data-recid=\"%d\" href=\"#\"></a><h3>%s</h3><p>".(($type=="user")?"<strong>Show:</strong> %s":"<strong>User:</strong> %s")."</p><span class=\"ui-li-count\">%s</span>");
			
			if ( $type == 'user' ) {
				$sql = "SELECT todo.*, showname, DATE_FORMAT(`due`, '%Y-%m-%d') as duedate, TIME_TO_SEC( TIMEDIFF(`due` , NOW())) AS remain FROM {$MYSQL_PREFIX}todo as todo, {$MYSQL_PREFIX}shows as shows WHERE shows.showid = todo.showid AND todo.assigned = '{$thiscond}' ORDER BY due DESC, added DESC";
				$num = get_single("SELECT COUNT(*) as num FROM {$MYSQL_PREFIX}todo WHERE complete = 0 AND assigned = {$thiscond}");
				$list->addRow("<li data-role=\"list-divider\">".$this->user->get_name($thiscond)."'s Todo List <span class=\"ui-li-count\">{$num}</span></li>", null, null, true);
			} elseif ( $type =='show' ) {
				$showname = db_list("SELECT showname FROM {$MYSQL_PREFIX}shows WHERE showid = {$thiscond}", 'showname');
				$sql = "SELECT todo.*, showname, DATE_FORMAT(`due`, '%Y-%m-%d') as duedate, TIME_TO_SEC( TIMEDIFF(`due` , NOW())) AS remain FROM {$MYSQL_PREFIX}todo as todo, {$MYSQL_PREFIX}shows as shows WHERE shows.showid = todo.showid AND todo.showid = '{$thiscond}' ORDER BY due DESC, added DESC";
				$num = get_single("SELECT COUNT(*) as num FROM {$MYSQL_PREFIX}todo WHERE complete = 0 AND showid = {$thiscond}");
				$list->addRow("<li data-role=\"list-divider\">{$showname[0]}'s Todo List <span class=\"ui-li-count\">{$num}</span></li>", null, null, true);
			} elseif ( $type == 'overdue' ) {
				$sql = "SELECT todo.*, showname, DATE_FORMAT(`due`, '%Y-%m-%d') as duedate, TIME_TO_SEC( TIMEDIFF(`due` , NOW())) AS remain FROM {$MYSQL_PREFIX}todo as todo, {$MYSQL_PREFIX}shows as shows WHERE shows.showid = todo.showid AND todo.due < CURRENT_TIMESTAMP AND todo.complete = 0 ORDER BY due DESC, added DESC";
				$num = get_single("SELECT COUNT(*) as num FROM {$MYSQL_PREFIX}todo WHERE complete = 0 AND due < NOW()");
				$list->addRow("<li data-role=\"list-divider\">Overdue Items Todo List <span class=\"ui-li-count\">{$num}</span></li>", null, null, true);
			}
			$result = mysql_query($sql, $db);
			$priorities = $this->priorities;
			
			$list->addAction(array('tdel', 'tdone'));
			
			if ( mysql_num_rows($result) < 1 ) {
				$list->addRaw("<li><h3>No Todo Items Found</h3></li>");
			} else {
				while ( $row = mysql_fetch_array($result) ) {
					$theme = (($row['remain'] < 0 && $row['complete'] == 0) ? 'e': 'c');
					$assig = ( $type == 'user' ) ? $row['showname'] : (($row['assigned'] > 0) ? $this->user->get_name($row['assigned']) : "-unassigned-");
					$statu = (($row['complete'] == 1 ) ? 'done' : "Due: {$row['duedate']}");
					$list->addRow(array($row['complete'], $row['id'], $row['dscr'], $assig, $statu), $row, false, false, $theme);
				}
			}
			
			return array_merge($list->output(), array("<br /><br /><a class=\"ajax-email\" data-email='{\"action\": \"todo\", \"id\": \"{$thiscond}\", \"type\": \"{$type}\"}' data-role=\"button\" data-theme=\"e\" href=\"#\">E-Mail this Report to Yourself</a>"));
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
