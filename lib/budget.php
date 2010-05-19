<?php
/**
 * TDTrac Budget Functions
 * 
 * Contains all budget related functions.
 * Data hardened as of 1.3.1
 *  
 * @package tdtrac
 * @version 1.3.1
 * @author J.T.Sage <jtsage@gmail.com>
 * @since 0.0.9a
 */

/**
 * Form to add a new budget item
 * 
 * @param integer Reciept Number if applicable
 * @global resource Database Connection
 * @global string MySQL Table Prefix
 * @global string TDTrac site address, for form actions
 * @return string HTML Output
 */
function budget_addform($rcpt = 0) {
	GLOBAL $db, $MYSQL_PREFIX, $TDTRAC_SITE;
	$html  = "<h3>Add Budget Expense</h3>\n";
	$form = new tdform("{$TDTRAC_SITE}add-budget", 'form1');
	
	$fesult = $form->addDrop('showid', 'Show', 'Show to Charge', db_list(get_sql_const('showid'), array(showid, showname)), False);
	$fesult = $form->addDate('date', 'Date', 'Date of Charge');
	$fesult = $form->addDrop('vendor', 'Vendor', 'Vendor for Charge', db_list(get_sql_const('vendor'), 'vendor'));
	$fesult = $form->addDrop('category', 'Category', 'Category for Charge', db_list(get_sql_const('category'), 'category'));
	$fesult = $form->addText('dscr', 'Description', 'Description of Charge');
	$fesult = $form->addMoney('price', 'Price', 'Amount of charge, no tax');
	$fesult = $form->addMoney('tax', 'Tax', 'Amount of tax paid, if any');
	$fesult = $form->addCheck('pending', 'Pending Payment');
	$fesult = $form->addCheck('needrepay', 'Reimbursable Charge');
	$fesult = $form->addCheck('gotrepay', 'Reimbursment Recieved');
	$fesult = $form->addHidden('rcptid', $rcpt);
	$html .= $form->output('Add Expense');
	return $html;
}

/**
 * Form to edit a budget item
 * 
 * @param integer Id of Budget Item
 * @global resource Database Connection
 * @global string MySQL Table Prefix
 * @global string TDTrac site address, for form actions
 * @return string HTML Output
 */
function budget_editform($id) {
	GLOBAL $db, $MYSQL_PREFIX, $TDTRAC_SITE;
	if ( !is_numeric($id) || $id < 1 ) { return perms_fail(); }
	$html  = "<h3>Edit Budget Expense</h3>\n";
	$sql = "SELECT showname, {$MYSQL_PREFIX}budget.* FROM `{$MYSQL_PREFIX}shows`, `{$MYSQL_PREFIX}budget` WHERE {$MYSQL_PREFIX}budget.id = {$id} AND {$MYSQL_PREFIX}budget.showid = {$MYSQL_PREFIX}shows.showid LIMIT 1;";
	$result = mysql_query($sql, $db);
	$row = mysql_fetch_array($result);
	if ( $row['imgid'] > 0 ) {
		$html .= "<div id=\"rcptbox\"><a href=\"rcpt.php?imgid={$row['imgid']}&amp;hires\" title=\"Zoom In (new window)\" target=\"_blank\"><img src=\"rcpt.php?imgid={$row['imgid']}\" alt=\"Reciept Image\" /></a></div>\n"; }
	$form = new tdform("{$TDTRAC_SITE}edit-budget", 'form1');
	
	$fesult = $form->addDrop('showid', 'Show', 'Show to Charge', db_list(get_sql_const('showid'), array(showid, showname)), False, $row['showid']);
	$fesult = $form->addDate('date', 'Date', 'Date of Charge', $row['date']);
	$fesult = $form->addDrop('vendor', 'Vendor', 'Vendor for Charge', db_list(get_sql_const('vendor'), 'vendor'), True, $row['vendor']);
	$fesult = $form->addDrop('category', 'Category', 'Category for Charge', db_list(get_sql_const('category'), 'category'), True, $row['category']);
	$fesult = $form->addText('dscr', 'Description', 'Description of Charge', $row['dscr']);
	$fesult = $form->addMoney('price', 'Price', 'Amount of charge, no tax', $row['price']);
	$fesult = $form->addMoney('tax', 'Tax', 'Amount of tax paid, if any', $row['tax']);
	$fesult = $form->addCheck('pending', 'Pending Payment', null, $row['pending']);
	$fesult = $form->addCheck('needrepay', 'Reimbursable Charge', null, $row['needrepay']);
	$fesult = $form->addCheck('gotrepay', 'Reimbursment Recieved', null, $row['gotrepay']);
	$fesult = $form->addHidden('id', $id);
	if ( isset($_REQUEST['redir-to']) ) { $form->addHidden('redir-to', $_REQUEST['redir-to']); }
	$html .= $form->output('Update Expense');
	return $html;
}

