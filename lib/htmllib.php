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

/**
 * Master makePage Function
 * 
 * @param array Body Contents
 * @param string Page Title
 * @return void
 */
function makePage($body = '', $title = '', $sidebar = '') {
    GLOBAL $user, $TEST_MODE;
    if (!is_array($body) ) {
        $body = preg_split("/\n/", $body);
    }
    $html = makeHeader($title);
    if ( !empty($sidebar) ) {
        $html[] = "\t\t    <div class='content-secondary'>\n";
        $html[] = "\t\t\t<div class='tdtractitle'>TD<span class='red'>Trac</span></div>\n";
        foreach ( $sidebar as $fixme ) {
            $html[] = "\t\t\t{$fixme}";
        }
        $html[] = "\t\t    </div><div class='content-primary'>\n";
    }
    foreach( $body as $fixme ) {
        $html[] = "\t\t\t{$fixme}";
    }
    if ( !empty($sidebar) ) {
        $html[] = "\t\t    </div>\n";
    }
    $html = array_merge($html, makeFooter($title, $user->loggedin));
    if ( !$TEST_MODE ) { ob_clean(); } //Hackish method to clear any extra lines / echos before html starts
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
 * @global array Link for Right Side of Header
 * @global bool Make back link say CANCEL
 * @global bool Make back link say CLOSE
 * @return array Formatted HTML
 */
function makeHeader($title = '') {
    GLOBAL $TDTRAC_VERSION, $TDTRAC_CPNY, $TDTRAC_SITE, $HEAD_LINK, $CANCEL, $CLOSE, $TEST_MODE, $action;

	$min = ( $TEST_MODE ) ? "" : ".min";
	
    $html = array();
    $html[] = '<!DOCTYPE html>';
    $html[] = '<html lang="en">';
    $html[] = '<head>';
    $html[] = '	<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />';
    $html[] = '	<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">';
    $html[] = '	<meta name="apple-mobile-web-app-capable" content="yes">';
    $html[] = "	<title>TDTrac{$TDTRAC_CPNY}:{$TDTRAC_VERSION} - {$title}</title>";
//    $html[] = '	<!--[if lt IE 9]>';
  //  $html[] = '		<script src="http://html5shim.googlecode.com/svn/trunk/html5.js"></script>';
   // $html[] = '	<![endif]-->';
    $html[] = '	<link type="text/css" href="http://code.jquery.com/mobile/latest/jquery.mobile.structure'.$min.'.css" rel="stylesheet" />';
    $html[] = '	<link type="text/css" href="http://dev.jtsage.com/cdn/datebox/latest/jqm-datebox'.$min.'.css" rel="stylesheet" /> ';
    $html[] = '	<link type="text/css" href="http://dev.jtsage.com/cdn/simpledialog/latest/jquery.mobile.simpledialog'.$min.'.css" rel="stylesheet" /> ';
    $html[] = '	<link type="text/css" href="'.$TDTRAC_SITE.'css/tdtheme.css" rel="stylesheet" /> ';
    $html[] = '	<link type="text/css" href="'.$TDTRAC_SITE.'css/tdtheme.mobile.css" rel="stylesheet" /> ';
    $html[] = '	<script type="text/javascript" src="http://code.jquery.com/jquery-1.7.1'.$min.'.js"></script>';
    $html[] = '	<script type="text/javascript" src="http://code.jquery.com/mobile/latest/jquery.mobile'.$min.'.js"></script>';
	$html[] = '	<script type="text/javascript" src="http://dev.jtsage.com/cdn/datebox/latest/jqm-datebox.core'.$min.'.js"></script>';
	$html[] = '	<script type="text/javascript" src="http://dev.jtsage.com/cdn/datebox/latest/jqm-datebox.mode.calbox'.$min.'.js"></script>';
	$html[] = '	<script type="text/javascript" src="http://dev.jtsage.com/cdn/datebox/i18n/jquery.mobile.datebox.i18n.en_US.utf8.js"></script>';
    //$html[] = '	<script type="text/javascript" src="http://dev.jtsage.com/cdn/simpledialog/latest/jquery.mobile.simpledialog'.$min.'.js"></script>';
    $html[] = '	<script type="text/javascript" src="http://dev.jtsage.com/cdn/popupwrap/latest/jqm-popupwrap'.$min.'.js"></script>';
    $html[] = '	<script type="text/javascript" src="'.$TDTRAC_SITE.'js/tdtrac.jquery.js"></script>';
	$html[] = '	<script type="text/javascript">jQuery.extend(jQuery.mobile.datebox.prototype.options, { "overrideDateFormat":"%Y-%m-%d" });</script>';
    $html[] = "</head>\n\n<body>";
    $stamp = time();
    $pageid = ( $action['module'] == 'help' ) ? "help-{$action['action']}-{$action['oper']}" : "{$action['module']}-{$action['action']}";
    $html[] = " <div id='tdtracconfig' data-base='{$TDTRAC_SITE}' data-testmode='{$TEST_MODE}'></div>";
    $html[] = "	<div data-role=\"page\" data-theme=\"c\" data-id=\"{$pageid}-{$stamp}\">";
    
    $html[] = "		<div data-role=\"header\">";
    if ( $CANCEL ) { $html[] = "			<a href='#' data-icon='delete' data-rel='back'>Cancel</a>";	}
    if ( $CLOSE )  { $html[] = "			<a href='#' data-icon='arrow-d' data-rel='back'>Close</a>";	}
    $html[] = "			<h1>TDTrac::{$title}</h1>";
    if ( count($HEAD_LINK) == 3 || count($HEAD_LINK) == 4 ) {
        $html[] = "			<a href=\"{$TDTRAC_SITE}{$HEAD_LINK[0]}\" data-icon=\"{$HEAD_LINK[1]}\" class=\"ui-btn-right\"".((isset($HEAD_LINK[3]))?" id=\"{$HEAD_LINK[3]}\"":"").">{$HEAD_LINK[2]}</a>";
    }
    $html[] = "		</div>";
    if ( $_SEVER['REQUEST_METHOD'] = "POST" && isset($_REQUEST['infobox']) ) {
        $html[] = "		<script type='text/javascript'>setTimeout(\"infobox('{$_REQUEST['infobox']}');\", 1000);</script>";
    }
    unset($_SESSION['infodata']);
    
    $html[] = "		<div data-role=\"content\">";
    if ( $TEST_MODE ) { $html[] = " <!-- SESSION:\n ".var_export($_SESSION, true).'-->'; }
    if ( $TEST_MODE ) { $html[] = " <!-- REQUEST:\n ".var_export($_REQUEST, true).'-->'; }
    
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
function makeFooter($title = '', $loggedin) {
    global $action, $EXTRA_NAV, $TDTRAC_SITE;
    $html[] = "		</div>";
    $html[] = "		<div data-role=\"footer\" data-theme=\"a\">";
    if ( $loggedin ) {
        $html[] = "			<div data-role=\"navbar\"><ul>";
        $html[] = "				<li><a href=\"{$TDTRAC_SITE}\" data-direction='reverse' data-icon=\"home\">Home</a></li>";
        if ( $EXTRA_NAV ) {
            $html[] = "				<li><a href=\"{$TDTRAC_SITE}{$action['module']}\" data-direction='reverse' data-icon=\"home\">".ucwords($action['module'])." Home</a></li>";
        } elseif ( $action['module'] == 'index' && $action['action'] == 'index' ) {
            $html[] = "				<li><a href=\"{$TDTRAC_SITE}user/password/\" data-icon=\"grid\">Change Password</a></li>";
        }
        $html[] = "				<li><a class='help-link' href=\"#\" data-base=\"{$action['module']}\" data-sub=\"{$action['action']}\" data-icon=\"info\">Help</a></li>";
        $html[] = "				<li><a href=\"{$TDTRAC_SITE}user/logout/\" rel='external' data-transition=\"slidedown\" data-icon=\"alert\">Logout</a></li>";
        $html[] = "			</ul></div>";
    }
    $html[] = "			<h3>&copy; 2008-".date('Y')." J.T.Sage</h3>"; // All rights reserved. <a href=\"http://tdtrac.com/\" title=\"TDTrac Homepage\">TDTrac Homepage</a></h3>";
    $html[] = "		</div>\n\t</div>";
    $html[] = "\n</body>\n</html>";
    return $html;
}
?>
