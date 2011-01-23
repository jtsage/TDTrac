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
	$SITE_SCRIPT[] = "\t$( \"#help\" ).dialog({ autoOpen: false, width: 500, modal: true });";
	$SITE_SCRIPT[] = "});";
	$SITE_SCRIPT[] = "$(function() {";
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
	
	$html[] = "\t<div id=\"upbg\"></div>";
	$html[] = "\t<div id=\"outer\">";
	$html[] = "\t\t<div id=\"header\">";
	$html[] = "\t\t\t<div id=\"headercontent\">";
	$html[] = "\t\t\t\t<h1><span style=\"letter-spacing: -5px\">TD<span style=\"color: #C3593C;\">T</span></span><span style=\"color: #C3593C;\">rac</span>{$TDTRAC_CPNY}<sup>{$TDTRAC_VERSION}</sup></h1>";
	if ( $user->loggedin ) { 
		$temp = "\t\t\t\t<h2 style=\"margin-left: 1.5em\"><strong>Logged In User:</strong> {$user->name} (ID: {$user->id}) <strong>Group: {$user->group}</strong> "; 
	} else {
		$temp = "\t\t\t\t<h2 style=\"margin-left: 1.5em\">Budget and Payroll Tracking";
	}
	$html[] = "{$temp}</h2>\n\t\t\t</div>\n\t\t</div>";

	if ( $user->loggedin ) {
		if ( $user->can('viewbudget') ) {
			$html[] = "\t\t\t<form method=\"post\" action=\"{$TDTRAC_SITE}budget/search/\">\n\t\t\t<div id=\"search\">";
			$html[] = "\t\t\t\t<input tabindex=\"81\" type=\"text\" class=\"text\" maxlength=\"64\" name=\"keywords\" />";
			$html[] = "\t\t\t\t<input tabindex=\"82\" type=\"submit\" class=\"submit\" value=\"Search\" />\n\t\t\t</div>\t\t\t</form>";
		}
		
	}

	$menu[] = array(true, 'Dashboard', '', 'Main Dashboard');
	$menu[] = array($user->loggedin, 'Change Password', 'user/password/', 'Change Your Password');
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
		array(($user->loggedin && $user->can('addshows')), 'Add Show', 'shows/add/', 'Add a Show'),
		array(($user->loggedin && $user->can('viewshows')), 'View Shows', 'shows/view/', 'View tracked Shows')
	));
	$menu[] = array($user->admin, 'Admin', 'admin/', 'Administration', array(
		array(true, 'Add User', 'admin/useradd/', 'Add A User'),
		array(true, 'View Users', 'admin/users/', 'View All Users'),
		array(true, 'View Permissions', 'admin/perms/', 'Manage Permissions')
	));
	$menu[] = array($user->loggedin, 'Logout', 'user/logout/', 'Log out of system');
	
	$html[] = "<!--";
	foreach ( $menu as $key => $item ) {
		if ( $item[0] ) {
			$mitem = array();
			$mitem[] = "<li><a tabindex=\"".($key+90)."\" href=\"{$TDTRAC_SITE}{$item[2]}\" title=\"{$item[3]}\" ";
			if ( preg_match("/\//", $item[2]) ) {
				$tester = preg_split("/\//", $item[2]);
				if ( $action['action'] == $tester[1] ) {
					$mitem[] = "class=\"active\" ";
				}
			} else {
				if ( $action['module'] == $item[2] || ( $item[2] == "" && $action['module'] == 'index' && $key == 0)) {
					$mitem[] = "class=\"active\" ";
				}
			}
			$mitem[] = ">{$item[1]}</a>";
			$subs = "";
			if ( count($item) > 4 ) {
				foreach( $item[4] as $subitem ) {
					if ( $subitem[0] ) {
						$subs .= "<li><a href=\"{$TDTRAC_SITE}{$subitem[2]}\" title=\"{$subitem[3]}\">{$subitem[1]}</a></li>";
					}
				}
				if ( !empty($subs) ) {
					$mitem[] = "<ul class=\"submenu\">{$subs}</ul>";
				}
			}
			$html[] = "\t\t\t\t".join($mitem)."</li>";
		}
	}
	
	
	$html[] = "-->";
	

	$html[] = "\t\t<div id=\"headerpic\"></div>\n\t\t<div id=\"menu\">\n\t\t\t<ul class=\"topnav\">";
	$html[] = "\t\t\t\t<li><a tabindex=\"90\" href=\"{$TDTRAC_SITE}\"".(($action['module'] == "index")?" class=\"active\"":"")." title=\"Main Index\">Home</a></li>";
	$html[] = ($user->loggedin)?"\t\t\t\t<li><a tabindex=\"91\" href=\"{$TDTRAC_SITE}user/password/\""	.(($action['action'] == "password")	?" class=\"active\"":"")." title=\"Change Your Password\">Change Password</a></li>":"";
	$html[] = "\t\t\t\t<li><a tabindex=\"92\" href=\"{$TDTRAC_SITE}budget/\""		.(($action['module'] == "budget")	?" class=\"active\"":"")." title=\"Budget Tracking\">Budget</a></li>";
	$html[] = "\t\t\t\t<li><a tabindex=\"93\" href=\"{$TDTRAC_SITE}hours/\""		.(($action['module'] == "hours")	?" class=\"active\"":"")." title=\"Payroll Tracking\">Payroll</a></li>";
	$html[] = "\t\t\t\t<li><a tabindex=\"94\" href=\"{$TDTRAC_SITE}shows/\""		.(($action['module'] == "shows")	?" class=\"active\"":"")." title=\"Show Managment\">Shows</a></li>";
	$html[] = "\t\t\t\t<li><a tabindex=\"95\" href=\"{$TDTRAC_SITE}todo/\""			.(($action['module'] == "todo")	?" class=\"active\"":"")." title=\"To-Do Lists\">ToDo</a></li>";
	$html[] = ($user->admin) ? "\t\t\t\t<li><a tabindex=\"96\" href=\"{$TDTRAC_SITE}admin/\""			.(($action['module'] == "admin")	?" class=\"active\"":"")." title=\"User, Group &amp; Permissions Management\">Admin</a></li>" : "";
	$html[] = ($user->loggedin)?"\t\t\t\t<li><a tabindex=\"97\" href=\"{$TDTRAC_SITE}user/logout/\" title=\"Logout of system\">Logout</a></li>":"";
	$html[] = "\t\t\t\t<li><a tabindex=\"98\" href=\"\" id=\"helplink\" title=\"Help Popup\" >Help</a></li>";
	$html[] = "\t\t\t</ul>\n\t\t</div>\n\t\t<div id=\"menubottom\"></div>\n\n\t\t<div id=\"content\">\n\t\t\t<div id=\"normalcontent\">";

	return $html;
}

/**
 * Make page footer
 * 
 * @return array Formatted HTML
 */
function makeFooter() {
	$html[] = "\t\t\t</div>\n\t\t</div>";
	$html[] = "\t\t<div id=\"footer\">";
	$html[] = "\t\t\t<div class=\"left\">&copy; 2008-".date('Y')." JTSage. All rights reserved.</div>";
	$html[] = "\t\t\t<div class=\"right\"><a href=\"http://tdtrac.com/\" title=\"TDTrac Homepage\">TDTrac Homepage</a></div>";
	$html[] = "\t\t</div>\n\t</div>\n</body>\n</html>";
	return $html;
}
?>
