<?php
/**
 * TDTrac Installation Program
 * 
 * Contains main program logic.
 * @package tdtrac
 * @version 1.4.0
 * @author J.T.Sage <jtsage@gmail.com>
 */
ob_start(); session_start(); 

## PROGRAM DETAILS. DO NOT EDIT UNLESS YOU KNOW WHAT YOU ARE DOING
$TDTRAC_VERSION = "1.4.0";
$TDTRAC_PERMS = array("addshow", "editshow", "viewshow", "addbudget", "editbudget", "viewbudget", "addhours", "edithours", "viewhours", "adduser");
$INSTALL_FILES = array(
	"index.php",
	"rcpt.php",
	"./lib/helpnodes.php",
	"./lib/install.inc.php",
	"./lib/budget.php",
	"./lib/dbaseconfig.php",
	"./lib/email.php",
	"./lib/formlib.php",
	"./lib/functions-load.php",
	"./lib/htmllib.php",
	"./lib/home.php",
	"./lib/hours.php",
	"./lib/login.php",
	"./lib/messaging.php",
	"./lib/permissions.php",
	"./lib/reciept.php", 
	"./lib/todo.php",
	"./lib/tablelib.php",
	"./lib/show.php" );
$INSTALL_TABLES = array(
        "tdtrac",
	"users",
	"budget",
	"groupnames",
	"hours",
	"msg",
	"permissions",
	"shows",
	"usergroups",
	"rcpts",
	"todo");

require_once("config.php");
require_once("lib/formlib.php");
$page_title = substr($_SERVER['REQUEST_URI'], 1); 
preg_match("/install.php\?(.+)$/", $page_title, $match);
$page_title = $match[1];
if ( $page_title == "" || $page_title == "install.php" ) { $page_title = "home"; }
require_once("lib/htmllib.php");
foreach ( makeHeader("Installer") as $line ) { echo "{$line}\n"; }

echo "<h3>TDTrac{$TDTRAC_VERSION} Installer</h3>\n";
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
		$form = new tdform("install.php?site", "form1");
		
		$fes = $form->addText('cpny', "Site Name", null, $TDTRAC_CPNY);
		$fes = $form->addText('site', "Site URL", null, $TDTRAC_SITE);
		$fes = $form->addText('dayrate', "Day Rate Payroll ( 1 = yes, 0 = no )", null, $TDTRAC_DAYRATE);
		$fes = $form->addText('payrate', "Default Day / Hourly Pay Rate", null, $TDTRAC_PAYRATE);
		
		echo join("\n", $form->output('Save Values'));
	}
	break;
    case "mysql" :
	if ( $_SERVER['REQUEST_METHOD'] == "POST" ) {
		$filename = "config.php";
		$fh = fopen($filename, 'a');
		fwrite($fh, "<?php\n");
		fwrite($fh, "\$MYSQL_SERVER = \"{$_REQUEST['server']}\";\n");
		fwrite($fh, "\$MYSQL_USER = \"{$_REQUEST['user']}\";\n");
		fwrite($fh, "\$MYSQL_PASS = \"{$_REQUEST['password']}\";\n");
		fwrite($fh, "\$MYSQL_DATABASE = \"{$_REQUEST['dbase']}\";\n");
		fwrite($fh, "\$MYSQL_PREFIX = \"{$_REQUEST['prefix']}\";\n?>\n");
		header("Location: install.php");
	} else {
		$form = new tdform("install.php?mysql", "form1");
		
		$fes = $form->addText('server', "MySQL Host (host:port)", null, $MYSQL_SERVER);
		$fes = $form->addText('user', "Username", null, $MYSQL_USER);
		$fes = $form->addText('password', "Password", null, $MYSQL_PASS);
		$fes = $form->addText('dbase', "Database", null, $MYSQL_DATABASE);
		$fes = $form->addText('prefix', "Table Prefix (prefix_)", null, $MYSQL_PREFIX);
		
		echo join("\n", $form->output('Save Values'));
	}
	break;
    case "home" :
	echo "<div class=\"installer\"><ul><li>Checking Enviroment...<ul>\n";
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
	echo "</ul></li></ul></div><div style=\"text-align: center\">\n";
	if ( !$noinstall ) { 
		echo "<a href=\"install.php?doinstall\">[-Proceed With Installation-]</a>\n";
	} else {
		echo "Unable To Install - Please Correct Above Errors\n";
	}
	echo "</div>\n";
	break;
}

foreach ( makeFooter() as $line ) { echo "{$line}\n"; }

?>
