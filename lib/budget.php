<?php
function budget_addform() {
	GLOBAL $db, $MYSQL_PREFIX;
	$html  = "<h2>Add Budget Expense</h2>\n";
	$html .= "<div id=\"genform\"><form method=\"post\" action=\"add-budget\" name=\"form1\">\n";
	$html .= "<div class=\"frmele\">Show: <select tabindex=\"1\" style=\"width: 25em;\" name=\"showid\">\n";
	$sql = "SELECT showname, showid FROM {$MYSQL_PREFIX}shows ORDER BY created DESC;";
	$result = mysql_query($sql, $db);
	while ( $row = mysql_fetch_array($result) ) {
		$html .= "<option value=\"{$row['showid']}\">{$row['showname']}</option>\n";
	}
	$html .= "</select></div>";
	$html .= "<div class=\"frmele\">Date: <input type=\"text\" size=\"18\" name=\"date\" id=\"date\" style=\"margin-right: 2px\" />\n";
	$html .= "<a href=\"#\" onClick=\"cal.select(document.forms['form1'].date,'anchor1','yyyy-MM-dd'); return false;\" name=\"anchor1\" id=\"anchor1\">[calendar popup]</a></div>\n";
	$html .= "<div class=\"frmele\">New Vendor: <input type=\"text\" size=\"35\" name=\"vendornew\" /></div>\n";
	$html .= "<div class=\"frmele\">Old Vendor: <select style=\"width: 25em\" name=\"vendor\" />\n";
	$html .= "<option value=\"--NEW--\">^--NEW</option>\n";
        $sql = "SELECT vendor FROM `{$MYSQL_PREFIX}budget` GROUP BY vendor ORDER BY COUNT(vendor) DESC, vendor ASC";
        $result = mysql_query($sql, $db);
        while ( $row = mysql_fetch_array($result) ) {
                $html .= "<option value=\"{$row['vendor']}\">{$row['vendor']}</option>\n";
        }
	$html .= "</select></div>\n";
	$html .= "<div class=\"frmele\">Description: <input type=\"text\" size=\"35\" name=\"dscr\" /></div>\n";
	$html .= "<div class=\"frmele\">Price: $<input type=\"text\" size=\"34\" name=\"price\" /></div>\n";
	$html .= "<div class=\"frmele\"><input type=\"submit\" value=\"Add Expense\" /></div></form></div>\n";
	return $html;
}

function budget_editform($id) {
	GLOBAL $db, $MYSQL_PREFIX;
	$html  = "<h2>Edit Budget Expense</h2>\n";
	$html .= "<div id=\"genform\"><form method=\"post\" action=\"edit-budget\" name=\"form1\">\n";
	$html .= "<div class=\"frmele\">Show: <select tabindex=\"1\" style=\"width: 25em;\" name=\"showid\">\n";
	$sql = "SELECT showname, {$MYSQL_PREFIX}budget.* FROM {$MYSQL_PREFIX}shows, {$MYSQL_PREFIX}budget WHERE {$MYSQL_PREFIX}budget.id = {$id} AND {$MYSQL_PREFIX}budget.showid = {$MYSQL_PREFIX}shows.showid LIMIT 1;";
	$result = mysql_query($sql, $db);
	$row = mysql_fetch_array($result);
	$html .= "<option value=\"{$row['showid']}\">{$row['showname']}</option>\n";
	$html .= "</select></div>";
	$html .= "<div class=\"frmele\">Date: <input type=\"text\" size=\"18\" name=\"date\" id=\"date\" style=\"margin-right: 2px\" value=\"{$row['date']}\" />\n";
	$html .= "<a href=\"#\" onClick=\"cal.select(document.forms['form1'].date,'anchor1','yyyy-MM-dd'); return false;\" name=\"anchor1\" id=\"anchor1\">[calendar popup]</a></div>\n";
	$html .= "<div class=\"frmele\">New Vendor: <input type=\"text\" size=\"35\" name=\"vendornew\" value=\"{$row['vendor']}\"/></div>\n";
	$html .= "<div class=\"frmele\">Old Vendor: <select style=\"width: 25em\" name=\"vendor\" />\n";
	$html .= "<option value=\"--NEW--\">^--NEW</option>\n";
        $sql = "SELECT vendor FROM `{$MYSQL_PREFX}budget` GROUP BY vendor ORDER BY COUNT(vendor) DESC, vendor ASC";
        $result2 = mysql_query($sql, $db);
        while ( $row2 = mysql_fetch_array($result2) ) {
                $html .= "<option value=\"{$row['vendor']}\">{$row['vendor']}</option>\n";
        }
	$html .= "</select></div>\n";
	$html .= "<div class=\"frmele\">Description: <input type=\"text\" size=\"35\" name=\"dscr\" value=\"{$row['dscr']}\" /></div>\n";
	$html .= "<div class=\"frmele\">Price: $<input type=\"text\" size=\"34\" name=\"price\" value=\"{$row['price']}\" /></div>\n";
	$html .= "<input type=\"hidden\" name=\"id\" value=\"{$id}\" />\n";
	$html .= "<div class=\"frmele\"><input type=\"submit\" value=\"Commit\" /></div></form></div>\n";
	return $html;
}