/**
 * Form to view a budget item
 * 
 * @param integer Id of Budget Item
 * @global resource Database Connection
 * @global string MySQL Table Prefix
 * @global string TDTrac site address, for form actions
 * @return string HTML Output
 */
function budget_viewitem($id) {
	GLOBAL $db, $MYSQL_PREFIX, $TDTRAC_SITE;
	if ( !is_numeric($id) || $id < 1 ) { return perms_fail(); }
	$html  = "<h3>Budget Expense</h3>\n";
	$sql = "SELECT showname, {$MYSQL_PREFIX}budget.* FROM `{$MYSQL_PREFIX}shows`, `{$MYSQL_PREFIX}budget` WHERE {$MYSQL_PREFIX}budget.id = {$id} AND {$MYSQL_PREFIX}budget.showid = {$MYSQL_PREFIX}shows.showid LIMIT 1;";
	$result = mysql_query($sql, $db);
	$row = mysql_fetch_array($result);
	if ( $row['imgid'] > 0 ) {
		$html .= "<div id=\"rcptbox\"><a href=\"rcpt.php?imgid={$row['imgid']}&amp;hires\" title=\"Zoom In (new window)\" target=\"_blank\"><img src=\"rcpt.php?imgid={$row['imgid']}\" alt=\"Reciept Image\" /></a></div>\n"; }
		
	$form = new tdform("{$TDTRAC_SITE}del-budget", 'form1');
	
	$fesult = $form->addDrop('showid', 'Show', 'Show to Charge', db_list(get_sql_const('showid'), array(showid, showname)), False, $row['showid'], False);
	$fesult = $form->addDate('date', 'Date', 'Date of Charge', $row['date'], False);
	$fesult = $form->addDrop('vendor', 'Vendor', 'Vendor for Charge', db_list(get_sql_const('vendor'), 'vendor'), True, $row['vendor'], False);
	$fesult = $form->addDrop('category', 'Category', 'Category for Charge', db_list(get_sql_const('category'), 'category'), True, $row['category'], False);
	$fesult = $form->addText('dscr', 'Description', 'Description of Charge', $row['dscr'], False);
	$fesult = $form->addMoney('price', 'Price', 'Amount of charge, no tax', $row['price'], False);
	$fesult = $form->addMoney('tax', 'Tax', 'Amount of tax paid, if any', $row['tax'], False);
	$fesult = $form->addCheck('pending', 'Pending Payment', null, $row['pending'], False);
	$fesult = $form->addCheck('needrepay', 'Reimbursable Charge', null, $row['needrepay'], False);
	$fesult = $form->addCheck('gotrepay', 'Reimbursment Recieved', null, $row['gotrepay'], False);
	$html .= $form->output(null,null,True);
	
	return $html;
}

/**
 * Form to confirm the deletion of a budget item
 * 
 * @param integer Id of Budget Item
 * @global resource Database Connection
 * @global string MySQL Table Prefix
 * @global string TDTrac site address, for form actions
 * @return string HTML Output
 */
