<?php
/**
 * TDTrac Todo List Functions
 * 
 * Contains the todo list functions
 * Data hardened since 1.3.1
 * @package tdtrac
 * @version 3.0.0
 * @author J.T.Sage <jtsage@gmail.com>
 * @since 1.3.1
 */

/**
 * TO-DO Module
 *  Allows per-user and per-show task lists
 * 
 * @package tdtrac
 * @version 3.0.0
 * @since 2.0.0
 * @author J.T.Sage <jtsage@gmail.com>
 */
class tdtrac_todo {
	
	/** @var array Parsed query string */
	private $action = array();
	
	/** @var array Formatted HTML */
	private $html = array();
	
	/** @var string Page Title */
	private $title = "To-Do Lists";
	
	
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
	}
	
	/**
	 * Output todo list operation
	 * 
	 * @global array Extra Header Link
	 * @global bool Make 'back' link a 'cancel' link
	 * @return void
	 */
	public function output() {
		global $HEAD_LINK, $CANCEL;
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
		makePage($this->html, $this->title, $this->sidebar());
	} // END OUTPUT FUNCTION
	
	/**
	 * Show Todo Add Form
	 * 
	 * @global string TDTrac Root Link HREF
	 * @return array Formatted HTML
	 */
	private function add_form() {
		global $TDTRAC_SITE;
		$form = new tdform(array('action' => "{$TDTRAC_SITE}json/save/base:todo/id:0/", 'id' => 'todo_add_form'));
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
		$result = $form->addText(array('name' => 'desc', 'label' => 'Description', 'placeholder' => 'Item Description'));
		$result = $form->addHidden('id', '0');
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
		
		$form = new tdform(array('action' => "{$TDTRAC_SITE}json/save/base:todo/id:{$id}/", 'id' => 'todo-edit-form'));
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
			
			$list->addDivide("List By User",mysql_num_rows($result)." Users");
			if ( mysql_num_rows($result) > 0 ) {
				while ( $row = mysql_fetch_array($result) ) {
					$list->addRow(array("/todo/view/type:user/id:{$row['userid']}/", $row['name'], $row['num']));
				}
			}
			
			$sql = "SELECT t.showid FROM {$MYSQL_PREFIX}todo t, {$MYSQL_PREFIX}shows s WHERE t.showid = s.showid AND s.closed = 0 AND t.complete = 0 GROUP BY t.showid";
			$result = mysql_query($sql, $db);
			$list->addDivide("List By Show",mysql_num_rows($result)." Shows");
			
			$sql = "SELECT showid, showname FROM {$MYSQL_PREFIX}shows s WHERE s.closed = 0 ORDER BY s.created DESC";
			$result = mysql_query($sql, $db);
			if ( mysql_num_rows($result) > 0 ) {
				while ( $row = mysql_fetch_array($result) ) {
					$the_num = get_single("SELECT COUNT(*) as num FROM {$MYSQL_PREFIX}todo WHERE complete = 0 and showid={$row['showid']}");
					$list->addRow(array("/todo/view/type:show/id:{$row['showid']}/", $row['showname'], $the_num));
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
			
			$list = new tdlist(array('id' => 'todo_view', 'actions' => 'true', 'icon' => 'check', 'inset' => true ));
			$list->setFormat(
				"<a class='todo-menu' data-done='0' data-recid='%d' data-edit='%d' href='#'><h3>%s</h3><p>"
				.(($type=="user")?"<strong>Show:</strong> %s":"<strong>User:</strong> %s")
				."</p><span class='ui-li-count'>%s</span></a>");
			
			if ( $type == 'user' ) {
				$sql = "SELECT todo.*, showname, DATE_FORMAT(`due`, '%Y-%m-%d') as duedate, TIME_TO_SEC( TIMEDIFF(`due` , NOW())) AS remain FROM {$MYSQL_PREFIX}todo as todo, {$MYSQL_PREFIX}shows as shows WHERE shows.showid = todo.showid AND todo.assigned = '{$thiscond}' ORDER BY complete ASC, due DESC, added DESC";
				$num = get_single("SELECT COUNT(*) as num FROM {$MYSQL_PREFIX}todo WHERE complete = 0 AND assigned = {$thiscond}");
				$list->addRaw("<li data-theme='d' id='todo-list-header'><h3>".$this->user->get_name($thiscond)."'s Todo List</h3> <span class='ui-li-count'>{$num}</span></li>");
			} elseif ( $type =='show' ) {
				$showname = db_list("SELECT showname FROM {$MYSQL_PREFIX}shows WHERE showid = {$thiscond}", 'showname');
				$sql = "SELECT todo.*, showname, DATE_FORMAT(`due`, '%Y-%m-%d') as duedate, TIME_TO_SEC( TIMEDIFF(`due` , NOW())) AS remain FROM {$MYSQL_PREFIX}todo as todo, {$MYSQL_PREFIX}shows as shows WHERE shows.showid = todo.showid AND todo.showid = '{$thiscond}' ORDER BY complete ASC, due DESC, added DESC";
				$num = get_single("SELECT COUNT(*) as num FROM {$MYSQL_PREFIX}todo WHERE complete = 0 AND showid = {$thiscond}");
				$list->addRaw("<li data-theme='d' id='todo-list-header'><h3>{$showname[0]}'s Todo List</h3> <span class='ui-li-count'>{$num}</span></li>");
			} elseif ( $type == 'overdue' ) {
				$sql = "SELECT todo.*, showname, DATE_FORMAT(`due`, '%Y-%m-%d') as duedate, TIME_TO_SEC( TIMEDIFF(`due` , NOW())) AS remain FROM {$MYSQL_PREFIX}todo as todo, {$MYSQL_PREFIX}shows as shows WHERE shows.showid = todo.showid AND todo.due < CURRENT_TIMESTAMP AND todo.complete = 0 ORDER BY due DESC, added DESC";
				$num = get_single("SELECT COUNT(*) as num FROM {$MYSQL_PREFIX}todo WHERE complete = 0 AND due < NOW()");
				$list->addRaw("<li data-theme='d' id='todo-list-header'><h3>Overdue Items Todo List</h3> <span class='ui-li-count'>{$num}</span></li>");
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
						$list->addRaw("<li data-role='list-divider' ".(($laststatus == 1)?" id='todo-list-done'":"").">".(($laststatus == 0)?"Incomplete Items":"Completed Items")."</li>");
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
	public function email() {
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
		$html[] = "<table><tr><th>Status</th><th>Due</th><th>Priority</th><th>Assigned To</th><th>Description</th></tr>";
		
		while ( $row = mysql_fetch_array($result) ) {
			$html[] = sprintf("<tr><td>%s</td><td>%s</td><td>%s</td><td>%s</td><td>%s</td></tr>",
				(($row['complete'])?"DONE":""),
				$row['duedate'],
				$priorities[$row['priority']][1],
				(($row['assigned'] > 0) ? $this->user->get_name($row['assigned']) : "-unassigned-"),
				$row['dscr']
			);
		}
		$html[] = "</table>";
		
		return mail($this->user->email, $subject, join($html), $headers);
	}
	
	/**
	 * View sidebar of shows
	 * 
	 * @global string MySQL Table Prefix
	 * @return array HTML Output
	 */
	private function sidebar() {
		GLOBAL $MYSQL_PREFIX, $TDTRAC_SITE;
	
		$todo_open = get_single("SELECT COUNT(id) as num FROM `{$MYSQL_PREFIX}todo` WHERE complete = 0");
		$todo_totl = get_single("SELECT COUNT(id) as num FROM `{$MYSQL_PREFIX}todo` WHERE 1");
		$todo_over = get_single("SELECT COUNT(id) as num FROM `{$MYSQL_PREFIX}todo` WHERE complete = 0 AND due < CURRENT_TIMESTAMP");
		$todo_your = get_single("SELECT COUNT(id) as num FROM `{$MYSQL_PREFIX}todo` WHERE complete = 0 AND assigned = {$this->user->id}");
		
		$list = new tdlist(array('id' => 'todo_sidebar', 'actions' => false, 'inset' => true));
		$showsopen = true;
		
		$html = array('<h4 class="intro">Manage Todo Lists and Items</h4>');
		
		$list->setFormat("%s");
		$list->addRow("<h3>Your Items</h3><p>Your outstanding items</p><p class='ui-li-count'>{$todo_your}</p></h3>");
		
		if ( $this->user->can('viewtodo') ) {
			$list->addRow("<h3>Overdue Items</h3><p>Total overdue items</p><p class='ui-li-count'>{$todo_over}</p></h3>");
			$list->addRow("<h3>Incomplete Items</h3><p>Total outstanding items</p><p class='ui-li-count'>{$todo_open}</p></h3>");
			$list->addRow("<h3>Total Items</h3><p>Total of all items</p><p class='ui-li-count'>{$todo_totl}</p></h3>");
		}
		$list->addRaw("<li data-icon='plus'><a href='{$TDTRAC_SITE}todo/add/'><h3>Add Item</h3></a></li>");
		
		
		return array_merge($html,$list->output());
	}
}


?>