function budget_delform($id) {
	GLOBAL $db, $MYSQL_PREFIX;
	$html  = "<h2>Remove Budget Expense</h2>\n";
	$html .= "<div id=\"genform\"><form method=\"post\" action=\"del-budget\" name=\"form1\">\n";
	$html .= "<div class=\"frmele\">Show: <select tabindex=\"1\" style=\"width: 25em;\" name=\"showid\" disabled=\"disabled\" >\n";
	$sql = "SELECT showname, {$MYSQL_PREFIX}budget.* FROM {$MYSQL_PREFIX}shows, {$MYSQL_PREFIX}budget WHERE {$MYSQL_PREFIX}budget.id = {$id} AND {$MYSQL_PREFIX}budget.showid = {$MYSQL_PREFIX}shows.showid LIMIT 1;";
	$result = mysql_query($sql, $db);
	$row = mysql_fetch_array($result);
	$html .= "<option value=\"{$row['showid']}\">{$row['showname']}</option>\n";
	$html .= "</select></div>";
	$html .= "<div class=\"frmele\">Date: <input type=\"text\" size=\"18\" name=\"date\" id=\"date\" style=\"margin-right: 2px\" value=\"{$row['date']}\" disabled=\"disabled\" />\n";
	$html .= "<a href=\"#\" onClick=\"cal.select(document.forms['form1'].date,'anchor1','yyyy-MM-dd'); return false;\" name=\"anchor1\" id=\"anchor1\">[calendar popup]</a></div>\n";
	$html .= "<div class=\"frmele\">New Vendor: <input type=\"text\" size=\"35\" name=\"vendornew\" value=\"{$row['vendor']}\" disabled=\"disabled\" /></div>\n";
	$html .= "<div class=\"frmele\">Description: <input type=\"text\" size=\"35\" name=\"dscr\" value=\"{$row['dscr']}\" disabled=\"disabled\" /></div>\n";
	$html .= "<div class=\"frmele\">Price: $<input type=\"text\" size=\"34\" name=\"price\" value=\"{$row['price']}\" disabled=\"disabled\" /></div>\n";
	$html .= "<input type=\"hidden\" name=\"id\" value=\"{$id}\" />\n";
	$html .= "<div class=\"frmele\"><input type=\"submit\" value=\"Confirm Delete\" /></div></form></div>\n";
	return $html;
}

function budget_add() {
	GLOBAL $db, $MYSQL_PREFIX;
	$sql  = "INSERT INTO {$MYSQL_PREFIX}budget ( showid, price, vendor, dscr, date ) VALUES ( {$_REQUEST['showid']} , '{$_REQUEST['price']}' , ";
        if ( ($_REQUEST['vendor'] == "--NEW--") && !($_REQUEST['vendornew'] == "") ) {
		$sql .= "'{$_REQUEST['vendornew']}' , ";
	} else { $sql .= "'{$_REQUEST['vendor']}' , "; }
	$sql .= "'{$_REQUEST['dscr']}' , '{$_REQUEST['date']}' )";
	$result = mysql_query($sql, $db);
	thrower("Expense Added");
}

function budget_edit_do($id) {
	GLOBAL $db, $MYSQL_PREFIX;
	$sql  = "UPDATE {$MYSQL_PREFIX}budget SET price = '{$_REQUEST['price']}' , vendor = ";
        if ( ($_REQUEST['vendor'] == "--NEW--") && !($_REQUEST['vendornew'] == "") ) {
                $sql .= "'{$_REQUEST['vendornew']}' , ";
        } else { $sql .= "'{$_REQUEST['vendor']}' , "; }
        $sql .= "dscr = '{$_REQUEST['dscr']}' , date = '{$_REQUEST['date']}' WHERE id = {$id}";
	$result = mysql_query($sql, $db);
	thrower("Expense #{$id} Updated");
}

function budget_del_do($id) {
	GLOBAL $db, $MYSQL_PREFIX;
	$sql = "DELETE FROM {$MYSQL_BUDGET}budget WHERE id = {$id}";
	$result = mysql_query($sql, $db);
	thrower("Expense #{$id} Removed");
}

