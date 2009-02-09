<?php
ob_start(); session_start(); 

## PROGRAM DETAILS. DO NOT EDIT UNLESS YOU KNOW WHAT YOU ARE DOING
$TDTRAC_VERSION = "1.0.0beta1";
$TDTRAC_PERMS = array("addshow", "editshow", "viewshow", "addbudget", "editbudget", "viewbudget", "addhours", "edithours", "viewhours", "adduser");
$INSTALL_FILES = array(
	"index.php",
	"./lib/install.inc.php",
	"./lib/budget.php",
	"./lib/dbaseconfig.php",
	"./lib/email.php",
	"./lib/footer.php",
	"./lib/functions-load.php",
	"./lib/header.php",
	"./lib/home.php",
	"./lib/hours.php",
	"./lib/login.php",
	"./lib/messaging.php",
	"./lib/permissions.php", 
	"./lib/show.php" );
$INSTALL_TABLES = array(
	"users",
	"budget",
	"groupnames",
	"hours",
	"msg",
	"permissions",
	"shows",
	"usergroups");

require_once("config.php");
$page_title = substr($_SERVER['REQUEST_URI'], 1); 
preg_match("/install.php\?(.+)$/", $page_title, $match);
$page_title = $match[1];
if ( $page_title == "" || $page_title == "install.php" ) { $page_title = "home"; }
require_once("lib/header.php");

echo "<h2>TDTrac{$TDTRAC_VERSION} Installer</h2>\n";
$sqllink = 1; $noinstall = 0;

