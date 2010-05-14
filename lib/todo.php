<?php
/**
 * TDTrac Todo List Functions
 * 
 * Contains the todo list functions
 * @package tdtrac
 * @version 1.3.1
 * @author J.T.Sage <jtsage@gmail.com>
 * @since 1.3.1
 */

/**
 * Show todo add form
 * 
 * @global resource Database Link
 * @global string User Name
 * @global string MySQL Table Prefix
 * @global string Site address for links
 * @return string HTML output
 */
function todo_add() {
	GLOBAL $db, $user_name, $MYSQL_PREFIX, $TDTRAC_SITE;
	$html  = "<h3>Add Todo Item</h3>\n";
	$form = new tdform("{$TDTRAC_SITE}add-todo", "form1");
	$result = $form->addDrop('showid', 'Show', null, db_list(get_sql_const('showid'), array('showid', 'showname')), False);
	$result = $form->addDrop('prio', 'Priority', null, todo_pri(), False, 1);
	$result = $form->addDate('date', 'Due Date');
	$result = $form->addDrop('assign', 'Assigned To', null, array_merge(array(array('0', '-unassigned-')), db_list(get_sql_const('todo'), array('userid', 'name'))), False);
	$result = $form->addText('desc', 'Description');
	$result = $form->addHidden('new-todo', true);
	$html .= $form->output('Add Todo');
	return $html;
}

/**
 * Show todo add logic
 * 
 * @global resource Database Link
 * @global string MySQL Table Prefix
 */
function todo_add_do() {
	GLOBAL $db, $MYSQL_PREFIX;
	$sql = "INSERT INTO {$MYSQL_PREFIX}todo ( showid, priority, due, assigned, dscr ) VALUES ( '{$_REQUEST['showid']}', '{$_REQUEST['prio']}', '{$_REQUEST['date']}', '{$_REQUEST['assign']}', '".mysql_real_escape_string($_REQUEST['desc'])."' )";
	$result = mysql_query($sql, $db);
	thrower("ToDo Item Added");
}

/**
 * Show todo edit form
 * 
 * @global resource Database Link
 * @global string User Name
 * @global string MySQL Table Prefix
 * @global string Site address for links
 * @param integer ID of Todo item
 * @return string HTML output
 */
function todo_edit_form($todoid) {
	GLOBAL $db, $user_name, $MYSQL_PREFIX, $TDTRAC_SITE;
	$html  = "<h3>Edit Todo Item</h3>\n";
	$sql = "SELECT *, DATE_FORMAT(`due`, '%Y-%m-%d') as duedate FROM {$MYSQL_PREFIX}todo WHERE id = {$todoid}";
	$result = mysql_query($sql, $db);
	$row = mysql_fetch_array($result);
	$form = new tdform("{$TDTRAC_SITE}edit-todo", "form1");
	$result = $form->addDrop('showid', 'Show', null, db_list(get_sql_const('showid'), array('showid', 'showname')), False, $row['showid']);
	$result = $form->addDrop('prio', 'Priority', null, todo_pri(), False, $row['priority']);
	$result = $form->addDate('date', 'Due Date', null, $row['duedate']);
	$result = $form->addDrop('assign', 'Assigned To', null, array_merge(array(array('0', '-unassigned-')), db_list(get_sql_const('todo'), array('userid', 'name'))), False, $row['assigned']);
	$result = $form->addText('desc', 'Description', null, $row['dscr']);
	$result = $form->addCheck('complete', 'Completed', null, $row['complete']);
	$result = $form->addHidden('edit-todo', true);
	$result = $form->addHidden('id', $todoid);
	if ( isset($_REQUEST['redir-to']) ) { $form->addHidden('redir-to', $_REQUEST['redir-to']); }
	$html .= $form->output('Edit Todo');
	return $html;
}

/**
 * Show todo delete confirmation form
 * 
 * @global resource Database Link
 * @global string User Name
 * @global string MySQL Table Prefix
 * @global string Site address for links
 * @param integer ID of Todo item
 * @return string HTML output
 */
