<?php
/**
 * TDTrac Header
 * 
 * Contains site header.
 * @package tdtrac
 * @version 1.3.1
 * @author J.T.Sage <jtsage@gmail.com>
 */
require_once("helpnodes.php");

function makePage($body = '', $title = '') {
	if (!is_array($body) ) {
		$body = preg_split("/\n/", $body);
	}
	$html = array_merge(makeHeader($title), makeNotice());
	foreach( $body as $fixme ) {
		$html[] = "\t\t\t\t{$fixme}";
	}
	$html = array_merge($html, makeFooter());
	//ob_clean(); //Hackish method to clear any extra lines / echos before html starts
	foreach ($html as $line) {
		echo $line . "\n";
	}
}

function makeNotice() {
	if ( isset($_SESSION['infodata']) ) { 
		$html[] = "\t\t\t\t<div class=\"infobox\"><span style=\"font-size: .7em\">{$_SESSION['infodata']}</span></div>";
		unset($_SESSION['infodata']);
		return $html;
	} else {
		return array("\t\t\t\t<!--No InfoNotices-->");
	}
}

function makeHeader($title = '') {
	GLOBAL $TDTRAC_VERSION, $TDTRAC_CPNY, $TDTRAC_SITE, $TDTRAC_HELP, $login, $SITE_SCRIPT, $action, $helpnode;

	$SITE_SCRIPT[] = "$(function() {";
	$SITE_SCRIPT[] = "\t$( \"#help\" ).dialog({ autoOpen: false, width: 500, modal: true });";
	$SITE_SCRIPT[] = "});";
	$SITE_SCRIPT[] = "$(document).ready(function() {";
	$SITE_SCRIPT[] = "\t$( \"#helplink\" ).click(function() {";
	$SITE_SCRIPT[] = "\t\t$( \"#help\" ).dialog('open'); return false;";
	$SITE_SCRIPT[] = "\t});";
	$SITE_SCRIPT[] = "});";

	$html = array();
	$html[] = '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">';
	$html[] = '<html xmlns="http://www.w3.org/1999/xhtml">';
	$html[] = "<head>\n\t<meta http-equiv=\"Content-Type\" content=\"text/html; charset=iso-8859-1\" />";
	$html[] = "\t<title>TDTrac{$TDTRAC_CPNY}:v{$TDTRAC_VERSION} - {$title}</title>";
	$html[] = "\t<link href=\"/css/tdtrac.css\" rel=\"stylesheet\" type=\"text/css\" />";
	$html[] = "\t<link type=\"text/css\" href=\"/css/custom-theme/jquery-ui-1.8.7.custom.css\" rel=\"stylesheet\" />";
	$html[] = "\t<link type=\"text/css\" href=\"/css/jquery.ui.selectmenu.css\" rel=\"stylesheet\" />";
	$html[] = "\t<script type=\"text/javascript\" src=\"/js/jquery-1.4.4.min.js\"></script>";
	$html[] = "\t<script type=\"text/javascript\" src=\"/js/jquery-ui-1.8.7.custom.min.js\"></script>";
	$html[] = "\t<script type=\"text/javascript\" src=\"/js/jquery.ui.selectmenu.js\"></script>";
	$html[] = "\t<script type=\"text/javascript\">";
	foreach ( $SITE_SCRIPT as $line ) {
		$html[] = "\t\t{$line}";
	}
	$html[] = "\t</script>\n</head>\n\n<body>";
	if ( $action[0] == 'index' ) {
		$hdivTitle = $helpnode['index']['title'];
		$hdivData = $helpnode['index']['data'];
	} else {
		if ( !isset($helpnode[$action[0]][$action[1]])) {
			$hdivTitle = $helpnode['error']['title'];
			$hdivData = $helpnode['error']['data'];
		} else {
			$hdivTitle = $helpnode[$action[0]][$action[1]]['title'];
			$hdivData = $helpnode[$action[0]][$action[1]]['data'];
		}
	}
	$html[] = "\t<div id=\"help\" title=\"{$hdivTitle}\">";
	foreach ( $hdivData as $line ) {
		$html[] = "\t\t<p>{$line}</p>";
	}
	$html[] = "\t</div>";
	
	$html[] = "\t<div id=\"upbg\"></div>";
	$html[] = "\t<div id=\"outer\">";
	$html[] = "\t\t<div id=\"header\">";
	$html[] = "\t\t\t<div id=\"headercontent\">";
	$html[] = "\t\t\t\t<h1>TDTrac{$TDTRAC_CPNY}<sup>{$TDTRAC_VERSION}</sup></h1>";
	if ( $login[0] ) { 
		$temp = "\t\t\t\t<h2><strong>Logged In User:</strong> {$login[1]} (ID: ".perms_getidbyname($login[1]).") <strong>Group:</strong> "; 
		$groups = perms_getgroups($login[1]);
		foreach ( $groups as $group ) { $temp .= "{$group} "; }
	} else {
		$temp = "\t\t\t\t<h2>Budget and Payroll Tracking";
	}
	$html[] = "{$temp}</h2>\n\t\t\t</div>\n\t\t</div>";

	if ( $login[0] ) {
		if ( perms_checkperm($login[1], 'viewbudget') ) {
			$html[] = "\t\t\t<form method=\"post\" action=\"{$TDTRAC_SITE}search\">\n\t\t\t<div id=\"search\">";
			$html[] = "\t\t\t\t<input tabindex=\"81\" type=\"text\" class=\"text\" maxlength=\"64\" name=\"keywords\" />";
			$html[] = "\t\t\t\t<input tabindex=\"82\" type=\"submit\" class=\"submit\" value=\"Search\" />\n\t\t\t</div>\t\t\t</form>";
		}
	}

	$html[] = "";
	$html[] = "\t\t<div id=\"headerpic\"></div>\n\t\t<div id=\"menu\">\n\t\t\t<ul>";
	$html[] = "\t\t\t\t<li><a tabindex=\"90\" href=\"{$TDTRAC_SITE}\"".(($title == "")?" class=\"active\"":"").">Home</a></li>";
	$html[] = "\t\t\t\t<li><a tabindex=\"91\" href=\"{$TDTRAC_SITE}user/password/\""	.((preg_match("/password/", $title))	?" class=\"active\"":"").">Change Password</a></li>";
	$html[] = "\t\t\t\t<li><a tabindex=\"92\" href=\"{$TDTRAC_SITE}budget/\""		.((preg_match("/budget/", $title))	?" class=\"active\"":"").">Budget</a></li>";
	$html[] = "\t\t\t\t<li><a tabindex=\"93\" href=\"{$TDTRAC_SITE}hours/\""		.((preg_match("/hours/", $title))	?" class=\"active\"":"").">Payroll</a></li>";
	$html[] = "\t\t\t\t<li><a tabindex=\"94\" href=\"{$TDTRAC_SITE}shows/\""		.((preg_match("/shows/", $title))	?" class=\"active\"":"").">Shows</a></li>";
	$html[] = "\t\t\t\t<li><a tabindex=\"95\" href=\"{$TDTRAC_SITE}todo/\""		.((preg_match("/todo/", $title))	?" class=\"active\"":"").">ToDo</a></li>";
	$html[] = "\t\t\t\t<li><a tabindex=\"96\" href=\"{$TDTRAC_SITE}user/perms/\""	.((preg_match("/perms/", $title))	?" class=\"active\"":"").">Admin</a></li>";
	$html[] = "\t\t\t\t<li><a tabindex=\"97\" href=\"{$TDTRAC_SITE}user/logout\">Logout</a></li>";
	$html[] = "\t\t\t\t<li><a tabindex=\"98\" href=\"\" id=\"helplink\" >Help</a></li>";
	$html[] = "\t\t\t</ul>\n\t\t</div>\n\t\t<div id=\"menubottom\"></div>\n\n\t\t<div id=\"content\">\n\t\t\t<div id=\"normalcontent\">";

	return $html;
}

function makeFooter() {
	$html[] = "\t\t\t</div>\n\t\t</div>";
	$html[] = "\t\t<div id=\"footer\">";
	$html[] = "\t\t\t<div class=\"left\">&copy; 2008-".date('Y')." JTSage. All rights reserved.</div>";
	$html[] = "\t\t\t<div class=\"right\"><a href=\"http://tdtrac.com/\" title=\"TDTrac Homepage\">TDTrac Homepage</a></div>";
	$html[] = "\t\t</div>\n\t</div>\n</body>\n</html>";
	return $html;
}
?>
