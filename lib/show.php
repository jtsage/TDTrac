<?php
/**
 * TDTrac Show Functions
 * 
 * Contains all show related functions. 
 * Data hardened
 * @package tdtrac
 * @version 1.4.0
 * @author J.T.Sage <jtsage@gmail.com>
 */

/**
 * Show the show add form
 * 
 * @global string Site address for links
 * @return string HTML output
 */
function show_add_form() {
	GLOBAL $TDTRAC_SITE;
	$form = new tdform("{$TDTRAC_SITE}add-show", 'genform', 1, 'genform', 'Add A Show');
	
	$result = $form->addText('showname', 'Show Name');
	$result = $form->addText('company', 'Show Company');
	$result = $form->addText('venue', 'Show Venue');
	$result = $form->addDate('dates', 'Show Opening');
	
	$html = $form->output('Add Show');
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
	$sqlstring  = "INSERT INTO `{$MYSQL_PREFIX}shows` ( showname, company, venue, dates )";
	$sqlstring .= " VALUES ( '%s', '%s', '%s', '%s' )";
	
	$sql = sprintf($sqlstring,
		mysql_real_escape_string($_REQUEST['showname']),
		mysql_real_escape_string($_REQUEST['company']),
		mysql_real_escape_string($_REQUEST['venue']),
		mysql_real_escape_string($_REQUEST['dates'])
	);

	$result = mysql_query($sql, $db);
	if ( $request ) {
		thrower("Show {$_REQUEST['showname']} Added");
	} else {
		thrower("Show Add :: Operation Failed");
	}
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
	GLOBAL $db, $user_name, $MYSQL_PREFIX, $TDTRAC_SITE;
	$sql = "SELECT * FROM `{$MYSQL_PREFIX}shows` ORDER BY `created` DESC";
	$result = mysql_query($sql, $db);
	$editlink = perms_isadmin($user_name);
	$html = array();
	while ( $row = mysql_fetch_array($result) ) {
		$html[] = "<h3>{$row['showname']}</h3>";
		$html[] = $editlink ? "<span class=\"overright\">[<a href=\"{$TDTRAC_SITE}shows/edit/{$row['showid']}/\">Edit</a>]</span>" : "";
		$html[] = "  <ul class=\"datalist\">";
		$html[] = "    <li><strong>Company</strong>: {$row['company']}</li>";
		$html[] = "    <li><strong>Venue</strong>: {$row['venue']}</li>";
		$html[] = "    <li><strong>Dates</strong>: {$row['dates']}</li>";
		$html[] = "    <li><strong>Show Record Open</strong>: " . (( $row['closed'] == 1 ) ? "NO" : "YES") . "</li>";
		$html[] = "</ul>";
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

	$sqlstring  = "SELECT `showname`, `company`, `venue`, `dates`, `closed` FROM `{$MYSQL_PREFIX}shows`";
	$sqlstring .= " WHERE `showid` = %d LIMIT 1";

	$sql = sprintf($sqlstring,
		intval($showid)
	);

	$result = mysql_query($sql, $db);
	$row = mysql_fetch_array($result);
	$form = new tdform("{$TDTRAC_SITE}shows/edit/{$showid}/", "showedit", 1, 'genform', 'Edit Show');
	
	$fesult = $form->addText('showname', 'Show Name', null, $row['showname']);
	$result = $form->addText('company', 'Show Company', null, $row['company']);
	$result = $form->addText('venue', 'Show Venue', null, $row['venue']);
	$result = $form->addDate('dates', 'Show Dates', null, $row['dates']);
	$openshow =  ( $row['closed'] ? 0 : 1 );
	$result = $form->addCheck('closed', 'Show Record Open', null, $openshow);
	$result = $form->addHidden('showid', $showid);
	
	$html = $form->output('Commit');
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
	$sqlstring  = "UPDATE `{$MYSQL_PREFIX}shows` SET showname='%s', company='%s', venue='%s', dates='%s',";
    $sqlstring .= " closed=%d WHERE showid = %d";

	$sql = sprintf($sqlstring,
		mysql_real_escape_string($_REQUEST['showname']),
		mysql_real_escape_string($_REQUEST['company']),
		mysql_real_escape_string($_REQUEST['venue']),
		mysql_real_escape_string($_REQUEST['dates']),
		intval($closedcheck),
		intval($showid)
	);

	$result = mysql_query($sql, $db); 
	if ( $result ) {
		thrower("Show {$_REQUEST['showname']} Updated");
	} else {
		thrower("Show Update :: Operation Failed");
	}
}
?>
;
