<?php
/**
 * TDTrac Todo List Functions
 * 
 * Contains the todo list functions
 * Data hardened since 1.3.1
 * @package tdtrac
 * @version 1.4.0
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
	$form = new tdform("{$TDTRAC_SITE}add-todo", "form1", 1, 'genform', 'Add To-Do Item');
	$result = $form->addDrop('showid', 'Show', null, db_list(get_sql_const('showid'), array('showid', 'showname')), False);
	$result = $form->addDrop('prio', 'Priority', null, todo_pri(), False, 1);
	$result = $form->addDate('date', 'Due Date');
	$result = $form->addDrop('assign', 'Assigned To', null, array_merge(array(array('0', '-unassigned-')), db_list(get_sql_const('todo'), array('userid', 'name'))), False);
	$result = $form->addText('desc', 'Description');
	$result = $form->addHidden('new-todo', true);
	$html = $form->output('Add Todo');
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
	$sqlstring  = "INSERT INTO `{$MYSQL_PREFIX}todo` ( showid, priority, due, assigned, dscr )";
	$sqlstring .= " VALUES ( '%d', '%d', '%s', '%d', '%s' )";
	
	$sql = sprintf($sqlstring,
		intval($_REQUEST['showid']),
		intval($_REQUEST['prio']),
		mysql_real_escape_string($_REQUEST['date']),
		intval($_REQUEST['assign']),
		mysql_real_escape_string($_REQUEST['desc'])
	);
	
	$result = mysql_query($sql, $db);
	if ( $result ) {
		thrower("To-Do Item Added");
	} else {
		thrower("To-Do Item Add :: Operation Failed");
	}
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
	$sql = "SELECT *, DATE_FORMAT(`due`, '%Y-%m-%d') as duedate FROM {$MYSQL_PREFIX}todo WHERE id = {$todoid}";
	$result = mysql_query($sql, $db);
	$row = mysql_fetch_array($result);
	$form = new tdform("{$TDTRAC_SITE}todo/edit/{$todoid}/", "form1", 1, 'genform', 'Edit To-Do Item');
	$result = $form->addDrop('showid', 'Show', null, db_list(get_sql_const('showid'), array('showid', 'showname')), False, $row['showid']);
	$result = $form->addDrop('prio', 'Priority', null, todo_pri(), False, $row['priority']);
	$result = $form->addDate('date', 'Due Date', null, $row['duedate']);
	$result = $form->addDrop('assign', 'Assigned To', null, array_merge(array(array('0', '-unassigned-')), db_list(get_sql_const('todo'), array('userid', 'name'))), False, $row['assigned']);
	$result = $form->addText('desc', 'Description', null, $row['dscr']);
	$result = $form->addCheck('complete', 'Completed', null, $row['complete']);
	$result = $form->addHidden('edit-todo', true);
	$result = $form->addHidden('id', $todoid);
	if ( isset($_REQUEST['redir-to']) ) { $form->addHidden('redir-to', $_REQUEST['redir-to']); }
	$html = $form->output('Edit Todo');
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
	$sql = "SELECT *, DATE_FORMAT(`due`, '%Y-%m-%d') as duedate FROM {$MYSQL_PREFIX}todo WHERE id = {$todoid}";
	$result = mysql_query($sql, $db);
	$row = mysql_fetch_array($result);
	$form = new tdform("{$TDTRAC_SITE}todo/del/{$todoid}/", "form1", 1, 'genform', 'Delete To-Do Item');
	$result = $form->addDrop('showid', 'Show', null, db_list(get_sql_const('showid'), array('showid', 'showname')), False, $row['showid'], False);
	$result = $form->addDrop('prio', 'Priority', null, todo_pri(), False, $row['priority'], False);
	$result = $form->addDate('date', 'Due Date', null, $row['duedate'], False);
	$result = $form->addDrop('assign', 'Assigned To', null, array_merge(array(array('0', '-unassigned-')), db_list(get_sql_const('todo'), array('userid', 'name'))), False, $row['assigned'], False);
	$result = $form->addText('desc', 'Description', null, $row['dscr'], False);
	$result = $form->addCheck('complete', 'Completed', null, $row['complete'], False);
	$result = $form->addHidden('del-todo', true);
	$result = $form->addHidden('id', $todoid);
	if ( isset($_REQUEST['redir-to']) ) { $form->addHidden('redir-to', $_REQUEST['redir-to']); }
	$html = $form->output('Delete Todo');
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
	if ( !is_numeric($id) || $id < 1 ) { thrower(perms_fail()); }
	$sqlstring  = "UPDATE `{$MYSQL_PREFIX}todo` SET showid = '%d', priority = '%d', assigned = '%d',";
	$sqlstring .= " dscr = '%s', due = '%s', complete = '%d' WHERE id = '%d'";
	
	$sql = sprintf($sqlstring,
		intval($_REQUEST['showid']),
		intval($_REQUEST['prio']),
		intval($_REQUEST['assign']),
		mysql_real_escape_string($_REQUEST['desc']),
		mysql_real_escape_string($_REQUEST['date']),
		(($_REQUEST['complete'] == "y") ? 1 : 0),
		intval($id)
	);
	
	$result = mysql_query($sql, $db);
	if ( $result ) {
		if ( isset($_REQUEST['redir-to']) ){
			$cleanredit = preg_replace("/\*/", "&", $_REQUEST['redir-to']);
			thrower("To-Do #{$id} Updated", $cleanredit);
		} else { thrower("To-Do #{$id} Updated"); }
	} else {
		thrower("To-Do Update :: Operation Failed");
	}
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
	if ( !is_numeric($id) || $id < 1 ) { thrower(perms_fail()); }
	$sql  = "UPDATE {$MYSQL_PREFIX}todo SET complete = 1 ";
	$sql .= " WHERE id = '".intval($id)."'";
	$result = mysql_query($sql, $db);
	if ( $result ) {
		if ( isset($_REQUEST['redir-to']) ){
			$cleanredit = preg_replace("/\*/", "&", $_REQUEST['redir-to']);
			thrower("To-Do #{$id} Marked Done", $cleanredit);
		} else { thrower("To-Do #{$id} Marked Done"); }
	} else {
		thrower("To-Do Mark :: Operation Failed");
	}
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
	if ( !is_numeric($id) || $id < 1 ) { thrower(perms_fail()); }
	$sql  = "DELETE FROM {$MYSQL_PREFIX}todo WHERE id = '".intval($id)."'";
	$result = mysql_query($sql, $db);
	if ( $result ) {
		if ( isset($_REQUEST['redir-to']) ){
			$cleanredit = preg_replace("/\*/", "&", $_REQUEST['redir-to']);
			thrower("To-Do #{$id} Removed", $cleanredit);
		} else { thrower("To-Do #{$id} Removed"); }
	} else {
		thrower("To-Do Delete :: Operation Failed");
	}
}