function todo_del_form($todoid) {
	GLOBAL $db, $user_name, $MYSQL_PREFIX, $TDTRAC_SITE;
	$html  = "<h3>Delete Todo Item</h3>\n";
	$sql = "SELECT *, DATE_FORMAT(`due`, '%Y-%m-%d') as duedate FROM {$MYSQL_PREFIX}todo WHERE id = {$todoid}";
	$result = mysql_query($sql, $db);
	$row = mysql_fetch_array($result);
	$form = new tdform("{$TDTRAC_SITE}del-todo", "form1");
	$result = $form->addDrop('showid', 'Show', null, db_list(get_sql_const('showid'), array('showid', 'showname')), False, $row['showid'], False);
	$result = $form->addDrop('prio', 'Priority', null, todo_pri(), False, $row['priority'], False);
	$result = $form->addDate('date', 'Due Date', null, $row['duedate'], False);
	$result = $form->addDrop('assign', 'Assigned To', null, array_merge(array(array('0', '-unassigned-')), db_list(get_sql_const('todo'), array('userid', 'name'))), False, $row['assigned'], False);
	$result = $form->addText('desc', 'Description', null, $row['dscr'], False);
	$result = $form->addCheck('complete', 'Completed', null, $row['complete'], False);
	$result = $form->addHidden('del-todo', true);
	$result = $form->addHidden('id', $todoid);
	if ( isset($_REQUEST['redir-to']) ) { $form->addHidden('redir-to', $_REQUEST['redir-to']); }
	$html .= $form->output('Delete Todo');
	return $html;
}

/**
 * Show todo edit logic
 * 
 * @global resource Database Link
 * @global string MySQL Table Prefix
 * @param integer ID of Todo item
 */
function todo_edit_do($id) {
	GLOBAL $db, $MYSQL_PREFIX;
	$sql  = "UPDATE {$MYSQL_PREFIX}todo SET showid = {$_REQUEST['showid']} , priority = {$_REQUEST['prio']} , assigned = {$_REQUEST['assign']} , ";
	$sql .= "dscr = '{$_REQUEST['desc']}' , due = '{$_REQUEST['date']}'";
	$sql .= " , complete = ".(($_REQUEST['complete'] == "y") ? "1" : "0");
	$sql .= " WHERE id = {$id}";
	$result = mysql_query($sql, $db);
	if ( isset($_REQUEST['redir-to']) ){
		$cleanredit = preg_replace("/\*/", "&", $_REQUEST['redir-to']);
		thrower("Todo #{$id} Updated", $cleanredit);
	} else { thrower("Todo #{$id} Updated"); }
}

/**
 * Show todo mark complete logic
 * 
 * @global resource Database Link
 * @global string MySQL Table Prefix
 * @param integer ID of Todo item
 */
function todo_mark_do($id) {
	GLOBAL $db, $MYSQL_PREFIX;
	$sql  = "UPDATE {$MYSQL_PREFIX}todo SET complete = 1 ";
	$sql .= " WHERE id = {$id}";
	$result = mysql_query($sql, $db);
	if ( isset($_REQUEST['redir-to']) ){
		$cleanredit = preg_replace("/\*/", "&", $_REQUEST['redir-to']);
		thrower("Todo #{$id} Marked Done", $cleanredit);
	} else { thrower("Todo #{$id} Marked Done"); }
}

/**
 * Show todo delete logic
 * 
 * @global resource Database Link
 * @global string MySQL Table Prefix
 * @param integer ID of Todo item
 */
function todo_del_do($id) {
	GLOBAL $db, $MYSQL_PREFIX;
	$sql  = "DELETE FROM {$MYSQL_PREFIX}todo WHERE id = {$id}";
	$result = mysql_query($sql, $db);
	if ( isset($_REQUEST['redir-to']) ){
		$cleanredit = preg_replace("/\*/", "&", $_REQUEST['redir-to']);
		thrower("Todo #{$id} Removed", $cleanredit);
	} else { thrower("Todo #{$id} Removed"); }
}

/**
 * Show todo delete confirmation form
 * 
 * @global resource Database Link
 * @global string MySQL Table Prefix
 * @global string User Name
 * @param string|integer UserID, User Name, or ShowID for display
 * @param string Type of display (user, show, overdue)
 * @return string HTML output
 */
