<?php
/**
 * TDTrac Show Functions
 * 
 * Contains all show related functions. 
 * @package tdtrac
 * @version 1.3.0
 */

/**
 * Show the show add form
 * 
 * @global string Site address for links
 * @return string HTML output
 */
function show_add_form() {
	GLOBAL $TDTRAC_SITE;
	$html  = "<h3>Add A Show</h3>\n";
	$form = new tdform("{$TDTRAC_SITE}add-show");
	
	$result = $form->addText('showname', 'Show Name');
	$result = $form->addText('company', 'Show Company');
	$result = $form->addText('venue', 'Show Venue');
	$result = $form->addText('dates', 'Show Dates');
	
	$html .= $form->output('Add Show');
	return $html;
}

/**
 * Logic to add show to database
 * 
 * @global resource Database Link
 * @global string MySQL Table Prefix
 */
function show_add_do() {
	GLOBAL $db, $MYSQL_PREFIX;
	$sql = "INSERT INTO {$MYSQL_PREFIX}shows ( showname, company, venue, dates ) VALUES ( '{$_REQUEST['showname']}', '{$_REQUEST['company']}', '{$_REQUEST['venue']}', '{$_REQUEST['dates']}' )";
	$result = mysql_query($sql, $db);
	thrower("Show {$_REQUEST['showname']} Added");
}

/**
 * View all shows in database
 * 
 * @global resource Database Link
 * @global string User Name
 * @global string MySQL Table Prefix
 * @return string HTML Output
 */
function show_view() {
	GLOBAL $db, $user_name, $MYSQL_PREFIX;
	$sql = "SELECT * FROM {$MYSQL_PREFIX}shows ORDER BY created DESC";
	$result = mysql_query($sql, $db);
	$editlink = perms_isadmin($user_name);
	$html = "";
	while ( $row = mysql_fetch_array($result) ) {
		$html .= "<h3>{$row['showname']}</h3>\n";
		$html .= $editlink ? "<span class=\"overright\">[<a href=\"/edit-show&amp;id={$row['showid']}\">Edit</a>]</span>\n" : "";
		$html .= "<ul class=\"datalist\">\n<li><strong>Company</strong>: {$row['company']}</li>\n";
		$html .= "<li><strong>Venue</strong>: {$row['venue']}</li>\n";
		$html .= "<li><strong>Dates</strong>: {$row['dates']}</li>\n";
		$html .= "<li><strong>Show Record Open</strong>: " . (( $row['closed'] == 1 ) ? "NO" : "YES") . "</li>\n";
		$html .= "</ul>\n";
	}
	return $html;
}

/**
 * Show the show edit form
 * 
 * @global resource Database Link
 * @global string MySQL Table Prefix
 * @global string Site address for links
 * @param integer Show ID
 * @return string HTML Output
 */
function show_edit_form($showid) {
	GLOBAL $db, $MYSQL_PREFIX, $TDTRAC_SITE;
	$sql = "SELECT showname, company, venue, dates, `closed` FROM {$MYSQL_PREFIX}shows WHERE showid = {$showid} LIMIT 1";
	$result = mysql_query($sql, $db);
	$row = mysql_fetch_array($result);
	$html  = "<h3>Edit A Show</h3>\n";
	$form = new tdform("{$TDTRAC_SITE}edit-show", "showedit");
	
	$fesult = $form->addText('showname', 'Show Name', null, $row['showname']);
	$result = $form->addText('company', 'Show Company', null, $row['company']);
	$result = $form->addText('venue', 'Show Venue', null, $row['venue']);
	$result = $form->addText('dates', 'Show Dates', null, $row['dates']);
	$openshow =  ( $row['closed'] ? 0 : 1 );
	$result = $form->addCheck('closed', 'Show Record Open', null, $openshow);
	$result = $form->addHidden('showid', $showid);
	
	$html .= $form->output('Commit');
	return $html;
}

/**
 * Logic to save show edit
 * 
 * @global resource Database Link
 * @global string MySQL Table Prefix
 */
function show_edit_do($showid) {
	GLOBAL $db, $MYSQL_PREFIX;
	$closedcheck = ($_REQUEST['closed'] == 'y') ? 0 : 1;
	$sql = "UPDATE {$MYSQL_PREFIX}shows SET showname='{$_REQUEST['showname']}' , company='{$_REQUEST['company']}' , venue='{$_REQUEST['venue']}' , dates='{$_REQUEST['dates']}', closed='{$closedcheck}' WHERE showid = {$showid}";
	$result = mysql_query($sql, $db);
	thrower("Show {$_REQUEST['showname']} Updated");
}
?>
