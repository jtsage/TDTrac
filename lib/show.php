<?php
function show_add_form() {
	$html  = "<h2>Add A Show</h2>\n";
	$html .= "<div id=\"genform\"><form method=\"post\" action=\"/add-show\">\n";
	$html .= "<div class=\"frmele\">Show Name:<input type=\"text\" size=\"35\" name=\"showname\" /></div>\n";
	$html .= "<div class=\"frmele\">Show Company:<input type=\"text\" size=\"35\" name=\"company\" /></div>\n";
	$html .= "<div class=\"frmele\">Show Venue:<input type=\"text\" size=\"35\" name=\"venue\" /></div>\n";
	$html .= "<div class=\"frmele\">Show Dates:<input type=\"text\" size=\"35\" name=\"dates\" /></div>\n";
	$html .= "<div class=\"frmele\"><input type=\"submit\" value=\"Add Show\" /></div></form></div>\n";
	return $html;
}

function show_add_do() {
	GLOBAL $db, $MYSQL_PREFIX;
	$sql = "INSERT INTO {$MYSQL_PREFIX}shows ( showname, company, venue, dates ) VALUES ( '{$_REQUEST['showname']}', '{$_REQUEST['company']}', '{$_REQUEST['venue']}', '{$_REQUEST['dates']}' )";
	$result = mysql_query($sql, $db);
	thrower("Show {$_REQUEST['showname']} Added");
}

function show_view() {
	GLOBAL $db, $user_name, $MYSQL_PREFIX;
	$sql = "SELECT * FROM {$MYSQL_PREFIX}shows ORDER BY created DESC";
	$result = mysql_query($sql, $db);
	$editlink = perms_isadmin($user_name);
	$html = "";
	while ( $row = mysql_fetch_array($result) ) {
		$html .= "<h2>{$row['showname']}</h2><p><ul>\n";
		$html .= $editlink ? "<div style=\"float: right\">[<a href=\"/edit-show?id={$row['showid']}\">Edit</a>]</div>\n" : "";
		$html .= "<li><strong>Company</strong>: {$row['company']}</li>\n";
		$html .= "<li><strong>Venue</strong>: {$row['venue']}</li>\n";
		$html .= "<li><strong>Dates</strong>: {$row['dates']}</li>\n";
		$html .= "</ul></p>\n";
	}
	return $html;
}

function show_edit_form($showid) {
	GLOBAL $db, $MYSQL_PREFIX;
	$sql = "SELECT showname, company, venue, dates FROM {$MYSQL_PREFIX}shows WHERE showid = {$showid} LIMIT 1";
	$result = mysql_query($sql, $db);
	$row = mysql_fetch_array($result);
        $html  = "<h2>Edit A Show</h2>\n";
        $html .= "<div id=\"genform\"><form method=\"post\" action=\"/edit-show\">\n";
        $html .= "<div class=\"frmele\">Show Name:<input type=\"text\" size=\"35\" name=\"showname\" value=\"{$row['showname']}\" /></div>\n";
        $html .= "<div class=\"frmele\">Show Company:<input type=\"text\" size=\"35\" name=\"company\" value=\"{$row['company']}\" /></div>\n";
        $html .= "<div class=\"frmele\">Show Venue:<input type=\"text\" size=\"35\" name=\"venue\" value=\"{$row['venue']}\" /></div>\n";
        $html .= "<div class=\"frmele\">Show Dates:<input type=\"text\" size=\"35\" name=\"dates\" value=\"{$row['dates']}\" /></div>\n";
	$html .= "<input type=\"hidden\" name=\"showid\" value=\"{$showid}\" />\n";
        $html .= "<div class=\"frmele\"><input type=\"submit\" value=\"Commit\" /></div></form></div>\n";
        return $html;
}

function show_edit_do($showid) {
	GLOBAL $db, $MYSQL_PREFIX;
	$sql = "UPDATE {$MYSQL_PREFIX}shows SET showname='{$_REQUEST['showname']}' , company='{$_REQUEST['company']}' , venue='{$_REQUEST['venue']}' , dates='{$_REQUEST['dates']}' WHERE showid = {$showid}";
	$result = mysql_query($sql, $db);
	thrower("Show {$_REQUEST['showname']} Updated");
}
?>