function budget_delform($id) {
	GLOBAL $db, $MYSQL_PREFIX, $TDTRAC_SITE;
	if ( !is_numeric($id) || $id < 1 ) { return perms_fail(); }
	$html  = "<h3>Remove Budget Expense</h3>\n";
	$sql = "SELECT showname, {$MYSQL_PREFIX}budget.* FROM `{$MYSQL_PREFIX}shows`, `{$MYSQL_PREFIX}budget` WHERE {$MYSQL_PREFIX}budget.id = {$id} AND {$MYSQL_PREFIX}budget.showid = {$MYSQL_PREFIX}shows.showid LIMIT 1;";
	$result = mysql_query($sql, $db);
	$row = mysql_fetch_array($result);
	if ( $row['imgid'] > 0 ) {
		$html .= "<div id=\"rcptbox\"><a href=\"rcpt.php?imgid={$row['imgid']}&amp;hires\" title=\"Zoom In (new window)\" target=\"_blank\"><img src=\"rcpt.php?imgid={$row['imgid']}\" alt=\"Reciept Image\" /></a></div>\n"; }
		
	$form = new tdform("{$TDTRAC_SITE}del-budget", 'form1');
	
	$fesult = $form->addDrop('showid', 'Show', 'Show to Charge', db_list(get_sql_const('showid'), array(showid, showname)), False, $row['showid'], False);
	$fesult = $form->addDate('date', 'Date', 'Date of Charge', $row['date'], False);
	$fesult = $form->addDrop('vendor', 'Vendor', 'Vendor for Charge', db_list(get_sql_const('vendor'), 'vendor'), True, $row['vendor'], False);
	$fesult = $form->addDrop('category', 'Category', 'Category for Charge', db_list(get_sql_const('category'), 'category'), True, $row['category'], False);
	$fesult = $form->addText('dscr', 'Description', 'Description of Charge', $row['dscr'], False);
	$fesult = $form->addMoney('price', 'Price', 'Amount of charge, no tax', $row['price'], False);
	$fesult = $form->addMoney('tax', 'Tax', 'Amount of tax paid, if any', $row['tax'], False);
	$fesult = $form->addCheck('pending', 'Pending Payment', null, $row['pending'], False);
	$fesult = $form->addCheck('needrepay', 'Reimbursable Charge', null, $row['needrepay'], False);
	$fesult = $form->addCheck('gotrepay', 'Reimbursment Recieved', null, $row['gotrepay'], False);
	$fesult = $form->addHidden('id', $id);
	if ( isset($_REQUEST['redir-to']) ) { $form->addHidden('redir-to', $_REQUEST['redir-to']); }
	$html .= $form->output('Confirm Delete');
	
	return $html;
}

/**
 * Logic to add a budget item
 * 
 * @global resource Database Connection
 * @global string MySQL Table Prefix
 */
function budget_add() {
	GLOBAL $db, $MYSQL_PREFIX;
	$taxxed = ( $_REQUEST['tax'] > 0 && is_numeric($_REQUEST['tax'])) ? $_REQUEST['tax'] : 0;
	$rcptid = ( $_REQUEST['rcptid'] > 0 && is_numeric($_REQUEST['rcptid'])) ? $_REQUEST['rcptid'] : 0;
	
	$sqlstring  = "INSERT INTO `{$MYSQL_PREFIX}budget` ";
	$sqlstring .= "( showid, price, tax, imgid, vendor, category, dscr, date, pending, needrepay, gotrepay )";
	$sqlstring .= " VALUES ( '%d','%f','%f','%d','%s','%s','%s','%s','%d','%d','%d' )";
	
	$sql = sprintf($sqlstring,
		intval($_REQUEST['showid']),
		floatval($_REQUEST['price']),
		floatval($taxxed),
		intval($rcptid),
		mysql_real_escape_string($_REQUEST['vendor']),
		mysql_real_escape_string($_REQUEST['category']),
		mysql_real_escape_string($_REQUEST['dscr']),
		mysql_real_escape_string($_REQUEST['date']),
		(($_REQUEST['pending'] == "y") ? "1" : "0"),
		(($_REQUEST['needrepay'] == "y") ? "1" : "0"),
		(($_REQUEST['gotrepay'] == "y") ? "1" : "0")
	);
	$result = mysql_query($sql, $db);
	$added = mysql_insert_id();
	
	if ( $rcptid > 0 ) {
		$sql = "UPDATE {$MYSQL_PREFIX}rcpts SET handled = '1' WHERE imgid = '{$rcptid}'";
		$result = mysql_query($sql, $db);
	}
	thrower("Expense #{$added} Added");
}

