<?php
/**
 * TDTrac Header
 * 
 * Contains site header.
 * @package tdtrac
 * @version 2.0.0
 * @since 1.4.0
 * @author J.T.Sage <jtsage@gmail.com>
 */
/** Library: Help Text */
require_once("helpnodes.php");

/**
 * Master makePage Function
 * 
 * @param array Body Contents
 * @param string Page Title
 * @return void
 */
function makePage($body = '', $title = '') {
	if (!is_array($body) ) {
		$body = preg_split("/\n/", $body);
	}
	$html = makeHeader($title);
	foreach( $body as $fixme ) {
		$html[] = "\t\t\t{$fixme}";
	}
	$html = array_merge($html, makeFooter($title));
	ob_clean(); //Hackish method to clear any extra lines / echos before html starts
	foreach ($html as $line) {
		echo $line . "\n";
	}
}

/** 
 * Make page header
 * 
 * @param string Page Title
 * @global string Program Version
 * @global string Company Name
 * @global string Base HREF
 * @global object User object
 * @global array JavaScript
 * @return array Formatted HTML
 */
function makeHeader($title = '') {
	GLOBAL $TDTRAC_VERSION, $TDTRAC_CPNY, $TDTRAC_SITE, $SITE_SCRIPT;

	$html = array();
	$html[] = '<!DOCTYPE html>';
	$html[] = '<html lang="en">';
	$html[] = "<head>\n\t<meta http-equiv=\"Content-Type\" content=\"text/html; charset=iso-8859-1\" />";
	$html[] = "\t<title>TDTrac{$TDTRAC_CPNY}:v{$TDTRAC_VERSION} - {$title}</title>";
	$html[] = "\t<!--[if lt IE 9]>";
	$html[] = "\t\t<script src=\"http://html5shim.googlecode.com/svn/trunk/html5.js\"></script>";
	$html[] = "\t<![endif]-->";
	$html[] = "\t<link type=\"text/css\" href=\"/css/jquery.mobile-1.0a3.min.css\" rel=\"stylesheet\" />";
	$html[] = "\t<script type=\"text/javascript\" src=\"/js/jquery-1.4.4.min.js\"></script>";
	$html[] = "\t<script type=\"text/javascript\" src=\"/js/jquery.mobile-1.0a3.min.js\"></script>";
	$html[] = "\t<script type=\"text/javascript\">";
	foreach ( $SITE_SCRIPT as $line ) {
		$html[] = "\t\t{$line}";
	}
	$html[] = "\n\t</script>\n</head>\n\n<body>";
	
	$html[] = "	<div data-role=\"page\" data-theme=\"b\" id=\"main\">";
	$html[] = "		<div data-role=\"header\">";
	$html[] = "			<h1>TDTrac::{$title}</h1>";
	$html[] = "			<a href=\"/\" data-icon=\"home\" data-direction=\"reverse\" data-iconpos=\"notext\" class=\"ui-btn-right\">Home</a>";
	$html[] = "		</div>";
	$html[] = "		<div data-role=\"content\">";
	
	return $html;
}

/**
 * Make page footer
 * 
 * @param string Page Title
 * @global array Dashboard block as appropriate
 * @global array Parsed Query String
 * @global array Help Text
 * @return array Formatted HTML
 */
function makeFooter($title = '') {
	global $SITE_BLOCK, $action, $helpnode;
	$html[] = "		</div>";
	$html[] = "		<div data-role=\"footer\" data-theme=\"c\">";
	$html[] = "			&copy; 2008-".date('Y')." JTSage. All rights reserved. <a data-icon=\"home\" href=\"http://tdtrac.com/\" title=\"TDTrac Homepage\">TDTrac Homepage</a>";
	$html[] = "			<a href=\"#help\" data-transition=\"slideup\" data-icon=\"info\">Help</a>";
	$html[] = "		</div>\n\t</div>";
	
	/* HELP SECTION */
	if ( $action['module'] == 'index' ) {
		$hdivTitle = $helpnode['index']['title'];
		$hdivData = $helpnode['index']['data'];
	} else {
		if ( !isset($helpnode[$action['module']][$action['action']])) {
			$hdivTitle = $helpnode['error']['title'];
			$hdivData = $helpnode['error']['data'];
		} else {
			$hdivTitle = $helpnode[$action['module']][$action['action']]['title'];
			$hdivData = $helpnode[$action['module']][$action['action']]['data'];
		}
	}
	$html[] = "	<div data-role=\"page\" id=\"help\" title=\"{$hdivTitle}\">";
	$html[] = "		<div data-role=\"header\"><h1>Help::{$hdivTitle}</h1></div>";
	$html[] = "		<div data-role=\"content\">";
	foreach ( $hdivData as $line ) {
		$html[] = "			<p>{$line}</p>";
	}
	$html[] = "		</div>\n	</div>";
	/* END HELP SECTION */
	
	$html[] = "\n</body>\n</html>";
	return $html;
}
?>
