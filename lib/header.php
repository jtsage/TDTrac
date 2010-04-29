<?php
/**
 * TDTrac Header
 * 
 * Contains site header.
 * @package tdtrac
 * @version 1.3.0
 */
GLOBAL $TDTRAC_VERSION, $TDTRAC_CPNY, $TDTRAC_SITE, $TDTRAC_HELP, $page_title, $login;
$html = <<<ENN
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
<title>TDTrac{$TDTRAC_CPNY}:v{$TDTRAC_VERSION} - {$page_title}</title>
<link href="td130.css" rel="stylesheet" type="text/css" />
<script type="text/javascript" src="TDTracCalendar.js"></script>
</head>

<body>

<div id="upbg"></div>

<div id="outer">

ENN;

$html .= "	<div id=\"header\">\n";
$html .= "		<div id=\"headercontent\">\n";
$html .= "			<h1>TDTrac{$TDTRAC_CPNY}<sup>{$TDTRAC_VERSION}</sup></h1>\n";
if ( $login[0] ) { 
	$html .= "			<h2><strong>Logged In User:</strong> {$login[1]} (ID: ".perms_getidbyname($login[1]).") <strong>Group:</strong> "; 
	$groups = perms_getgroups($login[1]);
	foreach ( $groups as $group ) { $html .= "{$group} "; }
} else {
	$html .= "			<h2>Budget and Payroll Tracking";
}

$html .= "</h2>\n		</div>\n	</div>\n";

if ( $login[0] ) {
	if ( perms_checkperm($login[1], 'viewbudget') ) {
		$html .= "		<form method=\"post\" action=\"{$TDTRAC_SITE}search\">\n		<div id=\"search\">\n";
		$html .= "			<select tabindex=\"80\" name=\"stype\" id=\"stype\"><option value=\"dscr\">Description &asymp;</option><option value=\"cat\">Category &asymp;</option><option value=\"vendor\">Vendor &asymp;</option><option value=\"date\">Date =</option></select>\n";
		$html .= "			<input tabindex=\"81\" type=\"text\" class=\"text\" maxlength=\"64\" name=\"keywords\" />\n 			<input tabindex=\"82\" type=\"submit\" class=\"submit\" value=\"Search\" />\n		</div>\n	</form>\n";
	}
}

$html .= "";
$html .= "<div id=\"headerpic\"></div>\n	<div id=\"menu\">\n		<ul>\n";
$html .= "			<li><a tabindex=\"90\" href=\"{$TDTRAC_SITE}home\"".(($page_title == "home")?" class=\"active\"":"").">Home</a></li>\n";
$html .= "			<li><a tabindex=\"91\" href=\"{$TDTRAC_SITE}change-pass\"".(($page_title == "change-pass")?" class=\"active\"":"").">Change Password</a></li>\n";
$html .= "			<li><a tabindex=\"92\" href=\"{$TDTRAC_SITE}main-budget\"".((preg_match("/budget/", $page_title))?" class=\"active\"":"").">Budget</a></li>\n";
$html .= "			<li><a tabindex=\"93\" href=\"{$TDTRAC_SITE}main-hours\"".((preg_match("/hours/", $page_title))?" class=\"active\"":"").">Payroll</a></li>\n";
$html .= "			<li><a tabindex=\"94\" href=\"{$TDTRAC_SITE}main-show\"".((preg_match("/show/", $page_title))?" class=\"active\"":"").">Shows</a></li>\n";
$html .= "			<li><a tabindex=\"95\" href=\"{$TDTRAC_SITE}main-perms\"".((preg_match("/perms/", $page_title) || preg_match("/user/", $page_title))?" class=\"active\"":"").">Admin</a></li>\n";
$html .= "			<li><a tabindex=\"96\" href=\"{$TDTRAC_SITE}logout\">Logout</a></li>\n";
$html .= "			<li><a tabindex=\"97\" href=\"#\" onclick=\"javascript:window.open('{$TDTRAC_HELP}{$page_title}')\" >Help</a></li>\n";
$html .= "		</ul>\n	</div>\n	<div id=\"menubottom\"></div>\n\n	<div id=\"content\">\n		<div id=\"normalcontent\">\n";

echo $html;
?>