/**
 * Logic to edit a budget item
 * 
 * @param integer Budget item to edit
 * @global resource Database Connection
 * @global string MySQL Table Prefix
 */
function budget_edit_do($id) {
	GLOBAL $db, $MYSQL_PREFIX;
	if ( !is_numeric($id) || $id < 1 ) { thrower(perms_fail()); }
	
	$sqlstring  = "UPDATE `{$MYSQL_PREFIX}budget` SET showid = '%d', price = '%f', tax = '%f' , vendor = '%s', ";
	$sqlstring .= "category = '%s', dscr = '%s' , date = '%s', pending = '%d', needrepay = '%d', gotrepay = '%d'";
	$sqlstring .= " WHERE id = {$id}";
	
	$sql = sprintf($sqlstring,
		intval($_REQUEST['showid']),
		floatval($_REQUEST['price']),
		floatval($_REQUEST['tax']),
		mysql_real_escape_string($_REQUEST['vendor']),
		mysql_real_escape_string($_REQUEST['category']),
		mysql_real_escape_string($_REQUEST['dscr']),
		mysql_real_escape_string($_REQUEST['date']),
		(($_REQUEST['pending'] == "y") ? "1" : "0"),
		(($_REQUEST['needrepay'] == "y") ? "1" : "0"),
		(($_REQUEST['gotrepay'] == "y") ? "1" : "0")
	);

	$result = mysql_query($sql, $db);
	if ( isset($_REQUEST['redir-to']) ){
		$cleanredit = preg_replace("/\*/", "&", $_REQUEST['redir-to']);
		thrower("Expense #{$id} Updated", $cleanredit);
	} else { thrower("Expense #{$id} Updated"); }
}

/**
 * Logic to delete a budget item
 * 
 * @param integer Budget item to remove
 * @global resource Database Connection
 * @global string MySQL Table Prefix
 */
function budget_del_do($id) {
	GLOBAL $db, $MYSQL_PREFIX;
	if ( !is_numeric($id) || $id < 1 ) { thrower(perms_fail()); }
	$sql = "DELETE FROM `{$MYSQL_PREFIX}budget` WHERE id = '".intval($id)."'";
	$result = mysql_query($sql, $db);
	if ( isset($_REQUEST['redir-to']) ){
		$cleanredit = preg_replace("/\*/", "&", $_REQUEST['redir-to']);
		thrower("Expense #{$id} Deleted", $cleanredit);
	} else { thrower("Expense #{$id} Deleted"); }
}

/**
 * Form to select show budget to view
 * 
 * @global resource Database Connection
 * @global string MySQL Table Prefix
 * @global string TDTrac site address, for form actions
 * @return string HTML Output
 */
function budget_viewselect() {
	GLOBAL $db, $MYSQL_PREFIX, $TDTRAC_SITE;
	$sql = "SELECT showid, showname FROM `{$MYSQL_PREFIX}shows` ORDER BY created DESC";
	$result = mysql_query($sql, $db);
	$html  = "<h3>View Budget</h3>";
	$form = new tdform("{$TDTRAC_SITE}view-budget");
	
	$result = $form->addDrop('showid', 'Show Name', null, db_list(get_sql_const('showidall'), array(showid, showname)), False);
	$result = $form->addHidden('view-bud-do', true);
	$html .= $form->output("View Selected");
	
	return $html;
}

