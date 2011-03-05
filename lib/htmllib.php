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
 * @global array Link for Right Side of Header
 * @return array Formatted HTML
 */
function makeHeader($title = '') {
	GLOBAL $TDTRAC_VERSION, $TDTRAC_CPNY, $TDTRAC_SITE, $SITE_SCRIPT, $HEAD_LINK, $CANCEL, $action;

	$html = array();
	$html[] = '<!DOCTYPE html>';
	$html[] = '<html lang="en">';
	$html[] = "<head>\n\t<meta http-equiv=\"Content-Type\" content=\"text/html; charset=iso-8859-1\" />";
	$html[] = "\t<title>TDTrac{$TDTRAC_CPNY}:v{$TDTRAC_VERSION} - {$title}</title>";
	$html[] = "\t<!--[if lt IE 9]>";
	$html[] = "\t\t<script src=\"http://html5shim.googlecode.com/svn/trunk/html5.js\"></script>";
	$html[] = "\t<![endif]-->";
	$html[] = '	<link rel="stylesheet" href="http://code.jquery.com/mobile/1.0a3/jquery.mobile-1.0a3.min.css" />';
	$html[] = '	<link type="text/css" href="http://dev.jtsage.com/cdn/datebox/latest/jquery.mobile.datebox.css" rel="stylesheet" /> ';
	$html[] = "\t<script type=\"text/javascript\" src=\"https://ajax.googleapis.com/ajax/libs/jquery/1.5.1/jquery.min.js\"></script>";
	$html[] = "\t<script type=\"text/javascript\">";
	$html[] = "		$(document).bind(\"mobileinit\", function(){ $.mobile.page.prototype.options.degradeInputs.date = 'text'; }); //$.extend(  $.mobile , { ajaxEnabled: false });  });";
	$html[] = "\t</script>";
	$html[] = '	<script type="text/javascript" src="http://code.jquery.com/mobile/1.0a3/jquery.mobile-1.0a3.min.js"></script>';
	$html[] = '	<script type="text/javascript" src="http://dev.jtsage.com/cdn/datebox/latest/jquery.mobile.datebox.js"></script>';
	$html[] = "	<script type=\"text/javascript\" src=\"{$TDTRAC_SITE}js/tdtrac.jquery.js\"></script>";
	$html[] = "\t<script type=\"text/javascript\">";
	foreach ( $SITE_SCRIPT as $line ) {
		$html[] = "\t\t{$line}";
	}
	$html[] = "\n\t</script>\n</head>\n\n<body>";
	
	$html[] = "	<div data-role=\"page\" data-theme=\"a\" id=\"{$action['module']}-{$action['action']}\">";
	$html[] = "		<div data-role=\"header\">";
	$html[] = "			<h1>TDTrac::{$title}</h1>";
	if ( count($HEAD_LINK) == 3 ) {
		$html[] = "			<a href=\"{$HEAD_LINK[0]}\" data-icon=\"{$HEAD_LINK[1]}\" class=\"ui-btn-right\">{$HEAD_LINK[2]}</a>";
	}
	$html[] = "		</div><div id='infobox' data-backbtn='false' data-role='header' data-theme='d'><h2>".((isset($_SESSION['infodata']))?$_SESSION['infodata']:"--")."</h2></div>";
	unset($_SESSION['infodata']);
	
	$html[] = "		<div data-role=\"content\" data-theme=\"c\">";
	
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
	$html[] = "		<div data-role=\"footer\" data-theme=\"a\">";
	$html[] = "			<div data-role=\"navbar\"><ul>";
	$html[] = "				<li><a href=\"/\" data-icon=\"home\">Home</a></li>";
	$html[] = "				<li><a href=\"#help\" data-transition=\"slideup\" data-icon=\"info\">Help</a></li>";
	$html[] = "				<li><a href=\"/user/logout/\" data-transition=\"slidedown\" data-icon=\"alert\">Logout</a></li>";
	$html[] = "			</ul></div>";
	$html[] = "			<h3>&copy; 2008-".date('Y')." JTSage. All rights reserved. <a href=\"http://tdtrac.com/\" title=\"TDTrac Homepage\">TDTrac Homepage</a></h3>";
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
	$html[] = "		<div data-role=\"header\" data-backbtn=\"false\"><a href=\"#main\" data-rel=\"back\" data-transition=\"slidedown\" data-icon=\"delete\" >Close</a><h1>Help::{$hdivTitle}</h1></div>";
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