switch ($page_title) {
    case "doinstall" :
	require_once("lib/dbaseconfig.php");
	require_once("lib/install.inc.php");
	echo "Installation DONE!";
	break;
    case "site" :
	if ( $_SERVER['REQUEST_METHOD'] == "POST" ) { 
		$filename = "config.php";
		$fh = fopen($filename, 'a');
		fwrite($fh, "<?php\n");
		fwrite($fh, "\$TDTRAC_CPNY = \"{$_REQUEST['cpny']}\";\n");
		fwrite($fh, "\$TDTRAC_SITE = \"{$_REQUEST['site']}\";\n");
		fwrite($fh, "\$TDTRAC_DAYRATE = \"{$_REQUEST['dayrate']}\";\n");
		fwrite($fh, "\$TDTRAC_PAYRATE = \"{$_REQUEST['payrate']}\";\n?>\n");
		header("Location: install.php");
	} else {
		echo "<div id=\"genform\"><form method=\"post\" action=\"install.php?site\" name=\"form1\">\n";
		echo "<div class=\"frmele\">Site Name: <input type=\"text\" size=\"35\" name=\"cpny\" value=\"{$TDTRAC_CPNY}\"/></div>\n";
		echo "<div class=\"frmele\">Site URL: <input type=\"text\" size=\"35\" name=\"site\" value=\"{$TDTRAC_SITE}\"/></div>\n";
		echo "<div class=\"frmele\">Day Rate Payroll ( 1 = yes, 0 = no ): <input type=\"text\" size=\"35\" name=\"dayrate\" value=\"{$TDTRAC_DAYRATE}\"/></div>\n";
		echo "<div class=\"frmele\">Day / Hourly Pay Rate: <input type=\"text\" size=\"35\" name=\"payrate\" value=\"{$TDTRAC_PAYRATE}\"/></div>\n";
        	echo "<div class=\"frmele\"><input type=\"submit\" value=\"Save Values\" /></div>\n";
        	echo "</form></div>\n";
	}
	break;
    case "mysql" :
	if ( $_SERVER['REQUEST_METHOD'] == "POST" ) {
                $filename = "config.php";
                $fh = fopen($filename, 'a');
                fwrite($fh, "<?php\n");
                fwrite($fh, "\$MYSQL_SERVER = \"{$_REQUEST['server']}\";\n");
                fwrite($fh, "\$MYSQL_USER = \"{$_REQUEST['user']}\";\n");
                fwrite($fh, "\$MYSQL_PASS = \"{$_REQUEST['pass']}\";\n");
                fwrite($fh, "\$MYSQL_DATABASE = \"{$_REQUEST['dbase']}\";\n");
                fwrite($fh, "\$MYSQL_PREFIX = \"{$_REQUEST['prefix']}\";\n?>\n");
                header("Location: install.php");

	} else {
		echo "<div id=\"genform\"><form method=\"post\" action=\"install.php?mysql\" name=\"form1\">\n";
		echo "<div class=\"frmele\">MySQL Host: (host:port) <input type=\"text\" size=\"35\" name=\"server\" value=\"{$MYSQL_SERVER}\"/></div>\n";
		echo "<div class=\"frmele\">Username: <input type=\"text\" size=\"35\" name=\"user\" value=\"{$MYSQL_USER}\"/></div>\n";
		echo "<div class=\"frmele\">Password: <input type=\"text\" size=\"35\" name=\"pass\" value=\"{$MYSQL_PASS}\"/></div>\n";
		echo "<div class=\"frmele\">Database: <input type=\"text\" size=\"35\" name=\"dbase\" value=\"{$MYSQL_DATABASE}\"/></div>\n";
		echo "<div class=\"frmele\">Table Prefix: (prefix_) <input type=\"text\" size=\"35\" name=\"prefix\" value=\"{$MYSQL_PREFIX}\"/></div>\n";
        	echo "<div class=\"frmele\"><input type=\"submit\" value=\"Save Values\" /></div>\n";
        	echo "</form></div>\n";
	}
	break;
    case "home" :
	echo "<p><ul><li>Checking Enviroment...<ul>\n";
	  // Config File
  	  $perms = substr(sprintf('%o', fileperms("config.php")), -4);
	  echo ($perms == "0666") ? "<li style=\"color:green\"><b>OK::</b> config.php - World Writable</li>" : "<li style=\"color:red\"><b>FAIL::</b> config.php - Must be writable by webserver</li>";
          if ( $perms <> "0666" ) { $sqllink = 0; }
	echo "</ul></li></ul>";
	echo "<ul><li>Checking Config...";
	echo ( $sqllink ) ? "<a href=\"install.php?site\">[-Edit Values-]</a>" : "";
	echo "<ul>\n";
	echo "<li><b>Site Name::</b> {$TDTRAC_CPNY}</li>\n";
	echo "<li><b>Site URL::</b> {$TDTRAC_SITE}</li>\n";
	echo "<li><b>Day Rate Payroll::</b> ";
        echo ($TDTRAC_DAYRATE) ? "YES" : "NO";
	echo "</li>\n";
	echo "<li><b>Per Day/Hour Pay Rate::</b> \${$TDTRAC_PAYRATE}</li>\n";
	echo "</ul></li></ul>\n";
	echo "<ul><li>Checking Files...<ul>\n";
	  // Check File Exists
	  foreach ($INSTALL_FILES as $file) {
		echo "<li style=\"color:";
		echo ( file_exists($file) ) ? "green\"><b>FOUND::</b> {$file}" : "red\"><b>NOT FOUND::</b> {$file} <b>!!ERROR!!</b>";
		echo "</li>\n";
	  } 
	echo "</ul></li></ul>\n";
	echo "<ul><li>Checking MySQL From config.php... ";
        echo ( $sqllink ) ? "<a href=\"install.php?mysql\">[-Edit Settings-]</a>" : "";
        echo "<ul>\n";
	  $db = mysql_connect($MYSQL_SERVER, $MYSQL_USER, $MYSQL_PASS);
	  if (!$db) {
		echo "<li style=\"color: red\"><b>FAILURE::</b> Could not connect: " . mysql_error() . "</li>\n"; $noinstall = 1;
	  } else {
		echo "<li style=\"color: green\"><b>SUCCESS::</b> Connected to mysql host</li>\n";
	  }
	  $dbr = mysql_select_db($MYSQL_DATABASE, $db);
	  if (!$dbr) {
		echo "<li style=\"color: red\"><b>FAILURE::</b> Cannot Use {$MYSQL_DATABASE}: " . mysql_error() . "</li>\n"; $noinstall = 1;
	  } else {
		echo "<li style=\"color: green\"><b>SUCCESS::</b> Connected to database</li>\n";
	  }
	  if ( !$noinstall ) {
		$sql = "SHOW TABLES";
		$result = mysql_query($sql, $db);
		while ( $row = mysql_fetch_array($result) ) {
			$found = 0;
			foreach ( $INSTALL_TABLES as $check ) {
				$check = $MYSQL_PREFIX . $check;
				if ( $row[0] == $check ) { $found = 1; }
			}
			if ( $found ) {
				echo "<li style=\"color: red\"><b>FAILURE::</b> {$row[0]} Table Already Exists.</li>\n";
				$noinstall = 1;
			}
		}
		echo ( !$noinstall ) ? "<li style=\"color: green\"><b>SUCCESS::</b> No Existing Tables will be clobbered</li>\n" : "";
	  }
	echo "</ul></li></ul><div style=\"text-align: center\">\n";
	if ( !$noinstall ) { 
		echo "<a href=\"install.php?doinstall\">[-Proceed With Installation-]</a>\n";
	} else {
		echo "Unable To Install - Please Correct Above Errors\n";
	}
	echo "</div></p>\n";
	break;
}

require_once("lib/footer.php");

?>