/**
 * Logic to display special budget type headings
 * 
 * @param integer ID of budget type
 * @global resource Database Connection
 * @global string MySQL Table Prefix
 * @return string HTML Output
 */
function budget_view_special($onlytype) {
	GLOBAL $db, $MYSQL_PREFIX;
	$sql = "SELECT showid FROM `{$MYSQL_PREFIX}shows` WHERE closed = 0 ORDER BY showid DESC";
	$rest = mysql_query($sql, $db);
	$newhtml = "";
	if ( $onlytype == 1 ) { $newhtml .= "<h3>Pending Payment Budget Items</h3><br /><br />\n"; }
	if ( $onlytype == 2 ) { $newhtml .= "<h3>All Reimbursment Budget Items</h3><br /><br />\n"; }
	if ( $onlytype == 3 ) { $newhtml .= "<h3>Reimbursment Paid Budget Items</h3><br /><br />\n"; }
	if ( $onlytype == 4 ) { $newhtml .= "<h3>Reimbursment UNPaid Budget Items</h3><br /><br />\n"; }
	while ( $row = mysql_fetch_array($rest) ) {
		$newhtml .= budget_view($row['showid'], $onlytype);
	}
	return $newhtml;
}


/** 
 * Logic to display a searched item
 * 
 * @param string keywords
 * @global resource Database Link
 * @global string User Name
 * @global string MySQL Table Prefix
 * @global string TDTrac site address for links
 * @return string HTML output
 * @since 1.3.1
 */
function budget_search($keywords) {
	GLOBAL $db, $user_name, $MYSQL_PREFIX, $TDTRAC_SITE;
	
	$sqlwhere  = "( category LIKE '%" . mysql_real_escape_string($keywords) . "%' OR "; 
	$sqlwhere .= "vendor LIKE '%" . mysql_real_escape_string($keywords) . "%' OR "; 
	$sqlwhere .= "date = '" . mysql_real_escape_string($keywords) . "' OR "; 
	$sqlwhere .= "dscr LIKE '%" . mysql_real_escape_string($keywords) . "%' )";
	
	$sql = "SELECT * FROM {$MYSQL_PREFIX}budget b, {$MYSQL_PREFIX}shows s WHERE b.showid = s.showid AND {$sqlwhere} ORDER BY b.showid DESC, category ASC, date ASC, vendor ASC";
	$result = mysql_query($sql, $db);
	
	$html = "<h3>Search Results</h3>\n";
	if ( mysql_num_rows($result) == 0 ) { return $html . "<br /><br /><h4>No Records Found!</h4>\n"; }
	
	$tabl = new tdtable("searchresult");
	$tabl->addHeader(array('Show', 'Date', 'Category', 'Vendor', 'Description', 'Price', 'Tax'));
	$tabl->addSubtotal('Show');
	$tabl->addCurrency('Price');
	$tabl->addCurrency('Tax');
	$tabl->addAction(array('bpend','breim','rview','bview'));
	if ( perms_checkperm($user_name, "editbudget") ) { $tabl->addAction(array('bedit', 'bdel')); }
	
	while( $line = mysql_fetch_array($result) ) {
		$tabl->addRow(array($line['showname'], $line['date'], $line['category'], $line['vendor'], $line['dscr'], $line['price'], $line['tax']), $line);
	}
	$html .= $tabl->output();
	return $html;
}

/**
 * Logic to display a show's budget
 * 
 * @param integer Id of Show
 * @param integer Special Budget type
 * @global resource Database Connection
 * @global string User name
 * @global string MySQL Table Prefix
 * @global bool Daily payrate vs. Hourly Payrate
 * @global double Default payrate
 * @global string TDTrac site address, for form actions
 * @return string HTML Output
 */