function budget_viewselect() {
	GLOBAL $db, $MYSQL_PREFIX;
	$sql = "SELECT showid, showname FROM {$MYSQL_PREFIX}shows ORDER BY created DESC";
	$result = mysql_query($sql, $db);
	$html  = "<h2>View Budget</h2>";
	$html .= "<div id=\"genform\"><form method=\"post\" action=\"/view-budget\">\n";
	$html .= "<div class=\"frmele\"><select style=\"width: 25em\" name=\"showid\">\n";
	while ( $row = mysql_fetch_array($result) ) {
		$html .= "<option value=\"{$row['showid']}\">{$row['showname']}</option>\n";
	}
	$html .= "</select></div>\n";
	$html .= "<div class=\"frmele\"><input type=\"submit\" value=\"View Selected\" /></div></form></div>\n";
	return $html;
}

function budget_view($showid) {
	GLOBAL $db, $user_name, $MYSQL_PREFIX;
        $sql = "SELECT * FROM {$MYSQL_PREFIX}shows WHERE showid = {$showid}";
        $editshow = perms_checkperm($user_name, "editshow");
	$editbudget = perms_checkperm($user_name, "editbudget"); 
        $result = mysql_query($sql, $db); 
        $html = "";
        $row = mysql_fetch_array($result);
        $html .= "<h2>{$row['showname']}</h2><p><ul>\n";
        $html .= $editshow ? "<div style=\"float: right\">[<a href=\"/edit-show?id={$row['showid']}\">Edit</a>]</div>\n" : "";
        $html .= "<li><strong>Company</strong>: {$row['company']}</li>\n";
        $html .= "<li><strong>Venue</strong>: {$row['venue']}</li>\n";
        $html .= "<li><strong>Dates</strong>: {$row['dates']}</li>\n";
        $html .= "</ul></p>\n";

	$html .= "<h2>Materials Expenses</h2><table id=\"budget\">\n";
	$html .= "<tr><th>Date</th><th>Vendor</th><th>Description</th><th>Price</th>";
	$html .= $editbudget ? "<th>Edit</th>" : "";
	$html .= $editbudget ? "<th>Del</th>" : "";
	$html .= "</tr>\n";
	$sql = "SELECT * FROM {$MYSQL_PREFIX}budget WHERE showid = {$showid} ORDER BY date ASC, vendor ASC";
	$result = mysql_query($sql, $db); $intr = 0; $tot = 0;
	while ( $row = mysql_fetch_array($result) ) {
		$intr++;
		$html .= "<tr".((($intr % 2) == 0 ) ? " class=\"odd\"" : "")."><td>{$row['date']}</td><td>{$row['vendor']}</td><td>{$row['dscr']}</td><td style=\"text-align: right\">$";
                $tot += $row['price'];
		$html .= number_format($row['price'], 2);
		$html .= "</td>";
		$html .= $editbudget ? "<td style=\"text-align: center\"><a href=\"/edit-budget?id={$row['id']}\">[-]</a></td>" : "";
		$html .= $editbudget ? "<td style=\"text-align: center\"><a href=\"/del-budget?id={$row['id']}\">[x]</a></td>" : "";
		$html .= "</tr>\n";
	}
	$html .= "<tr style=\"background-color: #FFCCFF\"><td></td><td></td><td style=\"text-align: center\">-=- TOTAL -=-</td><td style=\"text-align: right\">$" . number_format($tot, 2) . "</td></tr>\n";
	$html .= "</table>\n";

	$html .= "<h2>Payroll Expenses</h2><table id=\"budget\">\n";
	$html .= "<tr><th>Employee</th><th>Days Worked</th><th>Price</th></tr>\n";
	$sql = "SELECT SUM(worked) as days, CONCAT(first, ' ', last) as name FROM {$MYSQL_PREFIX}users u, {$MYSQL_PREFIX}hours h WHERE u.userid = h.userid AND h.showid = {$showid} GROUP BY h.userid ORDER BY last ASC";
	$result = mysql_query($sql, $db);
	$tot = 0; $intr = 0;
	while ( $row = mysql_fetch_array($result) ) {
		$intr++;
		$tot += $row['days'];
		$html .= "<tr".((($intr % 2) == 0 ) ? " class=\"odd\"" : "")."><td>{$row['name']}</td><td>{$row['days']}</td><td style=\"text-align: right\">$" . number_format($row['days'] * 75, 2) . "</td></tr>\n";
	}
	$html .= "<tr style=\"background-color: #FFCCFF\"><td></td><td>{$tot}</td><td style=\"text-align: right\">$" . number_format($tot * 75, 2) . "</td></tr>\n";
	$html .= "</table>\n";
        return $html;

}

?>
