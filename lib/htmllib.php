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
	$html = array_merge(makeHeader($title), makeNotice());
	foreach( $body as $fixme ) {
		$html[] = "\t\t\t\t{$fixme}";
	}
	$html = array_merge($html, makeFooter());
	ob_clean(); //Hackish method to clear any extra lines / echos before html starts
	foreach ($html as $line) {
		echo $line . "\n";
	}
}

/**
 * Make infonotice box
 * 
 * @return array Formatted HTML
 */
function makeNotice() {
	if ( isset($_SESSION['infodata']) ) { 
		$html[] = "\t\t\t\t<div id=\"popperdiv\" class=\"infobox\"><span id=\"popper\" style=\"font-size: .7em\">{$_SESSION['infodata']}</span></div>";
		unset($_SESSION['infodata']);
		return $html;
	} else {
		return array("\t\t\t\t<div id=\"popperdiv\" style=\"display: none\" class=\"infobox\"><span id=\"popper\" style=\"font-size: .7em\"></span></div>");
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
 * @global array Parsed Query String
 * @global array Help Node Text
 * @return array Formatted HTML
 */
function makeHeader($title = '') {
	GLOBAL $TDTRAC_VERSION, $TDTRAC_CPNY, $TDTRAC_SITE, $user, $SITE_SCRIPT, $action, $helpnode;

	$SITE_SCRIPT[] = "$(function() {";
	$SITE_SCRIPT[] = "	var hWide = 500;";
	$SITE_SCRIPT[] = "	if ( $(document).width() < 768 ) { hWide = 390; }";
	$SITE_SCRIPT[] = "	if ( $(document).width() < 480 ) { hWide = 220; }";
	$SITE_SCRIPT[] = "	$( \"#help\" ).dialog({ autoOpen: false, width: hWide, modal: true });";
	$SITE_SCRIPT[] = "});";
	$SITE_SCRIPT[] = "$(function() {";
	$SITE_SCRIPT[] = "	$( \"#helplink\" ).click(function() {";
	$SITE_SCRIPT[] = "		$( \"#help\" ).dialog('open'); return false;";
	$SITE_SCRIPT[] = "	});";
	$SITE_SCRIPT[] = "});";
	$SITE_SCRIPT[] = "$(document).ready(function(){";
	$SITE_SCRIPT[] = "	$('ul.subnav').parent().find('div.menubut').append('<span></span>');";
	$SITE_SCRIPT[] = "	$('ul.topnav li span').click(function() { ";
	$SITE_SCRIPT[] = "		$(this).parent().parent().find('ul.subnav').slideDown('fast').show();";
	$SITE_SCRIPT[] = "		$(this).parent().parent().hover(function() {";
	$SITE_SCRIPT[] = "		}, function(){";
	$SITE_SCRIPT[] = "			$(this).parent().parent().find('ul.subnav').slideUp('slow'); ";
	$SITE_SCRIPT[] = "		});";
	$SITE_SCRIPT[] = "		}).hover(function() {";
	$SITE_SCRIPT[] = "			$(this).addClass('subhover'); ";
	$SITE_SCRIPT[] = "		}, function(){	";
	$SITE_SCRIPT[] = "			$(this).removeClass('subhover');";
	$SITE_SCRIPT[] = "	});";
	$SITE_SCRIPT[] = "});";

	$html = array();
	$html[] = '<!DOCTYPE html>';
	$html[] = '<html lang="en">';
	$html[] = "<head>\n\t<meta http-equiv=\"Content-Type\" content=\"text/html; charset=iso-8859-1\" />";
	$html[] = "\t<title>TDTrac{$TDTRAC_CPNY}:v{$TDTRAC_VERSION} - {$title}</title>";
	$html[] = "\t<!--[if lt IE 9]>";
	$html[] = "\t\t<script src=\"http://html5shim.googlecode.com/svn/trunk/html5.js\"></script>";
	$html[] = "\t<![endif]-->";
	$html[] = "\t<meta name=\"viewport\" content=\"width=device-width; initial-scale=1\"/>";
	$html[] = "\t<link href=\"/css/tdtrac.css\" rel=\"stylesheet\" type=\"text/css\" />";
	$html[] = "\t<link type=\"text/css\" href=\"/css/custom-theme/jquery-ui-1.8.7.custom.css\" rel=\"stylesheet\" />";
	$html[] = "\t<link type=\"text/css\" href=\"/css/jquery.ui.selectmenu.css\" rel=\"stylesheet\" />";
	$html[] = "\t<script type=\"text/javascript\" src=\"/js/jquery-1.4.4.min.js\"></script>";
	$html[] = "\t<script type=\"text/javascript\" src=\"/js/jquery-ui-1.8.7.custom.min.js\"></script>";
	$html[] = "\t<script type=\"text/javascript\" src=\"/js/jquery.ui.selectmenu.js\"></script>";
	$html[] = "\t<script type=\"text/javascript\" src=\"/js/jquery.masonry.min.js\"></script>";
	$html[] = "\t<script type=\"text/javascript\">";
	foreach ( $SITE_SCRIPT as $line ) {
		$html[] = "\t\t{$line}";
	}
	$html[] = "\n\t</script>\n</head>\n\n<body>";
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
	$html[] = "\t<div id=\"help\" title=\"{$hdivTitle}\">";
	foreach ( $hdivData as $line ) {
		$html[] = "\t\t<p>{$line}</p>";
	}
	$html[] = "\t</div>";
	
	$html[] = "\t<div id=\"outer\">";
	$html[] = "\t\t<div id=\"header\">";
	$html[] = "\t\t\t<div id=\"headercontent\">";
	$html[] = "\t\t\t\t<h1><span class=\"logoclose\">TD<span class=\"red\">T</span></span><span class=\"red\">rac</span><span class=\"logoclose\">{$TDTRAC_CPNY}</span><sup>{$TDTRAC_VERSION}</sup></h1>";
	if ( $user->loggedin ) { 
		$temp = "\t\t\t\t<h3><strong>Logged In User:</strong> {$user->name} (ID::{$user->id}/Group::{$user->group})</h3>"; 
	} else {
		$temp = "\t\t\t\t<h2>Budget and Payroll Tracking</h2>";
	}
	$html[] = "{$temp}\n\t\t\t</div>";
	

	if ( $user->loggedin ) {
		if ( $user->can('viewbudget') ) {
			$html[] = "\t\t\t<form method=\"post\" action=\"{$TDTRAC_SITE}budget/search/\">\n\t\t\t<div id=\"search\">";
			$html[] = "\t\t\t\t<input tabindex=\"81\" type=\"text\" class=\"text\" maxlength=\"64\" name=\"keywords\" />";
			$html[] = "\t\t\t\t<input tabindex=\"82\" type=\"submit\" class=\"submit\" value=\"Search\" />\n\t\t\t</div>\t\t\t</form>";
		}
		
	}
	$html[] = "\n\t\t</div>";
	$menu[] = array(true, 'Dashboard', '', 'Main Dashboard');
	$menu[] = array($user->loggedin, 'Password', 'user/password/', 'Change Your Password');
	$menu[] = array(true, 'Budget', 'budget/', 'Manage Show Budgets', array(
		array(($user->loggedin && $user->can('addbudget')), 'Add Expense', 'budget/add/', 'Add An Expense'),
		array(($user->loggedin && $user->can('viewbudget')), 'View Expenses', 'budget/view/', 'View Show Budgets')
	));
	$menu[] = array(true, 'Payroll', 'hours/', 'Manage Payroll', array(
		array(($user->loggedin && $user->onpayroll && $user->can('addhours')), 'Add Own Hours', 'hours/add/own:1/', 'Add Hours to Yourself'),
		array(($user->loggedin && !$user->isemp && $user->can('addhours')), 'Add Hours', 'hours/add/', 'Add Payroll Hours'),
		array(($user->loggedin && $user->can('viewhours')), 'View Hours', 'hours/view/', 'View Payroll History'),
		array(($user->loggedin && $user->admin), 'View Unpaid Hours', 'hours/view/type:unpaid/', 'View Pending Payroll')
	));
	$menu[] = array(true, 'Shows', 'shows/', 'Manage Shows', array(
		array(($user->loggedin && $user->can('addshow')), 'Add Show', 'shows/add/', 'Add a Show'),
		array(($user->loggedin && $user->can('viewshow')), 'View Shows', 'shows/view/', 'View tracked Shows')
	));
	$menu[] = array(true, 'To-Do', 'todo/', 'Manage Todo Lists', array(
		array(($user->loggedin), 'View Your List', "todo/view/id:{$user->id}/type:user/", 'View Your Todo List'),
		array(($user->loggedin && $user->can('viewtodo')), 'View Overdue', 'todo/view/id:1/type:overdue', 'View Overdue Todo Items'),
		array(($user->loggedin && $user->can('viewtodo')), 'View Todo Items', 'todo/view/', 'View Todo Lists'),
		array(($user->loggedin && $user->can('addtodo')), 'Add Todo Item', 'todo/add', 'Add Todo List Item')
	));
	$menu[] = array($user->admin, 'Admin', 'admin/', 'Administration', array(
		array(true, 'Add User', 'admin/useradd/', 'Add A User'),
		array(true, 'View Users', 'admin/users/', 'View All Users'),
		array(true, 'View Permissions', 'admin/perms/', 'Manage Permissions')
	));
	$menu[] = array($user->loggedin, 'Logout', 'user/logout/', 'Log out of system');

	$html[] = "\t\t<div id=\"headerpic\"></div>\n\t\t<div id=\"menu\">\n\t\t\t<ul class=\"topnav\">";
	foreach ( $menu as $key => $item ) {
		if ( $item[0] ) {
			$mitem = array();
			$mitem[] = "<li><div class=\"menubut";
			if ( preg_match("/\//", $item[2]) ) {
				$tester = preg_split("/\//", $item[2]);
				if ( ( $action['action'] == $tester[1] || $action['module'] == $tester[0] ) && $key <> 7 ) {
					$mitem[] = " active";
				} 
			} else { 
				if ( $key == 0 && $action['module'] == 'index' ) {
					$mitem[] = " active";
				}
			}
					
			$mitem[] = "\"><a tabindex=\"".($key+90)."\" href=\"{$TDTRAC_SITE}{$item[2]}\" title=\"{$item[3]}\">{$item[1]}</a></div>";
			$subs = "";
			if ( count($item) > 4 ) {
				foreach( $item[4] as $subitem ) {
					if ( $subitem[0] ) {
						$subs .= "<li><a href=\"{$TDTRAC_SITE}{$subitem[2]}\" title=\"{$subitem[3]}\">{$subitem[1]}</a></li>";
					}
				}
				if ( !empty($subs) ) {
					$mitem[] = "<ul class=\"subnav\">{$subs}</ul>";
				}
			}
			$html[] = "\t\t\t\t".join($mitem)."</li>";
		}
	}
	
	$html[] = "\t\t\t\t<li><div class=\"menubut\"><a tabindex=\"100\" href=\"\" id=\"helplink\" title=\"Help Popup\" >?</a></div></li>";
	$html[] = "\t\t\t</ul>\n\t\t</div>\n\t\t<div id=\"menubottom\"></div>\n\n\t\t<div id=\"content\">\n\t\t\t<div id=\"normalcontent\">";

	return $html;
}

/**
 * Make page footer
 * 
 * @global array Dashboard block as appropriate
 * @return array Formatted HTML
 */
function makeFooter() {
	global $SITE_BLOCK;
	$html[] = "\t\t\t</div>\n\t\t</div>";
	$html[] = "\t\t<div id=\"footer\">";
	$html[] = "\t\t\t<div class=\"left\">&copy; 2008-".date('Y')." JTSage. All rights reserved.</div>";
	$html[] = "\t\t\t<div class=\"right\"><a href=\"http://tdtrac.com/\" title=\"TDTrac Homepage\">TDTrac Homepage</a></div>";
	$html[] = "\t\t</div>\n\t</div>";
	if ( count($SITE_BLOCK) > 0 ) { 
		$html[] = "\t<div id=\"dashfloat\">". join($SITE_BLOCK) . "</div>"; 
	}
	$html[] = "\n</body>\n</html>";
	return $html;
}
?>
