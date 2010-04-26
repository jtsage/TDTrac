<?php
/**
 * TDTrac Budget Functions
 * 
 * Contains all budget related functions. 
 * @package tdtrac
 * @version 1.3.0
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
	$html  = "<h2>Add Budget Expense</h2>\n";
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
	$html  = "<h2>Edit Budget Expense</h2>\n";
	$sql = "SELECT showname, {$MYSQL_PREFIX}budget.* FROM {$MYSQL_PREFIX}shows, {$MYSQL_PREFIX}budget WHERE {$MYSQL_PREFIX}budget.id = {$id} AND {$MYSQL_PREFIX}budget.showid = {$MYSQL_PREFIX}shows.showid LIMIT 1;";
	$result = mysql_query($sql, $db);
	$row = mysql_fetch_array($result);
	if ( $row['imgid'] > 0 ) {
		$html .= "<div id=\"rcptbox\"><br /><br /><a href=\"rcpt.php?imgid={$row['imgid']}&hires\" title=\"Zoom In (new window)\" target=\"_blank\"><img src=\"rcpt.php?imgid={$row['imgid']}\" /></a></div>\n"; }
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
	$html .= $form->output('Update Expense');
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
	$html  = "<h2>Remove Budget Expense</h2>\n";
	$sql = "SELECT showname, {$MYSQL_PREFIX}budget.* FROM {$MYSQL_PREFIX}shows, {$MYSQL_PREFIX}budget WHERE {$MYSQL_PREFIX}budget.id = {$id} AND {$MYSQL_PREFIX}budget.showid = {$MYSQL_PREFIX}shows.showid LIMIT 1;";
	$result = mysql_query($sql, $db);
	$row = mysql_fetch_array($result);
	if ( $row['imgid'] > 0 ) {
		$html .= "<div id=\"rcptbox\"><br /><br /><a href=\"rcpt.php?imgid={$row['imgid']}&hires\" title=\"Zoom In (new window)\" target=\"_blank\"><img src=\"rcpt.php?imgid={$row['imgid']}\" /></a></div>\n"; }
		
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
	$taxxed = ( $_REQUEST['tax'] > 0 ) ? $_REQUEST['tax'] : 0;
	$rcptid = ( $_REQUEST['rcptid'] > 0 ) ? $_REQUEST['rcptid'] : 0;
	$sql  = "INSERT INTO {$MYSQL_PREFIX}budget ( showid, price, tax, imgid, vendor, category, dscr, date, pending, needrepay, gotrepay ) VALUES ( {$_REQUEST['showid']} , '{$_REQUEST['price']}' , '{$taxxed}' , '{$rcptid}' , '{$_REQUEST['vendor']}' , '{$_REQUEST['category']}' , "; 
	$sql .= "'{$_REQUEST['dscr']}' , '{$_REQUEST['date']}' , ".(($_REQUEST['pending'] == "y") ? "1" : "0")." , ".(($_REQUEST['needrepay'] == "y") ? "1" : "0")." , ".(($_REQUEST['gotrepay'] == "y") ? "1" : "0")." )";
	$result = mysql_query($sql, $db);
	if ( $rcptid > 0 ) {
		$sql = "UPDATE {$MYSQL_PREFIX}rcpts SET handled = '1' WHERE imgid = '{$rcptid}'";
		$result = mysql_query($sql, $db);
	}
	thrower("Expense Added");
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
	$sql  = "UPDATE {$MYSQL_PREFIX}budget SET showid = {$_REQUEST['showid']} , price = '{$_REQUEST['price']}' , tax = '{$_REQUEST['tax']}' , vendor = '{$_REQUEST['vendor']}' , category = '{$_REQUEST['category']}' , ";
	$sql .= "dscr = '{$_REQUEST['dscr']}' , date = '{$_REQUEST['date']}'";
	$sql .= " , pending = ".(($_REQUEST['pending'] == "y") ? "1" : "0");
	$sql .= " , needrepay = ".(($_REQUEST['needrepay'] == "y") ? "1" : "0");
	$sql .= " , gotrepay = ".(($_REQUEST['gotrepay'] == "y") ? "1" : "0");
	$sql .= " WHERE id = {$id}";
	$result = mysql_query($sql, $db);
	thrower("Expense #{$id} Updated");
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
	$sql = "DELETE FROM {$MYSQL_PREFIX}budget WHERE id = {$id}";
	$result = mysql_query($sql, $db);
	thrower("Expense #{$id} Removed");
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
	$sql = "SELECT showid, showname FROM {$MYSQL_PREFIX}shows ORDER BY created DESC";
	$result = mysql_query($sql, $db);
	$html  = "<h2>View Budget</h2>";
	$form = new tdform("{$TDTRAC_SITE}view-budget");
	
	$result = $form->addDrop('showid', 'Show Name', null, db_list(get_sql_const('showidall'), array(showid, showname)), False);
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
	$sql = "SELECT showid FROM {$MYSQL_PREFIX}shows WHERE closed = 0 ORDER BY showid DESC";
	$rest = mysql_query($sql, $db);
	$newhtml = "";
	if ( $onlytype == 1 ) { $newhtml .= "<h2>Pending Payment Budget Items</h2>\n"; }
	if ( $onlytype == 2 ) { $newhtml .= "<h2>All Reimbursment Budget Items</h2>\n"; }
	if ( $onlytype == 3 ) { $newhtml .= "<h2>Reimbursment Paid Budget Items</h2>\n"; }
	if ( $onlytype == 4 ) { $newhtml .= "<h2>Reimbursment UNPaid Budget Items</h2>\n"; }
	while ( $row = mysql_fetch_array($rest) ) {
		$newhtml .= budget_view($row['showid'], $onlytype);
	}
	return $newhtml;
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
	if ( $onlytype == 0 ) { $sqlwhere = ""; }
	if ( $onlytype == 1 ) { $sqlwhere = " AND pending = 1"; }
	if ( $onlytype == 2 ) { $sqlwhere = " AND needrepay = 1"; }
	if ( $onlytype == 3 ) { $sqlwhere = " AND gotrepay = 1"; }
	if ( $onlytype == 4 ) { $sqlwhere = " AND needrepay = 1 AND gotrepay = 0"; }
	$sql = "SELECT * FROM {$MYSQL_PREFIX}shows WHERE showid = {$showid}";
	$editshow = perms_checkperm($user_name, "editshow");
	$editbudget = perms_checkperm($user_name, "editbudget"); 
	$result = mysql_query($sql, $db); 
	$html = "";
	$row = mysql_fetch_array($result);
	$html .= "<h2>{$row['showname']}</h2><p><ul>\n";
	$html .= $editshow ? "<div style=\"float: right\">[<a href=\"{$TDTRAC_SITE}edit-show&id={$row['showid']}\">Edit</a>]</div>\n" : "";
	$html .= "<li><strong>Company</strong>: {$row['company']}</li>\n";
	$html .= "<li><strong>Venue</strong>: {$row['venue']}</li>\n";
	$html .= "<li><strong>Dates</strong>: {$row['dates']}</li>\n";
	$html .= "</ul></p>\n";

	$sql = "SELECT * FROM {$MYSQL_PREFIX}budget WHERE showid = {$showid}{$sqlwhere} ORDER BY category ASC, date ASC, vendor ASC";
	$result = mysql_query($sql, $db); $intr = 0; $tot = 0; $tottax = 0;
	if ( mysql_num_rows($result) < 1 ) { return $html; }
	if ( $onlytype == 0 ) {
		$html .= "<h2>Materials Expenses</h2><br />";
		$html .= "<div style=\"float: right\">[<a href=\"{$TDTRAC_SITE}email-budget&id={$row['showid']}\">E-Mail To Self</a>]</div>\n";
	}
	$html .= "<table id=\"budget\">\n";
	$html .= "<tr><th>Date</th><th>Vendor</th><th>Category</th><th>Description</th><th>Price</th><th>Tax</th>";
	$html .= "<th>Pending</th><th>Reimburse</th>\n";
	$html .= "<th>Reciept</th>";
	$html .= $editbudget ? "<th>Edit</th>" : "";
	$html .= $editbudget ? "<th>Del</th>" : "";
	$html .= "</tr>\n";
	$last = "";
	while ( $row = mysql_fetch_array($result) ) {
		if ( $last != "" && $last != $row['category'] ) {
			$html .= "<tr style=\"background-color: #DDCCDD\"><td></td><td></td><td>{$last}</td><td style=\"text-align: center\">-=- SUB-TOTAL -=-</td><td style=\"text-align: right\">$" . number_format($subtot, 2) . "</td><td style=\"text-align: right\">$".number_format($subtax,2)."</td></tr>\n"; $subtot = 0; $subtax = 0;
		} 
		$intr++;
		$html .= "<tr".((($intr % 2) == 0 ) ? " class=\"odd\"" : "")."><td>{$row['date']}</td><td>{$row['vendor']}</td><td>{$row['category']}</td><td>{$row['dscr']}</td><td style=\"text-align: right\">$";
		$tottax += $row['tax']; $subtax += $row['tax'];
		$tot += $row['price']; $subtot += $row['price'];
		$html .= number_format($row['price'], 2);
		$html .= "</td><td style=\"text-align: right\">$";
		$html .= number_format($row['tax'], 2);
		$html .= "</td><td style=\"text-align: center\">" . (($row['pending'] == 1) ? "YES" : "NO") . "</td>";
		$html .= "<td style=\"text-align: center\">" . (($row['needrepay'] == 1) ? (($row['gotrepay'] == 1) ? "PAID" : "UNPAID") : "N/A") . "</td>";
		$html .= ( $row['imgid'] > 0 ) ? "<td style=\"text-align: center\"><a href=\"/rcpt.php?imgid={$row['imgid']}&hires\" target=\"_blank\">[^]</a></td>" : "<td style=\"text-align: center; font-size: .7em;\">none</td>";
		$html .= $editbudget ? "<td style=\"text-align: center\"><a href=\"{$TDTRAC_SITE}edit-budget&id={$row['id']}\">[-]</a></td>" : "";
		$html .= $editbudget ? "<td style=\"text-align: center\"><a href=\"{$TDTRAC_SITE}del-budget&id={$row['id']}\">[x]</a></td>" : "";
		$html .= "</tr>\n";
		$last = $row['category'];
	}
	$html .= "<tr style=\"background-color: #DDCCDD\"><td></td><td></td><td>{$last}</td><td style=\"text-align: center\">-=- SUB-TOTAL -=-</td><td style=\"text-align: right\">$" . number_format($subtot, 2) . "</td><td style=\"text-align: right\">$".number_format($subtax,2)."</td></tr>\n";
	$html .= "<tr style=\"background-color: #FFCCFF\"><td></td><td></td><td></td><td style=\"text-align: center\">-=- TOTAL -=-</td><td style=\"text-align: right\">$" . number_format($tot, 2) . "</td><td style=\"text-align: right\">$".number_format($tottax,2)."</td></tr>\n";
	$html .= "</table>\n";
	if ( $onlytype > 0 ) { return $html; }
	$html .= "<h2>Payroll Expenses</h2><table id=\"budget\">\n";
	$html .= "<tr><th>Employee</th><th>".(($TDTRAC_DAYRATE)?"Days":"Hours")." Worked</th><th>Price</th></tr>\n";
	$sql = "SELECT SUM(worked) as days, payrate, CONCAT(first, ' ', last) as name FROM {$MYSQL_PREFIX}users u, {$MYSQL_PREFIX}hours h WHERE u.userid = h.userid AND h.showid = {$showid} GROUP BY h.userid ORDER BY last ASC";
	$result = mysql_query($sql, $db);
	$tot = 0; $intr = 0; $mtot = 0;
	while ( $row = mysql_fetch_array($result) ) {
		$intr++;
		$tot += $row['days'];
		$mtot += $row['days'] * $row['payrate'];
		$html .= "<tr".((($intr % 2) == 0 ) ? " class=\"odd\"" : "")."><td>{$row['name']}</td><td>{$row['days']}</td><td style=\"text-align: right\">$" . number_format($row['days'] * $row['payrate'], 2) . "</td></tr>\n";
	}
	$html .= "<tr style=\"background-color: #FFCCFF\"><td></td><td>{$tot}</td><td style=\"text-align: right\">$" . number_format($mtot, 2) . "</td></tr>\n";
	$html .= "</table>\n";
	return $html;
}

?>