function todo_view($condition = null, $type = 'user') {
	GLOBAL $db, $MYSQL_PREFIX, $user_name;
	if ( is_null($condition) ) {
		$html = "<h3>View Todo By User</h3>\n";
		$form = new tdform("{$TDTRAC_SITE}view-todo", "form1");
		$result = $form->addDrop('todouser', 'Assigned To', null, db_list(get_sql_const('todo'), array('userid', 'name')), False);
		$html .= $form->output('View User');
		$html .= "<br /><br /><h3>View Todo By Show</h3>\n";
		$form = new tdform("{$TDTRAC_SITE}view-todo", "form2");
		$result = $form->addDrop('todoshow', 'Show', null, db_list(get_sql_const('showid'), array('showid', 'showname')), False);
		$html .= $form->output('View Show');
		$html .= "<br /><br /><h3>View Overdue Todo Items</h3>\n";
		$form = new tdform("{$TDTRAC_SITE}view-todo", "form2");
		$result = $form->addHidden('tododue', '1');
		$html .= $form->output('View Overdue');
		return $html;
	}
	else {
		if ( is_numeric($condition) ) { $thiscond = $condition; }
		else { $thiscond = perms_getidbyname($condition); }
		
		if ( $type == 'user' ) {
			$sql = "SELECT todo.*, showname, DATE_FORMAT(`due`, '%Y-%m-%d') as duedate, TIME_TO_SEC( TIMEDIFF(`due` , NOW())) AS remain FROM {$MYSQL_PREFIX}todo as todo, {$MYSQL_PREFIX}shows as shows WHERE shows.showid = todo.showid AND todo.assigned = '{$thiscond}' ORDER BY due DESC, added DESC";
			$html = "<h3>Todo Tasks by User (".perms_getfnamebyid($thiscond).")</h3>\n";
			$backlink = "*todouser={$thiscond}";
		} elseif ( $type =='show' ) {
			$showname = db_list("SELECT showname FROM {$MYSQL_PREFIX}shows WHERE showid = {$thiscond}", 'showname');
			$sql = "SELECT todo.*, showname, DATE_FORMAT(`due`, '%Y-%m-%d') as duedate, TIME_TO_SEC( TIMEDIFF(`due` , NOW())) AS remain FROM {$MYSQL_PREFIX}todo as todo, {$MYSQL_PREFIX}shows as shows WHERE shows.showid = todo.showid AND todo.showid = '{$thiscond}' ORDER BY due DESC, added DESC";
			$html = "<h3>Todo Tasks by Show ({$showname[0]})</h3>\n";
			$backlink = "*todoshow={$thiscond}";
		} elseif ( $type == 'overdue' ) {
			$sql = "SELECT todo.*, showname, DATE_FORMAT(`due`, '%Y-%m-%d') as duedate, TIME_TO_SEC( TIMEDIFF(`due` , NOW())) AS remain FROM {$MYSQL_PREFIX}todo as todo, {$MYSQL_PREFIX}shows as shows WHERE shows.showid = todo.showid AND todo.due < CURRENT_TIMESTAMP ORDER BY due DESC, added DESC";
			$html = "<h3>Overdue Todo Tasks</h3>\n";
			$backlink = "*tododue=1";
		}
		$result = mysql_query($sql, $db);
		$priorities = todo_pri();
		$tabl = new tdtable("todo", 'datatable', true, "view-todo{$backlink}");
		$tabl->addHeader(array('Due', 'Priority', 'Assigned To', 'Description'));
		$tabl->addAction(array('tdone',));
		if ( perms_checkperm($user_name, 'editbudget') ) { $tabl->addAction(array('tedit', 'tdel')); }
		while ( $row = mysql_fetch_array($result) ) {
			$tabl->addRow(array($row['duedate'], $priorities[$row['priority']][1], (($row['assigned'] > 0) ? perms_getfnamebyid($row['assigned']) : "-unassigned-"), $row['dscr']), $row, (($row['complete']=='1') ? "tododone" : (($row['remain'] < 0 ) ? "tododue": null))  );
		}
		$html .= $tabl->output();
		return $html;
		//return $thisuserid;
	}
}

/**
 * Check for outstanding todo items
 * 
 * @global resource Database Link
 * @global string MySQL Table Prefix
 * @global string User Name
 * @global string Site address for links
 * @return string HTML output
 */
function todo_check() {
	GLOBAL $db, $MYSQL_PREFIX, $user_name, $TDTRAC_SITE;
	$html  = "";
	$html .= "<div class=\"infobox\"><span style=\"font-size: .7em\">";
	$userid = perms_getidbyname($user_name);
	$tosql = "SELECT COUNT(id) as num FROM {$MYSQL_PREFIX}todo WHERE assigned = '{$userid}' AND complete = 0";
	$result1 = mysql_query($tosql, $db);
	if ( !mysql_error() && mysql_num_rows($result1) > 0 ) {
		$row1 = mysql_fetch_array($result1);
		mysql_free_result($result1);
		$ret = 0;
		if ( !is_null($row1['num']) && $row1['num'] > 0 ) { $html .= "You Have {$row1['num']} Uncompleted Tasks Waiting (<a href=\"{$TDTRAC_SITE}view-todo&onlyuser=1\">[-View-]</a>)<br />"; $ret = 1; }
		$html .= "</span></div>\n";
	} else { $ret = 0; }
	if ( $ret ) { return $html; } else { return ""; }
}

/**
 * Populate a list of priorities
 * 
 * @return array List of Priorities
 */
function todo_pri() {
	$names = array('Low', 'Normal', 'High', 'Critical');
	for ($i = 0; $i < count($names); $i++) {
		$retarr[] = array($i, $names[$i]);
	}
	return $retarr;
}

?>