/**
 * Show todo views - form for pick or todo list
 * 
 * @global resource Database Link
 * @global string MySQL Table Prefix
 * @global string User Name
 * @param string|integer UserID, User Name, or ShowID for display
 * @param string Type of display (user, show, overdue)
 * @return string HTML output
 */
function todo_view($condition = null, $type = 'user') {
	GLOBAL $db, $MYSQL_PREFIX, $user_name, $TDTRAC_SITE;
	if ( is_null($condition) ) {
		$form = new tdform("{$TDTRAC_SITE}todo/view/user/", "form1", 10, 'genform1', 'View To-Do By User');
		$result = $form->addDrop('todouser', 'Assigned To', null, db_list(get_sql_const('todo'), array('userid', 'name')), False);
		$html = $form->output('View User');
		$html[] = "<br /><br />\n";
		$form = new tdform("{$TDTRAC_SITE}todo/view/show/", "form2", 20, 'genform2', 'View To-Do By Show');
		$result = $form->addDrop('todoshow', 'Show', null, db_list(get_sql_const('showid'), array('showid', 'showname')), False);
		$html = array_merge($html, $form->output('View Show'));
		$html[] = "<br /><br />\n";
		$form = new tdform("{$TDTRAC_SITE}todo/view/due/", "form3", 30, 'genform3', 'View Overdue Items');
		$result = $form->addHidden('tododue', '1');
		$html = array_merge($html, $form->output('View Overdue'));
		return $html;
	}
	else {
		if ( is_numeric($condition) ) { $thiscond = $condition; }
		else { $thiscond = perms_getidbyname($condition); }
		
		if ( $type == 'user' ) {
			$sql = "SELECT todo.*, showname, DATE_FORMAT(`due`, '%Y-%m-%d') as duedate, TIME_TO_SEC( TIMEDIFF(`due` , NOW())) AS remain FROM {$MYSQL_PREFIX}todo as todo, {$MYSQL_PREFIX}shows as shows WHERE shows.showid = todo.showid AND todo.assigned = '{$thiscond}' ORDER BY due DESC, added DESC";
			$html[] = "<h3>Todo Tasks by User (".perms_getfnamebyid($thiscond).")</h3>\n";
			$backlink = "user/*todouser={$thiscond}";
		} elseif ( $type =='show' ) {
			$showname = db_list("SELECT showname FROM {$MYSQL_PREFIX}shows WHERE showid = {$thiscond}", 'showname');
			$sql = "SELECT todo.*, showname, DATE_FORMAT(`due`, '%Y-%m-%d') as duedate, TIME_TO_SEC( TIMEDIFF(`due` , NOW())) AS remain FROM {$MYSQL_PREFIX}todo as todo, {$MYSQL_PREFIX}shows as shows WHERE shows.showid = todo.showid AND todo.showid = '{$thiscond}' ORDER BY due DESC, added DESC";
			$html[] = "<h3>Todo Tasks by Show ({$showname[0]})</h3>\n";
			$backlink = "show/*todoshow={$thiscond}";
		} elseif ( $type == 'overdue' ) {
			$sql = "SELECT todo.*, showname, DATE_FORMAT(`due`, '%Y-%m-%d') as duedate, TIME_TO_SEC( TIMEDIFF(`due` , NOW())) AS remain FROM {$MYSQL_PREFIX}todo as todo, {$MYSQL_PREFIX}shows as shows WHERE shows.showid = todo.showid AND todo.due < CURRENT_TIMESTAMP AND todo.complete = 0 ORDER BY due DESC, added DESC";
			$html[] = "<h3>Overdue Todo Tasks</h3>\n";
			$backlink = "due/";
		}
		$result = mysql_query($sql, $db);
		$priorities = todo_pri();
		$html[] = "<br /><br />";
		$tabl = new tdtable("todo", 'datatable', true, "todo/view/{$backlink}");
		$tabl->addHeader(array('Due', 'Priority', 'Assigned To', 'Description'));
		$tabl->addAction(array('tdone',));
		if ( perms_checkperm($user_name, 'editbudget') ) { $tabl->addAction(array('tedit', 'tdel')); }
		while ( $row = mysql_fetch_array($result) ) {
			$tabl->addRow(array($row['duedate'], $priorities[$row['priority']][1], (($row['assigned'] > 0) ? perms_getfnamebyid($row['assigned']) : "-unassigned-"), $row['dscr']), $row, (($row['complete']=='1') ? "tododone" : (($row['remain'] < 0 ) ? "tododue": null))  );
		}
		$html = array_merge($html, $tabl->output(false));
		return $html;
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
	$html = "<div class=\"infobox\"><span style=\"font-size: .7em\">";
	$userid = perms_getidbyname($user_name);
	$tosql = "SELECT COUNT(id) as num FROM {$MYSQL_PREFIX}todo WHERE assigned = '{$userid}' AND complete = 0";
	$result1 = mysql_query($tosql, $db);
	if ( !mysql_error() && mysql_num_rows($result1) > 0 ) {
		$row1 = mysql_fetch_array($result1);
		mysql_free_result($result1);
		$ret = 0;
		if ( !is_null($row1['num']) && $row1['num'] > 0 ) { $html .= "You Have <strong>{$row1['num']}</strong> Uncompleted Tasks Waiting (<a href=\"{$TDTRAC_SITE}view-todo&onlyuser=1\">[-View-]</a>)<br />"; $ret = 1; }
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