function budget_view($showid, $onlytype) {
	GLOBAL $db, $user_name, $MYSQL_PREFIX, $TDTRAC_DAYRATE, $TDTRAC_PAYRATE, $TDTRAC_SITE;
	if ( !is_numeric($showid) || $showid < 1 ) { return perms_fail(); }
	if ( $onlytype == 0 ) { $sqlwhere = ""; }
	if ( $onlytype == 1 ) { $sqlwhere = " AND pending = 1"; }
	if ( $onlytype == 2 ) { $sqlwhere = " AND needrepay = 1"; }
	if ( $onlytype == 3 ) { $sqlwhere = " AND gotrepay = 1"; }
	if ( $onlytype == 4 ) { $sqlwhere = " AND needrepay = 1 AND gotrepay = 0"; }
	$sql = "SELECT * FROM `{$MYSQL_PREFIX}shows` WHERE showid = '".intval($showid)."'";
	$editshow = perms_checkperm($user_name, "editshow");
	$editbudget = perms_checkperm($user_name, "editbudget"); 
	$result = mysql_query($sql, $db); 
	$html = "";
	$row = mysql_fetch_array($result);
	$html .= "<h3>{$row['showname']}</h3>\n";
	$html .= $editshow ? "<span class=\"overright\">[<a href=\"{$TDTRAC_SITE}edit-show&amp;id={$row['showid']}\">Edit</a>]</span>\n" : "";
	$html .= "<ul class=\"datalist\"><li><strong>Company</strong>: {$row['company']}</li>\n";
	$html .= "<li><strong>Venue</strong>: {$row['venue']}</li>\n";
	$html .= "<li><strong>Dates</strong>: {$row['dates']}</li>\n";
	$html .= "</ul>\n";

	$sql = "SELECT * FROM {$MYSQL_PREFIX}budget WHERE showid = {$showid}{$sqlwhere} ORDER BY category ASC, date ASC, vendor ASC";
	$result = mysql_query($sql, $db); $intr = 0; $tot = 0; $tottax = 0;
	if ( mysql_num_rows($result) < 1 ) { return $html; }
	if ( $onlytype == 0 ) {
		$html .= "<h4>Materials Expenses</h4>";
		$html .= "<span class=\"upright\">[<a href=\"{$TDTRAC_SITE}email-budget&amp;id={$row['showid']}\">E-Mail To Self</a>]</span>";
	}
	$tabl = new tdtable("budget", 'datatable', true, "view-budget*showid={$showid}*view-bud-do=1");
	$tabl->addHeader(array('Date', 'Vendor', 'Category', 'Description', 'Price', 'Tax'));
	$tabl->addSubtotal('Category');
	$tabl->addCurrency('Price');
	$tabl->addCurrency('Tax');
	$tabl->addAction(array('bpend','breim','rview','bview'));
	if ( $editbudget ) { $tabl->addAction(array('bedit', 'bdel')); }
	while ( $row = mysql_fetch_array($result) ) {
		$tabl->addRow(array($row['date'], $row['vendor'], $row['category'], $row['dscr'], $row['price'], $row['tax']), $row);
	}
	$html .= $tabl->output();
	
	if ( $onlytype > 0 ) { return $html . "<br /><br /><br />"; } // Show payroll only on full budget report
	
	$html .= "<br /><br /><h4>Payroll Expenses</h4>\n";
	
	$tabl = new tdtable("hours", "datatable", False);
	$tabl->addHeader(array('Employee',(($TDTRAC_DAYRATE)?"Days":"Hours")." Worked",'Price'));
	$tabl->addNumber((($TDTRAC_DAYRATE)?"Days":"Hours")." Worked");
	$tabl->addCurrency('Price');
	
	$sql = "SELECT SUM(worked) as days, payrate, CONCAT(first, ' ', last) as name FROM {$MYSQL_PREFIX}users u, {$MYSQL_PREFIX}hours h WHERE u.userid = h.userid AND h.showid = '".intval($showid)."' GROUP BY h.userid ORDER BY last ASC";
	$result = mysql_query($sql, $db);
	
	while ( $row = mysql_fetch_array($result) ) {
		$tabl->addRow(array($row['name'], $row['days'], $row['days'] * $row['payrate']), $row);
	}
	$html .= $tabl->output();
	return $html;
}

?>
